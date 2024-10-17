<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait RandomIdTrait 
{
    /**
     * Generate a random string of a given length.
     *
     * @param int $length
     * @return string
     */
    public function generateCustomId($length = 10) 
    {
        return Str::random($length);
    }
}
