<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GenBIPoint;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class GenBIPointController extends Controller
{
    public function members()
    {
        $members = TeamMember::withSum('points', 'amount')->get();
        return response()->json(['success' => true, 'data' => $members]);
    }

    public function activities()
    {
        $activities = GenBIPoint::with('member')->latest()->paginate(10);
        return response()->json(['success' => true, 'data' => $activities]);
    }

    public function showActivity($id)
    {
        $activity = GenBIPoint::findOrFail($id);
        return response()->json(['success' => true, 'data' => $activity]);
    }

    public function storeActivity(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:tbl_team,id',
            'description' => 'required|string',
            'amount' => 'required|integer',
            'date' => 'required|date',
        ]);

        $activity = GenBIPoint::create($validated);
        return response()->json(['success' => true, 'message' => 'Point activity added.', 'data' => $activity]);
    }

    public function updateActivity(Request $request, $id)
    {
        $activity = GenBIPoint::findOrFail($id);
        $validated = $request->validate([
            'description' => 'required|string',
            'amount' => 'required|integer',
            'date' => 'required|date',
        ]);
        $activity->update($validated);
        return response()->json(['success' => true, 'message' => 'Point activity updated.', 'data' => $activity]);
    }
    
    public function destroyActivity($id)
    {
        $activity = GenBIPoint::findOrFail($id);
        $activity->delete();
        return response()->json(['success' => true, 'message' => 'Point activity deleted.']);
    }
}
