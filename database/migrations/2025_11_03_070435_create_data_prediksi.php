<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_prediksi', function (Blueprint $table) {
            $table->increments('id_prediksi')->primary();
            $table->integer('id_stok', false, true)->length(12);
            $table->string('prediksi', 20);
            $table->timestamps();
            
            $table->foreign('id_stok')->references('id_stok')->on('data_stok')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_prediksi');
    }
};