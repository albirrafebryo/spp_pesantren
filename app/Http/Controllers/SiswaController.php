<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Imports\SiswaImport;
use App\Models\HistoryKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::with(['kelas', 'tahunAjaranMasuk', 'historyKelasTerbaru.tahunAjaran', 'wali']);
        $waliList = User::role('wali')->get();

        $tahunAjaranAktif = TahunAjaran::where('is_active', 1)->first();
        $filterTahunAjaran = $request->tahun_ajaran_id ?? ($tahunAjaranAktif ? $tahunAjaranAktif->id : null);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                    ->orWhere('nisn', 'like', "%$search%")
                    ->orWhere('nis', 'like', "%$search%")
                    ->orWhereHas('kelas', function ($q) use ($search) {
                        $q->where('nama_kelas', 'like', "%$search%");
                    })
                    ->orWhereHas('tahunAjaranMasuk', function ($q) use ($search) {
                        $q->where('nama', 'like', "%$search%");
                    });
            });
        }

        // if ($filterTahunAjaran) {
        //     $query->where('tahun_masuk', '<=', $filterTahunAjaran);
        // }

        $siswa = $query->paginate(10)->withQueryString();
        $kelas = Kelas::all();
        $tahunAjarans = TahunAjaran::all();

        return view('siswa.index', [
            'siswa' => $siswa,
            'kelas' => $kelas,
            'tahunAjarans' => $tahunAjarans,
            'waliList' => $waliList,
            'tahunAjaranAktif' => $tahunAjaranAktif,
            'filterTahunAjaran' => $filterTahunAjaran
        ]);
    }


    public function search(Request $request)
    {
        $query = $request->get('query');
        $siswaQuery = Siswa::with(['kelas', 'tahunAjaranMasuk'])
            ->where(function ($q) use ($query) {
                $q->where('nama', 'like', "%$query%")
                    ->orWhere('nisn', 'like', "%$query%")
                    ->orWhere('nis', 'like', "%$query%")
                    ->orWhereHas('kelas', function ($q) use ($query) {
                        $q->where('nama_kelas', 'like', "%$query%");
                    })
                    ->orWhereHas('tahunAjaranMasuk', function ($q) use ($query) {
                        $q->where('nama', 'like', "%$query%");
                    });
            });

        if (Auth::check() && Auth::user()->hasRole('bendahara')) {
            // $userJenjang = Auth::user()->jenjang;
            // $siswaQuery->whereHas('kelas', function ($q) use ($userJenjang) {
            //     $q->where('jenjang', $userJenjang);
            // });
        }

        $siswa = $siswaQuery->paginate(10)->withQueryString();
        // Ambil tahun ajaran aktif untuk partial
        $tahunAjaranAktif = TahunAjaran::where('is_active', 1)->first();
        return view('siswa.partials.table', compact('siswa', 'tahunAjaranAktif'));
    }

    public function create()
    {
        if (Auth::user()->hasRole('admin')) {
            $kelas = Kelas::all();
            $tahunAjarans = TahunAjaran::all();
            return view('siswa.create', compact('kelas', 'tahunAjarans'));
        }

        return redirect()->route('siswa.index');
    }

     public function store(Request $request)
{
    // Validasi hanya field yang penting
    $request->validate([
        'nis'         => 'required|unique:siswas,nis',
        'nama'        => 'required|string|max:255',
        'kelas_id'    => 'required|exists:kelas,id',
        'no_hp'       => 'required|string|max:20',
        'tahun_masuk' => 'required|exists:tahun_ajarans,id',
    ]);

    // 1. Buat user wali otomatis (jika belum ada user dengan no_hp tsb)
    $wali = User::where('email', $request->no_hp)->first();
    if (!$wali) {
        $wali = User::create([
            'name'     => $request->nama . " (Wali)",
            'email'    => $request->no_hp, // Pakai no_hp sebagai username/email
            'password' => bcrypt($request->nama), // Password = nama anak (siswa)
        ]);
        $wali->assignRole('wali');
    }

    // 2. Simpan siswa
    $siswa = Siswa::create([
        'nis'         => $request->nis,
        'nama'        => $request->nama,
        'kelas_id'    => $request->kelas_id,
        'no_hp'       => $request->no_hp,
        'tahun_masuk' => $request->tahun_masuk,
        'wali_id'     => $wali->id,
        'status'      => 'aktif',
        // alamat dan nisn dikosongi, atau gunakan old value jika perlu
    ]);

    // 3. Buat HistoryKelas otomatis untuk tahun masuk
    HistoryKelas::updateOrCreate([
        'siswa_id'        => $siswa->id,
        'tahun_ajaran_id' => $request->tahun_masuk,
    ], [
        'kelas_id' => $request->kelas_id,
    ]);

    return redirect()->route('siswa.index')->with('success', 'Data siswa & user wali berhasil ditambahkan.');
}

    public function show($id)
    {
        if (Auth::user()->hasRole('admin')) {
            $siswa = Siswa::findOrFail($id);
            return view('admin.siswa.show', compact('siswa'));
        }

        return redirect()->route('siswa.index');
    }

    public function edit($id)
    {
        if (Auth::user()->hasRole('admin')) {
            $siswa = Siswa::findOrFail($id);
            $kelas = Kelas::all();
            $tahunAjarans = TahunAjaran::all();
            return view('siswa.edit', compact('siswa', 'kelas', 'tahunAjarans'));
        }

        return redirect()->route('siswa.index');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nisn' => 'required|unique:siswas,nisn,' . $id,
            'nis' => 'required|unique:siswas,nis,' . $id,
            'nama' => 'required|string|max:255',
            'wali_id' => 'required|exists:users,id',
            'kelas_id' => 'required|exists:kelas,id',
            'alamat' => 'required|string',
            'no_hp' => 'required|string|max:20',
            'tahun_masuk' => 'required|exists:tahun_ajarans,id',
        ]);

        $siswa = Siswa::findOrFail($id);
        $siswa->update($request->all());

        $tahunAjaran = TahunAjaran::find($siswa->tahun_masuk);
        if ($tahunAjaran) {
            HistoryKelas::updateOrCreate([
                'siswa_id' => $siswa->id,
                'tahun_ajaran_id' => $tahunAjaran->id,
            ], [
                'kelas_id' => $siswa->kelas_id,
            ]);
        }

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }
    public function destroy($id)
    {
        if (Auth::user()->hasRole('admin')) {
            $siswa = Siswa::findOrFail($id);
            $siswa->delete();

            // OPTIONAL: Hapus juga history_kelas jika siswa dihapus
            // HistoryKelas::where('siswa_id', $id)->delete();

            return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil dihapus.');
        }

        return redirect()->route('siswa.index');
    }

    // =========== Tambahan: Sinkronisasi Otomatis Untuk Tahun Ajaran Baru ===========
    // Panggil method ini di controller TahunAjaran saat tahun ajaran baru aktif
    public function syncHistoryKelasTahunAjaranBaru($tahun_ajaran_id)
    {
        $tahunAjaran = TahunAjaran::find($tahun_ajaran_id);
        if (!$tahunAjaran) {
            return redirect()->back()->with('error', 'Tahun ajaran tidak ditemukan.');
        }

        $siswas = Siswa::all();
        foreach ($siswas as $siswa) {
            // Jika siswa baru masuk pada tahun ajaran ini
            if ($siswa->tahun_masuk == $tahunAjaran->awal) {
                // Mapping dari data siswa (kelas 7/default)
                HistoryKelas::updateOrCreate([
                    'siswa_id' => $siswa->id,
                    'tahun_ajaran_id' => $tahunAjaran->id,
                ], [
                    'kelas_id' => $siswa->kelas_id,
                ]);
            }
            // Untuk siswa lain, harus ambil dari pengaturan_kelas (naik/tidak naik)
            // ... logika bisa ditambahkan di controller promosi ...
        }

        return redirect()->back()->with('success', 'Sinkronisasi history kelas untuk tahun ajaran baru berhasil!');
    }

    public function import(Request $request)
{
    $request->validate([
        'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
        'kelas_id' => 'required|exists:kelas,id',
        'file' => 'required|file|mimes:xls,xlsx'
    ]);

    Excel::import(
        new SiswaImport($request->tahun_ajaran_id, $request->kelas_id),
        $request->file('file')
    );

    return back()->with('success', 'Data siswa berhasil diimport!');
}
public function downloadTemplate()
{
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'nama');
    $sheet->setCellValue('B1', 'nis');
    $sheet->setCellValue('C1', 'no_hp_wali_santri');

    // Contoh baris kedua (optional, bisa dihapus)
    // $sheet->setCellValue('A2', 'Ahmad');
    // $sheet->setCellValue('B2', '2024001');
    // $sheet->setCellValue('C2', '08123456789');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filename = 'template_santri.xlsx';
    $temp_file = tempnam(sys_get_temp_dir(), $filename);
    $writer->save($temp_file);

    return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
}
}