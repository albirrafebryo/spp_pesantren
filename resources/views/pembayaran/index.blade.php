<x-app-layout>
    <div class="max-w-6xl mx-auto py-8 px-2 sm:px-4 lg:px-8">

        @if (session('success'))
            <div class="p-3 bg-green-50 text-green-800 border border-green-300 rounded-lg shadow mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('waiting_validation'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                setTimeout(() => {
                    Swal.fire({
                        icon: 'info',
                        title: 'Menunggu Validasi',
                        text: 'Pembayaran sedang menunggu validasi bendahara.',
                        confirmButtonText: 'OK'
                    });
                }, 200);
            </script>
        @endif

        {{-- Live Search Siswa khusus PETUGAS --}}
        @if (Auth::user()->hasRole('petugas') || Auth::user()->hasRole('admin'))
            <div class="mb-6 relative">
                <input type="text" id="livesearchInput" placeholder="Cari NISN / NIS / Nama"
                    class="border border-green-400 px-4 py-2 rounded-xl w-full shadow focus:ring-2 focus:ring-green-400 bg-white/80 transition"
                    autocomplete="off"/>
                <ul id="livesearchHasil"
                    class="absolute left-0 right-0 bg-white border border-green-200 mt-1 rounded-xl shadow-lg hidden max-h-48 overflow-auto z-20"></ul>
            </div>
        @endif

        @if ($siswa)
            {{-- KARTU DATA SISWA --}}
            <div class="bg-white/60 rounded-2xl shadow p-6 mb-6 max-w-5xl w-full mx-auto border border-green-100">
    <h2 class="text-2xl font-bold mb-6 text-center text-green-700 tracking-tight">
        Status Pembayaran - {{ $siswa->nama }}
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-6">
        {{-- Kolom Kiri: Data Siswa --}}
        <div class="space-y-2 text-base">
            @php
                $rows = [
                    ['label' => 'NIS', 'value' => $siswa->nis],
                    ['label' => 'Nama', 'value' => $siswa->nama],
                    ['label' => 'Kelas', 'value' => $siswa->kelas?->nama_kelas ?? '-'],
                    ['label' => 'Tahun Ajaran Berjalan', 'value' =>
                        $siswa->historyKelasTerbaru && $siswa->historyKelasTerbaru->tahunAjaran
                            ? $siswa->historyKelasTerbaru->tahunAjaran->nama
                            : '-'
                    ],
                    ['label' => 'No HP', 'value' => $siswa->no_hp]
                ];
            @endphp
            @foreach($rows as $row)
                <div class="flex items-center">
                    <div class="font-semibold text-green-700 w-56">{{ $row['label'] }}</div>
                    <div class="w-3 text-center">:</div>
                    <div class="text-gray-900">{{ $row['value'] }}</div>
                </div>
            @endforeach
        </div>
        {{-- Kolom Kanan: Total Pembayaran Cicilan Bulanan Sebelumnya --}}
        <div class="flex flex-col gap-2 w-full max-w-lg">
            <div class="font-semibold text-green-700 mb-1">
                Total Pembayaran Cicilan Bulanan Sebelumnya
            </div>
            <div class="grid grid-cols-2 gap-x-10 w-full">
                @foreach($totalCicilanPerJenis as $jenis => $total)
                    <div class="flex flex-col items-start">
                        <span class="uppercase text-gray-500 text-sm font-semibold tracking-wider mb-1">{{ $jenis }}</span>
                        <span class="text-xl font-bold text-gray-800">
                            @if($total > 0)
                                Rp {{ number_format($total, 0, ',', '.') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>   
            <div class="bg-white/70 rounded-2xl shadow-lg p-6 mb-8 max-w-full mx-auto border border-green-100">

@php
    $headers = [];
    $rowsAll = $siswa->tabelPembayaran ?? $tabelPembayaran ?? [];
    foreach($rowsAll as $row) {
        if (!empty($row['is_header'])) {
            $headers[] = $row;
        }
    }
@endphp

    <div class="mb-7">
        <ul class="flex border-b border-green-200 bg-white/30 backdrop-blur-xl rounded-t-2xl">
            <li class="-mb-px mr-2">
                <a href="#tab-bulanan" class="tab-pembayaran inline-block border-l border-t border-r rounded-t px-4 py-2 font-semibold text-green-700 bg-white/80 backdrop-blur-xl active" onclick="showTab('bulanan', event)">Bulanan</a>
            </li>
            <li class="mr-2">
                <a href="#tab-bebas" class="tab-pembayaran inline-block px-4 py-2 font-semibold text-green-700 bg-white/50 backdrop-blur-xl" onclick="showTab('bebas', event)">Bebas</a>
            </li>
            <li>
                <a href="#tab-tabungan" class="tab-pembayaran inline-block px-4 py-2 font-semibold text-green-700 bg-white/50 backdrop-blur-xl" onclick="showTab('tabungan', event)">Tabungan</a>
            </li>
        </ul>
    </div>
                {{-- ==== TAB CONTENT ==== --}}
                <div id="tab-bulanan-content" class="tab-content-pembayaran">
                    <div class="overflow-x-auto rounded-2xl bg-white/50 backdrop-blur-md p-2 border border-green-200">
                        <table class="min-w-[900px] w-full border-separate border-spacing-0 text-sm" id="tabelPembayaran">
                            @foreach($headers as $row)
                                @php
                                    $jenis = strtolower($row['jenis_pembayaran'] ?? '');
                                    $showHeader = false;
                                    foreach($bulanan as $r) {
                                        if ($r['tahun_ajaran'] == $row['tahun_ajaran']) $showHeader = true;
                                    }
                                @endphp
                                @if($showHeader)
                                    <thead class="bg-gradient-to-r from-gray-100 to-green-50 sticky top-0 z-10">
                                        <tr>
                                            <th class="border px-4 py-3 text-xl font-bold text-green-900 bg-green-50 rounded-tl-xl" style="min-width:150px;">
                                                {{ $row['tahun_ajaran'] }}
                                            </th>
                                            <th class="border px-4 py-3 font-semibold text-center bg-green-50">Jenis Pembayaran</th>
                                            @foreach($row['bulanAjaran'] as $b)
                                                <th class="px-3 py-2 border text-center font-semibold text-green-700 bg-green-50 whitespace-nowrap">
                                                    {{ $b['label'] }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bulanan as $r)
                                            @if($r['tahun_ajaran'] == $row['tahun_ajaran'])
                                                <tr>
                                                    <td class="border px-4 py-2 align-middle bg-gray-50"></td>
                                                    <td class="border px-4 py-2 font-semibold align-middle bg-gray-50">
                                                        {{ $r['jenis_pembayaran'] }}
                                                    </td>
                                                    @foreach($row['bulanAjaran'] as $b)
                                                        @php
                                                            $key = $b['bulan'] . '-' . $b['tahun'];
                                                            $data = $r['bulans'][$key] ?? null;
                                                            $warna = match($data['status'] ?? '') {
                                                                'lunas' => 'bg-green-500 text-white hover:bg-green-600',
                                                                'cicilan' => 'bg-yellow-300 text-black hover:bg-yellow-400',
                                                                'nunggak' => 'bg-red-500 text-white hover:bg-red-600',
                                                                'pending' => 'bg-orange-500 text-white hover:bg-orange-600',
                                                                default => 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                                                            };
                                                            $nominal = isset($data['nominal']) && $data['nominal'] ? number_format($data['nominal'], 0, ',', '.') : '-';
                                                            $isBtn = ($data && ($data['status'] ?? '') !== 'kosong');
                                                            $bukti = null;
                                                            $buktiUrl = '';
                                                            if(isset($data['detail_pembayaran_id']) && $data['detail_pembayaran_id']) {
                                                                $pembayaran = $siswa->pembayarans->first(function($p) use($b, $r) {
                                                                    return $p->detail_pembayaran_id == $r['detail_pembayaran_id'] && $p->bulan == $b['bulan'];
                                                                });
                                                                if($pembayaran && $pembayaran->buktiPembayarans->count()) {
                                                                    $bukti = $pembayaran->buktiPembayarans->last();
                                                                    $buktiUrl = asset('storage/' . $bukti->bukti);
                                                                }
                                                            }
                                                        @endphp
                                                        <td class="border text-center min-w-[80px] py-1 px-1 bg-white group relative">
    @php
        $isAdmin = Auth::user()->hasRole('admin');
        $isPetugas = Auth::user()->hasRole('petugas');
        $statusTagihan = $data['status'] ?? '';
        $disableBtn = false;

        // ADMIN: hanya disable jika status "belum"
        if ($isAdmin) {
            $disableBtn = ($statusTagihan === 'belum');
        }
        // PETUGAS: disable jika status "lunas"
        elseif ($isPetugas) {
            $disableBtn = ($statusTagihan === 'lunas');
        }

        $nominal = isset($data['nominal']) && $data['nominal'] ? number_format($data['nominal'], 0, ',', '.') : '-';
        $isBtn = ($data && ($data['status'] ?? '') !== 'kosong');
        $warna = match($data['status'] ?? '') {
            'lunas' => 'bg-green-500 text-white hover:bg-green-600',
            'cicilan' => 'bg-yellow-300 text-black hover:bg-yellow-400',
            'nunggak' => 'bg-red-500 text-white hover:bg-red-600',
            'pending' => 'bg-orange-500 text-white hover:bg-orange-600',
            default => 'bg-gray-200 text-gray-700 hover:bg-gray-300',
        };
        $bukti = null;
        $buktiUrl = '';
        if(isset($data['detail_pembayaran_id']) && $data['detail_pembayaran_id']) {
            $pembayaran = $siswa->pembayarans->first(function($p) use($b, $r) {
                return $p->detail_pembayaran_id == $r['detail_pembayaran_id'] && $p->bulan == $b['bulan'];
            });
            if($pembayaran && $pembayaran->buktiPembayarans->count()) {
                $bukti = $pembayaran->buktiPembayarans->last();
                $buktiUrl = asset('storage/' . $bukti->bukti);
            }
        }
    @endphp

    @if($isBtn)
        <button
            type="button"
            class="w-20 py-1 text-xs rounded-lg text-center relative {{ $warna }} btn-pilih-tagihan transition"
            data-tahun="{{ $r['tahun_ajaran'] }}"
            data-jenis="{{ $r['jenis_pembayaran'] }}"
            data-bulan="{{ $data['bulan_ke'] ?? '' }}"
            data-bulan-label="{{ $b['label'] ?? '' }}"
            data-nominal="{{ $data['nominal'] ?? 0 }}"
            data-status="{{ $data['status'] ?? '-' }}"
            data-dibayar="{{ $data['dibayar'] ?? 0 }}"
            data-detail-pembayaran-id="{{ $r['detail_pembayaran_id'] ?? '' }}"
            data-bukti-url="{{ $buktiUrl }}"
            data-bukti-id="{{ $bukti?->id ?? '' }}"
            @if($disableBtn)
                disabled
            @endif
        >
            {{ $nominal }}
            @if(($data['status'] ?? '') === 'pending')
                <span class="absolute top-0 right-1 text-[13px] font-bold text-white">
                    <i class="fa fa-exclamation-triangle"></i>
                </span>
            @endif
        </button>
        @if($bukti && $data['status']=='pending' && Auth::user()->hasRole('petugas'))
            <button
                type="button"
                onclick="showModalValidasi(
                    '{{ $buktiUrl }}',
                    '{{ $bukti->id }}',
                    '{{ $data['nominal'] }}',
                    '{{ $data['status'] }}',
                    '{{ pathinfo($bukti->bukti, PATHINFO_EXTENSION) }}'
                )"
                class="underline text-green-600 text-xs block mt-1"
            >
                Validasi
            </button>
        @endif
    @else
        <span>-</span>
    @endif
</td>

                                                    @endforeach
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                @endif
                            @endforeach
                        </table>
                    </div>
                </div>
   <div id="tab-bebas-content" class="tab-content-pembayaran hidden">
    <div class="overflow-x-auto rounded-2xl bg-white/50 backdrop-blur-md p-2 border border-green-200">
        <table class="min-w-[600px] w-full border-separate border-spacing-0 text-sm">
            <thead class="bg-gradient-to-r from-gray-100 to-green-50 sticky top-0 z-10">
                <tr>
                    <th class="border px-4 py-3 font-bold text-green-900 bg-green-50 rounded-tl-xl">Tahun Ajaran</th>
                    <th class="border px-4 py-3 font-semibold text-center bg-green-50">Jenis Pembayaran</th>
                    <th class="border px-4 py-3 font-semibold text-center bg-green-50">Nominal Tagihan</th>
                    <th class="border px-4 py-3 font-semibold text-center bg-green-50">Sudah Dibayar</th>
                    <th class="border px-4 py-3 font-semibold text-center bg-green-50">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bebas as $r)
                    <tr>
                        <td class="border px-4 py-2 align-middle bg-gray-50">{{ $r['tahun_ajaran'] }}</td>
                        <td class="border px-4 py-2 font-semibold align-middle bg-gray-50">{{ $r['jenis_pembayaran'] }}</td>
                        <td class="border px-4 py-2 text-right">
                            {{ ($r['nominal'] ?? 0) ? number_format($r['nominal'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="border px-4 py-2 text-right">
                            {{ isset($r['dibayar']) ? number_format($r['dibayar'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="border px-4 py-2 text-center">
                            @php
                                $warna = [
                                    'lunas' => 'bg-green-100 text-green-700',
                                    'cicilan' => 'bg-yellow-100 text-yellow-700',
                                    'belum' => 'bg-red-100 text-red-700',
                                ][$r['status'] ?? ''] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            @if($r['status'] !== 'lunas')
                                <button
                                    type="button"
                                    class="rounded px-3 py-1 font-bold text-xs {{ $warna }} btn-pilih-tagihan"
                                    data-tahun="{{ $r['tahun_ajaran'] }}"
                                    data-jenis="{{ $r['jenis_pembayaran'] }}"
                                    data-nominal="{{ $r['nominal'] }}"
                                    data-dibayar="{{ $r['dibayar'] }}"
                                    data-status="{{ $r['status'] }}"
                                    data-detail-pembayaran-id="{{ $r['detail_pembayaran_id'] }}"
                                    data-bulan=""
                                    data-bulan-label="-"
                                >
                                    {{ strtoupper($r['status'] ?? '-') }}
                                </button>
                            @else
                                <span class="rounded px-3 py-1 font-bold text-xs {{ $warna }}">{{ strtoupper($r['status'] ?? '-') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-400">Tidak ada data pembayaran bebas/daftar ulang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
                <div id="tab-tabungan-content" class="tab-content-pembayaran hidden">
                    <div class="overflow-x-auto rounded-2xl bg-white/50 backdrop-blur-md p-2 border border-green-200">
        <table class="min-w-[600px] w-full border-separate border-spacing-0 text-sm">
            <thead class="bg-gradient-to-r from-gray-100 to-green-50 sticky top-0 z-10">
                <tr>
                    <th class="border px-4 py-3 font-bold text-green-900 bg-green-50 rounded-tl-xl">Tahun Ajaran</th>
                    <th class="border px-4 py-3 font-semibold text-center bg-green-50">Jenis Tabungan</th>
                    <th class="border px-4 py-3 font-semibold text-center bg-green-50">Saldo Tabungan</th>
                </tr>
            </thead>
            <tbody>
    @forelse($tabungan as $r)
        <tr>
            <td class="border px-4 py-2 align-middle bg-gray-50">{{ $r['tahun_ajaran'] }}</td>
            <td class="border px-4 py-2 font-semibold align-middle bg-gray-50">{{ $r['jenis_pembayaran'] }}</td>
            <td class="border px-4 py-2 text-right font-bold text-green-800">
                {{ (isset($r['saldo']) && $r['saldo']) ? number_format($r['saldo'],0,',','.') : '-' }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="text-center py-4 text-gray-400">Tidak ada data tabungan.</td>
        </tr>
    @endforelse
</tbody>
</table>
{{-- FORM SETOR/TARIK TABUNGAN KHUSUS PETUGAS --}}

@if(Auth::user()->hasRole('petugas') && count($tabungan))
    <div class="mt-5 bg-white/80 rounded-2xl shadow p-6 border border-green-100 max-w-5xl mx-auto">
        <h3 class="text-xl font-bold text-green-700 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m0 0H8m4 0h4m-8 4a9 9 0 1118 0 9 9 0 01-18 0z" />
            </svg>
            Setor/Tarik Tabungan Siswa
        </h3>
        <form id="formTabungan" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            @csrf
            <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
            @if(count($tabungan) == 1)
                @php $row = $tabungan[0]; @endphp
                <input type="hidden" name="detail_pembayaran_id" value="{{ $row['detail_pembayaran_id'] }}">
                <div>
                    <label class="font-semibold block mb-1 text-green-700">Jenis Tabungan</label>
                    <div class="px-3 py-2 rounded-lg border bg-white text-green-700 font-bold shadow-sm">
                        {{ $row['jenis_pembayaran'] }} - {{ $row['tahun_ajaran'] }}
                    </div>
                </div>
            @else
                <div>
                    <label class="font-semibold block mb-1 text-green-700">Jenis Tabungan</label>
                    <select name="detail_pembayaran_id" class="border rounded-xl px-3 py-2 w-full focus:ring-2 focus:ring-green-400 bg-white shadow-sm font-semibold" required>
                        @foreach($tabungan as $row)
                            <option value="{{ $row['detail_pembayaran_id'] }}">
                                {{ $row['jenis_pembayaran'] }} - {{ $row['tahun_ajaran'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="font-semibold block mb-1 text-green-700">Aksi</label>
                <select name="jenis" class="border rounded-xl px-2 py-1 w-full focus:ring-2 focus:ring-green-400 bg-white shadow-sm font-semibold" required>
                    <option value="setor">Setor</option>
                    <option value="ambil">Tarik</option>
                </select>
            </div>
            <div>
                <label class="font-semibold block mb-1 text-green-700">Nominal</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-green-500 text-base font-bold">Rp</span>
                    <input
                        id="nominalTabungan"
                        name="nominal"
                        type="text"
                        inputmode="numeric"
                        pattern="[\d.]*"
                        class="border border-green-300 focus:ring-2 focus:ring-green-400 focus:border-green-500 rounded-xl pl-10 pr-3 py-2 w-full bg-white text-lg font-bold text-green-700 shadow transition placeholder:text-gray-400"
                        min="1"
                        placeholder="0"
                        required
                        autocomplete="off"
                    />
                </div>
            </div>
            <div class="flex items-end">
                <button type="button" id="btnKeranjangTabungan"
                    class="bg-gradient-to-tr from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-8 py-2 rounded-xl shadow-lg font-bold flex items-center gap-2 w-full md:w-auto transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m0 0H8m4 0h4m-8 4a9 9 0 1118 0 9 9 0 01-18 0z" />
                    </svg>
                    Masukkan Keranjang
                </button>
            </div>
        </form>
        <div class="text-xs text-gray-400 mt-2">* Masukkan angka tanpa desimal, contoh: 50.000</div>
    </div>
@endif  
        </table>
    </div>
</div>
</div>

            {{-- KERANJANG PEMBAYARAN --}}
            <div id="keranjang-wrap" class="mt-10 hidden">
    <div class="bg-white/70 shadow rounded-2xl p-6 border border-green-100">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xl font-bold text-green-800">Checkout Pembayaran</h3>
            <span id="keranjang-count" class="bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-green-100 text-sm mb-4" id="tabelKeranjang">
                <thead class="bg-green-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-green-700">Tahun Ajaran</th>
                        <th class="px-4 py-2 text-left font-semibold text-green-700">Jenis</th>
                        <th class="px-4 py-2 text-left font-semibold text-green-700">Bulan</th>
                        <th class="px-4 py-2 text-right font-semibold text-green-700">Tagihan</th>
                        <th class="px-4 py-2 text-center font-semibold text-green-700">Status</th>
                        <th class="px-4 py-2 text-center font-semibold text-green-700">Input Nominal</th>
                        <th class="px-4 py-2 text-center font-semibold text-green-700">Aksi</th>
                        <th class="px-4 py-2 text-center font-semibold text-green-700">Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-green-100"></tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"></td>
                        <td class="px-4 py-2 text-right font-semibold text-green-700">Total:</td>
                        <td colspan="3" class="px-4 py-2 text-left font-bold text-green-700" id="totalPembayaran">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @if(Auth::user()->hasRole('wali'))
            <div class="mb-4 p-4 bg-green-50 rounded-lg border border-green-100">
                <h4 class="font-semibold mb-2 text-green-700">Rekening Pembayaran</h4>
                <ul class="text-sm text-green-700">
                    <li><b>No. Rekening:</b> 1234-5678-9012-3456</li>
                    <li><b>Atas Nama:</b> Yayasan Pondok Pesantren</li>
                </ul>
            </div>
            <div class="mt-4">
                <label class="block font-semibold mb-2 text-green-700">
                    Upload Bukti Pembayaran <span class="text-red-600">*</span>
                </label>
                <input type="file" name="bukti_pembayaran[]" id="buktiPembayaranInput"
                    accept="image/*,application/pdf" required
                    class="border rounded-lg p-2 w-full file:bg-green-100 file:border-0 file:rounded-md file:px-4 file:py-2"
                    multiple />
                <div id="previewBukti" class="mt-2 flex flex-wrap gap-2"></div>
            </div>
        @endif

        {{-- Tombol aksi --}}
        <div class="flex justify-end gap-2 mt-6">
            @if(Auth::user()->hasRole('admin'))
    <button type="button" id="btnHapusPembayaran"
        class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 focus:ring-2 focus:ring-red-400 focus:outline-none transition px-6 py-2 rounded-xl shadow font-bold text-white text-base"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span>Hapus Pembayaran</span>
    </button>
            @elseif(!Auth::user()->hasRole('admin'))
                <button type="button" id="btnProsesPembayaran"
                    class="bg-green-600 hover:bg-green-700 transition text-white px-8 py-2 rounded-xl shadow font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h2l1 2h13a1 1 0 00.8-1.6l-2-4A1 1 0 0016 6H6.21l-.94-2H2" />
                    </svg>
                    Proses Pembayaran
                </button>
            @endif
        </div>
    </div>
</div>

        @else
            {{-- PETUGAS WAJIB CARI SISWA DULU --}}
            @if (Auth::user()->hasRole('petugas'))
                <p class="text-center text-gray-500 text-lg mt-12">Silakan cari dan pilih siswa terlebih dahulu.</p>
            @else
                <p class="text-center text-gray-500 text-lg mt-12">Tidak ada data siswa yang bisa ditampilkan.</p>
            @endif
        @endif

        <!-- MODAL KONFIRMASI PEMBAYARAN -->
        <div id="modalPembayaran" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 hidden">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-lg p-6 relative border border-green-100">
                <button id="closeModal" class="absolute top-3 right-3 text-gray-500 hover:text-red-600 text-2xl">&times;</button>
                <h3 class="text-xl font-bold mb-4 text-green-700">Konfirmasi Pembayaran</h3>
                <div id="modalBody"></div>
                <div class="flex justify-end gap-2 mt-6">
                    <button id="batalSimpan" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg">Batal</button>
                    <button type="button" id="btnSimpanPembayaran" class="px-6 py-2 bg-green-600 text-white rounded-lg font-bold">Simpan</button>
                </div>
            </div>
        </div>

        {{-- MODAL VALIDASI BUKTI (khusus petugas/bendahara) --}}
        @if(Auth::user()->hasRole('petugas'))
            <div id="modalValidasi" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-2xl p-6 shadow-lg w-full max-w-3xl max-h-[80vh] overflow-auto relative border border-green-100">
                    <button onclick="closeModalValidasi()" class="absolute top-2 right-3 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
                    <h3 class="text-lg font-bold mb-4 text-green-700">Validasi Bukti Pembayaran</h3>
                    <div id="preview-bukti-img" class="mb-3"></div>
                    <div class="mt-2 text-sm">
                        <b>Status:</b> <span id="modal-status"></span><br>
                        <b>Nominal:</b> <span id="modal-nominal"></span>
                    </div>
                    <form id="formValidasiBukti" method="POST" class="mt-6 flex gap-2">
                        @csrf
                        <input type="hidden" name="bukti_id" id="modal-bukti-id">
                        <button type="button" class="bg-green-600 text-white px-4 py-2 rounded-lg" onclick="validasiBukti('valid')">Valid</button>
                        <button type="button" class="bg-red-500 text-white px-4 py-2 rounded-lg" onclick="validasiBukti('invalid')">Tolak</button>
                    </form>
                </div>
            </div>
        @endif

        {{-- SCRIPT LIVESEARCH PETUGAS --}}
        <script>
        @if (Auth::user()->hasRole('petugas') || Auth::user()->hasRole('admin'))
            const input = document.getElementById('livesearchInput');
            const hasil = document.getElementById('livesearchHasil');
            let debounceTimeout = null;
            input.addEventListener('input', function() {
                const keyword = this.value.trim();
                clearTimeout(debounceTimeout);
                if (keyword.length < 2) {
                    hasil.classList.add('hidden');
                    hasil.innerHTML = '';
                    return;
                }
                debounceTimeout = setTimeout(() => {
                    fetch(`/api/livesearch-siswa?keyword=${encodeURIComponent(keyword)}`)
                        .then(res => res.json())
                        .then(data => {
                            hasil.innerHTML = '';
                            if (data.length) {
                                hasil.classList.remove('hidden');
                                data.forEach(siswa => {
                                    const li = document.createElement('li');
                                    li.className = 'px-4 py-2 hover:bg-green-100 cursor-pointer border-b transition';
                                    li.textContent = `${siswa.nama} [${siswa.nis} / ${siswa.nis}]`;
                                    li.addEventListener('click', () => {
                                        window.location = `{{ route('pembayaran.index') }}?keyword=${encodeURIComponent(siswa.nis)}`;
                                    });
                                    hasil.appendChild(li);
                                });
                            } else {
                                hasil.classList.remove('hidden');
                                const li = document.createElement('li');
                                li.className = 'px-4 py-2 text-gray-500';
                                li.textContent = 'Tidak ada hasil';
                                hasil.appendChild(li);
                            }
                        });
                }, 300);
            });
            document.addEventListener('click', function(e) {
                if (!hasil.contains(e.target) && e.target !== input) {
                    hasil.classList.add('hidden');
                }
            });
        @endif
        </script>

        <script>
document.addEventListener('DOMContentLoaded', function () {
    const nominalInput = document.getElementById('nominalTabungan');
    if (nominalInput) {
        nominalInput.addEventListener('input', function(e) {
            let value = this.value.replace(/[^\d]/g, '');
            if (value) {
                this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            } else {
                this.value = '';
            }
        });
        // Saat submit, hapus titik agar ke DB bersih
        nominalInput.form?.addEventListener('submit', function() {
            nominalInput.value = nominalInput.value.replace(/\./g,'');
        });
    }
});
</script>
        <script>
    window.isPetugas = @json(Auth::user()->hasRole('petugas'));
    window.isAdmin = @json(Auth::user()->hasRole('admin'));
</script>

        {{-- Script Keranjang, Modal, dan Validasi: --}}
        @include('pembayaran._keranjang_js')

        {{-- Preview bukti pembayaran untuk wali --}}
        @if(Auth::user()->hasRole('wali'))
        <script>
            document.getElementById('buktiPembayaranInput')?.addEventListener('change', function(e){
                let preview = document.getElementById('previewBukti');
                preview.innerHTML = '';
                if(this.files && this.files.length > 0) {
                    Array.from(this.files).forEach(file => {
                        if(file.type.startsWith('image/')){
                            let reader = new FileReader();
                            reader.onload = function(e){
                                preview.innerHTML += `<img src="${e.target.result}" class="max-h-32 rounded shadow mt-2"/>`;
                            }
                            reader.readAsDataURL(file);
                        } else if(file.type === 'application/pdf') {
                            preview.innerHTML += `<span class="text-sm text-green-700 block">File PDF: ${file.name}</span>`;
                        }
                    });
                }
            });
        </script>
        @endif

        {{-- ====== TAB JS & STYLING ====== --}}
        <script>
        function showTab(tab, ev) {
            document.querySelectorAll('.tab-content-pembayaran').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-pembayaran').forEach(el => el.classList.remove('active', 'border-l', 'border-t', 'border-r'));
            document.getElementById('tab-'+tab+'-content').classList.remove('hidden');
            if(ev) ev.preventDefault();
            // Set active tab
            if(ev) ev.target.classList.add('active', 'border-l', 'border-t', 'border-r');
        }
        window.addEventListener('DOMContentLoaded', () => { showTab('bulanan'); });
        </script>
        <style>
        .tab-content-pembayaran { display:none; }
        .tab-content-pembayaran:not(.hidden) { display:block; }
        .tab-pembayaran.active { background:#e8faf0; border-bottom: 1px solid #fff !important; }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Pembayaran Berhasil',
            text: '{{ session('success') }}',
            confirmButtonColor: '#15803D',
            confirmButtonText: 'OK'
        })
        @if(Auth::user()->hasRole('wali'))
            .then(() => {
                Swal.fire({
                    icon: 'info',
                    title: 'Menunggu Validasi',
                    text: 'Pembayaran Anda sedang menunggu validasi bendahara.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#15803D'
                });
            });
        @endif
    @endif

    @if (session('waiting_validation') && !session('success'))
        Swal.fire({
            icon: 'info',
            title: 'Menunggu Validasi',
            text: 'Pembayaran sedang menunggu validasi bendahara.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#15803D'
        });        
    @endif

    @if(Auth::user()->hasRole('admin'))
    document.getElementById('btnHapusPembayaran')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (keranjang.length === 0) {
            Swal.fire('Tidak ada pembayaran yang bisa dihapus!', '', 'info');
            return;
        }
        Swal.fire({
            title: 'Hapus Pembayaran?',
            text: 'Pembayaran yang dihapus akan hilang dari sistem dan status tagihan kembali seperti semula.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                fetch('{{ route("pembayaran.deletePembayaran") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        items: keranjang.map(item => ({
                            detail_pembayaran_id: item.detailId,
                            tahun: item.tahun,
                            bulan: item.bulan
                        }))
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Berhasil', 'Pembayaran berhasil dihapus!', 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Gagal', data.message || 'Gagal menghapus pembayaran!', 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Gagal', 'Terjadi error saat menghapus pembayaran.', 'error');
                });
            }
        });
    });
    @endif

});
</script>
    </div>
</x-app-layout>
