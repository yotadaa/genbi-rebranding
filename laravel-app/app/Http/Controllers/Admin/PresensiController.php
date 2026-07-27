<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Http\Request;

class PresensiController extends Controller
{
    public function index()
    {
        $presensi = Presensi::with(['event', 'member'])->latest()->paginate(10);
        return response()->json(['success' => true, 'data' => $presensi]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:tbl_event,id',
            'member_id' => 'required|exists:tbl_team,id',
            'status' => 'required|string',
        ]);

        $presensi = Presensi::create($validated);
        return response()->json(['success' => true, 'message' => 'Presensi created.', 'data' => $presensi]);
    }

    public function update(Request $request, $id)
    {
        $presensi = Presensi::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|string',
        ]);
        $presensi->update($validated);
        return response()->json(['success' => true, 'message' => 'Presensi updated.', 'data' => $presensi]);
    }

    public function destroy($id)
    {
        $presensi = Presensi::findOrFail($id);
        $presensi->delete();
        return response()->json(['success' => true, 'message' => 'Presensi deleted.']);
    }
}
