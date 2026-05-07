<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Core\ViewRenderer;
use App\Models\News;
use App\Models\Prestasi;
use App\Models\TeamMember;
use App\Services\CsrfService;

final class AdminPageController
{
    public function __construct(
        private StaticPageRenderer $renderer,
        private ?ViewRenderer $viewRenderer = null,
        private ?News $news = null,
        private ?TeamMember $teamModel = null,
        private ?Prestasi $prestasiModel = null,
    ) {
    }

    public function dashboard(Request $request, Response $response): void
    {
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
<script src="/assets/js/admin/cms.js"></script>
HTML;
        $cmsScript = '<script src="/assets/js/admin/cms.js"></script>';

        if ($page === 'news') {
            $pg = Paginator::resolve([
                'page' => $request->query('page'),
                'per_page' => $request->query('per_page'),
            ], 25, 100);
            $status = $request->query('status');
            $items = $this->news?->allForAdmin($pg['per_page'], $pg['offset'], $status) ?? [];
            $total = $this->news?->countForAdmin($status) ?? count($items);
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
                'status' => $status,
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
        $items = $this->teamModel->allForAdmin($filters, $pg['per_page'], $pg['offset']);
        $total = $this->teamModel->countPublic($filters);
        $totalPages = Paginator::totalPages($total, $pg['per_page']);

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
            'scripts' => '<script src="/assets/js/admin/cms.js"></script>',
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
<script src="/assets/js/admin/cms.js"></script>
HTML;
        $cmsScript = '<script src="/assets/js/admin/cms.js"></script>';

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
            if ($ssrHtml !== null) {
                $response->html($ssrHtml, 200, ['X-Robots-Tag' => 'noindex, nofollow']);
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
