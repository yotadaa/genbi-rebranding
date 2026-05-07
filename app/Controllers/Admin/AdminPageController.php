<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Core\ViewRenderer;
use App\Models\News;
use App\Services\CsrfService;

final class AdminPageController
{
    public function __construct(
        private StaticPageRenderer $renderer,
        private ?ViewRenderer $viewRenderer = null,
        private ?News $news = null,
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
            $items = $this->news?->allForAdmin(50, 0) ?? [];
            return $this->viewRenderer->renderWithLayout('admin/news/index.php', 'layouts/admin.php', [
                'title' => 'View News | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => 'news',
                'cmsMode' => 'list',
                'items' => $items,
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

    /** @param array{page?: string} $params */
    public function show(Request $request, Response $response, array $params): void
    {
        $page = preg_replace('/[^a-z0-9_-]/i', '', $params['page'] ?? 'dashboard') ?: 'dashboard';
        
        // SSR for admin news pages
        if ($this->viewRenderer instanceof ViewRenderer) {
            $ssrHtml = $this->renderAdminNewsSsr($page, $request);
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
