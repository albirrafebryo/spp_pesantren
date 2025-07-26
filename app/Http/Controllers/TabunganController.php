<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TabunganController extends Controller
{
    public function index()
    {
        return view('tabungan.index'); // mengarahkan ke resources/views/posts/index.blade.php
    }
}
