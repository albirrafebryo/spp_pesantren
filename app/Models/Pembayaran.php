<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $guarded = [];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function detailPembayaran()
    {
        return $this->belongsTo(DetailPembayaran::class, 'detail_pembayaran_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }
    public function histories() 
    {
        return $this->hasMany(PembayaranHistory::class);
    }

    // Sudah ada, ini relasi hasMany satu pembayaran bisa punya banyak bukti (legacy)
    public function buktiPembayarans()
    {
        return $this->hasMany(BuktiPembayaran::class, 'pembayaran_id');
    }

    // Tambahan: relasi many-to-many ke BuktiPembayaran lewat pivot validasi_pembayaran
    public function validasiBuktiPembayaran()
    {
        return $this->belongsToMany(BuktiPembayaran::class, 'validasi_pembayaran');
    }

    // Otomatis update status jika jumlah_bayar berubah
    protected static function booted()
{
    static::saving(function ($pembayaran) {
        // Jangan ubah status jika memang pending
        if ($pembayaran->status === 'pending') {
            return;
        }
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
