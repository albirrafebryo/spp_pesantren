<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelas') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div id="success-alert" class="mb-4 flex items-center justify-between p-4 rounded bg-green-100 text-green-800 border border-green-300">
                <span>{{ session('success') }}</span>
                <button onclick="document.getElementById('success-alert').classList.add('hidden')" class="text-green-700 hover:text-green-900 font-bold text-lg leading-none">&times;</button>
                </div>
            @endif
            <div class="bg-white shadow-md rounded p-6">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-xl font-bold">Data Kelas</h1>
                    <button onclick="document.getElementById('modal').classList.remove('hidden')"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        + Tambah Data
                    </button>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">No</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Nama Kelas</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kelas as $index => $item)
                                <tr class="border-t">
                                    <td class="px-6 py-4">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4">{{ $item->nama_kelas }}</td>
                                    <td class="px-6 py-4 space-x-2">
                                       <button onclick="openEditModal('{{ $item->id }}', '{{ $item->nama_kelas }}')" 
                                            class="text-yellow-600 hover:underline">
                                            Edit
                                        </button>
                                        <button onclick="openDeleteModal('{{ $item->id }}', '{{ $item->nama_kelas }}')" 
                                            class="text-red-600 hover:underline">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-gray-500 px-6 py-4">Data belum tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $kelas->links() }}
                </div>
                
                <div id="modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md mx-auto">
                        <h2 class="text-xl font-bold mb-4">Tambah Kelas</h2>
                        <form action="{{ route('kelas.store') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="nama_kelas" class="block text-gray-700">Nama Kelas</label>
                                <input type="text" name="nama_kelas" id="nama_kelas"
                                       class="w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                                       required>
                            </div>
                            <div class="flex justify-end space-x-2">
                                <button type="button" onclick="document.getElementById('modal').classList.add('hidden')"
                                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>  

                <div id="modal-edit" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md mx-auto">
                        <h2 class="text-xl font-bold mb-4">Edit Kelas</h2>
                        <form id="edit-form" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-4">
                                <label for="edit_nama_kelas" class="block text-gray-700">Nama Kelas</label>
                                <input type="text" name="nama_kelas" id="edit_nama_kelas"
                                       class="w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                                       required>
                            </div>

                            <div class="flex justify-end space-x-2">
                                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                                    Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
                    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                        <h2 class="text-lg font-semibold mb-4">Konfirmasi Hapus</h2>
                        <p class="mb-4">Yakin ingin menghapus <strong>Kelas</strong> <span id="kelas-nama" class="font-bold text-red-600"></span>?</p>
                        <form id="delete-form" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="flex justify-end space-x-2">
                                <button type="button" onclick="closeDeleteModal()" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Hapus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(id, namaKelas) {
            const form = document.getElementById('edit-form');
            const inputNama = document.getElementById('edit_nama_kelas');

            form.action = `/kelas/${id}`; // Sesuaikan route
            inputNama.value = namaKelas;

            document.getElementById('modal-edit').classList.remove('hidden');
        }
    </script>

    <script>
        function openDeleteModal(id, namaKelas) {
            document.getElementById('kelas-nama').innerText = namaKelas;
            const form = document.getElementById('delete-form');
            form.action = `/kelas/${id}`;
            document.getElementById('delete-modal').classList.remove('hidden');
            document.getElementById('delete-modal').classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.add('hidden');
        }
    </script>
</x-app-layout>
