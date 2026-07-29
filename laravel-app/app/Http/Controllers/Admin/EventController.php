<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    private function map(Event $event): array
    {
        return [
            'id' => $event->event_id,
            'event_id' => $event->event_id,
            'title' => $event->event_title ?? '',
            'event_title' => $event->event_title ?? '',
            'content' => $event->event_content ?? '',
            'event_content' => $event->event_content ?? '',
            'excerpt' => $event->event_content_short ?? '',
            'event_content_short' => $event->event_content_short ?? '',
            'start_date' => $event->event_start_date ?? '',
            'event_start_date' => $event->event_start_date ?? '',
            'end_date' => $event->event_end_date ?? '',
            'event_end_date' => $event->event_end_date ?? '',
            'location' => $event->event_location ?? '',
            'event_location' => $event->event_location ?? '',
            'map' => $event->event_map ?? '',
            'event_map' => $event->event_map ?? '',
            'photo' => $event->photo ?? '', 'banner' => $event->banner ?? '',
            'slug' => $event->slug, 'status' => $event->status,
            'meta_title' => $event->meta_title ?? '', 'meta_description' => $event->meta_description ?? '',
        ];
    }
    public function index(Request $request)
    {
        $query = Event::orderBy('event_start_date', 'desc');
        if ($q = $request->query('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('event_title', 'like', "%{$q}%")
                   ->orWhere('event_content', 'like', "%{$q}%")
                   ->orWhere('event_location', 'like', "%{$q}%");
            });
        }
        $events = $query->paginate(25);
        return response()->json([
            'success' => true,
            'ok' => true,
            'data' => collect($events->items())->map(fn (Event $event) => $this->map($event))->values(),
            'meta' => [
                'page' => $events->currentPage(),
                'total' => $events->total(),
                'last_page' => $events->lastPage()
            ]
        ]);
    }

    public function show($id)
    {
        $event = Event::find($id);
        if (!$event) {
            $event = Event::where('event_id', $id)->first();
        }
        if (!$event) {
            return response()->json(['success' => false, 'error' => 'Event not found'], 404);
        }
        return response()->json(['success' => true, 'ok' => true, 'data' => $this->map($event)]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $data = [
            'event_title' => $request->input('title'),
            'event_content' => $request->input('content', ''),
            'event_content_short' => $request->input('excerpt', ''),
            'event_start_date' => $request->input('start_date') ?: date('Y-m-d'),
            'event_end_date' => $request->input('end_date') ?: date('Y-m-d'),
            'event_location' => $request->input('location', ''),
            'event_map' => $request->input('map', ''),
            'photo' => $request->input('photo', ''),
            'banner' => $request->input('banner', ''),
            'meta_title' => $request->input('meta_title', ''),
            'meta_description' => $request->input('meta_description', ''),
            'slug' => Str::slug($request->input('title')) . '-' . time(),
            'status' => $request->input('status', 'Upcoming'),
        ];

        if ($request->hasFile('image') || $request->hasFile('photo')) {
            $file = $request->file('image') ?? $request->file('photo');
            $data['photo'] = Storage::url($file->store('events', 'public'));
        }
        if ($request->hasFile('banner_file')) {
            $data['banner'] = Storage::url($request->file('banner_file')->store('events', 'public'));
        }

        $event = Event::create($data);
        return response()->json(['success' => true, 'ok' => true, 'message' => 'Event created.', 'data' => $this->map($event)]);
    }

    public function update(Request $request, $id)
    {
        $event = Event::find($id) ?? Event::where('event_id', $id)->firstOrFail();

        $data = [
            'event_title' => $request->input('title', $event->event_title),
            'event_content' => $request->input('content', $event->event_content),
            'event_content_short' => $request->input('excerpt', $event->event_content_short),
            'event_start_date' => $request->input('start_date', $event->event_start_date),
            'event_end_date' => $request->input('end_date', $event->event_end_date),
            'event_location' => $request->input('location', $event->event_location),
            'event_map' => $request->input('map', $event->event_map),
            'photo' => $request->input('photo', $event->photo),
            'banner' => $request->input('banner', $event->banner),
            'meta_title' => $request->input('meta_title', $event->meta_title),
            'meta_description' => $request->input('meta_description', $event->meta_description),
            'status' => $request->input('status', $event->status),
        ];

        if ($request->filled('title') && $request->input('title') !== $event->event_title) {
            $data['slug'] = Str::slug($request->input('title')) . '-' . $event->event_id;
        }

        if ($request->hasFile('image') || $request->hasFile('photo')) {
            $file = $request->file('image') ?? $request->file('photo');
            $data['photo'] = Storage::url($file->store('events', 'public'));
        }
        if ($request->hasFile('banner_file')) {
            $data['banner'] = Storage::url($request->file('banner_file')->store('events', 'public'));
        }

        $event->update($data);
        return response()->json(['success' => true, 'ok' => true, 'message' => 'Event updated.', 'data' => $this->map($event->fresh())]);
    }

    public function destroy($id)
    {
        $event = Event::find($id) ?? Event::where('event_id', $id)->firstOrFail();
        $event->delete();
        return response()->json(['success' => true, 'ok' => true, 'message' => 'Event deleted.']);
    }
}
