<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LaundryController extends Controller
{
    public function index()
    {
        return view('laundry.index'); // mengarahkan ke resources/views/posts/index.blade.php
    }
}
