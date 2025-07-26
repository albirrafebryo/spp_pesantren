<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Tabungan;
use App\Models\Pembayaran;
use App\Models\DaftarUlang;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use App\Models\jenispembayaran;
use App\Models\DetailPembayaran;
use App\Models\PembayaranHistory;
use App\Models\DaftarUlangHistory;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    public function index(Request $request)
{
    $daftarTahunAjaran = TahunAjaran::all();
    $daftarKelas = Kelas::all();
    $jenisPembayaranList = Jenispembayaran::all();
    return view('laporan.index', compact('daftarTahunAjaran', 'daftarKelas', 'jenisPembayaranList'));
}

    public function livesearch(Request $request)
{
    $term = $request->input('q');
    $siswas = Siswa::where('nama', 'like', "%$term%")
        ->orWhere('nis', 'like', "%$term%")
        ->limit(10)
        ->get(['id', 'nama', 'nis']);
    return response()->json($siswas);
}

public function detail(Request $request)
{
    
    return $this->detailBulanan($request);
}

    public function bulanan(Request $request)
    {
        $jenis = $request->input('jenis');
        $kelas = $request->input('kelas');
        $tanggal_dari = $request->input('tanggal_dari');
        $tanggal_sampai = $request->input('tanggal_sampai');

        $jenisList = jenispembayaran::orderBy('nama')->get();
        $kelasList = Kelas::orderBy('nama_kelas')->get(); // gunakan nama_kelas jika field nama tidak ada

        $startDate = $tanggal_dari ? $tanggal_dari . ' 00:00:00' : null;
        $endDate   = $tanggal_sampai ? $tanggal_sampai . ' 23:59:59' : null;

        // Pembayaran Bulanan/Bebas
        $query = Pembayaran::with(['siswa.kelas', 'detailPembayaran.jenisPembayaran']);
        if ($jenis) {
            $detailIds = DetailPembayaran::where('jenis_pembayaran_id', $jenis)->pluck('id');
            $query->whereIn('detail_pembayaran_id', $detailIds);
        }
        if ($kelas) {
            $query->whereHas('siswa', function($q) use ($kelas) {
                $q->where('kelas_id', $kelas);
            });
        }
        if ($tanggal_dari && $tanggal_sampai) {
            if ($tanggal_dari === $tanggal_sampai) {
                $query->whereDate('created_at', $tanggal_dari);
            } else {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        } elseif ($tanggal_dari) {
            $query->where('created_at', '>=', $startDate);
        } elseif ($tanggal_sampai) {
            $query->where('created_at', '<=', $endDate);
        }
        $query->whereIn('status', ['lunas', 'cicilan']);
        $pembayarans = $query->get();

        // Daftar Ulang
        $queryDu = DaftarUlang::with(['siswa.kelas', 'detailPembayaran.jenisPembayaran']);
        if ($jenis) {
            $detailIds = DetailPembayaran::where('jenis_pembayaran_id', $jenis)->pluck('id');
            $queryDu->whereIn('detail_pembayaran_id', $detailIds);
        }
        if ($kelas) {
            $queryDu->whereHas('siswa', function($q) use ($kelas) {
                $q->where('kelas_id', $kelas);
            });
        }
        if ($tanggal_dari && $tanggal_sampai) {
            if ($tanggal_dari === $tanggal_sampai) {
                $queryDu->whereDate('created_at', $tanggal_dari);
            } else {
                $queryDu->whereBetween('created_at', [$startDate, $endDate]);
            }
        } elseif ($tanggal_dari) {
            $queryDu->where('created_at', '>=', $startDate);
        } elseif ($tanggal_sampai) {
            $queryDu->where('created_at', '<=', $endDate);
        }
        $queryDu->whereIn('status', ['lunas', 'cicilan']);
        $daftarUlang = $queryDu->get();

        // Gabungkan
        $rekap = collect();

        foreach ($pembayarans as $item) {
            $kelasNama = $item->siswa && $item->siswa->kelas
                ? ($item->siswa->kelas->nama ?? $item->siswa->kelas->nama_kelas ?? '-')
                : '-';
            $rekap->push((object)[
                'tipe' => 'pembayaran',
                'tanggal' => $item->created_at,
                'jenis_pembayaran' => $item->detailPembayaran->jenisPembayaran->nama ?? '-',
                'kelas' => $kelasNama,
                'jumlah_bayar' => $item->jumlah_bayar,
                'siswa' => $item->siswa,
                'detail' => $item,
            ]);
        }
        foreach ($daftarUlang as $item) {
            $kelasNama = $item->siswa && $item->siswa->kelas
                ? ($item->siswa->kelas->nama ?? $item->siswa->kelas->nama_kelas ?? '-')
                : '-';
            $rekap->push((object)[
                'tipe' => 'daftar_ulang',
                'tanggal' => $item->created_at,
                'jenis_pembayaran' => $item->detailPembayaran->jenisPembayaran->nama ?? '-',
                'kelas' => $kelasNama,
                'jumlah_bayar' => $item->jumlah_bayar,
                'siswa' => $item->siswa,
                'detail' => $item,
            ]);
        }

        // Group by: tanggal + jenis + kelas (ambil nama kelas real)
        $rekap_group = $rekap->groupBy(function($item) {
            $tgl = \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d');
            $jenis = $item->jenis_pembayaran;
            $kelas = $item->kelas ?: '-';
            return $tgl . '||' . $jenis . '||' . $kelas;
        })->map(function($group) {
            $total = $group->sum('jumlah_bayar');
            $siswas = $group->map(function($g) {
                $kelasNama = $g->siswa && $g->siswa->kelas
                    ? ($g->siswa->kelas->nama ?? $g->siswa->kelas->nama_kelas ?? '-')
                    : '-';
                return (object)[
                    'nama' => $g->siswa->nama ?? '-',
                    'nis'  => $g->siswa->nis ?? '-',
                    'kelas' => $kelasNama,
                    'nominal' => $g->jumlah_bayar
                ];
            });
            $first = $group->first();
            return (object)[
                'tanggal' => \Carbon\Carbon::parse($first->tanggal)->format('d-m-Y'),
                'jenis_pembayaran' => $first->jenis_pembayaran,
                'kelas' => $first->kelas ?: '-',
                'total' => $total,
                'siswas' => $siswas,
            ];
        })->sortByDesc('tanggal')->values();

        $total = $rekap_group->sum('total');

        return view('laporan.index', [
            'jenisList' => $jenisList,
            'kelasList' => $kelasList,
            'total' => $total,
            'pembayarans' => $rekap_group,
            'request' => $request
        ]);
    }

    public function detailBulanan(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $jenis_text = $request->input('jenis_text');
        $kelas_text = $request->input('kelas_text');

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal)) {
            $tanggalYmd = \Carbon\Carbon::createFromFormat('d-m-Y', $tanggal)->format('Y-m-d');
        } else {
            $tanggalYmd = $tanggal;
        }

        // Query pembayaran
        $query = Pembayaran::with(['siswa.kelas', 'detailPembayaran.jenisPembayaran'])
            ->whereDate('created_at', $tanggalYmd)
            ->whereHas('detailPembayaran.jenisPembayaran', function($q) use ($jenis_text) {
                $q->where('nama', 'LIKE', '%' . $jenis_text . '%');
            });
        if ($kelas_text && $kelas_text != '-' && !empty($kelas_text)) {
            $query->whereHas('siswa.kelas', function($q) use ($kelas_text) {
                $q->where(function($w) use ($kelas_text) {
                    $w->where('nama', $kelas_text)
                      ->orWhere('nama_kelas', $kelas_text);
                });
            });
        }
        $pembayarans = $query->get();

        // Query daftar ulang
        $queryDu = DaftarUlang::with(['siswa.kelas', 'detailPembayaran.jenisPembayaran'])
            ->whereDate('created_at', $tanggalYmd)
            ->whereHas('detailPembayaran.jenisPembayaran', function($q) use ($jenis_text) {
                $q->where('nama', 'LIKE', '%' . $jenis_text . '%');
            });
        if ($kelas_text && $kelas_text != '-' && !empty($kelas_text)) {
            $queryDu->whereHas('siswa.kelas', function($q) use ($kelas_text) {
                $q->where(function($w) use ($kelas_text) {
                    $w->where('nama', $kelas_text)
                      ->orWhere('nama_kelas', $kelas_text);
                });
            });
        }
        $daftarUlang = $queryDu->get();

        $detailData = collect();
        foreach ($pembayarans as $item) {
            $kelasNama = $item->siswa && $item->siswa->kelas
                ? ($item->siswa->kelas->nama ?? $item->siswa->kelas->nama_kelas ?? '-')
                : '-';
            $detailData->push((object)[
                'nama' => $item->siswa->nama ?? '-',
                'nis' => $item->siswa->nis ?? '-',
                'kelas' => $kelasNama,
                'jenis_pembayaran' => $item->detailPembayaran->jenisPembayaran->nama ?? '-',
                'nominal' => $item->jumlah_bayar,
                'status' => $item->status,
            ]);
        }
        foreach ($daftarUlang as $item) {
            $kelasNama = $item->siswa && $item->siswa->kelas
                ? ($item->siswa->kelas->nama ?? $item->siswa->kelas->nama_kelas ?? '-')
                : '-';
            $detailData->push((object)[
                'nama' => $item->siswa->nama ?? '-',
                'nis' => $item->siswa->nis ?? '-',
                'kelas' => $kelasNama,
                'jenis_pembayaran' => $item->detailPembayaran->jenisPembayaran->nama ?? '-',
                'nominal' => $item->jumlah_bayar,
                'status' => $item->status,
            ]);
        }
        $detailData = $detailData->sortBy('nama')->values();

        return view('laporan.detail', [
            'detailData' => $detailData,
            'tanggal' => $tanggal,
            'jenis_pembayaran' => $jenis_text,
            'kelas' => $kelas_text,
        ]);
    }

    public function exportBulanan(Request $request)
    {
        return 'Export Excel Coming Soon';
    }

      /**
     * Laporan per kelas (rekap seluruh siswa beserta tunggakan dari awal masuk)
     * @param Request $request (kelas_id, jenis_pembayaran_id)
     * @return \Illuminate\View\View
     */   
    public function laporanPerKelas(Request $request)
    {
        $kelasId = $request->input('kelas_id');
        $jenisId = $request->input('jenis_pembayaran_id');
        $kelas = Kelas::find($kelasId);

        // Ambil semua siswa yang SEKARANG berada di kelas ini
        $siswas = Siswa::where('kelas_id', $kelasId)->get();

        // Rekap tiap siswa (tagihan sejak awal masuk)
        $rekap = $siswas->map(function ($siswa) use ($jenisId) {
            // Ambil semua detail pembayaran sejak tahun masuk sampai sekarang
            $detailPembayarans = DetailPembayaran::where('jenis_pembayaran_id', $jenisId)
                ->where('angkatan_mulai', '<=', $siswa->tahun_masuk)
                ->orderBy('tahun_ajaran_id')
                ->get();

            $tagihanArr = [];
            foreach ($detailPembayarans as $dp) {
                $isBulanan = $dp->jenisPembayaran && $dp->jenisPembayaran->tipe == 1;
                $jumlah_tagihan = $isBulanan ? ($dp->nominal ?? 0) * 12 : ($dp->nominal ?? 0);

                $sudah_dibayar = Pembayaran::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)
                    ->sum('jumlah_bayar');

                $sisa = max($jumlah_tagihan - $sudah_dibayar, 0);

                // Cari bulan/batch yang belum lunas
                $detail = [
                    'tahun_ajaran' => $dp->tahunAjaran->nama ?? '-',
                    'jumlah_tagihan' => $jumlah_tagihan,
                    'sudah_dibayar' => $sudah_dibayar,
                    'sisa' => $sisa,
                    'status' => $sisa == 0 ? 'lunas' : 'belum',
'keterangan' => $sudah_dibayar > 0 ? 'Cicilan' : 'Belum Bayar'
                ];
                $tagihanArr[] = $detail;
            }
            return [
                'siswa' => $siswa,
                'tagihan' => $tagihanArr
            ];
        });

        // Tampilkan ke view
        return view('laporan.perkelas', [
            'kelas' => $kelas,
            'jenis_pembayaran_id' => $jenisId,
            'rekap' => $rekap
        ]);
    }

    /**
     * Laporan Individu (1 santri saja)
     * @param Request $request (siswa_id)
     * @return \Illuminate\View\View
     */
    public function laporanIndividu(Request $request)
    {
        $siswaId = $request->input('siswa_id');
        $siswa = Siswa::with('kelas')->findOrFail($siswaId);

        // Ambil semua detail pembayaran dari awal masuk s.d sekarang
        $detailPembayarans = DetailPembayaran::where('angkatan_mulai', '<=', $siswa->tahun_masuk)
            ->orderBy('tahun_ajaran_id')
            ->with('jenisPembayaran', 'tahunAjaran')
            ->get();

        $rekap = [];
        foreach ($detailPembayarans as $dp) {
            $isBulanan = $dp->jenisPembayaran && $dp->jenisPembayaran->tipe == 1;
            $jumlah_tagihan = $isBulanan ? ($dp->nominal ?? 0) * 12 : ($dp->nominal ?? 0);

            $pembayaran = Pembayaran::where('siswa_id', $siswa->id)
                ->where('detail_pembayaran_id', $dp->id)
                ->get();

            $sudah_dibayar = $pembayaran->sum('jumlah_bayar');
            $sisa = max($jumlah_tagihan - $sudah_dibayar, 0);

            // Cek bulan mana saja yang nunggak kalau bulanan
            $bulanTunggakan = [];
            if ($isBulanan && $jumlah_tagihan > 0) {
                // Anggap ada field "bulan" pada pembayaran
                $bulanTagihan = range(1, 12);
                $bayarLunas = $pembayaran->where('status', 'lunas')->pluck('bulan')->toArray();
                $bulanTunggakan = array_diff($bulanTagihan, $bayarLunas);
            }

            $rekap[] = [
                'tahun_ajaran' => $dp->tahunAjaran->nama ?? '-',
                'jenis_pembayaran' => $dp->jenisPembayaran->nama ?? '-',
                'jumlah_tagihan' => $jumlah_tagihan,
                'sudah_dibayar' => $sudah_dibayar,
                'sisa' => $sisa,
                'status' => $sisa == 0 ? 'lunas' : 'belum',
'keterangan' => $sudah_dibayar > 0 ? 'Cicilan' : 'Belum Bayar',
                'tunggakan_bulan' => $bulanTunggakan
            ];
        }

        return view('laporan.individu', [
            'siswa' => $siswa,
            'rekap' => $rekap
        ]);
    }
    public function rekap(Request $request)
{
    $tahunAjaranId = $request->input('tahun_ajaran_id');
    $kelasId = $request->input('kelas_id');
    $jenisPembayaranId = $request->input('jenis_pembayaran_id'); // Ambil dari request
    $daftarTahunAjaran = TahunAjaran::all();
    $daftarKelas = Kelas::all();
    $jenisPembayaranList = jenispembayaran::all();

    $rekapList = collect();

    if ($tahunAjaranId && $jenisPembayaranId) {
        // Ambil semua detail pembayaran pada tahun ajaran & jenis terpilih
        $detailPembayaranList = DetailPembayaran::where('tahun_ajaran_id', $tahunAjaranId)
            ->where('jenis_pembayaran_id', $jenisPembayaranId)
            ->get();

        // Ambil semua siswa yang pernah punya historyKelas di tahun ajaran ini
        $querySiswa = Siswa::whereHas('historyKelas', function($q) use($tahunAjaranId, $kelasId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId);
            if ($kelasId) {
                $q->where('kelas_id', $kelasId);
            }
        });

        $siswaList = $querySiswa->with(['historyKelas.kelas'])->get();

        foreach ($siswaList as $siswa) {
            // Cek apakah siswa ini punya detail pembayaran di angkatan dia untuk jenis_pembayaran_id terpilih
            $dp = $detailPembayaranList->first(function($item) use ($siswa) {
                return $item->angkatan_mulai == $siswa->tahun_masuk;
            });

            if (!$dp) continue; // Siswa ini tidak punya tagihan jenis tersebut pada tahun ajaran ini

            $kelas = optional($siswa->historyKelas->where('tahun_ajaran_id', $tahunAjaranId)->first())->kelas;
            
            $pembayaranSiswa = Pembayaran::where('siswa_id', $siswa->id)
                ->where('detail_pembayaran_id', $dp->id)
                ->get();

            $jumlah_tagihan = ($dp->nominal ?? 0) * 12;
            $sudah_dibayar = $pembayaranSiswa->sum('jumlah_bayar');
            $sisa = max($jumlah_tagihan - $sudah_dibayar, 0);

            if ($sudah_dibayar >= $jumlah_tagihan) {
    $statusPembayaran = 'lunas';
    $keterangan = 'Lunas';
} else {
    $statusPembayaran = 'belum'; // TIDAK ADA CICILAN sebagai status utama!
    $keterangan = $sudah_dibayar > 0 ? 'Cicilan' : 'Belum Bayar';
}

            $rekapList->push([
                'siswa' => $siswa,
                'kelas' => $kelas,
                'jumlah_tagihan' => $jumlah_tagihan,
                'sudah_dibayar' => $sudah_dibayar,
                'sisa' => $sisa,
                'status' => $statusPembayaran,
                'detail_pembayaran_id' => $dp->id,
            ]);
        }
    }

    return view('pembayaran.rekap', compact(
        'daftarTahunAjaran', 'daftarKelas', 'rekapList', 'jenisPembayaranList'
    ));
}

    public function rekapDetail(Request $request)
{
    $siswa_id = $request->input('siswa_id');
    $tahun_ajaran_id = $request->input('tahun_ajaran_id');
    $kelas_id = $request->input('kelas_id');
    $jenis_pembayaran_id = $request->input('jenis_pembayaran_id');

    if (!$siswa_id || !$tahun_ajaran_id || !$jenis_pembayaran_id) {
        return response()->json([
            'status' => 'error',
            'message' => 'Parameter tidak lengkap.'
        ], 400);
    }

    $siswa = Siswa::with('historyKelas.kelas')->find($siswa_id);
    if (!$siswa) {
        return response()->json([
            'status' => 'error',
            'message' => 'Siswa tidak ditemukan.'
        ], 404);
    }

    $kelas = '-';
    if ($siswa->historyKelas) {
        $history = $siswa->historyKelas->where('tahun_ajaran_id', $tahun_ajaran_id)->first();
        if ($history && $history->kelas) {
            $kelas = $history->kelas->nama_kelas ?? ($history->kelas->nama ?? '-');
        }
    }

    // Cari detail pembayaran milik siswa untuk jenis terpilih
    $dp = DetailPembayaran::where('tahun_ajaran_id', $tahun_ajaran_id)
        ->where('jenis_pembayaran_id', $jenis_pembayaran_id)
        ->where('angkatan_mulai', $siswa->tahun_masuk)
        ->with(['jenisPembayaran', 'tahunAjaran'])
        ->first();

    if (!$dp) {
        return response()->json([
            'status' => 'error',
            'message' => 'Detail pembayaran tidak ditemukan.'
        ], 404);
    }

    // ---- DETEKSI NAMA JENIS PEMBAYARAN SECARA TEPAT ----
    $jenisNama = strtolower($dp->jenisPembayaran->nama);
    $isTabungan = strpos($jenisNama, 'tabungan') !== false;
    $isDaftarUlang = strpos($jenisNama, 'daftar ulang') !== false;

    // ===== TABUNGAN =====
    if ($isTabungan) {
        $setor = Tabungan::where('siswa_id', $siswa_id)
            ->where('detail_pembayaran_id', $dp->id)
            ->where('jenis', 'setor')->sum('nominal');
        $tarik = Tabungan::where('siswa_id', $siswa_id)
            ->where('detail_pembayaran_id', $dp->id)
            ->where('jenis', 'ambil')->sum('nominal');
        $history = Tabungan::where('siswa_id', $siswa_id)
            ->where('detail_pembayaran_id', $dp->id)
            ->orderBy('tanggal')->get();

        // Hitung saldo berjalan per row
        $historyArr = [];
        $saldo = 0;
        foreach ($history as $row) {
            if ($row->jenis == 'setor') {
                $saldo += $row->nominal;
            } else {
                $saldo -= $row->nominal;
            }
            $historyArr[] = [
                'tanggal' => $row->tanggal,
                'masuk' => $row->jenis == 'setor' ? $row->nominal : 0,
                'keluar' => $row->jenis == 'ambil' ? $row->nominal : 0,
                'saldo' => $saldo,
            ];
        }

        return response()->json([
            'siswa' => [
                'nama' => $siswa->nama,
                'kelas' => $kelas,
                'tahun_ajaran' => $dp->tahunAjaran->nama,
            ],
            'tipe_pembayaran' => 'tabungan',
            'detail' => [
                [
                    'nama_jenis' => $dp->jenisPembayaran->nama,
                    'saldo_akhir' => $setor - $tarik,
                    'total_masuk' => $setor,
                    'total_keluar' => $tarik,
                    'history' => $historyArr,
                ]
            ]
        ]);
    }

    // ===== DAFTAR ULANG =====
    if ($isDaftarUlang) {
        $du = DaftarUlang::where('siswa_id', $siswa_id)
            ->where('detail_pembayaran_id', $dp->id)->first();
        $duHistory = DaftarUlangHistory::where('siswa_id', $siswa_id)
            ->where('detail_pembayaran_id', $dp->id)
            ->orderBy('tanggal_bayar')->get();

        return response()->json([
            'siswa' => [
                'nama' => $siswa->nama,
                'kelas' => $kelas,
                'tahun_ajaran' => $dp->tahunAjaran->nama,
            ],
            'tipe_pembayaran' => 'daftar_ulang',
            'detail' => [
                [
                    'nama_jenis' => $dp->jenisPembayaran->nama,
                    'nominal' => $dp->nominal,
                    'dibayar' => $du ? $du->jumlah_bayar : 0,
                    'status' => $du ? $du->status : 'belum',
                    'cicilan_list' => $duHistory->map(function($item, $idx) {
                        return [
                            'tanggal_bayar' => $item->tanggal_bayar,
                            'nominal' => $item->jumlah_bayar,
                            'status' => $item->status,
                            'keterangan' => $item->keterangan,
                        ];
                    })->values(),
                ]
            ]
        ]);
    }

    // ===== BULANAN =====
    if ($dp->jenisPembayaran->tipe == 1) {
    // 1. Ambil periode tahun ajaran
    $mulai = $dp->tahunAjaran->mulai;     // contoh: '2025-07-01'
    $selesai = $dp->tahunAjaran->selesai; // contoh: '2026-06-30'

    // 2. Buat array semua bulan (YYYY-MM)
    $listBulan = [];
    $periode = \Carbon\CarbonPeriod::create($mulai, '1 month', $selesai);
    foreach ($periode as $dt) {
        $key = $dt->format('Y-m'); // Key unik
        $listBulan[$key] = [
            'nama_bulan' => $dt->isoFormat('MMMM YYYY'), // Juli 2025
            'bulan' => intval($dt->format('m')),
            'tahun' => intval($dt->format('Y')),
            'key' => $key,
        ];
    }

    // 3. Ambil pembayaran per bulan dan simpan ke array [YYYY-MM] => data
    $pembayaranPerBulan = Pembayaran::where('siswa_id', $siswa_id)
        ->where('detail_pembayaran_id', $dp->id)
        ->get()
        ->keyBy(function($item) use ($dp) {
    $mulaiDate = \Carbon\Carbon::parse($dp->tahunAjaran->mulai);
    $tahunMulai = $mulaiDate->format('Y');
    $bulanMulai = intval($mulaiDate->format('m'));

    // Jika bulan pembayaran >= bulan mulai, tahun = tahun ajaran mulai
    // Jika bulan pembayaran < bulan mulai, tahun = tahun ajaran mulai + 1
    $tahun = ($item->bulan >= $bulanMulai) ? $tahunMulai : ($tahunMulai + 1);

    return $tahun . '-' . str_pad($item->bulan, 2, '0', STR_PAD_LEFT);
});

    // 4. Loop semua bulan (JULI 2025 dst) lalu gabung dengan pembayaran jika ada
    $histories = [];
    foreach ($listBulan as $key => $row) {
        $bayar = $pembayaranPerBulan->get($key); // null jika belum bayar
        $status = $bayar ? $bayar->status : 'belum';
        $nominal = $bayar ? $bayar->jumlah_bayar : 0;
        $tanggalBayar = '-';
        $cicilanList = [];
        if ($bayar) {
            $cicilanList = PembayaranHistory::where('pembayaran_id', $bayar->id)
                ->orderBy('tanggal_bayar')->get();
            $tanggalBayar = $cicilanList->count() > 0 ? $cicilanList->last()->tanggal_bayar : '-';
        }
        $histories[] = [
            'nama_bulan' => $row['nama_bulan'], // ini nanti yang ditampilkan
            'bulan' => $row['bulan'],
            'tahun' => $row['tahun'],
            'nominal' => $nominal,
            'status' => $status == 'lunas' ? 'Lunas' : ($status == 'cicilan' ? 'Cicilan' : 'Belum'),
            'keterangan' => $status == 'lunas' ? 'Lunas' : ($status == 'cicilan' ? 'Cicilan' : 'Belum'),
            'tanggal_bayar' => $tanggalBayar,
            'cicilan_list' => collect($cicilanList)->map(function($cicil) {
                return [
                    'nominal' => $cicil->jumlah_bayar,
                    'tanggal_bayar' => $cicil->tanggal_bayar,
                    'status' => $cicil->status,
                    'keterangan' => $cicil->keterangan,
                ];
            })->values(),
        ];
    }

    return response()->json([
        'siswa' => [
            'nama' => $siswa->nama,
            'kelas' => $kelas,
            'tahun_ajaran' => $dp->tahunAjaran->nama,
        ],
        'tipe_pembayaran' => 'bulanan',
        'detail' => [
            [
                'nama_jenis' => $dp->jenisPembayaran->nama,
                'detail' => $histories
            ]
        ]
    ]);
}

    // ===== BEBAS (LAINNYA, TIPE 0 tapi bukan tabungan/daftar ulang) =====
    if ($dp->jenisPembayaran->tipe == 0 && !$isTabungan && !$isDaftarUlang) {
        // Jika ada pembayaran bebas lain, mapping di sini
        $du = DaftarUlang::where('siswa_id', $siswa_id)
            ->where('detail_pembayaran_id', $dp->id)->first();
        $duHistory = DaftarUlangHistory::where('siswa_id', $siswa_id)
            ->where('detail_pembayaran_id', $dp->id)
            ->orderBy('tanggal_bayar')->get();

        return response()->json([
            'siswa' => [
                'nama' => $siswa->nama,
                'kelas' => $kelas,
                'tahun_ajaran' => $dp->tahunAjaran->nama,
            ],
            'tipe_pembayaran' => 'bebas',
            'detail' => [
                [
                    'nama_jenis' => $dp->jenisPembayaran->nama,
                    'nominal' => $dp->nominal,
                    'dibayar' => $du ? $du->jumlah_bayar : 0,
                    'status' => $du ? $du->status : 'belum',
                    'cicilan_list' => $duHistory->map(function($item, $idx) {
                        return [
                            'tanggal_bayar' => $item->tanggal_bayar,
                            'nominal' => $item->jumlah_bayar,
                            'status' => $item->status,
                            'keterangan' => $item->keterangan,
                        ];
                    })->values(),
                ]
            ]
        ]);
    }

    // Fallback
    return response()->json([
        'siswa' => [
            'nama' => $siswa->nama,
            'kelas' => $kelas,
            'tahun_ajaran' => $dp->tahunAjaran->nama,
        ],
        'detail' => [],
        'tipe_pembayaran' => null
    ]);
}

    public function apiRekapSiswa(Request $request)
{
    $siswaId = $request->input('siswa_id');
    $tahunAjaranId = $request->input('tahun_ajaran_id');
    $kelasId = $request->input('kelas_id');
    $jenisPembayaranId = $request->input('jenis_pembayaran_id');

    // Jika cari satu siswa (livesearch)
    if ($siswaId) {
        $siswa = Siswa::find($siswaId);

        if (!$siswa) {
            return response()->json(['rekap' => [], 'siswa' => null]);
        }

        $query = DetailPembayaran::query()
            ->where('angkatan_mulai', $siswa->tahun_masuk)
            ->with('tahunAjaran', 'jenisPembayaran');

        if ($tahunAjaranId) $query->where('tahun_ajaran_id', $tahunAjaranId);
        if ($jenisPembayaranId) $query->where('jenis_pembayaran_id', $jenisPembayaranId);

        $detailPembayaranList = $query->get();

        $rekap = [];
        foreach ($detailPembayaranList as $dp) {
            // ambil kelas berdasarkan TA
            $kelas = $siswa->historyKelas()
                ->where('tahun_ajaran_id', $dp->tahun_ajaran_id)
                ->first();
            $kelasNama = $kelas ? ($kelas->kelas->nama_kelas ?? $kelas->kelas->nama ?? '-') : '-';

            // TABUNGAN
            if ($dp->jenisPembayaran && stripos($dp->jenisPembayaran->nama, 'tabungan') !== false) {
                $setor = Tabungan::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)
                    ->where('status', 'valid')
                    ->where('jenis', 'setor')
                    ->sum('nominal');
                $tarik = Tabungan::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)
                    ->where('status', 'valid')
                    ->where('jenis', 'ambil')
                    ->sum('nominal');
                $saldoAkhir = $setor - $tarik;

                $setorTerakhir = Tabungan::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)
                    ->where('status', 'valid')
                    ->where('jenis', 'setor')
                    ->orderByDesc('tanggal')
                    ->value('nominal') ?? 0;

                $rekap[] = [
                    'tahun_ajaran' => $dp->tahunAjaran->nama,
                    'tahun_ajaran_id' => $dp->tahun_ajaran_id,
                    'jenis_pembayaran' => $dp->jenisPembayaran->nama,
                    'jenis_pembayaran_id' => $dp->jenis_pembayaran_id,
                    'kelas' => $kelasNama,
                    'kelas_id' => $kelas ? $kelas->kelas_id : null,
                    'jumlah_tagihan' => 0,
                    'sudah_dibayar' => $setorTerakhir,
                    'sisa' => $saldoAkhir,
                    'status' => 'lunas',
                    'siswa_id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'nis' => $siswa->nis,
                    'status_siswa' => $siswa->status,
                    'tipe_pembayaran' => 'tabungan',
                ];
                continue;
            }

            // DAFTAR ULANG / BEBAS (ambil dari tabel daftar_ulang!)
            $jenisNama = strtolower($dp->jenisPembayaran->nama);
            if ($dp->jenisPembayaran->tipe == 0 && (strpos($jenisNama, 'daftar ulang') !== false || $jenisNama == 'bebas')) {
                $du = DaftarUlang::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)
                    ->first();

                $jumlah_tagihan = $dp->nominal ?? 0;
                $sudah_dibayar = $du ? $du->jumlah_bayar : 0;
                $sisa = max($jumlah_tagihan - $sudah_dibayar, 0);
                $statusPembayaran = $du ? $du->status : 'belum';

                $rekap[] = [
                    'tahun_ajaran' => $dp->tahunAjaran->nama,
                    'tahun_ajaran_id' => $dp->tahun_ajaran_id,
                    'jenis_pembayaran' => $dp->jenisPembayaran->nama,
                    'jenis_pembayaran_id' => $dp->jenis_pembayaran_id,
                    'kelas' => $kelasNama,
                    'kelas_id' => $kelas ? $kelas->kelas_id : null,
                    'jumlah_tagihan' => $jumlah_tagihan,
                    'sudah_dibayar' => $sudah_dibayar,
                    'sisa' => $sisa,
                    'status' => $statusPembayaran,
                    'siswa_id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'nis' => $siswa->nis,
                    'status_siswa' => $siswa->status,
                    'tipe_pembayaran' => $jenisNama, // bisa 'daftar ulang' atau 'bebas'
                ];
                continue;
            }

            // BULANAN / LAINNYA
            $pembayaranSiswa = Pembayaran::where('siswa_id', $siswa->id)
                ->where('detail_pembayaran_id', $dp->id)
                ->get();

            $isBulanan = $dp->jenisPembayaran && $dp->jenisPembayaran->tipe == 1;
            if ($isBulanan) {
                $jumlah_tagihan = ($dp->nominal ?? 0) * 12;
            } else {
                $jumlah_tagihan = $dp->nominal ?? 0;
            }

            $sudah_dibayar = $pembayaranSiswa->sum('jumlah_bayar');
            $sisa = max($jumlah_tagihan - $sudah_dibayar, 0);

if ($sudah_dibayar >= $jumlah_tagihan) {
    $statusPembayaran = 'lunas';
    $keterangan = 'Lunas';
} else {
    $statusPembayaran = 'belum'; // TIDAK ADA CICILAN sebagai status utama!
    $keterangan = $sudah_dibayar > 0 ? 'Cicilan' : 'Belum Bayar';
}

            $rekap[] = [
                'tahun_ajaran' => $dp->tahunAjaran->nama,
                'tahun_ajaran_id' => $dp->tahun_ajaran_id,
                'jenis_pembayaran' => $dp->jenisPembayaran->nama,
                'jenis_pembayaran_id' => $dp->jenis_pembayaran_id,
                'kelas' => $kelasNama,
                'kelas_id' => $kelas ? $kelas->kelas_id : null,
                'jumlah_tagihan' => $jumlah_tagihan,
                'sudah_dibayar' => $sudah_dibayar,
                'sisa' => $sisa,
                'status' => $statusPembayaran,
                'siswa_id' => $siswa->id,
                'nama' => $siswa->nama,
                'nis' => $siswa->nis,
                'status_siswa' => $siswa->status,
                'keterangan' => $keterangan,
                'tipe_pembayaran' => $dp->jenisPembayaran->tipe,
            ];
        }

        return response()->json([
            'rekap' => $rekap,
            'siswa' => [
                'nama' => $siswa->nama,
                'nis' => $siswa->nis,
            ]
        ]);
    }

    // Jika tanpa siswa_id, tampilkan semua siswa sesuai filter (untuk mode filter massal)
    else {
        $statusSiswa = $request->input('status_siswa');
        $user = Auth::user();

        // === FILTER ANAK WALINYA JIKA ROLE WALI ===
        if ($user && $user->hasRole('wali')) {
            $anakWaliIds = $user->siswas()->pluck('id');
            $query = Siswa::whereIn('id', $anakWaliIds);
        } else {
            $query = Siswa::query();
        }
        // Tambahkan filter status siswa jika ada
        if ($statusSiswa) $query->where('status', $statusSiswa);
        // Tambahkan filter tahun ajaran jika diisi (berarti filter siswa yang punya history kelas pada tahun ajaran tsb)
       if ($tahunAjaranId && $kelasId) {
    $query->whereHas('historyKelas', function($q) use ($tahunAjaranId, $kelasId) {
        $q->where('tahun_ajaran_id', $tahunAjaranId)
          ->where('kelas_id', $kelasId);
    });
} elseif ($tahunAjaranId) {
    $query->whereHas('historyKelas', function($q) use ($tahunAjaranId) {
        $q->where('tahun_ajaran_id', $tahunAjaranId);
    });
} elseif ($kelasId) {
    $query->whereHas('historyKelas', function($q) use ($kelasId) {
        $q->where('kelas_id', $kelasId);
    });
}
        $siswaList = $query->get();

        $rekap = [];
        foreach ($siswaList as $siswa) {

            // ------ INI BLOK BARU: Untuk lulus tanpa filter tahun ajaran, tampilkan semua history ------
            if ($statusSiswa == 'lulus' && !$tahunAjaranId) {
                foreach ($siswa->historyKelas as $history) {
                    $kelasNama = $history->kelas ? ($history->kelas->nama_kelas ?? $history->kelas->nama ?? '-') : '-';

                    // Ambil semua detail pembayaran pada tahun ajaran history tsb & angkatan siswa
                    $dpList = DetailPembayaran::where('angkatan_mulai', $siswa->tahun_masuk)
                        ->where('tahun_ajaran_id', $history->tahun_ajaran_id)
                        ->when($jenisPembayaranId, function($q) use ($jenisPembayaranId) {
                            $q->where('jenis_pembayaran_id', $jenisPembayaranId);
                        })
                        ->with('tahunAjaran', 'jenisPembayaran')
                        ->get();

                    foreach ($dpList as $dp) {
                        // -- TABUNGAN
                        if ($dp->jenisPembayaran && stripos($dp->jenisPembayaran->nama, 'tabungan') !== false) {
                            $setor = Tabungan::where('siswa_id', $siswa->id)
                                ->where('detail_pembayaran_id', $dp->id)
                                ->where('status', 'valid')
                                ->where('jenis', 'setor')
                                ->sum('nominal');
                            $tarik = Tabungan::where('siswa_id', $siswa->id)
                                ->where('detail_pembayaran_id', $dp->id)
                                ->where('status', 'valid')
                                ->where('jenis', 'ambil')
                                ->sum('nominal');
                            $saldoAkhir = $setor - $tarik;
                            $setorTerakhir = Tabungan::where('siswa_id', $siswa->id)
                                ->where('detail_pembayaran_id', $dp->id)
                                ->where('status', 'valid')
                                ->where('jenis', 'setor')
                                ->orderByDesc('tanggal')
                                ->value('nominal') ?? 0;

                            $rekap[] = [
                                'tahun_ajaran' => $dp->tahunAjaran->nama,
                                'tahun_ajaran_id' => $dp->tahun_ajaran_id,
                                'jenis_pembayaran' => $dp->jenisPembayaran->nama,
                                'jenis_pembayaran_id' => $dp->jenis_pembayaran_id,
                                'kelas' => $kelasNama,
                                'kelas_id' => $history->kelas_id,
                                'jumlah_tagihan' => 0,
                                'sudah_dibayar' => $setorTerakhir,
                                'sisa' => $saldoAkhir,
                                'status' => 'lunas',
                                'siswa_id' => $siswa->id,
                                'nama' => $siswa->nama,
                                'nis' => $siswa->nis,
                                'status_siswa' => $siswa->status,
                                'tipe_pembayaran' => 'tabungan',
                            ];
                            continue;
                        }

                        // -- DAFTAR ULANG / BEBAS
                        $jenisNama = strtolower($dp->jenisPembayaran->nama);
                        if ($dp->jenisPembayaran->tipe == 0 && (strpos($jenisNama, 'daftar ulang') !== false || $jenisNama == 'bebas')) {
                            $du = DaftarUlang::where('siswa_id', $siswa->id)
                                ->where('detail_pembayaran_id', $dp->id)
                                ->first();
                            $jumlah_tagihan = $dp->nominal ?? 0;
                            $sudah_dibayar = $du ? $du->jumlah_bayar : 0;
                            $sisa = max($jumlah_tagihan - $sudah_dibayar, 0);
                            $statusPembayaran = $du ? $du->status : 'belum';

                            $rekap[] = [
                                'tahun_ajaran' => $dp->tahunAjaran->nama,
                                'tahun_ajaran_id' => $dp->tahun_ajaran_id,
                                'jenis_pembayaran' => $dp->jenisPembayaran->nama,
                                'jenis_pembayaran_id' => $dp->jenis_pembayaran_id,
                                'kelas' => $kelasNama,
                                'kelas_id' => $history->kelas_id,
                                'jumlah_tagihan' => $jumlah_tagihan,
                                'sudah_dibayar' => $sudah_dibayar,
                                'sisa' => $sisa,
                                'status' => $statusPembayaran,
                                'siswa_id' => $siswa->id,
                                'nama' => $siswa->nama,
                                'nis' => $siswa->nis,
                                'status_siswa' => $siswa->status,
                                'tipe_pembayaran' => $jenisNama,
                            ];
                            continue;
                        }

                        // -- BULANAN / LAINNYA
                        $isBulanan = $dp->jenisPembayaran && $dp->jenisPembayaran->tipe == 1;
                        $jumlah_tagihan = $isBulanan ? ($dp->nominal ?? 0) * 12 : ($dp->nominal ?? 0);
                        $pembayaranSiswa = Pembayaran::where('siswa_id', $siswa->id)
                            ->where('detail_pembayaran_id', $dp->id)
                            ->get();
                        $sudah_dibayar = $pembayaranSiswa->sum('jumlah_bayar');
                        $sisa = max($jumlah_tagihan - $sudah_dibayar, 0);

                        if ($sudah_dibayar >= $jumlah_tagihan) {
    $statusPembayaran = 'lunas';
    $keterangan = 'Lunas';
} else {
    $statusPembayaran = 'belum'; // TIDAK ADA CICILAN sebagai status utama!
    $keterangan = $sudah_dibayar > 0 ? 'Cicilan' : 'Belum Bayar';
}

                        $rekap[] = [
                            'tahun_ajaran' => $dp->tahunAjaran->nama,
                            'tahun_ajaran_id' => $dp->tahun_ajaran_id,
                            'jenis_pembayaran' => $dp->jenisPembayaran->nama,
                            'jenis_pembayaran_id' => $dp->jenis_pembayaran_id,
                            'kelas' => $kelasNama,
                            'kelas_id' => $history->kelas_id,
                            'jumlah_tagihan' => $jumlah_tagihan,
                            'sudah_dibayar' => $sudah_dibayar,
                            'sisa' => $sisa,
                            'status' => $statusPembayaran,
                            'siswa_id' => $siswa->id,
                            'nama' => $siswa->nama,
                            'nis' => $siswa->nis,
                            'status_siswa' => $siswa->status,
                            'tipe_pembayaran' => $dp->jenisPembayaran->tipe,
                        ];
                    }
                }
                continue; // skip ke siswa berikutnya
            }
            // ------ END BLOK BARU ------

            // Selain itu, tetap hanya satu tahun ajaran saja (seperti semula)
            $history = $siswa->historyKelas()
                ->when($tahunAjaranId, function($q) use ($tahunAjaranId) {
                    $q->where('tahun_ajaran_id', $tahunAjaranId);
                })
                ->when($kelasId, function($q) use ($kelasId) {
                    $q->where('kelas_id', $kelasId);
                })
                ->orderByDesc('tahun_ajaran_id')
                ->first();

            $kelasNama = $history ? ($history->kelas->nama_kelas ?? $history->kelas->nama ?? '-') : '-';

            $dpQuery = DetailPembayaran::where('angkatan_mulai', $siswa->tahun_masuk);
            if ($tahunAjaranId) $dpQuery->where('tahun_ajaran_id', $tahunAjaranId);
            if ($jenisPembayaranId) $dpQuery->where('jenis_pembayaran_id', $jenisPembayaranId);
            $dp = $dpQuery->with('tahunAjaran', 'jenisPembayaran')->first();
            if (!$dp) continue;

            // TABUNGAN
            if ($dp->jenisPembayaran && stripos($dp->jenisPembayaran->nama, 'tabungan') !== false) {
                $setor = Tabungan::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)
                    ->where('status', 'valid')
                    ->where('jenis', 'setor')
                    ->sum('nominal');
                $tarik = Tabungan::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)
                    ->where('status', 'valid')
                    ->where('jenis', 'ambil')
                    ->sum('nominal');
                $saldoAkhir = $setor - $tarik;
                $setorTerakhir = Tabungan::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)
                    ->where('status', 'valid')
                    ->where('jenis', 'setor')
                    ->orderByDesc('tanggal')
                    ->value('nominal') ?? 0;

                $rekap[] = [
                    'tahun_ajaran' => $dp->tahunAjaran->nama,
                    'tahun_ajaran_id' => $dp->tahun_ajaran_id,
                    'jenis_pembayaran' => $dp->jenisPembayaran->nama,
                    'jenis_pembayaran_id' => $dp->jenis_pembayaran_id,
                    'kelas' => $kelasNama,
                    'kelas_id' => $history ? $history->kelas_id : null,
                    'jumlah_tagihan' => 0,
                    'sudah_dibayar' => $setorTerakhir,
                    'sisa' => $saldoAkhir,
                    'status' => 'lunas',
                    'siswa_id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'nis' => $siswa->nis,
                    'status_siswa' => $siswa->status,
                    'tipe_pembayaran' => 'tabungan',
                ];
                continue;
            }

            // DAFTAR ULANG / BEBAS
            $jenisNama = strtolower($dp->jenisPembayaran->nama);
            if ($dp->jenisPembayaran->tipe == 0 && (strpos($jenisNama, 'daftar ulang') !== false || $jenisNama == 'bebas')) {
                $du = DaftarUlang::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)
                    ->first();

                $jumlah_tagihan = $dp->nominal ?? 0;
                $sudah_dibayar = $du ? $du->jumlah_bayar : 0;
                $sisa = max($jumlah_tagihan - $sudah_dibayar, 0);
                $statusPembayaran = $du ? $du->status : 'belum';

                $rekap[] = [
                    'tahun_ajaran' => $dp->tahunAjaran->nama,
                    'tahun_ajaran_id' => $dp->tahun_ajaran_id,
                    'jenis_pembayaran' => $dp->jenisPembayaran->nama,
                    'jenis_pembayaran_id' => $dp->jenis_pembayaran_id,
                    'kelas' => $kelasNama,
                    'kelas_id' => $history ? $history->kelas_id : null,
                    'jumlah_tagihan' => $jumlah_tagihan,
                    'sudah_dibayar' => $sudah_dibayar,
                    'sisa' => $sisa,
                    'status' => $statusPembayaran,
                    'siswa_id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'nis' => $siswa->nis,
                    'status_siswa' => $siswa->status,
                    'tipe_pembayaran' => $jenisNama,
                ];
                continue;
            }

            // BULANAN / LAINNYA
            $pembayaranSiswa = Pembayaran::where('siswa_id', $siswa->id)
                ->where('detail_pembayaran_id', $dp->id)
                ->get();

            $isBulanan = $dp->jenisPembayaran && $dp->jenisPembayaran->tipe == 1;
            if ($isBulanan) {
                $jumlah_tagihan = ($dp->nominal ?? 0) * 12;
            } else {
                $jumlah_tagihan = $dp->nominal ?? 0;
            }

            $sudah_dibayar = $pembayaranSiswa->sum('jumlah_bayar');
            $sisa = max($jumlah_tagihan - $sudah_dibayar, 0);

            if ($sudah_dibayar >= $jumlah_tagihan) {
    $statusPembayaran = 'lunas';
    $keterangan = 'Lunas';
} else {
    $statusPembayaran = 'belum'; // TIDAK ADA CICILAN sebagai status utama!
    $keterangan = $sudah_dibayar > 0 ? 'Cicilan' : 'Belum Bayar';
}

            $rekap[] = [
                'tahun_ajaran' => $dp->tahunAjaran->nama,
                'tahun_ajaran_id' => $dp->tahun_ajaran_id,
                'jenis_pembayaran' => $dp->jenisPembayaran->nama,
                'jenis_pembayaran_id' => $dp->jenis_pembayaran_id,
                'kelas' => $kelasNama,
                'kelas_id' => $history ? $history->kelas_id : null,
                'jumlah_tagihan' => $jumlah_tagihan,
                'sudah_dibayar' => $sudah_dibayar,
                'sisa' => $sisa,
                'status' => $statusPembayaran,
                'siswa_id' => $siswa->id,
                'nama' => $siswa->nama,
                'nis' => $siswa->nis,
                'status_siswa' => $siswa->status,
                'tipe_pembayaran' => $dp->jenisPembayaran->tipe,
            ];
        }

        return response()->json([
            'rekap' => $rekap,
            'siswa' => null
        ]);
    }
}

public function export(Request $request)
{
    $tahunAjaranId = $request->input('tahun_ajaran_id');
    $kelasId = $request->input('kelas_id');
    $jenisPembayaranId = $request->input('jenis_pembayaran_id');
    $statusSiswa = $request->input('status_siswa');
    $siswaId = $request->input('siswa_id');
    $statusPembayaranFilter = $request->input('status_pembayaran');

    // ------- prepare siswa & detail pembayaran --------
    $querySiswa = Siswa::query();
    if ($statusSiswa) $querySiswa->where('status', $statusSiswa);
    if ($kelasId) {
        $querySiswa->whereHas('historyKelas', function($q) use($kelasId, $tahunAjaranId) {
            $q->where('kelas_id', $kelasId);
            if ($tahunAjaranId) $q->where('tahun_ajaran_id', $tahunAjaranId);
        });
    }
    if ($siswaId) $querySiswa->where('id', $siswaId);
    $siswaList = $querySiswa->get();

    $detailPembayaranQuery = DetailPembayaran::with('tahunAjaran', 'jenisPembayaran');
    if ($tahunAjaranId) $detailPembayaranQuery->where('tahun_ajaran_id', $tahunAjaranId);
    if ($jenisPembayaranId) $detailPembayaranQuery->where('jenis_pembayaran_id', $jenisPembayaranId);
    $detailPembayaranList = $detailPembayaranQuery->get();

    // ----------- BULANAN SHEET -----------
    $dataBulanan = [];
    $dataBulanan[] = ['No', 'Nama Siswa', 'NIS', 'Kelas', 'Tahun Ajaran', 'Jenis Pembayaran', 'Bulan', 'Tanggal Bayar', 'Nominal', 'Status', 'Keterangan'];
    $idx = 1;
    foreach ($siswaList as $siswa) {
        foreach ($detailPembayaranList as $dp) {
            if ($dp->jenisPembayaran->tipe != 1) continue;
            if ($dp->angkatan_mulai != $siswa->tahun_masuk) continue;
            $nominalTagihanBulan = $dp->nominal ?? 0;
            $mulai = $dp->tahunAjaran->mulai;
            $selesai = $dp->tahunAjaran->selesai;
            $listBulan = [];
            $periode = \Carbon\CarbonPeriod::create($mulai, '1 month', $selesai);
            foreach ($periode as $dt) {
                $key = $dt->format('Y-m');
                $listBulan[$key] = [
                    'nama_bulan' => $dt->isoFormat('MMMM YYYY'),
                    'bulan' => intval($dt->format('m')),
                    'tahun' => intval($dt->format('Y')),
                    'key' => $key,
                ];
            }
            $pembayaranPerBulan = Pembayaran::where('siswa_id', $siswa->id)
                ->where('detail_pembayaran_id', $dp->id)
                ->get()
                ->keyBy(function($item) use ($dp) {
                    $mulaiDate = \Carbon\Carbon::parse($dp->tahunAjaran->mulai);
                    $tahunMulai = $mulaiDate->format('Y');
                    $bulanMulai = intval($mulaiDate->format('m'));
                    $tahun = ($item->bulan >= $bulanMulai) ? $tahunMulai : ($tahunMulai + 1);
                    return $tahun . '-' . str_pad($item->bulan, 2, '0', STR_PAD_LEFT);
                });

            // --- Ambil nama kelas dari historyKelas pada tahun ajaran tagihan
            $kelasNama = '-';
            $historyKelas = $siswa->historyKelas()
                ->where('tahun_ajaran_id', $dp->tahun_ajaran_id)
                ->first();
            if ($historyKelas && $historyKelas->kelas) {
                $kelasNama = $historyKelas->kelas->nama_kelas ?? $historyKelas->kelas->nama ?? '-';
            }

            foreach ($listBulan as $key => $row) {
                $bayar = $pembayaranPerBulan->get($key);
                $status = $bayar ? $bayar->status : 'belum';
                $nominal = $bayar ? $bayar->jumlah_bayar : 0;
                $tanggalBayar = '-';
                $keterangan = '-';

                if ($bayar) {
                    $cicilanList = PembayaranHistory::where('pembayaran_id', $bayar->id)
                        ->orderBy('tanggal_bayar')->get();
                    if ($cicilanList->count() > 0) {
                        $tanggalBayar = $cicilanList->last()->tanggal_bayar;
                        $keterangan = $cicilanList->last()->keterangan ?: ($bayar->status == 'cicilan' ? 'Cicilan' : ucfirst($bayar->status));
                    } else {
                        $tanggalBayar = $bayar->created_at ? $bayar->created_at->format('d-m-Y') : '-';
                        $keterangan = ($bayar->status == 'cicilan' ? 'Cicilan' : ucfirst($bayar->status));
                    }
                }

                $statusTampil = ($status == 'lunas') ? 'Lunas' : 'Belum';
                $keteranganTampil = ($status == 'lunas')
                    ? 'Lunas'
                    : (($status == 'cicilan' || ($bayar && $bayar->jumlah_bayar > 0)) ? 'Cicilan' : 'Belum Bayar');

                // ==== FILTER export: hanya tampil sesuai filter status pembayaran ====
                if ($statusPembayaranFilter) {
                    if ($statusPembayaranFilter == 'belum') {
                        if ($status == 'lunas' || $nominal >= $nominalTagihanBulan) {
                            continue;
                        }
                    }
                    if ($statusPembayaranFilter == 'lunas') {
                        if ($status != 'lunas' || $nominal < $nominalTagihanBulan) {
                            continue;
                        }
                    }
                }

                $dataBulanan[] = [
                    $idx++,
                    $siswa->nama,
                    $siswa->nis,
                    $kelasNama,
                    $dp->tahunAjaran->nama ?? '-',
                    $dp->jenisPembayaran->nama ?? '-',
                    $row['nama_bulan'],
                    $tanggalBayar,
                    'Rp ' . number_format($nominal, 0, ',', '.'),
                    $statusTampil,
                    $keteranganTampil,
                ];
            }
        }
    }
    if (count($dataBulanan) == 1) {
        $dataBulanan[] = ['-', 'Tidak ada data sesuai filter', '', '', '', '', '', '', '', '', ''];
    }

    // ----------- DAFTAR ULANG (sheet bebas): Tambah filter juga -----------
    $dataDaftarUlang = [];
    $dataDaftarUlang[] = ['No', 'Nama Siswa', 'NIS', 'Kelas', 'Tahun Ajaran', 'Jenis Pembayaran', 'Nominal Tagihan', 'Dibayar', 'Status', 'Keterangan Cicilan/Transaksi'];
    $idx = 1;
    foreach ($siswaList as $siswa) {
        foreach ($detailPembayaranList as $dp) {
            $jenisNama = strtolower($dp->jenisPembayaran->nama ?? '');
            if ($dp->jenisPembayaran->tipe == 0 && (strpos($jenisNama, 'daftar ulang') !== false || $jenisNama == 'bebas')) {
                $du = DaftarUlang::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)->first();
                $duHistory = DaftarUlangHistory::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)->orderBy('tanggal_bayar')->get();

                $cicilanStr = '';
                foreach ($duHistory as $idxCicil => $cicil) {
                    $cicilanStr .= ($idxCicil + 1) . '. Rp ' . number_format($cicil->jumlah_bayar, 0, ',', '.') .
                        ' (' . ($cicil->status ?? '-') . ', ' . ($cicil->tanggal_bayar ?? '-') . ")\n";
                }
                if ($cicilanStr == '') $cicilanStr = 'Belum ada pembayaran';

                $statusTampil = ($du && $du->status == 'lunas') ? 'Lunas' : 'Belum';
                $keteranganTampil = ($du && $du->jumlah_bayar > 0 && $du->status != 'lunas')
                    ? 'Cicilan'
                    : (($du && $du->status == 'lunas') ? 'Lunas' : 'Belum Bayar');
                $keteranganAll = ($keteranganTampil == 'Cicilan') ? $cicilanStr : $keteranganTampil;

                // --- Ambil nama kelas dari historyKelas pada tahun ajaran tagihan
                $kelasNama = '-';
                $historyKelas = $siswa->historyKelas()
                    ->where('tahun_ajaran_id', $dp->tahun_ajaran_id)
                    ->first();
                if ($historyKelas && $historyKelas->kelas) {
                    $kelasNama = $historyKelas->kelas->nama_kelas ?? $historyKelas->kelas->nama ?? '-';
                }

                // ==== FILTER export: hanya tampil sesuai filter status pembayaran ====
                if ($statusPembayaranFilter) {
                    $nominal = $du ? $du->jumlah_bayar : 0;
                    $nominalTagihanBulan = $dp->nominal ?? 0;
                    $status = $du ? $du->status : 'belum';

                    if ($statusPembayaranFilter == 'belum') {
                        if ($status == 'lunas' || $nominal >= $nominalTagihanBulan) {
                            continue;
                        }
                    }
                    if ($statusPembayaranFilter == 'lunas') {
                        if ($status != 'lunas' || $nominal < $nominalTagihanBulan) {
                            continue;
                        }
                    }
                }

                $dataDaftarUlang[] = [
                    $idx++,
                    $siswa->nama,
                    $siswa->nis,
                    $kelasNama,
                    $dp->tahunAjaran->nama ?? '-',
                    $dp->jenisPembayaran->nama ?? '-',
                    'Rp ' . number_format($dp->nominal ?? 0, 0, ',', '.'),
                    'Rp ' . number_format($du ? $du->jumlah_bayar : 0, 0, ',', '.'),
                    $statusTampil,
                    $keteranganAll,
                ];
            }
        }
    }
    if (count($dataDaftarUlang) == 1) {
        $dataDaftarUlang[] = ['-', 'Tidak ada data sesuai filter', '', '', '', '', '', '', '', ''];
    }

    // ------------- TABUNGAN SHEET -------------
    $dataTabungan = [];
    $dataTabungan[] = ['No', 'Nama Siswa', 'NIS', 'Kelas', 'Tahun Ajaran', 'Jenis Pembayaran', 'Total Masuk', 'Total Keluar', 'Saldo Akhir'];
    $idx = 1;
    foreach ($siswaList as $siswa) {
        foreach ($detailPembayaranList as $dp) {
            $jenisNama = strtolower($dp->jenisPembayaran->nama ?? '');
            if (strpos($jenisNama, 'tabungan') !== false) {
                $setor = Tabungan::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)
                    ->where('jenis', 'setor')->sum('nominal');
                $tarik = Tabungan::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)
                    ->where('jenis', 'ambil')->sum('nominal');
                // --- Ambil nama kelas dari historyKelas pada tahun ajaran tagihan
                $kelasNama = '-';
                $historyKelas = $siswa->historyKelas()
                    ->where('tahun_ajaran_id', $dp->tahun_ajaran_id)
                    ->first();
                if ($historyKelas && $historyKelas->kelas) {
                    $kelasNama = $historyKelas->kelas->nama_kelas ?? $historyKelas->kelas->nama ?? '-';
                }
                $dataTabungan[] = [
                    $idx++,
                    $siswa->nama,
                    $siswa->nis,
                    $kelasNama,
                    $dp->tahunAjaran->nama ?? '-',
                    $dp->jenisPembayaran->nama ?? '-',
                    'Rp ' . number_format($setor, 0, ',', '.'),
                    'Rp ' . number_format($tarik, 0, ',', '.'),
                    'Rp ' . number_format($setor - $tarik, 0, ',', '.'),
                ];
            }
        }
    }
    if (count($dataTabungan) == 1) {
        $dataTabungan[] = ['-', 'Tidak ada data sesuai filter', '', '', '', '', '', '', ''];
    }

    // ----------- EXPORT MULTISHEET -----------
    return \Maatwebsite\Excel\Facades\Excel::download(
        new \App\Exports\LaporanPembayaranMultiSheetExport(
            $dataBulanan, $dataDaftarUlang, $dataTabungan
        ),
        'laporan_pembayaran_' . now()->format('Ymd_His') . '.xlsx'
    );
}

}
