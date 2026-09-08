<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotoGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhotoGalleryController extends Controller
{
    public function index(Request $request)
    {
        $photos = PhotoGallery::query()
            ->orderBy('sort_order')
            ->latest('created_at')
            ->paginate(max(1, min(100, (int) $request->query('per_page', 100))));

        return response()->json([
            'success' => true,
            'data' => $photos->getCollection()->map(fn (PhotoGallery $photo) => $this->mapPhoto($photo))->values(),
            'meta' => ['page' => $photos->currentPage(), 'per_page' => $photos->perPage(), 'total' => $photos->total()],
        ]);
    }

    public function show(int $id)
    {
        return response()->json(['success' => true, 'data' => $this->mapPhoto(PhotoGallery::findOrFail($id))]);
    }

    public function store(Request $request)
    {
        $photo = PhotoGallery::create($this->payload($request, true));
        return response()->json(['success' => true, 'data' => ['id' => $photo->photo_id]], 201);
    }

    public function update(Request $request, int $id)
    {
        $photo = PhotoGallery::findOrFail($id);
        $photo->update($this->payload($request, false));
        return response()->json(['success' => true, 'data' => ['id' => $photo->photo_id, 'updated' => true]]);
    }

    public function destroy(int $id)
    {
        $photo = PhotoGallery::findOrFail($id);
        $photo->delete();
        return response()->json(['success' => true, 'data' => ['id' => $id, 'deleted' => true]]);
    }

    public function upload(Request $request)
    {
        $request->validate(['image' => 'required|file|image|mimes:jpg,jpeg,png,webp,gif|max:5120']);

        $file = $request->file('image');
        $filename = 'gallery-' . Str::random(24) . '.' . $file->extension();
        $directory = public_path('uploads/gallery');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $file->move($directory, $filename);

        $path = '/uploads/gallery/' . $filename;
        return response()->json(['success' => true, 'data' => [
            'path' => $path,
            'url' => url($path),
            'filename' => $filename,
        ]], 201);
    }

    /** @return array<string, mixed> */
    private function payload(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'title' => ($creating ? 'required' : 'sometimes') . '|string|max:255',
            'image' => ($creating ? 'required' : 'sometimes') . '|string|max:1000',
            'caption' => 'nullable|string|max:1000',
            'status' => 'nullable|in:show,hide',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $payload = [];
        if (array_key_exists('title', $data)) $payload['title'] = trim(strip_tags((string) $data['title']));
        if (array_key_exists('image', $data)) {
            $image = $this->normaliseImagePath((string) $data['image']);
            abort_if($image === '', 422, 'Path gambar galeri tidak valid.');
            $payload['image_url'] = $image;
        }
        if (array_key_exists('caption', $data)) $payload['caption'] = trim(strip_tags((string) $data['caption']));
        if (array_key_exists('status', $data)) $payload['status'] = $data['status'];
        if (array_key_exists('sort_order', $data)) $payload['sort_order'] = (int) $data['sort_order'];
        if ($creating) $payload += ['caption' => '', 'status' => 'show', 'sort_order' => 0];
        return $payload;
    }

    private function normaliseImagePath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || str_contains($path, "\0")) return '';
        if (preg_match('#^https?://#i', $path)) {
            $parsed = parse_url($path, PHP_URL_PATH) ?: '';
            if (!str_starts_with($parsed, '/uploads/gallery/')) return $path;
            $path = $parsed;
        }
        if (!str_starts_with($path, '/uploads/gallery/')) return '';
        if (!preg_match('#^/uploads/gallery/([A-Za-z0-9][A-Za-z0-9._-]*)$#', $path, $matches)) return '';
        return '/uploads/gallery/' . $matches[1];
    }

    /** @return array<string, mixed> */
    private function mapPhoto(PhotoGallery $photo): array
    {
        $path = (string) $photo->image_url;
        return [
            'id' => $photo->photo_id,
            'photo_id' => $photo->photo_id,
            'title' => (string) $photo->title,
            'image' => preg_match('#^https?://#i', $path) ? $path : url(str_starts_with($path, '/') ? $path : '/uploads/gallery/' . ltrim($path, '/')),
            'caption' => (string) $photo->caption,
            'status' => (string) $photo->status,
            'sort_order' => (int) $photo->sort_order,
        ];
    }
}
