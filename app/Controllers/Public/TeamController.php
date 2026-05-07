<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Core\ViewRenderer;
use App\Models\TeamMember;
use App\Services\SeoService;
use App\Services\StructuredData;

class TeamController
{
    public function __construct(
        private StaticPageRenderer $renderer,
        private ?TeamMember $teamModel = null,
        private ?ViewRenderer $viewRenderer = null,
    ) {}

    public function index(Request $request, Response $response): void
    {
        $queryFilters = [
            'q' => $request->query('q'),
            'division' => $request->query('division'),
            'campus' => $request->query('campus'),
            'year' => $request->query('year'),
        ];

        if ($request->acceptsJson()) {
            $pg = Paginator::resolve([
                'page' => $request->query('page'),
                'per_page' => $request->query('per_page'),
            ], 12, 200);
            $members = $this->teamModel?->allActive($queryFilters, $pg['per_page'], $pg['offset']) ?? [];
            $total = $this->teamModel?->countPublic($queryFilters) ?? count($members);
            $filterOptions = $this->teamModel?->filterOptions() ?? ['divisions' => [], 'campuses' => [], 'years' => []];
            $bpi = $this->teamModel?->bpiCore() ?? [];
            $response->json([
                'data' => $members,
                'bpi' => $bpi,
                'filters' => $filterOptions,
                'meta' => Paginator::meta($pg['page'], $pg['per_page'], $total),
            ]);
            return;
        }

        $seo = SeoService::forPage('team.html');
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = StructuredData::organization() . PHP_EOL . '  ' . StructuredData::breadcrumbs([
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Tim', 'url' => '/team'],
        ]);

        if ($this->viewRenderer instanceof ViewRenderer) {
            $pg = Paginator::resolve([
                'page' => $request->query('page'),
                'per_page' => $request->query('per_page'),
            ], 12, 48);
            $members = $this->teamModel?->allActive($queryFilters, $pg['per_page'], $pg['offset']) ?? [];
            $total = $this->teamModel?->countPublic($queryFilters) ?? count($members);
            $totalPages = Paginator::totalPages($total, $pg['per_page']);
            $filterOptions = $this->teamModel?->filterOptions() ?? ['divisions' => [], 'campuses' => [], 'years' => []];

            $html = $this->viewRenderer->renderWithLayout('public/team/index.php', 'layouts/public.php', [
                'members' => $members,
                'page' => $pg['page'],
                'perPage' => $pg['per_page'],
                'total' => $total,
                'totalPages' => $totalPages,
                'filters' => $queryFilters,
                'filterOptions' => $filterOptions,
                'meta' => $meta,
                'jsonld' => $jsonld,
                'bodyClass' => 'page-team',
                'scripts' => '<script src="/assets/js/pages/team.js"></script>',
            ]);
            $response->html($html);
            return;
        }

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
