<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentSettingController extends Controller
{
    public function show()
    {
        $settings = DB::table('tbl_settings')->first();
        return response()->json(['success' => true, 'data' => $settings]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'comments_enabled' => 'required|boolean',
            'require_approval' => 'required|boolean',
        ]);

        DB::table('tbl_settings')->update($request->only(['comments_enabled', 'require_approval']));
        
        return response()->json(['success' => true, 'message' => 'Comment settings updated.']);
    }
}
