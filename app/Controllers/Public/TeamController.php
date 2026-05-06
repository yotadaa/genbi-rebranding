<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Models\TeamMember;
use App\Services\SeoService;

class TeamController
{
    public function __construct(
        private StaticPageRenderer $renderer,
        private ?TeamMember $teamModel = null,
    ) {}

    public function index(Request $request, Response $response): void
    {
        if ($request->acceptsJson()) {
            $members = $this->teamModel?->allActive() ?? [];
            $bpi = $this->teamModel?->bpiCore() ?? [];
            $response->json(['data' => $members, 'bpi' => $bpi]);
            return;
        }

        $seo = SeoService::forPage('team.html');
        $meta = SeoService::renderMetaBlock($seo);
        $response->html($this->renderer->render('team.html', ['meta' => $meta]));
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);

        if ($request->acceptsJson()) {
            $member = $this->teamModel?->findById($id);
            if (!$member) {
                $response->json(['error' => 'Not found'], 404);
                return;
            }
            $response->json(['data' => $member]);
            return;
        }

        $seo = SeoService::forPage('team.html');
        $meta = SeoService::renderMetaBlock($seo);
        $response->html($this->renderer->render('team.html', ['meta' => $meta]));
    }
}
