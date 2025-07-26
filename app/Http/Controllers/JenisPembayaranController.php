<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisPembayaran;

class JenisPembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // PAGINASI 10, dan urut alfabet!
        $jenispembayarans = JenisPembayaran::orderBy('nama', 'asc')->paginate(10);
        return view('jenispembayaran.index', compact('jenispembayarans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'tipe' => 'required|in:0,1',
        ]);
        JenisPembayaran::create([
            'nama' => $request->nama,
            'tipe' => $request->tipe,
        ]);
        return redirect()->route('jenispembayaran.index')->with('success', 'Jenis pembayaran berhasil ditambahkan!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'tipe' => 'required|in:0,1',
        ]);
        $jenis = JenisPembayaran::findOrFail($id);
        $jenis->update([
            'nama' => $request->nama,
            'tipe' => $request->tipe,
        ]);
        return redirect()->route('jenispembayaran.index')->with('success', 'Jenis pembayaran berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $jenis = JenisPembayaran::findOrFail($id);
        $jenis->delete();
        return redirect()->route('jenispembayaran.index')->with('success', 'Jenis pembayaran berhasil dihapus!');
    }
}
