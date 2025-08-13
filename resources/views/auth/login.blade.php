<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.png') }}">
    <link href="https://fonts.googleapis.com/css?family=Inter:400,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icon CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-50 via-yellow-50 to-white">
    <div class="w-full max-w-md mx-auto p-6 bg-amber-50 rounded-2xl shadow-2xl border-t-8 border-green-400">
        <div class="flex justify-center mb-6">
            <div class="rounded-full border-4 border-green-300 shadow-md p-1 bg-white flex items-center justify-center" style="width: 90px; height: 90px;">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="object-contain w-20 h-20" style="max-width: 80px; max-height: 80px;" />
            </div>
        </div>
        <h2 class="text-center text-2xl font-bold text-green-700 mb-7 mt-3">Login</h2>
        <!-- Form -->
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label for="login" class="block text-green-700 font-semibold mb-1">Email atau Username</label>
                <input id="login" type="text" name="login" value="{{ old('login') }}"
                    class="w-full rounded-lg border border-green-200 focus:ring-2 focus:ring-green-300 px-4 py-2.5 text-gray-700 bg-white"
                    required autofocus>
                @error('login')
                    <div class="text-red-600 mt-1 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-2 relative">
                <label for="password" class="block text-green-700 font-semibold mb-1">Password</label>
                <div class="relative">
                    <input id="password" type="password" name="password"
                        class="w-full h-12 pr-12 rounded-lg border border-green-200 focus:ring-2 focus:ring-green-300 px-4 text-gray-700 bg-white"
                        required autocomplete="current-password">
                    <button type="button"
                        onclick="togglePassword()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center text-gray-400 hover:text-green-700 focus:outline-none"
                        tabindex="-1"
                        style="width:2rem; height:2rem;">
                        <i data-lucide="eye" id="eye-icon" class="w-6 h-6"></i>
                    </button>
                </div>
                @error('password')
                    <div class="text-red-600 mt-1 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-between mt-2 mb-5">
                <label class="flex items-center text-gray-700 text-sm">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="ml-2">Ingat saya</span>
                </label>
                @if (Route::has('password.request'))
                    <a class="text-green-600 hover:underline text-sm font-semibold" href="{{ route('password.request') }}">
                        Lupa Password?
                    </a>
                @endif
            </div>

            <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 rounded-lg shadow-lg transition text-lg tracking-wider">
                LOGIN
            </button>
        </form>
    </div>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script>
        lucide.createIcons();
        function togglePassword() {
            const pwInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (pwInput.type === "password") {
                pwInput.type = "text";
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                pwInput.type = "password";
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
</body>
</html>
