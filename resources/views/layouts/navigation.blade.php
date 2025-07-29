<div x-data="$persist({ sidebarOpen: true }).as('sidebarState')" class="flex">

    <!-- Sidebar -->
    <aside
        :class="sidebarOpen ? 'block translate-x-0' : 'hidden -translate-x-full'"
        class="fixed sm:static sm:block bg-white w-72 h-screen p-6 z-50 sm:z-auto sm:relative transition-all duration-300 overflow-y-auto space-y-6 border-r border-gray-200"
    >

        {{-- <div class="flex items-center space-x-4 pb-4 border-b">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="h-12 w-auto" />
            <span class="font-semibold text-gray-800 text-sm leading-tight">
                Pondok Pesantren<br>Bilal bin Rabah
            </span>
        </div> --}}

        <nav class="space-y-2 text-sm text-gray-700 font-medium">

            @if (Auth::user()->hasRole('admin'))

                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}"
                   @click="$root.sidebarOpen = false"
                   class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-gray-100 transition {{ request()->routeIs('dashboard') ? 'bg-gray-100 text-black' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-icon lucide-house"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('users.index') }}"
                   @click="$root.sidebarOpen = false"
                   class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-gray-100 transition {{ request()->routeIs('users.index') ? 'bg-gray-100 text-black' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-cog-icon lucide-user-round-cog"><path d="m14.305 19.53.923-.382"/><path d="m15.228 16.852-.923-.383"/><path d="m16.852 15.228-.383-.923"/><path d="m16.852 20.772-.383.924"/><path d="m19.148 15.228.383-.923"/><path d="m19.53 21.696-.382-.924"/><path d="M2 21a8 8 0 0 1 10.434-7.62"/><path d="m20.772 16.852.924-.383"/><path d="m20.772 19.148.924.383"/><circle cx="10" cy="8" r="5"/><circle cx="18" cy="18" r="3"/></svg>
                    <span>Kelola Pengguna</span>
                </a>

                <a href="{{ route('tahun_ajarans.index') }}"
                   @click="$root.sidebarOpen = false"
                   class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-gray-100 transition {{ request()->routeIs('tahun_ajarans.index') ? 'bg-gray-100 text-black' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-icon lucide-calendar"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                    <span>Tahun Ajaran</span>
                </a>

                <!-- Kelola kelas -->
                <div x-data="{ kelasOpen: $persist(true).as('kelolaKelas') }">
                    <button @click="kelasOpen = !kelasOpen"
                            class="w-full flex justify-between items-center px-3 py-2 hover:bg-gray-100 rounded transition">
                        <span class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-school-icon lucide-school"><path d="M14 22v-4a2 2 0 1 0-4 0v4"/><path d="m18 10 3.447 1.724a1 1 0 0 1 .553.894V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-7.382a1 1 0 0 1 .553-.894L6 10"/><path d="M18 5v17"/><path d="m4 6 7.106-3.553a2 2 0 0 1 1.788 0L20 6"/><path d="M6 5v17"/><circle cx="12" cy="9" r="2"/></svg>
                            <span>Kelola kelas</span>
                        </span>
                        <svg class="w-4 h-4 transform transition-transform"
                             :class="{ 'rotate-90': kelasOpen }" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div x-show="kelasOpen" x-cloak class="mt-2 space-y-1 text-gray-600 pl-10 flex flex-col">
                        <x-nav-link :href="route('kelas.index')" @click="$root.sidebarOpen = false" :active="request()->routeIs('kelas.*')">Kelas</x-nav-link>
                        <x-nav-link :href="route('pengaturan_kelas.index')" @click="$root.sidebarOpen = false" :active="request()->routeIs('pengaturan_kelas.*')">Pengaturan Kelas</x-nav-link>
                    </div>
                </div>

                <!-- Kelola Santri -->
                <div x-data="{ santriOpen: $persist(true).as('kelolasantri') }">
                    <button @click="santriOpen = !santriOpen"
                            class="w-full flex justify-between items-center px-3 py-2 hover:bg-gray-100 rounded transition">
                        <span class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                            <span>Kelola santri</span>
                        </span>
                        <svg class="w-4 h-4 transform transition-transform"
                             :class="{ 'rotate-90': santriOpen }" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div x-show="santriOpen" x-cloak class="mt-2 space-y-1 text-gray-600 pl-10 flex flex-col">
                        <x-nav-link :href="route('siswa.index')" @click="$root.sidebarOpen = false" :active="request()->routeIs('siswa.*')">Data Santri</x-nav-link>
                    </div>
                </div>

                

                <!-- Kelola Pembayaran -->
                <div x-data="{ bayarOpen: $persist(false).as('kelolaPembayaran') }">
                    <button @click="bayarOpen = !bayarOpen"
                            class="w-full flex justify-between items-center px-3 py-2 hover:bg-gray-100 rounded transition">
                        <span class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-banknote-icon lucide-banknote"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                            <span>Kelola Data Pembayaran</span>
                        </span>
                        <svg class="w-4 h-4 transform transition-transform"
                             :class="{ 'rotate-90': bayarOpen }" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div x-show="bayarOpen" x-cloak class="mt-2 space-y-1 text-gray-600 pl-10 flex flex-col">
                        <x-nav-link :href="route('jenispembayaran.index')" @click="$root.sidebarOpen = false" :active="request()->routeIs('jenispembayaran.*')">Jenis Pembayaran</x-nav-link>
                        <x-nav-link :href="route('detailpembayaran.index')" @click="$root.sidebarOpen = false" :active="request()->routeIs('detailpembayaran.*')">Nominal Pembayaran</x-nav-link>
                        <x-nav-link :href="route('spp.index')" @click="$root.sidebarOpen = false" :active="request()->routeIs('spp.*')">Data Pembayaran</x-nav-link>
                    </div>
                    <a href="{{ route('pembayaran.index') }}"
                    @click="$root.sidebarOpen = false"
                        class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-gray-100 transition {{ request()->routeIs('pembayaran.index') ? 'bg-gray-100 text-black' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right-left-icon lucide-arrow-right-left"><path d="m16 3 4 4-4 4"/><path d="M20 7H4"/><path d="m8 21-4-4 4-4"/><path d="M4 17h16"/>
                            </svg>
                    <span>Kelola Pembayaran</span>
                </a>
                </div>

            @elseif (Auth::user()->hasRole('petugas'))
            <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold border-b pb-2">Bendahara</div>
            <div class="flex flex-col space-y-2 pl-1 text-gray-700 text-sm">

                <a href="{{ route('dashboard') }}"
                   @click="$root.sidebarOpen = false"
                   class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-gray-100 transition {{ request()->routeIs('dashboard') ? 'bg-gray-100 text-black' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-icon lucide-house"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('siswa.index') }}"
                   @click="$root.sidebarOpen = false"
                   class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-gray-100 transition {{ request()->routeIs('siswa.index') ? 'bg-gray-100 text-black' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                    <span>Data Santri</span>
                </a>

                <a href="{{ route('pembayaran.index') }}"
                   @click="$root.sidebarOpen = false"
                   class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-gray-100 transition {{ request()->routeIs('pembayaran.index') ? 'bg-gray-100 text-black' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right-left-icon lucide-arrow-right-left"><path d="m16 3 4 4-4 4"/><path d="M20 7H4"/><path d="m8 21-4-4 4-4"/><path d="M4 17h16"/></svg>
                    <span>Pembayaran</span>
                </a>

                <a href="{{ route('laporan.index') }}"
                   @click="$root.sidebarOpen = false"
                   class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-gray-100 transition {{ request()->routeIs('laporan.index') ? 'bg-gray-100 text-black' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-folder-icon lucide-folder"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
                    <span>Laporan</span>
                </a>

                {{-- <div x-data="{ transaksiOpen: $persist(false).as('transaksiMenu') }">
                    <button @click="transaksiOpen = !transaksiOpen"
                            class="w-full flex justify-between items-center px-3 py-2 hover:bg-gray-100 rounded transition">
                        <span class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right-left-icon lucide-arrow-right-left"><path d="m16 3 4 4-4 4"/><path d="M20 7H4"/><path d="m8 21-4-4 4-4"/><path d="M4 17h16"/></svg>
                            <span>Transaksi</span>
                        </span>
                        <svg class="w-4 h-4 transform transition-transform"
                             :class="{ 'rotate-90': transaksiOpen }" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div x-show="transaksiOpen" x-cloak class="mt-2 space-y-2 text-gray-600 pl-8 flex flex-col">
                        <x-nav-link :href="route('pembayaran.index')" @click="$root.sidebarOpen = false" :active="request()->routeIs('pembayaran.index')">Input Pembayaran</x-nav-link>
                        <x-nav-link :href="route('pembayaran.rekap')" @click="$root.sidebarOpen = false" :active="request()->routeIs('pembayaran.rekap')">Rekap Pembayaran</x-nav-link>
                    </div>
                </div> --}}
                <!-- Laporan -->
    {{-- <div x-data="{ laporanOpen: $persist(false).as('laporanMenu') }">
        <button @click="laporanOpen = !laporanOpen"
                class="w-full flex justify-between items-center px-3 py-2 hover:bg-gray-100 rounded transition">
            <span class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-folder-icon lucide-folder"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
                <span>Laporan</span>
            </span>
            <svg class="w-4 h-4 transform transition-transform"
                 :class="{ 'rotate-90': laporanOpen }" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5l7 7-7 7" />
            </svg>
        </button>
        <div x-show="laporanOpen" x-cloak class="mt-2 space-y-2 text-gray-600 pl-8 flex flex-col">
            <x-nav-link :href="route('laporan.bulanan')" @click="$root.sidebarOpen = false" :active="request()->routeIs('laporan.bulanan')">Laporan Bulanan</x-nav-link>
            <x-nav-link :href="route('laporan.tahunan')" @click="$root.sidebarOpen = false" :active="request()->routeIs('laporan.tahunan')">Laporan Tahunan</x-nav-link>
        </div>
    </div>
            </div> --}}
            @endif

            @if (Auth::user()->hasRole('wali'))
                <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold border-b pb-2">Menu Wali</div>
                <div class="flex flex-col space-y-2 pl-1 text-gray-700 text-sm">

                    <a href="{{ route('dashboard') }}"
                       @click="$root.sidebarOpen = false"
                       class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-gray-100 transition {{ request()->routeIs('wali.dashboard') ? 'bg-gray-100 text-black' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-icon lucide-house"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('pembayaran.index') }}"
                       @click="$root.sidebarOpen = false"
                       class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-gray-100 transition {{ request()->routeIs('wali.pembayaran') ? 'bg-gray-100 text-black' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right-left-icon lucide-arrow-right-left"><path d="m16 3 4 4-4 4"/><path d="M20 7H4"/><path d="m8 21-4-4 4-4"/><path d="M4 17h16"/></svg>
                        <span>Pembayaran</span>
                    </a>
                    <a href="{{ route('laporan.index') }}"
                   @click="$root.sidebarOpen = false"
                   class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-gray-100 transition {{ request()->routeIs('siswa.*') ? 'bg-gray-100 text-black' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-folder-icon lucide-folder"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
                    <span>Laporan</span>
                    </a>
                </div>
            @endif
        </nav>
    </aside>

    <!-- Hamburger Button (Mobile) -->
    <div class="sm:hidden fixed top-4 left-4 z-50">
        <button @click="sidebarOpen = !sidebarOpen"
            class="text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700">
            
        </button>
    </div>
</div>
