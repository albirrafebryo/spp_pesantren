<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    {{-- TOAST SELAMAT DATANG ADMIN --}}
    @if (session('show_welcome'))
        @if (Auth::user()->hasRole('admin'))
            <div 
                id="welcome-toast"
                class="fixed top-20 left-1/2 z-50 transform -translate-x-1/2 transition-all duration-500 opacity-0 pointer-events-none"
                style="min-width:240px;max-width:340px"
            >
                <div class="flex items-center gap-2 px-6 py-4 rounded-xl shadow-2xl border border-green-200 bg-green-50 text-green-900 font-semibold text-base">
                    <svg class="w-7 h-7 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Selamat Datang Admin!
                </div>
            </div>
        @elseif (Auth::user()->hasRole('petugas'))
            <div 
                id="welcome-toast"
                class="fixed top-20 left-1/2 z-50 transform -translate-x-1/2 transition-all duration-500 opacity-0 pointer-events-none"
                style="min-width:240px;max-width:340px"
            >
                <div class="flex items-center gap-2 px-6 py-4 rounded-xl shadow-2xl border border-green-200 bg-green-50 text-green-900 font-semibold text-base">
                    <svg class="w-7 h-7 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Selamat Datang Bendahara!
                </div>
            </div>
        @elseif (Auth::user()->hasRole('wali'))
            <div 
                id="welcome-toast"
                class="fixed top-20 left-1/2 z-50 transform -translate-x-1/2 transition-all duration-500 opacity-0 pointer-events-none"
                style="min-width:240px;max-width:340px"
            >
                <div class="flex items-center gap-2 px-6 py-4 rounded-xl shadow-2xl border border-green-200 bg-green-50 text-green-900 font-semibold text-base">
                    <svg class="w-7 h-7 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Selamat Datang Wali Santri!
                </div>
            </div>
        @endif
        {{-- Hapus session show_welcome setelah tampil --}}
        @php session()->forget('show_welcome'); @endphp
    @endif

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="p-8 md:p-12 text-gray-900">
            @if (Auth::user()->hasRole('admin'))
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8 md:gap-12 mt-6">

                    <a href="{{ route('tahun_ajarans.index') }}"
                       class="rounded-2xl border border-gray-200 p-10 md:p-14 bg-white bg-opacity-70 backdrop-blur-sm text-center shadow min-h-[220px] flex flex-col justify-center hover:shadow-2xl hover:ring-4 hover:ring-green-200 transition cursor-pointer focus:outline-none"
                       title="Lihat Data Tahun Ajaran">
                        <div class="text-gray-500 mb-3 text-lg md:text-xl">Tahun Ajaran</div>
                        <div class="text-3xl md:text-4xl font-extrabold tracking-tight">
                            {{ $tahunAjaran ?? '-' }}
                        </div>
                    </a>

                    <a href="{{ route('kelas.index') }}"
                       class="rounded-2xl border border-gray-200 p-10 md:p-14 bg-white bg-opacity-70 backdrop-blur-sm text-center shadow min-h-[220px] flex flex-col justify-center hover:shadow-2xl hover:ring-4 hover:ring-blue-200 transition cursor-pointer focus:outline-none"
                       title="Lihat Data Kelas">
                        <div class="text-gray-500 mb-3 text-lg md:text-xl">Total Kelas</div>
                        <div class="text-3xl md:text-4xl font-extrabold tracking-tight count-up" data-count="{{ $totalKelas ?? 0 }}">0</div>
                    </a>

                    <a href="{{ route('siswa.index') }}"
                       class="rounded-2xl border border-gray-200 p-10 md:p-14 bg-white bg-opacity-70 backdrop-blur-sm text-center shadow min-h-[220px] flex flex-col justify-center hover:shadow-2xl hover:ring-4 hover:ring-purple-200 transition cursor-pointer focus:outline-none"
                       title="Lihat Data Santri">
                        <div class="text-gray-500 mb-3 text-lg md:text-xl">Total Santri</div>
                        <div class="text-3xl md:text-4xl font-extrabold tracking-tight count-up" data-count="{{ $totalSantri ?? 0 }}">0</div>
                    </a>

                    <a href="{{ route('jenispembayaran.index') }}"
                       class="rounded-2xl border border-gray-200 p-10 md:p-14 bg-white bg-opacity-70 backdrop-blur-sm text-center shadow min-h-[220px] flex flex-col justify-center hover:shadow-2xl hover:ring-4 hover:ring-pink-200 transition cursor-pointer focus:outline-none"
                       title="Lihat Data Jenis Pembayaran">
                        <div class="text-gray-500 mb-3 text-lg md:text-xl">Total Data Pembayaran</div>
                        <div class="text-3xl md:text-4xl font-extrabold tracking-tight count-up" data-count="{{ $totalDataPembayaran ?? 0 }}">0</div>
                    </a>

                    <a href="{{ route('profile.edit') }}"
                       class="rounded-2xl border border-gray-200 p-10 md:p-14 bg-white bg-opacity-70 backdrop-blur-sm text-center shadow min-h-[220px] flex flex-col justify-center hover:shadow-2xl hover:ring-4 hover:ring-yellow-200 transition cursor-pointer focus:outline-none"
                       title="Lihat Data Bendahara">
                        <div class="text-gray-500 mb-3 text-lg md:text-xl">Total Bendahara</div>
                        <div class="text-3xl md:text-4xl font-extrabold tracking-tight count-up" data-count="{{ $totalBendahara ?? 0 }}">0</div>
                    </a>

                    <a href="{{ route('profile.edit') }}"
                       class="rounded-2xl border border-gray-200 p-10 md:p-14 bg-white bg-opacity-70 backdrop-blur-sm text-center shadow min-h-[220px] flex flex-col justify-center hover:shadow-2xl hover:ring-4 hover:ring-orange-200 transition cursor-pointer focus:outline-none"
                       title="Lihat Data Wali Santri">
                        <div class="text-gray-500 mb-3 text-lg md:text-xl">Total Wali Santri</div>
                        <div class="text-3xl md:text-4xl font-extrabold tracking-tight count-up" data-count="{{ $totalWaliSantri ?? 0 }}">0</div>
                    </a>
                </div>
            @elseif (Auth::user()->hasRole('petugas'))
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                    <!-- Tahun Ajaran -->
                    <div class="rounded-2xl border border-gray-300 p-6 md:p-10 bg-white text-center shadow min-h-[120px] flex flex-col justify-center hover:shadow-lg transition">
                        <div class="text-gray-700 mb-2 text-base md:text-lg">Tahun Ajaran</div>
                        <div class="text-2xl md:text-3xl font-extrabold tracking-tight">{{ $tahunAjaran ?? '-' }}</div>
                    </div>
                    <!-- Total Kelas -->
                    <div class="rounded-2xl border border-gray-300 p-6 md:p-10 bg-white text-center shadow min-h-[120px] flex flex-col justify-center hover:shadow-lg transition">
                        <div class="text-gray-700 mb-2 text-base md:text-lg">Total Kelas</div>
                        <div class="text-2xl md:text-3xl font-extrabold tracking-tight count-up" data-count="{{ $totalKelas ?? 0 }}">0</div>
                    </div>
                    <!-- Total Santri -->
                    <div class="rounded-2xl border border-gray-300 p-6 md:p-10 bg-white text-center shadow min-h-[120px] flex flex-col justify-center hover:shadow-lg transition">
                        <div class="text-gray-700 mb-2 text-base md:text-lg">Total Santri</div>
                        <div class="text-2xl md:text-3xl font-extrabold tracking-tight count-up" data-count="{{ $totalSantri ?? 0 }}">0</div>
                    </div>
                    <!-- Total SPP -->
                    <div class="rounded-2xl border border-gray-300 p-6 md:p-10 bg-white text-center shadow min-h-[120px] flex flex-col justify-center hover:shadow-lg transition">
                        <div class="text-gray-700 mb-2 text-base md:text-lg">Total SPP</div>
                        <div class="text-2xl md:text-3xl font-extrabold tracking-tight">
                        {{ 'Rp ' . number_format($totalSPP ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <!-- Total Laundry -->
                    <div class="rounded-2xl border border-gray-300 p-6 md:p-10 bg-white text-center shadow min-h-[120px] flex flex-col justify-center hover:shadow-lg transition">
                        <div class="text-gray-700 mb-2 text-base md:text-lg">Total Laundry</div>
                        <div class="text-2xl md:text-3xl font-extrabold tracking-tight count-up" data-count="{{ $totalLaundry ?? 0 }}">0</div>
                    </div>
                    <!-- Total Tabungan -->
                    <div class="rounded-2xl border border-gray-300 p-6 md:p-10 bg-white text-center shadow min-h-[120px] flex flex-col justify-center hover:shadow-lg transition">
                        <div class="text-gray-700 mb-2 text-base md:text-lg">Total Tabungan</div>
                        <div class="text-2xl md:text-3xl font-extrabold tracking-tight count-up" data-count="{{ $totalTabungan ?? 0 }}">0</div>
                    </div>
                </div>
            @elseif (Auth::user()->hasRole('wali'))
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
        <!-- Tahun Ajaran -->
        <div class="rounded-2xl border border-gray-300 p-6 md:p-10 bg-white text-center shadow min-h-[120px] flex flex-col justify-center hover:shadow-lg transition">
            <div class="text-gray-700 mb-2 text-base md:text-lg">Tahun Ajaran</div>
            <div class="text-2xl md:text-3xl font-extrabold tracking-tight">{{ $tahunAjaran ?? '-' }}</div>
        </div>
        <!-- Total Tagihan -->
        <div class="rounded-2xl border border-gray-300 p-6 md:p-10 bg-white text-center shadow min-h-[120px] flex flex-col justify-center hover:shadow-lg transition">
            <div class="text-gray-700 mb-2 text-base md:text-lg">Total Tagihan</div>
            <div class="text-2xl md:text-3xl font-extrabold tracking-tight">
                Rp {{ number_format($totalTagihanWali ?? 0, 0, ',', '.') }}
            </div>
        </div>
        <!-- Tagihan SPP -->
        <div class="rounded-2xl border border-gray-300 p-6 md:p-10 bg-white text-center shadow min-h-[120px] flex flex-col justify-center hover:shadow-lg transition">
            <div class="text-gray-700 mb-2 text-base md:text-lg">Tagihan SPP</div>
            <div class="text-2xl md:text-3xl font-extrabold tracking-tight">
                Rp {{ number_format($tagihanWali['SPP'] ?? 0, 0, ',', '.') }}
            </div>
        </div>
        <!-- Tagihan Laundry -->
        <div class="rounded-2xl border border-gray-300 p-6 md:p-10 bg-white text-center shadow min-h-[120px] flex flex-col justify-center hover:shadow-lg transition">
            <div class="text-gray-700 mb-2 text-base md:text-lg">Tagihan Laundry</div>
            <div class="text-2xl md:text-3xl font-extrabold tracking-tight">
                Rp {{ number_format($tagihanWali['Laundry'] ?? 0, 0, ',', '.') }}
            </div>
        </div>
        <!-- Tagihan Daftar Ulang -->
        <div class="rounded-2xl border border-gray-300 p-6 md:p-10 bg-white text-center shadow min-h-[120px] flex flex-col justify-center hover:shadow-lg transition">
            <div class="text-gray-700 mb-2 text-base md:text-lg">Tagihan Daftar Ulang</div>
            <div class="text-2xl md:text-3xl font-extrabold tracking-tight">
                Rp {{ number_format($tagihanWali['Daftar Ulang'] ?? 0, 0, ',', '.') }}
            </div>
        </div>
    </div>
@endif

        </div>
    </div>

    {{-- Toast JS + Animated Counter --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Show toast for admin/petugas/wali hanya jika ada elementnya
            const toast = document.getElementById("welcome-toast");
            if (toast) {
                setTimeout(() => {
                    toast.classList.remove("opacity-0", "pointer-events-none");
                    toast.classList.add("opacity-100");
                }, 200);
                setTimeout(() => {
                    toast.classList.remove("opacity-100");
                    toast.classList.add("opacity-0");
                }, 1700);
            }
            // Animated Counter
            const counters = document.querySelectorAll('.count-up');
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-count');
                const speed = 28;
                let count = 0;
                const inc = Math.max(1, Math.ceil(target / speed));
                function animate() {
                    count += inc;
                    if (count >= target) {
                        counter.textContent = target;
                    } else {
                        counter.textContent = count;
                        requestAnimationFrame(animate);
                    }
                }
                animate();
            });
        });
    </script>
</x-app-layout>
