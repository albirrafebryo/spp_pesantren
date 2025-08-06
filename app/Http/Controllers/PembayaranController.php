<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
    use App\Models\Siswa;
    use App\Models\Kelas;
    use App\Models\Tabungan;
    use App\Models\Pembayaran;
    use App\Models\TahunAjaran;
    use App\Models\HistoryKelas;
    use Illuminate\Http\Request;
    use App\Models\BuktiPembayaran;
    use App\Models\JenisPembayaran;
    use App\Models\DetailPembayaran;
    use App\Models\PembayaranHistory;
    use App\Models\DaftarUlang;
    use App\Models\DaftarUlangHistory;
    use Illuminate\Support\Facades\DB;

    use Illuminate\Support\Facades\Auth;

    class PembayaranController extends Controller
    {
        protected function denyIfAdmin($message = 'Admin tidak diizinkan melakukan aksi ini.')
{
    if (Auth::user()->hasRole('admin')) {
        abort(403, $message);
    }
}
        public function index(Request $request)
    {
        $siswa = null;
        $siswas = null;
        $tabelPembayaran = [];
        $keranjang = [];
        $bulanan = [];
        $bebas = [];
        $tabungan = [];
        $headersPetugas = [];

        // ==== MODE WALI ====
        if (Auth::user()->hasRole('wali')) {
            $siswas = Siswa::where('wali_id', Auth::user()->id)
                ->with('pembayarans')
                ->get();

            $siswa = $siswas->first();

            if ($siswa) {
               $totalCicilanBulanan = 0;
    if ($siswa) {
        $jenisBulanan = ['spp', 'laundry'];
        $pembayaranCicilan = Pembayaran::where('siswa_id', $siswa->id)
            ->where('status', 'cicilan')
            ->whereHas('detailPembayaran.jenisPembayaran', function($q) use ($jenisBulanan) {
            })
            ->get();

$jenisBulanan = ['spp', 'laundry'];
 $totalCicilanPerJenis = [];
    foreach ($jenisBulanan as $jenis) {
        $total = Pembayaran::where('siswa_id', $siswa->id)
            ->where('status', 'cicilan')
            ->whereHas('detailPembayaran.jenisPembayaran', function($q) use ($jenis) {
                $q->whereRaw('LOWER(nama) LIKE ?', ['%' . strtolower($jenis) . '%']);
            })
            ->sum('jumlah_bayar');
        $totalCicilanPerJenis[$jenis] = $total;
    }
}

                $historyKelas = HistoryKelas::where('siswa_id', $siswa->id)
                    ->with('tahunAjaran')
                    ->orderBy('tahun_ajaran_id')
                    ->get();

                $daftarTahunAjaran = $historyKelas->pluck('tahunAjaran.nama')->unique()->values();
                $detailPembayaran = DetailPembayaran::with(['tahunAjaran', 'jenisPembayaran'])->get();
                $dataPembayaran = $siswa->pembayarans;

                foreach ($daftarTahunAjaran as $tahunAjaran) {
                    $detailPembayaranTA = $detailPembayaran->filter(function ($dp) use ($tahunAjaran, $siswa) {
                        return ($dp->tahunAjaran->nama == $tahunAjaran) && ($dp->angkatan_mulai == $siswa->tahun_masuk);
                    });

                    $taModel = $detailPembayaranTA->first() ? $detailPembayaranTA->first()->tahunAjaran : null;
                    if (!$taModel) continue;

                    $mulaiDate = new \DateTime($taModel->mulai);
                    $akhirDate = new \DateTime($taModel->selesai);
                    $bulanAjaran = [];
                    $bulanPointer = clone $mulaiDate;

                    while ($bulanPointer <= $akhirDate) {
                        $bulanAjaran[] = [
                            'bulan' => (int)$bulanPointer->format('n'),
                            'tahun' => (int)$bulanPointer->format('Y'),
                            'label' => $bulanPointer->format('M Y'),
                            'label_short' => $bulanPointer->format('M'),
                        ];
                        $bulanPointer->modify('+1 month');
                    }

                    $tabelPembayaran[] = [
                        'is_header' => true,
                        'tahun_ajaran' => $tahunAjaran,
                        'bulanAjaran' => $bulanAjaran
                    ];

                    foreach ($detailPembayaranTA as $dp) {
                        $historyKelasSiswa = $historyKelas->first(function ($hk) use ($dp) {
                            return $hk->tahun_ajaran_id == $dp->tahun_ajaran_id;
                        });
                        $bulanMulai = $historyKelasSiswa ? $historyKelasSiswa->bulan_mulai : ($dp->bulan_mulai ?? (int)$mulaiDate->format('n'));
                        $tahunMulai = $historyKelasSiswa ? (int)date('Y', strtotime($historyKelasSiswa->created_at ?? $taModel->mulai)) : (int)$mulaiDate->format('Y');

                        $startIndex = 0;
                        foreach ($bulanAjaran as $i => $b) {
                            if ($b['bulan'] == $bulanMulai && $b['tahun'] == $tahunMulai) {
                                $startIndex = $i;
                                break;
                            }
                        }

                        $row = [
                            'is_header' => false,
                            'tahun_ajaran' => $tahunAjaran,
                            'jenis_pembayaran' => $dp->jenisPembayaran->nama,
                            'detail_pembayaran_id' => $dp->id,
                            'bulanAjaran' => $bulanAjaran,
                            'bulans' => []
                        ];

                        foreach ($bulanAjaran as $iBulan => $b) {
                            $bulanKe = $b['bulan'];
                            $tahunKe = $b['tahun'];
                            $namaBulan = $b['label_short'];

                            if ($iBulan < $startIndex) {
                                $row['bulans']["$bulanKe-$tahunKe"] = [
                                    'status' => 'kosong',
                                    'nominal' => 0,
                                    'dibayar' => 0,
                                    'bulan_ke' => $bulanKe,
                                    'tahun_ke' => $tahunKe,
                                    'nama_bulan' => $namaBulan,
                                ];
                                continue;
                            }

                            $pembayaranBulanIni = $dataPembayaran->first(function ($p) use ($dp, $bulanKe, $tahunKe) {
                                return $p->detail_pembayaran_id == $dp->id && $p->bulan == $bulanKe &&
                                    (isset($p->tahun_ajaran_id) && isset($dp->tahun_ajaran_id) ? $p->tahun_ajaran_id == $dp->tahun_ajaran_id : true);
                            });

                            $nominal = $dp->nominal ?? 0;
                            $dibayar = $pembayaranBulanIni->jumlah_bayar ?? 0;
                            $status = $pembayaranBulanIni->status ?? 'belum';

                            $now = now();
                            $bulanSekarang = intval($now->format('n'));
                            $tahunSekarang = intval($now->format('Y'));
                            $isTabungan = false;
                            if (isset($dp->jenisPembayaran)) {
                                $isTabungan = stripos($dp->jenisPembayaran->nama, 'tabungan') !== false;
                            }
                            if ($isTabungan) {
                                $visualStatus = 'setor';
                            } elseif ($status === 'lunas') {
                                $visualStatus = 'lunas';
                            } elseif ($status === 'cicilan') {
                                $visualStatus = 'cicilan';
                            } elseif ($status === 'pending') {
                                $visualStatus = 'pending';
                            } elseif ($status === 'belum') {
                                if (($tahunKe < $tahunSekarang) || ($tahunKe == $tahunSekarang && $bulanKe < $bulanSekarang)) {
                                    $visualStatus = 'nunggak';
                                } else {
                                    $visualStatus = 'belum';
                                }
                            } else {
                                $visualStatus = 'belum';
                            }

                            $row['bulans']["$bulanKe-$tahunKe"] = [
                                'status' => $visualStatus,
                                'nominal' => $nominal,
                                'dibayar' => $dibayar,
                                'bulan_ke' => $bulanKe,
                                'tahun_ke' => $tahunKe,
                                'nama_bulan' => $namaBulan,
                            ];
                        }
                        $tabelPembayaran[] = $row;
                    }
                }

                // MAPPING DATA BULANAN UNTUK TABEL
                foreach ($tabelPembayaran as $row) {
                    if (!empty($row['is_header'])) {
                        $headersPetugas[] = $row;
                        continue;
                    }
                    $jenis = JenisPembayaran::where('nama', $row['jenis_pembayaran'])->first();
                    if ($jenis && $jenis->tipe == 1) {
                        $bulanan[] = $row;
                    }
                }

                // --------- MAPPING DATA BEBAS ---------
                $detailBebas = DetailPembayaran::with(['tahunAjaran', 'jenisPembayaran'])
                    ->where('angkatan_mulai', $siswa->tahun_masuk)
                    ->whereHas('jenisPembayaran', function($q) {
                        $q->where('tipe', 0)->where('nama', 'not like', 'tabungan%');
                    })
                    ->get();

                foreach ($detailBebas as $dp) {
                    $trans = DaftarUlang::where('siswa_id', $siswa->id)
                        ->where('detail_pembayaran_id', $dp->id)
                        ->first();
                    $bebas[] = [
                        'tahun_ajaran'       => $dp->tahunAjaran->nama ?? '',
                        'jenis_pembayaran'   => $dp->jenisPembayaran->nama ?? '',
                        'detail_pembayaran_id' => $dp->id,
                        'nominal'            => $dp->nominal ?? 0,
                        'dibayar'            => $trans ? $trans->jumlah_bayar : 0,
                        'status'             => $trans ? $trans->status : 'belum',
                    ];
                }

                // --------- MAPPING DATA TABUNGAN ---------
                $detailsTabungan = DetailPembayaran::with(['tahunAjaran', 'jenisPembayaran'])
                    ->whereHas('jenisPembayaran', fn($q) => $q->where('nama', 'like', 'tabungan%'))
                    ->where('angkatan_mulai', $siswa->tahun_masuk)
                    ->get();

                foreach ($detailsTabungan as $dp) {
                    $setor = Tabungan::where('siswa_id', $siswa->id)
                        ->where('detail_pembayaran_id', $dp->id)
                        ->where('jenis', 'setor')
                        ->sum('nominal');
                    $tarik = Tabungan::where('siswa_id', $siswa->id)
                        ->where('detail_pembayaran_id', $dp->id)
                        ->where('jenis', 'ambil')
                        ->sum('nominal');
                    $tabungan[] = [
                        'tahun_ajaran' => $dp->tahunAjaran->nama,
                        'jenis_pembayaran' => $dp->jenisPembayaran->nama,
                        'saldo' => $setor - $tarik,
                        'detail_pembayaran_id' => $dp->id,
                    ];
                }
            }
            
            // BALIKKAN KE VIEW SAMA DENGAN PETUGAS
            return view('pembayaran.index', compact(
        'siswas', 'siswa', 'tabelPembayaran',
        'bulanan', 'bebas', 'tabungan', 'headersPetugas', 'keranjang',
        'totalCicilanBulanan', 'totalCicilanPerJenis'
    ));
        }

        // ==== END MODE WALI ====

        // ==== MODE PETUGAS ====
        $keyword = $request->input('keyword');

        if (Auth::user()->hasRole('petugas') || Auth::user()->hasRole('admin')) {
            if (!$keyword) {
                return view('pembayaran.index', compact(
                    'siswa', 'tabelPembayaran', 'keranjang',
                    'bulanan', 'bebas', 'tabungan', 'headersPetugas'
                ));
            }
            $siswa = Siswa::with('pembayarans')->where(function ($q) use ($keyword) {
                $q->where('nisn', $keyword)
                    ->orWhere('nis', $keyword)
                    ->orWhere('nama', 'like', '%' . $keyword . '%');
            })->first();

            if (!$siswa) {
                return view('pembayaran.index', compact(
                    'siswa', 'tabelPembayaran', 'keranjang',
                    'bulanan', 'bebas', 'tabungan', 'headersPetugas','totalCicilanBulanan'
                ))->with('message', 'Siswa tidak ditemukan.');
            }
             $siswa = Siswa::with('pembayarans.detailPembayaran.jenisPembayaran')->where(function ($q) use ($keyword) {
        $q->where('nisn', $keyword)
            ->orWhere('nis', $keyword)
            ->orWhere('nama', 'like', '%' . $keyword . '%');
    })->first();
            $totalCicilanBulanan = 0;
    if ($siswa) {
        $jenisBulanan = ['spp', 'laundry'];
        $pembayaranCicilan = Pembayaran::where('siswa_id', $siswa->id)
            ->where('status', 'cicilan')
            ->whereHas('detailPembayaran.jenisPembayaran', function($q) use ($jenisBulanan) {
            })
            ->get();

        $jenisBulanan = ['spp', 'laundry'];
 $totalCicilanPerJenis = [];
    foreach ($jenisBulanan as $jenis) {
        $total = Pembayaran::where('siswa_id', $siswa->id)
            ->where('status', 'cicilan')
            ->whereHas('detailPembayaran.jenisPembayaran', function($q) use ($jenis) {
                $q->whereRaw('LOWER(nama) LIKE ?', ['%' . strtolower($jenis) . '%']);
            })
            ->sum('jumlah_bayar');
        $totalCicilanPerJenis[$jenis] = $total;
    }
    }
            $historyKelas = HistoryKelas::where('siswa_id', $siswa->id)
                ->with('tahunAjaran')
                ->orderBy('tahun_ajaran_id')
                ->get();

            $daftarTahunAjaran = $historyKelas->pluck('tahunAjaran.nama')->unique()->values();
            $detailPembayaran = DetailPembayaran::with(['tahunAjaran', 'jenisPembayaran'])->get();
            $dataPembayaran = $siswa->pembayarans;

            foreach ($daftarTahunAjaran as $tahunAjaran) {
                $detailPembayaranTA = $detailPembayaran->filter(function ($dp) use ($tahunAjaran, $siswa) {
                    return ($dp->tahunAjaran->nama == $tahunAjaran) && ($dp->angkatan_mulai == $siswa->tahun_masuk);
                });

                $taModel = $detailPembayaranTA->first() ? $detailPembayaranTA->first()->tahunAjaran : null;
                if (!$taModel) continue;

                $mulaiDate = new \DateTime($taModel->mulai);
                $akhirDate = new \DateTime($taModel->selesai);
                $bulanAjaran = [];
                $bulanPointer = clone $mulaiDate;

                while ($bulanPointer <= $akhirDate) {
                    $bulanAjaran[] = [
                        'bulan' => (int)$bulanPointer->format('n'),
                        'tahun' => (int)$bulanPointer->format('Y'),
                        'label' => $bulanPointer->format('M Y'),
                        'label_short' => $bulanPointer->format('M'),
                    ];
                    $bulanPointer->modify('+1 month');
                }

                $tabelPembayaran[] = [
                    'is_header' => true,
                    'tahun_ajaran' => $tahunAjaran,
                    'bulanAjaran' => $bulanAjaran
                ];

                foreach ($detailPembayaranTA as $dp) {
                    $historyKelasSiswa = $historyKelas->first(function ($hk) use ($dp) {
                        return $hk->tahun_ajaran_id == $dp->tahun_ajaran_id;
                    });

                    $bulanMulai = $historyKelasSiswa ? $historyKelasSiswa->bulan_mulai : ($dp->bulan_mulai ?? (int)$mulaiDate->format('n'));
                    $tahunMulai = $historyKelasSiswa ? (int)date('Y', strtotime($historyKelasSiswa->created_at ?? $taModel->mulai)) : (int)$mulaiDate->format('Y');

                    $startIndex = 0;
                    foreach ($bulanAjaran as $i => $b) {
                        if ($b['bulan'] == $bulanMulai && $b['tahun'] == $tahunMulai) {
                            $startIndex = $i;
                            break;
                        }
                    }

                    $row = [
                        'is_header' => false,
                        'tahun_ajaran' => $tahunAjaran,
                        'jenis_pembayaran' => $dp->jenisPembayaran->nama,
                        'detail_pembayaran_id' => $dp->id,
                        'bulanAjaran' => $bulanAjaran,
                        'bulans' => []
                    ];

                    foreach ($bulanAjaran as $iBulan => $b) {
                        $bulanKe = $b['bulan'];
                        $tahunKe = $b['tahun'];
                        $namaBulan = $b['label_short'];

                        if ($iBulan < $startIndex) {
                            $row['bulans']["$bulanKe-$tahunKe"] = [
                                'status' => 'kosong',
                                'nominal' => 0,
                                'dibayar' => 0,
                                'bulan_ke' => $bulanKe,
                                'tahun_ke' => $tahunKe,
                                'nama_bulan' => $namaBulan,
                            ];
                            continue;
                        }

                        $pembayaranBulanIni = $dataPembayaran->first(function ($p) use ($dp, $bulanKe, $tahunKe) {
                            return $p->detail_pembayaran_id == $dp->id && $p->bulan == $bulanKe &&
                                (isset($p->tahun_ajaran_id) && isset($dp->tahun_ajaran_id) ? $p->tahun_ajaran_id == $dp->tahun_ajaran_id : true);
                        });

                        $nominal = $dp->nominal ?? 0;
                        $dibayar = $pembayaranBulanIni->jumlah_bayar ?? 0;
                        $status = $pembayaranBulanIni->status ?? 'belum';

                        $now = now();
                        $bulanSekarang = intval($now->format('n'));
                        $tahunSekarang = intval($now->format('Y'));
                        $isTabungan = false;
                        if (isset($dp->jenisPembayaran)) {
                            $isTabungan = stripos($dp->jenisPembayaran->nama, 'tabungan') !== false;
                        }
                        if ($isTabungan) {
                            $visualStatus = 'setor';
                        } elseif ($status === 'lunas') {
                            $visualStatus = 'lunas';
                        } elseif ($status === 'cicilan') {
                            $visualStatus = 'cicilan';
                        } elseif ($status === 'pending') {
                            $visualStatus = 'pending';
                        } elseif ($status === 'belum') {
                            if (($tahunKe < $tahunSekarang) || ($tahunKe == $tahunSekarang && $bulanKe < $bulanSekarang)) {
                                $visualStatus = 'nunggak';
                            } else {
                                $visualStatus = 'belum';
                            }
                        } else {
                            $visualStatus = 'belum';
                        }

                        $row['bulans']["$bulanKe-$tahunKe"] = [
                            'status' => $visualStatus,
                            'nominal' => $nominal,
                            'dibayar' => $dibayar,
                            'bulan_ke' => $bulanKe,
                            'tahun_ke' => $tahunKe,
                            'nama_bulan' => $namaBulan,
                        ];
                    }
                    $tabelPembayaran[] = $row;
                }
            }

            // Mapping bulanan, headers, bebas, tabungan untuk petugas
            foreach ($tabelPembayaran as $row) {
                if (!empty($row['is_header'])) {
                    $headersPetugas[] = $row;
                    continue;
                }
                $jenis = JenisPembayaran::where('nama', $row['jenis_pembayaran'])->first();
                if ($jenis && $jenis->tipe == 1) {
                    $bulanan[] = $row;
                }
            }

            // --------- MAPPING DATA BEBAS ---------
            $detailBebas = DetailPembayaran::with(['tahunAjaran', 'jenisPembayaran'])
                ->where('angkatan_mulai', $siswa->tahun_masuk)
                ->whereHas('jenisPembayaran', function($q) {
                    $q->where('tipe', 0)->where('nama', 'not like', 'tabungan%');
                })
                ->get();

            foreach ($detailBebas as $dp) {
                $trans = DaftarUlang::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)
                    ->first();
                $bebas[] = [
                    'tahun_ajaran'       => $dp->tahunAjaran->nama ?? '',
                    'jenis_pembayaran'   => $dp->jenisPembayaran->nama ?? '',
                    'detail_pembayaran_id' => $dp->id,
                    'nominal'            => $dp->nominal ?? 0,
                    'dibayar'            => $trans ? $trans->jumlah_bayar : 0,
                    'status'             => $trans ? $trans->status : 'belum',
                ];
            }

            // --------- MAPPING DATA TABUNGAN ---------
            $detailsTabungan = DetailPembayaran::with(['tahunAjaran', 'jenisPembayaran'])
                ->whereHas('jenisPembayaran', fn($q) => $q->where('nama', 'like', 'tabungan%'))
                ->where('angkatan_mulai', $siswa->tahun_masuk)
                ->get();

            foreach ($detailsTabungan as $dp) {
                $setor = Tabungan::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)
                    ->where('jenis', 'setor')
                    ->sum('nominal');
                $tarik = Tabungan::where('siswa_id', $siswa->id)
                    ->where('detail_pembayaran_id', $dp->id)
                    ->where('jenis', 'ambil')
                    ->sum('nominal');
                $tabungan[] = [
                    'tahun_ajaran' => $dp->tahunAjaran->nama,
                    'jenis_pembayaran' => $dp->jenisPembayaran->nama,
                    'saldo' => $setor - $tarik,
                    'detail_pembayaran_id' => $dp->id,
                ];
            }

            // KERANJANG PENDING KHUSUS PETUGAS
            $detailPembayaran = DetailPembayaran::with(['tahunAjaran', 'jenisPembayaran'])->get();

            foreach ($detailPembayaran as $dp) {
                for ($bulan = 1; $bulan <= 12; $bulan++) {
                    $pembayaran = Pembayaran::where([
                        'siswa_id' => $siswa->id,
                        'detail_pembayaran_id' => $dp->id,
                        'tahun_ajaran_id' => $dp->tahun_ajaran_id,
                        'bulan' => $bulan,
                    ])->first();

                    $bulanLabel = isset($dp->tahunAjaran)
                        ? date('M Y', strtotime($dp->tahunAjaran->mulai . ' +' . ($bulan - 1) . ' month'))
                        : 'Bulan ' . $bulan;

                    // === HANYA MASUKKAN YANG PENDING SAJA! ===
                    if ($pembayaran && $pembayaran->status == 'pending') {
                        $bukti = $pembayaran->buktiPembayarans()->latest()->first();
                        $keranjang[] = [
                            'tahun'      => $dp->tahunAjaran->nama ?? '',
                            'jenis'      => $dp->jenisPembayaran->nama ?? '',
                            'bulan'      => $bulan,
                            'bulanLabel' => $bulanLabel,
                            'nominal'    => $dp->nominal ?? 0,
                            'status'     => 'pending',
                            'cicilan'    => $pembayaran->jumlah_bayar,
                            'dibayar'    => $pembayaran->jumlah_bayar,
                            'sisa'       => ($dp->nominal ?? 0) - ($pembayaran->jumlah_bayar ?? 0),
                            'detailId'   => $dp->id,
                            'buktiUrl'   => $bukti ? asset('storage/' . $bukti->bukti) : null,
                            'buktiId'   => $bukti ? $bukti->id : null,
                        ];
                    }
                }
            }

            return view('pembayaran.index', compact(
        'siswas', 'siswa', 'tabelPembayaran',
        'bulanan', 'bebas', 'tabungan', 'headersPetugas', 'keranjang',
        'totalCicilanBulanan','totalCicilanPerJenis',
    ));
        }
        abort(403, 'Akses tidak diizinkan');
    }

        public function livesearchSiswa(Request $request)
        {
            $keyword = $request->input('keyword');
            if (!$keyword || strlen($keyword) < 2) {
                return response()->json([]);
            }

            $siswas = Siswa::where('nisn', 'like', "%$keyword%")
                ->orWhere('nis', 'like', "%$keyword%")
                ->orWhere('nama', 'like', "%$keyword%")
                ->limit(10)
                ->get(['id', 'nama', 'nisn', 'nis']);

            return response()->json($siswas);
        }

        public function bayar(Request $request)
        {
            $siswa = Siswa::findOrFail($request->input('siswa_id'));
            $detail = DetailPembayaran::findOrFail($request->input('detail_pembayaran_id'));
            $bulan = $request->input('bulan');
            $tahunAjaran = str_replace('-', '/', $request->input('tahunAjaran'));

            $pembayaran = Pembayaran::where([
                'siswa_id' => $siswa->id,
                'detail_pembayaran_id' => $detail->id,
                'tahun_ajaran_id' => $detail->tahun_ajaran_id,
                'bulan' => $bulan,
            ])->first();

            $nominalTagihan = $detail->nominal ?? 0;

            return view('pembayaran.form', compact('siswa', 'detail', 'bulan', 'tahunAjaran', 'pembayaran', 'nominalTagihan'));
        }

    public function checkout(Request $request)
    {
        $this->denyIfAdmin();
        $isWali = Auth::user()->hasRole('wali');
        $rules = [
            'siswa_id' => 'required|exists:siswas,id',
            'items' => 'required|array|min:1',
            'items.*.tahun_ajaran' => 'required|string',
            'items.*.jenis' => 'required|string',
            'items.*.cicilan' => 'required|numeric|min:1',
        ];
        if ($isWali) {
            $rules['bukti_pembayaran'] = 'required';
            $rules['bukti_pembayaran.*'] = 'file|mimes:jpg,jpeg,png,pdf|max:2048';
        }
        $request->validate($rules);

        $siswa = Siswa::findOrFail($request->siswa_id);
        $totalBayar = 0;
        $pembayaranIds = [];

        // Ambil daftar ulang (tipe 0) di tabel jenis pembayaran
        $jenisDaftarUlang = JenisPembayaran::where('tipe', 0)
            ->where('nama', 'like', 'daftar ulang%')
            ->pluck('nama')->map(fn($n) => strtolower($n))->toArray();

        foreach ($request->items as $idx => $item) {
            $jenis = strtolower($item['jenis']);
            $isBulanan = !in_array($jenis, array_merge($jenisDaftarUlang, ['tabungan', 'bebas']));

            // Validasi untuk bulanan harus ada bulan
            if ($isBulanan && empty($item['bulan'])) {
                return back()->withErrors(["items.$idx.bulan" => 'Field bulan wajib diisi untuk pembayaran bulanan.'])->withInput();
            }

            // TABUNGAN
            if ($jenis === 'tabungan' || stripos($jenis, 'tabungan') !== false) {
                try {
                    Tabungan::create([
    'siswa_id'              => $siswa->id,
    'detail_pembayaran_id'  => $item['detail_pembayaran_id'], // WAJIB ADA!
    'tanggal'               => now(),
    'jenis'                 => $item['keterangan'] ?? 'setor',
    'nominal'               => abs($item['cicilan']),
    'user_id'               => Auth::id(),
    'keterangan'            => $item['keterangan'] ?? null,
    'status'                => 'valid',
]);
                    $totalBayar += abs($item['cicilan']);
                } catch (\Exception $e) {
                    // \Log::error('Gagal simpan tabungan', ['error' => $e->getMessage(), 'item' => $item]);
                    return back()->withErrors(['msg' => 'Gagal simpan tabungan'])->withInput();
                }
                continue;
            }

            // --- DAFTAR ULANG ---
            if (in_array($jenis, $jenisDaftarUlang)) {
                // Cari detail pembayaran & tahun ajaran
                $detail = DetailPembayaran::with('tahunAjaran', 'jenisPembayaran')
                    ->whereHas('tahunAjaran', function ($q) use ($item) {
                        $q->where('nama', $item['tahun_ajaran']);
                    })
                    ->whereHas('jenisPembayaran', function ($q) use ($item) {
                        $q->where('nama', $item['jenis']);
                    })
                    ->first();

                if (!$detail) {
                    return back()->withErrors(["items.$idx" => "Detail pembayaran untuk daftar ulang tidak ditemukan."]);
                }

                $tahunAjaranModel = TahunAjaran::where('nama', $item['tahun_ajaran'])->first();
                if (!$tahunAjaranModel) {
                    return back()->withErrors(["items.$idx" => "Tahun ajaran tidak ditemukan."]);
                }

                $jumlah = (int)$item['cicilan'];
                $nominalTagihan = $detail->nominal ?? 0;

                // Store daftar ulang (sesuaikan field dengan tabel migration)
                $du = DaftarUlang::firstOrNew([
                    'siswa_id' => $siswa->id,
                    'tahun_ajaran_id' => $tahunAjaranModel->id,
                    'detail_pembayaran_id' => $detail->id,
                ]);
                $du->jumlah_tagihan = $nominalTagihan;
                $du->jumlah_bayar = ($du->jumlah_bayar ?? 0) + $jumlah;
                $du->status = ($du->jumlah_bayar >= $nominalTagihan) ? 'lunas' : 'cicilan';
                $du->save();

                // Store daftar ulang history
                DaftarUlangHistory::create([
                    'daftar_ulang_id' => $du->id,
                    'siswa_id' => $siswa->id,
                    'tahun_ajaran_id' => $tahunAjaranModel->id,
                    'detail_pembayaran_id' => $detail->id,
                    'jumlah_bayar' => $jumlah,
                    'status' => $du->status,
                    'keterangan' => ($du->status == 'lunas') ? 'Pelunasan' : 'Cicilan',
                    'tanggal_bayar' => now(),
                    'user_id' => Auth::id(),
                ]);

                $totalBayar += $jumlah;
                continue;
            }

            // PEMBAYARAN BULANAN
            if (empty($item['bulan'])) {
                return back()->withErrors(["items.$idx.bulan" => 'Field bulan wajib diisi untuk pembayaran bulanan.'])->withInput();
            }

            try {
                $detail = DetailPembayaran::with('tahunAjaran', 'jenisPembayaran')
                    ->whereHas('tahunAjaran', fn($q) => $q->where('nama', $item['tahun_ajaran']))
                    ->whereHas('jenisPembayaran', fn($q) => $q->where('nama', $item['jenis']))
                    ->first();
                if (!$detail) {
                    // \Log::warning('Detail pembayaran tidak ditemukan', $item);
                    continue;
                }

                $bulan = (int)$item['bulan'];
                $jumlah = (int)$item['cicilan'];
                $nominalTagihan = $detail->nominal ?? 0;

                $pembayaran = Pembayaran::firstOrNew([
                    'siswa_id' => $siswa->id,
                    'detail_pembayaran_id' => $detail->id,
                    'tahun_ajaran_id' => $detail->tahun_ajaran_id,
                    'bulan' => $bulan,
                ]);

                $jumlahLama = $pembayaran->jumlah_bayar ?? 0;
                $jumlahTotal = $jumlahLama + $jumlah;

                if ($jumlahTotal > $nominalTagihan) {
                    // \Log::warning('Pembayaran melebihi nominal tagihan', ['total' => $jumlahTotal, 'tagihan' => $nominalTagihan]);
                    continue;
                }

                $pembayaran->jumlah_bayar = $jumlahTotal;
                $pembayaran->jumlah_tagihan = $nominalTagihan;
                $pembayaran->status = $isWali
                    ? 'pending'
                    : ($jumlahTotal >= $nominalTagihan ? 'lunas' : 'cicilan');
                $pembayaran->save();

                $pembayaranIds[] = $pembayaran->id;

                $countValid = PembayaranHistory::where('pembayaran_id', $pembayaran->id)
                    ->whereIn('status', ['cicilan', 'lunas'])
                    ->count();

                $histStatus = $isWali
                    ? 'pending'
                    : ($pembayaran->status == 'lunas' ? 'lunas' : 'cicilan');
                $ket = $histStatus == 'lunas'
                    ? 'Pelunasan'
                    : 'Cicilan ke-' . ($countValid + 1);

                PembayaranHistory::create([
                    'pembayaran_id'        => $pembayaran->id,
                    'siswa_id'             => $siswa->id,
                    'detail_pembayaran_id' => $detail->id,
                    'tahun_ajaran_id'      => $detail->tahun_ajaran_id,
                    'bulan'                => $bulan,
                    'jumlah_bayar'         => $jumlah,
                    'status'               => $histStatus,
                    'keterangan'           => $ket,
                    'tanggal_bayar'        => now(),
                    'user_id'              => Auth::id(),
                ]);

                $totalBayar += $jumlah;
            } catch (\Exception $e) {
                // \Log::error('Gagal simpan pembayaran bulanan', ['error' => $e->getMessage(), 'item' => $item]);
                return back()->withErrors(['msg' => 'Gagal simpan pembayaran bulanan'])->withInput();
            }
        }

        // Simpan bukti jika wali
        if ($isWali && $request->hasFile('bukti_pembayaran')) {
            try {
                $files = $request->file('bukti_pembayaran');
                foreach ($pembayaranIds as $pembayaran_id) {
                    foreach ($files as $file) {
                        $ext = $file->getClientOriginalExtension();
                        $path = $file->storeAs('bukti_pembayaran', uniqid() . '.' . $ext, 'public');
                        BuktiPembayaran::create([
                            'pembayaran_id' => $pembayaran_id,
                            'user_id' => Auth::id(),
                            'bukti' => $path,
                            'status' => 'pending',
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // \Log::error('Gagal simpan bukti pembayaran', ['error' => $e->getMessage()]);
                return back()->withErrors(['msg' => 'Gagal simpan bukti pembayaran'])->withInput();
            }
        }

        return redirect()->route('pembayaran.index', [
            'keyword' => $siswa->nisn
        ])->with([
            'waiting_validation' => true
        ]);
    }

    public function verifikasiBukti(Request $request, $bukti_id)
    {
         if (!Auth::user()->hasRole('petugas')) {
        abort(403, 'Akses hanya untuk petugas.');
    }

        $request->validate([
            'status' => 'required|in:valid,invalid',
            'catatan_verifikasi' => 'nullable|string'
        ]);

        $bukti      = BuktiPembayaran::findOrFail($bukti_id);
        $pembayaran = $bukti->pembayaran;

        // 1) Update status bukti
        $bukti->status             = $request->status;
        $bukti->catatan_verifikasi = $request->catatan_verifikasi;
        $bukti->diverifikasi_oleh  = Auth::id();
        $bukti->tanggal_verifikasi = now();
        $bukti->save();

        if ($bukti->status === 'invalid') {
            // 2a) Cari history terakhir yang statusnya "pending" (atau yang berkaitan dengan bukti ini)
            $lastHistory = PembayaranHistory::where('pembayaran_id', $pembayaran->id)
                ->orderByDesc('id')
                ->first();

            // Tandai history ini invalid
            if ($lastHistory) {
                $lastHistory->status = 'invalid';
                $lastHistory->keterangan = 'Ditolak';
                $lastHistory->save();
            }

            // 2b) Apakah history yang ditolak adalah "lunas" (pelunasan)?
            $isPelunasan = $lastHistory && (
                strtolower($lastHistory->keterangan) == 'pelunasan' ||
                $lastHistory->status == 'lunas'
            );

            if ($isPelunasan) {
                // Jika pelunasan, reset ke 0
                $pembayaran->jumlah_bayar = 0;
                $pembayaran->status = 'belum';
            } else {
                // Jika cicilan, hitung ulang jumlah_bayar dari history valid
                $totalValid = PembayaranHistory::where('pembayaran_id', $pembayaran->id)
                    ->whereIn('status', ['cicilan', 'lunas'])
                    ->sum('jumlah_bayar');
                $pembayaran->jumlah_bayar = $totalValid;
                if ($totalValid >= $pembayaran->jumlah_tagihan) {
                    $pembayaran->status = 'lunas';
                } elseif ($totalValid > 0) {
                    $pembayaran->status = 'cicilan';
                } else {
                    $pembayaran->status = 'belum';
                }
            }
            $pembayaran->save();
        } else {
            // 3a) Update pembayaran jadi lunas atau cicilan (validasi diterima)
            $isFull = $pembayaran->jumlah_bayar >= $pembayaran->jumlah_tagihan;
            $pembayaran->status = $isFull ? 'lunas' : 'cicilan';
            $pembayaran->save();

            // 3b) Simpan entry history untuk validasi
            if ($isFull) {
                $histStatus = 'lunas';
                $ket        = 'Pelunasan';
            } else {
                // hitung berapa cicilan valid sebelumnya
                $countCicilan = PembayaranHistory::where('pembayaran_id', $pembayaran->id)
                                ->where('status', 'cicilan')
                                ->count();
                $histStatus = 'cicilan';
                $ket        = 'Cicilan ke-'.($countCicilan+1);
            }

            PembayaranHistory::create([
                'pembayaran_id'        => $pembayaran->id,
                'siswa_id'             => $pembayaran->siswa_id,
                'detail_pembayaran_id' => $pembayaran->detail_pembayaran_id,
                'tahun_ajaran_id'      => $pembayaran->tahun_ajaran_id,
                'bulan'                => $pembayaran->bulan,
                'jumlah_bayar'         => $pembayaran->jumlah_bayar,
                'status'               => $histStatus,
                'keterangan'           => $ket,
                'tanggal_bayar'        => now(),
                'user_id'              => Auth::id(),
            ]);
        }

        // 4) Response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Bukti diverifikasi sebagai '.$bukti->status);
    }

   public function updateStatus(Request $request)
{
    $this->denyIfAdmin();
    try {
        $detail = DetailPembayaran::find($request->detail_pembayaran_id);
        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail pembayaran tidak ditemukan!'
            ], 422);
        }
        $jenisPembayaran = $detail ? strtolower($detail->jenisPembayaran->nama) : '';
        $isTabungan = strpos($jenisPembayaran, 'tabungan') !== false;

        $isBebasAtauDaftarUlang =
            (strpos($jenisPembayaran, 'bebas') !== false) ||
            (strpos($jenisPembayaran, 'daftar ulang') !== false);

        if ($isBebasAtauDaftarUlang) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak boleh proses pembayaran bebas/daftar ulang lewat endpoint ini.'
            ], 422);
        }

        $rules = [
            'siswa_id' => 'required|exists:siswas,id',
            'detail_pembayaran_id' => 'required|exists:detail_pembayarans,id',
            'tahunAjaran' => 'required',
            'jumlah' => 'required|numeric|min:1',
            'keterangan' => 'nullable|in:setor,ambil',
        ];
        if (!$isTabungan) $rules['bulan'] = 'required|integer|min:1|max:12';
        $validated = $request->validate($rules);

        $siswa = Siswa::findOrFail($request->siswa_id);

        $kelasNama = '-';
if ($siswa->kelas && $siswa->kelas->nama_kelas) {
    $kelasNama = $siswa->kelas->nama_kelas;
} else {
    $kelasTerakhir = \App\Models\HistoryKelas::where('siswa_id', $siswa->id)
        ->with('kelas')
        ->orderByDesc('tahun_ajaran_id')
        ->orderByDesc('id')
        ->first();
    if ($kelasTerakhir && $kelasTerakhir->kelas && $kelasTerakhir->kelas->nama_kelas) {
        $kelasNama = $kelasTerakhir->kelas->nama_kelas;
    }
}

        // Proses TABUNGAN
        if ($isTabungan) {
            $keterangan = $request->input('keterangan', 'setor');
            if (!in_array($keterangan, ['setor', 'ambil'])) $keterangan = 'setor';
            Tabungan::create([
    'siswa_id'              => $siswa->id,
    'detail_pembayaran_id'  => $request['detail_pembayaran_id'], // WAJIB ADA!
    'tanggal'               => now(),
    'jenis'                 => $request['keterangan'] ?? 'setor',
    'nominal'               => abs($request->jumlah),
    'user_id'               => Auth::id(),
    'keterangan'            => $request['keterangan'] ?? null,
    'status'                => 'valid',
]);
            return response()->json([
                'success' => true,
                'message' => 'Tabungan berhasil disimpan.',
                'no_hp'   => $this->formatHp($siswa->no_hp),
                'nama_siswa' => $siswa->nama,
                'nis' => $siswa->nis,
                'kelas_terakhir' => $kelasNama,
                'petugas' => Auth::user()->name ?? '-',
                'no_bukti' => null,
            ]);
        }

        // Proses BULANAN
        $pembayaran = Pembayaran::firstOrNew([
            'siswa_id' => $siswa->id,
            'detail_pembayaran_id' => $detail->id,
            'tahun_ajaran_id' => $detail->tahun_ajaran_id,
            'bulan' => $request->bulan,
        ]);
        $jumlahLama = $pembayaran->jumlah_bayar ?? 0;
        $jumlahBaru = $request->jumlah;
        $jumlahTotal = $jumlahLama + $jumlahBaru;
        $nominalTagihan = $detail->nominal ?? 0;

        if ($jumlahTotal > $nominalTagihan) {
            Log::warning('Pembayaran melebihi tagihan', [
                'jumlahTotal' => $jumlahTotal, 'nominalTagihan' => $nominalTagihan
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Total pembayaran melebihi nominal tagihan.'
            ], 422);
        }

        $pembayaran->jumlah_bayar = $jumlahTotal;
        $pembayaran->jumlah_tagihan = $nominalTagihan;
        $pembayaran->status = $jumlahTotal >= $nominalTagihan ? 'lunas' : 'cicilan';
        $pembayaran->save();

        PembayaranHistory::create([
            'pembayaran_id'        => $pembayaran->id,
            'siswa_id'             => $siswa->id,
            'detail_pembayaran_id' => $detail->id,
            'tahun_ajaran_id'      => $detail->tahun_ajaran_id,
            'bulan'                => $request->bulan,
            'jumlah_bayar'         => $jumlahBaru,
            'status'               => $pembayaran->status,
            'keterangan'           => $pembayaran->status == 'lunas' ? 'Pelunasan' : 'Cicilan',
            'tanggal_bayar'        => now(),
            'user_id'              => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status dan jumlah pembayaran berhasil diperbarui.',
            'no_hp'   => $this->formatHp($siswa->no_hp),
            'nama_siswa' => $siswa->nama,
            'nis' => $siswa->nis,
            'kelas_terakhir' => $kelasNama,
            'petugas' => Auth::user()->name ?? '-',
            'no_bukti' => $pembayaran->id,
        ]);
    } catch (\Exception $e) {
        Log::error('ERROR updateStatus', [
            'msg' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Gagal simpan: '.$e->getMessage()
        ], 500);
    }
}

// Helper untuk format no hp
protected function formatHp($noHp)
{
    $noHp = trim($noHp);
    if (substr($noHp, 0, 1) === '0') {
        return '62' . substr($noHp, 1);
    }
    return $noHp;
}

    public function storeDaftarUlang(Request $request)
{
    $this->denyIfAdmin();
    Log::info('Masuk ke StoreDaftarUlang', $request->all());
    $request->validate([
        'siswa_id' => 'required|exists:siswas,id',
        'tahun_ajaran' => 'required|string',
        'jenis' => 'required|string',
        'cicilan' => 'required|numeric|min:1',
    ]);

    $siswa = Siswa::findOrFail($request->siswa_id);
    $tahunAjaranModel = TahunAjaran::where('nama', $request->tahun_ajaran)->first();
    if (!$tahunAjaranModel) {
        return response()->json(['success' => false, 'message' => 'Tahun ajaran tidak ditemukan'], 422);
    }

    $detail = DetailPembayaran::with('tahunAjaran', 'jenisPembayaran')
        ->whereHas('tahunAjaran', function ($q) use ($request) {
            $q->where('nama', $request->tahun_ajaran);
        })
        ->whereHas('jenisPembayaran', function ($q) use ($request) {
            $q->where('nama', $request->jenis);
        })
        ->first();

    if (!$detail) {
        return response()->json(['success' => false, 'message' => 'Detail pembayaran tidak ditemukan'], 422);
    }

    $jumlah = (int)$request->cicilan;
    $nominalTagihan = $detail->nominal ?? 0;

    // Ambil data atau buat baru jika belum ada
    $du = DaftarUlang::firstOrNew([
        'siswa_id' => $siswa->id,
        'tahun_ajaran_id' => $tahunAjaranModel->id,
        'detail_pembayaran_id' => $detail->id,
    ]);
    
    // Jika field jumlah_bayar belum ada, default ke 0
    if (is_null($du->jumlah_bayar)) $du->jumlah_bayar = 0;
    if (is_null($du->jumlah_tagihan)) $du->jumlah_tagihan = $nominalTagihan;

    // **Tambah cicilan**
    $du->jumlah_bayar = ($du->jumlah_bayar ?? 0) + $jumlah;
    $du->jumlah_tagihan = $nominalTagihan;
    $du->status = ($du->jumlah_bayar >= $nominalTagihan) ? 'lunas' : 'cicilan';
    $du->save();

    // Pastikan save DaftarUlangHistory
    DaftarUlangHistory::create([
        'daftar_ulang_id' => $du->id,
        'siswa_id' => $siswa->id,
        'tahun_ajaran_id' => $tahunAjaranModel->id,
        'detail_pembayaran_id' => $detail->id,
        'jumlah_bayar' => $jumlah,
        'status' => $du->status,
        'keterangan' => ($du->status == 'lunas') ? 'Pelunasan' : 'Cicilan',
        'tanggal_bayar' => now(),
        'user_id' => Auth::id(),
    ]);

    // --- Kirim response ke JS untuk kebutuhan WhatsApp ---
    return response()->json([
        'success' => true,
        'message' => 'Pembayaran daftar ulang berhasil',
        'no_hp' => (substr($siswa->no_hp, 0, 1) === '0' ? '62' . substr($siswa->no_hp, 1) : $siswa->no_hp),
        'nama_siswa' => $siswa->nama,
        'nis' => $siswa->nis,
        'kelas_terakhir' => $siswa->kelas ? $siswa->kelas->nama : '-',
        'petugas' => Auth::user()->name ?? '-',
        'no_bukti' => $du->id, // Bisa ganti ke DaftarUlangHistory terakhir jika perlu
    ]);
}

        public function setorTarikTabungan(Request $request)
    {
        if (!Auth::user()->hasRole('petugas')) {
        abort(403, 'Akses hanya untuk petugas.');
    }
        // Validasi request
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'detail_pembayaran_id' => 'required|exists:detail_pembayarans,id',
            'nominal' => 'required|numeric|min:1',
            'jenis' => 'required|in:setor,ambil',
        ]);

        // Hitung saldo terakhir
        $setor = Tabungan::where('siswa_id', $validated['siswa_id'])
            ->where('detail_pembayaran_id', $validated['detail_pembayaran_id'])
            ->where('jenis', 'setor')
            ->sum('nominal');
        $tarik = Tabungan::where('siswa_id', $validated['siswa_id'])
            ->where('detail_pembayaran_id', $validated['detail_pembayaran_id'])
            ->where('jenis', 'ambil')
            ->sum('nominal');
        $saldo = $setor - $tarik;

        // Jika mau tarik, cek saldo cukup atau tidak
        if ($validated['jenis'] == 'ambil' && $validated['nominal'] > $saldo) {
            return back()->with('error', 'Saldo tabungan tidak cukup untuk ditarik.');
        }

        // Simpan ke tabel tabungan
        Tabungan::create([
            'siswa_id'             => $validated['siswa_id'],
            'detail_pembayaran_id' => $validated['detail_pembayaran_id'],
            'tanggal'              => now(),
            'jenis'                => $validated['jenis'],
            'nominal'              => abs($validated['nominal']),
            'status'               => 'valid',
            'keterangan'           => $request->input('keterangan', null),
            'user_id'              => Auth::id(),
        ]);

        return back()->with('success', 'Tabungan berhasil '.($validated['jenis']=='setor' ? 'disetor' : 'ditarik'));
    }
        
        public function destroy($id)
{
    // Hanya admin & petugas yang boleh hapus pembayaran
    if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('petugas')) {
        abort(403, 'Akses hanya untuk admin/petugas.');
    }

    $pembayaran = Pembayaran::findOrFail($id);
    $pembayaran->delete();

    return redirect()->back()->with('success', 'Data pembayaran berhasil dihapus.');
}

public function deletePembayaran(Request $request)
{
    if (!Auth::user()->hasRole('admin')) {
        return response()->json(['success' => false, 'message' => 'Akses hanya untuk admin!'], 403);
    }

    $request->validate([
        'items' => 'required|array|min:1',
        'items.*.detail_pembayaran_id' => 'required|integer|exists:detail_pembayarans,id',
        'items.*.tahun' => 'required|string',
        'items.*.bulan' => 'nullable',
    ]);

    DB::beginTransaction();
    try {
        foreach ($request->items as $item) {
            // --- 1. Hapus Bulanan (cicilan bulanan, sudah dijelaskan sebelumnya) ---
            $pembayaran = Pembayaran::where('detail_pembayaran_id', $item['detail_pembayaran_id'])
                ->when(!empty($item['bulan']), function($q) use ($item) {
                    $q->where('bulan', $item['bulan']);
                })->first();

            if ($pembayaran) {
                $lastHistory = PembayaranHistory::where('pembayaran_id', $pembayaran->id)
                    ->whereIn('status', ['cicilan', 'lunas'])
                    ->orderByDesc('id')
                    ->first();

                if ($lastHistory) {
                    $pembayaran->jumlah_bayar = max(0, ($pembayaran->jumlah_bayar ?? 0) - ($lastHistory->jumlah_bayar ?? 0));
                    if ($pembayaran->jumlah_bayar >= $pembayaran->jumlah_tagihan) {
                        $pembayaran->status = 'lunas';
                    } elseif ($pembayaran->jumlah_bayar > 0) {
                        $pembayaran->status = 'cicilan';
                    } else {
                        $pembayaran->status = 'belum';
                    }
                    $pembayaran->save();
                    $lastHistory->delete();
                } else {
                    $pembayaran->delete();
                }
            }

            // --- 2. Hapus Daftar Ulang (Bebas/Daftar Ulang) ---
            // Cari data DaftarUlang utama
            $tahunAjaran = $item['tahun'];
            $detailId = $item['detail_pembayaran_id'];
            $du = DaftarUlang::where('detail_pembayaran_id', $detailId)
                ->whereHas('tahunAjaran', function($q) use ($tahunAjaran) {
                    $q->where('nama', $tahunAjaran);
                })->first();

            if ($du) {
                // Hapus history cicilan/angsuran terakhir
                $lastHistory = DaftarUlangHistory::where('daftar_ulang_id', $du->id)
                    ->orderByDesc('id')
                    ->first();

                if ($lastHistory) {
                    // Kurangi jumlah_bayar di DaftarUlang utama
                    $du->jumlah_bayar = max(0, ($du->jumlah_bayar ?? 0) - ($lastHistory->jumlah_bayar ?? 0));
                    // Update status (lunas/cicilan/belum)
                    if ($du->jumlah_bayar >= ($du->jumlah_tagihan ?? 0)) {
                        $du->status = 'lunas';
                    } elseif ($du->jumlah_bayar > 0) {
                        $du->status = 'cicilan';
                    } else {
                        $du->status = 'belum';
                    }
                    $du->save();

                    // Hapus history terakhir
                    $lastHistory->delete();
                } else {
                    // Jika tidak ada history, hapus data utama
                    $du->delete();
                }
            }

            // --- 3. (Jika ada tabungan atau jenis lain, tambahkan logika di sini sesuai kebutuhan) ---
        }

        DB::commit();
        return response()->json(['success' => true, 'message' => 'Cicilan terakhir pembayaran/daftar ulang berhasil dihapus!']);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Gagal hapus: '.$e->getMessage()], 500);
    }
}

    }
