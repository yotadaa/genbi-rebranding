<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private const MAX_JSON_BODY_BYTES = 1_048_576;

    /** @var array<string, mixed>|null */
    private ?array $jsonBody = null;

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
        if ($this->jsonBody !== null) {
            return $this->jsonBody;
        }

        $contentLength = $_SERVER['CONTENT_LENGTH'] ?? null;
        if (is_string($contentLength) && ctype_digit($contentLength) && (int) $contentLength > self::MAX_JSON_BODY_BYTES) {
            throw new PayloadTooLargeException('JSON request body exceeds the allowed size.');
        }

        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            $this->jsonBody = [];
            return $this->jsonBody;
        }

        $decoded = json_decode($raw, true);

        $this->jsonBody = is_array($decoded) ? $decoded : [];
        return $this->jsonBody;
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
