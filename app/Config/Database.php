<?php

declare(strict_types=1);

namespace App\Config;

use App\Core\Env;

final class Database
{
    /** @return array{host: string, port: string, name: string, user: string, pass: string, charset: string} */
    public static function config(): array
    {
        return [
            'host' => Env::get('DB_HOST', '127.0.0.1') ?? '127.0.0.1',
            'port' => Env::get('DB_PORT', '3306') ?? '3306',
            'name' => Env::get('DB_NAME', 'genc1357_genbijambi') ?? 'genc1357_genbijambi',
            'user' => Env::get('DB_USER', 'root') ?? 'root',
            'pass' => Env::get('DB_PASS', '') ?? '',
            'charset' => 'utf8mb4',
        ];
    }
}
