<?php

declare(strict_types=1);

namespace App\Config;

use App\Core\Env;

final class Security
{
    /** @return array{session_name: string, session_secure: bool, session_samesite: string} */
    public static function config(): array
    {
        $appEnv = Env::get('APP_ENV', 'local') ?? 'local';
        $defaultSecure = $appEnv === 'local' ? 'false' : 'true';
        $sameSite = Env::get('SESSION_SAMESITE', 'Lax') ?? 'Lax';
        $sameSite = in_array($sameSite, ['Lax', 'Strict', 'None'], true) ? $sameSite : 'Lax';

        return [
            'session_name' => Env::get('SESSION_NAME', 'GENBISESSID') ?? 'GENBISESSID',
            'session_secure' => filter_var(Env::get('SESSION_SECURE', $defaultSecure), FILTER_VALIDATE_BOOLEAN),
            'session_samesite' => $sameSite,
        ];
    }
}
