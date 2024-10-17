<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Validations\BarangValidation;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\RandomIdTrait;

class BarangController extends Controller
{
    use RandomIdTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 5);
        $barangs = Barang::whereNull('deleted_at')->paginate($limit);
    
        return response()->json([
            'message'    => 'List of Barangs',
            'data'       => $barangs->items(),
            'pagination' => [
                'current_page' => $barangs->currentPage(),
                'last_page'    => $barangs->lastPage(),
                'per_page'     => $barangs->perPage(),
                'total'        => $barangs->total(),
            ],
        ], Response::HTTP_OK);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), BarangValidation::storeOrUpdate());
    
        if ($validated->fails()) {
            return $this->responseError($validated->errors(), 'The given parameter was invalid', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        // Generate custom ID untuk kode barang
        $customId = $this->generateCustomId(10);
    
        $barang = Barang::create([
            'kode'      => $customId, 
            'nama'      => $request->nama,
            'kategori'  => $request->kategori,
            'harga'     => $request->harga,
        ]);
    
        return $this->responseSuccess($barang, 'Successfully saved new Barang', Response::HTTP_CREATED);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(string $barangId)
    {
        // Menggunakan withTrashed untuk cek barang yang mungkin sudah di-soft-delete
        $barang = Barang::withTrashed()->find($barangId);

        if (!$barang) {
            return $this->responseError(null, 'Barang not found', Response::HTTP_NOT_FOUND);
        }

        return $this->responseSuccess($barang, 'Get Barang detail');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $barangId)
    {
        $validated = Validator::make($request->all(), BarangValidation::storeOrUpdate());

        if ($validated->fails()) {
            return $this->responseError($validated->errors(), 'The given parameter was invalid', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Menggunakan withTrashed untuk memastikan barang bisa ditemukan walaupun sudah di-soft-delete
        $barang = Barang::withTrashed()->find($barangId);

        if (!$barang) {
            return $this->responseError(null, 'Barang not found', Response::HTTP_NOT_FOUND);
        }

        $barang->nama      = $request->nama;
        $barang->kategori  = $request->kategori;
        $barang->harga     = $request->harga;

        // Simpan update barang
        $barang->save();

        return $this->responseSuccess($barang, 'Barang updated successfully');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(string $barangId)
    {
        // Menggunakan withTrashed agar barang yang sudah di-soft-delete tidak bisa dihapus dua kali
        $barang = Barang::withTrashed()->find($barangId);

        if (!$barang) {
            return $this->responseError(null, 'Barang not found', Response::HTTP_NOT_FOUND);
        }

        // Jika barang sudah di-soft-delete, tidak perlu dihapus lagi
        if ($barang->trashed()) {
            return $this->responseError(null, 'Barang is already soft deleted', Response::HTTP_BAD_REQUEST);
        }

        // Soft delete barang
        $barang->delete();

        return $this->responseSuccess(null, 'Barang deleted successfully', Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore the specified soft-deleted resource.
     */
    public function restore(string $barangId)
    {
        // Mencari barang yang sudah di-soft-delete
        $barang = Barang::onlyTrashed()->find($barangId);

        if (!$barang) {
            return $this->responseError(null, 'Barang not found or not soft deleted', Response::HTTP_NOT_FOUND);
        }

        // Mengembalikan barang yang di-soft-delete
        $barang->restore();

        return $this->responseSuccess($barang, 'Barang restored successfully');
    }
}
