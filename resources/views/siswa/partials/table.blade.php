<div class="overflow-x-auto rounded-2xl border border-green-200 shadow bg-white/80 backdrop-blur-md">
    <table class="min-w-full divide-y divide-green-100">
        <thead class="bg-green-100/70">
            <tr>
                <th class="px-4 py-3 text-center text-xs font-bold text-green-700 uppercase tracking-wider">No</th>
                {{-- <th class="px-4 py-3 text-center text-xs font-bold text-green-700 uppercase">NISN</th> --}}
                <th class="px-4 py-3 text-center text-xs font-bold text-green-700 uppercase tracking-wider">NIS</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-green-700 uppercase tracking-wider">Nama Siswa</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-green-700 uppercase tracking-wider">Kelas</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-green-700 uppercase tracking-wider">No Telp Wali</th>
                {{-- <th class="px-4 py-3 text-center text-xs font-bold text-green-700 uppercase tracking-wider">Alamat</th> --}}
                <th class="px-4 py-3 text-center text-xs font-bold text-yellow-700 uppercase tracking-wider">Tahun Masuk</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-green-700 uppercase tracking-wider">Tahun Aktif</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-green-50 bg-white/70">
            @forelse ($siswa as $index => $item)
                <tr class="hover:bg-green-50/80 transition-colors">
                    <td class="px-4 py-3 text-sm text-green-900 text-center">{{ $siswa->firstItem() + $index }}</td>
                    {{-- <td class="px-4 py-3 text-sm text-green-900 text-center">{{ $item->nisn }}</td> --}}
                    <td class="px-4 py-3 text-sm text-green-900 text-center">{{ $item->nis }}</td>
                    <td class="px-4 py-3 text-sm text-green-900 text-center">{{ $item->nama }}</td>
                    <td class="px-4 py-3 text-sm text-green-900 text-center">{{ $item->kelas->nama_kelas ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-green-800 text-center">{{ $item->no_hp }}</td>
                    {{-- <td class="px-4 py-3 text-sm text-green-800 text-center" title="{{ $item->alamat }}">{{ $item->alamat }}</td> --}}
                    <td class="px-4 py-3 text-sm font-semibold text-yellow-700 text-center">
                        {{ $item->tahunAjaranMasuk->nama ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-sm font-bold text-green-700 text-center">
                        {{
                            $item->historyKelasTerbaru && $item->historyKelasTerbaru->tahunAjaran
                            ? $item->historyKelasTerbaru->tahunAjaran->nama
                            : '-'
                        }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center px-4 py-6 text-gray-400 font-semibold bg-white/70">
                        Belum ada data siswa.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
