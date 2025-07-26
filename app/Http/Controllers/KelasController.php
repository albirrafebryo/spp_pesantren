<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kelas = Kelas::paginate(6);
        return view('kelas.index', compact('kelas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
        ]);

        $jenjang = $this->tentukanJenjang($request->nama_kelas); // Tentukan jenjang otomatis

        $kelas = Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            
        ]);

        if ($kelas) {
            return redirect()->route('kelas.index')->with('success', 'Data Kelas Berhasil Ditambahkan.');
        } else {
            return redirect()->route('kelas.index')->with('error', 'Data Kelas Tidak Dapat Ditambahkan.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
        ]);

        $kelas = Kelas::findOrFail($id);
        $kelas->nama_kelas = $request->nama_kelas;
        $kelas->jenjang = $this->tentukanJenjang($request->nama_kelas); // Update jenjang juga
        $kelas->save();

        return redirect()->route('kelas.index')->with('success', 'Data kelas berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();

        return redirect()->route('kelas.index')->with('success', 'Data kelas berhasil dihapus!');
    }

    /**
     * Fungsi bantu untuk menentukan jenjang berdasarkan nama_kelas.
     */
    private function tentukanJenjang($namaKelas)
    {
        preg_match('/\d+/', $namaKelas, $matches);
        $angka = isset($matches[0]) ? (int)$matches[0] : null;

        if ($angka >= 7 && $angka <= 9) {
            return 'smp';
        } elseif ($angka >= 10 && $angka <= 12) {
            return 'sma';
        }

        return null; // Default jika tidak sesuai
    }
}
