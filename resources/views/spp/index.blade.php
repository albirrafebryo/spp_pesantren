<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-700 leading-tight">
            Data Pembayaran
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Notifikasi --}}
            @foreach (['success'=>'green', 'info'=>'yellow', 'error'=>'red'] as $msg => $color)
                @if(session($msg))
                    <div class="mb-4 p-4 rounded-xl bg-{{ $color }}-100/90 text-{{ $color }}-800 border border-{{ $color }}-300/80 flex justify-between items-center shadow">
                        <span>{{ session($msg) }}</span>
                        <button onclick="this.parentElement.classList.add('hidden')" class="text-xl font-bold leading-none">&times;</button>
                    </div>
                @endif
            @endforeach

            <div class="bg-white rounded-2xl shadow-xl border border-green-100 p-6">
                <h1 class="text-xl font-bold mb-4 text-green-800">Daftar Data Pembayaran</h1>
                <div class="overflow-x-auto rounded-xl border border-green-100 bg-white/90">
                    <table class="min-w-full divide-y divide-green-100 text-green-900 rounded-xl overflow-hidden text-sm">
                        <thead class="bg-green-50 text-green-900">
                            <tr>
                                <th class="px-4 py-3 text-center font-semibold">No</th>
                                <th class="px-4 py-3 text-center font-semibold">Tahun Ajaran</th>
                                <th class="px-4 py-3 text-center font-semibold">Jenis Pembayaran</th>
                                <th class="px-4 py-3 text-center font-semibold">Nominal</th>
                                <th class="px-4 py-3 text-center font-semibold">Tipe</th>
                                <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white/80">
                            @forelse($detailPembayarans as $i => $detail)
                            <tr class="hover:bg-green-50/40 transition">
                                <td class="px-4 py-3 text-center">{{ $detailPembayarans->firstItem() + $i }}</td>
                                <td class="px-4 py-3 text-center">{{ $detail->tahunAjaran->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ $detail->jenisPembayaran->nama ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold text-green-700 text-center">
                                    Rp {{ number_format($detail->nominal, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($detail->jenisPembayaran)
                                        @if ($detail->jenisPembayaran->tipe == 1)
                                            <span class="inline-block px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                                                Bulanan
                                            </span>
                                        @else
                                            <span class="inline-block px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-semibold">
                                                Bebas
                                            </span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('spp.daftar-siswa', $detail->id) }}"
                                       class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-xl font-semibold transition shadow text-xs sm:text-sm"
                                       title="Lihat Siswa">
                                       <span class="hidden sm:inline">📋</span>
                                       <span>Lihat Siswa</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center px-4 py-4 text-gray-500">Belum ada data.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $detailPembayarans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
