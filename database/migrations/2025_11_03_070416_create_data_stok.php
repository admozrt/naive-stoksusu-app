<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_stok', function (Blueprint $table) {
            $table->integer('id_stok', false, true)->length(12)->primary();
            $table->string('merk', 100);
            $table->integer('stok', false, true)->length(12);
            $table->integer('permintaan', false, true)->length(10);
            $table->integer('penjualan', false, true)->length(10);
            $table->enum('kategori_stok', ['Sedang', 'Banyak', 'Sedikit', 'Tidak Ada']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_stok');
    }
};