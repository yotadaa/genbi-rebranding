<?php

namespace App\Services;

class ImageResolver
{
    public static function resolve(?string $path, string $default = '/uploads/slider-1.png'): string
    {
        if (empty($path)) {
            return $default;
        }
        $path = trim((string)$path);
        if (str_contains($path, 'drive.google.com')) {
            if (preg_match('/(?:id=|file\/d\/)([\w-]+)/', $path, $matches)) {
                return 'https://drive.google.com/thumbnail?id=' . $matches[1] . '&sz=w1000';
            }
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            if (str_contains($path, 'genbijambi.com/public/')) {
                $path = str_replace(['https://genbijambi.com/public/', 'http://genbijambi.com/public/'], '/', $path);
            } elseif (str_contains($path, 'official.genbijambi.com/')) {
                $path = str_replace(['https://official.genbijambi.com/', 'http://official.genbijambi.com/'], '/', $path);
            } elseif (str_contains($path, 'genbijambi.com/')) {
                $path = str_replace(['https://genbijambi.com/', 'http://genbijambi.com/'], '/', $path);
            } else {
                return $path;
            }
        }
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'uploads/')) {
            return '/' . $path;
        }
        return '/uploads/' . $path;
    }
}
