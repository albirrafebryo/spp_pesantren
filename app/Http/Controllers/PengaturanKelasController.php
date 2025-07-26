<?php

namespace App\Http\Controllers;

use App\Models\PengaturanKelas;
use App\Models\HistoryKelas;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class PengaturanKelasController extends Controller
{
    // Halaman utama pengaturan kelas (naik kelas massal)
    public function index(Request $request)
    {
        $tahunAjarans = TahunAjaran::all();
        $kelas = Kelas::all();

        $tahun_ajaran_id = $request->tahun_ajaran_id ?? '';
        $kelas_id = $request->kelas_id ?? '';
        $tahun_ajaran_baru_id = $request->tahun_ajaran_baru_id ?? '';
        
        $siswa_naik = collect();

        if ($tahun_ajaran_id && $kelas_id) {
            $pengaturanKelas = PengaturanKelas::with(['siswa', 'kelas'])
                ->where('tahun_ajaran_id', $tahun_ajaran_id)
                ->where('kelas_id', $kelas_id)
                ->get();

            $siswa_lama = $pengaturanKelas->map(function ($peng) {
                $siswa = $peng->siswa;
                $siswa->Kelas = $peng->kelas;
                $siswa->pengaturan_status = $peng->status;
                $siswa->pengaturan_keterangan = $peng->keterangan;
                return $siswa;
            });

            $ids_lama = $siswa_lama->pluck('id')->toArray();
            $siswa_baru = Siswa::with('kelas')
                ->where('kelas_id', $kelas_id)
                ->whereNotIn('id', $ids_lama)
                ->orderBy('nama')
                ->get()
                ->map(function ($siswa) {
                    $siswa->pengaturan_status = null;
                    $siswa->pengaturan_keterangan = null;
                    return $siswa;
                });

            $siswa_naik = $siswa_lama->merge($siswa_baru)->values();

            if ($tahun_ajaran_baru_id) {
                $siswa_naik = $siswa_naik->filter(function ($siswa) use ($tahun_ajaran_baru_id) {
                    return !PengaturanKelas::where('siswa_id', $siswa->id)
                        ->where('tahun_ajaran_id', $tahun_ajaran_baru_id)
                        ->exists();
                })->values();
            }
        }

        return view('pengaturan_kelas.index', compact(
            'tahunAjarans', 'kelas',
            'tahun_ajaran_id', 'kelas_id', 'tahun_ajaran_baru_id',
            'siswa_naik'
        ));
    }

    // Proses naik kelas massal
    public function prosesNaikKelasMassal(Request $request)
    {
        $request->validate([
            'tahun_ajaran_baru_id' => 'required|exists:tahun_ajarans,id',
            'kelas_baru_id' => 'required|exists:kelas,id',
            'siswa_ids' => 'required|array'
        ]);

        $sukses = 0; $duplikat = 0;
        
        foreach ($request->siswa_ids as $siswa_id) {
            $cek = PengaturanKelas::where('siswa_id', $siswa_id)
                ->where('tahun_ajaran_id', $request->tahun_ajaran_baru_id)
                ->first();
            if (!$cek) {
                $pengaturan = PengaturanKelas::create([
                    'siswa_id' => $siswa_id,
                    'kelas_id' => $request->kelas_baru_id,
                    'tahun_ajaran_id' => $request->tahun_ajaran_baru_id,
                    'status' => 'aktif'
                ]);
                Siswa::where('id', $siswa_id)->update(['kelas_id' => $request->kelas_baru_id]);
                $sukses++;

                // === Sinkronisasi ke history_kelas ===
                $tahunAjaranBaru = TahunAjaran::find($request->tahun_ajaran_baru_id);
                $bulanMulai = $tahunAjaranBaru ? (int)date('n', strtotime($tahunAjaranBaru->mulai)) : 7;

                HistoryKelas::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id,
                        'tahun_ajaran_id' => $request->tahun_ajaran_baru_id,
                    ],
                    [
                        'kelas_id' => $request->kelas_baru_id,
                        'bulan_mulai' => $bulanMulai,
                        'status' => 'aktif',
                    ]
                );
            } else {
                $duplikat++;
            }
        }
        return back()->with('success', "Naik kelas massal selesai. Sukses: $sukses, Duplikat: $duplikat");
    }

    // FORM ATUR SISWA (TIDAK NAIK, LULUS, KELUAR, MUTASI)
    public function aturSiswaForm(Request $request)
{
    $tahunAjarans = TahunAjaran::all();
    $kelas = Kelas::all();
    $tahun_ajaran_id = $request->tahun_ajaran_id ?? '';
    $kelas_id = $request->kelas_id ?? '';
    $status = $request->status ?? '';

    $siswaList = collect();

    if ($tahun_ajaran_id) {
        // Jika status == lulus, ambil kelas 12 saja (otomatis deteksi kelas 12)
        if ($status == 'lulus') {
            // Cek field "nama_kelas" == "12"
            $kelas12 = Kelas::where('nama_kelas', '12')->first();
            if ($kelas12) {
                $kelas_id = $kelas12->id; // force filter kelas_id ke kelas 12
                $siswaList = Siswa::with('kelas')
                    ->where('kelas_id', $kelas12->id)
                    ->where('status', 'aktif')
                    ->orderBy('nama')
                    ->get();
            }
        } else if ($kelas_id) {
            // Jika bukan lulus, filter sesuai kelas_id request
            $siswaList = Siswa::with('kelas')
                ->where('kelas_id', $kelas_id)
                ->where('status', 'aktif')
                ->orderBy('nama')
                ->get();
        }
    }

    return view('pengaturan_kelas.atur_siswa', compact(
        'tahunAjarans', 'kelas', 'tahun_ajaran_id', 'kelas_id', 'siswaList', 'status'
    ));
}


    // PROSES ATUR SISWA STATUS KHUSUS
    // PROSES ATUR SISWA STATUS KHUSUS
public function aturSiswaProses(Request $request)
{
    $request->validate([
        'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
        'kelas_id'        => 'required|exists:kelas,id',
        'siswa_id'        => 'required|array',
        'status'          => 'required|in:tidak naik,lulus,keluar,mutasi',
        'tahun_ajaran_baru_id' => 'nullable|exists:tahun_ajarans,id'
        // Tidak perlu kelas_baru_id
    ]);
    $status = $request->status;
    $keterangan = $request->keterangan ?? null;
    $tahun_ajaran_baru_id = $request->tahun_ajaran_baru_id ?? null;
    $updated = 0;

    foreach ($request->siswa_id as $siswa_id) {
        if ($status === 'tidak naik' && $tahun_ajaran_baru_id) {
            // Insert pengaturan_kelas tahun ajaran baru (status tidak naik)
            $asal = PengaturanKelas::where('siswa_id', $siswa_id)
                ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
                ->first();

            $cek = PengaturanKelas::where('siswa_id', $siswa_id)
                ->where('tahun_ajaran_id', $tahun_ajaran_baru_id)
                ->where('status', 'tidak naik')
                ->first();

            if (!$cek) {
                // Ambil kelas lama dari pengaturan asal atau dari request
                $kelasTidakNaik = $asal ? $asal->kelas_id : $request->kelas_id;

                PengaturanKelas::create([
                    'siswa_id'        => $siswa_id,
                    'kelas_id'        => $kelasTidakNaik,
                    'tahun_ajaran_id' => $tahun_ajaran_baru_id,
                    'status'          => 'tidak naik',
                    'keterangan'      => $keterangan,
                ]);
                $updated++;

                // === Sinkronkan ke history_kelas juga ===
                $tahunAjaranBaru = TahunAjaran::find($request->tahun_ajaran_baru_id);
                $bulanMulai = $tahunAjaranBaru ? (int)date('n', strtotime($tahunAjaranBaru->mulai)) : 7;

                HistoryKelas::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id,
                        'tahun_ajaran_id' => $request->tahun_ajaran_baru_id,
                    ],
                    [
                        'kelas_id' => $kelasTidakNaik,
                        'bulan_mulai' => $bulanMulai,
                        'status' => 'tidak naik', // status history_kelas diisi "tidak naik"
                    ]
                );
                // Status di tabel siswa tetap aktif
            }
        } else {
            // Update status khusus pada tahun ajaran sekarang
            $peng = PengaturanKelas::where('siswa_id', $siswa_id)
                ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
                ->where('kelas_id', $request->kelas_id)
                ->first();

            // Tentukan status history_kelas dan update status siswa jika perlu
            $statusHistory = 'aktif'; // default
            if ($status == 'lulus') {
                $statusHistory = 'lulus';
                Siswa::where('id', $siswa_id)->update(['status' => 'lulus']);
            } elseif ($status == 'keluar') {
                $statusHistory = 'keluar';
                Siswa::where('id', $siswa_id)->update(['status' => 'keluar']);
            } elseif ($status == 'mutasi') {
                $statusHistory = 'mutasi';
                Siswa::where('id', $siswa_id)->update(['status' => 'mutasi']);
            } elseif ($status == 'tidak naik') {
                $statusHistory = 'tidak naik';
                // Tidak update tabel siswa, biarkan tetap aktif
            }

            if (!$peng) {
                PengaturanKelas::create([
                    'siswa_id'        => $siswa_id,
                    'kelas_id'        => $request->kelas_id,
                    'tahun_ajaran_id' => $request->tahun_ajaran_id,
                    'status'          => $status,
                    'keterangan'      => $keterangan,
                ]);
                $updated++;

                $tahunAjaran = TahunAjaran::find($request->tahun_ajaran_id);
                $bulanMulai = $tahunAjaran ? (int)date('n', strtotime($tahunAjaran->mulai)) : 7;

                HistoryKelas::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id,
                        'tahun_ajaran_id' => $request->tahun_ajaran_id,
                    ],
                    [
                        'kelas_id' => $request->kelas_id,
                        'bulan_mulai' => $bulanMulai,
                        'status' => $statusHistory,
                    ]
                );
            } else {
                $peng->update([
                    'status'     => $status,
                    'keterangan' => $keterangan,
                ]);
                $updated++;

                $tahunAjaran = TahunAjaran::find($request->tahun_ajaran_id);
                $bulanMulai = $tahunAjaran ? (int)date('n', strtotime($tahunAjaran->mulai)) : 7;

                HistoryKelas::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id,
                        'tahun_ajaran_id' => $request->tahun_ajaran_id,
                    ],
                    [
                        'kelas_id' => $request->kelas_id,
                        'bulan_mulai' => $bulanMulai,
                        'status' => $statusHistory,
                    ]
                );
            }
        }
    }
    return back()->with('success', "Berhasil mengatur status $status pada $updated siswa.");
}

}
