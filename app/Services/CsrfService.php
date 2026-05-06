<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;

final class CsrfService
{
    private const TOKEN_KEY = '_csrf_token';

    public static function token(): string
    {
        if (!Session::has(self::TOKEN_KEY)) {
            Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
        }
        return (string) Session::get(self::TOKEN_KEY);
    }

    public static function validate(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        $stored = Session::get(self::TOKEN_KEY);
        if (empty($stored)) {
            return false;
        }
        return hash_equals((string) $stored, $token);
    }

    public static function regenerate(): void
    {
        Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
    }

    public static function hiddenInput(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}
