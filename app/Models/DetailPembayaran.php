<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPembayaran extends Model
{
    /** @use HasFactory<\Database\Factories\DetailPembayaranFactory> */
    use HasFactory;

    protected $guarded =[];
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function jenisPembayaran()
    {
        return $this->belongsTo(JenisPembayaran::class, 'jenis_pembayaran_id');
    }
    
}
