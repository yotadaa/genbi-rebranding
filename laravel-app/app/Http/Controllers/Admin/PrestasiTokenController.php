<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrestasiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PrestasiTokenController extends Controller
{
    public function index()
    {
        $tokens = PrestasiToken::latest()->paginate(10);
        return response()->json(['success' => true, 'data' => $tokens]);
    }

    public function generate()
    {
        $plainToken = Str::random(32);
        
        $token = PrestasiToken::create([
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(7),
            'used' => false
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Token generated.', 
            'plain_token' => $plainToken,
            'data' => $token
        ]);
    }

    public function revoke($id)
    {
        $token = PrestasiToken::findOrFail($id);
        $token->delete();
        return response()->json(['success' => true, 'message' => 'Token revoked.']);
    }
}
