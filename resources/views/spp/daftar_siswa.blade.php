<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-700 leading-tight text-center">
            Daftar Siswa - {{ $detail->nama }} ({{ $detail->tahunajaran->nama }})
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

    <div class="py-6 px-2 max-w-7xl mx-auto">

        {{-- Tombol buka modal --}}
        @if($siswaPindahanCount > 0)
            <button onclick="document.getElementById('modalBulan').classList.remove('hidden')" 
                class="mb-4 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-xl shadow font-semibold transition active:scale-95">
                Atur Bulan Mulai Pembayaran
            </button>
        @endif

        {{-- Modal Atur Bulan Mulai Pembayaran --}}
        <div id="modalBulan" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-3">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative border border-green-100">
                <button type="button" class="absolute top-2 right-3 text-2xl text-gray-400 hover:text-red-500"
                    onclick="document.getElementById('modalBulan').classList.add('hidden')">&times;</button>
                <h3 class="text-lg font-bold mb-5 text-green-800 text-center">Atur Bulan Mulai Pembayaran (Siswa Pindahan)</h3>
                <form method="POST" action="{{ route('spp.setBulanMulai', $detail->id) }}">
                    @csrf

                    <div class="mb-5 relative" id="siswaSearchContainer">
                        <label class="block mb-2 font-medium text-green-800">Cari Siswa Pindahan</label>
                        <input type="text" id="searchSiswa" placeholder="Ketik nama siswa minimal 3 huruf..."
                               class="w-full border border-green-300 px-4 py-2 rounded-xl focus:ring-2 focus:ring-green-300 outline-none bg-white"
                               autocomplete="off">
                        <input type="hidden" name="siswa_id" id="siswaId">
                        <ul id="hasilPencarian" class="absolute z-10 bg-white border border-green-300 w-full mt-1 rounded shadow-lg hidden max-h-40 overflow-auto"></ul>
                    </div>

                    <div class="mb-6">
                        <label class="block mb-2 font-medium text-green-800">Bulan Mulai</label>
                        <select name="bulan_mulai" class="w-full border border-green-300 px-4 py-2 rounded-xl focus:ring-2 focus:ring-green-300 outline-none bg-white" required>
                            @foreach($bulanAjaran as $i)
                                <option value="{{ $i['bulan'] }}" @if($i['bulan'] == ($periodeMulai ?? $bulanAjaran[0]['bulan'])) selected @endif>
                                    {{ $i['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" onclick="document.getElementById('modalBulan').classList.add('hidden')"
                                class="px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 rounded-xl font-semibold transition border border-yellow-300">Batal</button>
                        <button type="submit"
                                class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition shadow">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto rounded-xl border border-green-100 shadow bg-white bg-opacity-90 backdrop-blur-sm p-6">
            <table class="min-w-full text-sm rounded-lg overflow-hidden">
    <thead>
        <tr>
            <th class="px-4 py-3 text-center font-bold text-green-800 bg-green-50">#</th>
            <th class="px-6 py-3 text-center font-bold text-green-800 bg-green-50 min-w-[160px] max-w-[280px] whitespace-normal">Nama</th>
            <th class="px-4 py-3 text-center font-bold text-green-800 bg-green-50">Kelas</th>
            @if($isTabungan)
                <th class="px-4 py-3 text-center font-bold text-green-800 bg-green-50">Total Tabungan</th>
            @elseif($isDaftarUlang)
                <th class="px-4 py-3 text-center font-bold text-green-800 bg-green-50">Tahun</th>
                <th class="px-4 py-3 text-center font-bold text-green-800 bg-green-50">Nominal</th>
            @else
                @foreach($bulanAjaran as $bulan)
                    <th class="px-4 py-3 text-xs text-center font-semibold bg-green-50 min-w-[90px] text-green-700">
                        {{ $bulan['label'] }}
                    </th>
                @endforeach
            @endif
        </tr>
    </thead>
    <tbody class="bg-white/80 divide-y divide-green-100">
    @forelse($historyKelasList as $no => $item)
        <tr>
            <td class="px-4 py-2 text-center">{{ $historyKelasList->firstItem() + $no }}</td>
            <td class="px-6 py-2 whitespace-normal">{{ $item->siswa->nama }}</td>
            <td class="px-4 py-2 text-center">{{ $item->kelas->nama_kelas ?? '-' }}</td>
            @if($isTabungan)
                <td class="px-4 py-2 text-right font-bold {{ $item->totalTabungan > 0 ? 'text-green-700' : 'text-gray-400' }}">
                    {{ $item->totalTabungan > 0 ? number_format($item->totalTabungan, 0, ',', '.') : '-' }}
                </td>
            @elseif($isDaftarUlang)
                <td class="px-4 py-2 text-center">
                    {{ $detail->tahunAjaran->nama ?? '-' }}
                </td>
                <td class="px-4 py-2 text-right font-bold text-green-700">
                    {{ number_format($detail->nominal, 0, ',', '.') }}
                </td>
            @else
                @php
                    $bulanMulaiSiswa = (int)($item->bulan_mulai ?? $detail->bulan_mulai ?? $bulanAjaran[0]['bulan']);
                    $mulaiIndex = 0;
                    foreach($bulanAjaran as $idx => $b) {
                        if ((int)$b['bulan'] == $bulanMulaiSiswa) {
                            $mulaiIndex = $idx;
                            break;
                        }
                    }
                @endphp
                @foreach($bulanAjaran as $idx => $bulan)
                    <td class="px-4 py-2 text-right font-medium">
                        @if($idx >= $mulaiIndex)
                            <span class="text-green-700 font-bold">{{ number_format($detail->nominal, 0, ',', '.') }}</span>
                        @else
                            <span class="text-gray-300">0</span>
                        @endif
                    </td>
                @endforeach
            @endif
        </tr>
    @empty
        <tr>
            <td colspan="{{ $isTabungan ? 4 : ($isDaftarUlang ? 5 : 3 + count($bulanAjaran)) }}" class="text-center text-gray-500 py-6">
                Tidak ada siswa pada kelas di tahun ajaran ini.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

        </div>

        <div class="mt-8 flex justify-center">
            {{ $historyKelasList->onEachSide(1)->links('pagination::tailwind') }}
        </div>
    </div>

<script>
    const searchInput = document.getElementById('searchSiswa');
    const hasilList = document.getElementById('hasilPencarian');
    const siswaIdInput = document.getElementById('siswaId');

    searchInput && searchInput.addEventListener('input', function () {
        const keyword = this.value.trim();

        if (keyword.length < 3) {
            hasilList.innerHTML = '';
            hasilList.classList.add('hidden');
            return;
        }

        fetch(`/api/cari-siswa?q=${encodeURIComponent(keyword)}`)
            .then(res => res.json())
            .then(data => {
                hasilList.innerHTML = '';
                if (data.length) {
                    hasilList.classList.remove('hidden');
                    data.forEach(siswa => {
                        const li = document.createElement('li');
                        li.className = 'px-3 py-2 hover:bg-green-100 cursor-pointer';
                        li.textContent = `${siswa.nama} (${siswa.kelas})`;
                        li.addEventListener('click', () => {
                            searchInput.value = siswa.nama;
                            siswaIdInput.value = siswa.id;
                            hasilList.classList.add('hidden');
                        });
                        hasilList.appendChild(li);
                    });
                } else {
                    hasilList.classList.add('hidden');
                }
            });
    });

    document.addEventListener('click', function (e) {
        if (!hasilList.contains(e.target) && e.target !== searchInput) {
            hasilList.classList.add('hidden');
        }
    });
</script>
</x-app-layout>
