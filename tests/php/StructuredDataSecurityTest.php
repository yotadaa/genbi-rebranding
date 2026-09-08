<?php

declare(strict_types=1);

use App\Services\StructuredData;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

function expect_structured_data_security(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$script = StructuredData::newsArticle([
    'title' => '</script><img src=x onerror=alert(1)>',
    'slug' => 'jsonld-xss-test',
    'excerpt' => 'Tom & Jerry "quote" \'apos\' <tag>',
    'published_at' => '2026-06-23',
    'author' => 'Eve </script>',
]);

$prefix = '<script type="application/ld+json">';
$suffix = '</script>';

expect_structured_data_security(str_starts_with($script, $prefix), 'Structured data should start with the JSON-LD script wrapper.');
expect_structured_data_security(str_ends_with($script, $suffix), 'Structured data should end with the JSON-LD script wrapper.');

$json = substr($script, strlen($prefix), -strlen($suffix));
$decoded = json_decode($json, true);

expect_structured_data_security(is_array($decoded), 'Structured data output should remain valid JSON.');
expect_structured_data_security(!str_contains($json, '</script>'), 'Structured data JSON must not contain a raw closing script tag.');
expect_structured_data_security(!str_contains($json, '<img'), 'Structured data JSON must not contain raw HTML tags.');
expect_structured_data_security(str_contains($json, '\u003C/script\u003E'), 'Structured data JSON should hex-encode angle brackets.');
expect_structured_data_security(str_contains($json, '\u0026'), 'Structured data JSON should hex-encode ampersands.');
expect_structured_data_security(str_contains($json, '\u0022'), 'Structured data JSON should hex-encode quotes inside values.');
expect_structured_data_security(str_contains($json, '\u0027'), 'Structured data JSON should hex-encode apostrophes inside values.');
expect_structured_data_security(($decoded['headline'] ?? '') === '</script><img src=x onerror=alert(1)>', 'Structured data should preserve data semantics after safe JSON encoding.');

echo "PHP structured data security tests passed\n";
