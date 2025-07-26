<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\tahunajaran;
use Illuminate\Http\Request;
use App\Models\jenispembayaran;
use App\Models\PengaturanKelas;
use App\Models\DetailPembayaran;
use App\Models\HistoryKelas;

class SppController extends Controller
{
    public function index()
    {
        $detailPembayarans = DetailPembayaran::with(['tahunAjaran', 'jenisPembayaran'])
                                ->latest()
                                ->paginate(20);

        $tahunAjarans = tahunajaran::all();
        $jenispembayarans = jenispembayaran::all();

        return view('spp.index', compact('detailPembayarans', 'tahunAjarans', 'jenispembayarans'));
    }

    public function daftarSiswa($id)
{
    $detail = DetailPembayaran::with(['tahunAjaran', 'jenisPembayaran'])->findOrFail($id);
    $angkatanMulai = $detail->angkatan_mulai;

    $historyKelasList = HistoryKelas::with(['siswa', 'kelas'])
        ->where('tahun_ajaran_id', $detail->tahun_ajaran_id)
        ->whereHas('siswa', function($query) use ($angkatanMulai) {
            $query->where('tahun_masuk', $angkatanMulai);
        })
        ->paginate(20);

    // Cek apakah ada siswa pindahan di tahun ajaran ini
    $siswaPindahanCount = Siswa::where('is_pindahan', 1)
        ->whereHas('historyKelas', function($q) use ($detail) {
            $q->where('tahun_ajaran_id', $detail->tahun_ajaran_id);
        })->count();

    // --- REVISI: GENERATE BULAN TAHUN AJARAN ---
    $mulai = $detail->tahunAjaran->mulai;
    $selesai = $detail->tahunAjaran->selesai;

    $bulanAjaran = [];
    if ($mulai && $selesai) {
        $start = new \DateTime($mulai);
        $end = new \DateTime($selesai);
        $end->modify('+1 month');
        $period = new \DatePeriod($start, new \DateInterval('P1M'), $end);

        foreach ($period as $dt) {
            $bulanAjaran[] = [
                'value' => $dt->format('Y-m'),
                'label' => $dt->format('M Y'),
                'bulan' => $dt->format('n'),
                'tahun' => $dt->format('Y')
            ];
        }
    }
    // --- END: REVISI ---

    // --- OTOMATIS DETEKSI JENIS PEMBAYARAN ---
    $namaJenis = strtolower($detail->jenisPembayaran->nama);
    $isTabungan = str_contains($namaJenis, 'tabungan');
    $isDaftarUlang = str_contains($namaJenis, 'daftar ulang');

    // Untuk tabungan: hitung total tabungan per siswa (TIDAK per bulan)
    if ($isTabungan) {
        foreach ($historyKelasList as $item) {
            $totalTabungan = $item->siswa->tabungan()
                ->where('detail_pembayaran_id', $detail->id)
                ->sum('nominal');
            $item->totalTabungan = $totalTabungan;
        }
    }

    return view('spp.daftar_siswa', [
        'detail' => $detail,
        'historyKelasList' => $historyKelasList,
        'bulanAjaran' => $bulanAjaran,
        'siswaPindahanCount' => $siswaPindahanCount,
        'isTabungan' => $isTabungan,
        'isDaftarUlang' => $isDaftarUlang,
    ]);
}

    public function setBulanMulai(Request $request, $detail_id)
    {
        $request->validate([
            'bulan_mulai' => 'required|integer|min:1|max:12',
            'siswa_id' => 'required|exists:siswas,id', // siswa_id wajib
        ]);

        $detail = DetailPembayaran::findOrFail($detail_id);

        // Pastikan siswa adalah pindahan
        $siswa = Siswa::where('id', $request->siswa_id)->where('is_pindahan', 1)->first();
        if (!$siswa) {
            return back()->with('error', 'Hanya siswa pindahan yang bisa diatur bulan mulai.');
        }

        // Update history_kelas untuk siswa pindahan tersebut
        $updated = HistoryKelas::where('tahun_ajaran_id', $detail->tahun_ajaran_id)
            ->where('siswa_id', $request->siswa_id)
            ->update(['bulan_mulai' => $request->bulan_mulai]);

        if ($updated) {
            return back()->with('success', 'Bulan mulai pembayaran khusus siswa pindahan diperbarui.');
        } else {
            return back()->with('error', 'Data siswa tidak ditemukan pada history kelas tahun ajaran ini.');
        }
    }

    public function cariSiswa(Request $request)
    {
        $q = $request->q;
        if (!$q || strlen($q) < 3) return response()->json([]);

        $siswa = Siswa::with('kelas')
            ->where('is_pindahan', 1) // filter hanya siswa pindahan!
            ->where('nama', 'like', '%' . $q . '%')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'kelas' => $item->kelas->nama_kelas ?? '-'
                ];
            });

        return response()->json($siswa);
    }
}
