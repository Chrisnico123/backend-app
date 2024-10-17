<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\Pelanggan;
use App\Models\ItemPenjualan;
use App\Models\Barang;
use App\Traits\RandomIdTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PenjualanController extends Controller
{
    use RandomIdTrait;
    public function index(Request $request)
    {
        $limit = $request->get('limit', 5); 
        $page = $request->get('page', 1);

        $penjualan = DB::table('penjualan as p')
            ->leftJoin('pelanggan as p2', 'p.kode_pelanggan', '=', 'p2.id_pelanggan')
            ->select('p.id_nota', 'p2.nama', 'p.created_at', 'p.subtotal')
            ->paginate($limit, ['*'], 'page', $page);

        $data = $penjualan->map(function ($item) {
            return [
                'id_nota'    => $item->id_nota,
                'nama'       => $item->nama,
                'created_at' => $item->created_at,
                'subtotal'   => $item->subtotal,
            ];
        });

        return response()->json([
            'message'    => 'Data retrieved successfully',
            'data'       => $data,
            'pagination' => [
                'current_page' => $penjualan->currentPage(),
                'last_page'    => $penjualan->lastPage(),
                'per_page'     => $penjualan->perPage(),
                'total'        => $penjualan->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
    * Show the specified Penjualan by ID Nota.
    */
    public function show($idNota)
    {
        $penjualan = DB::table('penjualan as p')
            ->leftJoin('pelanggan as p2', 'p.kode_pelanggan', '=', 'p2.id_pelanggan')
            ->leftJoin('item_penjualan as ip', 'p.id_nota', '=', 'ip.nota')
            ->leftJoin('barang as b', 'ip.kode_barang', '=', 'b.kode')
            ->select(
                'p.id_nota',
                'p2.id_pelanggan',
                'p2.nama',
                'p2.domisili',
                'p2.jenis_kelamin',
                'p.created_at',
                'p.subtotal',
                'b.kode as kode_barang',
                'b.nama as nama_barang',
                'ip.qty'
            )
            ->where('p.id_nota', $idNota)
            ->get();

        // Jika data tidak ditemukan, kembalikan respon not found
        if ($penjualan->isEmpty()) {
            return response()->json([
                'message' => 'Penjualan not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Menyusun respons data
        $response = [
            'id_nota'      => $penjualan[0]->id_nota,
            'id_pelanggan' => $penjualan[0]->id_pelanggan,
            'nama'         => $penjualan[0]->nama,
            'domisili'     => $penjualan[0]->domisili,
            'jenis_kelamin'=> $penjualan[0]->jenis_kelamin,
            'created_at'   => $penjualan[0]->created_at,
            'barang'       => $penjualan->map(function ($item) {
                return [
                    'kode_barang' => $item->kode_barang,
                    'qty'         => $item->qty,
                    'nama_barang' => $item->nama_barang,
                ];
            })->toArray(),
        ];

        // Mengembalikan respons JSON
        return response()->json([
            'message' => 'Data retrieved successfully',
            'data'    => $response,
        ], Response::HTTP_OK);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'penjualan'    => 'required|array',
            'penjualan.*.kode_barang' => 'required|exists:barang,kode',
            'penjualan.*.qty'         => 'required|integer|min:1',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => 'The given parameter was invalid',
                'errors' => $validated->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $customId = $this->generateCustomId(10);

        DB::beginTransaction();

        try {
            
            $subtotal = 0;
            
            foreach ($request->penjualan as $item) {
                $harga = $this->getHargaBarang($item['kode_barang']);
                $total = $harga * $item['qty'];
                $subtotal += $total;
            }
            
            $penjualan = Penjualan::create([
                'id_nota'        => $customId,
                'tgl'            => now(),
                'kode_pelanggan' => $request->id_pelanggan,
                'subtotal'       => $subtotal, 
            ]);

            foreach ($request->penjualan as $item) {
                ItemPenjualan::create([
                    'nota'        => $customId,
                    'kode_barang' => $item['kode_barang'],
                    'qty'         => $item['qty'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Successfully saved new Penjualan',
                'data'    => $penjualan,
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Failed to save Penjualan',
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idNota)
    {
        $validated = Validator::make($request->all(), [
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'penjualan'    => 'required|array',
            'penjualan.*.kode_barang' => 'required|exists:barang,kode',
            'penjualan.*.qty'         => 'required|integer|min:1',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => 'The given parameter was invalid',
                'errors' => $validated->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();

        try {   
            $penjualan = Penjualan::find($idNota);
            if (!$penjualan) {
                return response()->json([
                    'message' => 'Penjualan not found',
                ], Response::HTTP_NOT_FOUND);
            }

            $penjualan->update(['kode_pelanggan' => $request->id_pelanggan]);

            $penjualan->itemPenjualan()->delete();

            $subtotal = 0;

            foreach ($request->penjualan as $item) {
                $harga = $this->getHargaBarang($item['kode_barang']);
                $total = $harga * $item['qty'];
                $subtotal += $total;

                ItemPenjualan::create([
                    'nota'        => $penjualan->id_nota,
                    'kode_barang' => $item['kode_barang'],
                    'qty'         => $item['qty'],
                    'harga'       => $harga,
                    'total'       => $total,
                ]);
            }

            $penjualan->update(['subtotal' => $subtotal]);

            DB::commit();

            return response()->json([
                'message' => 'Penjualan updated successfully',
                'data'    => $penjualan,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Failed to update Penjualan',
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function destroy(string $penjualanId)
    {
        $penjualan = Penjualan::find($penjualanId);

        if (!$penjualan) {
            return $this->responseError(null, 'penjualan not found', Response::HTTP_NOT_FOUND);
        }

        $penjualan->delete();

        return $this->responseSuccess(null, 'penjualan deleted successfully', Response::HTTP_NO_CONTENT);
    }

    /**
     * Get the price of a specific item.
     */
    private function getHargaBarang(string $kodeBarang)
    {
        $barang = Barang::where('kode', $kodeBarang)->first();
        return $barang ? $barang->harga : 0;
    }
     
}