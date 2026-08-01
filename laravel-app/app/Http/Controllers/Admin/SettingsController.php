<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;
use App\Services\SiteSettings;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        return app(\App\Http\Controllers\Admin\AdminPageController::class)->show($request, 'settings');
    }

    public function showTheme(Request $request)
    {
        return app(\App\Http\Controllers\Admin\AdminPageController::class)->show($request, 'settings');
    }

    public function showHomePage(Request $request)
    {
        return app(\App\Http\Controllers\Admin\AdminPageController::class)->show($request, 'settings');
    }

    public function data()
    {
        $settings = app(SiteSettings::class)->all();
        return response()->json(['ok' => true, 'success' => true, 'data' => $settings]);
    }

    private function updateMany(array $data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['_token', '_method', 'logo', 'favicon', 'banner', 'upload', 'file', 'image'])) {
                continue;
            }
            Setting::put((string)$key, is_scalar($value) ? (string)$value : json_encode($value));
        }
        SiteSettings::clearCache();
    }

    public function updateLogo(Request $request)
    {
        if ($request->hasFile('logo') || $request->hasFile('upload') || $request->hasFile('file')) {
            $file = $request->file('logo') ?? $request->file('upload') ?? $request->file('file');
            $path = $file->store('settings', 'public');
            Setting::put('site.logo_url', Storage::url($path));
            SiteSettings::clearCache();
        }
        $this->updateMany($request->all());
        return response()->json(['ok' => true, 'success' => true, 'message' => 'Logo updated', 'data' => $request->all()]);
    }

    public function updateFavicon(Request $request)
    {
        if ($request->hasFile('favicon') || $request->hasFile('upload') || $request->hasFile('file')) {
            $file = $request->file('favicon') ?? $request->file('upload') ?? $request->file('file');
            $path = $file->store('settings', 'public');
            Setting::put('site.favicon_url', Storage::url($path));
            SiteSettings::clearCache();
        }
        $this->updateMany($request->all());
        return response()->json(['ok' => true, 'success' => true, 'message' => 'Favicon updated', 'data' => $request->all()]);
    }

    public function updateTopbar(Request $request)
    {
        $this->updateMany($request->all());
        return response()->json(['ok' => true, 'success' => true, 'message' => 'Topbar updated', 'data' => $request->all()]);
    }

    public function updateFooter(Request $request)
    {
        $this->updateMany($request->all());
        return response()->json(['ok' => true, 'success' => true, 'message' => 'Footer updated', 'data' => $request->all()]);
    }

    public function updateEmail(Request $request)
    {
        $this->updateMany($request->all());
        return response()->json(['ok' => true, 'success' => true, 'message' => 'Email updated', 'data' => $request->all()]);
    }

    public function updateBanner(Request $request)
    {
        if ($request->hasFile('banner') || $request->hasFile('upload') || $request->hasFile('file')) {
            $file = $request->file('banner') ?? $request->file('upload') ?? $request->file('file');
            $path = $file->store('settings', 'public');
            Setting::put('site.banner_image_1', Storage::url($path));
            SiteSettings::clearCache();
        }
        $this->updateMany($request->all());
        return response()->json(['ok' => true, 'success' => true, 'message' => 'Banner updated', 'data' => $request->all()]);
    }

    public function updateSidebar(Request $request)
    {
        $this->updateMany($request->all());
        return response()->json(['ok' => true, 'success' => true, 'message' => 'Sidebar updated', 'data' => $request->all()]);
    }

    public function updateColor(Request $request)
    {
        $this->updateMany($request->all());
        return response()->json(['ok' => true, 'success' => true, 'message' => 'Colors updated', 'data' => $request->all()]);
    }

    public function updateTheme(Request $request)
    {
        $this->updateMany($request->all());
        return response()->json(['ok' => true, 'success' => true, 'message' => 'Theme updated', 'data' => $request->all()]);
    }

    public function updateHomePage(Request $request)
    {
        $this->updateMany($request->all());
        return response()->json(['ok' => true, 'success' => true, 'message' => 'Home page updated', 'data' => $request->all()]);
    }

    public function pageContent(string $page)
    {
        $page = $this->pageKey($page);
        $value = Setting::get('page.' . $page . '.content', '[]');
        $decoded = json_decode((string) $value, true);
        return response()->json(['ok' => true, 'data' => is_array($decoded) ? $decoded : []]);
    }

    public function updatePageContent(Request $request, string $page)
    {
        $page = $this->pageKey($page);
        $data = $request->validate(['content' => 'required|array']);
        Setting::put('page.' . $page . '.content', json_encode($data['content'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        SiteSettings::clearCache();
        return response()->json(['ok' => true, 'success' => true, 'data' => $data['content']]);
    }

    private function pageKey(string $page): string
    {
        abort_unless(in_array($page, ['about', 'faq', 'service', 'testimonial', 'news', 'event', 'search', 'terms', 'privacy', 'team', 'portfolio'], true), 404);
        return $page;
    }

    public function upload(Request $request)
    {
        $file = $request->file('upload') ?? $request->file('file') ?? $request->file('image') ?? $request->file('logo') ?? $request->file('favicon') ?? $request->file('banner');
        if (!$file) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
        $path = $file->store('uploads', 'public');
        $url = Storage::url($path);
        return response()->json(['ok' => true, 'success' => true, 'url' => $url, 'data' => ['url' => $url]]);
    }
}
