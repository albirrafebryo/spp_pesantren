<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SppRincian extends Model
{
    use HasFactory;

     protected $table = 'spprincians';
    protected $guarded = [];

     public function spp()
    {
        return $this->belongsTo(Spp::class);
    }
}
