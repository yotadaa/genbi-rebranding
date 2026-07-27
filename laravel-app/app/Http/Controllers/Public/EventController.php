<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', $request->input('limit', 9));
        $page = $request->input('page', 1);
        $activeQ = $request->input('q');

        $resolveImageUrl = function($path) {
            return \App\Services\ImageResolver::resolve($path, '/uploads/slider-1.png');
        };

        $query = Event::published();
        
        if ($activeQ !== null && $activeQ !== '' && $activeQ !== 'Semua' && $activeQ !== 'All') {
            $query->where(function($q) use ($activeQ) {
                $q->where('event_title', 'like', '%' . $activeQ . '%')
                  ->orWhere('event_content', 'like', '%' . $activeQ . '%')
                  ->orWhere('event_location', 'like', '%' . $activeQ . '%');
            });
        }

        $paginator = $query->latestEvent()->paginate($perPage);

        $items = $paginator->map(function($event) use ($resolveImageUrl) {
            return [
                'id' => $event->event_id,
                'slug' => $event->slug,
                'title' => $event->event_title,
                'excerpt' => mb_strimwidth(strip_tags($event->event_content), 0, 150, '...'),
                'start_date' => $event->event_start_date,
                'end_date' => $event->event_end_date,
                'location' => $event->event_location,
                'status' => $event->status,
                'image' => $resolveImageUrl($event->photo ?: $event->banner),
            ];
        })->values()->toArray();

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

        return view('public.event.index', [
            'items' => $items,
            'filters' => ['q' => $activeQ ?? ''],
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
            'totalPages' => $paginator->lastPage(),
            'scripts' => '<script defer src="/assets/js/dist/pages/event.js"></script>',
        ]);
    }

    public function show(Request $request, $slug)
    {
        $resolveImageUrl = function($path) {
            return \App\Services\ImageResolver::resolve($path, '/uploads/slider-1.png');
        };

        $eventModel = Event::published()->where('slug', $slug)->first();
        
        if (!$eventModel) {
            if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
                return response()->json(['error' => 'Not found'], 404);
            }
            abort(404);
        }

        $event = [
            'id' => $eventModel->event_id,
            'slug' => $eventModel->slug,
            'title' => $eventModel->event_title,
            'content' => $eventModel->event_content,
            'start_date' => $eventModel->event_start_date,
            'end_date' => $eventModel->event_end_date,
            'location' => $eventModel->event_location,
            'map' => $eventModel->event_map,
            'status' => $eventModel->status,
            'image' => $resolveImageUrl($eventModel->photo ?: $eventModel->banner),
            'banner' => $resolveImageUrl($eventModel->banner ?: $eventModel->photo),
        ];

        if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
            return response()->json(['data' => $event]);
        }

        return view('public.event.show', [
            'event' => $event,
            'item' => $event, // Fallback just in case
            'scripts' => '<script defer src="/assets/js/dist/pages/event.js"></script>',
        ]);
    }
}
