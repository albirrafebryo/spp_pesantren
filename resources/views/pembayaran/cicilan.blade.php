<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Cicilan Siswa: {{ $siswa->nama }} ({{ $siswa->nisn }})
        </h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if ($pembayaranCicilan->isEmpty())
            <div class="p-4 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded">
                Belum ada data cicilan untuk siswa ini.
            </div>
        @else
            <div class="bg-white rounded shadow p-6">
                <h3 class="text-lg font-bold mb-4">Detail Pembayaran Cicilan</h3>
                <table class="min-w-full table-auto border border-gray-300">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="px-4 py-2 border">Tahun Ajaran</th>
                            <th class="px-4 py-2 border">Bulan</th>
                            <th class="px-4 py-2 border">Status</th>
                            <th class="px-4 py-2 border">Tanggal Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pembayaranCicilan as $p)
                            <tr>
                                <td class="px-4 py-2 border">{{ $p->tahun_ajaran }}</td>
                                <td class="px-4 py-2 border capitalize">{{ $p->bulan }}</td>
                                <td class="px-4 py-2 border">
                                    <span class="px-2 py-1 rounded text-white text-sm
                                        {{ $p->status == 'lunas' ? 'bg-green-500' : ($p->status == 'cicilan' ? 'bg-yellow-500' : 'bg-red-500') }}">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 border">{{ $p->tanggal_bayar ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
