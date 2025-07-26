<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-700 leading-tight">
            Nominal Pembayaran
        </h2>
    </x-slot>

    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- SWAL Success --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: @json(session('success')),
                    showConfirmButton: false,
                    timer: 1700,
                    timerProgressBar: true,
                    position: 'center',
                    customClass: { popup: 'rounded-2xl' }
                });
            });
        </script>
    @endif
    {{-- SWAL Error --}}
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: @json(session('error')),
                    showConfirmButton: false,
                    timer: 2100,
                    timerProgressBar: true,
                    position: 'center',
                    customClass: { popup: 'rounded-2xl' }
                });
            });
        </script>
    @endif

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-green-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <h3 class="font-bold text-lg text-green-800">Daftar Nominal Pembayaran</h3>
                    <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-xl font-semibold shadow transition"
                    >
                        + Tambah Data
                    </button>
                </div>
                <div class="overflow-x-auto rounded-xl border border-green-100 bg-white/90">
                    <table class="min-w-full table-auto text-sm rounded-lg overflow-hidden">
                        <thead>
                            <tr class="bg-green-50 text-green-900">
                                <th class="px-4 py-3 font-semibold">No</th>
                                <th class="px-4 py-3 font-semibold">Tahun Ajaran</th>
                                <th class="px-4 py-3 font-semibold">Jenis Pembayaran</th>
                                <th class="px-4 py-3 font-semibold">Nominal</th>
                                <th class="px-4 py-3 font-semibold">Angkatan Mulai</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white/80 divide-y divide-green-100">
                            @forelse ($DetailPembayarans as $detail)
                                <tr class="hover:bg-green-50/40 transition">
                                    <td class="px-4 py-3 text-center">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 text-center">{{ $detail->tahunAjaran->nama ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">{{ $detail->jenisPembayaran->nama ?? '-' }}</td>
                                    <td class="px-4 py-3 font-semibold text-green-700 text-center">
                                        Rp {{ number_format($detail->nominal, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ $detail->angkatan_mulai ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-gray-400">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Data -->
    <div id="modal-tambah" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative border border-green-100">
            <button type="button" class="absolute top-2 right-3 text-2xl text-gray-400 hover:text-red-500"
                onclick="document.getElementById('modal-tambah').classList.add('hidden')">&times;</button>
            <h2 class="text-lg font-bold mb-4 text-green-800">Tambah Detail Pembayaran</h2>
            <form action="{{ route('detail_pembayaran.store') }}" method="POST" id="form-tambah" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-1 font-medium text-green-800">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="border border-green-300 rounded-xl px-3 py-2 w-full focus:ring-2 focus:ring-green-400 outline-none bg-white" required>
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($tahunAjarans as $ta)
                            <option value="{{ $ta->id }}">{{ $ta->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-medium text-green-800">Jenis Pembayaran</label>
                    <select name="jenis_pembayaran_id" class="border border-green-300 rounded-xl px-3 py-2 w-full focus:ring-2 focus:ring-green-400 outline-none bg-white" required>
                        <option value="">-- Pilih Jenis Pembayaran --</option>
                        @foreach($jenisPembayarans as $jp)
                            <option value="{{ $jp->id }}">{{ $jp->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-medium text-green-800">Nominal (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                        <input type="text" name="nominal" id="input-nominal"
                            class="border border-green-300 rounded-xl pl-10 px-2 py-2 w-full focus:ring-2 focus:ring-green-400 outline-none bg-white"
                            placeholder="0,00" required autocomplete="off" inputmode="decimal">
                    </div>
                </div>
                <div>
                    <label class="block mb-1 font-medium text-green-800">Angkatan Mulai (Tahun Ajaran)</label>
                    <select name="angkatan_mulai" class="border border-green-300 rounded-xl px-3 py-2 w-full focus:ring-2 focus:ring-green-400 outline-none bg-white" required>
                        <option value="">-- Pilih Angkatan --</option>
                        @foreach($angkatanList as $angkatan)
                            <option value="{{ $angkatan['id'] }}">{{ $angkatan['label'] }}</option>
                        @endforeach
                    </select>
                    <small class="text-gray-500 block mt-1">Urutan angkatan dan tahun ajaran siswa yang mulai <span class="font-bold">WAJIB</span> membayar tagihan ini.</small>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button"
                        onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                        class="px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 rounded-xl font-semibold transition border border-yellow-300"
                    >Batal</button>
                    <button type="submit"
                        class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition shadow"
                    >Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Format input nominal: Rp, ribuan, dan desimal (koma)
    const inputNominal = document.getElementById('input-nominal');
    if(inputNominal){
        inputNominal.addEventListener('input', function (e) {
            let value = e.target.value.replace(/[^0-9,]/g, '').replace(/^0+(?=\d)/, "");
            let parts = value.split(',');
            // ribuan pakai titik
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            e.target.value = parts.join(',');
        });
    }
    // Saat submit, kirim ke backend tanpa titik ribuan, koma jadi titik (misal 10.000,50 => 10000.50)
    document.getElementById('form-tambah').addEventListener('submit', function(e){
        let input = document.getElementById('input-nominal');
        if(input){
            let val = input.value.replace(/\./g,'').replace(',','.');
            input.value = val;
        }
    });
    </script>
</x-app-layout>
