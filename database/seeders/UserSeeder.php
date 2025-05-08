<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat role jika belum ada
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $petugasRole = Role::firstOrCreate([
            'name' => 'petugas',
            'guard_name' => 'web',
        ]);

        // Buat user Admin
        $adminUser = User::firstOrCreate([
            'email' => 'admin@pondok.com',
        ], [
            'name' => 'Admin Pondok',
            'password' => Hash::make('password'),
        ]);

        // Beri role admin ke user Admin
        $adminUser->assignRole($adminRole);

        // Buat user Petugas
        $petugasUser = User::firstOrCreate([
            'email' => 'petugas@pondok.com',
        ], [
            'name' => 'Petugas Pondok',
            'password' => Hash::make('password'),
        ]);

        // Beri role petugas ke user Petugas
        $petugasUser->assignRole($petugasRole);
    }
}
