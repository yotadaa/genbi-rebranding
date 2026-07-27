<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamMemberController extends Controller
{
    public function index()
    {
        $members = TeamMember::latest()->paginate(10);
        return response()->json(['success' => true, 'data' => $members]);
    }

    public function show($id)
    {
        $member = TeamMember::findOrFail($id);
        return response()->json(['success' => true, 'data' => $member]);
    }

    public function options()
    {
        $members = TeamMember::select('id', 'name')->get();
        return response()->json(['success' => true, 'data' => $members]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'generation' => 'nullable|integer',
            'is_home' => 'boolean',
            'is_alumni' => 'boolean',
            'image' => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('team', 'public');
        }

        $member = TeamMember::create($validated);
        return response()->json(['success' => true, 'message' => 'Team member created.', 'data' => $member]);
    }

    public function update(Request $request, $id)
    {
        $member = TeamMember::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'position' => 'sometimes|string|max:255',
            'generation' => 'nullable|integer',
            'is_home' => 'boolean',
            'is_alumni' => 'boolean',
            'image' => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            if ($member->image) Storage::disk('public')->delete($member->image);
            $validated['image'] = $request->file('image')->store('team', 'public');
        }

        $member->update($validated);
        return response()->json(['success' => true, 'message' => 'Team member updated.', 'data' => $member]);
    }

    public function destroy($id)
    {
        $member = TeamMember::findOrFail($id);
        if ($member->image) Storage::disk('public')->delete($member->image);
        $member->delete();
        return response()->json(['success' => true, 'message' => 'Team member deleted.']);
    }

    public function bulk(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        TeamMember::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true, 'message' => 'Members deleted.']);
    }

    public function setHome(Request $request, $id)
    {
        $member = TeamMember::findOrFail($id);
        $member->update(['is_home' => $request->boolean('is_home')]);
        return response()->json(['success' => true, 'message' => 'Home status updated.']);
    }

    public function alumni(Request $request, $id)
    {
        $member = TeamMember::findOrFail($id);
        $member->update(['is_alumni' => $request->boolean('is_alumni')]);
        return response()->json(['success' => true, 'message' => 'Alumni status updated.']);
    }
}
