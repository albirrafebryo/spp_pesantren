<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Siswa extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi ke kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function pengaturanKelas()
{
    return $this->hasMany(PengaturanKelas::class, 'siswa_id');
}

    // Relasi ke tahun ajaran (tahun masuk)
    public function tahunAjaranMasuk()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_masuk');
    }

     public function riwayatKelas()
    {
        return $this->hasMany(PengaturanKelas::class, 'siswa_id');
    }

    // Relasi ke pembayaran berdasarkan NISN
    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'siswa_id');
    }

    public function historyKelas()
    {
    return $this->hasMany(HistoryKelas::class, 'siswa_id');
    }
    public function historyKelasTerbaru()
    {
    return $this->hasOne(HistoryKelas::class, 'siswa_id')->orderByDesc('tahun_ajaran_id');
    }

    public function wali()
    {
    return $this->belongsTo(User::class, 'wali_id');
    }
    public function tabungan()
    {
    return $this->hasMany(Tabungan::class, 'siswa_id');
    }
}
