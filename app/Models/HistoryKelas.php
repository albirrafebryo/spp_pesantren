<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryKelas extends Model
{
    protected $table = 'history_kelas';
    protected $guarded = [];

    public function siswa() { return $this->belongsTo(Siswa::class); }
    public function kelas() { return $this->belongsTo(Kelas::class); }
    public function tahunAjaran() { return $this->belongsTo(TahunAjaran::class); }
}
