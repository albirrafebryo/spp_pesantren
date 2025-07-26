<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-700 leading-tight">
            Kelola Pengguna
        </h2>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: @json(session('success')),
                    showConfirmButton: false,
                    timer: 1800,
                    timerProgressBar: true,
                    position: 'center',
                    customClass: { popup: 'rounded-2xl' }
                });
            });
        </script>
    @endif

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

    <div class="py-8 max-w-5xl mx-auto px-2">
        <div class="bg-white rounded-2xl shadow-xl p-5 md:p-8 border border-green-100">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-2">
                <form method="GET" class="flex flex-wrap items-center gap-2">
                    <label for="filter-role" class="block text-green-800 font-semibold">Filter Role: </label>
                    <select name="role" id="filter-role" class="border border-green-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-400 outline-none"
                            onchange="this.form.submit()">
                        <option value="">Semua Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ ($selectedRole == $role) ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </form>
                <button id="btn-tambah-user"
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-xl font-bold shadow active:scale-95 transition">
                    + Tambah Pengguna
                </button>
            </div>

            <h3 class="text-lg font-bold mb-4 text-green-700">Daftar Pengguna</h3>
            <div class="overflow-x-auto rounded-xl border border-green-100 shadow-sm bg-white">
                <table class="min-w-full divide-y divide-green-100 text-green-900 text-[15px]">
                    <thead class="bg-green-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">#</th>
                            <th class="px-4 py-3 text-left font-semibold">Nama</th>
                            <th class="px-4 py-3 text-left font-semibold">Email/Username</th>
                            <th class="px-4 py-3 text-left font-semibold">Role</th>
                            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-green-50">
                        @forelse ($users as $user)
                            <tr class="hover:bg-green-50/60 transition">
                                <td class="px-4 py-3 text-center">{{ (($users->currentPage()-1)*$users->perPage()) + $loop->iteration }}</td>
                                <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                                <td class="px-4 py-3">{{ $user->email }}</td>
                                <td class="px-4 py-3 capitalize">
                                    <span class="inline-block px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 font-semibold text-xs">
                                        {{ $user->getRoleNames()->first() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center flex flex-wrap gap-2 justify-center">
                                    <button type="button"
                                        class="bg-yellow-400 hover:bg-yellow-500 text-green-900 border border-yellow-300 px-4 py-1.5 rounded-lg font-semibold text-xs shadow btn-edit transition"
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        data-role="{{ $user->getRoleNames()->first() }}">
                                        Edit
                                    </button>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline form-hapus-user">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="bg-red-100 hover:bg-red-200 text-red-700 border border-red-300 px-4 py-1.5 rounded-lg font-semibold text-xs shadow btn-hapus-user transition">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-gray-400">Belum ada data pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- PAGINATION --}}
            <div class="mt-5 flex justify-between items-center flex-wrap gap-3">
                <div class="text-xs text-gray-500">
                    Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna
                </div>
                <div class="flex-1 flex justify-end">
                    {{ $users->withQueryString()->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah/Edit Pengguna --}}
    <div id="user-modal" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative border border-green-100">
            <button type="button" class="absolute top-2 right-3 text-2xl text-gray-400 hover:text-red-500"
                onclick="closeUserModal()">&times;</button>
            <h2 class="text-lg font-bold mb-4 text-green-700" id="modal-title">Tambah Pengguna</h2>
            <form method="POST" action="{{ route('users.store') }}" id="user-form" autocomplete="off">
                @csrf
                <input type="hidden" name="id" id="user-id" value="">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-green-800 mb-1">Nama</label>
                        <input type="text" name="name" id="user-name"
                            class="w-full border border-green-200 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-green-400 outline-none" required>
                        @error('name') <div class="text-red-500 text-sm">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-green-800 mb-1">Email atau Username</label>
                        <input type="text" name="email" id="user-email"
                            class="w-full border border-green-200 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-green-400 outline-none"
                            required placeholder="user@email.com atau username">
                        <span class="text-xs text-gray-400">Bisa diisi email <b>atau</b> username unik</span>
                        @error('email') <div class="text-red-500 text-sm">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-green-800 mb-1">Role</label>
                        <select name="role" id="user-role"
                            class="w-full border border-green-200 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-green-400 outline-none" required>
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-green-800 mb-1">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="user-password"
                                class="w-full border border-green-200 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-green-400 outline-none pr-10">
                            <button type="button"
                                class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 hover:text-green-700 focus:outline-none"
                                tabindex="-1"
                                onclick="togglePassword()">
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path id="eye-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm-9 0a9 9 0 0118 0 9 9 0 01-18 0z" />
                                    <path id="eye-closed" style="display:none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3l18 18M9.37 9.37A3 3 0 0115 12m0 0A3 3 0 019.37 9.37m1.41 1.41l7.07 7.07m-13.14 0l7.07-7.07" />
                                </svg>
                            </button>
                        </div>
                        <span class="text-xs text-gray-400" id="pass-note"></span>
                        @error('password') <div class="text-red-500 text-sm">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="mt-4 flex gap-2 justify-end">
                    <button type="button"
                        class="px-4 py-2 rounded-xl bg-yellow-100 hover:bg-yellow-200 text-yellow-800 font-semibold border border-yellow-300 transition"
                        onclick="closeUserModal()">Batal</button>
                    <button type="submit" id="form-submit-btn"
                        class="px-5 py-2 rounded-xl bg-green-600 text-white font-semibold hover:bg-green-700 transition shadow">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('btn-tambah-user').addEventListener('click', function() {
            showUserModal('Tambah Pengguna', '', '', '', '', 'Simpan', "{{ route('users.store') }}");
        });

        document.querySelectorAll('.btn-edit').forEach(function(btn) {
            btn.addEventListener('click', function() {
                showUserModal(
                    'Edit Pengguna',
                    this.dataset.id,
                    this.dataset.name,
                    this.dataset.email,
                    this.dataset.role,
                    'Update',
                    "/users/" + this.dataset.id
                );
            });
        });

        function showUserModal(title, id, name, email, role, submitText, action) {
            document.getElementById('modal-title').textContent = title;
            document.getElementById('user-id').value = id || '';
            document.getElementById('user-name').value = name || '';
            document.getElementById('user-email').value = email || '';
            document.getElementById('user-role').value = role || '';
            document.getElementById('user-password').value = '';
            document.getElementById('form-submit-btn').textContent = submitText;
            document.getElementById('user-form').action = action;
            // PATCH method
            let methodField = document.getElementById('user-form').querySelector('input[name="_method"]');
            if(submitText === 'Update') {
                if(!methodField) {
                    let method = document.createElement('input');
                    method.type = "hidden";
                    method.name = "_method";
                    method.value = "PATCH";
                    method.id = "method-field";
                    document.getElementById('user-form').appendChild(method);
                }
                document.getElementById('pass-note').textContent = "Biarkan kosong jika tidak ingin ganti password";
            } else {
                if(methodField) methodField.remove();
                document.getElementById('pass-note').textContent = "";
            }
            document.getElementById('user-modal').classList.remove('hidden');
            resetPasswordEye();
        }
        function closeUserModal() {
            document.getElementById('user-modal').classList.add('hidden');
        }
        function togglePassword() {
            const pwInput = document.getElementById('user-password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');
            if (pwInput.type === "password") {
                pwInput.type = "text";
                eyeOpen.style.display = "none";
                eyeClosed.style.display = "";
            } else {
                pwInput.type = "password";
                eyeOpen.style.display = "";
                eyeClosed.style.display = "none";
            }
        }
        function resetPasswordEye() {
            const pwInput = document.getElementById('user-password');
            pwInput.type = "password";
            document.getElementById('eye-open').style.display = "";
            document.getElementById('eye-closed').style.display = "none";
        }

        document.querySelectorAll('.btn-hapus-user').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                let form = this.closest('form');
                Swal.fire({
                    title: 'Yakin hapus pengguna ini?',
                    text: "Data user yang dihapus tidak bisa dikembalikan!",
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

        document.getElementById('user-form').addEventListener('submit', function(e) {
            let emailInput = document.getElementById('user-email').value.trim();
            if(emailInput === "") {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Kolom wajib diisi!',
                    text: 'Silakan isi Email atau Username!',
                    showConfirmButton: false,
                    timer: 1800,
                    customClass: { popup: 'rounded-2xl' }
                });
                return false;
            }
            if(emailInput.includes(" ")) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak boleh ada spasi!',
                    text: 'Email atau username tidak boleh mengandung spasi.',
                    showConfirmButton: false,
                    timer: 2000,
                    customClass: { popup: 'rounded-2xl' }
                });
                return false;
            }
        });
    </script>
</x-app-layout>
