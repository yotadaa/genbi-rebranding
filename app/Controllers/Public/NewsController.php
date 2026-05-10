<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Core\ViewRenderer;
use App\Models\News;
use App\Models\NewsComment;
use App\Models\Setting;
use App\Services\CommentPolicy;
use App\Services\HtmlSanitizer;
use App\Services\SeoService;
use App\Services\StructuredData;
use Throwable;

final class NewsController
{
    public function __construct(
        private StaticPageRenderer $renderer,
        private ?News $news = null,
        private ?NewsComment $comments = null,
        private ?ViewRenderer $viewRenderer = null,
    ) {
    }

    public function index(Request $request, Response $response): void
    {
        if ($request->acceptsJson()) {
            $pg = Paginator::resolve([
                'page' => $request->query('page'),
                'per_page' => $request->query('per_page'),
            ], 12, 100);
            $filters = [
                'category' => $request->query('category'),
                'q' => $request->query('q'),
            ];
            $items = $this->readFromDatabase(static fn (News $news): array => $news->paginate($filters, $pg['per_page'], $pg['offset']));
            $total = $this->readFromDatabase(static fn (News $news): int => $news->countPublic($filters));

            $response->json([
                'data' => array_map(fn (array $item): array => $this->sanitizePublicItem($item), $items ?? []),
                'meta' => Paginator::meta($pg['page'], $pg['per_page'], $total ?? count($items ?? [])),
            ]);
            return;
        }

        $seo = SeoService::forPage('news.html');
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = StructuredData::organization() . PHP_EOL . '  ' . StructuredData::breadcrumbs([
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Berita', 'url' => '/news'],
        ]);

        if ($this->viewRenderer instanceof ViewRenderer) {
            $pg = Paginator::resolve([
                'page' => $request->query('page'),
                'per_page' => $request->query('per_page'),
            ], 12, 24);
            $filters = [
                'category' => $request->query('category'),
                'q' => $request->query('q'),
            ];
            $items = $this->readFromDatabase(static fn (News $news): array => $news->paginate($filters, $pg['per_page'], $pg['offset'])) ?? [];
            $total = $this->readFromDatabase(static fn (News $news): int => $news->countPublic($filters)) ?? count($items);
            $totalPages = Paginator::totalPages($total, $pg['per_page']);

            $html = $this->viewRenderer->renderWithLayout('public/news/index.php', 'layouts/public.php', [
                'items' => $items,
                'page' => $pg['page'],
                'perPage' => $pg['per_page'],
                'total' => $total,
                'totalPages' => $totalPages,
                'filters' => $filters,
                'meta' => $meta,
                'jsonld' => $jsonld,
                'bodyClass' => 'page-news',
                'scripts' => '<script defer src="/assets/js/dist/pages/news.js"></script>',
            ]);
            $response->html($html);
            return;
        }

        $response->html($this->renderer->render('news.html', ['meta' => $meta, 'jsonld' => $jsonld]));
    }

    /** @param array{slug?: string} $params */
    public function show(Request $request, Response $response, array $params): void
    {
        $slug = $params['slug'] ?? '';
        if ($request->acceptsJson()) {
            $item = $this->readFromDatabase(static function (News $news) use ($slug): ?array {
                $row = $news->findPublicBySlug($slug);
                if ($row !== null) {
                    $news->incrementViews((int) $row['id']);
                }

                return $row;
            });

            if (is_array($item)) {
                $response->json($this->sanitizePublicItem($item));
                return;
            }

            $response->json(['message' => 'News not found'], 404);
            return;
        }

        // Fetch news from DB for SEO meta injection (HTML rendering)
        $item = $this->readFromDatabase(static fn (News $news): ?array => $news->findPublicBySlug($slug));
        $comments = [];
        if (is_array($item)) {
            $item = $this->sanitizePublicItem($item);
            $item['comment_policy'] = [
                'comments_enabled' => ($item['comment'] ?? 'On') !== 'Off',
                'voting_enabled' => true,
                'replies_enabled' => true,
                'max_reply_depth' => 3,
                'replies_require_moderation' => true,
            ];
            if ($this->comments instanceof NewsComment) {
                try {
                    $newsId = (int) ($item['id'] ?? 0);
                    try {
                        $settings = new Setting(\App\Core\Database::connection());
                        $policy = new CommentPolicy($settings);
                        $item['comment_policy'] = $policy->forNews($item);
                    } catch (Throwable) {
                        // Keep SSR page resilient when settings are unavailable.
                    }

                    $comments = $this->comments->treeForNews(
                        $newsId,
                        (string) ($item['comment_policy']['root_sort'] ?? 'newest_first'),
                        (string) ($item['comment_policy']['reply_sort'] ?? 'oldest_first'),
                        (int) ($item['comment_policy']['max_reply_depth'] ?? 3)
                    );
                } catch (Throwable) {
                    $comments = [];
                }
            }
        }
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

        if ($this->viewRenderer instanceof ViewRenderer) {
            $html = $this->viewRenderer->renderWithLayout('public/news/show.php', 'layouts/public.php', [
                'item' => $item,
                'comments' => $comments,
                'meta' => $meta,
                'jsonld' => $jsonld,
                'bodyClass' => 'page-news-detail',
                'scripts' => '<script defer src="/assets/js/dist/pages/news-detail.js"></script>',
            ]);
            $response->html($html, is_array($item) ? 200 : 404);
            return;
        }

        $response->html($this->renderer->render('news-detail.html', ['slug' => $slug, 'meta' => $meta, 'jsonld' => $jsonld]));
    }

    /** @param array{id?: string} $params */
    public function legacyShow(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $item = $id > 0 ? $this->readFromDatabase(static fn (News $news): ?array => $news->findPublicById($id)) : null;
        if (is_array($item) && !empty($item['slug'])) {
            $response->redirect('/news/' . rawurlencode((string) $item['slug']), 301);
            return;
        }

        $response->html('<!doctype html><title>404</title><h1>404 - Berita tidak ditemukan</h1>', 404);
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

    /** @param array<string, mixed> $item @return array<string, mixed> */
    private function sanitizePublicItem(array $item): array
    {
        $item['title'] = strip_tags((string) ($item['title'] ?? ''));
        $item['news_title'] = $item['title'];
        $item['excerpt'] = strip_tags((string) ($item['excerpt'] ?? ''));
        $item['news_content_short'] = $item['excerpt'];
        $item['category'] = strip_tags((string) ($item['category'] ?? ''));
        $item['category_name'] = $item['category'];
        $item['contributor_pewarta'] = strip_tags((string) ($item['contributor_pewarta'] ?? ''));
        $item['contributor_editor'] = strip_tags((string) ($item['contributor_editor'] ?? ''));
        $item['author'] = $item['contributor_pewarta'];
        $item['editor'] = $item['contributor_editor'];
        $item['content'] = HtmlSanitizer::sanitize((string) ($item['content'] ?? ''));
        $item['news_content'] = $item['content'];

        return $item;
    }
}
