<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles; // Import HasRoles trait jika perlu

class Kelas extends Model
{
    use HasFactory, HasRoles; // Menggunakan HasRoles jika diperlukan untuk kontrol hak akses

    protected $guarded = [];

    /**
     * Relasi Kelas ke Siswa (One-to-Many)
     * Menghubungkan kelas dengan siswa melalui foreign key 'kelas_id' pada tabel 'siswa'
     */
    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'kelas_id'); // relasi ke model Siswa dengan foreign key 'kelas_id'
    }

    // Jika Anda ingin menambahkan fitur role ke dalam model Kelas (misalnya, admin hanya bisa melihat kelas tertentu)
    // public function assignRoleToKelas($role)
    // {
    //     $this->assignRole($role);
    // }
}
