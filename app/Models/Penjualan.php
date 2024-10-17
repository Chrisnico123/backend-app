<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan'; 
    protected $primaryKey = 'id_nota'; 
    protected $keyType = 'string'; 
    public $incrementing = false; 

    protected $fillable = ['id_nota', 'tgl', 'kode_pelanggan', 'subtotal']; 

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'kode_pelanggan', 'id_pelanggan');
    }    

    public function itemPenjualan()
    {
        return $this->hasMany(ItemPenjualan::class, 'nota', 'id_nota');
    }
}
