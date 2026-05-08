<?php

declare(strict_types=1);

use App\Services\HtmlSanitizer;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$html = '<p>Hello <strong>world</strong> <script>alert(1)</script><a href="javascript:alert(1)" onclick="alert(1)" target="_blank">bad</a><img src="https://example.test/image.jpg" onerror="alert(1)" loading="lazy"></p>';
$sanitized = HtmlSanitizer::sanitize($html);

assert(str_contains($sanitized, '<p>Hello <strong>world</strong> '));
assert(!str_contains($sanitized, '<script'));
assert(!str_contains($sanitized, 'onclick='));
assert(!str_contains($sanitized, 'javascript:'));
assert(str_contains($sanitized, '<a target="_blank" rel="noopener noreferrer">bad</a>'));
assert(str_contains($sanitized, '<img src="https://example.test/image.jpg" loading="lazy">'));

$mapOk = HtmlSanitizer::sanitizeMapEmbedUrl('https://www.google.com/maps/embed?pb=abc');
$mapBad = HtmlSanitizer::sanitizeMapEmbedUrl('https://evil.example/embed');

assert($mapOk === 'https://www.google.com/maps/embed?pb=abc');
assert($mapBad === '');

echo "PHP HTML sanitizer tests passed\n";
