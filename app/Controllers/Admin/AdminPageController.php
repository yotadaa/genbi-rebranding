<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Core\ViewRenderer;
use App\Models\News;
use App\Models\Feature;
use App\Models\GenBIPoint;
use App\Models\PresensiEvent;
use App\Models\PresensiSubmission;
use App\Models\Prestasi;
use App\Models\TeamMember;
use App\Services\CsrfService;
use App\Services\SiteSettings;

final class AdminPageController
{
    public function __construct(
        private StaticPageRenderer $renderer,
        private ?ViewRenderer $viewRenderer = null,
        private ?News $news = null,
        private ?TeamMember $teamModel = null,
        private ?Prestasi $prestasiModel = null,
        private ?Feature $featureModel = null,
        private ?SiteSettings $siteSettings = null,
        private ?PresensiEvent $presensiEventModel = null,
        private ?PresensiSubmission $presensiSubmissionModel = null,
        private ?GenBIPoint $genbiPointModel = null,
    ) {
    }

    public function dashboard(Request $request, Response $response): void
    {
        if ($this->viewRenderer instanceof ViewRenderer && $this->siteSettings instanceof SiteSettings) {
            $extracted = $this->renderer->extractAdminPage('admin/dashboard.html', [
                'noindex' => true,
                'csrf_token' => CsrfService::token(),
            ]);

            if (is_array($extracted)) {
                $response->html($this->viewRenderer->renderWithLayout('admin/static-shell.php', 'layouts/admin.php', [
                    'title' => $extracted['title'],
                    'csrfToken' => CsrfService::token(),
                    'cmsPage' => $extracted['cmsPage'] ?: 'dashboard',
                    'cmsMode' => $extracted['cmsMode'] ?: 'list',
                    'staticContent' => $extracted['content'],
                    'scripts' => $extracted['scripts'],
                ]), 200, ['X-Robots-Tag' => 'noindex, nofollow']);
                return;
            }
        }

        $response->html($this->renderer->render('admin/dashboard.html', [
            'noindex' => true,
            'csrf_token' => CsrfService::token(),
        ]), 200, [
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    private function renderAdminNewsSsr(string $page, Request $request): ?string
    {
        $editorScripts = <<<'HTML'
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script>
<script defer src="/assets/js/dist/admin/cms.js?v=20260617a"></script>
HTML;
        $cmsScript = '<script defer src="/assets/js/dist/admin/cms.js?v=20260617a"></script>';

        if ($page === 'news') {
            $pg = Paginator::resolve([
                'page' => $request->query('page'),
                'per_page' => $request->query('per_page'),
            ], 25, 100);
            
            // Build filters array
            $filters = [];
            if ($request->query('status')) {
                $filters['status'] = $request->query('status');
            }
            if ($request->query('q')) {
                $filters['q'] = $request->query('q');
            }
            
            // Parse category[] query params
            $categoryParams = $_GET['category'] ?? [];
            if (!is_array($categoryParams)) {
                $categoryParams = [$categoryParams];
            }
            $categoryIds = array_filter(array_map('intval', $categoryParams));
            if (!empty($categoryIds)) {
                $filters['categories'] = $categoryIds;
            }
            
            $layout = $request->query('layout') ?: 'list';
            $categories = $this->news?->categories() ?? [];
            $items = $this->news?->allForAdmin($pg['per_page'], $pg['offset'], $filters) ?? [];
            $total = $this->news?->countForAdmin($filters) ?? count($items);
            $totalPages = Paginator::totalPages($total, $pg['per_page']);

            return $this->viewRenderer->renderWithLayout('admin/news/index.php', 'layouts/admin.php', [
                'title' => 'View News | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => 'news',
                'cmsMode' => 'list',
                'items' => $items,
                'page' => $pg['page'],
                'perPage' => $pg['per_page'],
                'total' => $total,
                'totalPages' => $totalPages,
                'filters' => $filters,
                'categories' => $categories,
                'selectedCategories' => $categoryIds,
                'layout' => $layout,
                'scripts' => $cmsScript,
            ]);
        }

        if ($page === 'news-add') {
            $categories = $this->news?->categories() ?? [];
            return $this->viewRenderer->renderWithLayout('admin/news/form.php', 'layouts/admin.php', [
                'title' => 'Add News | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => 'news',
                'cmsMode' => 'editor',
                'isEdit' => false,
                'item' => null,
                'categories' => $categories,
                'scripts' => $editorScripts,
            ]);
        }

        if ($page === 'news-edit') {
            $id = (int) ($request->query('id') ?? 0);
            $item = $id > 0 ? $this->news?->findById($id) : null;
            $categories = $this->news?->categories() ?? [];
            return $this->viewRenderer->renderWithLayout('admin/news/form.php', 'layouts/admin.php', [
                'title' => ($item ? $item['title'] . ' - Edit' : 'Edit News') . ' | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => 'news-edit',
                'cmsMode' => 'editor',
                'isEdit' => true,
                'item' => $item,
                'categories' => $categories,
                'scripts' => $editorScripts,
            ]);
        }

        return null;
    }

    private function renderAdminTeamSsr(Request $request): ?string
    {
        if (!$this->teamModel) {
            return null;
        }

        $pg = Paginator::resolve([
            'page' => $request->query('page'),
            'per_page' => $request->query('per_page'),
        ], 24, 100);
        $filters = [
            'q' => $request->query('q'),
            'division' => $request->query('division'),
            'campus' => $request->query('campus'),
            'year' => $request->query('year'),
        ];
        $layout = $request->query('layout') ?: 'grid';
        $items = $this->teamModel->allForAdmin($filters, $pg['per_page'], $pg['offset']);
        $total = $this->teamModel->countPublic($filters);
        $totalPages = Paginator::totalPages($total, $pg['per_page']);
        $filterOptions = $this->teamModel->filterOptions();

        return $this->viewRenderer->renderWithLayout('admin/team/index.php', 'layouts/admin.php', [
            'title' => 'View Team Members | Admin GenBI',
            'csrfToken' => CsrfService::token(),
            'cmsPage' => 'team',
            'cmsMode' => 'list',
            'items' => $items,
            'page' => $pg['page'],
            'perPage' => $pg['per_page'],
            'total' => $total,
            'totalPages' => $totalPages,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'layout' => $layout,
            'scripts' => '<script defer src="/assets/js/dist/admin/cms.js?v=20260617a"></script>',
        ]);
    }

    private function renderAdminPrestasiSsr(string $page, Request $request): ?string
    {
        if (!$this->prestasiModel) {
            return null;
        }

        $editorScripts = <<<'HTML'
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script>
<script defer src="/assets/js/dist/admin/cms.js?v=20260617a"></script>
HTML;
        $cmsScript = '<script defer src="/assets/js/dist/admin/cms.js?v=20260617a"></script>';

        if ($page === 'prestasi') {
            $pg = Paginator::resolve([
                'page' => $request->query('page'),
                'per_page' => $request->query('per_page'),
            ], 25, 100);
            $filters = [
                'q' => $request->query('q'),
                'category' => $request->query('category'),
                'year' => $request->query('year'),
                'status' => $request->query('status'),
            ];
            $items = $this->prestasiModel->allForAdmin($filters, $pg['per_page'], $pg['offset']);
            $total = $this->prestasiModel->countForAdmin($filters);
            $totalPages = Paginator::totalPages($total, $pg['per_page']);

            return $this->viewRenderer->renderWithLayout('admin/prestasi/index.php', 'layouts/admin.php', [
                'title' => 'View Prestasi | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => 'prestasi',
                'cmsMode' => 'list',
                'items' => $items,
                'page' => $pg['page'],
                'perPage' => $pg['per_page'],
                'total' => $total,
                'totalPages' => $totalPages,
                'filters' => $filters,
                'scripts' => $cmsScript,
            ]);
        }

        if ($page === 'prestasi-add') {
            return $this->viewRenderer->renderWithLayout('admin/prestasi/form.php', 'layouts/admin.php', [
                'title' => 'Add Prestasi | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => 'prestasi',
                'cmsMode' => 'editor',
                'isEdit' => false,
                'item' => null,
                'scripts' => $editorScripts,
            ]);
        }

        if ($page === 'prestasi-edit') {
            $id = (int) ($request->query('id') ?? 0);
            $item = $id > 0 ? $this->prestasiModel->findById($id) : null;
            return $this->viewRenderer->renderWithLayout('admin/prestasi/form.php', 'layouts/admin.php', [
                'title' => ($item ? $item['title'] . ' - Edit' : 'Edit Prestasi') . ' | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => 'prestasi-edit',
                'cmsMode' => 'editor',
                'isEdit' => true,
                'item' => $item,
                'scripts' => $editorScripts,
            ]);
        }

        return null;
    }

    private function renderAdminFeatureSsr(string $page, Request $request): ?string
    {
        if (!$this->featureModel) {
            return null;
        }

        $cmsScript = '<script defer src="/assets/js/dist/admin/cms.js?v=20260617a"></script>';

        if ($page === 'feature') {
            $pg = Paginator::resolve([
                'page' => $request->query('page'),
                'per_page' => $request->query('per_page'),
            ], 25, 100);
            $filters = [
                'q' => $request->query('q'),
                'status' => $request->query('status'),
                'show_on_home' => $request->query('show_on_home'),
            ];
            $items = $this->featureModel->allForAdmin($filters, $pg['per_page'], $pg['offset']);
            $total = $this->featureModel->countForAdmin($filters);
            $totalPages = Paginator::totalPages($total, $pg['per_page']);

            return $this->viewRenderer->renderWithLayout('admin/feature/index.php', 'layouts/admin.php', [
                'title' => 'Program Utama | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => 'feature',
                'cmsMode' => 'list',
                'items' => $items,
                'page' => $pg['page'],
                'perPage' => $pg['per_page'],
                'total' => $total,
                'totalPages' => $totalPages,
                'filters' => $filters,
                'scripts' => $cmsScript,
            ]);
        }

        if ($page === 'feature-add' || $page === 'feature-edit') {
            $id = (int) ($request->query('id') ?? 0);
            $item = $page === 'feature-edit' && $id > 0 ? $this->featureModel->findById($id) : null;
            return $this->viewRenderer->renderWithLayout('admin/feature/form.php', 'layouts/admin.php', [
                'title' => ($page === 'feature-edit' ? 'Edit Program Utama' : 'Add Program Utama') . ' | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => $page === 'feature-edit' ? 'feature-edit' : 'feature',
                'cmsMode' => 'editor',
                'isEdit' => $page === 'feature-edit',
                'item' => $item,
                'scripts' => $cmsScript,
            ]);
        }

        return null;
    }

    private function renderAdminPresensiSsr(string $page, Request $request): ?string
    {
        if (!$this->presensiEventModel) {
            return null;
        }

        $script = '<script defer src="/assets/js/dist/lib/qr-creator.min.js?v=20260616g"></script>' . PHP_EOL
            . '<script defer src="/assets/js/dist/admin/presensi.js?v=20260617a"></script>';

        if ($page === 'presensi') {
            $pg = Paginator::resolve([
                'page' => $request->query('page'),
                'per_page' => $request->query('per_page'),
            ], 25, 100);
            $filters = [
                'q' => $request->query('q'),
                'status' => $request->query('status'),
            ];
            $items = $this->presensiEventModel->allForAdmin($filters, $pg['per_page'], $pg['offset']);
            $total = $this->presensiEventModel->countForAdmin($filters);
            $totalPages = Paginator::totalPages($total, $pg['per_page']);

            return $this->viewRenderer->renderWithLayout('admin/presensi/index.php', 'layouts/admin.php', [
                'title' => 'Presensi | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => 'presensi',
                'cmsMode' => 'list',
                'items' => $items,
                'page' => $pg['page'],
                'perPage' => $pg['per_page'],
                'total' => $total,
                'totalPages' => $totalPages,
                'filters' => $filters,
                'scripts' => $script,
            ]);
        }

        if ($page === 'presensi-add' || $page === 'presensi-edit') {
            $id = (int) ($request->query('id') ?? 0);
            $item = $page === 'presensi-edit' && $id > 0 ? $this->presensiEventModel->findById($id) : null;

            return $this->viewRenderer->renderWithLayout('admin/presensi/form.php', 'layouts/admin.php', [
                'title' => ($page === 'presensi-edit' ? 'Edit Presensi' : 'Add Presensi') . ' | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => $page === 'presensi-edit' ? 'presensi-edit' : 'presensi',
                'cmsMode' => 'editor',
                'isEdit' => $page === 'presensi-edit',
                'item' => $item,
                'scripts' => $script,
            ]);
        }

        if ($page === 'presensi-detail') {
            $id = (int) ($request->query('id') ?? 0);
            $item = $id > 0 ? $this->presensiEventModel->findById($id) : null;
            $submissions = $id > 0 ? ($this->presensiSubmissionModel?->forEvent($id) ?? []) : [];

            return $this->viewRenderer->renderWithLayout('admin/presensi/show.php', 'layouts/admin.php', [
                'title' => ($item ? $item['event_name'] : 'Detail Presensi') . ' | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => 'presensi-detail',
                'cmsMode' => 'detail',
                'item' => $item,
                'submissions' => $submissions,
                'scripts' => $script,
            ]);
        }

        return null;
    }

    private function renderAdminGenBIPoinSsr(string $page, Request $request): ?string
    {
        if (!$this->genbiPointModel) {
            return null;
        }

        $script = '<script defer src="/assets/js/dist/admin/genbi-point.js?v=20260617a"></script>';

        if ($page === 'genbi-poin') {
            $pg = Paginator::resolve([
                'page' => $request->query('page'),
                'per_page' => $request->query('per_page'),
            ], 25, 100);
            $filters = [
                'q' => $request->query('q'),
            ];
            $items = $this->genbiPointModel->membersWithPoints($filters, $pg['per_page'], $pg['offset']);
            $total = $this->genbiPointModel->countMembers($filters);
            $totalPages = Paginator::totalPages($total, $pg['per_page']);
            $activities = $this->genbiPointModel->activities([], 10, 0);

            return $this->viewRenderer->renderWithLayout('admin/genbi-poin/index.php', 'layouts/admin.php', [
                'title' => 'GenBI Poin | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => 'genbi-poin',
                'cmsMode' => 'list',
                'items' => $items,
                'activities' => $activities,
                'page' => $pg['page'],
                'perPage' => $pg['per_page'],
                'total' => $total,
                'totalPages' => $totalPages,
                'filters' => $filters,
                'scripts' => $script,
            ]);
        }

        if ($page === 'genbi-poin-add' || $page === 'genbi-poin-edit') {
            $id = (int) ($request->query('id') ?? 0);
            $item = $page === 'genbi-poin-edit' && $id > 0 ? $this->genbiPointModel->findActivity($id) : null;
            $prefillTeamId = $page === 'genbi-poin-add' ? (int) ($request->query('team_id') ?? 0) : 0;
            $prefillMember = $prefillTeamId > 0 ? $this->genbiPointModel->memberWithPoints($prefillTeamId) : null;

            return $this->viewRenderer->renderWithLayout('admin/genbi-poin/form.php', 'layouts/admin.php', [
                'title' => ($page === 'genbi-poin-edit' ? 'Edit Aktivitas Poin' : 'Tambah Aktivitas Poin') . ' | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => 'genbi-poin-add',
                'cmsMode' => 'editor',
                'isEdit' => $page === 'genbi-poin-edit',
                'item' => $item,
                'prefillMember' => $prefillMember,
                'scripts' => $script,
            ]);
        }

        if ($page === 'genbi-poin-detail') {
            $id = (int) ($request->query('id') ?? 0);
            $member = $id > 0 ? $this->genbiPointModel->memberWithPoints($id) : null;
            $presensiActivities = $id > 0 ? $this->genbiPointModel->presensiActivitiesForTeam($id, 200) : [];
            $manualActivities = $id > 0 ? $this->genbiPointModel->manualActivitiesForTeam($id, 200) : [];

            return $this->viewRenderer->renderWithLayout('admin/genbi-poin/show.php', 'layouts/admin.php', [
                'title' => ($member ? ($member['name'] ?? 'Detail Aktivitas') : 'Detail Aktivitas') . ' | GenBI Poin',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => 'genbi-poin',
                'cmsMode' => 'detail',
                'member' => $member,
                'teamId' => $id,
                'presensiActivities' => $presensiActivities,
                'manualActivities' => $manualActivities,
                'scripts' => $script,
            ]);
        }

        return null;
    }

    /** @param array{page?: string} $params */
    public function show(Request $request, Response $response, array $params): void
    {
        $page = preg_replace('/[^a-z0-9_-]/i', '', $params['page'] ?? 'dashboard') ?: 'dashboard';
        
        // SSR for admin pages
        if ($this->viewRenderer instanceof ViewRenderer) {
            $ssrHtml = $this->renderAdminNewsSsr($page, $request);
            if ($ssrHtml === null && $page === 'team-member') {
                $ssrHtml = $this->renderAdminTeamSsr($request);
            }
            if ($ssrHtml === null && in_array($page, ['prestasi', 'prestasi-add', 'prestasi-edit'], true)) {
                $ssrHtml = $this->renderAdminPrestasiSsr($page, $request);
            }
            if ($ssrHtml === null && in_array($page, ['presensi', 'presensi-add', 'presensi-edit', 'presensi-detail'], true)) {
                $ssrHtml = $this->renderAdminPresensiSsr($page, $request);
            }
            if ($ssrHtml === null && in_array($page, ['genbi-poin', 'genbi-poin-add', 'genbi-poin-edit', 'genbi-poin-detail'], true)) {
                $ssrHtml = $this->renderAdminGenBIPoinSsr($page, $request);
            }
            if ($ssrHtml === null && in_array($page, ['feature', 'feature-add', 'feature-edit'], true)) {
                $ssrHtml = $this->renderAdminFeatureSsr($page, $request);
            }
            if ($ssrHtml !== null) {
                $response->html($ssrHtml, 200, ['X-Robots-Tag' => 'noindex, nofollow']);
                return;
            }
        }
        
        if ($this->viewRenderer instanceof ViewRenderer && $this->siteSettings instanceof SiteSettings) {
            $extracted = $this->renderer->extractAdminPage('admin/' . $page . '.html', [
                'noindex' => true,
                'csrf_token' => CsrfService::token(),
            ]);

            if (is_array($extracted)) {
                $response->html($this->viewRenderer->renderWithLayout('admin/static-shell.php', 'layouts/admin.php', [
                    'title' => $extracted['title'],
                    'csrfToken' => CsrfService::token(),
                    'cmsPage' => $extracted['cmsPage'] ?: $page,
                    'cmsMode' => $extracted['cmsMode'] ?: 'list',
                    'staticContent' => $extracted['content'],
                    'scripts' => $extracted['scripts'],
                ]), 200, ['X-Robots-Tag' => 'noindex, nofollow']);
                return;
            }
        }

        // Static fallback for all other pages
        $response->html($this->renderer->render('admin/' . $page . '.html', [
            'noindex' => true,
            'csrf_token' => CsrfService::token(),
        ]), 200, [
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
