<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrestasiSubmissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('tbl_prestasi_submission_token', function (Blueprint $table): void {
            $table->increments('token_id');
            $table->char('token_hash', 64)->unique();
            $table->string('label', 120)->nullable();
            $table->string('intended_for', 120)->nullable();
            $table->unsignedInteger('max_uses')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('used_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('revoked_at')->nullable();
        });

        Schema::create('tbl_prestasi', function (Blueprint $table): void {
            $table->increments('prestasi_id');
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('category', 100);
            $table->integer('year');
            $table->string('member_name', 120);
            $table->string('institution', 120)->nullable();
            $table->text('description');
            $table->longText('detail')->nullable();
            $table->string('photo', 120)->nullable();
            $table->string('certificate_photo', 120)->nullable();
            $table->string('status', 20)->default('published');
            $table->boolean('is_featured')->default(false);
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_keyword')->nullable();
            $table->text('meta_description')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('tbl_prestasi_submission', function (Blueprint $table): void {
            $table->increments('submission_id');
            $table->unsignedInteger('token_id');
            $table->unsignedInteger('prestasi_id')->nullable();
            $table->string('submitter_name', 120);
            $table->string('submitter_email', 120);
            $table->longText('payload_json');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->dateTime('created_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        if (Schema::hasTable('tbl_prestasi')) {
            DB::table('tbl_prestasi')
                ->pluck('photo')
                ->filter(fn ($photo): bool => is_string($photo) && str_starts_with($photo, '/uploads/prestasi/prestasi-submit-'))
                ->each(function (string $photo): void {
                    $path = public_path(ltrim($photo, '/'));
                    if (is_file($path)) {
                        @unlink($path);
                    }
                });
        }

        Schema::dropIfExists('tbl_prestasi_submission');
        Schema::dropIfExists('tbl_prestasi');
        Schema::dropIfExists('tbl_prestasi_submission_token');

        parent::tearDown();
    }

    public function test_submission_page_reuses_public_layout_and_is_noindex(): void
    {
        $response = $this->get('/prestasi/submit/AbCxyzToken1234567890');

        $response->assertOk()
            ->assertSee('id="site-header"', false)
            ->assertSee('id="prestasi-submit-root"', false)
            ->assertSee('Form Pengajuan Prestasi')
            ->assertSee('noindex, nofollow')
            ->assertSee('/assets/js/dist/pages/prestasi-submit.js', false);
    }

    public function test_json_validation_accepts_an_active_alphanumeric_token(): void
    {
        $token = 'AbCxyzToken1234567890BeyondHex';
        $this->insertToken($token, maxUses: 1);

        $this->withHeader('Accept', 'application/json')
            ->get('/prestasi/submit/'.$token)
            ->assertOk()
            ->assertJsonPath('data.valid', true)
            ->assertJsonPath('data.label', 'Token Pengujian');
    }

    public function test_submission_creates_a_draft_log_and_consumes_single_use_token(): void
    {
        $token = 'SingleUseTokenXYZ123456789';
        $this->insertToken($token, maxUses: 1);

        $payload = [
            'title' => 'Juara Inovasi 2026',
            'category' => 'Inovasi',
            'year' => 2026,
            'campus' => 'Universitas Jambi',
            'name' => 'Siti Nur Anisa',
            'institution' => 'Bank Indonesia',
            'description' => 'Prestasi tingkat nasional.',
            'content' => "<script>alert('x')</script>\nDokumentasi prestasi.",
            'image_url' => 'https://example.com/photo.jpg',
        ];

        $response = $this->postJson('/prestasi/submit/'.$token, $payload);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('tbl_prestasi', [
            'title' => 'Juara Inovasi 2026',
            'member_name' => 'Siti Nur Anisa',
            'status' => 'draft',
            'photo' => 'https://example.com/photo.jpg',
        ]);
        $this->assertDatabaseCount('tbl_prestasi_submission', 1);

        $prestasi = DB::table('tbl_prestasi')->first();
        $this->assertStringNotContainsString('<script', (string) $prestasi->detail);
        $this->assertStringContainsString('Dokumentasi prestasi.', (string) $prestasi->detail);

        $tokenRow = DB::table('tbl_prestasi_submission_token')->first();
        $this->assertSame(1, (int) $tokenRow->used_count);
        $this->assertNotNull($tokenRow->used_at);

        $this->postJson('/prestasi/submit/'.$token, $payload)
            ->assertForbidden();
        $this->assertDatabaseCount('tbl_prestasi', 1);
    }

    public function test_invalid_or_expired_token_is_rejected_without_creating_data(): void
    {
        $token = 'ExpiredTokenXYZ123456789';
        $this->insertToken($token, maxUses: 1, expiresAt: now()->subMinute());

        $this->withHeader('Accept', 'application/json')
            ->get('/prestasi/submit/'.$token)
            ->assertForbidden();

        $this->postJson('/prestasi/submit/'.$token, [
            'title' => 'Tidak boleh tersimpan',
        ])->assertForbidden();

        $this->assertDatabaseCount('tbl_prestasi', 0);
        $this->assertDatabaseCount('tbl_prestasi_submission', 0);
    }

    public function test_valid_image_upload_is_verified_stored_and_logged(): void
    {
        $token = 'UploadTokenXYZ123456789';
        $this->insertToken($token, maxUses: 1);
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );
        $this->assertIsString($png);

        $response = $this->post('/prestasi/submit/'.$token, [
            'title' => 'Prestasi dengan Foto',
            'category' => 'Kreativitas',
            'year' => 2026,
            'campus' => 'Universitas Jambi',
            'name' => 'Anggota GenBI',
            'photos' => [
                UploadedFile::fake()->createWithContent('prestasi.png', $png),
            ],
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertCreated();

        $prestasi = DB::table('tbl_prestasi')->first();
        $this->assertStringStartsWith('/uploads/prestasi/prestasi-submit-', (string) $prestasi->photo);
        $this->assertFileExists(public_path(ltrim((string) $prestasi->photo, '/')));

        $submission = DB::table('tbl_prestasi_submission')->first();
        $payload = json_decode((string) $submission->payload_json, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('image/png', $payload['photos'][0]['mime']);
        $this->assertSame($prestasi->photo, $payload['photos'][0]['url']);
    }

    private function insertToken(
        string $plainToken,
        int $maxUses,
        ?\DateTimeInterface $expiresAt = null
    ): void {
        DB::table('tbl_prestasi_submission_token')->insert([
            'token_hash' => hash('sha256', $plainToken),
            'label' => 'Token Pengujian',
            'max_uses' => $maxUses,
            'used_count' => 0,
            'expires_at' => $expiresAt ?? now()->addHour(),
            'created_at' => now(),
        ]);
    }
}
