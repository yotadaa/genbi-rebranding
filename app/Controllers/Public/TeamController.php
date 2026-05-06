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
            $page = max(1, (int) ($request->query('page') ?? '1'));
            $perPage = min(200, max(1, (int) ($request->query('per_page') ?? '200')));
            $filters = [
                'q' => $request->query('q'),
                'division' => $request->query('division'),
                'campus' => $request->query('campus'),
                'year' => $request->query('year'),
            ];
            $offset = ($page - 1) * $perPage;
            $members = $this->teamModel?->allActive($filters, $perPage, $offset) ?? [];
            $total = $this->teamModel?->countPublic($filters) ?? count($members);
            $filters = $this->teamModel?->filterOptions() ?? ['divisions' => [], 'campuses' => [], 'years' => []];
            $bpi = $this->teamModel?->bpiCore() ?? [];
            $response->json([
                'data' => $members,
                'bpi' => $bpi,
                'filters' => $filters,
                'meta' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                ],
            ]);
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
