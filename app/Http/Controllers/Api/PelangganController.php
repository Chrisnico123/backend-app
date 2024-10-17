<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Validations\PelangganValidation;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\RandomIdTrait;

class PelangganController extends Controller
{
    use RandomIdTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 5);
        
        $pelanggans = Pelanggan::whereNull('deleted_at')->paginate($limit);
    
        return response()->json([
            'message'    => 'Data retrieved successfully',
            'data'       => $pelanggans->items(),
            'pagination' => [
                'current_page' => $pelanggans->currentPage(),
                'last_page'    => $pelanggans->lastPage(),
                'per_page'     => $pelanggans->perPage(),
                'total'        => $pelanggans->total(),
            ],
        ], Response::HTTP_OK);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), PelangganValidation::storeOrUpdate());

        if ($validated->fails()) {
            return $this->responseError($validated->errors(), 'The given parameter was invalid', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $customId = $this->generateCustomId(10);

        $pelanggan = Pelanggan::create([
            'id_pelanggan'  => $customId,
            'nama'          => $request->nama,
            'domisili'      => $request->domisili,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        return $this->responseSuccess($pelanggan, 'Successfully saved new Pelanggan', Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $pelangganId)
    {
        $pelanggan = Pelanggan::withTrashed()->find($pelangganId);

        if (!$pelanggan) {
            return $this->responseError(null, 'Pelanggan not found', Response::HTTP_NOT_FOUND);
        }

        return $this->responseSuccess($pelanggan, 'Get Pelanggan detail');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $pelangganId)
    {
        $validated = Validator::make($request->all(), PelangganValidation::storeOrUpdate());

        if ($validated->fails()) {
            return $this->responseError($validated->errors(), 'The given parameter was invalid', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pelanggan = Pelanggan::withTrashed()->find($pelangganId);

        if (!$pelanggan) {
            return $this->responseError(null, 'Pelanggan not found', Response::HTTP_NOT_FOUND);
        }

        $pelanggan->nama          = $request->nama;
        $pelanggan->domisili      = $request->domisili;
        $pelanggan->jenis_kelamin = $request->jenis_kelamin;

        $pelanggan->save();

        return $this->responseSuccess($pelanggan, 'Pelanggan updated successfully');
    }

    /**
     * Soft delete the specified resource from storage.
     */
    public function destroy(string $pelangganId)
    {
        $pelanggan = Pelanggan::find($pelangganId);

        if (!$pelanggan) {
            return $this->responseError(null, 'Pelanggan not found', Response::HTTP_NOT_FOUND);
        }

        $pelanggan->delete();

        return $this->responseSuccess(null, 'Pelanggan soft deleted successfully', Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore the specified soft-deleted resource.
     */
    public function restore(string $pelangganId)
    {
        $pelanggan = Pelanggan::onlyTrashed()->find($pelangganId);

        if (!$pelanggan) {
            return $this->responseError(null, 'Pelanggan not found or not soft deleted', Response::HTTP_NOT_FOUND);
        }

        $pelanggan->restore();

        return $this->responseSuccess($pelanggan, 'Pelanggan restored successfully');
    }
}
