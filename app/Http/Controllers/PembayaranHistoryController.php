<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Pembayaran;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use App\Models\jenispembayaran;
use App\Models\TabunganHistory;
use App\Models\DetailPembayaran;
use App\Models\PembayaranHistory;

class PembayaranHistoryController extends Controller
{
    public function index() { }

    public function rekap(Request $request)
    {
        $tahunAjaranId = $request->input('tahun_ajaran_id');
        $kelasId = $request->input('kelas_id');
        $jenisPembayaranId = $request->input('jenis_pembayaran');
        $status = $request->input('status', 'lunas');

        $daftarTahunAjaran = TahunAjaran::all();
        $daftarKelas = $tahunAjaranId
            ? Kelas::whereHas('historyKelas', function($q) use($tahunAjaranId){
                $q->where('tahun_ajaran_id', $tahunAjaranId);
            })->get()
            : [];

        $jenisPembayaranList = jenispembayaran::all();
        $selectedJenisPembayaran = $jenisPembayaranId ?? ($jenisPembayaranList->first()->id ?? null);

        // Ambil rekap hanya satu baris per siswa per jenis pembayaran
        $rekapList = Siswa::whereHas('historyKelas', function($q) use($tahunAjaranId, $kelasId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId);
            if ($kelasId) $q->where('kelas_id', $kelasId);
        })
        ->with(['pembayarans.detailPembayaran.jenisPembayaran', 'historyKelas.kelas'])
        ->get()
        ->map(function($siswa) use ($tahunAjaranId, $selectedJenisPembayaran) {
            // Ambil semua pembayaran siswa ini pada tahun ajaran & jenis pembayaran terpilih
            $pembayarans = $siswa->pembayarans
                ->filter(function($p) use ($tahunAjaranId, $selectedJenisPembayaran) {
                    return $p->tahun_ajaran_id == $tahunAjaranId
                        && $p->detailPembayaran->jenis_pembayaran_id == $selectedJenisPembayaran;
                });

            if ($pembayarans->isEmpty()) return null;

            $sudahDibayar = $pembayarans->sum('jumlah_bayar');
            $jumlahTagihan = $pembayarans->sum('jumlah_tagihan');
            $sisa = max($jumlahTagihan - $sudahDibayar, 0);
            $status = $sisa == 0 && $jumlahTagihan > 0 ? 'lunas' : ($sudahDibayar > 0 ? 'cicilan' : 'belum');

            return (object)[
                'siswa' => $siswa,
                'kelas' => $siswa->historyKelas->where('tahun_ajaran_id', $tahunAjaranId)->first()->kelas ?? null,
                'jenis_pembayaran' => $pembayarans->first()->detailPembayaran->jenisPembayaran->nama ?? '',
                'jumlah_tagihan' => $jumlahTagihan,
                'sudah_dibayar' => $sudahDibayar,
                'sisa' => $sisa,
                'status' => $status,
            ];
        })
        ->filter()
        ->values();

        return view('pembayaran.rekap', compact(
            'daftarTahunAjaran', 'daftarKelas', 'rekapList', 'jenisPembayaranList'
        ));
    }

    public function rekapDetail(Request $request)
{
    $siswa_id = $request->input('siswa_id');
    $tahun_ajaran_id = $request->input('tahun_ajaran_id');
    $jenis_pembayaran_id = $request->input('jenis_pembayaran_id');
    $kelas_id = $request->input('kelas_id'); // Optional

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

    // --- Ambil detail pembayaran ---
    $dp = DetailPembayaran::where('tahun_ajaran_id', $tahun_ajaran_id)
        ->where('jenis_pembayaran_id', $jenis_pembayaran_id)
        ->where('angkatan_mulai', $siswa->tahun_masuk)
        ->with(['jenisPembayaran', 'tahunAjaran'])
        ->first();

    $result = [];
    $tipePembayaran = null;

    if ($dp) {
        $jenis = $dp->jenisPembayaran;
        $tipe = $jenis->tipe ?? 1;

        // --- TABUNGAN ---
        if ($tipe == 3 || (stripos($jenis->nama, 'tabungan') !== false)) {
    $tipePembayaran = 'tabungan';

    // Ambil semua transaksi setor/ambil tabungan dari tabel Tabungan
    $historiTabungan = \App\Models\Tabungan::where('siswa_id', $siswa_id)
        ->where('detail_pembayaran_id', $dp->id)
        ->orderBy('tanggal')
        ->get();

    $saldo = 0;
    $historyArr = [];
    $totalSetor = 0;
    $totalTarik = 0;
    foreach ($historiTabungan as $row) {
        if ($row->jenis == 'setor') {
            $saldo += $row->nominal;
            $totalSetor += $row->nominal;
        } else {
            $saldo -= $row->nominal;
            $totalTarik += $row->nominal;
        }
        $historyArr[] = [
            'tanggal' => $row->tanggal ? Carbon::parse($row->tanggal)->format('d/m/Y') : '-',
            'masuk'   => $row->jenis == 'setor' ? $row->nominal : 0,
            'keluar'  => $row->jenis == 'ambil' ? $row->nominal : 0,
            'saldo'   => $saldo,
        ];
    }

    // Jika history kosong, tetap kirim 1 row dummy agar tabel di frontend tidak error
    if (empty($historyArr)) {
        $historyArr[] = [
            'tanggal' => '-',
            'masuk' => 0,
            'keluar' => 0,
            'saldo' => 0
        ];
    }

    $result[] = [
        'nama_jenis' => $jenis->nama,
        'saldo_akhir' => $saldo,
        'total_masuk' => $totalSetor,
        'total_keluar' => $totalTarik,
        'history' => $historyArr
    ];
}
        // --- BULANAN ---
        else if ($tipe == 1) {
            $tipePembayaran = 'bulanan';

            $namaBulanArr = [
                1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
            ];
            $ta = $dp->tahunAjaran;
            $periodeMulai = $ta->mulai ? (int)date('n', strtotime($ta->mulai)) : 7;
            $periodeAkhir = $ta->selesai ? (int)date('n', strtotime($ta->selesai)) : 6;
            $tahunAwal = intval(substr($ta->nama, 0, 4));
            $tahunAkhir = intval(substr($ta->nama, 5, 4));
            $bulanList = [];
            $bulan = $periodeMulai;
            $tahun = ($bulan >= 7) ? $tahunAwal : $tahunAkhir;
            do {
                $bulanList[] = [
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'nama' => $namaBulanArr[$bulan],
                    'ta'   => $ta->nama
                ];
                $bulan++;
                if ($bulan > 12) {
                    $bulan = 1;
                    $tahun++;
                }
            } while (!($bulan == (($periodeAkhir % 12) + 1) && $tahun == $tahunAkhir));

            $pembayaranPerBulan = Pembayaran::where('siswa_id', $siswa_id)
                ->where('detail_pembayaran_id', $dp->id)
                ->orderBy('bulan')
                ->get();

            $detail = [];
            foreach ($bulanList as $b) {
                $bulan = $b['bulan'];
                $tahun = $b['tahun'];
                $namaBulan = $b['nama'];
                $tahunAjaranStr = $b['ta'];

                $pembayaran = $pembayaranPerBulan->where('bulan', $bulan)->first();

                if ($pembayaran) {
                    $status = $pembayaran->status ?? 'belum';
                    $cicilanList = PembayaranHistory::where('pembayaran_id', $pembayaran->id)
                        ->orderBy('tanggal_bayar')
                        ->get()
                        ->map(function($cicil) {
                            return [
                                'jumlah_bayar' => $cicil->jumlah_bayar,
                                'status' => $cicil->status,
                                'tanggal_bayar' => $cicil->tanggal_bayar
                                    ? Carbon::parse($cicil->tanggal_bayar)->format('d/m/Y')
                                    : '-',
                                'keterangan' => $cicil->keterangan,
                            ];
                        })
                        ->toArray();

                    $keterangan = '';
                    if ($status == 'lunas') {
                        $keterangan = count($cicilanList) > 1
                            ? 'Lunas via cicilan (' . count($cicilanList) . 'x)'
                            : 'Lunas langsung';
                    } elseif ($status == 'cicilan') {
                        $keterangan = 'Masih nyicil (' . count($cicilanList) . 'x)';
                    } else {
                        $keterangan = 'Belum bayar';
                    }

                    $detail[] = [
                        'bulan' => $namaBulan . ' ' . $tahun . ' (' . $tahunAjaranStr . ')',
                        'tanggal_bayar' => $cicilanList ? implode(', ', array_column($cicilanList, 'tanggal_bayar')) : null,
                        'nominal' => $pembayaran->jumlah_bayar ?? 0,
                        'status' => $status,
                        'keterangan' => $keterangan,
                        'cicilan_list' => $cicilanList
                    ];
                } else {
                    $detail[] = [
                        'bulan' => $namaBulan . ' ' . $tahun . ' (' . $tahunAjaranStr . ')',
                        'tanggal_bayar' => null,
                        'nominal' => 0,
                        'status' => 'belum',
                        'keterangan' => 'Belum bayar',
                        'cicilan_list' => []
                    ];
                }
            }

            $result[] = [
                'nama_jenis' => $jenis->nama,
                'detail' => $detail
            ];
        }

        // --- BEBAS/DAFTAR ULANG ---
        else {
            $tipePembayaran = 'bebas';
            $pembayaranBebas = \App\Models\DaftarUlang::where('siswa_id', $siswa_id)
                ->where('detail_pembayaran_id', $dp->id)
                ->first();

            $nominal = $dp->nominal ?? 0;
            $dibayar = $pembayaranBebas ? $pembayaranBebas->jumlah_bayar : 0;
            $status = $pembayaranBebas ? $pembayaranBebas->status : 'belum';

            $detail = [[
                'bulan' => '-',
                'tanggal_bayar' => $pembayaranBebas && $pembayaranBebas->updated_at
                    ? Carbon::parse($pembayaranBebas->updated_at)->format('d/m/Y')
                    : '-',
                'nominal' => $dibayar,
                'status' => $status,
                'keterangan' => $status == 'lunas' ? 'Lunas' : ($status == 'cicilan' ? 'Belum lunas' : 'Belum bayar'),
                'cicilan_list' => []
            ]];

            $result[] = [
                'nama_jenis' => $jenis->nama,
                'detail' => $detail
            ];
        }
    }

    $tahunAjaran = TahunAjaran::find($tahun_ajaran_id);

    return response()->json([
        'siswa' => [
            'nama' => $siswa->nama,
            'kelas' => $kelas,
            'tahun_ajaran' => $tahunAjaran->nama ?? '-',
        ],
        'detail' => $result,
        'tipe_pembayaran' => $tipePembayaran
    ]);
}

}
