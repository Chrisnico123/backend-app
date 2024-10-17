<?php

namespace App\Http\Validations;

class PelangganValidation
{
    /**
     * @return array
     */
    public static function storeOrUpdate()
    {
        return [
            'nama'          => ['required', 'string', 'max:255'],
            'domisili'      => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'], 
        ];
    }
}
