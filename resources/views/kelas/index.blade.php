<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-900 leading-tight">
            {{ __('Kelas') }}
        </h2>
    </x-slot>
    <div class="max-w-4xl mx-auto px-2 sm:px-6 lg:px-8">

        {{-- SweetAlert2 Success --}}
        @if (session('success'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: @json(session('success')),
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        position: 'center',
                        customClass: { popup: 'rounded-2xl' }
                    });
                });
            </script>
        @endif

        <div class="bg-white shadow-xl rounded-2xl p-6 md:p-8">
            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                <h1 class="text-2xl font-bold text-green-900">Data Kelas</h1>
                <button 
                    onclick="document.getElementById('modal').classList.remove('hidden')" 
                    class="inline-flex items-center gap-2 px-5 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl shadow focus:outline-none focus:ring-2 focus:ring-green-400 transition text-sm"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Tambah Data
                </button>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto rounded-xl border border-green-200">
                <table class="min-w-full w-full divide-y divide-green-100 text-green-900">
                    <thead class="bg-green-50">
                        <tr>
                            <th class="px-4 md:px-6 py-3 md:py-4 text-left text-xs font-bold text-green-700 uppercase tracking-wider">No</th>
                            <th class="px-4 md:px-6 py-3 md:py-4 text-left text-xs font-bold text-green-700 uppercase tracking-wider">Nama Kelas</th>
                            <th class="px-4 md:px-6 py-3 md:py-4 text-left text-xs font-bold text-green-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-green-50">
                        @forelse ($kelas as $index => $item)
                            <tr class="hover:bg-green-50/60 transition">
                                <td class="px-4 md:px-6 py-3 text-sm">{{ $index + 1 }}</td>
                                <td class="px-4 md:px-6 py-3 text-sm font-medium">{{ $item->nama_kelas }}</td>
                                <td class="px-4 md:px-6 py-3 flex flex-wrap gap-2">
                                    <!-- Tombol Edit Kuning Solid -->
                                    <button 
                                        onclick="openEditModal('{{ $item->id }}', '{{ $item->nama_kelas }}')" 
                                        class="bg-yellow-400 hover:bg-yellow-500 text-green-900 border border-yellow-300 px-4 py-1.5 rounded-lg font-semibold text-xs shadow btn-edit transition"
                                    >
                                        Edit
                                    </button>
                                    <!-- Tombol Hapus Merah Solid -->
                                    <button 
                                        onclick="openDeleteModal('{{ $item->id }}', '{{ $item->nama_kelas }}')" 
                                        class="bg-red-100 hover:bg-red-200 text-red-700 border border-red-300 px-4 py-1.5 rounded-lg font-semibold text-xs shadow btn-hapus transition"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-gray-400 text-sm">Data belum tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $kelas->links() }}
            </div>
        </div>

        {{-- Modal Tambah --}}
        <div id="modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto p-8 relative animate-fade-in">
                <h2 class="text-xl font-bold mb-6 text-green-900">Tambah Kelas</h2>
                <form action="{{ route('kelas.store') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="nama_kelas" class="block text-green-800 font-medium mb-2">Nama Kelas</label>
                        <input type="text" name="nama_kelas" id="nama_kelas"
                            class="w-full border border-green-300 rounded-xl shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 py-2 px-3"
                            required autofocus>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modal').classList.add('hidden')"
                            class="px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 rounded-lg font-semibold border border-yellow-300 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition shadow">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>  

        {{-- Modal Edit --}}
        <div id="modal-edit" class="fixed inset-0 z-50  flex items-center justify-center bg-black bg-opacity-40 hidden">
            <div class="bg-white rounded-2xl z-50 shadow-xl w-full max-w-md mx-auto p-8 relative animate-fade-in">
                <h2 class="text-xl font-bold mb-6 text-green-900">Edit Kelas</h2>
                <form id="edit-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-6">
                        <label for="edit_nama_kelas" class="block text-green-800 font-medium mb-2">Nama Kelas</label>
                        <input type="text" name="nama_kelas" id="edit_nama_kelas"
                            class="w-full border border-green-300 rounded-xl shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 py-2 px-3"
                            required>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                            class="px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 rounded-lg font-semibold border border-yellow-300 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-green-900 rounded-lg font-semibold border border-yellow-300 transition shadow">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Form Hapus "kosong", dikirim via js swal --}}
        <form id="delete-form" method="POST" style="display:none">
            @csrf
            @method('DELETE')
        </form>
    </div>

    {{-- Animasi modal fade-in sederhana --}}
    <style>
        @keyframes fade-in {
            from { transform: translateY(24px) scale(.98); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }
        .animate-fade-in { animation: fade-in .15s ease; }
    </style>

    {{-- SweetAlert2 CDN (sekali saja) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function openEditModal(id, namaKelas) {
            const form = document.getElementById('edit-form');
            const inputNama = document.getElementById('edit_nama_kelas');
            form.action = `/kelas/${id}`;
            inputNama.value = namaKelas;
            document.getElementById('modal-edit').classList.remove('hidden');
        }
        // Hapus konfirmasi swal
        function openDeleteModal(id, namaKelas) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Hapus kelas: " + namaKelas,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-2xl' }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('delete-form');
                    form.action = `/kelas/${id}`;
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>
