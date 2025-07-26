<?php

namespace App\Models;

use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Spp extends Model
{
    use HasFactory;
    protected $guarded = [];
     public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'spp_id'); // relasi ke model Siswa dengan foreign key 'spp_id'
    }

    public function rincians()
    {
        return $this->hasMany(SppRincian::class);
    }
}
