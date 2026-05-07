<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Shared pagination helper.
 *
 * Parses page/per_page from query params, computes offset,
 * builds page-link query strings preserving active filters.
 */
final class Paginator
{
    /**
     * Parse and clamp page/per_page from raw query params.
     *
     * @param array<string, string|null> $params  Raw query params (e.g. $_GET)
     * @param int $defaultPerPage                 Default items per page
     * @param int $maxPerPage                     Maximum allowed per_page
     * @return array{page: int, per_page: int, offset: int}
     */
    public static function resolve(array $params, int $defaultPerPage = 12, int $maxPerPage = 24): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));

        $perPage = (int) ($params['per_page'] ?? $defaultPerPage);
        if ($perPage < 1) {
            $perPage = $defaultPerPage;
        }
        if ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }

        return [
            'page' => $page,
            'per_page' => $perPage,
            'offset' => ($page - 1) * $perPage,
        ];
    }

    /**
     * Compute total number of pages.
     */
    public static function totalPages(int $total, int $perPage): int
    {
        if ($total < 1) {
            return 1;
        }

        return (int) ceil($total / max(1, $perPage));
    }

    /**
     * Build a standard pagination meta array for JSON responses.
     *
     * @return array{page: int, per_page: int, total: int, total_pages: int}
     */
    public static function meta(int $page, int $perPage, int $total): array
    {
        return [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => self::totalPages($total, $perPage),
        ];
    }

    /**
     * Build a query string for a specific page, preserving active filters.
     *
     * @param array<string, string|null> $filters  Active filter values
     */
    public static function buildQuery(int $page, array $filters = []): string
    {
        $params = [];
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }
        $params['page'] = (string) $page;

        return http_build_query($params);
    }
}
