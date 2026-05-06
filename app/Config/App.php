<?php

declare(strict_types=1);

namespace App\Config;

use App\Core\Env;

final class App
{
    public const DEFAULT_URL = 'https://official.genbijambi.com';

    /** @return array{env: string, url: string} */
    public static function config(): array
    {
        return [
            'env' => Env::get('APP_ENV', 'local') ?? 'local',
            'url' => rtrim(Env::get('APP_URL', self::DEFAULT_URL) ?? self::DEFAULT_URL, '/'),
        ];
    }
}
