<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.png') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        [x-cloak] { display: none !important; }

        body {
            position: relative;
            background-color: white;
            --wm-offset-x: 0px; /* default offset watermark */
        }
        /* Watermark logo fixed di tengah main content */
        body::before {
            content: "";
            position: fixed;
            top: 50%;
            left: calc(50% + var(--wm-offset-x));
            width: 440px;
            height: 440px;
            background-image: url('{{ asset('images/logo.jpg') }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            opacity: 0.07;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 0;
            transition: left 0.3s;
        }
        /* Pastikan header, sidebar, dan main content tetap di atas watermark */
        header, aside, main, .main-content {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body
  class="font-sans antialiased overflow-x-hidden"
  x-data="{
    sidebarOpen: window.innerWidth >= 640,
    sidebarWidth: 288, // default in px (18rem)
    setWatermarkOffset() {
      // Pusat main: setengah sidebar saja!
      let offset = this.sidebarOpen ? (this.sidebarWidth / 2) : 0;
      document.body.style.setProperty('--wm-offset-x', offset + 'px');
    },
    init() {
      // Dapatkan lebar sidebar secara dinamis jika ingin (opsional)
      let sb = document.querySelector('.w-72');
      this.sidebarWidth = sb ? sb.offsetWidth : 288;
      this.setWatermarkOffset();
      window.addEventListener('resize', () => {
        this.sidebarOpen = window.innerWidth >= 640;
        // reset sidebarWidth jika layout berubah lebar
        let sb = document.querySelector('.w-72');
        this.sidebarWidth = sb ? sb.offsetWidth : 288;
        this.setWatermarkOffset();
      });
      this.$watch('sidebarOpen', (v) => this.setWatermarkOffset());
    }
  }"
  x-init="init()"
>

    <!-- Header Bar -->
    <header
        class="fixed top-0 left-0 right-0 bg-green-800 flex items-center justify-between px-4 sm:px-6 py-3 z-50 shadow"
    >
        <!-- Hamburger Button -->
        <button
            @click="sidebarOpen = !sidebarOpen"
            class="text-white focus:outline-none focus:ring-2 focus:ring-white rounded p-2"
            aria-label="Toggle sidebar"
        >
            <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    :class="{ 'hidden': sidebarOpen, 'inline-flex': !sidebarOpen }"
                    class="inline-flex"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                />
                <path
                    :class="{ 'hidden': !sidebarOpen, 'inline-flex': sidebarOpen }"
                    class="hidden"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>

        <!-- Title -->
        <div
            class="text-white font-semibold text-lg sm:text-xl truncate mx-4 flex-1 text-center sm:text-left"
            :class="{ 'text-sm': !sidebarOpen }"
        >
            PPTQ Bilal bin Rabah
        </div>

        <!-- Profile -->
        <div class="flex items-center space-x-4 min-w-[120px] justify-end">
            <div class="relative" x-data="{ openUser: false }" @click.outside="openUser = false">
                <button @click="openUser = !openUser" class="focus:outline-none">
                    @if(Auth::user()->profile_photo)
                        <img src="{{ Storage::url(Auth::user()->profile_photo) }}"
                            alt="User"
                            class="w-8 h-8 rounded-full object-cover"
                            :class="{ 'w-6 h-6': !sidebarOpen }" />
                    @else
                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center"
                            :class="{ 'w-6 h-6': !sidebarOpen }">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-user">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                    @endif
                </button>
                <div
                    x-show="openUser"
                    x-cloak
                    class="absolute right-0 mt-2 w-48 bg-white rounded shadow-lg border border-gray-200 py-2 z-50"
                >
                    <div class="px-4 py-3 border-b flex items-center space-x-3">
                        @if(Auth::user()->profile_photo)
                            <img src="{{ Storage::url(Auth::user()->profile_photo) }}"
                                alt="User"
                                class="w-8 h-8 rounded-full object-cover"
                                :class="{ 'w-6 h-6': !sidebarOpen }" />
                        @else
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center"
                                :class="{ 'w-6 h-6': !sidebarOpen }">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-user">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <div class="text-sm font-semibold">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100 text-sm">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                        >
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <div
        class="fixed top-14 left-0 h-[calc(100vh-56px)] w-72 bg-white border-r transition-transform duration-300 ease-in-out"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        <div class="flex items-center space-x-4 p-4 border-b">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="h-12 w-auto" />
            <span class="font-semibold text-gray-800 text-sm leading-tight">
                Pondok Pesantren<br />Bilal bin Rabah
            </span>
        </div>
        @include('layouts.navigation')
    </div>

    <!-- Main content -->
    <div
      class="pt-14 min-h-screen bg-white"
      :class="sidebarOpen ? 'ml-72' : 'ml-0'"
    >
        <main class="p-4 max-w-full relative z-10">
            @isset($header)
                <h1 class="text-2xl font-bold mb-4 sm:hidden">{{ $header }}</h1>
            @endisset
            {{ $slot }}
        </main>
    </div>
</body>
</html>
