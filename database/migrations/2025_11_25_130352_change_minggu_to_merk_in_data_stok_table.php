<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('data_stok', function (Blueprint $table) {
            // Check if minggu column exists before renaming
            if (Schema::hasColumn('data_stok', 'minggu')) {
                $table->renameColumn('minggu', 'merk');
            }

            // If merk already exists, ensure it has the right type
            if (Schema::hasColumn('data_stok', 'merk')) {
                DB::statement('ALTER TABLE data_stok MODIFY merk VARCHAR(100) NOT NULL');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_stok', function (Blueprint $table) {
            // Revert back to minggu if needed
            if (Schema::hasColumn('data_stok', 'merk')) {
                $table->renameColumn('merk', 'minggu');
            }

            // Ensure minggu has the right type
            if (Schema::hasColumn('data_stok', 'minggu')) {
                DB::statement('ALTER TABLE data_stok MODIFY minggu INT(10) UNSIGNED NOT NULL');
            }
        });
    }
};
