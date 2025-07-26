<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TabunganHistory extends Model
{
    protected $guarded = [];

    public function tabungan()
    {
        return $this->belongsTo(Tabungan::class, 'tabungan_id');
    }
}