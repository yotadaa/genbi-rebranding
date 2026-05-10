<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Core\ViewRenderer;
use App\Models\Event;
use App\Services\HtmlSanitizer;
use App\Services\SeoService;
use App\Services\StructuredData;
use Throwable;

final class EventController
{
    public function __construct(
        private StaticPageRenderer $renderer,
        private ?Event $eventModel = null,
        private ?ViewRenderer $viewRenderer = null,
    ) {}

    public function index(Request $request, Response $response): void
    {
        $filters = ['q' => $request->query('q')];

        if ($request->acceptsJson()) {
            $pg = Paginator::resolve([
                'page' => $request->query('page'),
                'per_page' => $request->query('per_page'),
            ], 9, 24);
            $items = $this->eventModel?->paginate($filters, $pg['per_page'], $pg['offset']) ?? [];
            $total = $this->eventModel?->countPublic($filters) ?? count($items);

            $response->json([
                'data' => array_map(fn (array $item): array => $this->sanitizePublicItem($item), $items),
                'meta' => Paginator::meta($pg['page'], $pg['per_page'], $total),
            ]);
            return;
        }

        $seo = SeoService::forPage('event.html');
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = StructuredData::organization() . PHP_EOL . '  ' . StructuredData::breadcrumbs([
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Event', 'url' => '/event'],
        ]);

        if ($this->viewRenderer instanceof ViewRenderer) {
            $pg = Paginator::resolve([
                'page' => $request->query('page'),
                'per_page' => $request->query('per_page'),
            ], 9, 24);
            $items = $this->eventModel?->paginate($filters, $pg['per_page'], $pg['offset']) ?? [];
            $total = $this->eventModel?->countPublic($filters) ?? count($items);
            $totalPages = Paginator::totalPages($total, $pg['per_page']);

            $html = $this->viewRenderer->renderWithLayout('public/event/index.php', 'layouts/public.php', [
                'items' => $items,
                'page' => $pg['page'],
                'perPage' => $pg['per_page'],
                'total' => $total,
                'totalPages' => $totalPages,
                'filters' => $filters,
                'meta' => $meta,
                'jsonld' => $jsonld,
                'bodyClass' => 'page-event',
                'scripts' => '<script defer src="/assets/js/dist/pages/event.js"></script>',
            ]);
            $response->html($html);
            return;
        }

        $response->html($this->renderer->render('event.html', ['meta' => $meta, 'jsonld' => $jsonld]));
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $slug = trim((string) ($params['slug'] ?? ''));
        $item = $this->findPublicEvent($slug);

        if ($request->acceptsJson()) {
            if (!$item) {
                $response->json(['error' => 'Event not found'], 404);
                return;
            }
            $response->json(['data' => $this->sanitizePublicItem($item)]);
            return;
        }

        if (ctype_digit($slug) && is_array($item) && !empty($item['slug'])) {
            $response->redirect('/event/' . rawurlencode((string) $item['slug']), 301);
            return;
        }

        if (is_array($item)) {
            $item = $this->sanitizePublicItem($item);
        }

        if (is_array($item)) {
            $seo = SeoService::forPage('event.html');
            // Override with event-specific meta
            $seo['title'] = ($item['title'] ?? 'Event') . ' | GenBI Provinsi Jambi';
            $seo['description'] = mb_substr(strip_tags($item['excerpt'] ?? ''), 0, 160);
            $seo['canonical'] = '/event/' . ($item['slug'] ?? $slug);
        } else {
            $seo = SeoService::forPage('event.html');
        }

        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = is_array($item)
            ? StructuredData::event($item) . PHP_EOL . '  ' . StructuredData::breadcrumbs([
                ['name' => 'Beranda', 'url' => '/'],
                ['name' => 'Event', 'url' => '/event'],
                ['name' => $item['title'] ?? 'Detail', 'url' => '/event/' . ($item['slug'] ?? $slug)],
            ])
            : '';

        if ($this->viewRenderer instanceof ViewRenderer) {
            $html = $this->viewRenderer->renderWithLayout('public/event/show.php', 'layouts/public.php', [
                'item' => $item,
                'meta' => $meta,
                'jsonld' => $jsonld,
                'bodyClass' => 'page-event-detail',
                'scripts' => '<script defer src="/assets/js/dist/pages/event.js"></script>',
            ]);
            $response->html($html, is_array($item) ? 200 : 404);
            return;
        }

        $response->html($this->renderer->render('event.html', ['meta' => $meta, 'jsonld' => $jsonld]));
    }

    /** @param array<string, mixed> $item @return array<string, mixed> */
    private function sanitizePublicItem(array $item): array
    {
        $item['title'] = strip_tags((string) ($item['title'] ?? ''));
        $item['event_title'] = $item['title'];
        $item['excerpt'] = strip_tags((string) ($item['excerpt'] ?? ''));
        $item['location'] = strip_tags((string) ($item['location'] ?? ''));
        $item['content'] = HtmlSanitizer::sanitize((string) ($item['content'] ?? $item['excerpt'] ?? ''));
        $item['map'] = HtmlSanitizer::sanitizeMapEmbedUrl((string) ($item['map'] ?? ''));

        return $item;
    }

    private function findPublicEvent(string $slug): ?array
    {
        if ($slug === '') {
            return null;
        }

        if (!ctype_digit($slug)) {
            return $this->eventModel?->findPublicBySlug($slug);
        }

        return $this->eventModel?->findPublicById((int) $slug);
    }
}
