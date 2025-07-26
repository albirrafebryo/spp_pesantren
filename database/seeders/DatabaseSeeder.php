<?php

namespace Database\Seeders;

use App\Models\Spp;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\JenisPembayaran;
use App\Models\HistoryKelas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Jalankan user seeder (admin, bendahara, dan wali)
        $this->call([
            UserSeeder::class,
        ]);

        // Buat Tahun Ajaran
       TahunAjaran::create([
    'nama'      => '2025/2026',
    'mulai'     => '2025-07-01',
    'selesai'   => '2026-06-30',
    'is_active' => true,
]);

        // Buat kelas (pastikan id kelas sesuai kebutuhan factory siswa)
        Kelas::insert([
            ['nama_kelas' => '7', ],
            ['nama_kelas' => '8', ],
            ['nama_kelas' => '9', ],
            ['nama_kelas' => '10', ],
            ['nama_kelas' => '11', ],
            ['nama_kelas' => '12', ],
        ]);

        // Buat jenis pembayaran
        JenisPembayaran::insert([
            ['nama' => 'SPP A6', 'tipe' => 1],
            ['nama' => 'Laundry A6', 'tipe' => 1],
            ['nama' => 'Daftar Ulang A6', 'tipe' => 0],
            ['nama' => 'Tabungan A6', 'tipe' => 0],
        ]);

        // ==== Ambil semua user wali untuk assign ke siswa ====
       $waliUsers = User::role('wali')->get();

    // Buat siswa
    // $jumlahSiswa = 50; // Ganti sesuai kebutuhan
    // Siswa::factory($jumlahSiswa)->create([
    //     'tahun_masuk' => $tahunAjaran2024->id,
    // ]);

    // Ambil ulang semua siswa
    // $siswas = Siswa::all();

    // Generate user wali jika kurang dari jumlah siswa
//     if ($waliUsers->count() < $siswas->count()) {
//     $kurang = $siswas->count() - $waliUsers->count();
//     for ($i = 0; $i < $kurang; $i++) {
//         $newWali = User::factory()->create([
//             'name' => 'Wali Auto ' . ($waliUsers->count() + 1),
//             'email' => 'wali' . ($waliUsers->count() + 1) . '@example.com',
//             'password' => bcrypt('password'),
//         ]);
//         $newWali->assignRole('wali'); // Assign role wali
//         $waliUsers->push($newWali);
//     }
// }

    // Pair satu-satu siswa ke wali
//     $waliUsers = $waliUsers->values(); // reset index
// foreach ($siswas as $idx => $siswa) {
//     $wali = $waliUsers[$idx];
//     $siswa->wali_id = $wali->id;
//     $siswa->save();
// }

        // ===================== AUTO GENERATE HISTORY_KELAS =====================
        // $siswas = Siswa::all();
        // foreach ($siswas as $siswa) {
        //     $tahunAjaran = TahunAjaran::find($siswa->tahun_masuk);
        //     if ($tahunAjaran) {
        //         $bulanMulai = $tahunAjaran->mulai ? (int)date('n', strtotime($tahunAjaran->mulai)) : 7;
        //         HistoryKelas::updateOrCreate([
        //             'siswa_id' => $siswa->id,
        //             'tahun_ajaran_id' => $tahunAjaran->id,
        //         ], [
        //             'kelas_id' => $siswa->kelas_id,
        //             'bulan_mulai' => $bulanMulai,
        //         ]);
        //     }
        // }
        // =======================================================================
    }
}
