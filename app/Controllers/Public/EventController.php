<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Models\Event;
use App\Services\SeoService;
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
        $response->html($this->renderer->render('event.html', ['meta' => $meta]));
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

        $seo = SeoService::forPage('event.html');
        $meta = SeoService::renderMetaBlock($seo);
        $response->html($this->renderer->render('event.html', ['meta' => $meta]));
    }
}
