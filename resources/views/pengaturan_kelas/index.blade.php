<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-700 leading-tight">
            Pengaturan Kelas
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 space-y-8 px-4 sm:px-6 lg:px-8">
        
        {{-- TOMBOL ATUR SISWA --}}
        <div class="flex justify-end">
            <a href="{{ route('pengaturan_kelas.aturSiswa') }}"
               class="inline-flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-green-900 font-semibold rounded-xl px-5 py-3 shadow transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4" />
                </svg>
                Atur Siswa (Tidak Naik/Lulus/Keluar)
            </a>
        </div>

        {{-- SWEETALERT SUCCESS --}}
        @if(session('success'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: @json(session('success')),
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        position: 'center',
                        customClass: { popup: 'rounded-2xl' }
                    });
                });
            </script>
        @endif

        {{-- SWEETALERT ERROR --}}
        @if(session('error'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: @json(session('error')),
                        timer: 2200,
                        timerProgressBar: true,
                        showConfirmButton: false,
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
                    <select id="tahun_ajaran_id" name="tahun_ajaran_id" class="w-full border-green-300 rounded-xl shadow-sm focus:ring-green-500 focus:border-green-500" required>
                        <option value="">Pilih Tahun Ajaran</option>
                        @foreach($tahunAjarans as $ta)
                            <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id', $tahun_ajaran_id ?? '') == $ta->id ? 'selected' : '' }}>
                                {{ $ta->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="kelas_id" class="block mb-2 font-semibold text-green-700 text-sm">Kelas</label>
                    <select id="kelas_id" name="kelas_id" class="w-full border-green-300 rounded-xl shadow-sm focus:ring-green-500 focus:border-green-500" required>
                        <option value="">Pilih Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id', $kelas_id ?? '') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
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

        {{-- FORM NAIK KELAS --}}
        @if($tahun_ajaran_id && $kelas_id)
            <section class="bg-green-50 rounded-2xl shadow-lg p-6 border border-green-100 space-y-6">
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <input type="hidden" name="tahun_ajaran_id" value="{{ $tahun_ajaran_id }}">
                    <input type="hidden" name="kelas_id" value="{{ $kelas_id }}">
                    <div>
                        <label for="tahun_ajaran_baru_id" class="block mb-2 font-semibold text-green-700 text-sm">Tahun Ajaran Baru</label>
                        <select id="tahun_ajaran_baru_id" name="tahun_ajaran_baru_id" class="w-full border-green-300 rounded-xl shadow-sm focus:ring-green-500 focus:border-green-500" required>
                            <option value="">Pilih Tahun Ajaran Baru</option>
                            @foreach($tahunAjarans as $ta)
                                @if($ta->id > $tahun_ajaran_id)
                                    <option value="{{ $ta->id }}" {{ old('tahun_ajaran_baru_id', $tahun_ajaran_baru_id ?? '') == $ta->id ? 'selected' : '' }}>
                                        {{ $ta->nama }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-500 text-green-900 font-bold py-3 rounded-xl shadow transition-all">
                            Tampilkan Siswa
                        </button>
                    </div>
                </form>

                @if($tahun_ajaran_baru_id)
                    <form id="naikKelasForm" action="{{ route('pengaturan_kelas.prosesNaikKelasMassal') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="tahun_ajaran_id" value="{{ $tahun_ajaran_id }}">
                        <input type="hidden" name="kelas_id" value="{{ $kelas_id }}">
                        <input type="hidden" name="tahun_ajaran_baru_id" value="{{ $tahun_ajaran_baru_id }}">

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div>
                                <label for="kelas_baru_id" class="block mb-2 font-semibold text-green-700 text-sm">Kelas Baru</label>
                                <select id="kelas_baru_id" name="kelas_baru_id" class="w-full border-green-300 rounded-xl shadow-sm focus:ring-green-500 focus:border-green-500" required>
                                    <option value="">Pilih Kelas Baru</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="button" onclick="konfirmasiNaikKelas()" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-xl shadow transition-all mt-1">
                                    Naikkan Siswa Terpilih
                                </button>
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
                                        <th class="px-4 py-3 text-left text-xs font-bold text-green-700 uppercase">Kelas Sekarang</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-green-700 uppercase">Tahun Ajaran</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-yellow-700 uppercase">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-green-100">
                                    @forelse($siswa_naik as $i => $siswa)
                                        @php
                                            $sudahDiaturTA = \App\Models\PengaturanKelas::where('siswa_id', $siswa->id)
                                                ->where('tahun_ajaran_id', $tahun_ajaran_baru_id)
                                                ->exists();

                                            $statusKhusus = \App\Models\PengaturanKelas::where('siswa_id', $siswa->id)
                                                ->where('tahun_ajaran_id', $tahun_ajaran_baru_id)
                                                ->whereIn('status', ['tidak naik', 'lulus', 'mutasi', 'keluar'])
                                                ->first();
                                        @endphp
                                        <tr class="hover:bg-green-50 transition">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-green-900">{{ $i + 1 }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                                <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}" {{ $sudahDiaturTA ? 'disabled' : 'checked' }} class="form-checkbox h-5 w-5 text-green-500 rounded-lg border-green-300"/>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-green-900">{{ $siswa->nama }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-green-800">{{ $siswa->nis }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-green-800">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-green-800">{{ $tahunAjarans->where('id', $tahun_ajaran_id)->first()->nama ?? '-' }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-yellow-600 font-bold">
                                                @if($siswa->pengaturan_status == 'tidak naik')
                                                    Tidak Naik
                                                    @if($siswa->pengaturan_keterangan)
                                                        <span class="font-normal">({{ $siswa->pengaturan_keterangan }})</span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-10 text-gray-400 italic">
                                                Tidak ada siswa yang dapat dinaikkan kelas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>
                @endif
            </section>
        @endif

    </div>

    {{-- CDN SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Konfirmasi sebelum submit form naik kelas (tetap pakai tombol)
        function konfirmasiNaikKelas() {
            Swal.fire({
                title: 'Proses Naik Kelas?',
                text: 'Data siswa yang terpilih akan dinaikkan kelas!',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, proses',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-2xl' }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('naikKelasForm').submit();
                }
            });
        }
    </script>
</x-app-layout>
