<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\spp;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Siswa;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(
            [UserSeeder::class]
        );
        spp::create([
            'tahun_ajaran' => '2024/2025',
            'nominal' => '500000',
        ]);
        Kelas::create(['nama_kelas' => 'VII A']);
        Siswa::factory(150)->create();
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
