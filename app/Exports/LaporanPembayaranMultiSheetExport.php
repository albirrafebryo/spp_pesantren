<?php

namespace App\Exports;

use App\Exports\ArraySheetExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanPembayaranMultiSheetExport implements WithMultipleSheets
{
    protected $bulanan, $daftarUlang, $tabungan;

    public function __construct($bulanan, $daftarUlang, $tabungan)
    {
        $this->bulanan     = $bulanan;
        $this->daftarUlang = $daftarUlang;
        $this->tabungan    = $tabungan;
    }

    public function sheets(): array
    {
        return [
            new ArraySheetExport($this->bulanan,     'Bulanan'),
            new ArraySheetExport($this->daftarUlang, 'Daftar Ulang'),
            new ArraySheetExport($this->tabungan,    'Tabungan'),
        ];
    }
}
