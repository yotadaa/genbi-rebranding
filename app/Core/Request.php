<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public function query(string $key, ?string $default = null): ?string
    {
        $value = $_GET[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }

    /** @return array<string, mixed> */
    public function json(): array
    {
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function ip(): ?string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        return is_string($ip) ? $ip : null;
    }

    public function userAgent(): ?string
    {
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        return is_string($agent) ? substr($agent, 0, 255) : null;
    }

    public function acceptsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        return is_string($accept) && str_contains(strtolower($accept), 'application/json');
    }

    public function session(string $key, mixed $default = null): mixed
    {
        return Session::get($key, $default);
    }
}
