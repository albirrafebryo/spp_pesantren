<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-green-700 leading-tight">
            Daftar Jenis Pembayaran
        </h2>
    </x-slot>

    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- SWEETALERT SUCCESS --}}
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
    {{-- SWEETALERT ERROR --}}
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
            <div class="bg-white rounded-xl shadow-xl p-6 border border-green-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <h3 class="font-bold text-lg text-green-800">Daftar Jenis Pembayaran</h3>
                    <button onclick="document.getElementById('modal-tambah-jenis').classList.remove('hidden')"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl shadow transition font-semibold"
                    >
                        + Tambah Jenis
                    </button>
                </div>
                <div class="overflow-x-auto rounded-xl border border-green-100 bg-white/90">
                    <table class="min-w-full table-auto text-sm rounded-lg overflow-hidden">
                        <thead>
                            <tr class="bg-green-100 text-green-900">
                                <th class="px-4 py-3 font-semibold text-left">Nama</th>
                                <th class="px-4 py-3 font-semibold text-left">Tipe</th>
                                <th class="px-4 py-3 font-semibold text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-green-100">
                            @forelse ($jenispembayarans as $jenis)
                                <tr class="hover:bg-green-50 transition">
                                    <td class="px-4 py-3">{{ $jenis->nama ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($jenis->tipe === 1)
                                            <span class="inline-block px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">Bulanan</span>
                                        @elseif ($jenis->tipe === 0)
                                            <span class="inline-block px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-semibold">Bebas</span>
                                        @else
                                            <span class="inline-block px-2 py-1 rounded-full bg-gray-200 text-gray-600 text-xs font-medium">Tidak Diketahui</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 flex gap-2">
                                        <!-- Edit -->
                                        <button
                                            onclick="editJenis({{ $jenis->id }}, '{{ $jenis->nama }}', '{{ $jenis->tipe }}')"
                                            class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-green-900 border border-yellow-300 rounded-xl font-semibold shadow transition"
                                        >Edit</button>
                                        <!-- Hapus -->
                                        <form action="{{ route('jenispembayaran.destroy', $jenis->id) }}" method="POST" class="form-hapus inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="btn-hapus px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 border border-red-200 rounded-xl font-semibold shadow transition"
                                            >Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-gray-400 py-6">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- PAGINATION -->
                <div class="mt-4">
                    {{ $jenispembayarans->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Jenis Pembayaran -->
    <div id="modal-tambah-jenis" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative border border-green-100">
            <button type="button" class="absolute top-2 right-3 text-2xl text-gray-400 hover:text-red-500"
                onclick="document.getElementById('modal-tambah-jenis').classList.add('hidden')">&times;</button>
            <h2 class="text-lg font-bold mb-4 text-green-800">Tambah Jenis Pembayaran</h2>
            <form action="{{ route('jenispembayaran.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-1 font-medium text-green-800">Nama Jenis</label>
                    <input type="text" name="nama" class="border border-green-200 rounded-xl px-3 py-2 w-full focus:ring-2 focus:ring-green-300 outline-none bg-white" required>
                </div>
                <div>
                    <label class="block mb-1 font-medium text-green-800">Tipe</label>
                    <select name="tipe" class="border border-green-200 rounded-xl px-3 py-2 w-full focus:ring-2 focus:ring-green-300 outline-none bg-white" required>
                        <option value="">-- Pilih Tipe --</option>
                        <option value="1">Bulanan</option>
                        <option value="0">Bebas</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button"
                        onclick="document.getElementById('modal-tambah-jenis').classList.add('hidden')"
                        class="px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 rounded-xl font-semibold transition border border-yellow-300"
                    >Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition shadow"
                    >Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Jenis Pembayaran -->
    <div id="modal-edit-jenis" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative border border-green-100">
            <button type="button" class="absolute top-2 right-3 text-2xl text-gray-400 hover:text-red-500"
                onclick="document.getElementById('modal-edit-jenis').classList.add('hidden')">&times;</button>
            <h2 class="text-lg font-bold mb-4 text-green-800">Edit Jenis Pembayaran</h2>
            <form id="form-edit-jenis" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block mb-1 font-medium text-green-800">Nama Jenis</label>
                    <input type="text" id="edit-nama" name="nama" class="border border-green-200 rounded-xl px-3 py-2 w-full focus:ring-2 focus:ring-green-300 outline-none bg-white" required>
                </div>
                <div>
                    <label class="block mb-1 font-medium text-green-800">Tipe</label>
                    <select id="edit-tipe" name="tipe" class="border border-green-200 rounded-xl px-3 py-2 w-full focus:ring-2 focus:ring-green-300 outline-none bg-white" required>
                        <option value="">-- Pilih Tipe --</option>
                        <option value="1">Bulanan</option>
                        <option value="0">Bebas</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button"
                        onclick="document.getElementById('modal-edit-jenis').classList.add('hidden')"
                        class="px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 rounded-xl font-semibold transition border border-yellow-300"
                    >Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition shadow"
                    >Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Modal edit
    function editJenis(id, nama, tipe) {
        document.getElementById('modal-edit-jenis').classList.remove('hidden');
        document.getElementById('edit-nama').value = nama;
        document.getElementById('edit-tipe').value = tipe;
        document.getElementById('form-edit-jenis').action = `/jenispembayaran/${id}`;
    }

    // SweetAlert2 untuk hapus
    document.querySelectorAll('.btn-hapus').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            let form = this.closest('form');
            Swal.fire({
                title: 'Hapus jenis pembayaran?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-2xl' }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
    </script>
</x-app-layout>
