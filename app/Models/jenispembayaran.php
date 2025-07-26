<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class jenispembayaran extends Model
{
     protected $table = 'jenispembayarans';
     protected $guarded = [];
     public function detailPembayaran()
     {
    return $this->hasMany(DetailPembayaran::class, 'jenis_pembayaran_id');
     }
}
