<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanKelas extends Model
{
    protected $table = 'pengaturan_kelas'; // pastikan nama sesuai
    protected $guarded = [];

    public function siswa() {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
    public function kelas() {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
    public function tahunAjaran() {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }
}
