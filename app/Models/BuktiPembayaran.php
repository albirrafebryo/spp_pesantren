<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiPembayaran extends Model
{
    
    protected $guarded = [];

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    // Tambahan: relasi many-to-many ke Pembayaran lewat pivot validasi_pembayaran
    public function validasiPembayarans()
    {
        return $this->belongsToMany(Pembayaran::class, 'validasi_pembayaran');
    }
}
