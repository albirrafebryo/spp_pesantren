<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-2 sm:px-4">
        <h2 class="text-2xl md:text-3xl font-bold mb-8 text-center tracking-tight text-gray-800 drop-shadow">
            Rekapitulasi Pembayaran Siswa
        </h2>

        {{-- Filter Card --}}
        <form method="GET" id="formFilter" class="mb-8 flex flex-col items-center w-full" autocomplete="off" onsubmit="return false">
            <div class="w-full md:w-auto flex flex-wrap gap-4 justify-center items-end 
                bg-white/60 backdrop-blur-lg shadow-lg rounded-2xl px-4 md:px-8 py-6 border border-white/30">
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Cari Siswa</label>
                    <div class="relative">
                        <input type="text" id="inputCariSiswa"
                            class="border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-xl px-4 py-2 w-48 sm:w-56 md:w-64 transition duration-150 shadow-sm outline-none bg-white/80 backdrop-blur"
                            placeholder="Nama/NIS" autocomplete="off">
                        <input type="hidden" name="siswa_id" id="inputSiswaId" value="{{ request('siswa_id') }}">
                        <div id="livesearchResult"
                            class="absolute bg-white/90 backdrop-blur-lg border rounded-xl shadow z-30 mt-1 w-full hidden"></div>
                    </div>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" id="selectTahunAjaran"
                        class="border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-xl px-4 py-2 w-32 sm:w-40 md:w-56 transition duration-150 shadow-sm outline-none bg-white/80 backdrop-blur">
                        <option value="">Pilih Tahun Ajaran</option>
                        @foreach($daftarTahunAjaran as $ta)
                            <option value="{{ $ta->id }}" {{ request('tahun_ajaran_id') == $ta->id ? 'selected' : '' }}>
                                {{ $ta->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Kelas</label>
                    <select name="kelas_id" id="selectKelas"
                        class="border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-xl px-4 py-2 w-24 sm:w-32 md:w-48 transition duration-150 shadow-sm outline-none bg-white/80 backdrop-blur">
                        <option value="">Pilih Kelas</option>
                        @foreach($daftarKelas as $kls)
                            <option value="{{ $kls->id }}" {{ request('kelas_id') == $kls->id ? 'selected' : '' }}>
                                {{ $kls->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Jenis Pembayaran</label>
                    <select name="jenis_pembayaran_id" id="selectJenisPembayaran"
                        class="border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-xl px-4 py-2 w-32 sm:w-44 md:w-60 transition duration-150 shadow-sm outline-none bg-white/80 backdrop-blur">
                        <option value="">Pilih Jenis Pembayaran</option>
                        @foreach($jenisPembayaranList as $jenis)
                            <option value="{{ $jenis->id }}" {{ request('jenis_pembayaran_id') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="button" id="btnResetFilter"
                        onclick="location.href='{{ route(request()->route()->getName()) }}';"
                        class="border border-gray-300 bg-white/90 hover:bg-red-50 hover:border-red-400 text-red-600 font-semibold rounded-xl px-5 py-2 shadow transition duration-150 backdrop-blur-lg">
                        Reset
                    </button>
                </div>
            </div>
        </form>

        {{-- Table Card --}}
        <div class="bg-white/70 backdrop-blur-lg shadow-2xl rounded-2xl p-2 sm:p-4 md:p-6 min-h-[200px] overflow-x-auto border border-white/20">
            <div id="wrapperTabelRekap">
                <div class="text-center text-gray-400 py-10" id="loadingTabel">
                    Silakan cari siswa atau isi filter...
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Detail (FLUID) --}}
    <div id="modalDetail"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[9999] flex items-center justify-center hidden transition-all">
        <div class="modal-content bg-white/80 backdrop-blur-lg rounded-2xl shadow-2xl w-[97vw] max-w-2xl p-2 sm:p-4 md:p-6 relative flex flex-col border border-white/30"
            style="max-height: 90vh;">
            <button id="closeModalDetail"
                class="absolute top-3 right-3 text-gray-500 hover:text-red-600 text-2xl transition">
                &times;
            </button>
            <h3 class="text-lg font-bold mb-3 text-gray-700">Detail Pembayaran Per Bulan</h3>
            <div id="modalSiswaInfo" class="mb-4"></div>
            <div id="modalDetailBody" class="overflow-y-auto" style="max-height: 70vh;"></div>
        </div>
    </div>

    <style>
        #wrapperTabelRekap::-webkit-scrollbar { height: 8px; }
        #wrapperTabelRekap { scrollbar-width: thin; }
        table { width: 100%; border-radius: 1rem; overflow: hidden; background: transparent; }
        th, td { background: transparent; }
        @media (max-width: 700px) {
            table th, table td {
                font-size: 0.88rem !important;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
        }

        /* MODAL FLUID CSS */
        #modalDetail .modal-content {
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%,-50%);
            transition: all 0.25s cubic-bezier(.4,2.2,.2,1);
            z-index: 9999;
        }
        body.sidebar-open #modalDetail .modal-content {
            margin-left: 288px !important;   /* Ubah 288px sesuai lebar sidebar kamu */
            left: 0 !important;
            transform: translateY(-50%) !important;
        }
        @media (max-width: 900px) {
            #modalDetail .modal-content,
            body.sidebar-open #modalDetail .modal-content {
                margin-left: 0 !important;
                left: 50% !important;
                transform: translate(-50%,-50%) !important;
            }
        }
    </style>

    <script>
        // === MODAL FLUID ===
        // Panggil ini di toggle sidebar kamu: setSidebarOpen(true/false)
        function setSidebarOpen(isOpen) {
            document.body.classList.toggle('sidebar-open', !!isOpen);
        }

        // === LIVESERACH SISWA & TABLE LOGIC (original kamu) ===
        let timer;
        const inputCariSiswa = document.getElementById('inputCariSiswa');
        const livesearchResult = document.getElementById('livesearchResult');
        const inputSiswaId = document.getElementById('inputSiswaId');

        document.addEventListener('DOMContentLoaded', function() {
            @if(request('siswa_id') && isset($rekapList) && $rekapList->count())
                inputCariSiswa.value = '{{ $rekapList->first()->siswa->nama ?? "" }}';
            @endif
        });

        inputCariSiswa.addEventListener('keyup', function(e) {
            clearTimeout(timer);
            const val = this.value.trim();
            if (val.length < 2) {
                livesearchResult.classList.add('hidden');
                return;
            }
            timer = setTimeout(() => {
                fetch(`/api/livesearch-siswa?keyword=${encodeURIComponent(val)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length) {
                            livesearchResult.innerHTML = data.map(s => 
                                `<div class="px-3 py-2 cursor-pointer hover:bg-blue-50 border-b"
                                    data-id="${s.id}" data-nama="${s.nama}">${s.nama} <span class="text-xs text-gray-400">(${s.nis})</span></div>`
                            ).join('');
                            livesearchResult.classList.remove('hidden');
                        } else {
                            livesearchResult.innerHTML = '<div class="px-3 py-2 text-gray-400">Tidak ditemukan.</div>';
                            livesearchResult.classList.remove('hidden');
                        }
                    });
            }, 250);
        });

        // Klik pada hasil live search
        livesearchResult.addEventListener('click', function(e) {
            if (e.target && e.target.dataset && e.target.dataset.id) {
                inputSiswaId.value = e.target.dataset.id;
                inputCariSiswa.value = e.target.dataset.nama;
                livesearchResult.classList.add('hidden');
                loadTabelRekap();
            }
        });

        // Klik di luar live search menutup hasil
        document.addEventListener('click', function(e){
            if (!livesearchResult.contains(e.target) && e.target !== inputCariSiswa) {
                livesearchResult.classList.add('hidden');
            }
        });

        // Jika input siswa dihapus manual, reset siswa_id (tapi filter tetap)
        inputCariSiswa.addEventListener('input', function() {
            if(this.value.trim().length < 2) {
                inputSiswaId.value = '';
            }
            loadTabelRekap();
        });

        // Filter berubah, langsung reload tabel (tidak reset siswa_id)
        document.getElementById('selectTahunAjaran').addEventListener('change', loadTabelRekap);
        document.getElementById('selectKelas').addEventListener('change', loadTabelRekap);
        document.getElementById('selectJenisPembayaran').addEventListener('change', loadTabelRekap);

        // Reset tombol
        document.getElementById('btnResetFilter').addEventListener('click', function() {
            document.getElementById('selectTahunAjaran').value = '';
            document.getElementById('selectKelas').value = '';
            document.getElementById('selectJenisPembayaran').value = '';
            inputCariSiswa.value = '';
            inputSiswaId.value = '';
            loadTabelRekap();
        });


        // --- LOAD TABEL REKAP ---
        function loadTabelRekap() {
            const siswaId = inputSiswaId.value;
            const tahunAjaranId = document.getElementById('selectTahunAjaran').value;
            const kelasId = document.getElementById('selectKelas').value;
            const jenisPembayaranId = document.getElementById('selectJenisPembayaran').value;

            // Jika semua kosong
            if (!siswaId && !tahunAjaranId && !kelasId && !jenisPembayaranId) {
                document.getElementById('wrapperTabelRekap').innerHTML = '<div class="text-center text-gray-400 py-10">Silakan cari siswa atau isi filter...</div>';
                return;
            }
            document.getElementById('wrapperTabelRekap').innerHTML = '<div class="text-center text-gray-400 py-10">Memuat data...</div>';

            fetch(`/api/rekap-siswa?siswa_id=${siswaId}&tahun_ajaran_id=${tahunAjaranId}&kelas_id=${kelasId}&jenis_pembayaran_id=${jenisPembayaranId}`)
                .then(res => res.json())
                .then(resp => {
                    if (!resp.rekap || !resp.rekap.length) {
                        document.getElementById('wrapperTabelRekap').innerHTML = '<div class="text-center text-gray-500 py-8">Tidak ada data rekap.</div>';
                        return;
                    }
                    let html = `
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm border mb-5 bg-white/70 backdrop-blur rounded-2xl shadow">
                                <thead class="bg-white/60 backdrop-blur">
                                    <tr>
                                        <th class="px-3 py-2 border">No</th>
                                        <th class="px-3 py-2 border">Nama Siswa</th>
                                        <th class="px-3 py-2 border">NIS</th>
                                        <th class="px-3 py-2 border">Kelas</th>
                                        <th class="px-3 py-2 border">Tahun Ajaran</th>
                                        <th class="px-3 py-2 border">Jenis Pembayaran</th>
                                        <th class="px-3 py-2 border">Tagihan</th>
                                        <th class="px-3 py-2 border">Dibayar</th>
                                        <th class="px-3 py-2 border">Sisa</th>
                                        <th class="px-3 py-2 border">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    let rekap = resp.rekap;
                    if (tahunAjaranId) rekap = rekap.sort((a, b) => (a.tahun_ajaran ?? '').localeCompare(b.tahun_ajaran ?? ''));
                    if (kelasId) rekap = rekap.sort((a, b) => (a.kelas ?? '').localeCompare(b.kelas ?? ''));
                    if (jenisPembayaranId) rekap = rekap.sort((a, b) => (a.jenis_pembayaran ?? '').localeCompare(b.jenis_pembayaran ?? ''));
                    rekap.forEach((item, idx) => {
                        html += `
                        <tr>
                            <td class="border px-3 py-2">${idx+1}</td>
                            <td class="border px-3 py-2">${item.nama}</td>
                            <td class="border px-3 py-2">${item.nis}</td>
                            <td class="border px-3 py-2">${item.kelas ?? '-'}</td>
                            <td class="border px-3 py-2">${item.tahun_ajaran ?? '-'}</td>
                            <td class="border px-3 py-2">${item.jenis_pembayaran ?? '-'}</td>
                            <td class="border px-3 py-2 text-right">
                                Rp ${parseInt(item.jumlah_tagihan || 0).toLocaleString()}
                            </td>
                            <td class="border px-3 py-2 text-right text-green-700">
                                ${
                                    item.tipe_pembayaran === "tabungan"
                                    ? "Rp " + parseInt(item.sudah_dibayar || 0).toLocaleString()
                                    : parseInt(item.sudah_dibayar || 0).toLocaleString()
                                }
                            </td>
                            <td class="border px-3 py-2 text-right text-red-700">
                                ${
                                    item.tipe_pembayaran === "tabungan"
                                    ? "Rp " + parseInt(item.sisa || 0).toLocaleString()
                                    : parseInt(item.sisa || 0).toLocaleString()
                                }
                            </td>
                            <td class="border px-3 py-2 text-center">
                                <button type="button"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs detail-btn"
                                    data-siswa-id="${item.siswa_id || ''}"
                                    data-ta="${item.tahun_ajaran_id || ''}"
                                    data-kelas="${item.kelas_id || ''}"
                                    data-jenis="${item.jenis_pembayaran_id || ''}">
                                    Detail
                                </button>
                            </td>
                        </tr>
                        `;
                    });
                    html += '</tbody></table></div>';
                    document.getElementById('wrapperTabelRekap').innerHTML = html;

                    // Rebind detail-btn click
                    document.querySelectorAll('.detail-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const siswaId = this.dataset.siswaId;
                            const tahunAjaranId = this.dataset.ta;
                            const kelasId = this.dataset.kelas;
                            const jenisPembayaranId = this.dataset.jenis;
                            fetch(`/api/pembayaran/rekap-detail?siswa_id=${siswaId}&tahun_ajaran_id=${tahunAjaranId}&kelas_id=${kelasId}&jenis_pembayaran_id=${jenisPembayaranId}`)
                                .then(res => res.json())
                                .then(data => {
                                    if (data && data.siswa) {
                                        let tahunAjaran = data.tahun_ajaran ?? '';
                                        document.getElementById('modalSiswaInfo').innerHTML = `
                                            <div class="text-[1.13rem] font-semibold mb-1">
                                                <span class="text-gray-500">Nama Siswa:</span> <span class="text-gray-800">${data.siswa.nama}</span>
                                            </div>
                                            <div class="text-[1.13rem] font-semibold mb-1">
                                                <span class="text-gray-500">Kelas:</span> <span class="text-gray-800">${data.siswa.kelas}</span>
                                            </div>
                                            <div class="text-[1.13rem] font-semibold mb-3">
                                                <span class="text-gray-500">Tahun Ajaran:</span> <span class="text-gray-800">${data.siswa.tahun_ajaran ?? '-'}</span>
                                            </div>
                                        `;
                                    } else {
                                        document.getElementById('modalSiswaInfo').innerHTML = '';
                                    }
                                    let html = '';
                                    let detail = data.detail ?? data;
                                    let tipe = data.tipe_pembayaran || 'bulanan';

                                    if (tipe === 'bulanan') {
                                        detail.forEach(jenis => {
                                            html += `<h4 class="font-bold mt-2 mb-1">${jenis.nama_jenis}</h4>
                                                <table class="w-full text-sm border mb-2 bg-white/60 backdrop-blur rounded-xl">
                                                <thead>
                                                    <tr>
                                                        <th class="border px-2 py-1">Bulan</th>
                                                        <th class="border px-2 py-1">Tanggal Bayar</th>
                                                        <th class="border px-2 py-1">Nominal</th>
                                                        <th class="border px-2 py-1">Status</th>
                                                        <th class="border px-2 py-1">Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>`;
                                            jenis.detail.forEach(row => {
                                                let tanggalBayarHtml = '-';
                                                if (row.cicilan_list && row.cicilan_list.length > 1) {
                                                    tanggalBayarHtml = '';
                                                    row.cicilan_list.forEach((cicil, idx) => {
                                                        tanggalBayarHtml += `${idx + 1}. ${cicil.tanggal_bayar ? cicil.tanggal_bayar : '-'}<br>`;
                                                    });
                                                } else if (row.tanggal_bayar && row.tanggal_bayar !== '-') {
                                                    tanggalBayarHtml = row.tanggal_bayar;
                                                }
                                                html += `<tr>
                                                    <td class="border px-2 py-1">${row.bulan}</td>
                                                    <td class="border px-2 py-1">${tanggalBayarHtml}</td>
                                                    <td class="border px-2 py-1 text-right">Rp ${parseInt(row.nominal || 0).toLocaleString()}</td>
                                                    <td class="border px-2 py-1 capitalize">${row.status ?? '-'}</td>
                                                    <td class="border px-2 py-1">${row.keterangan ?? '-'}</td>
                                                </tr>`;
                                            });
                                            html += `</tbody></table>`;
                                        });
                                    } else if (tipe === 'bebas') {
                                        detail.forEach(jenis => {
                                            html += `<h4 class="font-bold mt-2 mb-1">${jenis.nama_jenis}</h4>
                                                <table class="w-full text-sm border mb-2 bg-white/60 backdrop-blur rounded-xl">
                                                <thead>
                                                    <tr>
                                                        <th class="border px-2 py-1">Nominal Tagihan</th>
                                                        <th class="border px-2 py-1">Dibayar</th>
                                                        <th class="border px-2 py-1">Status</th>
                                                        <th class="border px-2 py-1">Cicilan/Transaksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    <td class="border px-2 py-1 text-right">Rp ${parseInt(jenis.nominal || 0).toLocaleString()}</td>
                                                    <td class="border px-2 py-1 text-right">Rp ${parseInt(jenis.dibayar || 0).toLocaleString()}</td>
                                                    <td class="border px-2 py-1 capitalize">${jenis.status ?? '-'}</td>
                                                    <td class="border px-2 py-1">`;
                                            if (jenis.cicilan_list && jenis.cicilan_list.length > 0) {
                                                jenis.cicilan_list.forEach((cicil, idx) => {
                                                    html += `${idx + 1}. Rp ${parseInt(cicil.nominal).toLocaleString()} (${cicil.status}, ${cicil.tanggal_bayar}) <br>`;
                                                });
                                            } else {
                                                html += 'Belum ada pembayaran';
                                            }
                                            html += `</td>
                                                </tr>
                                                </tbody></table>`;
                                        });
                                    } else if (tipe === 'tabungan') {
                                        detail.forEach(jenis => {
                                            html += `
                                                <table class="w-full text-sm border mb-2 bg-white/60 backdrop-blur rounded-xl">
                                                <thead>
                                                    <tr>
                                                        <th class="border px-2 py-1">Tanggal</th>
                                                        <th class="border px-2 py-1">Saldo Masuk</th>
                                                        <th class="border px-2 py-1">Saldo Keluar</th>
                                                        <th class="border px-2 py-1">Total Tabungan</th>
                                                        <th class="border px-2 py-1">Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>`;
                                            if (jenis.history && jenis.history.length > 0) {
                                                jenis.history.forEach(row => {
                                                    html += `<tr>
                                                        <td class="border px-2 py-1">${row.tanggal}</td>
                                                        <td class="border px-2 py-1 text-right ${row.masuk > 0 ? 'text-green-700' : ''}">
                                                            ${row.masuk ? 'Rp ' + parseInt(row.masuk).toLocaleString() : '-'}</td>
                                                        <td class="border px-2 py-1 text-right ${row.keluar > 0 ? 'text-red-700' : ''}">
                                                            ${row.keluar ? 'Rp ' + parseInt(row.keluar).toLocaleString() : '-'}</td>
                                                        <td class="border px-2 py-1 text-right font-semibold">Rp ${parseInt(row.saldo || 0).toLocaleString()}</td>
                                                        <td class="border px-2 py-1">${row.masuk > 0 ? 'Setor' : (row.keluar > 0 ? 'Ambil' : '-')}</td>
                                                    </tr>`;
                                                });
                                            } else {
                                                html += `<tr><td colspan="5" class="text-center text-gray-500 py-2">Belum ada transaksi tabungan.</td></tr>`;
                                            }
                                            html += `</tbody></table>`;
                                        });
                                    }
                                    else {
                                        html = '<div class="text-center py-6 text-gray-400">Tidak ada data detail.</div>';
                                    }

                                    document.getElementById('modalDetailBody').innerHTML = html;
                                    document.getElementById('modalDetail').classList.remove('hidden');
                                });
                        });
                    });
                });
        }

        document.getElementById('closeModalDetail').addEventListener('click', function() {
            document.getElementById('modalDetail').classList.add('hidden');
        });

        document.getElementById('modalDetail').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === "Escape") {
                document.getElementById('modalDetail').classList.add('hidden');
            }
        });

        // AUTO LOAD jika siswa/filter sudah ada dari parameter
        @if(request('siswa_id') || (request('tahun_ajaran_id') || request('kelas_id') || request('jenis_pembayaran_id')))
            document.addEventListener('DOMContentLoaded', function(){
                loadTabelRekap();
            });
        @endif
    </script>
</x-app-layout>
