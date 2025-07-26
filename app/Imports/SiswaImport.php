<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use App\Models\HistoryKelas;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToModel, WithHeadingRow
{
    protected $tahun_ajaran_id, $kelas_id;

    public function __construct($tahun_ajaran_id, $kelas_id)
    {
        $this->tahun_ajaran_id = $tahun_ajaran_id;
        $this->kelas_id = $kelas_id;
    }

    public function model(array $row)
    {
        // Kolom di Excel: nama, nis, no_hp_wali_santri
        $no_hp = $row['no_hp_wali_santri'] ?? $row['no_hp_ortu'] ?? null;
        $nama = $row['nama'] ?? '';
        $nis = $row['nis'] ?? '';

        // Jika ada kolom tahun_ajaran_id dan kelas_id di Excel, gunakan dari Excel, override constructor
        $tahun_ajaran_id = $row['tahun_ajaran_id'] ?? $this->tahun_ajaran_id;
        $kelas_id = $row['kelas_id'] ?? $this->kelas_id;

        // Skip jika kolom penting kosong
        if (!$no_hp || !$nama || !$nis || !$tahun_ajaran_id || !$kelas_id) {
            return null;
        }

        // 1. Buat/Cari User Wali (username/email = no_hp, password = 123)
        $wali = User::where('email', $no_hp)->first(); // atau pakai username jika ada field username
        if (!$wali) {
            $wali = User::create([
                'name'     => $nama,
                'email'    => $no_hp,
                'password' => Hash::make('123'), // password default "123"
            ]);
            // Assign role wali jika pakai Spatie
            if (method_exists($wali, 'assignRole')) {
                $wali->assignRole('wali');
            }
        }

        // 2. Buat Data Siswa
        $siswa = Siswa::create([
            'nama'        => $nama,
            'nis'         => $nis,
            'no_hp'       => $no_hp,
            'wali_id'     => $wali->id,
            'tahun_masuk' => $tahun_ajaran_id,
            'kelas_id'    => $kelas_id,
            'status'      => 'aktif',      
        ]);

        // 3. Sinkronisasi history_kelas
        HistoryKelas::updateOrCreate([
            'siswa_id'        => $siswa->id,
            'tahun_ajaran_id' => $tahun_ajaran_id,
        ], [
            'kelas_id' => $kelas_id,
        ]);

        return $siswa;
    }
}
