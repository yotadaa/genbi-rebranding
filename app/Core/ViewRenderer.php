<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class ViewRenderer
{
    /** @var array<string, mixed> */
    private array $shared = [];

    public function __construct(private string $viewRoot)
    {
    }

    /** @param array<string, mixed> $data */
    public function share(array $data): void
    {
        $this->shared = array_merge($this->shared, $data);
    }

    /** @param array<string, mixed> $data */
    public function render(string $view, array $data = []): string
    {
        $data = array_merge($this->shared, $data);
        $file = $this->resolve($view);
        if ($file === null) {
            return '<!doctype html><title>404</title><h1>404 - View tidak ditemukan</h1>';
        }

        $e = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $asset = static fn (string $path): string => '/assets/' . ltrim($path, '/');
        $url = static fn (string $name, array $params = []): string => match ($name) {
            'home' => '/',
            'about' => '/about',
            'team' => '/team',
            'news' => '/news',
            'news.show' => '/news/' . rawurlencode($params['slug'] ?? ''),
            'event' => '/event',
            'event.show' => '/event/' . rawurlencode($params['slug'] ?? ''),
            'prestasi' => '/prestasi',
            'prestasi.show' => '/prestasi/' . rawurlencode($params['slug'] ?? ''),
            'presensi.show' => '/presensi/' . rawurlencode($params['token'] ?? ''),
            'contact' => '/contact',
            'admin.dashboard' => '/admin/dashboard',
            'admin.news' => '/admin/news',
            'admin.news.add' => '/admin/news-add',
            'admin.news.edit' => '/admin/news-edit?id=' . ($params['id'] ?? ''),
            'admin.prestasi' => '/admin/prestasi',
            'admin.prestasi.add' => '/admin/prestasi-add',
            'admin.prestasi.edit' => '/admin/prestasi-edit?id=' . ($params['id'] ?? ''),
            'admin.presensi' => '/admin/presensi',
            'admin.presensi.add' => '/admin/presensi-add',
            'admin.presensi.edit' => '/admin/presensi-edit?id=' . ($params['id'] ?? ''),
            'admin.presensi.show' => '/admin/presensi-detail?id=' . ($params['id'] ?? ''),
            'admin.genbiPoin' => '/admin/genbi-poin',
            'admin.genbiPoin.add' => '/admin/genbi-poin-add',
            'admin.genbiPoin.edit' => '/admin/genbi-poin-edit?id=' . ($params['id'] ?? ''),
            'admin.genbiPoin.show' => '/admin/genbi-poin-detail?id=' . ($params['id'] ?? ''),
            'admin.feature' => '/admin/feature',
            'admin.feature.add' => '/admin/feature-add',
            'admin.feature.edit' => '/admin/feature-edit?id=' . ($params['id'] ?? ''),
            'admin.team' => '/admin/team-member',
            'admin.team.add' => '/admin/team-member-add',
            'admin.team.edit' => '/admin/team-member-edit?id=' . ($params['id'] ?? ''),
            default => '/' . ltrim($name, '/'),
        };

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
