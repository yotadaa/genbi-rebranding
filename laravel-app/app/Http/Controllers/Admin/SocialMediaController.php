<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Social;
use App\Services\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SocialMediaController extends Controller
{
    private const CHANNELS = [
        'YouTube' => ['icon' => 'fa fa-youtube', 'default' => 'https://youtube.com/@genbijambi'],
        'Instagram' => ['icon' => 'fa fa-instagram', 'default' => 'https://instagram.com/genbijambi'],
        'WhatsApp' => ['icon' => 'fa fa-whatsapp', 'default' => 'https://wa.me/6289627896750'],
    ];

    public function index()
    {
        return response()->json(['success' => true, 'data' => $this->channels()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.name' => 'required|string|max:30',
            'items.*.url' => [
                'nullable',
                'string',
                'max:60',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value !== null && $value !== '' && !preg_match('#^https?://#i', (string) $value)) {
                        $fail('URL social media harus menggunakan http:// atau https://.');
                    }
                },
            ],
        ]);

        $submitted = collect($data['items'])->keyBy(fn (array $item) => strtolower(trim((string) $item['name'])));
        DB::transaction(function () use ($submitted) {
            foreach (self::CHANNELS as $name => $configuration) {
                $item = $submitted->get(strtolower($name));
                if (!is_array($item)) {
                    throw ValidationException::withMessages(['items' => "Data {$name} tidak ditemukan."]);
                }

                $social = Social::query()
                    ->whereRaw('LOWER(social_name) = ?', [strtolower($name)])
                    ->first() ?? new Social(['social_name' => $name]);
                $social->social_url = trim((string) ($item['url'] ?? ''));
                $social->social_icon = $social->social_icon ?: $configuration['icon'];
                $social->save();
            }
        });

        SiteSettings::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Social media berhasil diperbarui.',
            'data' => $this->channels(),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function channels(): array
    {
        $stored = Social::query()->get()->keyBy(fn (Social $social) => strtolower((string) $social->social_name));

        return collect(self::CHANNELS)->map(function (array $configuration, string $name) use ($stored) {
            $social = $stored->get(strtolower($name));
            return [
                'id' => $social?->social_id,
                'name' => $name,
                'url' => $social ? (string) $social->social_url : $configuration['default'],
                'icon' => $social ? (string) $social->social_icon : $configuration['icon'],
            ];
        })->values()->all();
    }
}
