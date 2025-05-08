<?php

namespace App\Http\Controllers;

use App\Models\{Siswa, Kelas, Spp};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;  // Import Auth facade

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $role)
    {
        $query = Siswa::with(['kelas', 'spp']);

        // Memanggil fungsi pencarian berdasarkan filter
        $this->applyFilters($request, $query);

        $siswa = $query->paginate(10)->withQueryString();

        // Tentukan view dan data tambahan berdasarkan peran
        if ($role === 'admin') {
            $kelas = Kelas::all();
            $spps = Spp::all();
            return view('siswa.index', compact('siswa', 'kelas', 'spps'));
        }

        return view('siswa.index', compact('siswa'));
    }

    /**
     * Fungsi untuk menerapkan filter pencarian
     */
    private function applyFilters(Request $request, $query)
    {
        if ($request->filled('filter_by') && $request->filled('search')) {
            $filterBy = $request->filter_by;
            $search = $request->search;

            if (in_array($filterBy, ['nama', 'nisn', 'nis'])) {
                $query->where($filterBy, 'like', "%$search%");
            } elseif ($filterBy === 'kelas') {
                $query->whereHas('kelas', function ($q) use ($search) {
                    $q->where('nama_kelas', 'like', "%$search%");
                });
            } elseif ($filterBy === 'tahun_ajaran') {
                $query->whereHas('spp', function ($q) use ($search) {
                    $q->where('tahun_ajaran', 'like', "%$search%");
                });
            }
        }
    }

    /**
     * Store a newly created resource in storage (Admin only).
     */
    public function store(Request $request)
    {
        // Cek apakah pengguna memiliki peran 'admin'
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('siswa.index')->with('error', 'Akses ditolak, hanya admin yang dapat menambah data.');
        }

        $request->validate([
            'nisn' => 'required|unique:siswas,nisn',
            'nis' => 'required|unique:siswas,nis',
            'nama' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'alamat' => 'required|string',
            'no_hp' => 'required|string|max:20',
            'spp_id' => 'required|exists:spps,id',
        ]);

        // Menambah data siswa baru
        Siswa::create($request->all());

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    /**
     * Remove the specified resource from storage (Admin only).
     */
    public function destroy(string $id)
    {
        // Cek apakah pengguna memiliki peran 'admin'
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('siswa.index')->with('error', 'Akses ditolak, hanya admin yang dapat menghapus data.');
        }

        $siswa = Siswa::findOrFail($id);

        try {
            // Menghapus data siswa
            $siswa->delete();
            return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil dihapus.');
        } catch (\Exception $e) {
            // Menghandle jika terjadi error
            return redirect()->route('siswa.index')->with('error', 'Gagal menghapus data siswa.');
        }
    }
}
