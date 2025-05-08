<x-app-layout>
    <div class="max-w-5xl mx-auto py-8">
        @if (session('success'))
            <div class="p-3 bg-green-100 text-green-800 border border-green-300 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('pembayaran.index') }}" class="mb-6">
            <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Cari NISN / NIS / Nama"
                   class="border px-4 py-2 rounded w-full" />
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded mt-2 hover:bg-blue-700">
                Cari
            </button>
        </form>

        @isset($siswa)
            <h2 class="text-2xl font-bold mb-6">Status Pembayaran - {{ $siswa->nama }}</h2>

            <table class="table-auto w-full border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border text-left">Tahun Ajaran</th>
                        @foreach (['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'] as $namaBulan)
                            <th class="px-2 py-1 border text-center text-sm">{{ $namaBulan }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tahunAjaranList as $tahun => $bulanList)
                        <tr>
                            <td class="px-4 py-2 border font-semibold">{{ $tahun }}</td>
                            @foreach ($bulanList as $bulan => $data)
                                @php
                                    $warna = match($data['statusVisual']) {
                                        'lunas' => 'bg-green-500 text-white hover:bg-green-600',
                                        'cicilan' => 'bg-yellow-400 text-black hover:bg-yellow-500',
                                        'nunggak' => 'bg-red-500 text-white hover:bg-red-600',
                                        default => 'bg-black text-white hover:bg-gray-800',
                                    };
                                @endphp
                                <td class="px-1 py-1 border text-center">
                                    <a
                                        href="{{ route('pembayaran.form', ['nisn' => $siswa->nisn, 'tahunAjaran' => str_replace('/', '-', $tahun), 'bulan' => $bulan, 'keyword' => request('keyword')]) }}"
                                        class="inline-block w-20 py-1 text-xs rounded text-center {{ $warna }}">
                                        {{ ucfirst($bulan) }}
                                        @if($data['statusVisual'] === 'cicilan')
                                            <span class="absolute top-0 right-1 text-[10px] font-bold">⚠</span>
                                        @endif
                                    </a>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-center text-gray-500 text-lg mt-12">Silakan cari dan pilih siswa terlebih dahulu.</p>
        @endisset
    </div>
</x-app-layout>
