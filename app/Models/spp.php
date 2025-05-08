<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class spp extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'spp_id'); // relasi ke model Siswa dengan foreign key 'spp_id'
    }
}
