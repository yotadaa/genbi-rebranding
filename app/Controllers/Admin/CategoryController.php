<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\Category;

final class CategoryController
{
    public function __construct(private ?Category $categories = null)
    {
    }

    public function index(Request $request, Response $response): void
    {
        $response->json(['data' => $this->categories?->all() ?? []]);
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->categories instanceof Category) {
            $response->json(['error' => 'Database tidak tersedia'], 500);
            return;
        }

        $name = $this->validatedName($request);
        if ($name === '') {
            $response->json(['error' => 'Nama kategori wajib diisi'], 422);
            return;
        }

        if ($this->categories->existsByName($name)) {
            $response->json(['error' => 'Kategori sudah ada'], 409);
            return;
        }

        $id = $this->categories->create($name);
        $response->json(['data' => $this->categories->findById($id)], 201);
    }

    public function update(Request $request, Response $response, array $params): void
    {
        if (!$this->categories instanceof Category) {
            $response->json(['error' => 'Database tidak tersedia'], 500);
            return;
        }

        $id = (int) ($params['id'] ?? 0);
        $name = $this->validatedName($request);
        if ($id <= 0 || $name === '') {
            $response->json(['error' => 'Data kategori tidak valid'], 422);
            return;
        }

        if ($this->categories->existsByName($name, $id)) {
            $response->json(['error' => 'Kategori sudah ada'], 409);
            return;
        }

        $existing = $this->categories->findById($id);
        if (!$existing) {
            $response->json(['error' => 'Kategori tidak ditemukan'], 404);
            return;
        }

        $this->categories->update($id, $name);

        $response->json(['data' => $this->categories->findById($id)]);
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        if (!$this->categories instanceof Category) {
            $response->json(['error' => 'Database tidak tersedia'], 500);
            return;
        }

        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            $response->json(['error' => 'Kategori tidak valid'], 422);
            return;
        }

        $usedByNews = $this->categories->newsCount($id);
        if ($usedByNews > 0) {
            $response->json(['error' => 'Kategori masih digunakan oleh berita', 'news_count' => $usedByNews], 409);
            return;
        }

        if (!$this->categories->delete($id)) {
            $response->json(['error' => 'Kategori tidak ditemukan'], 404);
            return;
        }

        $response->json(['data' => ['id' => $id, 'deleted' => true]]);
    }

    private function validatedName(Request $request): string
    {
        $body = $request->json();
        if ($body === []) {
            $body = $_POST;
        }

        $name = trim((string) ($body['name'] ?? $body['category_name'] ?? ''));
        $name = preg_replace('/\s+/', ' ', $name) ?? '';

        $name = function_exists('mb_substr') ? mb_substr($name, 0, 60) : substr($name, 0, 60);

        return strip_tags($name);
    }
}
