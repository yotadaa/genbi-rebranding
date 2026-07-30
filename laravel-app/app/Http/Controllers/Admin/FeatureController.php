<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\FeatureImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FeatureController extends Controller
{
    private const ICON_KEYS = ['sparkles', 'users', 'bank', 'chart', 'academic', 'calendar', 'heart', 'news', 'grid'];

    public function index(Request $request)
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 25)));
        $query = Feature::with('images')->orderBy('sort_order')->orderByDesc('id');

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('focus', 'like', "%{$search}%");
            });
        }
        if (in_array($request->query('status'), ['draft', 'published', 'archived'], true)) {
            $query->where('status', $request->query('status'));
        }
        if ($request->filled('show_on_home')) {
            $query->where('show_on_home', $request->boolean('show_on_home'));
        }

        $features = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $features->getCollection()->map(fn (Feature $feature) => $this->mapFeature($feature))->values(),
            'meta' => [
                'page' => $features->currentPage(),
                'per_page' => $features->perPage(),
                'total' => $features->total(),
                'total_pages' => $features->lastPage(),
            ],
        ]);
    }

    public function show(int $id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->mapFeature(Feature::with('images')->findOrFail($id)),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->payload($request, true);
        $images = $payload['images'];
        unset($payload['images']);

        $feature = DB::transaction(function () use ($payload, $images) {
            $feature = Feature::create($payload);
            $this->syncImages($feature, $images);
            return $feature;
        });

        return response()->json(['success' => true, 'data' => ['id' => $feature->id]], 201);
    }

    public function update(Request $request, int $id)
    {
        $feature = Feature::findOrFail($id);
        $payload = $this->payload($request, false);
        $images = $payload['images'] ?? null;
        unset($payload['images']);

        DB::transaction(function () use ($feature, $payload, $images) {
            if ($payload !== []) {
                $feature->update($payload);
            }
            if (is_array($images)) {
                $this->syncImages($feature, $images);
            }
        });

        return response()->json(['success' => true, 'data' => ['id' => $feature->id, 'updated' => true]]);
    }

    public function destroy(int $id)
    {
        $feature = Feature::findOrFail($id);
        $feature->update(['show_on_home' => false, 'status' => 'archived']);
        $feature->delete();

        return response()->json(['success' => true, 'data' => ['id' => $id, 'deleted' => true]]);
    }

    public function upload(Request $request)
    {
        $request->validate(['image' => 'required|file|image|mimes:jpg,jpeg,png,webp,gif|max:5120']);

        $file = $request->file('image');
        $filename = 'feature-' . Str::random(24) . '.' . $file->extension();
        $directory = public_path('uploads/features');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $file->move($directory, $filename);

        $path = '/uploads/features/' . $filename;
        return response()->json(['success' => true, 'data' => [
            'path' => $path,
            'url' => url($path),
            'filename' => $filename,
        ]], 201);
    }

    public function deleteImage(int $id, int $imageId)
    {
        $image = FeatureImage::query()->where('feature_id', $id)->findOrFail($imageId);
        $path = (string) $image->image_path;
        $image->delete();
        $this->removeLocalUpload($path, 'features');

        return response()->json(['success' => true, 'data' => ['id' => $imageId, 'deleted' => true]]);
    }

    public function reorderImages(Request $request, int $id)
    {
        $data = $request->validate(['image_ids' => 'required|array', 'image_ids.*' => 'integer']);
        $imageIds = array_values(array_unique(array_map('intval', $data['image_ids'])));
        $images = FeatureImage::query()->where('feature_id', $id)->whereIn('id', $imageIds)->get()->keyBy('id');

        if ($images->count() !== count($imageIds)) {
            return response()->json(['error' => 'Gambar Program Utama tidak valid.'], 422);
        }

        DB::transaction(function () use ($images, $imageIds) {
            foreach ($imageIds as $order => $imageId) {
                $images[$imageId]->update(['sort_order' => $order]);
            }
        });

        return response()->json(['success' => true, 'data' => ['feature_id' => $id, 'reordered' => true]]);
    }

    /** @return array<string, mixed> */
    private function payload(Request $request, bool $creating): array
    {
        $rules = [
            'title' => ($creating ? 'required' : 'sometimes') . '|string|max:120',
            'name' => ($creating ? 'required' : 'sometimes') . '|string|max:255',
            'description' => 'nullable|string|max:5000',
            'focus' => 'nullable|string|max:120',
            'icon_key' => 'nullable|in:' . implode(',', self::ICON_KEYS),
            'show_on_home' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'nullable|in:draft,published,archived',
            'images' => 'nullable|array',
            'images.*.id' => 'nullable|integer',
            'images.*.path' => 'required_with:images|string|max:1000',
        ];
        $data = $request->validate($rules);
        $payload = [];

        foreach (['title', 'name', 'focus'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = trim(strip_tags((string) $data[$field]));
            }
        }
        if (array_key_exists('description', $data)) {
            $payload['description'] = trim(strip_tags((string) $data['description']));
            $payload['content'] = $payload['description'];
        }
        if (array_key_exists('icon_key', $data)) {
            $payload['icon_key'] = $data['icon_key'] ?: 'sparkles';
            $payload['icon'] = $payload['icon_key'];
        }
        foreach (['show_on_home', 'sort_order', 'status'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $field === 'show_on_home' ? (bool) $data[$field] : $data[$field];
            }
        }
        if ($creating) {
            $payload += [
                'content' => $payload['description'] ?? '',
                'icon' => $payload['icon_key'] ?? 'sparkles',
                'description' => $payload['description'] ?? '',
                'focus' => $payload['focus'] ?? '',
                'icon_key' => $payload['icon_key'] ?? 'sparkles',
                'show_on_home' => $payload['show_on_home'] ?? false,
                'sort_order' => $payload['sort_order'] ?? 0,
                'status' => $payload['status'] ?? 'draft',
            ];
        }
        if (array_key_exists('images', $data)) {
            $payload['images'] = $this->normaliseImages($data['images']);
        }

        return $payload;
    }

    /** @param array<int, array<string, mixed>> $images */
    private function syncImages(Feature $feature, array $images): void
    {
        $existing = $feature->images()->get()->keyBy('id');
        $incomingIds = collect($images)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

        foreach ($incomingIds as $imageId) {
            abort_unless($existing->has($imageId), 422, 'Gambar Program Utama tidak valid.');
        }
        foreach ($existing->except($incomingIds) as $image) {
            $path = (string) $image->image_path;
            $image->delete();
            $this->removeLocalUpload($path, 'features');
        }
        foreach (array_values($images) as $order => $image) {
            $attributes = ['image_path' => $image['path'], 'sort_order' => $order];
            if (!empty($image['id'])) {
                $existing[(int) $image['id']]->update($attributes);
            } else {
                $feature->images()->create($attributes);
            }
        }
    }

    /** @param array<int, array<string, mixed>> $images @return array<int, array{id:int,path:string}> */
    private function normaliseImages(array $images): array
    {
        $normalised = [];
        foreach ($images as $image) {
            $path = $this->normaliseImagePath((string) ($image['path'] ?? $image['url'] ?? ''));
            if ($path === '') {
                abort(422, 'Path gambar Program Utama tidak valid.');
            }
            $normalised[] = ['id' => max(0, (int) ($image['id'] ?? 0)), 'path' => $path];
        }
        return $normalised;
    }

    private function normaliseImagePath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || str_contains($path, "\0")) {
            return '';
        }
        if (preg_match('#^https?://#i', $path)) {
            $parsed = parse_url($path, PHP_URL_PATH) ?: '';
            if (!str_starts_with($parsed, '/uploads/features/')) {
                return $path;
            }
            $path = $parsed;
        }
        if (!str_starts_with($path, '/uploads/features/')) {
            return '';
        }
        if (!preg_match('#^/uploads/features/([A-Za-z0-9][A-Za-z0-9._-]*)$#', $path, $matches)) {
            return '';
        }
        return '/uploads/features/' . $matches[1];
    }

    /** @return array<string, mixed> */
    private function mapFeature(Feature $feature): array
    {
        $images = $feature->images->map(fn (FeatureImage $image) => [
            'id' => $image->id,
            'feature_id' => $feature->id,
            'path' => $image->image_path,
            'url' => $this->imageUrl((string) $image->image_path, 'features'),
            'sort_order' => $image->sort_order,
        ])->values();

        return [
            'id' => $feature->id,
            'feature_id' => $feature->id,
            'title' => (string) $feature->title,
            'name' => (string) $feature->name,
            'description' => (string) ($feature->description ?: $feature->content),
            'focus' => (string) $feature->focus,
            'icon_key' => (string) ($feature->icon_key ?: $feature->icon ?: 'sparkles'),
            'show_on_home' => (bool) $feature->show_on_home,
            'sort_order' => (int) $feature->sort_order,
            'status' => (string) $feature->status,
            'images' => $images,
            'image' => $images->first()['url'] ?? '',
        ];
    }

    private function imageUrl(string $path, string $directory): string
    {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        return url(str_starts_with($path, '/') ? $path : "/uploads/{$directory}/" . ltrim($path, '/'));
    }

    private function removeLocalUpload(string $path, string $directory): void
    {
        if (!str_starts_with($path, "/uploads/{$directory}/")) {
            return;
        }
        $target = public_path('uploads/' . $directory . '/' . basename($path));
        if (is_file($target)) {
            @unlink($target);
        }
    }
}
