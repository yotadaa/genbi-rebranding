<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminPageController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function show(Request $request, $page)
    {
        $viewName = "admin.{$page}";
        if (view()->exists($viewName)) {
            return view($viewName);
        }
        return abort(404);
    }
}
