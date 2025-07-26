<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-700 leading-tight">
            Daftar Tahun Ajaran
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto px-2 sm:px-4 lg:px-8 py-4">
        {{-- Form Input Tahun Ajaran --}}
        <form action="{{ route('tahun_ajarans.store') }}" method="POST"
            class="mb-8 bg-white border border-green-100 rounded-2xl shadow p-4 md:p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <x-input-label for="nama" value="Tahun Ajaran" class="text-green-800"/>
                    <x-text-input
                        id="nama"
                        name="nama"
                        type="text"
                        class="mt-2 block w-full rounded-lg border-green-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-green-900 bg-white"
                        placeholder="2024/2025"
                        required
                        oninput="autoIsiPeriode()" />
                    <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="mulai" value="Mulai" class="text-green-800"/>
                    <x-text-input
                        id="mulai"
                        name="mulai"
                        type="date"
                        class="mt-2 block w-full rounded-lg border-green-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-green-900 bg-white" />
                    <x-input-error :messages="$errors->get('mulai')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="selesai" value="Selesai" class="text-green-800"/>
                    <x-text-input
                        id="selesai"
                        name="selesai"
                        type="date"
                        class="mt-2 block w-full rounded-lg border-green-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-green-900 bg-white" />
                    <x-input-error :messages="$errors->get('selesai')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="is_active" value="Status Aktif" class="text-green-800"/>
                    <label class="flex items-center gap-2 mt-3">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                            class="h-5 w-5 rounded border-green-400 text-green-600 focus:ring-green-500 transition">
                        <span class="text-sm text-green-700 font-medium">Aktif</span>
                    </label>
                </div>
                <div class="flex md:justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl shadow transition duration-150 focus:outline-none focus:ring-2 focus:ring-green-400 uppercase text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Tambah
                    </button>
                </div>
            </div>
        </form>

        {{-- Daftar Tahun Ajaran --}}
        <div class="bg-white border border-green-100 shadow-xl rounded-2xl overflow-x-auto">
            <table class="min-w-full divide-y divide-green-200 text-green-900">
                <thead class="bg-green-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Periode</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white/80 divide-y divide-green-100">
                    @forelse($tahunAjarans as $tahunAjaran)
                        <tr class="hover:bg-green-50/60 transition">
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-green-800">{{ $tahunAjaran->nama }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                @if($tahunAjaran->mulai && $tahunAjaran->selesai)
                                    <span class="font-medium text-green-700">{{ $tahunAjaran->mulai }}</span>
                                    <span class="mx-1 text-gray-400">s/d</span>
                                    <span class="font-medium text-green-700">{{ $tahunAjaran->selesai }}</span>
                                @else
                                    <span class="italic text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($tahunAjaran->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-200 text-green-800 shadow">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700 shadow">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm flex flex-col gap-2 md:flex-row md:gap-1">
                                <button type="button"
                                    onclick="openEditModal({{ $tahunAjaran->id }}, '{{ $tahunAjaran->nama }}', '{{ $tahunAjaran->mulai }}', '{{ $tahunAjaran->selesai }}', {{ $tahunAjaran->is_active ? 'true' : 'false' }})"
                                    class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-yellow-400 hover:bg-yellow-500 text-green-900 border border-yellow-300 shadow btn-edit transition">
                                    Edit
                                </button>
                                <form action="{{ route('tahun_ajarans.destroy', $tahunAjaran->id) }}" method="POST" class="form-hapus inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="btn-hapus inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-red-100 hover:bg-red-200 text-red-700 border border-red-300 shadow transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-400 text-sm">Belum ada data tahun ajaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Edit Tahun Ajaran --}}
    <div id="modal-edit-tahun" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative border border-green-100">
            <button type="button" class="absolute top-2 right-3 text-2xl text-gray-400 hover:text-red-500"
                onclick="document.getElementById('modal-edit-tahun').classList.add('hidden')">&times;</button>
            <h2 class="text-lg font-bold mb-4 text-green-700">Edit Tahun Ajaran</h2>
            <form id="form-edit-tahun" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block mb-1 font-medium text-green-800">Tahun Ajaran</label>
                    <input type="text" id="edit-nama" name="nama" class="border border-green-200 rounded px-3 py-2 w-full focus:ring-2 focus:ring-green-400 outline-none bg-white text-green-900" required>
                </div>
                <div>
                    <label class="block mb-1 font-medium text-green-800">Mulai</label>
                    <input type="date" id="edit-mulai" name="mulai" class="border border-green-200 rounded px-3 py-2 w-full focus:ring-2 focus:ring-green-400 outline-none bg-white text-green-900">
                </div>
                <div>
                    <label class="block mb-1 font-medium text-green-800">Selesai</label>
                    <input type="date" id="edit-selesai" name="selesai" class="border border-green-200 rounded px-3 py-2 w-full focus:ring-2 focus:ring-green-400 outline-none bg-white text-green-900">
                </div>
                <div>
                    <label class="block mb-1 font-medium text-green-800">Status Aktif</label>
                    <label class="flex items-center gap-2 mt-3">
                        <input type="checkbox" id="edit-is_active" name="is_active" value="1"
                            class="h-5 w-5 rounded border-green-400 text-green-600 focus:ring-green-500 transition">
                        <span class="text-sm text-green-700 font-medium">Aktif</span>
                    </label>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button"
                        onclick="document.getElementById('modal-edit-tahun').classList.add('hidden')"
                        class="px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 rounded-lg font-semibold border border-yellow-300 transition"
                    >Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition shadow"
                    >Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if(session('success'))
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
        @endif

        @if(session('error'))
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
        @endif

        function openEditModal(id, nama, mulai, selesai, is_active) {
            document.getElementById('modal-edit-tahun').classList.remove('hidden');
            document.getElementById('edit-nama').value = nama;
            document.getElementById('edit-mulai').value = mulai;
            document.getElementById('edit-selesai').value = selesai;
            document.getElementById('edit-is_active').checked = is_active ? true : false;
            document.getElementById('form-edit-tahun').action = `/tahun-ajaran/${id}`;
        }

        // Konfirmasi hapus
        document.querySelectorAll('.btn-hapus').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                let form = this.closest('form');
                Swal.fire({
                    title: 'Hapus tahun ajaran?',
                    text: "Data yang dihapus tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
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

        function autoIsiPeriode() {
            let val = document.getElementById('nama').value;
            let regex = /^(\d{4})\/(\d{4})$/;
            let match = val.match(regex);
            if (match) {
                let mulai = document.getElementById('mulai');
                let selesai = document.getElementById('selesai');
                if (!mulai.value || mulai.value.slice(0,4) != match[1]) {
                    mulai.value = match[1] + '-07-01';
                }
                if (!selesai.value || selesai.value.slice(0,4) != match[2]) {
                    selesai.value = match[2] + '-06-30';
                }
            }
        }

        const tahunAjaranList = @json($tahunAjarans->pluck('nama')->toArray());
        function setupTahunAjaranInput(inputId) {
            const input = document.getElementById(inputId);
            input.addEventListener('input', function(e) {
                let val = this.value.replace(/[^0-9\/]/g, '');
                if (val.length > 4 && val.charAt(4) !== '/') {
                    val = val.slice(0,4) + '/' + val.slice(4,9);
                }
                if (val.length > 9) val = val.slice(0,9);
                this.value = val;

                if (tahunAjaranList.includes(val)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplikat!',
                        text: 'Tahun ajaran ' + val + ' sudah ada.',
                        timer: 2000,
                        showConfirmButton: false,
                        position: 'center',
                        customClass: { popup: 'rounded-2xl' }
                    });
                    this.focus();
                }
            });
        }
        setupTahunAjaranInput('nama');
        setupTahunAjaranInput('edit-nama');
    </script>
</x-app-layout>
