<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Services\SeoService;

final class PageController
{
    public function __construct(private StaticPageRenderer $renderer)
    {
    }

    public function show(Request $request, Response $response, string $file): void
    {
        $seo = SeoService::forPage($file);
        $meta = SeoService::renderMetaBlock($seo);
        $response->html($this->renderer->render($file, ['meta' => $meta]));
    }
}
