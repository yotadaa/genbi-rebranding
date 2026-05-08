<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\ViewRenderer;
use App\Models\ContactSetting;
use App\Services\SeoService;

final class ContactController
{
    public function __construct(
        private ViewRenderer $viewRenderer,
        private ?ContactSetting $contactSetting = null
    ) {
    }

    public function index(Request $request, Response $response): void
    {
        $contact = $this->contactSetting?->get() ?? [];
        $seo = [
            'title' => $contact['meta_title'] ?? 'Contact | GenBI Provinsi Jambi',
            'description' => $contact['meta_description'] ?? 'Hubungi GenBI Provinsi Jambi.',
            'canonical' => SeoService::absoluteUrl('/contact'),
            'robots' => 'index, follow',
            'og_type' => 'website',
            'og_title' => $contact['meta_title'] ?? 'Contact | GenBI Provinsi Jambi',
            'og_description' => $contact['meta_description'] ?? 'Hubungi GenBI Provinsi Jambi.',
            'og_url' => SeoService::absoluteUrl('/contact'),
            'og_image' => SeoService::absoluteUrl('/uploads/slider-1.png'),
            'og_image_width' => '1200',
            'og_image_height' => '630',
            'og_image_alt' => 'Contact GenBI Provinsi Jambi',
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $contact['meta_title'] ?? 'Contact | GenBI Provinsi Jambi',
            'twitter_description' => $contact['meta_description'] ?? 'Hubungi GenBI Provinsi Jambi.',
            'twitter_image' => SeoService::absoluteUrl('/uploads/slider-1.png'),
        ];

        $response->html($this->viewRenderer->renderWithLayout('public/contact/index.php', 'layouts/public.php', [
            'title' => 'Contact | GenBI Provinsi Jambi',
            'meta' => SeoService::renderMetaBlock($seo),
            'contact' => $contact,
            'scripts' => '<script src="/assets/js/pages/contact.js?v=20260508f"></script>',
        ]));
    }
}

