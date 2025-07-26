<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranHistory extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi
    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function detailPembayaran()
    {
        return $this->belongsTo(DetailPembayaran::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
