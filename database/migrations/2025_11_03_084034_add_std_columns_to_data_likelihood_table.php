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
        Schema::table('data_likelihood', function (Blueprint $table) {
            $table->decimal('stok_std', 10, 2)->default(0)->after('penjualan_li');
            $table->decimal('permintaan_std', 10, 2)->default(0)->after('stok_std');
            $table->decimal('penjualan_std', 10, 2)->default(0)->after('permintaan_std');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_likelihood', function (Blueprint $table) {
            $table->dropColumn(['stok_std', 'permintaan_std', 'penjualan_std']);
        });
    }
};