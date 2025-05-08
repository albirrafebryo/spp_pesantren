<x-app-layout>
    <div class="max-w-2xl mx-auto py-8">
        <h2 class="text-2xl font-bold mb-4">Form Pembayaran</h2>

        <div class="bg-white p-6 rounded shadow space-y-3">
            <p><strong>Nama:</strong> {{ $siswa->nama }}</p>
            <p><strong>Tahun Ajaran:</strong> {{ $tahunAjaran }}</p>
            <p><strong>Bulan:</strong> {{ $bulan }}</p>
            <p><strong>Nominal SPP:</strong> Rp {{ number_format($siswa->spp->nominal, 0, ',', '.') }}</p>

            <form action="{{ route('pembayaran.updateStatus', [
                'nisn' => $siswa->nisn,
                'tahunAjaran' => str_replace('/', '-', $tahunAjaran),
                'bulan' => $bulan
            ]) }}" method="POST" class="mt-4 space-y-4" id="formPembayaran">
                @csrf

                <input type="hidden" name="keyword" value="{{ request('keyword', $siswa->nisn) }}">

                @php
                    $sudahLunas = $pembayaran && $pembayaran->jumlah_bayar >= $siswa->spp->nominal;
                @endphp

                @if (! $sudahLunas && $pembayaran && $pembayaran->status == 'cicilan' && $pembayaran->jumlah_bayar > 0)
                    <div class="p-3 bg-yellow-100 rounded border border-yellow-300 text-sm text-yellow-800 mb-2">
                        Cicilan terakhir pada: <strong>{{ $pembayaran->updated_at->format('d-m-Y H:i') }}</strong><br>
                        Total cicilan: <strong>Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</strong>
                    </div>
                @endif

                @if (! $sudahLunas)
                    <div>
                        <label class="block text-sm font-medium">Status Pembayaran</label>
                        <select name="status_pembayaran" id="statusSelect"
                            class="mt-1 w-full border px-3 py-2 rounded" required>
                            <option value="">-- Pilih --</option>
                            <option value="cicilan" {{ (old('status_pembayaran', $pembayaran->status ?? '') == 'cicilan') ? 'selected' : '' }}>Cicilan</option>
                            <option value="lunas" {{ (old('status_pembayaran', $pembayaran->status ?? '') == 'lunas') ? 'selected' : '' }}>Lunas</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Jumlah yang dibayarkan</label>
                        <input type="text" name="jumlah" id="jumlahInput" required
                            class="mt-1 w-full border px-3 py-2 rounded"
                            placeholder="Masukkan jumlah pembayaran"
                             value="{{ old('jumlah') ? number_format(old('jumlah'), 0, ',', '.') : '' }}">
                        <span id="errorMsg" class="text-red-600 text-sm mt-1 block"></span>
                    </div>

                    <button type="submit"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        id="submitButton">
                        Simpan Pembayaran
                    </button>
                @else
                    <div class="p-3 bg-green-100 rounded border border-green-300 text-sm text-green-800">
                        Pembayaran Lunas pada: <strong>{{ $pembayaran->updated_at->format('d-m-Y H:i') }}</strong><br>
                        Total dibayarkan: <strong>Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</strong>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Modal -->
    <div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 hidden">
        <div class="bg-white p-6 rounded shadow-lg w-96">
            <h3 class="text-xl font-semibold mb-4">Konfirmasi Pembayaran</h3>
            <p class="text-sm mb-4">Apakah yakin ingin menyimpan pembayaran?</p>
            <div class="flex justify-end">
                <button id="cancelButton" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Batal</button>
                <button id="confirmButton" class="bg-green-600 text-white px-4 py-2 rounded">Ya, Simpan</button>
            </div>
        </div>
    </div>

    <script>
        const statusSelect = document.getElementById('statusSelect');
        const jumlahInput = document.getElementById('jumlahInput');
        const submitButton = document.getElementById('submitButton');
        const errorMsg = document.getElementById('errorMsg');
        const form = document.getElementById('formPembayaran');

        const nominalSpp = {{ $siswa->spp->nominal }};
        const currentAmount = {{ $pembayaran->jumlah_bayar ?? 0 }};
        const totalAmount = nominalSpp - currentAmount;

        const formatRupiah = (number) => {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        };

        const unformatRupiah = (str) => {
            return parseInt(str.replace(/\./g, '')) || 0;
        };

        const validateInput = () => {
            const status = statusSelect.value;
            const jumlah = unformatRupiah(jumlahInput.value);

            if (status === 'cicilan' && jumlah > totalAmount) {
                submitButton.disabled = true;
                jumlahInput.classList.add('border-red-500');
                errorMsg.textContent = 'Jumlah cicilan melebihi sisa tagihan.';
            } else {
                submitButton.disabled = false;
                jumlahInput.classList.remove('border-red-500');
                errorMsg.textContent = '';
            }
        };

        if (statusSelect) {
            statusSelect.addEventListener('change', function () {
                if (this.value === 'lunas') {
                    const autoValue = totalAmount > 0 ? totalAmount : nominalSpp;
                    jumlahInput.value = formatRupiah(autoValue);
                    jumlahInput.readOnly = true;
                    submitButton.disabled = false;
                    errorMsg.textContent = '';
                } else if (this.value === 'cicilan') {
                    jumlahInput.value = '';
                    jumlahInput.readOnly = false;
                    submitButton.disabled = true;
                } else {
                    jumlahInput.value = '';
                    jumlahInput.readOnly = false;
                    submitButton.disabled = true;
                }
            });

            jumlahInput.addEventListener('input', function () {
                let value = this.value.replace(/\D/g, '');
                if (!isNaN(value)) {
                    this.value = formatRupiah(value);
                }
                validateInput();
            });
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault(); // Prevent form submission

            // Show the confirmation modal
            const modal = document.getElementById('confirmationModal');
            modal.classList.remove('hidden');
        });

        const cancelButton = document.getElementById('cancelButton');
        const confirmButton = document.getElementById('confirmButton');

        cancelButton.addEventListener('click', function () {
            const modal = document.getElementById('confirmationModal');
            modal.classList.add('hidden'); // Hide the modal if user cancels
        });

        confirmButton.addEventListener('click', function () {
            // Convert jumlahInput to unformatted number
            jumlahInput.value = unformatRupiah(jumlahInput.value);

            // Proceed to submit the form
            form.submit();
        });
    </script>
</x-app-layout>
