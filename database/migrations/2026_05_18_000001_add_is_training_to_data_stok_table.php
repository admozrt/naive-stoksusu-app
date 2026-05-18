<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_stok', function (Blueprint $table) {
            // true = data training (manual input), false = hasil prediksi
            $table->boolean('is_training')->default(true)->after('kategori_stok');
        });
    }

    public function down(): void
    {
        Schema::table('data_stok', function (Blueprint $table) {
            $table->dropColumn('is_training');
        });
    }
};
