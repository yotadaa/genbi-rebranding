<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Presensi;

class PresensiController extends Controller
{
    public function show($token)
    {
        $event = Event::where('presensi_token', $token)->first();
        
        if (!$event) {
            abort(404, 'Invalid presensi token.');
        }
        
        return view('public.presensi.show', compact('event', 'token'));
    }

    public function members($token)
    {
        return response()->json(['members' => []]);
    }

    public function submit(Request $request, $token)
    {
        $event = Event::where('presensi_token', $token)->first();
        
        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Invalid token.'], 400);
        }
        
        $request->validate([
            'member_id' => 'required|integer',
            'status' => 'required|string',
        ]);
        
        Presensi::create([
            'event_id' => $event->id,
            'member_id' => $request->member_id,
            'status' => $request->status,
        ]);
        
        return response()->json(['success' => true, 'message' => 'Presensi submitted successfully.']);
    }
}
