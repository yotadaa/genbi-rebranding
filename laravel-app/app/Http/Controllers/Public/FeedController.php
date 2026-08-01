<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use Illuminate\Support\Str;

class FeedController extends Controller
{
    private function baseUrl(): string
    {
        return url('/');
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1, 'UTF-8');
    }

    public function news()
    {
        $base = $this->baseUrl();
        $items = News::published()->latest('news_date')->take(20)->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . PHP_EOL;
        $xml .= '  <channel>' . PHP_EOL;
        $xml .= '    <title>GenBI Provinsi Jambi - Berita</title>' . PHP_EOL;
        $xml .= '    <link>' . $this->e($base . '/news') . '</link>' . PHP_EOL;
        $xml .= '    <description>Berita dan kegiatan terbaru dari Generasi Baru Indonesia (GenBI) Provinsi Jambi.</description>' . PHP_EOL;
        $xml .= '    <language>id-ID</language>' . PHP_EOL;
        $xml .= '    <atom:link href="' . $this->e($base . '/feed.xml') . '" rel="self" type="application/rss+xml"/>' . PHP_EOL;

        foreach ($items as $item) {
            if (empty($item->slug)) continue;

            $xml .= '    <item>' . PHP_EOL;
            $xml .= '      <title>' . $this->e($item->news_title) . '</title>' . PHP_EOL;
            $xml .= '      <link>' . $this->e($base . '/news/' . $item->slug) . '</link>' . PHP_EOL;
            $xml .= '      <guid isPermaLink="true">' . $this->e($base . '/news/' . $item->slug) . '</guid>' . PHP_EOL;
            
            $desc = Str::limit(strip_tags((string) $item->news_content), 300);
            $xml .= '      <description>' . $this->e($desc) . '</description>' . PHP_EOL;
            
            if (!empty($item->news_date)) {
                $xml .= '      <pubDate>' . date('r', strtotime($item->news_date)) . '</pubDate>' . PHP_EOL;
            }
            $xml .= '    </item>' . PHP_EOL;
        }

        $xml .= '  </channel>' . PHP_EOL;
        $xml .= '</rss>';

        return response($xml)->header('Content-Type', 'application/rss+xml');
    }
}
