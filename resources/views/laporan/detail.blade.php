<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Pembayaran - {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}
        </h2>
        <div class="mt-1 text-gray-500 text-sm">
            Jenis Pembayaran: <b>{{ $jenis_pembayaran }}</b> &nbsp; | &nbsp; Kelas: <b>{{ $kelas }}</b>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded shadow">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-3 py-2">No</th>
                            <th class="px-3 py-2">Nama Siswa</th>
                            <th class="px-3 py-2">NIS</th>
                            <th class="px-3 py-2">Kelas</th>
                            <th class="px-3 py-2">Jenis Pembayaran</th>
                            <th class="px-3 py-2">Nominal</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detailData as $idx => $item)
                            <tr>
                                <td class="px-3 py-2">{{ $idx+1 }}</td>
                                <td class="px-3 py-2">{{ $item->nama }}</td>
                                <td class="px-3 py-2">{{ $item->nis }}</td>
                                <td class="px-3 py-2">{{ $item->kelas }}</td>
                                <td class="px-3 py-2">{{ $item->jenis_pembayaran }}</td>
                                <td class="px-3 py-2">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-1 rounded text-xs
                                        {{ strtolower($item->status) == 'lunas' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-gray-500">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Export Excel --}}
            <div class="mt-4">
                <a href="#" class="inline-block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                    Export Excel
                </a>
            </div>
            <div class="mt-2">
                <a href="{{ url()->previous() }}" class="inline-block bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
