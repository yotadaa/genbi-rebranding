<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PresensiEvent;
use App\Models\PresensiSubmission;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        $query = PresensiEvent::withCount(['members', 'submissions', 'submissions as pending_count' => fn ($q) => $q->where('status', 'pending'), 'submissions as approved_count' => fn ($q) => $q->where('status', 'approved')]);
        if ($status = $request->query('status')) $query->where('status', $status);
        if ($q = trim((string) $request->query('q', ''))) $query->where(fn ($b) => $b->where('event_name', 'like', "%{$q}%")->orWhere('location', 'like', "%{$q}%"));
        $events = $query->latest('created_at')->paginate(min(100, max(1, (int) $request->query('per_page', 25))));
        return response()->json(['success' => true, 'data' => $events->getCollection()->map(fn (PresensiEvent $event) => $this->mapEvent($event))->values(), 'meta' => ['page' => $events->currentPage(), 'total' => $events->total(), 'per_page' => $events->perPage()]]);
    }

    public function show($id)
    {
        return response()->json(['success' => true, 'data' => $this->mapEvent(PresensiEvent::with(['members', 'submissions.member'])->withCount(['members', 'submissions'])->findOrFail($id), true)]);
    }

    public function submissions($id)
    {
        PresensiEvent::findOrFail($id);
        return response()->json(['success' => true, 'data' => PresensiSubmission::with('member')->where('presensi_event_id', $id)->latest('created_at')->get()->map(fn (PresensiSubmission $s) => $this->mapSubmission($s))->values()]);
    }

    public function store(Request $request)
    {
        [$data, $roles, $memberIds] = $this->validatedPayload($request);
        $token = 'prs_' . rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $event = DB::transaction(function () use ($data, $roles, $memberIds, $token) {
            $event = PresensiEvent::create([...$data, 'slug' => $this->uniqueSlug($data['event_name']), 'public_token' => 'sha256:' . hash('sha256', $token), 'public_token_hash' => hash('sha256', $token), 'roles_json' => $roles, 'created_by' => auth()->id()]);
            $event->members()->sync($memberIds);
            return $event;
        });
        return response()->json(['success' => true, 'data' => ['id' => $event->presensi_event_id, 'public_url' => url('/presensi/' . $token)]], 201);
    }

    public function update(Request $request, $id)
    {
        $event = PresensiEvent::findOrFail($id);
        [$data, $roles, $memberIds] = $this->validatedPayload($request);
        DB::transaction(function () use ($event, $data, $roles, $memberIds) {
            $event->update([...$data, 'roles_json' => $roles, 'updated_by' => auth()->id(), 'updated_at' => now()]);
            $event->members()->sync($memberIds);
        });
        return response()->json(['success' => true, 'data' => ['id' => $event->presensi_event_id]]);
    }

    public function destroy($id)
    {
        $event = PresensiEvent::findOrFail($id);
        $event->delete();
        return response()->json(['success' => true]);
    }

    public function approve($id)
    {
        $submission = PresensiSubmission::findOrFail($id);
        $submission->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now(), 'updated_at' => now()]);
        return response()->json(['success' => true, 'data' => $this->mapSubmission($submission->fresh('member'))]);
    }

    public function cancel($id)
    {
        PresensiSubmission::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function approveMember(Request $request, $eventId, $teamId)
    {
        $event = PresensiEvent::with('members')->findOrFail($eventId);
        abort_unless($event->members->contains('id', (int) $teamId), 422, 'Anggota tidak terdaftar pada event ini.');
        $role = (string) $request->validate(['role' => 'required|string|max:120'])['role'];
        abort_unless(in_array($role, array_column($this->roles($event->roles_json), 'name'), true), 422, 'Role presensi tidak valid.');
        $submission = PresensiSubmission::firstOrCreate(['presensi_event_id' => $event->presensi_event_id, 'team_id' => $teamId], ['role' => $role, 'photo_path' => 'manual-approval', 'status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now(), 'created_at' => now()]);
        return response()->json(['success' => true, 'data' => $this->mapSubmission($submission->fresh('member'))], 201);
    }

    private function validatedPayload(Request $request): array
    {
        $data = $request->validate(['event_name' => 'required|string|max:255', 'location' => 'required|string|max:255', 'status' => 'required|in:draft,open,closed,archived', 'roles' => 'required|array|min:1', 'roles.*.name' => 'required|string|max:120', 'roles.*.score' => 'nullable|integer|min:0|max:100000', 'member_ids' => 'required|array|min:1', 'member_ids.*' => 'integer|exists:teams,id']);
        $roles = $this->roles($data['roles']);
        $memberIds = array_values(array_unique($data['member_ids']));
        unset($data['roles'], $data['member_ids']);
        return [$data, $roles, $memberIds];
    }

    private function roles(array $roles): array
    {
        return array_values(array_unique(array_map(function ($role) {
            if (is_array($role)) {
                return ['name' => trim((string) ($role['name'] ?? '')), 'score' => max(0, (int) ($role['score'] ?? 0))];
            }
            return ['name' => trim((string) $role), 'score' => 0];
        }, $roles), SORT_REGULAR));
    }

    private function uniqueSlug(string $value): string
    {
        $base = Str::slug($value) ?: 'presensi'; $slug = $base; $i = 2;
        while (PresensiEvent::withTrashed()->where('slug', $slug)->exists()) $slug = $base . '-' . $i++;
        return $slug;
    }

    private function mapEvent(PresensiEvent $event, bool $detail = false): array
    {
        $roles = $this->roles($event->roles_json ?: []);
        $data = ['id' => $event->presensi_event_id, 'presensi_event_id' => $event->presensi_event_id, 'event_name' => $event->event_name, 'location' => $event->location, 'status' => $event->status, 'roles' => array_column($roles, 'name'), 'role_options' => $roles, 'member_count' => $event->members_count ?? 0, 'submission_count' => $event->submissions_count ?? 0, 'pending_count' => $event->pending_count ?? 0, 'approved_count' => $event->approved_count ?? 0, 'created_at' => $event->created_at];
        if ($detail) { $data['members'] = $event->members->map(fn (TeamMember $member) => ['id' => $member->id, 'name' => $member->name, 'role' => $member->designation, 'division' => $member->divisi_wilayah ?? $member->divisi_komsat, 'campus' => $member->komsat])->values(); $data['submissions'] = $event->submissions->map(fn (PresensiSubmission $s) => $this->mapSubmission($s))->values(); }
        return $data;
    }

    private function mapSubmission(PresensiSubmission $submission): array
    {
        return ['id' => $submission->submission_id, 'submission_id' => $submission->submission_id, 'team_id' => $submission->team_id, 'member_name' => $submission->member?->name ?? '', 'role' => $submission->role, 'photo_url' => $submission->photo_path === 'manual-approval' ? '' : url('/uploads/' . ltrim((string) $submission->photo_path, '/')), 'status' => $submission->status, 'created_at' => $submission->created_at];
    }
}
