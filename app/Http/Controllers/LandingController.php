<?php

namespace App\Http\Controllers;

use App\Models\BusinessCategory;

class LandingController extends Controller
{
    public function index()
    {
        return view('landing.index', [
            'categories' => BusinessCategory::orderBy('sort_order')->get(),
            'registered' => session('registered'),   // data sukses setelah registrasi
        ]);
    }
}
