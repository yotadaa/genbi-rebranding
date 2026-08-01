<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        return view('public.about.index', [
            'scripts' => '<script defer src="/assets/js/dist/pages/about.js"></script>'
        ]);
    }
}
