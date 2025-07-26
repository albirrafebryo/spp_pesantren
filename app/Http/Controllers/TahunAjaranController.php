<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tahunAjarans = TahunAjaran::orderBy('mulai', 'desc')->get();
        return view('tahun_ajarans.index', compact('tahunAjarans'));
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
            'nama' => 'required|string|max:20|unique:tahun_ajarans,nama',
            'mulai' => 'nullable|date',
            'selesai' => 'nullable|date|after_or_equal:mulai',
            'is_active' => 'boolean',
        ]);

        TahunAjaran::create([
            'nama'      => $request->nama,
            'mulai'     => $request->mulai,
            'selesai'   => $request->selesai,
            'is_active' => $request->is_active ?? false,
        ]);

        return redirect()->route('tahun_ajarans.index')->with('success', 'Tahun ajaran berhasil ditambah!');
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
         $tahunAjaran = TahunAjaran::findOrFail($id);
        return view('tahun_ajarans.edit', compact('tahunAjaran'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
        'nama' => 'required|string|max:20|unique:tahun_ajarans,nama,' . $id,
        'mulai' => 'nullable|date',
        'selesai' => 'nullable|date|after_or_equal:mulai',
        'is_active' => 'boolean',
    ]);

    $tahunAjaran = TahunAjaran::findOrFail($id);
    $tahunAjaran->update([
        'nama'      => $request->nama,
        'mulai'     => $request->mulai,
        'selesai'   => $request->selesai,
        'is_active' => $request->is_active ?? false,
    ]);

    return redirect()->route('tahun_ajarans.index')->with('success', 'Tahun ajaran berhasil diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tahunAjaran = TahunAjaran::findOrFail($id);
        $tahunAjaran->delete();
        return redirect()->route('tahun_ajarans.index')->with('success', 'Tahun ajaran berhasil dihapus!');
    }
}
