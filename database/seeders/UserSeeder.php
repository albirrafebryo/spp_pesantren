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

        $petugasRole = Role::firstOrCreate([ // Ubah ke petugas
            'name' => 'petugas',
            'guard_name' => 'web',
        ]);

        $waliRole = Role::firstOrCreate([
            'name' => 'wali',
            'guard_name' => 'web',
        ]);

        // Buat user Admin
        $adminUser = User::firstOrCreate([
            'email' => 'admin@pondok.com',
        ], [
            'name' => 'Admin Pondok',
            'password' => Hash::make('password'),
        ]);
        $adminUser->assignRole($adminRole);

        // Buat user Bendahara (role = petugas, nama tetap Bendahara Pondok)
        $bendaharaUser = User::firstOrCreate([
            'email' => 'bendahara@pondok.com',
        ], [
            'name' => 'Bendahara Pondok',
            'password' => Hash::make('password'),
        ]);
        $bendaharaUser->assignRole($petugasRole); // Role pakai petugas

        // ======================= BUAT USER WALI ==========================
        // $waliList = [
        //     [
        //         'name' => 'Wali A',
        //         'email' => 'wali_a@pondok.com',
        //     ],
        //     [
        //         'name' => 'Wali B',
        //         'email' => 'wali_b@pondok.com',
        //     ],
        //     [
        //         'name' => 'Wali C',
        //         'email' => 'wali_c@pondok.com',
        //     ],
           
        // ];

        // foreach ($waliList as $wali) {
        //     $userWali = User::firstOrCreate([
        //         'email' => $wali['email'],
        //     ], [
        //         'name' => $wali['name'],
        //         'password' => Hash::make('password'),
        //     ]);
        //     $userWali->assignRole($waliRole);
        // }
        // ======================= END USER WALI ===========================
    }
}
