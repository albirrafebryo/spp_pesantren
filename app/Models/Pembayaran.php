<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $fillable = [
        'nisn',
        'tahun_ajaran',
        'bulan',
        'jumlah_bayar',
        'jumlah_tagihan',
        'status'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'nisn', 'nisn');
    }

    // Otomatis update status jika jumlah_bayar berubah
    protected static function booted()
    {
        static::saving(function ($pembayaran) {
            if ($pembayaran->jumlah_bayar >= $pembayaran->jumlah_tagihan) {
                $pembayaran->status = 'lunas';
            } elseif ($pembayaran->jumlah_bayar > 0) {
                $pembayaran->status = 'cicilan';
            } else {
                $pembayaran->status = 'belum';
            }
        });
    }
}
