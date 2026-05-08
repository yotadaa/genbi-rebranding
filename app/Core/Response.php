<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function html(string $content, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        if ($this->isHeadRequest()) {
            return;
        }
        echo HotReload::inject($content);
    }

    public function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header('Location: ' . $url);
    }

    /** @param array<string, mixed>|array<int, mixed> $payload */
    public function json(array $payload, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        if ($this->isHeadRequest()) {
            return;
        }
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function xml(string $content, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        header('Content-Type: application/xml; charset=UTF-8');
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        if ($this->isHeadRequest()) {
            return;
        }
        echo $content;
    }

    private function isHeadRequest(): bool
    {
        return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')) === 'HEAD';
    }
}
