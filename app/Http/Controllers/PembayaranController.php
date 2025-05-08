<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $siswa = null;
        $tahunAjaranList = [];

        $keyword = $request->input('keyword');

        if ($request->filled('keyword')) {
            $siswa = Siswa::with(['spp', 'pembayarans'])
                ->where('nisn', $keyword)
                ->orWhere('nis', $keyword)
                ->orWhere('nama', 'like', '%' . $keyword . '%')
                ->first();

            if (!$siswa) {
                return view('pembayaran.index')
                    ->with('message', 'Siswa tidak ditemukan.');
            }

            if ($siswa) {
                $tahunMasuk = $siswa->spp->tahun_ajaran;
                $tahunAwal = (int) substr($tahunMasuk, 0, 4);
                $bulanList = [
                    'Juli',
                    'Agustus',
                    'September',
                    'Oktober',
                    'November',
                    'Desember',
                    'Januari',
                    'Februari',
                    'Maret',
                    'April',
                    'Mei',
                    'Juni'
                ];

                for ($i = 0; $i < 6; $i++) {
                    $tahun1 = $tahunAwal + $i;
                    $tahun2 = $tahun1 + 1;
                    $tahunAjaran = "$tahun1/$tahun2";
                    $tahunAjaranKey = "$tahun1-$tahun2";

                    foreach ($bulanList as $index => $bulan) {
                        $status = 'belum';
                        $pembayaran = $siswa->pembayarans->firstWhere(function ($p) use ($tahunAjaran, $bulan) {
                            return $p->tahun_ajaran === $tahunAjaran && $p->bulan === $bulan;
                        });

                        if ($pembayaran) {
                            $status = $pembayaran->status;
                        }

                        $jatuhTempo = \Carbon\Carbon::createFromDate($tahun1, 7, 10)->addMonths($index);

                        $statusVisual = match (true) {
                            $status === 'lunas' => 'lunas',
                            $status === 'cicilan' => 'cicilan',
                            $status === 'belum' && $jatuhTempo->isPast() => 'nunggak',
                            default => 'belum'
                        };

                        $tahunAjaranList[$tahunAjaranKey][$bulan] = [
                            'statusVisual' => $statusVisual,
                            'link' => route('pembayaran.form', [
                                'nisn' => $siswa->nisn,
                                'tahunAjaran' => $tahunAjaranKey,
                                'bulan' => $bulan,
                            ]),
                        ];
                    }
                }
            }
        }

        return view('pembayaran.index', compact('siswa', 'tahunAjaranList'));
    }

    public function show($nisn)
    {
        $siswa = Siswa::with(['spp', 'pembayarans'])->where('nisn', $nisn)->firstOrFail();
        $tahunMasuk = $siswa->spp->tahun_ajaran;
        $tahunAwal = (int) substr($tahunMasuk, 0, 4);
        $bulanList = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
        $tahunAjaranList = [];

        for ($i = 0; $i < 6; $i++) {
            $tahun1 = $tahunAwal + $i;
            $tahun2 = $tahun1 + 1;
            $tahunAjaran = "$tahun1/$tahun2";
            $tahunAjaranKey = "$tahun1-$tahun2";

            foreach ($bulanList as $index => $bulan) {
                $status = 'belum';
                $pembayaran = $siswa->pembayarans->firstWhere(function ($p) use ($tahunAjaran, $bulan) {
                    return $p->tahun_ajaran === $tahunAjaran && $p->bulan === $bulan;
                });

                if ($pembayaran) {
                    $status = $pembayaran->status;
                }

                $jatuhTempo = \Carbon\Carbon::createFromDate($tahun1, 7, 10)->addMonths($index);

                $statusVisual = match (true) {
                    $status === 'lunas' => 'lunas',
                    $status === 'cicilan' => 'cicilan',
                    $status === 'belum' && $jatuhTempo->isPast() => 'nunggak',
                    default => 'belum'
                };

                $tahunAjaranList[$tahunAjaranKey][$bulan] = $statusVisual;
            }
        }

        return view('pembayaran.show', compact('siswa', 'tahunAjaranList'));
    }
}
