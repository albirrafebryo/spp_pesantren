<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
// Spatie
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Ambil role dari parameter GET, default null
        $selectedRole = $request->input('role');
        $roles = Role::pluck('name');

        $users = User::with('roles')
            ->when($selectedRole, function($q) use ($selectedRole) {
                $q->role($selectedRole);
            })
            ->orderBy('name', 'asc') // urutkan berdasarkan nama (alfabet)
            ->paginate(10); // paginasi 10 per halaman

        // Pastikan query string role ikut saat pindah halaman
        $users->appends(['role' => $selectedRole]);
        return view('users.index', compact('users', 'roles', 'selectedRole'));
    }

    public function create()
    {
        // Tidak perlu, semua CRUD di halaman index
        return redirect()->route('users.index');
    }

    public function store(Request $request)
    {
        if ($request->id) {
            $user = User::findOrFail($request->id);

            $request->validate([
                'name' => 'required|string|max:255',
                'role' => 'required|in:admin,petugas,wali',
                'email' => 'required|unique:users,email,'.$user->id,
                'password' => 'nullable|string|min:6',
            ]);

            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            // Assign atau sync role (Spatie)
            $user->syncRoles([$request->role]);

            return redirect()->route('users.index')->with('success', 'User berhasil diupdate.');
        } else {
            $request->validate([
                'name' => 'required|string|max:255',
                'role' => 'required|in:admin,petugas,wali',
                // Hapus 'email' dari rules agar boleh username/email, hanya required & unique
                'email' => 'required|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email, // boleh username atau email
                'password' => Hash::make($request->password),
            ]);

            // Assign role (Spatie)
            $user->assignRole($request->role);

            return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
        }
    }

    public function edit(User $user)
    {
        // Tidak perlu, form ada di index
        return redirect()->route('users.index');
    }

    public function update(Request $request, User $user)
    {
        // Tidak perlu, proses update di method store() (karena satu form satu halaman)
        return redirect()->route('users.index');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
