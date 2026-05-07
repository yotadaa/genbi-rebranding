<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class ViewRenderer
{
    public function __construct(private string $viewRoot)
    {
    }

    /** @param array<string, mixed> $data */
    public function render(string $view, array $data = []): string
    {
        $file = $this->resolve($view);
        if ($file === null) {
            return '<!doctype html><title>404</title><h1>404 - View tidak ditemukan</h1>';
        }

        $e = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $asset = static fn (string $path): string => '/assets/' . ltrim($path, '/');
        $url = static fn (string $path): string => '/' . ltrim($path, '/');

        try {
            ob_start();
            extract($data, EXTR_SKIP);
            require $file;
            return (string) ob_get_clean();
        } catch (Throwable $throwable) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $throwable;
        }
    }

    /** @param array<string, mixed> $data */
    public function renderWithLayout(string $view, string $layout, array $data = []): string
    {
        $content = $this->render($view, $data);
        return $this->render($layout, array_merge($data, ['content' => $content]));
    }

    private function resolve(string $view): ?string
    {
        $safe = str_replace('..', '', ltrim($view, '/'));
        $file = rtrim($this->viewRoot, '/') . '/' . $safe;
        return is_file($file) ? $file : null;
    }
}
