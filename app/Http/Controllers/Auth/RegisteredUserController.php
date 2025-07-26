<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        // Email atau username harus unik (di kolom email DB)
        'email' => [
            'required',
            'string',
            'max:255',
            'unique:users,email',
            // Custom rule: valid email atau minimal 3 karakter (username)
            function($attribute, $value, $fail) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !preg_match('/^[a-zA-Z0-9_.-]{3,}$/', $value)) {
                    $fail('Format email/username tidak valid');
                }
            }
        ],
        'password' => 'required|string|min:6|confirmed',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email, // username/email tetap masuk ke kolom email
        'password' => Hash::make($request->password),
    ]);

    $user->assignRole('wali');
    return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
}
}
