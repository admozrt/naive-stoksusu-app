<?php

namespace Database\Seeders;

use App\Models\DataStok;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Naive',
            'username' => 'naive',
            'email' => 'test@example.com',
            'password' => 'adminja'
        ]);

        // Create 10 random DataStok entries without using factory
        for ($i = 0; $i < 15; $i++) {
            DataStok::create([
                'merk' => rand(1, 20),
                'stok' => rand(10, 200),
                'permintaan' => rand(10, 200),
                'penjualan' => rand(10, 200),
                'kategori_stok' => ['sedang', 'banyak', 'sedikit'][array_rand(['sedang', 'banyak', 'sedikit'])]
            ]);
        }
    }
}
