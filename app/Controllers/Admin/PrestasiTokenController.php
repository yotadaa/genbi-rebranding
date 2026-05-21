<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\PrestasiToken;

class PrestasiTokenController
{
    public function __construct(private ?PrestasiToken $tokenModel = null) {}

    public function index(Request $request, Response $response): void
    {
        $items = $this->tokenModel?->all() ?? [];
        $response->json(['data' => $items]);
    }

    public function generate(Request $request, Response $response): void
    {
        $body = $request->json();
        $label = strip_tags(mb_substr(trim($body['label'] ?? ''), 0, 255));
        $expiresAt = $this->validateExpiresAt($body['expires_at'] ?? null);

        if (empty($label)) {
            $response->json(['error' => 'Label/keterangan wajib diisi'], 422);
            return;
        }

        // Get authenticated admin user ID from session
        $user = Session::get('_auth_user');
        $createdBy = (int) ($user['id'] ?? 1);

        $generated = $this->tokenModel?->generate($label, $createdBy, $expiresAt);

        if ($generated) {
            $response->json(['data' => [
                'id' => $generated['id'],
                'token' => $generated['token'],
                'submit_url' => '/prestasi/submit/' . hash('sha256', $generated['token']),
                'label' => $label,
            ]], 201);
        } else {
            $response->json(['error' => 'Gagal membuat token'], 500);
        }
    }

    public function revoke(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $success = $this->tokenModel?->revoke($id);

        if ($success) {
            $response->json(['data' => ['id' => $id, 'revoked' => true]]);
        } else {
            $response->json(['error' => 'Gagal merevoke token atau token tidak aktif'], 404);
        }
    }

    /**
     * Validate expires_at is a valid datetime string or null.
     */
    private function validateExpiresAt(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = strip_tags(trim((string) $value));

        // Accept ISO 8601 / MySQL datetime formats
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $value)
            ?: \DateTime::createFromFormat('Y-m-d\TH:i:s', $value)
            ?: \DateTime::createFromFormat('Y-m-d\TH:i', $value)
            ?: \DateTime::createFromFormat('Y-m-d', $value);

        if (!$dt) {
            return null; // Invalid format, treat as no expiry
        }

        return $dt->format('Y-m-d H:i:s');
    }
}
