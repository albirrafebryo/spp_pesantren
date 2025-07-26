<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Pembayaran;
use App\Models\DaftarUlang;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\jenispembayaran;
use App\Models\DetailPembayaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today()->toDateString();

        // Cari tahun ajaran dimana hari ini di antara mulai dan selesai
        $tahunAjaran = TahunAjaran::where('mulai', '<=', $today)
            ->where('selesai', '>=', $today)
            ->first();

        $tahunAjaranNama = $tahunAjaran ? $tahunAjaran->nama : '-';
        // Card lain
        $totalKelas = Kelas::count();
        $totalSantri = Siswa::count();
        $totalDataPembayaran = JenisPembayaran::count();
        $totalBendahara = User::role('petugas')->count();
        $totalWaliSantri = User::role('wali')->count();

        // ======== TOTAL SPP ========
        $totalSPP = Pembayaran::whereHas('detailPembayaran.jenisPembayaran', function($q) {
             $q->where('nama', 'LIKE', 'SPP%'); // Pastikan di DB nama jenis pembayaran SPP = 'SPP'
        })
        ->whereIn('status', ['lunas', 'cicilan'])
        ->sum('jumlah_bayar');

        // Card lain: Total Laundry
        $totalLaundry = Pembayaran::whereHas('detailPembayaran.jenisPembayaran', function($q) {
            $q->where('nama', 'Laundry');
        })
        ->whereIn('status', ['lunas', 'cicilan'])
        ->sum('jumlah_bayar');

        // Card lain: Total Tabungan
        $totalTabungan = Pembayaran::whereHas('detailPembayaran.jenisPembayaran', function($q) {
            $q->where('nama', 'Tabungan');
        })
        ->whereIn('status', ['lunas', 'cicilan'])
        ->sum('jumlah_bayar');
$totalTagihanWali = 0;
    $tagihanWali = [
        'SPP'         => 0,
        'Laundry'     => 0,
        'Daftar Ulang'=> 0,
    ];

    if (Auth::check() && Auth::user()->hasRole('wali')) {
        $anakList = Siswa::where('wali_id', Auth::id())->get();

        // Loop per anak dan per kategori pembayaran
        foreach ($anakList as $anak) {
            // SPP (bulanan)
            $detailSPP = DetailPembayaran::whereHas('jenisPembayaran', function($q) {
                $q->where('nama', 'like', 'SPP%');
            })
            ->where('angkatan_mulai', $anak->tahun_masuk)
            ->when($tahunAjaran, function($q) use ($tahunAjaran) {
                $q->where('tahun_ajaran_id', $tahunAjaran->id);
            })
            ->first();

            if ($detailSPP) {
                $bulanAjaran = 12; // default 12 bulan, sesuaikan jika perlu
                $totalTagihanSPP = $detailSPP->nominal * $bulanAjaran;
                $totalDibayarSPP = Pembayaran::where('siswa_id', $anak->id)
                    ->where('detail_pembayaran_id', $detailSPP->id)
                    ->where('tahun_ajaran_id', $detailSPP->tahun_ajaran_id)
                    ->whereIn('status', ['lunas', 'cicilan'])
                    ->sum('jumlah_bayar');
                $sisaSPP = max(0, $totalTagihanSPP - $totalDibayarSPP);
                $tagihanWali['SPP'] += $sisaSPP;
                $totalTagihanWali += $sisaSPP;
            }

            // Laundry
            $detailLaundry = DetailPembayaran::whereHas('jenisPembayaran', function($q) {
                $q->where('nama', 'like', 'Laundry%');
            })
            ->where('angkatan_mulai', $anak->tahun_masuk)
            ->when($tahunAjaran, function($q) use ($tahunAjaran) {
                $q->where('tahun_ajaran_id', $tahunAjaran->id);
            })
            ->first();

            if ($detailLaundry) {
                $bulanAjaran = 12;
                if ($detailLaundry->jenisPembayaran->tipe == 1) {
                $totalTagihanLaundry = $detailLaundry->nominal * $bulanAjaran;
                } else {
                $totalTagihanLaundry = $detailLaundry->nominal;
                }

                $totalDibayarLaundry = Pembayaran::where('siswa_id', $anak->id)
                    ->where('detail_pembayaran_id', $detailLaundry->id)
                    ->where('tahun_ajaran_id', $detailLaundry->tahun_ajaran_id)
                    ->whereIn('status', ['lunas', 'cicilan'])
                    ->sum('jumlah_bayar');
                $sisaLaundry = max(0, $totalTagihanLaundry - $totalDibayarLaundry);
                $tagihanWali['Laundry'] += $sisaLaundry;
                $totalTagihanWali += $sisaLaundry;
                }

            // Daftar Ulang (ambil dari tabel DaftarUlang, bukan Pembayaran)
            $detailDaftarUlang = DetailPembayaran::whereHas('jenisPembayaran', function($q) {
                $q->where('nama', 'like', 'Daftar Ulang%');
            })
            ->where('angkatan_mulai', $anak->tahun_masuk)
            ->when($tahunAjaran, function($q) use ($tahunAjaran) {
                $q->where('tahun_ajaran_id', $tahunAjaran->id);
            })
            ->first();

            if ($detailDaftarUlang) {
                $du = DaftarUlang::where('siswa_id', $anak->id)
                    ->where('detail_pembayaran_id', $detailDaftarUlang->id)
                    ->where('tahun_ajaran_id', $detailDaftarUlang->tahun_ajaran_id)
                    ->first();

                $totalTagihanDU = $detailDaftarUlang->nominal;
                $totalDibayarDU = $du ? $du->jumlah_bayar : 0;
                $sisaDU = max(0, $totalTagihanDU - $totalDibayarDU);
                $tagihanWali['Daftar Ulang'] += $sisaDU;
                $totalTagihanWali += $sisaDU;
            }
        }
    }

    // ===============================

    // Kirim variabel ke view
    return view('dashboard', [
        'tahunAjaran'         => $tahunAjaranNama,
        'totalKelas'          => $totalKelas,
        'totalSantri'         => $totalSantri,
        'totalDataPembayaran' => $totalDataPembayaran,
        'totalBendahara'      => $totalBendahara,
        'totalWaliSantri'     => $totalWaliSantri,
        'totalSPP'            => $totalSPP,
        'totalLaundry'        => $totalLaundry,
        'totalTabungan'       => $totalTabungan,

        // TAMBAHAN untuk dashboard wali
        'totalTagihanWali'    => $totalTagihanWali,
        'tagihanWali'         => $tagihanWali,
    ]);
}
}
