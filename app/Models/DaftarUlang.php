<?php

namespace App\Models;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\DetailPembayaran;
use Illuminate\Database\Eloquent\Model;

class DaftarUlang extends Model
{
    protected $table = 'daftar_ulang';
    protected $guarded = [];
    public function tahunAjaran()
{
    return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
}

public function siswa()
{
    return $this->belongsTo(Siswa::class, 'siswa_id');
}

public function detailPembayaran()
{
    return $this->belongsTo(DetailPembayaran::class, 'detail_pembayaran_id');
}
}

