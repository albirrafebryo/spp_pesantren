<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Siswa extends Model
{
    use HasFactory;

    // protected $table = 'siswas';
    protected $guarded = [];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function spp()
    {
        return $this->belongsTo(Spp::class, 'spp_id');
    }

    // Relasi ke tabel pembayaran berdasarkan NISN
    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'nisn', 'nisn');
    }
}
