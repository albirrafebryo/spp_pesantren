<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use App\Models\JenisPembayaran;
use App\Models\DetailPembayaran;

class DetailPembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $DetailPembayarans = DetailPembayaran::with(['tahunAjaran', 'jenisPembayaran'])->get();
        $tahunAjarans = TahunAjaran::orderBy('nama')->get();
        $jenisPembayarans = JenisPembayaran::all();

        // Siapkan $angkatanList: [ [ 'id' => id_tahun_ajaran, 'label' => "1 (2024/2025)" ], ... ]
        $angkatanList = [];
        foreach ($tahunAjarans as $i => $ta) {
            $angkatanList[] = [
                'id' => $ta->id,
                'label' => ($i + 1) . ' (' . $ta->nama . ')'
            ];
        }

        return view('detailpembayaran.index', compact('DetailPembayarans', 'tahunAjarans', 'jenisPembayarans', 'angkatanList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'jenis_pembayaran_id' => 'required|exists:jenispembayarans,id',
            'nominal' => 'required|numeric|min:0',
            'angkatan_mulai' => 'required|exists:tahun_ajarans,id', // Validasi: id tahun ajaran
        ]);

        DetailPembayaran::create([
            'tahun_ajaran_id' => $request->tahun_ajaran_id,
            'jenis_pembayaran_id' => $request->jenis_pembayaran_id,
            'nominal' => (int) str_replace('.', '', $request->nominal),
            'angkatan_mulai' => $request->angkatan_mulai, // simpan id tahun ajaran
        ]);

        return redirect()->route('detailpembayaran.index')->with('success', 'Detail pembayaran berhasil ditambahkan!');
    }
}
