<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Models\News;
use App\Services\SeoService;
use App\Services\StructuredData;
use Throwable;

final class NewsController
{
    public function __construct(private StaticPageRenderer $renderer, private ?News $news = null)
    {
    }

    public function index(Request $request, Response $response): void
    {
        if ($request->acceptsJson()) {
            $page = max(1, (int) ($request->query('page') ?? '1'));
            $perPage = min(100, max(1, (int) ($request->query('per_page') ?? '100')));
            $filters = [
                'category' => $request->query('category'),
                'q' => $request->query('q'),
            ];
            $offset = ($page - 1) * $perPage;
            $items = $this->readFromDatabase(static fn (News $news): array => $news->paginate($filters, $perPage, $offset));
            $total = $this->readFromDatabase(static fn (News $news): int => $news->countPublic($filters));

            // Always return JSON when client expects JSON, even if DB is unavailable
            $response->json([
                'data' => $items ?? [],
                'meta' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total ?? count($items ?? []),
                ],
            ]);
            return;
        }

        $seo = SeoService::forPage('news.html');
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = StructuredData::organization() . PHP_EOL . '  ' . StructuredData::breadcrumbs([
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Berita', 'url' => '/news'],
        ]);
        $response->html($this->renderer->render('news.html', ['meta' => $meta, 'jsonld' => $jsonld]));
    }

    /** @param array{slug?: string} $params */
    public function show(Request $request, Response $response, array $params): void
    {
        $slug = $params['slug'] ?? '';
        if ($request->acceptsJson()) {
            $item = $this->readFromDatabase(static function (News $news) use ($slug): ?array {
                $row = $news->findBySlug($slug);
                if ($row !== null) {
                    $news->incrementViews((int) $row['id']);
                }

                return $row;
            });

            if (is_array($item)) {
                $response->json($item);
                return;
            }

            $response->json(['message' => 'News not found'], 404);
            return;
        }

        // Fetch news from DB for SEO meta injection (HTML rendering)
        $item = $this->readFromDatabase(static fn (News $news): ?array => $news->findBySlug($slug));
        if (is_array($item)) {
            $seo = SeoService::forNews($item);
        } else {
            $seo = SeoService::forPage('news.html');
        }
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = is_array($item)
            ? StructuredData::newsArticle($item) . PHP_EOL . '  ' . StructuredData::breadcrumbs([
                ['name' => 'Beranda', 'url' => '/'],
                ['name' => 'Berita', 'url' => '/news'],
                ['name' => $item['title'] ?? 'Detail', 'url' => '/news/' . ($item['slug'] ?? $slug)],
            ])
            : '';
        $response->html($this->renderer->render('news-detail.html', ['slug' => $slug, 'meta' => $meta, 'jsonld' => $jsonld]));
    }

    /** @param array{id?: string} $params */
    public function legacyShow(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $item = $id > 0 ? $this->readFromDatabase(static fn (News $news): ?array => $news->findById($id)) : null;
        if (is_array($item) && !empty($item['slug'])) {
            $response->redirect('/news/' . rawurlencode((string) $item['slug']), 301);
            return;
        }

        $response->redirect('/news-detail.html?id=' . rawurlencode((string) ($params['id'] ?? '')), 301);
    }

    /** @template T @param callable(News): T $callback @return T|null */
    private function readFromDatabase(callable $callback): mixed
    {
        if (!$this->news instanceof News) {
            return null;
        }

        try {
            return $callback($this->news);
        } catch (Throwable) {
            return null;
        }
    }
}
