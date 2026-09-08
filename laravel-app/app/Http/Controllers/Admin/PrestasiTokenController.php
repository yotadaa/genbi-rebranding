<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrestasiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PrestasiTokenController extends Controller
{
    /**
     * GET /admin/prestasi-tokens
     * cms.js renderPrestasiTokenList() expects { data: [...], meta: {...} }
     * Token keys: id, label, token_hash (truncated), expires_at, used_count, max_uses, revoked
     */
    public function index()
    {
        $tokens = PrestasiToken::latest('created_at')->get();

        $data = $tokens->map(function ($t) {
            $status = $t->revoked_at ? 'revoked' : (($t->expires_at && $t->expires_at->isPast()) ? 'expired' : (($t->max_uses > 0 && $t->used_count >= $t->max_uses) ? 'used' : 'active'));
            return [
                'id'           => $t->token_id ?? $t->id,
                'label'        => $t->label ?? 'Token #' . ($t->token_id ?? $t->id),
                'intended_for' => $t->intended_for ?? '',
                'max_uses'     => $t->max_uses ?? 1,
                'used_count'   => $t->used_count ?? 0,
                'expires_at'   => $t->expires_at ?? null,
                'revoked'      => !empty($t->revoked_at),
                'revoked_at'   => $t->revoked_at ?? null,
                'created_at'   => $t->created_at ?? null,
                'used_at'      => $t->used_at ?? null,
                'status'       => $status,
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => ['total' => count($data)],
        ]);
    }

    /**
     * POST /admin/prestasi-tokens
     */
    public function generate(Request $request)
    {
        $request->validate([
            'label'        => 'nullable|string|max:255',
            'intended_for' => 'nullable|string|max:500',
            'max_uses'     => 'nullable|integer|min:1|max:100',
            'expires_at'   => 'nullable|date',
        ]);

        $plainToken = Str::random(40);

        $token = PrestasiToken::create([
            'token_hash'   => hash('sha256', $plainToken),
            'label'        => $request->input('label', 'Token ' . now()->format('Y-m-d H:i')),
            'intended_for' => $request->input('intended_for', ''),
            'max_uses'     => $request->input('max_uses', 1),
            'used_count'   => 0,
            'expires_at'   => $request->input('expires_at') ?: now()->addDays(30),
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Token berhasil dibuat. Simpan token ini, tidak dapat ditampilkan ulang.',
            'data'        => [
                'id'           => $token->token_id ?? $token->id,
                'label'        => $token->label,
                'intended_for' => $token->intended_for,
                'max_uses'     => $token->max_uses,
                'used_count'   => 0,
                'expires_at'   => $token->expires_at,
                'revoked'      => false,
                // Plain token is returned once to construct the share URL; only
                // its SHA-256 hash is persisted in tbl_prestasi_submission_token.
                'token'        => $plainToken,
                'submit_url'   => url('/prestasi/submit/' . $plainToken),
            ],
        ]);
    }

    /**
     * POST /admin/prestasi-tokens/{id}/revoke
     */
    public function revoke($id)
    {
        $token = PrestasiToken::findOrFail($id);
        $token->update(['revoked_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Token berhasil direvoke.']);
    }
}
