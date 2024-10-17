<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'barang'; 
    protected $primaryKey = 'kode'; 
    public $incrementing = false;

    protected $fillable = ['kode', 'nama', 'kategori', 'harga'];
    protected $dates = ['deleted_at'];

    public function itemPenjualan()
    {
        return $this->hasMany(ItemPenjualan::class, 'kode_barang', 'kode');
    }
}
