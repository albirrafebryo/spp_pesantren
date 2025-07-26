<?php

namespace App\Models;

use App\Models\PengaturanKelas;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles; 
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kelas extends Model
{
    use HasFactory, HasRoles; 
    protected $guarded = [];

   
    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'kelas_id'); 
    }

    public function pengaturanKelas()
    {
        return $this->hasMany(PengaturanKelas::class, 'kelas_id');
    }
     public function historyKelas()
    {
        return $this->hasMany(\App\Models\HistoryKelas::class, 'kelas_id');
    }
}
