<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class ArraySheetExport implements FromArray, WithTitle
{
    protected $data, $title;
    public function __construct($data, $title) {
        $this->data = $data;
        $this->title = $title;
    }
    public function array(): array {
        return $this->data;
    }
    public function title(): string {
        return $this->title;
    }
}
