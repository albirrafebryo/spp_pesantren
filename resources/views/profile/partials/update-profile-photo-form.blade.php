<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Ubah Foto Profil
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            Unggah foto profil baru untuk akun Anda.
        </p>
    </header>

    <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <input type="file" name="profile_photo" accept="image/*" class="block w-full text-sm text-gray-700">
            @error('profile_photo')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Simpan
            </button>

            @if (session('status') === 'Foto profil berhasil diperbarui.')
                <p class="text-sm text-green-600">{{ session('status') }}</p>
            @endif
        </div>
    </form>
</section>
