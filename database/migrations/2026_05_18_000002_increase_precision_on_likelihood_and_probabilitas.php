<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Naikkan presisi agar std/mean/prior tidak ter-truncate jadi 0 atau pembulatan kasar
        Schema::table('data_likelihood', function (Blueprint $table) {
            $table->decimal('stok_li', 18, 6)->change();
            $table->decimal('permintaan_li', 18, 6)->change();
            $table->decimal('penjualan_li', 18, 6)->change();
            $table->decimal('stok_std', 18, 6)->default(0)->change();
            $table->decimal('permintaan_std', 18, 6)->default(0)->change();
            $table->decimal('penjualan_std', 18, 6)->default(0)->change();
        });

        Schema::table('data_probabilitas', function (Blueprint $table) {
            $table->decimal('probability', 20, 10)->change();
        });
    }

    public function down(): void
    {
        Schema::table('data_likelihood', function (Blueprint $table) {
            $table->float('stok_li', 15, 2)->change();
            $table->float('permintaan_li', 15, 2)->change();
            $table->float('penjualan_li', 15, 2)->change();
            $table->decimal('stok_std', 10, 2)->default(0)->change();
            $table->decimal('permintaan_std', 10, 2)->default(0)->change();
            $table->decimal('penjualan_std', 10, 2)->default(0)->change();
        });

        Schema::table('data_probabilitas', function (Blueprint $table) {
            $table->float('probability', 20, 2)->change();
        });
    }
};
