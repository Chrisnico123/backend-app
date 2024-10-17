<?php

namespace App\Http\Validations;

class BarangValidation
{
    /**
     * @return array
     */
    public static function storeOrUpdate()
    {
        return [
            'nama'      => ['required', 'string', 'max:255'],
            'kategori'  => ['required', 'string', 'max:100'],
            'harga'     => ['required', 'numeric', 'min:0'], 
        ];
    }
}
