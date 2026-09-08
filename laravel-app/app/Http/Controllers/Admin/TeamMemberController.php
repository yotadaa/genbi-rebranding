<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Models\Divisi;
use App\Models\Komsat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamMemberController extends Controller
{
    private function mapMember(TeamMember $m): array
    {
        $photo = $m->photo ?? '';
        return [
            'id'              => $m->id,
            'name'            => $m->name ?? '',
            'role'            => $m->jabatan_wilayah ?? $m->jabatan_komsat ?? $m->designation ?? '',
            'designation'     => $m->designation ?? '',
            'jabatan_wilayah' => $m->jabatan_wilayah ?? '',
            'jabatan_komsat'  => $m->jabatan_komsat ?? '',
            'division'        => $m->divisiRelation?->nama ?? $m->divisi_wilayah ?? $m->divisi_komsat ?? '',
            'divisi_id'       => $m->divisi_id ?? null,
            'divisi_wilayah'  => $m->divisi_wilayah ?? '',
            'divisi_komsat'   => $m->divisi_komsat ?? '',
            'campus'          => $m->komsatRelation?->nama ?? $m->komsat ?? '',
            'komsat_id'       => $m->komsat_id ?? null,
            'komsat'          => $m->komsat ?? '',
            'year'            => $m->tahun ?? '',
            'photo'           => $photo ? url('/uploads/' . ltrim($photo, '/')) : '',
            'email'           => $m->email ?? '',
            'instagram'       => $m->instagram ?? '',
            'detail'          => $m->detail ?? '',
            'show_on_home'    => (bool) ($m->show_on_home ?? false),
            'home_sort_order' => (int) ($m->home_sort_order ?? 0),
        ];
    }

    /**
     * GET /admin/team-members
     * cms.js expects: { data: [...], meta: { total, page }, filters: { divisions, campuses, years } }
     */
    public function index(Request $request)
    {
        $perPage  = max(1, min(100, (int) $request->input('per_page', 12)));
        $page     = max(1, (int) $request->input('page', 1));
        $q        = $request->input('q', '');
        $division = $request->input('division', '');
        $campus   = $request->input('campus', '');
        $year     = $request->input('year', '');

        $query = TeamMember::with(['divisiRelation', 'komsatRelation'])->activeDirectory();

        if ($q !== '') {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('jabatan_wilayah', 'like', "%{$q}%")
                   ->orWhere('jabatan_komsat', 'like', "%{$q}%")
                   ->orWhere('divisi_wilayah', 'like', "%{$q}%")
                   ->orWhere('divisi_komsat', 'like', "%{$q}%");
            });
        }

        if ($division !== '') {
            // Filter by division name (from related Divisi model or direct column)
            $query->where(function ($qb) use ($division) {
                $qb->where('divisi_wilayah', $division)
                   ->orWhere('divisi_komsat', $division)
                   ->orWhereHas('divisiRelation', fn($q2) => $q2->where('nama', $division));
            });
        }

        if ($campus !== '') {
            $query->where(function ($qb) use ($campus) {
                $qb->where('komsat', $campus)
                   ->orWhereHas('komsatRelation', fn($q2) => $q2->where('nama', $campus));
            });
        }

        if ($year !== '') {
            $query->where('tahun', $year);
        }

        $total      = $query->count();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $items      = $query->orderBy('name')
                            ->offset(($page - 1) * $perPage)
                            ->limit($perPage)
                            ->get();

        $data = $items->map(fn($m) => $this->mapMember($m))->values()->all();

        // Build filter options
        $divisions = Divisi::pluck('nama')->filter()->unique()->sort()->values()->all();
        $campuses  = Komsat::pluck('nama')->filter()->unique()->sort()->values()->all();
        $years     = TeamMember::select('tahun')->distinct()->pluck('tahun')
                               ->filter()->unique()->sortDesc()->values()->all();

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => $totalPages,
            ],
            'filters' => [
                'divisions' => $divisions,
                'campuses'  => $campuses,
                'years'     => $years,
            ],
        ]);
    }

    /**
     * GET /admin/team-members/options  (for select dropdowns in other forms)
     */
    public function options(Request $request)
    {
        $limit = min(50, max(1, (int) $request->query('limit', 12)));
        $query = TeamMember::with(['divisiRelation', 'komsatRelation'])->orderBy('name');
        if ($search = trim((string) $request->query('q', ''))) {
            $query->where('name', 'like', '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search) . '%');
        }
        $members = $query->limit($limit)->get()
                             ->map(fn(TeamMember $m) => [
                                 'id' => $m->id, 'name' => $m->name ?? '',
                                 'role' => $m->designation ?? $m->jabatan_wilayah ?? $m->jabatan_komsat ?? '',
                                 'division' => $m->divisiRelation?->nama ?? $m->divisi_wilayah ?? $m->divisi_komsat ?? '',
                                 'campus' => $m->komsatRelation?->nama ?? $m->komsat ?? '',
                                 'year' => $m->tahun ?? '',
                             ])
                             ->values();
        // `data` remains the member autocomplete payload used by Prestasi.  The
        // Team editor also needs its legacy lookup lists, so expose those under
        // explicit sibling keys instead of changing the autocomplete contract.
        $divisions = Divisi::orderBy('nama')->get(['id', 'nama'])
            ->map(fn (Divisi $division) => ['id' => $division->id, 'nama' => $division->nama ?? ''])
            ->filter(fn (array $division) => $division['nama'] !== '')
            ->values();
        $commissions = Komsat::orderBy('nama')->get(['id', 'nama'])
            ->map(fn (Komsat $komsat) => ['id' => $komsat->id, 'nama' => $komsat->nama ?? ''])
            ->filter(fn (array $komsat) => $komsat['nama'] !== '')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $members,
            'divisions' => $divisions,
            'commissions' => $commissions,
        ]);
    }

    /**
     * GET /admin/team-members/{id}
     */
    public function show($id)
    {
        $m = TeamMember::with(['divisiRelation', 'komsatRelation'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $this->mapMember($m)]);
    }

    /**
     * POST /admin/team-members
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'designation'     => 'nullable|string|max:255',
            'jabatan_wilayah' => 'nullable|string|max:255',
            'jabatan_komsat'  => 'nullable|string|max:255',
            'divisi_wilayah'  => 'nullable|string|max:255',
            'divisi_komsat'   => 'nullable|string|max:255',
            'komsat'          => 'nullable|string|max:255',
            'komsat_id'       => 'nullable|integer',
            'divisi_id'       => 'nullable|integer',
            'tahun'           => 'nullable|integer',
            'email'           => 'nullable|email|max:255',
            'instagram'       => 'nullable|string|max:255',
            'detail'          => 'nullable|string',
            'photo'           => 'nullable|string',
            'show_on_home'    => 'nullable|boolean',
            'home_sort_order' => 'nullable|integer',
        ]);

        $member = TeamMember::create($data);
        return response()->json(['success' => true, 'message' => 'Anggota berhasil ditambahkan.', 'data' => ['id' => $member->id]]);
    }

    /**
     * POST /admin/team-members/{id}/update
     */
    public function update(Request $request, $id)
    {
        $member = TeamMember::findOrFail($id);

        $data = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'designation'     => 'nullable|string|max:255',
            'jabatan_wilayah' => 'nullable|string|max:255',
            'jabatan_komsat'  => 'nullable|string|max:255',
            'divisi_wilayah'  => 'nullable|string|max:255',
            'divisi_komsat'   => 'nullable|string|max:255',
            'komsat'          => 'nullable|string|max:255',
            'komsat_id'       => 'nullable|integer',
            'divisi_id'       => 'nullable|integer',
            'tahun'           => 'nullable|integer',
            'email'           => 'nullable|email|max:255',
            'instagram'       => 'nullable|string|max:255',
            'detail'          => 'nullable|string',
            'photo'           => 'nullable|string',
            'show_on_home'    => 'nullable|boolean',
            'home_sort_order' => 'nullable|integer',
        ]);

        $member->update($data);
        return response()->json(['success' => true, 'message' => 'Anggota berhasil diperbarui.', 'data' => ['id' => $member->id]]);
    }

    /**
     * POST /admin/team-members/{id}/delete
     */
    public function destroy($id)
    {
        TeamMember::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Anggota berhasil dihapus.']);
    }

    /**
     * POST /admin/team-members/bulk
     */
    public function bulk(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $action = $request->input('action', 'delete');

        if ($action === 'delete') {
            TeamMember::whereIn('id', $request->input('ids'))->delete();
            return response()->json(['success' => true, 'message' => count($request->input('ids')) . ' anggota dihapus.']);
        }
        if ($action === 'set_home') {
            TeamMember::whereIn('id', $request->input('ids'))->update(['show_on_home' => 1]);
            return response()->json(['success' => true, 'message' => 'Anggota ditampilkan di beranda.']);
        }
        if ($action === 'alumni') {
            $affected = $this->setAlumniStatus($request->input('ids'));
            return response()->json([
                'success' => true,
                'message' => $affected . ' anggota dijadikan alumni.',
                'data' => ['affected' => $affected, 'action' => 'alumni'],
            ]);
        }
        return response()->json(['success' => false, 'message' => 'Aksi tidak dikenal.'], 422);
    }

    /**
     * POST /admin/team-members/{id}/home
     */
    public function setHome(Request $request, $id)
    {
        $member = TeamMember::findOrFail($id);
        $member->update([
            'show_on_home'    => $request->boolean('show_on_home', true),
            'home_sort_order' => (int) $request->input('sort_order', $member->home_sort_order ?? 0),
        ]);
        return response()->json(['success' => true, 'message' => 'Status beranda diperbarui.']);
    }

    /**
     * POST /admin/team-members/{id}/alumni
     */
    public function alumni($id)
    {
        $member = TeamMember::findOrFail($id);
        $affected = $this->setAlumniStatus([$member->id]);

        if ($affected < 1 && !str_contains(strtolower((string) $member->komsat), 'alumni')) {
            return response()->json(['success' => false, 'message' => 'Gagal menjadikan anggota alumni.'], 422);
        }

        return response()->json(['success' => true, 'message' => 'Anggota dijadikan alumni.', 'data' => ['id' => $member->id, 'alumni' => true]]);
    }

    /**
     * POST /admin/team-members/upload  (photo upload)
     */
    public function upload(Request $request)
    {
        $request->validate(['image' => 'required_without:photo|file|image|max:5120', 'photo' => 'required_without:image|file|image|max:5120']);
        $file     = $request->file('image') ?? $request->file('photo');
        $filename = uniqid('team_', true) . '.' . $file->getClientOriginalExtension();
        $dest     = public_path('uploads/team');
        if (!is_dir($dest)) mkdir($dest, 0755, true);
        $file->move($dest, $filename);
        return response()->json([
            'success' => true,
            'url'     => url('/uploads/team/' . $filename),
            'data'    => ['url' => url('/uploads/team/' . $filename)],
            'file'    => ['url' => url('/uploads/team/' . $filename)],
        ]);
    }

    /** Move members to the dedicated Alumni commission using the legacy schema. */
    private function setAlumniStatus(array $ids): int
    {
        $ids = collect($ids)->map(fn ($id) => (int) $id)->filter(fn (int $id) => $id > 0)->unique()->values()->all();
        if ($ids === []) return 0;

        try {
            $alumniId = DB::table('komsats')
                ->whereRaw('LOWER(nama) = ? OR LOWER(nama) LIKE ?', ['alumni', '%alumni%'])
                ->orderByRaw("CASE WHEN LOWER(nama) = 'alumni' THEN 0 ELSE 1 END")
                ->orderBy('id')
                ->value('id');
            if (!$alumniId) {
                $alumniId = DB::table('komsats')->insertGetId(['nama' => 'Alumni']);
            }

            return TeamMember::whereIn('id', $ids)->update([
                'komsat_id' => $alumniId,
                'komsat' => 'Alumni',
                'show_on_home' => false,
            ]);
        } catch (\Throwable) {
            return 0;
        }
    }
}
