<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Prestasi;
use App\Models\PrestasiToken;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PrestasiController extends Controller
{
    private const MAX_UPLOAD_FILES = 6;
    private const MAX_UPLOAD_SIZE_KB = 5120;
    private const ALLOWED_IMAGE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', $request->input('limit', 12));
        $page = $request->input('page', 1);
        $activeQ = $request->input('q');
        $activeCategory = $request->input('category');
        $activeYear = $request->input('year');
        $layout = $request->input('layout', 'grid');

        $resolveImageUrl = function($path) {
            return \App\Services\ImageResolver::resolve($path, '/uploads/slider-4.png');
        };

        $query = Prestasi::published()->latestPrestasi();

        if ($activeQ !== null && $activeQ !== '' && $activeQ !== 'Semua' && $activeQ !== 'All') {
            $query->where('title', 'like', '%' . $activeQ . '%');
        }
        if ($activeCategory !== null && $activeCategory !== '' && $activeCategory !== 'Semua' && $activeCategory !== 'All') {
            $query->where('category', $activeCategory);
        }
        if ($activeYear !== null && $activeYear !== '' && $activeYear !== 'Semua' && $activeYear !== 'All') {
            $query->where('year', $activeYear);
        }

        $paginator = $query->paginate($perPage);

        $items = $paginator->map(function($p) use ($resolveImageUrl) {
            return [
                'id' => $p->id,
                'slug' => $p->slug,
                'title' => $p->title,
                'category' => $p->category,
                'year' => $p->year,
                'description' => $p->description,
                'name' => current(array_filter([$p->member_name, $p->institution_name, ''])),
                'institution' => current(array_filter([$p->institution, $p->campus_name, ''])),
                'image' => $resolveImageUrl($p->photo),
            ];
        })->values()->toArray();

        $categories = Prestasi::select('category')->distinct()->pluck('category')->filter()->toArray();
        sort($categories);
        $years = Prestasi::select('year')->distinct()->pluck('year')->filter()->toArray();
        rsort($years);

        if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
            return response()->json([
                'items' => $items,
                'meta' => [
                    'page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ]
            ]);
        }

        return view('public.prestasi.index', [
            'items' => $items,
            'filters' => [
                'q' => $activeQ ?? '',
                'category' => $activeCategory ?? '',
                'year' => $activeYear ?? '',
                'layout' => $layout
            ],
            'filterOptions' => [
                'categories' => $categories,
                'years' => $years
            ],
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
            'totalPages' => $paginator->lastPage(),
            'scripts' => '<script defer src="/assets/js/dist/pages/prestasi.js"></script>',
        ]);
    }

    public function show(Request $request, $slug)
    {
        $resolveImageUrl = function($path) {
            return \App\Services\ImageResolver::resolve($path, '/uploads/slider-4.png');
        };

        $prestasiItem = Prestasi::published()->where('slug', $slug)->first();
        
        if (!$prestasiItem) {
            if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
                return response()->json(['error' => 'Not found'], 404);
            }
            abort(404);
        }

        $item = [
            'id' => $prestasiItem->id,
            'slug' => $prestasiItem->slug,
            'title' => $prestasiItem->title,
            'category' => $prestasiItem->category,
            'year' => $prestasiItem->year,
            'description' => $prestasiItem->description,
            'content' => $prestasiItem->detail ?? $prestasiItem->content,
            'name' => current(array_filter([$prestasiItem->member_name, $prestasiItem->institution_name, ''])),
            'institution' => current(array_filter([$prestasiItem->institution, $prestasiItem->campus_name, ''])),
            'image' => $resolveImageUrl($prestasiItem->photo),
        ];

        if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
            return response()->json(['data' => $item]);
        }

        return view('public.prestasi.show', [
            'item' => $item,
            'seo' => [
                'canonical' => url()->current()
            ],
            'scripts' => '<script defer src="/assets/js/dist/pages/prestasi-detail.js"></script>',
        ]);
    }

    public function submissionForm(Request $request, string $token)
    {
        if ($this->expectsJson($request)) {
            $prestasiToken = PrestasiToken::findAvailableByPlainToken($token);

            if (!$prestasiToken) {
                return response()->json([
                    'error' => 'Token tidak valid, kedaluwarsa, sudah digunakan, atau sudah dicabut.',
                ], 403);
            }

            return response()->json([
                'data' => [
                    'valid' => true,
                    'label' => (string) ($prestasiToken->label ?? ''),
                ],
            ]);
        }

        $seo = SeoService::forPage('prestasi.html');
        $seo = array_merge($seo, [
            'title' => 'Form Pengajuan Prestasi | GenBI Provinsi Jambi',
            'description' => 'Form privat untuk mengajukan prestasi anggota GenBI Provinsi Jambi.',
            'canonical' => url()->current(),
            'robots' => 'noindex, nofollow',
            'og_title' => 'Form Pengajuan Prestasi | GenBI Provinsi Jambi',
            'og_description' => 'Form privat untuk mengajukan prestasi anggota GenBI Provinsi Jambi.',
            'og_url' => url()->current(),
            'twitter_title' => 'Form Pengajuan Prestasi | GenBI Provinsi Jambi',
            'twitter_description' => 'Form privat untuk mengajukan prestasi anggota GenBI Provinsi Jambi.',
        ]);

        return view('public.prestasi.submit', [
            'meta' => SeoService::renderMetaBlock($seo),
            'bodyClass' => 'page-prestasi-submit',
            'scripts' => '<script defer src="/assets/js/dist/pages/prestasi-submit.js?v=20260730a"></script>',
        ]);
    }

    public function submitWithToken(Request $request, string $token)
    {
        if (!PrestasiToken::findAvailableByPlainToken($token)) {
            return response()->json([
                'error' => 'Token tidak valid, kedaluwarsa, sudah digunakan, atau sudah dicabut.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'between:1900,2099'],
            'campus' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:120'],
            'institution' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'content' => ['nullable', 'string', 'max:50000'],
            'image_url' => ['nullable', 'url:http,https', 'max:120'],
            'photos' => ['nullable', 'array', 'max:' . self::MAX_UPLOAD_FILES],
            'photos.*' => [
                'file',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:' . self::MAX_UPLOAD_SIZE_KB,
            ],
        ], [
            'title.required' => 'Judul prestasi wajib diisi.',
            'category.required' => 'Kategori wajib diisi.',
            'year.required' => 'Tahun wajib diisi.',
            'year.between' => 'Tahun harus berada antara 1900 dan 2099.',
            'campus.required' => 'Komisariat wajib diisi.',
            'name.required' => 'Nama anggota wajib diisi.',
            'photos.max' => 'Maksimal ' . self::MAX_UPLOAD_FILES . ' foto dapat diunggah.',
            'photos.*.mimes' => 'Foto harus berformat JPG, PNG, WebP, atau GIF.',
            'photos.*.max' => 'Ukuran setiap foto maksimal 5MB.',
            'image_url.max' => 'URL gambar terlalu panjang.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validasi gagal.',
                'details' => $validator->errors()->all(),
            ], 422);
        }

        $body = $this->sanitizeSubmission($validator->validated());
        $uploadedImages = [];

        try {
            $uploadedImages = $this->storeSubmissionImages($request->file('photos', []));
        } catch (\InvalidArgumentException $exception) {
            $this->deleteUploadedImages($uploadedImages);

            return response()->json([
                'error' => 'Validasi gagal.',
                'details' => [$exception->getMessage()],
            ], 422);
        } catch (\Throwable $exception) {
            $this->deleteUploadedImages($uploadedImages);
            Log::error('Prestasi submission upload failed.', [
                'exception' => $exception,
                'token_prefix' => substr(hash('sha256', trim($token)), 0, 12),
            ]);

            return response()->json([
                'error' => 'Gagal menyimpan foto. Silakan coba lagi.',
            ], 500);
        }

        DB::beginTransaction();

        try {
            $prestasiToken = PrestasiToken::findAvailableByPlainToken($token, true);

            if (!$prestasiToken) {
                DB::rollBack();
                $this->deleteUploadedImages($uploadedImages);

                return response()->json([
                    'error' => 'Token tidak valid, kedaluwarsa, sudah digunakan, atau sudah dicabut.',
                ], 403);
            }

            $slug = $this->generateUniqueSlug($body['title']);
            $primaryImage = $uploadedImages[0]['url'] ?? ($body['image_url'] ?: null);
            $seo = $this->buildSubmissionSeo($body);

            $prestasi = Prestasi::create([
                'title' => $body['title'],
                'slug' => $slug,
                'category' => $body['category'],
                'year' => (int) $body['year'],
                'member_name' => $body['name'],
                'institution' => $body['institution'] ?: null,
                'description' => $body['description'],
                'detail' => $this->safeDetailHtml($body['content']),
                'photo' => $primaryImage,
                'status' => 'draft',
                'is_featured' => 0,
                'meta_title' => $seo['meta_title'],
                'meta_keyword' => $seo['meta_keyword'],
                'meta_description' => $seo['meta_description'],
                'created_at' => now(),
            ]);

            DB::table('tbl_prestasi_submission')->insert([
                'token_id' => $prestasiToken->getKey(),
                'prestasi_id' => $prestasi->getKey(),
                'submitter_name' => $body['name'],
                'submitter_email' => 'token-submission@genbijambi.local',
                'payload_json' => json_encode([
                    'title' => $body['title'],
                    'category' => $body['category'],
                    'year' => $body['year'],
                    'campus' => $body['campus'],
                    'name' => $body['name'],
                    'institution' => $body['institution'],
                    'description' => $body['description'],
                    'content' => $body['content'],
                    'image_url' => $body['image_url'],
                    'photos' => $uploadedImages,
                ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip_address' => mb_substr((string) $request->ip(), 0, 45),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
                'created_at' => now(),
            ]);

            $prestasiToken->recordUse();
            DB::commit();

            return response()->json([
                'data' => [
                    'id' => $prestasi->getKey(),
                    'status' => 'pending',
                ],
            ], 201);
        } catch (\Throwable $exception) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $this->deleteUploadedImages($uploadedImages);

            $requestId = 'prestasi_submit_' . Str::lower(Str::random(16));
            Log::error('Prestasi token submission failed.', [
                'request_id' => $requestId,
                'exception' => $exception,
                'token_prefix' => substr(hash('sha256', trim($token)), 0, 12),
                'title_length' => mb_strlen($body['title']),
                'upload_count' => count($uploadedImages),
            ]);

            return response()->json([
                'error' => 'Gagal menyimpan data.',
                'request_id' => $requestId,
            ], 500);
        }
    }

    private function expectsJson(Request $request): bool
    {
        return $request->wantsJson()
            || $request->ajax()
            || str_contains($request->header('Accept', ''), 'application/json');
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, string>
     */
    private function sanitizeSubmission(array $validated): array
    {
        return [
            'title' => $this->sanitizeText($validated['title'] ?? '', 255),
            'category' => $this->sanitizeText($validated['category'] ?? '', 100),
            'year' => $this->sanitizeText((string) ($validated['year'] ?? ''), 4),
            'campus' => $this->sanitizeText($validated['campus'] ?? '', 255),
            'name' => $this->sanitizeText($validated['name'] ?? '', 120),
            'institution' => $this->sanitizeText($validated['institution'] ?? '', 120),
            'description' => $this->sanitizeText($validated['description'] ?? '', 5000),
            'content' => $this->sanitizeText($validated['content'] ?? '', 50000),
            'image_url' => trim((string) ($validated['image_url'] ?? '')),
        ];
    }

    private function sanitizeText(mixed $value, int $maxLength): string
    {
        $text = trim(strip_tags(is_scalar($value) ? (string) $value : ''));
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? '';

        return mb_substr(trim($text), 0, $maxLength);
    }

    private function safeDetailHtml(string $content): string
    {
        return nl2br(e($content));
    }

    /**
     * @param array<int, UploadedFile>|UploadedFile|null $files
     * @return array<int, array{url: string, filename: string, mime: string, size: int}>
     */
    private function storeSubmissionImages(array|UploadedFile|null $files): array
    {
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }
        if (!is_array($files) || $files === []) {
            return [];
        }
        if (count($files) > self::MAX_UPLOAD_FILES) {
            throw new \InvalidArgumentException('Maksimal ' . self::MAX_UPLOAD_FILES . ' foto dapat diunggah.');
        }

        $validatedFiles = [];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        foreach (array_values($files) as $index => $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                throw new \InvalidArgumentException('Upload foto #' . ($index + 1) . ' gagal.');
            }

            $path = $file->getRealPath();
            $mime = $path ? $finfo->file($path) : false;

            if (!is_string($mime) || !isset(self::ALLOWED_IMAGE_TYPES[$mime])) {
                throw new \InvalidArgumentException(
                    'Tipe file foto #' . ($index + 1) . ' tidak diizinkan. Gunakan JPEG, PNG, WebP, atau GIF.'
                );
            }
            if (!$path || @getimagesize($path) === false) {
                throw new \InvalidArgumentException('File foto #' . ($index + 1) . ' bukan gambar yang valid.');
            }

            $validatedFiles[] = [$file, $mime, self::ALLOWED_IMAGE_TYPES[$mime]];
        }

        $destination = public_path('uploads/prestasi');
        if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination)) {
            throw new \RuntimeException('Direktori upload prestasi tidak dapat dibuat.');
        }

        $stored = [];
        try {
            foreach ($validatedFiles as [$file, $mime, $extension]) {
                $filename = 'prestasi-submit-' . Str::lower(Str::random(24)) . '.' . $extension;
                $size = (int) $file->getSize();
                $file->move($destination, $filename);
                $stored[] = [
                    'url' => '/uploads/prestasi/' . $filename,
                    'filename' => $filename,
                    'mime' => $mime,
                    'size' => $size,
                ];
            }
        } catch (\Throwable $exception) {
            $this->deleteUploadedImages($stored);
            throw $exception;
        }

        return $stored;
    }

    /** @param array<int, array{url: string, filename: string, mime: string, size: int}> $uploadedImages */
    private function deleteUploadedImages(array $uploadedImages): void
    {
        foreach ($uploadedImages as $image) {
            $filename = basename((string) ($image['filename'] ?? ''));
            if ($filename === '') {
                continue;
            }

            $path = public_path('uploads/prestasi/' . $filename);
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'prestasi';
        $slug = $base;
        $suffix = 1;

        while (Prestasi::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;

            if ($suffix > 100) {
                return $base . '-' . Str::lower(Str::random(8));
            }
        }

        return $slug;
    }

    /** @param array<string, string> $body */
    private function buildSubmissionSeo(array $body): array
    {
        $title = mb_substr($body['title'] ?: 'Prestasi GenBI Jambi', 0, 180);
        $summary = $body['description'] ?: trim(
            $body['category']
            . ($body['name'] !== '' ? ' ' . $body['name'] : '')
            . ($body['institution'] !== '' ? ' oleh ' . $body['institution'] : '')
            . ' tahun ' . $body['year']
            . '. Dokumentasi prestasi GenBI Jambi.'
        );

        return [
            'meta_title' => mb_substr($title . ' | GenBI Jambi', 0, 255),
            'meta_keyword' => mb_substr(implode(', ', array_filter([
                $body['category'],
                'prestasi GenBI Jambi',
                $body['name'],
                $body['institution'],
                $body['year'],
            ])), 0, 1000),
            'meta_description' => mb_substr($summary, 0, 1000),
        ];
    }
}
