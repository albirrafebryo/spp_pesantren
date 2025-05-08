<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('SPP') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert Success --}}
            @if (session('success'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800 border border-green-300 flex justify-between items-center">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.classList.add('hidden')" class="text-xl font-bold leading-none">&times;</button>
                </div>
            @endif

            {{-- Card --}}
            <div class="bg-white shadow-md rounded p-6">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-xl font-bold">Data SPP</h1>
                    <button onclick="document.getElementById('modal-spp').classList.remove('hidden')"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        + Tambah SPP
                    </button>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">No</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Tahun Ajaran</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Nominal</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($spps as $index => $spp)
                                <tr class="border-t">
                                    <td class="px-6 py-4">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4">{{ $spp->tahun_ajaran }}</td>
                                    <td class="px-6 py-4">Rp {{ number_format($spp->nominal, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 space-x-2">
                                        <button class="text-yellow-600 hover:underline"
                                            onclick="openEditModalSpp('{{ $spp->id }}', '{{ $spp->tahun_ajaran }}', '{{ $spp->nominal }}')">
                                            Edit
                                        </button>
                                        <button type="button"
                                            onclick="openDeleteModalSpp('{{ $spp->id }}', '{{ $spp->tahun_ajaran }}')"
                                            class="text-red-600 hover:underline">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center px-6 py-4 text-gray-500">Belum ada data SPP.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $spps->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah SPP --}}
    <div id="modal-spp" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Tambah SPP</h2>
            <form action="{{ route('spp.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="tahun_ajaran" class="block text-gray-700">Tahun Ajaran</label>
                    <input type="text" name="tahun_ajaran" id="tahun_ajaran" placeholder="2024/2025"
                        class="w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200" required>
                </div>
                <div class="mb-4">
                    <label for="nominal" class="block text-gray-700">Nominal</label>
                    <input type="number" name="nominal" id="nominal"
                        class="w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200" required>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('modal-spp').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit SPP --}}
    <div id="modal-edit-spp" class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md mx-auto">
            <h2 class="text-xl font-bold mb-4">Edit SPP</h2>
            <form id="edit-form-spp" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-gray-700">Tahun Ajaran</label>
                    <input type="text" name="tahun_ajaran" id="edit_tahun_ajaran"
                        class="w-full mt-1 border border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Nominal</label>
                    <input type="number" name="nominal" id="edit_nominal"
                        class="w-full mt-1 border border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('modal-edit-spp').classList.add('hidden')"
                        class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                    <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Delete SPP --}}
    <div id="modal-delete-spp" class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md mx-auto">
            <h2 class="text-xl font-bold mb-4 text-red-600">Hapus SPP</h2>
            <p class="mb-4 text-gray-700">
                Apakah Anda yakin ingin menghapus data SPP tahun ajaran <strong id="delete_tahun_ajaran"></strong>?
            </p>
            <form id="delete-form-spp" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-end space-x-2">
                    <button type="button"
                        onclick="document.getElementById('modal-delete-spp').classList.add('hidden')"
                        class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                    <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModalSpp(id, tahunAjaran, nominal) {
            const form = document.getElementById('edit-form-spp');
            form.action = `/spp/${id}`; // Ganti '/admin/spp' menjadi '/spp'

            document.getElementById('edit_tahun_ajaran').value = tahunAjaran;
            document.getElementById('edit_nominal').value = nominal;

            document.getElementById('modal-edit-spp').classList.remove('hidden');
            document.getElementById('modal-edit-spp').classList.add('flex');
        }

        function openDeleteModalSpp(id, tahunAjaran) {
            const form = document.getElementById('delete-form-spp');
            form.action = `/spp/${id}`; // Ganti '/admin/spp' menjadi '/spp'

            document.getElementById('delete_tahun_ajaran').textContent = tahunAjaran;

            document.getElementById('modal-delete-spp').classList.remove('hidden');
            document.getElementById('modal-delete-spp').classList.add('flex');
        }
    </script>
</x-app-layout>
