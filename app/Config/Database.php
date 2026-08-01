<?php

declare(strict_types=1);

namespace App\Config;

use App\Core\Env;
use RuntimeException;

final class Database
{
    /** @return array{host: string, port: string, name: string, user: string, pass: string, charset: string} */
    public static function config(): array
    {
        $isProduction = Env::get('APP_ENV', 'local') === 'production';
        $host = Env::get('DB_HOST', $isProduction ? null : '127.0.0.1');
        $port = Env::get('DB_PORT', '3306') ?? '3306';
        $name = Env::get('DB_NAME', $isProduction ? null : 'genbi');
        $user = Env::get('DB_USER', $isProduction ? null : 'root');
        $pass = Env::get('DB_PASS', $isProduction ? null : '');

        if ($isProduction) {
            $missing = [];
            foreach (['DB_HOST' => $host, 'DB_NAME' => $name, 'DB_USER' => $user, 'DB_PASS' => $pass] as $key => $value) {
                if ($value === null || $value === '') {
                    $missing[] = $key;
                }
            }

            if ($missing !== []) {
                throw new RuntimeException('Missing production database configuration: ' . implode(', ', $missing));
            }
        }

        return [
            'host' => $host ?? '127.0.0.1',
            'port' => $port,
            'name' => $name ?? '',
            'user' => $user ?? '',
            'pass' => $pass ?? '',
            'charset' => 'utf8mb4',
        ];
    }
}
