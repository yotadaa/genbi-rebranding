<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit');
    }

    public function data()
    {
        $settings = DB::table('tbl_settings')->first();
        return response()->json(['success' => true, 'data' => $settings]);
    }

    private function updateSetting($key, $value)
    {
        DB::table('tbl_settings')->update([$key => $value]);
    }

    public function updateLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image']);
        $path = $request->file('logo')->store('settings', 'public');
        $this->updateSetting('logo', $path);
        return response()->json(['success' => true, 'message' => 'Logo updated']);
    }

    public function updateFavicon(Request $request)
    {
        $request->validate(['favicon' => 'required|image']);
        $path = $request->file('favicon')->store('settings', 'public');
        $this->updateSetting('favicon', $path);
        return response()->json(['success' => true, 'message' => 'Favicon updated']);
    }

    public function updateTopbar(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string',
            'instagram' => 'nullable|string',
            'youtube' => 'nullable|string',
            'tiktok' => 'nullable|string',
        ]);
        
        DB::table('tbl_settings')->update($request->only(['email', 'phone', 'instagram', 'youtube', 'tiktok']));
        return response()->json(['success' => true, 'message' => 'Topbar updated']);
    }

    public function updateFooter(Request $request)
    {
        $request->validate([
            'footer_text' => 'required|string',
            'address' => 'required|string',
        ]);
        DB::table('tbl_settings')->update($request->only(['footer_text', 'address']));
        return response()->json(['success' => true, 'message' => 'Footer updated']);
    }

    public function updateEmail(Request $request)
    {
        // Email settings
        $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|integer',
            'smtp_user' => 'required|string',
            'smtp_pass' => 'required|string',
        ]);
        DB::table('tbl_settings')->update($request->only(['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass']));
        return response()->json(['success' => true, 'message' => 'Email updated']);
    }

    public function updateBanner(Request $request)
    {
        $request->validate(['banner' => 'required|image']);
        $path = $request->file('banner')->store('settings', 'public');
        $this->updateSetting('banner', $path);
        return response()->json(['success' => true, 'message' => 'Banner updated']);
    }

    public function updateSidebar(Request $request)
    {
        $request->validate(['sidebar_text' => 'required|string']);
        $this->updateSetting('sidebar_text', $request->sidebar_text);
        return response()->json(['success' => true, 'message' => 'Sidebar updated']);
    }

    public function updateColor(Request $request)
    {
        $request->validate(['primary_color' => 'required|string', 'secondary_color' => 'required|string']);
        DB::table('tbl_settings')->update($request->only(['primary_color', 'secondary_color']));
        return response()->json(['success' => true, 'message' => 'Colors updated']);
    }

    public function updateTheme(Request $request)
    {
        $request->validate(['theme' => 'required|string']);
        $this->updateSetting('theme', $request->theme);
        return response()->json(['success' => true, 'message' => 'Theme updated']);
    }

    public function updateHomePage(Request $request)
    {
        $request->validate([
            'hero_title' => 'required|string',
            'hero_subtitle' => 'required|string',
        ]);
        DB::table('tbl_settings')->update($request->only(['hero_title', 'hero_subtitle']));
        return response()->json(['success' => true, 'message' => 'Home page updated']);
    }

    public function upload(Request $request)
    {
        $request->validate(['upload' => 'required|image']);
        $path = $request->file('upload')->store('uploads', 'public');
        return response()->json(['url' => Storage::url($path)]);
    }
}
