<script>
// ======== LOGIC KERANJANG & MODAL UNTUK WALI & PETUGAS ========

// Inject data awal keranjang (dari backend)
@if(isset($keranjang))
    let keranjang = {!! json_encode($keranjang) !!};
@else
    let keranjang = [];
@endif

// Handler tombol pilih tagihan - PAKAI EVENT DELEGASI
document.addEventListener('click', function(e) {
  // 1) Hanya tombol “Pilih Tagihan”
  const btn = e.target.closest('.btn-pilih-tagihan');
  if (!btn) return;

  // 2) Ambil status asli (raw) dari data-status
  const statusRaw = (btn.dataset.status || '').toLowerCase();

  // 3) Cek: kalau sudah ada yang pending di keranjang, 
  //    maka hanya yang pending saja yang boleh masuk lagi
  const hasPending = keranjang.some(k => k.rawStatus === 'pending');
  if (hasPending && statusRaw !== 'pending') {
    return alert('Masih ada pembayaran “pending”. Validasi dulu sebelum memilih tagihan lain.');
  }

  // 4) Cegah duplikat entry yang sama
  const tahun = btn.dataset.tahun;
  const jenis = btn.dataset.jenis;
  const bulan = btn.dataset.bulan;
  if (keranjang.some(k => k.tahun === tahun && k.jenis === jenis && k.bulan == bulan)) {
    return;
  }

  // 5) Ambil data nominal, sudah dibayar, dan info lain
  const nominal  = parseInt(btn.dataset.nominal)   || 0;
  const dibayar  = parseInt(btn.dataset.dibayar)   || 0;
  const detailId = btn.dataset.detailPembayaranId;
  const buktiUrl = btn.dataset.buktiUrl || '';
  const buktiId  = btn.dataset.buktiId  || null;

  // Cek tabungan
  const isTabungan = (jenis || '').toLowerCase().includes('tabungan');

  // 6) Tentukan apakah cicilan atau pelunasan penuh
  let newStatus, cicilanValue;
  if (isTabungan) {
  newStatus = 'setor';
  cicilanValue = 0;
} else if (statusRaw === 'pending') {
  newStatus = dibayar < nominal ? 'cicilan' : 'lunas';
  cicilanValue = dibayar;
} else {
  newStatus = 'lunas';
  cicilanValue = nominal - dibayar; // otomatis isi sisa tagihan
}
  // 7) Push ke keranjang
  keranjang.push({
    tahun:      tahun,
    jenis:      jenis,
    bulan:      bulan,
    bulanLabel: btn.dataset.bulanLabel,
    nominal:    nominal,
    status:     newStatus,
    rawStatus:  statusRaw,
    cicilan:    cicilanValue,
    dibayar:    dibayar,
    sisa:       nominal - dibayar,
    detailId:   detailId,
    buktiUrl:   buktiUrl,
    buktiId:    buktiId,
    isTabungan: isTabungan // <--- TAMBAHAN
  });

  // 8) Render ulang tabel keranjang
  renderKeranjang();
});

function formatRupiah(angka) {
    if (!angka) angka = 0;
    angka = angka.toString().replace(/[^,\d]/g, "");
    let split = angka.split(",");
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
    if (ribuan) {
        let separator = sisa ? "." : "";
        rupiah += separator + ribuan.join(".");
    }
    rupiah = split[1] !== undefined ? rupiah + "," + split[1] : rupiah;
    return rupiah ? "Rp. " + rupiah : "";
}
function unformatRupiah(str) {
    return parseInt((str || '').replace(/[^0-9]/g, "")) || 0;
}

function renderKeranjang() {
  const wrap  = document.getElementById('keranjang-wrap');
  const tbody = document.querySelector('#tabelKeranjang tbody');
  if (!tbody || !wrap) return;
  tbody.innerHTML = '';
  let total = 0;

  if (keranjang.length === 0) {
    wrap.classList.add('hidden');
    return;
  }
  wrap.classList.remove('hidden');

  keranjang.forEach((item, idx) => {
    total += item.cicilan;

    // ======= Hanya dari wali (buktiId) yang harus readonly =======
    const isFromWali    = !!item.buktiId;
    const selectAttr    = isFromWali ? 'disabled' : '';
    const inputAttr     = isFromWali ? 'readonly' : '';
    const removeAttr    = isFromWali ? 'disabled' : '';
    const inputNominalAttr = (window.isAdmin || item.isTabungan || item.status === 'lunas')
      ? 'readonly style="background:#f2f2f2;cursor:not-allowed;"'
      : inputAttr;

    let statusCell;
    if (item.isTabungan) {
      if (window.isPetugas) {
        statusCell = `
          <select
            name="items[${idx}][keterangan]"
            class="w-32 h-10 border rounded-md px-2"
            disabled
          >
            <option value="setor" ${item.keterangan === 'setor' ? 'selected' : ''}>Setor</option>
            <option value="ambil" ${item.keterangan === 'ambil' ? 'selected' : ''}>Tarik</option>
          </select>
        `;
      } else {
        statusCell = `
          <span class="capitalize text-blue-700 font-semibold">Setor</span>
          <input type="hidden" name="items[${idx}][keterangan]" value="setor" />
        `;
      }
    } else if (item.buktiId && item.status === 'pending') {
      const label = item.cicilan < item.nominal ? 'Cicilan' : 'Lunas';
      statusCell = `<span class="capitalize">${label}</span>`;
    } else {
      const statusDisabled = (window.isAdmin) ? 'disabled' : selectAttr;
      statusCell = `
        <select
          name="items[${idx}][status]"
          onchange="ubahStatus(${idx}, this.value)"
          class="w-32 h-10 border rounded-md px-2"
          ${statusDisabled}
        >
          <option value="lunas" ${item.status === 'lunas' ? 'selected' : ''}>Lunas</option>
          <option value="cicilan" ${item.status === 'cicilan' ? 'selected' : ''}>Cicilan</option>
        </select>
      `;
    }

    // cicilan input
    const maxNom = item.sisa > 0 ? item.sisa : item.nominal;
    const val = (item.cicilan !== undefined && item.cicilan !== null) ? item.cicilan : item.nominal;

    // tombol hapus
    let removeBtn = '';
    if (window.isAdmin) {
      removeBtn = `<button 
        type="button" 
        onclick="hapusItem(${idx})" 
        class="text-red-600 font-bold hover:underline"
      >Hapus</button>`;
    } else {
      removeBtn = `<button 
        type="button" 
        onclick="hapusItem(${idx})" 
        ${removeAttr} 
        class="text-red-500 hover:underline"
      >Hapus</button>`;
    }
    // tombol validasi (hanya tampil untuk petugas & pending dari wali)
    let buktiCell = '-';
    if (item.buktiId) {
      buktiCell = `<button
                     type="button"
                     onclick="showModalValidasi(
                       '${item.buktiUrl}',
                       '${item.buktiId}',
                       '${item.cicilan}',
                       '${item.status}',
                       '${item.rawStatus}',           
                       '${item.buktiUrl.split('.').pop()}'
                     )"
                     class="underline text-blue-600 text-xs"
                   >
                     Validasi
                   </button>`;
    }

    tbody.innerHTML += `
      <tr>
        <td>
          <input type="hidden" name="items[${idx}][tahun_ajaran]" value="${item.tahun}" />
          ${item.tahun}
        </td>
        <td>
          <input type="hidden" name="items[${idx}][jenis]" value="${item.jenis}" />
          ${item.jenis}
        </td>
        <td>
          <input type="hidden" name="items[${idx}][bulan]" value="${item.bulan}" />
          ${item.bulanLabel}
        </td>
        <td>Rp ${item.nominal.toLocaleString()}</td>
        <td class="text-center">
          ${statusCell}
          <!-- Untuk non-tabungan, hidden input status sudah ada di select -->
          ${item.isTabungan ? '' : `<input type="hidden" name="items[${idx}][status]" value="${item.status}">`}
        </td>
        <td class="text-center">
          <input
            type="text"
            name="items[${idx}][cicilan]"
            value="${formatRupiah(val)}"
            data-idx="${idx}"
            data-max="${maxNom}"
            class="w-24 md:w-32 h-10 border rounded-md px-2 text-center cicilan-input"
            id="cicilan-input-${idx}"
            ${inputNominalAttr}
            autocomplete="off"
            autocorrect="off"
            autocapitalize="off"
            spellcheck="false"
          />
          <div id="cicilan-error-${idx}" class="text-red-600 text-xs mt-1" style="display:none"></div>
        </td>
        <td class="text-center">${removeBtn}</td>
        <td class="text-center">${buktiCell}</td>
      </tr>`;
  });

  // Update total
  const elTotal = document.getElementById('totalPembayaran');
  if (elTotal) elTotal.innerText = 'Rp ' + total.toLocaleString();

  // Bind ulang hanya untuk input yang **tidak** readonly
  document.querySelectorAll('.cicilan-input').forEach(inp => {
    if (inp.hasAttribute('readonly')) return;

    const idx   = +inp.dataset.idx;
    const max   = +inp.dataset.max;
    const errEl = document.getElementById(`cicilan-error-${idx}`);

    if (!inp.dataset.prev) {
      inp.dataset.prev = inp.value;
    }

    inp.addEventListener('input', function() {
      let numeric = unformatRupiah(this.value);

      if (keranjang[idx].isTabungan) {
        errEl.innerText = '';
        errEl.style.display = 'none';
        const formatted = formatRupiah(numeric);
        this.value = formatted;
        this.dataset.prev = formatted;
        keranjang[idx].cicilan = numeric;
        updateTotal();
      } else {
        if (numeric > max) {
          errEl.innerText = 'Nominal tidak boleh melebihi sisa tagihan!';
          errEl.style.display = 'block';
          this.value = this.dataset.prev;
        } else if (this.value === '' || isNaN(numeric)) {
          this.value = this.dataset.prev || formatRupiah(max);
          keranjang[idx].cicilan = unformatRupiah(this.value);
          errEl.innerText = '';
          errEl.style.display = 'none';
          updateTotal();
        } else {
          errEl.innerText = '';
          errEl.style.display = 'none';
          const formatted = formatRupiah(numeric);
          this.value = formatted;
          this.dataset.prev = formatted;
          keranjang[idx].cicilan = numeric;
          updateTotal();
        }
      }
    });
  });
  updateTotal();
}

window.hapusItem = function(idx) {
  keranjang.splice(idx, 1);
  renderKeranjang();
};

window.ubahStatus = function(idx, status) {
  let item = keranjang[idx];
  let sisa = item.nominal - item.dibayar;
  item.status = status;

  if (status === 'lunas') {
    item.cicilan = sisa;
  } else {
    item.cicilan = 0;
  }

  renderKeranjang();
};

// Modal pembayaran logic
const modal = document.getElementById('modalPembayaran');
const modalBody = document.getElementById('modalBody');
const btnProses = document.getElementById('btnProsesPembayaran');
const btnClose  = document.getElementById('closeModal');
const btnBatal  = document.getElementById('batalSimpan');
const btnSimpan = document.getElementById('btnSimpanPembayaran');

if (btnProses) {
  btnProses.onclick = function() {
    if (window.isAdmin) {
      Swal.fire('Akses Ditolak', 'Admin tidak diizinkan memproses pembayaran.', 'warning');
      return;
    }
    let valid = true;
    keranjang.forEach((item, idx) => {
      let cicil = item.status === 'lunas'
        ? item.sisa
        : (
          item.status === 'pending'
            ? item.cicilan
            : unformatRupiah(document.getElementById('cicilan-input-' + idx)?.value)
        );

      if (item.isTabungan) {
        if (cicil < 1) {
          valid = false;
          const el = document.getElementById('cicilan-error-' + idx);
          if (el) { el.innerText = "Isi nominal!"; el.style.display = 'block'; }
        }
      } else {
        if (item.status !== 'lunas' && item.status !== 'pending' && cicil < 1) {
          const el = document.getElementById('cicilan-error-' + idx);
          valid = false;
          if (el) { el.innerText = "Isi nominal!"; el.style.display = 'block'; }
        }
        if (item.status !== 'pending' && cicil > item.sisa) {
          valid = false;
          const el = document.getElementById('cicilan-error-' + idx);
          if (el) { el.innerText = "Nominal melebihi sisa!"; el.style.display = 'block'; }
        }
      }
    });
    if (!valid) {
      alert("Nominal cicilan wajib diisi dan tidak boleh melebihi sisa tagihan!");
      return;
    }
    if (!modalBody || !modal) return;
    let html = `<table class="w-full text-sm border">
      <thead><tr>
        <th class="border px-2 py-1">Tahun</th>
        <th class="border px-2 py-1">Jenis</th>
        <th class="border px-2 py-1">Bulan</th>
        <th class="border px-2 py-1">Nominal</th>
        <th class="border px-2 py-1">Status</th>
      </tr></thead>
      <tbody>`;
    let total = 0;
    keranjang.forEach(item => {
      let nilai = item.status === 'lunas' ? item.sisa : (item.cicilan || 0);
      total += nilai;
      html += `<tr>
        <td class="border px-2 py-1">${item.tahun}</td>
        <td class="border px-2 py-1">${item.jenis}</td>
        <td class="border px-2 py-1">${item.bulanLabel}</td>
        <td class="border px-2 py-1 text-right">Rp ${nilai.toLocaleString()}</td>
        <td class="border px-2 py-1 text-center capitalize">${item.status}</td>
      </tr>`;
    });
    html += `</tbody></table>
      <div class="text-right mt-4 font-bold text-green-700">
        Total Pembayaran: Rp ${total.toLocaleString()}
      </div>`;
    modalBody.innerHTML = html;
    modal.classList.remove('hidden');
  }
}

function closeModal() {
  if (modal) modal.classList.add('hidden');
}
if (btnClose) btnClose.onclick = closeModal;
if (btnBatal) btnBatal.onclick = closeModal;

if (btnSimpan) {
  btnSimpan.addEventListener('click', function(e) {
    e.preventDefault();

    @if(Auth::user()->hasRole('petugas'))
      @if(isset($siswa))
        const siswaId = {{ $siswa->id }};
        const bebasDaftarUlang = ['bebas', 'daftar ulang', 'daftar ulang a1', 'daftar ulang a2', 'daftar ulang a3'];

        let promises = keranjang
          .filter(item => {
            let jenis = (item.jenis || '').toLowerCase();
            if (item.isTabungan) return false; // skip tabungan
            return item.bulan || jenis.includes('daftar ulang') || jenis === 'bebas';
          })
          .map(item => {
            let jenis = (item.jenis || '').toLowerCase();
            if (jenis.includes('daftar ulang') || jenis === 'bebas') {
              return fetch('{{ route("pembayaran.storeDaftarUlang") }}', {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
                },
                body: JSON.stringify({
                  siswa_id: siswaId,
                  tahun_ajaran: item.tahun,
                  jenis: item.jenis,
                  cicilan: parseInt(item.cicilan)
                })
              });
            } else {
              // Proses bulanan
              return fetch('{{ route("pembayaran.updateStatus") }}', {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
                },
                body: JSON.stringify({
                  siswa_id: siswaId,
                  detail_pembayaran_id: item.detailId,
                  tahunAjaran: item.tahun,
                  bulan: item.bulan,
                  jumlah: parseInt(item.cicilan)
                })
              });
            }
          });

        Promise.all(promises.map(p =>
          p.then(res => {
            if (!res.ok) throw res;
            return res.json();
          })
        )).then((responses) => {
          // Ambil info siswa (pakai yang pertama, karena sama saja)
          const infoWA = responses.find(row => row && row.no_hp);
          const nomorWa = infoWA ? infoWA.no_hp : null;

          // Siapkan isi pembayaran, default strip semua
          let spp = '-', laundry = '-', daftarUlang = '-', tabungan = '-', total = 0;

          keranjang.forEach(item => {
            const nilai = parseInt(item.cicilan) || 0;
            total += nilai;
            if (/spp/i.test(item.jenis)) spp = "Rp. " + nilai.toLocaleString();
            if (/laundry/i.test(item.jenis)) laundry = "Rp. " + nilai.toLocaleString();
            if (/daftar.?ulang/i.test(item.jenis)) daftarUlang = "Rp. " + nilai.toLocaleString();
            if (/tabungan/i.test(item.jenis)) tabungan = "Rp. " + nilai.toLocaleString();
          });

          const namaSiswa = infoWA?.nama_siswa || keranjang[0]?.nama_siswa || "-";
          const nis = infoWA?.nis || keranjang[0]?.nis || "-";
          const kelasTerakhir = infoWA?.kelas_terakhir || "-";
          const tglBayar = new Date().toLocaleDateString('id-ID');
          const metode = "Cash";
          const noBukti = infoWA?.no_bukti || "-";
          const petugas = infoWA?.petugas || "-";
          const totalFormat = "Rp. " + total.toLocaleString();

          const pesan = `*#info sistem*\n*#jangan dibalas*\n
Assalamualaikum Wr. Wb

*Pondok Pesantren Tahfizul Quran Bilal bin Rabah Sukoharjo*
==============================
*TRANSAKSI PEMBAYARAN*

• *Nama*     : ${namaSiswa}
• *NIS*      : ${nis}
• *Kelas*    : ${kelasTerakhir}
• *Tgl Bayar*: ${tglBayar}
• *No Bukti* : ${noBukti}

*Rincian Pembayaran*
━━━━━━━━━━━━━━━━━━━━
SPP          : ${spp}
Laundry      : ${laundry}
Daftar Ulang : ${daftarUlang}
Tabungan     : ${tabungan}
━━━━━━━━━━━━━━━━━━━━
*Total*       : ${totalFormat}

Terima kasih, Bapak/Ibu telah menunaikan kewajibannya.

_Petugas_
${petugas}
`;

          Swal.fire({
            title: nomorWa ? 'Kirim info pembayaran ke WhatsApp?' : 'Pembayaran Berhasil',
            text: nomorWa ? 'Ingin mengirim info pembayaran ke wali melalui WhatsApp Web?' : 'Pembayaran berhasil disimpan.',
            icon: 'success',
            showCancelButton: !!nomorWa,
            confirmButtonText: nomorWa ? 'Ya, kirim!' : 'Tutup',
            cancelButtonText: nomorWa ? 'Tidak' : undefined,
          }).then((result) => {
            if (nomorWa && result.isConfirmed) {
  let waUrl = "";
  if (/Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
    waUrl = `https://api.whatsapp.com/send?phone=${nomorWa}&text=${encodeURIComponent(pesan)}`;
  } else {
    waUrl = `https://web.whatsapp.com/send?phone=${nomorWa}&text=${encodeURIComponent(pesan)}`;
  }
  window.open(waUrl, '_blank');
  setTimeout(() => window.location.reload(), 1500);
} else {
  window.location.reload();
}
          });
        }).catch(async err => {
          let msg = 'Terjadi kesalahan saat menyimpan pembayaran.';
          if (err.json) {
            let data = await err.json();
            if (data.message) msg = data.message;
          }
          alert(msg);
        });

      @else
        alert('Silakan cari dan pilih siswa terlebih dahulu sebelum memproses pembayaran.');
      @endif

    @else
      document.querySelectorAll('.cicilan-input').forEach(inp => {
        const idx = parseInt(inp.dataset.idx, 10);
        inp.value = keranjang[idx].cicilan;
      });
      const formCheckout = document.getElementById('formCheckout');
      if (formCheckout) formCheckout.submit();
    @endif
  });
}

// Validasi bukti modal logic (hanya untuk petugas)
@if(Auth::user()->hasRole('petugas'))
function showModalValidasi(imgUrl, buktiId, nominal, status, rawStatus, ext) {
  const modal = document.getElementById('modalValidasi');
  if (!modal) return;
  modal.classList.remove('hidden');

  const previewEl = document.getElementById('preview-bukti-img');
  if (!previewEl) return;
  previewEl.innerHTML = '';

  if (ext.toLowerCase() === 'pdf') {
    previewEl.innerHTML = `
      <a href="${imgUrl}" target="_blank" class="text-blue-600 underline">
        Lihat file PDF
      </a>`;
  } else {
    const img = document.createElement('img');
    img.src = imgUrl;
    img.alt = 'Bukti Pembayaran';
    img.className = 'rounded shadow max-w-full max-h-60 mb-4 cursor-zoom-in';
    img.addEventListener('click', e => {
      e.stopPropagation();
      const lightbox = document.createElement('div');
      Object.assign(lightbox.style, {
        position: 'fixed',
        top: 0, left: 0,
        width: '100%', height: '100%',
        backgroundColor: 'rgba(0,0,0,0.8)',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        cursor: 'pointer',
        zIndex: 10000
      });
      const bigImg = document.createElement('img');
      bigImg.src = imgUrl;
      bigImg.alt = 'Bukti Pembayaran (Zoom)';
      Object.assign(bigImg.style, {
        maxWidth: '90%', maxHeight: '90%'
      });
      lightbox.appendChild(bigImg);
      document.body.appendChild(lightbox);
      lightbox.addEventListener('click', () => document.body.removeChild(lightbox));
    });
    previewEl.appendChild(img);
  }

  const inputBukti = document.getElementById('modal-bukti-id');
  if (inputBukti) inputBukti.value = buktiId;

  const label = status.charAt(0).toUpperCase() + status.slice(1)
              + (rawStatus === 'pending' ? ' (pending)' : '');
  const elStatus = document.getElementById('modal-status');
  if (elStatus) elStatus.innerText = label;

  const elNominal = document.getElementById('modal-nominal');
  if (elNominal) elNominal.innerText = 'Rp ' + parseInt(nominal, 10).toLocaleString();
}

function closeModalValidasi() {
  const modal = document.getElementById('modalValidasi');
  if (modal) modal.classList.add('hidden');
}
function getCsrfToken(){
  return document.querySelector('meta[name="csrf-token"]')?.content;
}
function validasiBukti(status) {
  const buktiId = document.getElementById('modal-bukti-id')?.value;
  if (!buktiId) return;
  fetch(`/pembayaran/verifikasi-bukti/${buktiId}`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': getCsrfToken(),
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ status })
  })
  .then(resp => {
    return resp.json();
  })
  .then(data => {
    if (data.success) window.location.reload();
    else alert('Gagal validasi: '+ (data.message || 'unknown'));
  })
  .catch(err => {
    alert('Error, cek console log.');
  });
}
@endif

document.addEventListener('DOMContentLoaded', () => {
  @if(Auth::user()->hasRole('wali'))
    const fileInput = document.getElementById('buktiPembayaranInput');
    const preview   = document.getElementById('previewBukti');
    const dt        = new DataTransfer();

    function onFileChange(e) {
      fileInput.removeEventListener('change', onFileChange);

      Array.from(e.target.files).forEach(file => {
        const exists = Array.from(dt.files).some(
          f => f.name === file.name && f.size === file.size && f.type === file.type
        );
        if (!exists) dt.items.add(file);
      });

      fileInput.files = dt.files;

      preview.innerHTML = '';
      Array.from(dt.files).forEach(file => {
        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = evt => {
            preview.innerHTML += `
              <img
                src="${evt.target.result}"
                class="max-h-32 rounded shadow mt-2"
              />`;
          };
          reader.readAsDataURL(file);
        } else {
          preview.innerHTML += `
            <span class="text-sm text-gray-700 block">
              File: ${file.name}
            </span>`;
        }
      });

      fileInput.addEventListener('change', onFileChange);
    }
    // fileInput.addEventListener('change', onFileChange);
  @endif

  renderKeranjang();
  const prosesBtn = document.getElementById('btnProsesPembayaran');
  if (prosesBtn && window.isAdmin) {
    prosesBtn.disabled = true;
    prosesBtn.classList.add('cursor-not-allowed', 'opacity-60');
    prosesBtn.title = "Admin tidak dapat memproses pembayaran.";
  }
});

// ===== Tambahkan kode custom tabungan di bawah ini =====
document.addEventListener('DOMContentLoaded', function () {
  const btnTabungan = document.getElementById('btnKeranjangTabungan');
  if (btnTabungan) {
    btnTabungan.addEventListener('click', function(e) {
      e.preventDefault();
      const form = document.getElementById('formTabungan');
      if (!form) return;
      const jenisAksi = form.querySelector('[name="jenis"]')?.value; // setor/ambil
      const nominalRaw = form.querySelector('[name="nominal"]')?.value;
      const nominal = parseInt(nominalRaw?.replace(/\./g,'')) || 0;
      const detailPembayaranId = form.querySelector('[name="detail_pembayaran_id"]')?.value;

      if (!nominal || nominal < 1) {
        Swal.fire('Nominal tidak valid!', '', 'warning');
        return;
      }

      let tahunAjaran = '';
      let jenisTabungan = 'Tabungan';

      let jenisSelect = form.querySelector('select[name="detail_pembayaran_id"]');
      if (jenisSelect) {
        let opt = jenisSelect.options[jenisSelect.selectedIndex];
        if (opt) {
          let label = opt.textContent;
          let split = label.split('-');
          jenisTabungan = split[0]?.trim() || 'Tabungan';
          tahunAjaran = split[1]?.trim() || '';
        }
      } else {
        jenisTabungan = form.querySelector('[name="detail_pembayaran_id"]')?.dataset.jenis || 'Tabungan';
        tahunAjaran = form.querySelector('[name="detail_pembayaran_id"]')?.dataset.tahun || '';
      }

      if (keranjang.some(k =>
        k.tahun === tahunAjaran &&
        k.jenis === jenisTabungan &&
        k.isTabungan
      )) {
        Swal.fire('Tabungan sudah ada di keranjang!', '', 'info');
        return;
      }

      keranjang.push({
        tahun: tahunAjaran,
        jenis: jenisTabungan,
        bulan: '-',
        bulanLabel: '-',
        nominal: nominal,
        status: jenisAksi,
        rawStatus: jenisAksi,
        cicilan: nominal,
        dibayar: 0,
        sisa: 0,
        detailId: detailPembayaranId,
        buktiUrl: '',
        buktiId: null,
        isTabungan: true,
        keterangan: jenisAksi
      });

      renderKeranjang();
      form.reset();
    });
  }
});

function updateTotal() {
  let total = 0;
  keranjang.forEach((item, idx) => {
    const inp = document.getElementById('cicilan-input-' + idx);
    let nilai = item.cicilan || 0;
    if (inp && !inp.readOnly) {
      nilai = unformatRupiah(inp.value);
      keranjang[idx].cicilan = nilai;
    }
    total += nilai;
  });
  const elTotal = document.getElementById('totalPembayaran');
  if (elTotal) elTotal.innerText = 'Rp ' + total.toLocaleString();
}
</script>
