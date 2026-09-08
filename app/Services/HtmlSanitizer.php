<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

final class HtmlSanitizer
{
    /** @var array<string, array<int, string>> */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        'div' => ['class', 'data-block-type', 'data-map-url', 'data-caption'],
        'iframe' => ['src', 'loading', 'referrerpolicy'],
    ];

    /** @var array<int, string> */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'a',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'blockquote', 'cite', 'figure', 'figcaption', 'img', 'div', 'iframe',
    ];

    /** @var array<int, string> */
    private const DROP_WITH_CHILDREN = [
        'script', 'style', 'object', 'embed', 'form',
        'input', 'button', 'textarea', 'select', 'option', 'meta', 'link',
    ];

    /** @var array<int, string> */
    private const VOID_TAGS = ['br', 'img'];

    public static function sanitize(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $loaded = $document->loadHTML(
            '<?xml encoding="utf-8" ?><body>' . $html . '</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($loaded !== true) {
            return htmlspecialchars(strip_tags($html), ENT_QUOTES, 'UTF-8');
        }

        $body = $document->getElementsByTagName('body')->item(0);
        if (!$body instanceof DOMElement) {
            return '';
        }

        $output = '';
        foreach ($body->childNodes as $child) {
            $output .= self::sanitizeNode($child);
        }

        return $output;
    }

    public static function sanitizeUrl(?string $url, array $allowedSchemes = ['http', 'https']): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        if (str_starts_with($url, '/')) {
            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return '';
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if ($scheme === '') {
            return '';
        }

        if (!in_array($scheme, $allowedSchemes, true)) {
            return '';
        }

        return $url;
    }

    public static function sanitizeMapEmbedUrl(?string $url): string
    {
        $url = self::sanitizeUrl($url);
        if ($url === '') {
            return '';
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);
        $allowedHosts = ['www.google.com', 'google.com', 'maps.google.com'];

        if (!in_array($host, $allowedHosts, true)) {
            return '';
        }

        if (!str_contains($path, '/maps')) {
            return '';
        }

        return $url;
    }

    private static function sanitizeNode(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return htmlspecialchars($node->nodeValue ?? '', ENT_QUOTES, 'UTF-8');
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return '';
        }

        $tag = strtolower($node->nodeName);
        if (in_array($tag, self::DROP_WITH_CHILDREN, true)) {
            return '';
        }

        $children = self::sanitizeChildren($node);
        if (!in_array($tag, self::ALLOWED_TAGS, true)) {
            return $children;
        }

        $attributes = self::sanitizeAttributes($node instanceof DOMElement ? $node : null, $tag);
        if (in_array($tag, self::VOID_TAGS, true)) {
            return '<' . $tag . $attributes . '>';
        }

        return '<' . $tag . $attributes . '>' . $children . '</' . $tag . '>';
    }

    private static function sanitizeChildren(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= self::sanitizeNode($child);
        }

        return $html;
    }

    private static function sanitizeAttributes(?DOMElement $element, string $tag): string
    {
        if (!$element instanceof DOMElement) {
            return '';
        }

        $allowed = self::ALLOWED_ATTRIBUTES[$tag] ?? [];
        if ($allowed === []) {
            return '';
        }

        $attributes = [];
        foreach ($allowed as $name) {
            if (!$element->hasAttribute($name)) {
                continue;
            }

            $value = trim($element->getAttribute($name));
            if ($value === '') {
                continue;
            }

            if (in_array($name, ['href', 'src'], true)) {
                $value = ($tag === 'iframe' && $name === 'src')
                    ? self::sanitizeMapEmbedUrl($value)
                    : self::sanitizeUrl($value);
                if ($value === '') {
                    continue;
                }
            }

            if ($tag === 'div' && $name === 'class') {
                if ($value !== 'event-map-block') {
                    continue;
                }
            }

            if ($tag === 'div' && $name === 'data-block-type') {
                if ($value !== 'map') {
                    continue;
                }
            }

            if ($tag === 'div' && $name === 'data-map-url') {
                $value = self::sanitizeMapEmbedUrl($value);
                if ($value === '') {
                    continue;
                }
            }

            if ($tag === 'iframe' && $name === 'referrerpolicy') {
                $allowedPolicies = ['no-referrer-when-downgrade', 'strict-origin-when-cross-origin'];
                if (!in_array(strtolower($value), $allowedPolicies, true)) {
                    continue;
                }
            }

            if ($tag === 'a' && $name === 'target') {
                $target = strtolower($value);
                if (!in_array($target, ['_blank', '_self'], true)) {
                    continue;
                }
                $value = $target;
            }

            if ($tag === 'a' && $name === 'rel') {
                $tokens = preg_split('/\s+/', strtolower($value)) ?: [];
                $tokens = array_values(array_unique(array_intersect($tokens, ['noopener', 'noreferrer', 'nofollow'])));
                if ($tokens === []) {
                    continue;
                }
                $value = implode(' ', $tokens);
            }

            if ($tag === 'a' && $name === 'target' && strtolower($value) === '_blank') {
                $attributes['rel'] = 'noopener noreferrer';
            }

            if ($tag === 'img' && $name === 'loading') {
                $loading = strtolower($value);
                if (!in_array($loading, ['lazy', 'eager'], true)) {
                    continue;
                }
                $value = $loading;
            }

            if ($tag === 'img' && in_array($name, ['width', 'height'], true) && !preg_match('/^\d{1,4}$/', $value)) {
                continue;
            }

            $attributes[$name] = $value;
        }

        if ($tag === 'a' && ($attributes['target'] ?? '') === '_blank') {
            $attributes['rel'] = 'noopener noreferrer';
        }

        $serialized = '';
        foreach ($attributes as $name => $value) {
            $serialized .= ' ' . $name . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }

        return $serialized;
    }
}
