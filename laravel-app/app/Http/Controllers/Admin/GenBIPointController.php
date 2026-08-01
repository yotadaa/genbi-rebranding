<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GenBIPoint;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenBIPointController extends Controller
{
    public function members(Request $request)
    {
        $query = TeamMember::query()->orderByDesc('tahun')->orderBy('name');
        if ($q = trim((string) $request->query('q', ''))) $query->where('name', 'like', "%{$q}%");
        $members = $query->paginate(min(100, max(1, (int) $request->query('per_page', 25))));
        return response()->json(['success' => true, 'data' => $members->getCollection()->map(fn (TeamMember $member) => $this->mapMember($member))->values(), 'meta' => ['page' => $members->currentPage(), 'total' => $members->total(), 'per_page' => $members->perPage()]]);
    }

    public function activities(Request $request)
    {
        $query = GenBIPoint::with('member')->latest('activity_date')->latest('activity_id');
        if ($teamId = (int) $request->query('team_id', 0)) $query->where('team_id', $teamId);
        return response()->json(['success' => true, 'data' => $query->paginate(min(100, max(1, (int) $request->query('per_page', 25))))->through(fn (GenBIPoint $activity) => $this->mapActivity($activity))]);
    }

    public function showActivity($id)
    {
        return response()->json(['success' => true, 'data' => $this->mapActivity(GenBIPoint::with('member')->findOrFail($id))]);
    }

    public function storeActivity(Request $request)
    {
        $data = $this->validated($request);
        $activity = GenBIPoint::create([...$data, 'created_by' => auth()->id(), 'created_at' => now()]);
        return response()->json(['success' => true, 'data' => $this->mapActivity($activity->fresh('member'))], 201);
    }

    public function updateActivity(Request $request, $id)
    {
        $activity = GenBIPoint::findOrFail($id);
        $activity->update([...$this->validated($request), 'updated_by' => auth()->id(), 'updated_at' => now()]);
        return response()->json(['success' => true, 'data' => $this->mapActivity($activity->fresh('member'))]);
    }

    public function destroyActivity($id)
    {
        GenBIPoint::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    private function validated(Request $request): array
    {
        return $request->validate(['team_id' => 'required|integer|exists:teams,id', 'activity_name' => 'required|string|max:255', 'points' => 'required|integer|min:0|max:100000', 'activity_date' => 'nullable|date']);
    }

    private function mapActivity(GenBIPoint $activity): array
    {
        return ['id' => $activity->activity_id, 'activity_id' => $activity->activity_id, 'team_id' => $activity->team_id, 'member_name' => $activity->member?->name ?? '', 'activity_name' => $activity->activity_name, 'points' => (int) $activity->points, 'activity_date' => $activity->activity_date, 'created_at' => $activity->created_at];
    }

    private function mapMember(TeamMember $member): array
    {
        $manual = (int) GenBIPoint::where('team_id', $member->id)->sum('points');
        $presensi = $this->presensiPoints((int) $member->id);
        return ['id' => $member->id, 'name' => $member->name, 'role' => $member->designation ?? $member->jabatan_wilayah ?? $member->jabatan_komsat ?? '', 'division' => $member->divisi_wilayah ?? $member->divisi_komsat ?? '', 'campus' => $member->komsat ?? '', 'presensi_points' => $presensi, 'manual_points' => $manual, 'total_points' => $presensi + $manual];
    }

    private function presensiPoints(int $teamId): int
    {
        $rows = DB::table('tbl_presensi_submission as submission')->join('tbl_presensi_event as event', 'event.presensi_event_id', '=', 'submission.presensi_event_id')->where('submission.team_id', $teamId)->where('submission.status', 'approved')->whereNull('event.deleted_at')->get(['submission.role', 'event.roles_json']);
        return $rows->sum(function ($row) { $roles = json_decode((string) $row->roles_json, true) ?: []; foreach ($roles as $role) if (($role['name'] ?? '') === $row->role) return max(0, (int) ($role['score'] ?? 0)); return 0; });
    }
}
