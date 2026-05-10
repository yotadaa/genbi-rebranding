<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\Security;

final class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $config = Security::config();
        session_name($config['session_name']);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $config['session_secure'],
            'httponly' => true,
            'samesite' => $config['session_samesite'],
        ]);
        session_start();
        self::$started = true;

        // Idle timeout (30 minutes)
        if (isset($_SESSION['_last_activity']) && (time() - $_SESSION['_last_activity']) > 1800) {
            self::destroy();
            return;
        }
        $_SESSION['_last_activity'] = time();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name() ?: 'PHPSESSID',
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        self::$started = false;
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}
