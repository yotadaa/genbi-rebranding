<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactSettingController extends Controller
{
    public function show()
    {
        $settings = DB::table('tbl_settings')->first();
        return response()->json(['success' => true, 'data' => $settings]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'instagram' => 'nullable|string',
            'youtube' => 'nullable|string',
            'tiktok' => 'nullable|string',
        ]);

        DB::table('tbl_settings')->update($request->only(['email', 'phone', 'address', 'instagram', 'youtube', 'tiktok']));
        
        return response()->json(['success' => true, 'message' => 'Contact settings updated.']);
    }
}
