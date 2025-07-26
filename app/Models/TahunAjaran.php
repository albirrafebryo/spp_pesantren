<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TahunAjaran extends Model
{
     use HasFactory;

    protected $guarded = [];
    
      public function spps()
    {
        return $this->hasMany(Spp::class);
    }
     public function pengaturanKelas()
    {
        return $this->hasMany(PengaturanKelas::class, 'tahun_ajaran_id');
    }
}
