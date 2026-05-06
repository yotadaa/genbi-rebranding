<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Models\Event;
use App\Services\SeoService;
use App\Services\StructuredData;
use Throwable;

final class EventController
{
    public function __construct(
        private StaticPageRenderer $renderer,
        private ?Event $eventModel = null,
    ) {}

    public function index(Request $request, Response $response): void
    {
        if ($request->acceptsJson()) {
            $filters = ['q' => $request->query('q')];
            $items = $this->eventModel?->paginate($filters) ?? [];
            $total = $this->eventModel?->countPublic($filters) ?? count($items);

            $response->json([
                'data' => $items,
                'meta' => ['total' => $total],
            ]);
            return;
        }

        $seo = SeoService::forPage('event.html');
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = StructuredData::organization() . PHP_EOL . '  ' . StructuredData::breadcrumbs([
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Event', 'url' => '/event'],
        ]);
        $response->html($this->renderer->render('event.html', ['meta' => $meta, 'jsonld' => $jsonld]));
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);

        if ($request->acceptsJson()) {
            $item = $id > 0 ? $this->eventModel?->findById($id) : null;
            if (!$item) {
                $response->json(['error' => 'Event not found'], 404);
                return;
            }
            $response->json(['data' => $item]);
            return;
        }

        $item = $id > 0 ? $this->eventModel?->findById($id) : null;
        $seo = SeoService::forPage('event.html');
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = is_array($item)
            ? StructuredData::event($item) . PHP_EOL . '  ' . StructuredData::breadcrumbs([
                ['name' => 'Beranda', 'url' => '/'],
                ['name' => 'Event', 'url' => '/event'],
                ['name' => $item['title'] ?? 'Detail', 'url' => '/event/' . $id],
            ])
            : '';
        $response->html($this->renderer->render('event.html', ['meta' => $meta, 'jsonld' => $jsonld]));
    }
}
