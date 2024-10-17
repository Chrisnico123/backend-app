<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelanggan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pelanggan'; 
    protected $primaryKey = 'id_pelanggan'; 
    protected $keyType = 'string'; 
    public $incrementing = false; 
    protected $fillable = ['id_pelanggan', 'nama', 'domisili', 'jenis_kelamin']; 
    protected $dates = ['deleted_at'];

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class);
    }
}
