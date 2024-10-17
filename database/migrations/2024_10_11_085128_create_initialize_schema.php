<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->string('id_pelanggan', 12)->primary(); 
            $table->string('nama'); 
            $table->string('domisili'); 
            $table->enum('jenis_kelamin', ['L', 'P']); 
            $table->timestamps(); 
            $table->softDeletes(); 
        });

        Schema::create('barang', function (Blueprint $table) {
            $table->string('kode', 12)->primary(); 
            $table->string('nama'); 
            $table->string('kategori'); 
            $table->decimal('harga', 15, 2); 
            $table->timestamps(); 
            $table->softDeletes(); 
        });

        Schema::create('penjualan', function (Blueprint $table) {
            $table->string('id_nota', 12)->primary(); 
            $table->date('tgl'); 
            $table->string('kode_pelanggan', 12); 
            $table->foreign('kode_pelanggan')->references('id_pelanggan')->on('pelanggan')->onDelete('cascade');
            $table->decimal('subtotal', 15, 2); 
            $table->timestamps(); 
        });

        Schema::create('item_penjualan', function (Blueprint $table) {
            $table->id(); 
            $table->string('nota', 12); 
            $table->foreign('nota')->references('id_nota')->on('penjualan')->onDelete('cascade');
            $table->string('kode_barang', 12); 
            $table->foreign('kode_barang')->references('kode')->on('barang')->onDelete('cascade');
            $table->integer('qty'); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_penjualan');
        Schema::dropIfExists('penjualan');
        Schema::dropIfExists('barang');
        Schema::dropIfExists('pelanggan');
    }
};

