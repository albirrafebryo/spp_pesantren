<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-700 leading-tight">
            Atur Status Siswa 
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-8 py-8 px-2 sm:px-6 lg:px-8">

        {{-- TOMBOL KEMBALI --}}
        <div>
            <a 
                href="{{ route('pengaturan_kelas.index', ['tahun_ajaran_id' => $tahun_ajaran_id, 'kelas_id' => $kelas_id]) }}"
                class="inline-flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-green-900 font-bold rounded-xl px-5 py-2 shadow transition-all"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke Pengaturan Kelas
            </a>
        </div>

        {{-- SWEETALERT2 NOTIFIKASI --}}
        @if(session('success'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: @json(session('success')),
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                        position: 'center',
                        customClass: { popup: 'rounded-2xl' }
                    });
                });
            </script>
        @endif
        @if(session('error'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: @json(session('error')),
                        timer: 2200,
                        showConfirmButton: false,
                        timerProgressBar: true,
                        position: 'center',
                        customClass: { popup: 'rounded-2xl' }
                    });
                });
            </script>
        @endif

        {{-- FILTER --}}
        <section class="bg-white rounded-2xl shadow-lg p-6 border border-green-100">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label for="tahun_ajaran_id" class="block mb-2 font-semibold text-green-700 text-sm">Tahun Ajaran</label>
                    <select id="tahun_ajaran_id" name="tahun_ajaran_id" required
                        class="w-full border-green-300 rounded-xl shadow-sm focus:ring-green-500 focus:border-green-500">
                        <option value="">Tahun Ajaran</option>
                        @foreach($tahunAjarans as $ta)
                            <option value="{{ $ta->id }}" {{ $tahun_ajaran_id == $ta->id ? 'selected' : '' }}>{{ $ta->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="kelas_id" class="block mb-2 font-semibold text-green-700 text-sm">Kelas</label>
                    <select id="kelas_id" name="kelas_id" required
                        class="w-full border-green-300 rounded-xl shadow-sm focus:ring-green-500 focus:border-green-500">
                        <option value="">Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-xl shadow transition-all">
                        Tampilkan
                    </button>
                </div>
            </form>
        </section>

        {{-- FORM ATUR STATUS --}}
        @if($siswaList->count() > 0)
            <form action="{{ route('pengaturan_kelas.aturSiswaProses') }}" method="POST" class="bg-white rounded-2xl shadow-lg p-6 space-y-6 border border-green-100">
                @csrf
                <input type="hidden" name="tahun_ajaran_id" value="{{ $tahun_ajaran_id }}">
                <input type="hidden" name="kelas_id" value="{{ $kelas_id }}">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 items-end">
                    <div>
                        <label for="status" class="block mb-2 font-semibold text-green-700 text-sm">Status Siswa</label>
                        <select id="statusSelect" name="status" required onchange="toggleTahunBaru(this.value)"
                            class="w-full border-green-300 rounded-xl shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">Pilih Status</option>
                            <option value="tidak naik">Tidak Naik</option>
                            <option value="lulus">Lulus</option>
                            <option value="keluar">Keluar</option>
                            <option value="mutasi">Mutasi</option>
                        </select>
                    </div>
                    <div id="tahunBaruDiv" class="hidden">
                        <label for="tahun_ajaran_baru_id" class="block mb-2 font-semibold text-green-700 text-sm">Tahun Ajaran Baru</label>
                        <select name="tahun_ajaran_baru_id" id="tahun_ajaran_baru_id"
                            class="w-full border-green-300 rounded-xl shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">Pilih Tahun Ajaran Baru</option>
                            @foreach($tahunAjarans as $ta)
                                @if($ta->id != $tahun_ajaran_id)
                                    <option value="{{ $ta->id }}">{{ $ta->nama }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="keterangan" class="block mb-2 font-semibold text-green-700 text-sm">Keterangan (opsional)</label>
                        <input type="text" id="keterangan" name="keterangan" placeholder="Keterangan"
                            class="w-full border-green-300 rounded-xl shadow-sm focus:ring-green-500 focus:border-green-500 px-3 py-2" />
                    </div>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-green-200 shadow-sm">
                    <table class="min-w-full divide-y divide-green-100">
                        <thead class="bg-green-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-green-700 uppercase">#</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-green-700 uppercase">Pilih</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-green-700 uppercase">Nama Siswa</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-green-700 uppercase">NIS</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-green-700 uppercase">Kelas</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-green-700 uppercase">Tahun Ajaran</th>
                                {{-- Jika ingin fitur hapus per siswa, tambahkan kolom ini --}}
                                {{-- <th class="px-4 py-3 text-center text-xs font-bold text-red-700 uppercase">Hapus</th> --}}
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-green-100">
                            @foreach($siswaList as $i => $siswa)
                                <tr class="hover:bg-green-50 transition">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-green-900">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <input type="checkbox"
    name="siswa_id[]"
    value="{{ $siswa->id }}"
    class="form-checkbox h-5 w-5 text-green-500 rounded-lg border-green-300"
    data-kelas="{{ $siswa->kelas->nama_kelas ?? '' }}"
/>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-green-900">{{ $siswa->nama }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-green-800">{{ $siswa->nis }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-green-800">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-green-800">
                                        {{ $tahunAjarans->where('id', $tahun_ajaran_id)->first()->nama ?? '-' }}
                                    </td>
                                    {{-- Contoh jika ingin tombol hapus di tiap baris (bisa diaktifkan jika di-backend juga support) --}}
                                    {{--
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" onclick="hapusSiswa('{{ $siswa->id }}', '{{ $siswa->nama }}')"
                                            class="bg-red-100 text-red-600 border border-red-300 px-3 py-1 rounded-lg font-bold text-xs shadow hover:bg-red-200 transition">
                                            Hapus
                                        </button>
                                    </td>
                                    --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="w-full sm:w-auto bg-yellow-500 hover:bg-yellow-600 text-green-900 font-bold py-3 px-6 rounded-xl shadow transition-all">
                    Proses
                </button>
            </form>
        @elseif($tahun_ajaran_id && $kelas_id)
            <div class="bg-white p-6 rounded-xl text-center text-gray-400 font-semibold border border-green-100 shadow">
                Tidak ada siswa di filter ini.
            </div>
        @endif
    </div>

    {{-- Script show/hide tahun ajaran baru --}}
    <script>
         function toggleTahunBaru(status) {
        const div = document.getElementById('tahunBaruDiv');
        if (status === 'tidak naik') {
            div.classList.remove('hidden');
        } else {
            div.classList.add('hidden');
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('statusSelect');
        const checkboxes = document.querySelectorAll('input[type="checkbox"][name="siswa_id[]"]');

        function updateCheckboxes() {
            if (statusSelect.value === 'lulus') {
                checkboxes.forEach(cb => {
                    // Hanya centang siswa kelas 12
                    if (cb.dataset.kelas == '12') {
                        cb.checked = true;
                    } else {
                        cb.checked = false;
                    }
                });
            } else {
                checkboxes.forEach(cb => {
                    cb.checked = false;
                });
            }
        }

        // Jalankan saat pertama kali load
        const val = statusSelect.value;
        toggleTahunBaru(val);
        updateCheckboxes();

        // Saat status berubah
        statusSelect.addEventListener('change', function() {
            toggleTahunBaru(this.value);
            updateCheckboxes();
        });
    });
        // Jika ingin fitur hapus per siswa aktif, tambahkan handler ini
        /*
        function hapusSiswa(id, nama) {
            Swal.fire({
                icon: 'warning',
                title: 'Yakin hapus siswa?',
                text: 'Siswa: ' + nama,
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true,
                position: 'center',
                customClass: { popup: 'rounded-2xl' }
            });
            // Tambahkan submit ke backend jika diperlukan
        }
        */
    </script>
</x-app-layout>
