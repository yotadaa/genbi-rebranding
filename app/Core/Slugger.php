<?php

declare(strict_types=1);

namespace App\Core;

final class Slugger
{
    public static function slugify(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $slug = strtolower($normalized === false ? $value : $normalized);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?: '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'item';
    }
}
