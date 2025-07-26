<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-800 leading-tight">
            Data Siswa
        </h2>
    </x-slot>

    <div class="max-w-8xl mx-auto px-2 sm:px-6 lg:px-8">

        {{-- SWAL ALERT --}}
        @if (session('success'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: @json(session('success')),
                        timer: 1800,
                        showConfirmButton: false,
                        timerProgressBar: true,
                        position: 'center',
                        customClass: { popup: 'rounded-2xl' }
                    });
                });
            </script>
        @endif
        @if (session('error'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: @json(session('error')),
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                        position: 'center',
                        customClass: { popup: 'rounded-2xl' }
                    });
                });
            </script>
        @endif

        @if(Auth::user()->hasRole('admin'))
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex gap-2">
                <button
                    onclick="document.getElementById('modal').classList.remove('hidden')"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-xl shadow transition"
                >
                    + Tambah Siswa
                </button>
                <button
                    onclick="document.getElementById('modalImport').classList.remove('hidden')"
                    class="bg-yellow-400 hover:bg-yellow-500 text-green-900 font-semibold px-4 py-2 rounded-xl shadow transition border border-yellow-300"
                >
                    Import Siswa
                </button>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <input
                    type="text"
                    id="search-input"
                    placeholder="Cari nama / NIS / NISN"
                    class="border border-green-300 rounded-xl px-3 py-2 w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-green-400 bg-white"
                />
                @if(request('search'))
                    <a href="{{ route('siswa.index') }}" class="text-sm text-red-600 underline ml-2 whitespace-nowrap">Reset</a>
                @endif
            </div>
        </div>
        <div class="bg-white/80 rounded-2xl shadow-xl p-6 border border-green-100 backdrop-blur-md">
            <div id="siswa-table" class="min-w-full">
                @include('siswa.partials.table', ['siswa' => $siswa])
            </div>
            <div class="mt-3">
                {{ $siswa->links() }}
            </div>
        </div>

        {{-- Modal Import Siswa --}}
        <div id="modalImport" class="fixed inset-0 z-50 flex items-start justify-center bg-black bg-opacity-50 p-4 pt-20 overflow-x-auto overflow-y-auto hidden" style="backdrop-filter: blur(4px);">
            <div class="bg-white/90 p-6 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto border border-green-200">
                <h2 class="text-xl font-bold mb-4 text-green-900">Import Data Siswa dari Excel</h2>
                <form action="{{ route('siswa.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <span class="block font-semibold text-sm text-green-800">
                            Format:
                            <a href="{{ route('siswa.template') }}" class="text-blue-700 font-semibold underline" target="_blank">
                                Download Template Siswa
                            </a>
                        </span>
                        <span class="block text-xs text-gray-600">Kolom wajib: <b>nama</b>, <b>nis</b>, <b>no_hp_wali_santri</b></span>
                    </div>
                    <div>
                        <label class="block font-semibold text-green-700 mb-1">Tahun Ajaran Masuk <span class="text-red-500">*</span></label>
                        <select name="tahun_ajaran_id" required class="w-full border-2 border-green-400 rounded-xl shadow py-2 px-4 focus:ring-2 focus:ring-green-300 text-base">
                            <option value="">Pilih Tahun Ajaran</option>
                            @foreach($tahunAjarans as $ta)
                                <option value="{{ $ta->id }}">{{ $ta->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-green-700 mb-1">Kelas <span class="text-red-500">*</span></label>
                        <select name="kelas_id" required class="w-full border-2 border-green-400 rounded-xl shadow py-2 px-4 focus:ring-2 focus:ring-green-300 text-base">
                            <option value="">Pilih Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-green-700 mb-1">File Excel <span class="text-red-500">*</span></label>
                        <input type="file" name="file" accept=".xls,.xlsx" required class="block w-full border-2 border-green-400 rounded-xl py-2 px-4 focus:ring-2 focus:ring-green-300"/>
                    </div>
                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" onclick="document.getElementById('modalImport').classList.add('hidden')" class="px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 font-semibold rounded-xl border border-yellow-300 transition">Batal</button>
                        <button type="submit" class="bg-yellow-400 hover:bg-yellow-500 text-green-900 font-semibold px-4 py-2 rounded-xl shadow border border-yellow-300 transition">Import</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Tambah Siswa --}}
        <div id="modal" class="fixed inset-0 z-50 flex items-start justify-center bg-black bg-opacity-50 p-4 pt-20 overflow-x-auto overflow-y-auto hidden" style="backdrop-filter: blur(4px);">
            <div class="bg-white/90 p-6 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto border border-green-200">
                <h2 class="text-xl font-bold mb-4 text-green-900">Tambah Siswa</h2>
                <form action="{{ route('siswa.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-green-700 font-semibold mb-1">NIS</label>
                            <input name="nis" type="text" required class="w-full border border-green-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-green-300 bg-white" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-green-700 font-semibold mb-1">Nama Siswa</label>
                            <input name="nama" type="text" required class="w-full border border-green-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-green-300 bg-white" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-green-700 font-semibold mb-1">Kelas</label>
                            <select name="kelas_id" required class="w-full border border-green-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-green-300 bg-white">
                                <option value="" disabled selected>-- Pilih Kelas --</option>
                                @foreach ($kelas as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-green-700 font-semibold mb-1">No Telp Orang Tua / Wali</label>
                            <input name="no_hp" type="text" required class="w-full border border-green-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-green-300 bg-white" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-green-700 font-semibold mb-1">Tahun Ajaran Masuk</label>
                            <select name="tahun_masuk" required class="w-full border border-green-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-green-300 bg-white">
                                <option value="" disabled selected>-- Pilih Tahun Ajaran --</option>
                                @foreach ($tahunAjarans as $ta)
                                    <option value="{{ $ta->id }}">{{ $ta->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 mt-6">
                        <button
                            type="button"
                            onclick="document.getElementById('modal').classList.add('hidden')"
                            class="px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 font-semibold rounded-xl border border-yellow-300 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-xl shadow transition"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @elseif(Auth::user()->hasRole('petugas'))
        {{-- PETUGAS: TIDAK ADA TOMBOL TAMBAH DAN IMPORT --}}
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <input
                    type="text"
                    id="search-input"
                    placeholder="Cari nama / NIS / NISN"
                    class="border border-green-300 rounded-xl px-3 py-2 w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-green-400 bg-white"
                />
                @if(request('search'))
                    <a href="{{ route('siswa.index') }}" class="text-sm text-red-600 underline ml-2 whitespace-nowrap">Reset</a>
                @endif
            </div>
        </div>
        <div class="bg-white/80 rounded-2xl shadow-xl p-6 border border-green-100 backdrop-blur-md">
            <div id="siswa-table" class="min-w-full">
                @include('siswa.partials.table', ['siswa' => $siswa])
            </div>
            <div class="mt-3">
                {{ $siswa->links() }}
            </div>
        </div>
        @endif
    </div>

    <script>
        // Fade out helper
        function fadeOut(el, duration = 200) {
            el.style.transition = `opacity ${duration}ms`;
            el.style.opacity = 0.4;
        }
        function fadeIn(el, duration = 200) {
            el.style.transition = `opacity ${duration}ms`;
            el.style.opacity = 1;
        }
        function debounce(fn, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn.apply(this, args), delay);
            };
        }
        const searchInput = document.getElementById('search-input');
        const siswaTable = document.getElementById('siswa-table');
        if(searchInput && siswaTable){
            searchInput.addEventListener('input', debounce(function () {
                const query = this.value;
                fadeOut(siswaTable, 150);
                fetch(`{{ route('siswa.index') }}?search=${encodeURIComponent(query)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.text())
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    let newTable = doc.getElementById('siswa-table');
                    if(newTable){
                        siswaTable.innerHTML = newTable.innerHTML;
                    }
                    fadeIn(siswaTable, 150);
                });
            }, 250));
        }
    </script>
</x-app-layout>
