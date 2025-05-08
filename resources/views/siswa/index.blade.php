<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $role == 'admin' ? __('Data Siswa') : __('Pembayaran') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800 border border-green-300 flex justify-between items-center">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.classList.add('hidden')" class="text-xl font-bold leading-none">&times;</button>
                </div>
            @endif

            <div class="bg-white shadow-md rounded p-6">
                <div class="flex justify-between items-center mb-4">
                    {{-- Tombol Tambah hanya muncul untuk Admin --}}
                    @if($role == 'admin')
                        <button onclick="document.getElementById('modal').classList.remove('hidden')"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            + Tambah Siswa
                        </button>
                    @endif

                    {{-- Form Pencarian --}}
                    <form method="GET" action="{{ route('siswa.index') }}" class="flex items-center gap-2">
                        <select name="filter_by" id="filter-by" class="border rounded px-1 py-2 text-sm w-40">
                            <option value=""> -- Pilih Pencarian -- </option>
                            @if($role == 'admin')
                                <option value="nama" {{ request('filter_by') == 'nama' ? 'selected' : '' }}>Nama Siswa</option>
                                <option value="kelas" {{ request('filter_by') == 'kelas' ? 'selected' : '' }}>Kelas</option>
                                <option value="tahun_ajaran" {{ request('filter_by') == 'tahun_ajaran' ? 'selected' : '' }}>Tahun Ajaran</option>
                            @else
                                <option value="nisn" {{ request('filter_by') == 'nisn' ? 'selected' : '' }}>NISN</option>
                                <option value="nis" {{ request('filter_by') == 'nis' ? 'selected' : '' }}>NIS</option>
                                <option value="nama" {{ request('filter_by') == 'nama' ? 'selected' : '' }}>Nama Siswa</option>
                                <option value="kelas" {{ request('filter_by') == 'kelas' ? 'selected' : '' }}>Kelas</option>
                            @endif
                        </select>

                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Masukkan pencarian" class="border rounded px-3 py-1 w-48 hidden" id="search-input" />

                        <button type="submit" class="bg-blue-500 text-white px-4 py-1.5 rounded hover:bg-blue-600">Cari</button>

                        @if(request('search') || request('filter_by'))
                            <a href="{{ route('siswa.index') }}" class="text-sm text-red-500 underline ml-2">Reset</a>
                        @endif
                    </form>
                </div>

                {{-- Tabel Data Siswa --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th>No</th>
                                <th>NISN</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                @if($role == 'admin')
                                    <th>No Telp</th>
                                    <th>Alamat</th>
                                    <th>Tahun Ajaran</th>
                                @endif
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($siswa as $index => $item)
                                <tr class="border-t">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->nisn }}</td>
                                    <td>{{ $item->nis }}</td>
                                    <td>{{ $item->nama }}</td>
                                    <td>{{ $item->kelas->nama_kelas }}</td>
                                    @if($role == 'admin')
                                        <td>{{ $item->no_hp }}</td>
                                        <td>{{ $item->alamat }}</td>
                                        <td>{{ $item->spp->tahun_ajaran }}</td>
                                    @endif
                                    <td>
                                        @if($role == 'admin')
                                            <button onclick="openEditModal({{ $item }})" class="text-yellow-600">Edit</button>
                                            <button onclick="openDeleteModal('{{ $item->id }}', '{{ $item->nama }}')" class="text-red-600">Hapus</button>
                                        @else
                                            <a href="{{ route('petugas.pembayaran.show', $item->nisn) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Bayar</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $role == 'admin' ? 9 : 6 }}" class="text-center">Belum ada data siswa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $siswa->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
