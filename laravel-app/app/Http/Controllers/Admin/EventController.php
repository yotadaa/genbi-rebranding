<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::latest()->paginate(10);
        return response()->json(['success' => true, 'data' => $events]);
    }

    public function show($id)
    {
        $event = Event::findOrFail($id);
        return response()->json(['success' => true, 'data' => $event]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'image' => 'nullable|image',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event = Event::create($validated);
        return response()->json(['success' => true, 'message' => 'Event created.', 'data' => $event]);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'date' => 'sometimes|date',
            'image' => 'nullable|image',
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        if ($request->hasFile('image')) {
            if ($event->image) Storage::disk('public')->delete($event->image);
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);
        return response()->json(['success' => true, 'message' => 'Event updated.', 'data' => $event]);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        if ($event->image) Storage::disk('public')->delete($event->image);
        $event->delete();
        return response()->json(['success' => true, 'message' => 'Event deleted.']);
    }
}
