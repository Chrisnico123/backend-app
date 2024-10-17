<?php

namespace App\Http\Validations;

class ItemPenjualanValidation
{
    /**
     * @return array
     */
    public static function storeOrUpdate()
    {
        return [
            'nota'         => ['required', 'exists:App\Models\Penjualan,id_nota'], 
            'kode_barang'  => ['required', 'exists:App\Models\Barang,kode'], 
            'qty'          => ['required', 'integer', 'min:1'], 
        ];
    }
}
