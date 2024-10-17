<?php

namespace App\Http\Validations;

class PenjualanValidation
{
    /**
     * @return array
     */
    public static function storeOrUpdate()
    {
        return [
            'tgl'           => ['required', 'date'], 
            'kode_pelanggan' => ['required', 'exists:App\Models\Pelanggan,id_pelanggan'], 
            'subtotal'      => ['required', 'numeric', 'min:0'], 
        ];
    }
}
