<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 25)));
        $query = Faq::query()->orderBy('faq_id');

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('faq_title', 'like', "%{$search}%")
                    ->orWhere('faq_content', 'like', "%{$search}%");
            });
        }

        $faqs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $faqs->getCollection()->map(fn (Faq $faq) => $this->mapFaq($faq))->values(),
            'meta' => [
                'page' => $faqs->currentPage(),
                'per_page' => $faqs->perPage(),
                'total' => $faqs->total(),
                'total_pages' => $faqs->lastPage(),
            ],
        ]);
    }

    public function show(int $id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->mapFaq(Faq::findOrFail($id)),
        ]);
    }

    public function store(Request $request)
    {
        $faq = Faq::create($this->payload($request, true));

        return response()->json([
            'success' => true,
            'data' => ['id' => $faq->faq_id],
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $faq = Faq::findOrFail($id);
        $faq->update($this->payload($request, false));

        return response()->json([
            'success' => true,
            'data' => ['id' => $faq->faq_id, 'updated' => true],
        ]);
    }

    public function destroy(int $id)
    {
        Faq::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'data' => ['id' => $id, 'deleted' => true],
        ]);
    }

    /** @return array<string, string> */
    private function payload(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'title' => ($creating ? 'required' : 'sometimes') . '|string|max:60',
            'content' => ($creating ? 'required' : 'sometimes') . '|string|max:10000',
            'show_on_home' => 'nullable|boolean',
        ]);
        $payload = [];

        if (array_key_exists('title', $data)) {
            $payload['faq_title'] = trim(strip_tags((string) $data['title']));
        }
        if (array_key_exists('content', $data)) {
            $payload['faq_content'] = trim(html_entity_decode(strip_tags((string) $data['content'])));
        }
        if (array_key_exists('show_on_home', $data)) {
            $payload['show_on_home'] = $data['show_on_home'] ? 'Yes' : 'No';
        } elseif ($creating) {
            $payload['show_on_home'] = 'No';
        }

        return $payload;
    }

    /** @return array<string, mixed> */
    private function mapFaq(Faq $faq): array
    {
        return [
            'id' => $faq->faq_id,
            'faq_id' => $faq->faq_id,
            'title' => (string) $faq->faq_title,
            'content' => trim(html_entity_decode(strip_tags((string) $faq->faq_content))),
            'show_on_home' => strtolower((string) $faq->show_on_home) === 'yes',
        ];
    }
}
