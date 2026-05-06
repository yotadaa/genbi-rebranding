<?php

declare(strict_types=1);

namespace App\Config;

use App\Core\Env;

final class App
{
    /** @return array{env: string, url: string} */
    public static function config(): array
    {
        return [
            'env' => Env::get('APP_ENV', 'local') ?? 'local',
            'url' => rtrim(Env::get('APP_URL', 'http://127.0.0.1:8000') ?? 'http://127.0.0.1:8000', '/'),
        ];
    }
}
