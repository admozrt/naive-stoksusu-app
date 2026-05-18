<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Setelah refactor: 1 baris per kategori (bukan per data_stok),
        // jadi id_stok tidak lagi diperlukan. Drop FK + jadikan nullable.
        Schema::table('data_likelihood', function (Blueprint $table) {
            $table->dropForeign(['id_stok']);
            $table->unsignedInteger('id_stok')->nullable()->change();
        });

        Schema::table('data_probabilitas', function (Blueprint $table) {
            $table->dropForeign(['id_stok']);
            $table->unsignedInteger('id_stok')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('data_likelihood', function (Blueprint $table) {
            $table->unsignedInteger('id_stok')->nullable(false)->change();
            $table->foreign('id_stok')->references('id_stok')->on('data_stok')->onDelete('cascade');
        });

        Schema::table('data_probabilitas', function (Blueprint $table) {
            $table->unsignedInteger('id_stok')->nullable(false)->change();
            $table->foreign('id_stok')->references('id_stok')->on('data_stok')->onDelete('cascade');
        });
    }
};
