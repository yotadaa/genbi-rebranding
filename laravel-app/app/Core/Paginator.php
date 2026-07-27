<?php

namespace App\Core;

class Paginator
{
    public static function buildQuery(int $page, array $additional = []): string
    {
        $params = array_merge(request()->query(), $additional);
        $params['page'] = $page;
        return http_build_query($params);
    }
}
