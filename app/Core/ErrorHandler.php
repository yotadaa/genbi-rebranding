<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\App;
use Throwable;

final class ErrorHandler
{
    public static function render(Response $response, int $status, string $title = '', string $message = ''): void
    {
        $status = in_array($status, [400, 403, 404, 405, 413, 422, 500, 503], true) ? $status : 500;
        $defaults = self::defaults($status);
        $title = $title !== '' ? $title : $defaults['title'];
        $message = $message !== '' ? $message : $defaults['message'];

        if ((new Request())->acceptsJson()) {
            $response->json(['error' => $title, 'message' => $message], $status, self::headers($status));
            return;
        }

        $viewRoot = dirname(__DIR__) . '/Views';
        $renderer = new ViewRenderer($viewRoot);
        $seoTitle = $status . ' - ' . $title . ' | GenBI Provinsi Jambi';
        $meta = '<title>' . self::escape($seoTitle) . '</title>' . PHP_EOL .
            '  <meta name="description" content="' . self::escape($message) . '">' . PHP_EOL .
            '  <meta name="robots" content="noindex, follow">' . PHP_EOL .
            '  <link rel="canonical" href="' . self::escape(App::config()['url'] . ($_SERVER['REQUEST_URI'] ?? '/')) . '">';

        try {
            $html = $renderer->renderWithLayout('errors/status.php', 'layouts/public.php', [
                'status' => $status,
                'title' => $title,
                'message' => $message,
                'meta' => $meta,
                'bodyClass' => 'page-error page-ready',
            ]);
        } catch (Throwable) {
            $html = '<!doctype html><html lang="id"><head><meta charset="UTF-8"><title>' . self::escape($seoTitle) . '</title><meta name="robots" content="noindex, follow"></head><body><main><h1>' . self::escape((string) $status) . ' - ' . self::escape($title) . '</h1><p>' . self::escape($message) . '</p><p><a href="/">Kembali ke Beranda</a></p></main></body></html>';
        }

        $response->html($html, $status, self::headers($status));
    }

    public static function renderThrowable(Response $response, Throwable $error): void
    {
        self::log($error);
        if ($error instanceof PayloadTooLargeException) {
            self::render($response, 413, 'Payload terlalu besar', 'Ukuran data yang dikirim melebihi batas yang diizinkan.');
            return;
        }

        self::render($response, 500, 'Terjadi kesalahan', 'Maaf, sistem sedang mengalami gangguan. Silakan coba beberapa saat lagi.');
    }

    public static function log(Throwable|string $error, array $context = []): void
    {
        $message = $error instanceof Throwable
            ? sprintf('%s: %s in %s:%d', $error::class, $error->getMessage(), $error->getFile(), $error->getLine())
            : $error;

        if ($context !== []) {
            $message .= ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        error_log('[GenBI Error] ' . $message);
    }

    /** @return array{title: string, message: string} */
    private static function defaults(int $status): array
    {
        return match ($status) {
            400 => ['title' => 'Permintaan tidak valid', 'message' => 'URL atau data yang dikirim tidak dapat diproses.'],
            403 => ['title' => 'Akses ditolak', 'message' => 'Anda tidak memiliki akses ke halaman ini.'],
            404 => ['title' => 'Halaman tidak ditemukan', 'message' => 'Halaman yang Anda cari tidak tersedia atau sudah dipindahkan.'],
            405 => ['title' => 'Metode tidak diizinkan', 'message' => 'Metode request tidak tersedia untuk URL ini.'],
            413 => ['title' => 'Payload terlalu besar', 'message' => 'Ukuran data yang dikirim melebihi batas yang diizinkan.'],
            422 => ['title' => 'Data tidak valid', 'message' => 'Mohon periksa kembali data yang dikirim.'],
            503 => ['title' => 'Layanan belum tersedia', 'message' => 'Fitur ini sedang tidak tersedia. Silakan coba lagi nanti.'],
            default => ['title' => 'Terjadi kesalahan', 'message' => 'Maaf, sistem sedang mengalami gangguan. Silakan coba beberapa saat lagi.'],
        };
    }

    /** @return array<string, string> */
    private static function headers(int $status): array
    {
        return $status >= 500 ? ['Cache-Control' => 'no-store'] : ['Cache-Control' => 'public, max-age=60'];
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
