<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_stok', function (Blueprint $table) {
            // Ubah kolom dari integer ke decimal untuk mendukung angka desimal dan negatif
            $table->decimal('stok', 10, 2)->change();
            $table->decimal('permintaan', 10, 2)->change();
            $table->decimal('penjualan', 10, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('data_stok', function (Blueprint $table) {
            // Kembalikan ke integer jika rollback
            $table->integer('stok')->change();
            $table->integer('permintaan')->change();
            $table->integer('penjualan')->change();
        });
    }
};
