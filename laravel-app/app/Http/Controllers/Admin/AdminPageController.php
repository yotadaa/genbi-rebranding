<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\GenBIPoint;
use App\Models\PresensiEvent;
use App\Models\PresensiSubmission;
use App\Models\Prestasi;
use App\Models\PrestasiToken;
use App\Models\TeamMember;
use Illuminate\Support\Facades\DB;

class AdminPageController extends Controller
{
    public function dashboard(Request $request)
    {
        $stats = [
            'news' => \App\Models\News::count(),
            'prestasi' => \App\Models\Prestasi::count(),
            'comments' => \App\Models\NewsComment::where('status', 'pending')->count(),
        ];

        return view('admin.dashboard', [
            'title' => 'Dashboard | Admin GenBI',
            'cmsPage' => 'dashboard',
            'cmsMode' => 'list',
            'stats' => $stats,
            'scripts' => '<script defer src="/assets/js/dist/admin/dashboard.js?v=20260616g"></script>',
        ]);
    }

    public function newsIndex(Request $request)
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 25);
        $query = \App\Models\News::with('category');

        if ($q = $request->query('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('news_title', 'like', "%{$q}%")
                   ->orWhere('news_content', 'like', "%{$q}%");
            });
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($cats = $request->query('category')) {
            $catIds = is_array($cats) ? $cats : [$cats];
            $query->whereIn('category_id', $catIds);
        }

        $total = $query->count();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $newsModels = $query->latest('news_date')->offset(($page - 1) * $perPage)->limit($perPage)->get();

        $items = $newsModels->map(function ($n) {
            return [
                'id' => $n->news_id ?? $n->id,
                'title' => $n->news_title ?? $n->title,
                'date' => $n->news_date ?? $n->created_at,
                'excerpt' => Str::limit(strip_tags($n->news_content ?? $n->content ?? ''), 100),
                'photo' => $n->resolveImageUrl($n->photo ?? ''),
                'category' => $n->category?->category_name ?? $n->category?->name ?? 'Uncategorized',
                'status' => $n->status ?? 'published',
            ];
        })->toArray();

        $categories = \App\Models\Category::all()->map(function ($c) {
            return [
                'id' => $c->category_id ?? $c->id,
                'category_id' => $c->category_id ?? $c->id,
                'name' => $c->category_name ?? $c->name,
                'category_name' => $c->category_name ?? $c->name,
            ];
        })->toArray();

        $filters = [
            'q' => $request->query('q', ''),
            'status' => $request->query('status', ''),
        ];
        $selectedCategories = is_array($request->query('category')) ? $request->query('category') : ($request->query('category') ? [$request->query('category')] : []);
        $layout = $request->query('layout', 'list');

        return view('admin.news.index', [
            'title' => 'View News | Admin GenBI',
            'cmsPage' => 'news',
            'cmsMode' => 'list',
            'items' => $items,
            'categories' => $categories,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'filters' => $filters,
            'selectedCategories' => $selectedCategories,
            'layout' => $layout,
            'scripts' => '<script defer src="/assets/js/dist/admin/cms.js?v=20260617a"></script>',
        ]);
    }

    public function newsAdd()
    {
        $categories = \App\Models\Category::all()->map(function ($c) {
            return ['id' => $c->category_id ?? $c->id, 'category_id' => $c->category_id ?? $c->id, 'name' => $c->category_name ?? $c->name, 'category_name' => $c->category_name ?? $c->name];
        })->toArray();

        $editorScripts = <<<'HTML'
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script>
<script defer src="/assets/js/dist/admin/cms.js?v=20260617a"></script>
HTML;

        return view('admin.news.form', [
            'title' => 'Add News | Admin GenBI',
            'cmsPage' => 'news-add',
            'cmsMode' => 'editor',
            'isEdit' => false,
            'item' => null,
            'categories' => $categories,
            'scripts' => $editorScripts,
        ]);
    }

    public function newsEdit(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $n = \App\Models\News::findOrFail($id);
        $item = [
            'id' => $n->news_id ?? $n->id,
            'title' => $n->news_title ?? $n->title,
            'excerpt' => $n->news_content_short ?? Str::limit(strip_tags($n->news_content ?? ''), 100),
            'content' => $n->news_content ?? $n->content,
            'date' => $n->news_date ?? $n->created_at,
            'category_id' => $n->category_id,
            'photo' => $n->resolveImageUrl($n->photo ?? ''),
            'contributor_pewarta' => $n->contributor_pewarta ?? '',
            'contributor_editor' => $n->contributor_editor ?? '',
            'meta_title' => $n->meta_title ?? '',
            'meta_keyword' => $n->meta_keyword ?? '',
            'meta_description' => $n->meta_description ?? '',
            'status' => $n->status ?? 'draft',
        ];

        $categories = \App\Models\Category::all()->map(function ($c) {
            return ['id' => $c->category_id ?? $c->id, 'category_id' => $c->category_id ?? $c->id, 'name' => $c->category_name ?? $c->name, 'category_name' => $c->category_name ?? $c->name];
        })->toArray();

        $editorScripts = <<<'HTML'
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script>
<script defer src="/assets/js/dist/admin/cms.js?v=20260617a"></script>
HTML;

        return view('admin.news.form', [
            'title' => ($item['title'] . ' - Edit') . ' | Admin GenBI',
            'cmsPage' => 'news-edit',
            'cmsMode' => 'editor',
            'isEdit' => true,
            'item' => $item,
            'categories' => $categories,
            'scripts' => $editorScripts,
        ]);
    }

    /** Render the legacy Prestasi list layout with Laravel-backed data. */
    public function prestasiIndex(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 25)));
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'category' => trim((string) $request->query('category', '')),
            'status' => trim((string) $request->query('status', '')),
            'year' => trim((string) $request->query('year', '')),
        ];
        $query = Prestasi::query();
        if ($filters['q'] !== '') {
            $query->where(fn ($builder) => $builder->where('title', 'like', "%{$filters['q']}%")
                ->orWhere('member_name', 'like', "%{$filters['q']}%")
                ->orWhere('institution', 'like', "%{$filters['q']}%"));
        }
        foreach (['category', 'status', 'year'] as $field) {
            if ($filters[$field] !== '') $query->where($field, $filters[$field]);
        }
        $total = (clone $query)->count();
        $items = $query->orderByDesc('created_at')->orderByDesc('prestasi_id')
            ->offset(($page - 1) * $perPage)->limit($perPage)->get()
            ->map(fn (Prestasi $prestasi) => $this->mapPrestasi($prestasi))->toArray();

        return view('admin.prestasi.index', [
            'title' => 'View Prestasi | Admin GenBI', 'cmsPage' => 'prestasi', 'cmsMode' => 'list',
            'items' => $items, 'filters' => $filters, 'page' => $page, 'perPage' => $perPage,
            'total' => $total, 'totalPages' => max(1, (int) ceil($total / $perPage)),
            'scripts' => '<script defer src="/assets/js/dist/admin/cms.js?v=20260729a"></script>',
        ]);
    }

    /** Render the legacy Prestasi create/edit form and hydrate it with cms.js. */
    public function prestasiForm(Request $request, bool $isEdit = false)
    {
        $prestasi = $isEdit ? Prestasi::findOrFail((int) $request->query('id')) : null;
        return view('admin.prestasi.form', [
            'title' => ($isEdit ? 'Edit' : 'Add') . ' Prestasi | Admin GenBI',
            'cmsPage' => $isEdit ? 'prestasi-edit' : 'prestasi-add', 'cmsMode' => 'editor',
            'isEdit' => $isEdit, 'item' => $prestasi ? $this->mapPrestasi($prestasi, true) : null,
            'scripts' => '<script defer src="/assets/js/dist/admin/cms.js?v=20260729a"></script>',
        ]);
    }

    /** Keep the token screen in the same old admin table/card layout. */
    public function prestasiTokenIndex()
    {
        $items = PrestasiToken::latest('created_at')->get()->map(function (PrestasiToken $token) {
            $status = $token->revoked_at ? 'revoked' : (($token->expires_at && $token->expires_at->isPast()) ? 'expired' : (($token->max_uses > 0 && $token->used_count >= $token->max_uses) ? 'used' : 'active'));
            return [
                'id' => $token->token_id, 'label' => $token->label ?: 'Token #' . $token->token_id,
                'intended_for' => $token->intended_for ?? '', 'status' => $status,
                'created_at' => $token->created_at, 'expires_at' => $token->expires_at,
                'used_at' => $token->used_at, 'max_uses' => $token->max_uses, 'used_count' => $token->used_count,
            ];
        })->toArray();
        return view('admin.prestasi.token', [
            'title' => 'Prestasi Token | Admin GenBI', 'cmsPage' => 'prestasi-token', 'cmsMode' => 'list',
            'items' => $items, 'scripts' => '<script defer src="/assets/js/dist/admin/cms.js?v=20260729a"></script>',
        ]);
    }

    /** Render the legacy Team Member layout, including its custom select inputs. */
    public function teamIndex(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 24)));
        $filters = ['q' => trim((string) $request->query('q', '')), 'division' => trim((string) $request->query('division', '')), 'campus' => trim((string) $request->query('campus', '')), 'year' => trim((string) $request->query('year', ''))];
        $query = TeamMember::with(['divisiRelation', 'komsatRelation'])->activeDirectory();
        if ($filters['q'] !== '') $query->where('name', 'like', "%{$filters['q']}%");
        if ($filters['division'] !== '') $query->where(fn ($builder) => $builder->where('divisi_wilayah', $filters['division'])->orWhere('divisi_komsat', $filters['division'])->orWhereHas('divisiRelation', fn ($related) => $related->where('nama', $filters['division'])));
        if ($filters['campus'] !== '') $query->where(fn ($builder) => $builder->where('komsat', $filters['campus'])->orWhereHas('komsatRelation', fn ($related) => $related->where('nama', $filters['campus'])));
        if ($filters['year'] !== '') $query->where('tahun', $filters['year']);
        $total = (clone $query)->count();
        $items = $query->orderBy('name')->offset(($page - 1) * $perPage)->limit($perPage)->get()->map(fn (TeamMember $member) => $this->mapTeamMember($member))->toArray();
        // Old data can store division/campus as text even when the lookup table
        // has no corresponding row. Merge both sources so the old Team dropdown
        // never becomes empty after the Laravel port.
        $teamFilterMembers = TeamMember::with(['divisiRelation', 'komsatRelation'])->activeDirectory()->get();
        $divisionOptions = collect(\App\Models\Divisi::pluck('nama'))
            ->merge($teamFilterMembers->map(fn (TeamMember $member) => $member->divisiRelation?->nama ?? $member->divisi_wilayah ?? $member->divisi_komsat ?? ''))
            ->filter()->unique()->sort()->values()->all();
        $campusOptions = collect(\App\Models\Komsat::pluck('nama'))
            ->merge($teamFilterMembers->map(fn (TeamMember $member) => $member->komsatRelation?->nama ?? $member->komsat ?? ''))
            ->filter()->unique()->sort()->values()->all();
        $yearOptions = $teamFilterMembers->pluck('tahun')->filter()->unique()->sortDesc()->values()->all();

        return view('admin.team.index', [
            'title' => 'View Team Members | Admin GenBI', 'cmsPage' => 'team', 'cmsMode' => 'list',
            'items' => $items, 'filters' => $filters, 'page' => $page, 'perPage' => $perPage,
            'total' => $total, 'totalPages' => max(1, (int) ceil($total / $perPage)),
            'filterOptions' => ['divisions' => $divisionOptions, 'campuses' => $campusOptions, 'years' => $yearOptions],
            'layout' => $request->query('layout', 'grid') === 'list' ? 'list' : 'grid',
            'scripts' => '<script defer src="/assets/js/dist/admin/cms.js?v=20260729a"></script>',
        ]);
    }

    /** Render the existing JavaScript Team editor through an explicit Laravel route. */
    public function teamForm(Request $request, bool $isEdit = false)
    {
        if ($isEdit) {
            TeamMember::findOrFail((int) $request->query('id'));
        }

        return view('admin.static-shell', [
            'title' => ($isEdit ? 'Edit' : 'Add') . ' Team Member | Admin GenBI',
            'cmsPage' => 'team',
            'cmsMode' => 'editor',
            'staticContent' => '',
            'scripts' => '<script defer src="/assets/js/dist/admin/cms.js?v=20260730a"></script>',
        ]);
    }

    public function presensiIndex(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 25)));
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => trim((string) $request->query('status', '')),
        ];

        $query = PresensiEvent::withCount(['members', 'submissions', 'submissions as pending_count' => fn ($q) => $q->where('status', 'pending'), 'submissions as approved_count' => fn ($q) => $q->where('status', 'approved')]);
        if ($filters['q'] !== '') {
            $query->where(fn ($builder) => $builder->where('event_name', 'like', "%{$filters['q']}%")->orWhere('location', 'like', "%{$filters['q']}%"));
        }
        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        $total = (clone $query)->count();
        $items = $query->latest('created_at')->offset(($page - 1) * $perPage)->limit($perPage)->get()->map(fn (PresensiEvent $event) => $this->mapPresensiEvent($event))->toArray();

        return view('admin.presensi.index', ['title' => 'Presensi | Admin GenBI', 'cmsPage' => 'presensi', 'cmsMode' => 'list', 'items' => $items, 'filters' => $filters, 'page' => $page, 'perPage' => $perPage, 'total' => $total, 'totalPages' => max(1, (int) ceil($total / $perPage)), 'scripts' => '<script defer src="/assets/js/dist/lib/qr-creator.min.js"></script><script defer src="/assets/js/dist/admin/presensi.js?v=20260730a"></script>']);
    }

    public function presensiForm(Request $request, bool $isEdit = false)
    {
        $event = $isEdit ? PresensiEvent::with('members')->withCount('members')->findOrFail((int) $request->query('id')) : null;
        return view('admin.presensi.form', ['title' => ($isEdit ? 'Edit' : 'Add') . ' Presensi | Admin GenBI', 'cmsPage' => $isEdit ? 'presensi-edit' : 'presensi-add', 'cmsMode' => 'editor', 'isEdit' => $isEdit, 'item' => $event ? $this->mapPresensiEvent($event, true) : null, 'scripts' => '<script defer src="/assets/js/dist/admin/presensi.js?v=20260730a"></script>']);
    }

    public function presensiDetail(Request $request)
    {
        $event = PresensiEvent::with(['members', 'submissions.member'])->withCount('members')->findOrFail((int) $request->query('id'));
        return view('admin.presensi.show', ['title' => 'Detail Presensi | Admin GenBI', 'cmsPage' => 'presensi-detail', 'cmsMode' => 'detail', 'item' => $this->mapPresensiEvent($event, true), 'submissions' => $event->submissions->map(fn (PresensiSubmission $submission) => $this->mapPresensiSubmission($submission))->toArray(), 'scripts' => '<script defer src="/assets/js/dist/lib/qr-creator.min.js"></script><script defer src="/assets/js/dist/admin/presensi.js?v=20260730a"></script>']);
    }

    public function genbiPoinIndex(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 25)));
        $filters = ['q' => trim((string) $request->query('q', ''))];
        $query = TeamMember::query();
        if ($filters['q'] !== '') {
            $query->where('name', 'like', "%{$filters['q']}%");
        }
        $total = (clone $query)->count();
        $items = $query->orderBy('name')->offset(($page - 1) * $perPage)->limit($perPage)->get()->map(fn (TeamMember $member) => $this->mapGenBIMember($member))->toArray();
        $activities = GenBIPoint::with('member')->latest('activity_date')->latest('activity_id')->limit(10)->get()->map(fn (GenBIPoint $activity) => $this->mapGenBIActivity($activity))->toArray();

        return view('admin.genbi-poin.index', ['title' => 'GenBI Poin | Admin GenBI', 'cmsPage' => 'genbi-poin', 'cmsMode' => 'list', 'items' => $items, 'activities' => $activities, 'filters' => $filters, 'page' => $page, 'perPage' => $perPage, 'total' => $total, 'totalPages' => max(1, (int) ceil($total / $perPage)), 'scripts' => '<script defer src="/assets/js/dist/admin/genbi-point.js?v=20260617a"></script>']);
    }

    public function genbiPoinForm(Request $request, bool $isEdit = false)
    {
        $activity = $isEdit ? GenBIPoint::with('member')->findOrFail((int) $request->query('id')) : null;
        $member = !$isEdit && $request->query('team_id') ? TeamMember::find((int) $request->query('team_id')) : null;
        return view('admin.genbi-poin.form', ['title' => ($isEdit ? 'Edit' : 'Add') . ' GenBI Poin | Admin GenBI', 'cmsPage' => $isEdit ? 'genbi-poin-edit' : 'genbi-poin-add', 'cmsMode' => 'editor', 'isEdit' => $isEdit, 'item' => $activity ? $this->mapGenBIActivity($activity) : null, 'prefillMember' => $member ? $this->mapGenBIMember($member) : null, 'scripts' => '<script defer src="/assets/js/dist/admin/genbi-point.js?v=20260617a"></script>']);
    }

    public function genbiPoinDetail(Request $request)
    {
        $member = TeamMember::find((int) $request->query('id'));
        $mappedMember = $member ? $this->mapGenBIMember($member) : null;
        $manualActivities = $member ? GenBIPoint::with('member')->where('team_id', $member->id)->latest('activity_date')->latest('activity_id')->get()->map(fn (GenBIPoint $activity) => $this->mapGenBIActivity($activity))->toArray() : [];
        $presensiActivities = $member ? $this->genBIPresensiActivities((int) $member->id) : [];

        return view('admin.genbi-poin.show', ['title' => 'Detail GenBI Poin | Admin GenBI', 'cmsPage' => 'genbi-poin', 'cmsMode' => 'detail', 'member' => $mappedMember, 'teamId' => (int) ($member->id ?? 0), 'manualActivities' => $manualActivities, 'presensiActivities' => $presensiActivities, 'scripts' => '<script defer src="/assets/js/dist/admin/genbi-point.js?v=20260617a"></script>']);
    }

    private function mapPresensiEvent(PresensiEvent $event, bool $detail = false): array
    {
        $roles = is_array($event->roles_json) ? $event->roles_json : (json_decode((string) $event->roles_json, true) ?: []);
        $roleOptions = array_values(array_filter(array_map(function ($role) {
            if (is_array($role)) {
                return ['name' => trim((string) ($role['name'] ?? '')), 'score' => max(0, (int) ($role['score'] ?? 0))];
            }
            return ['name' => trim((string) $role), 'score' => 0];
        }, $roles), fn ($role) => $role['name'] !== ''));
        $data = ['id' => $event->presensi_event_id, 'event_name' => $event->event_name, 'location' => $event->location, 'status' => $event->status, 'roles' => array_column($roleOptions, 'name'), 'role_options' => $roleOptions, 'public_url' => '', 'member_count' => $event->members_count ?? $event->members()->count(), 'submission_count' => $event->submissions_count ?? $event->submissions()->count(), 'pending_count' => $event->pending_count ?? 0, 'approved_count' => $event->approved_count ?? 0, 'created_at' => $event->created_at];
        if ($detail) {
            $data['members'] = $event->members->map(fn (TeamMember $member) => ['id' => $member->id, 'name' => $member->name, 'role' => $member->designation ?? $member->jabatan_wilayah ?? $member->jabatan_komsat ?? '', 'division' => $member->divisi_wilayah ?? $member->divisi_komsat ?? '', 'campus' => $member->komsat ?? ''])->values()->toArray();
        }
        return $data;
    }

    private function mapPresensiSubmission(PresensiSubmission $submission): array
    {
        return ['id' => $submission->submission_id, 'team_id' => $submission->team_id, 'member_name' => $submission->member?->name ?? '', 'role' => $submission->role, 'photo_url' => $submission->photo_path === 'manual-approval' ? '' : url('/uploads/' . ltrim((string) $submission->photo_path, '/')), 'status' => $submission->status, 'created_at' => $submission->created_at];
    }

    private function mapGenBIActivity(GenBIPoint $activity): array
    {
        return ['id' => $activity->activity_id, 'team_id' => $activity->team_id, 'member_name' => $activity->member?->name ?? '', 'activity_name' => $activity->activity_name, 'points' => (int) $activity->points, 'activity_date' => $activity->activity_date, 'created_at' => $activity->created_at];
    }

    private function mapPrestasi(Prestasi $prestasi, bool $detail = false): array
    {
        $photo = trim((string) ($prestasi->photo ?? ''));
        $data = [
            'id' => $prestasi->prestasi_id, 'title' => $prestasi->title ?? '',
            'name' => $prestasi->member_name ?? '', 'member_name' => $prestasi->member_name ?? '',
            'category' => $prestasi->category ?? '', 'year' => $prestasi->year ?? '',
            'institution' => $prestasi->institution ?? '', 'description' => $prestasi->description ?? '',
            'status' => $prestasi->status ?? 'draft',
            'image' => $prestasi->resolveImageUrl($photo),
            'photo' => $prestasi->resolveImageUrl($photo),
            'meta_title' => $prestasi->meta_title ?? '', 'meta_keyword' => $prestasi->meta_keyword ?? '',
            'meta_description' => $prestasi->meta_description ?? '',
        ];
        if ($detail) {
            $data['content'] = $prestasi->detail ?? '';
            $data['detail'] = $prestasi->detail ?? '';
            $data['images'] = $photo ? [$data['image']] : [];
        }
        return $data;
    }

    private function mapTeamMember(TeamMember $member): array
    {
        $photo = trim((string) ($member->photo ?? ''));
        return [
            'id' => $member->id, 'name' => $member->name ?? '',
            'role' => $member->designation ?? $member->jabatan_wilayah ?? $member->jabatan_komsat ?? '',
            'division' => $member->divisiRelation?->nama ?? $member->divisi_wilayah ?? $member->divisi_komsat ?? '',
            'campus' => $member->komsatRelation?->nama ?? $member->komsat ?? '', 'year' => $member->tahun ?? '',
            'photo' => $photo ? url('/uploads/' . ltrim($photo, '/')) : '',
            'show_on_home' => (bool) ($member->show_on_home ?? false),
        ];
    }

    private function mapGenBIMember(TeamMember $member): array
    {
        $manual = (int) GenBIPoint::where('team_id', $member->id)->sum('points');
        $presensi = $this->genBIPresensiPoints((int) $member->id);
        return ['id' => $member->id, 'name' => $member->name, 'role' => $member->designation ?? $member->jabatan_wilayah ?? $member->jabatan_komsat ?? '', 'division' => $member->divisi_wilayah ?? $member->divisi_komsat ?? '', 'campus' => $member->komsat ?? '', 'presensi_points' => $presensi, 'manual_points' => $manual, 'total_points' => $presensi + $manual];
    }

    private function genBIPresensiPoints(int $teamId): int
    {
        return array_sum(array_column($this->genBIPresensiActivities($teamId), 'points'));
    }

    private function genBIPresensiActivities(int $teamId): array
    {
        $rows = DB::table('tbl_presensi_submission as submission')->join('tbl_presensi_event as event', 'event.presensi_event_id', '=', 'submission.presensi_event_id')->where('submission.team_id', $teamId)->where('submission.status', 'approved')->whereNull('event.deleted_at')->latest('submission.created_at')->get(['submission.role', 'submission.status', 'submission.created_at', 'event.event_name', 'event.location', 'event.roles_json']);
        return $rows->map(function ($row) {
            $roles = json_decode((string) $row->roles_json, true) ?: [];
            $points = 0;
            foreach ($roles as $role) {
                if (($role['name'] ?? '') === $row->role) {
                    $points = max(0, (int) ($role['score'] ?? 0));
                    break;
                }
            }
            return ['event_name' => $row->event_name, 'location' => $row->location, 'role' => $row->role, 'points' => $points, 'status' => $row->status, 'created_at' => $row->created_at];
        })->toArray();
    }

    public function show(Request $request, $page, $sub = null)
    {
        $pageName = $sub ? "{$page}-{$sub}" : $page;
        $viewName = "admin.{$pageName}";

        // Try fallback html file extraction first if view doesn't exist or to maintain SPA compatibility
        $htmlPath = base_path('../fallbacks/admin/' . $pageName . '.html');
        if (!file_exists($htmlPath)) {
            $htmlPath = base_path('fallbacks/admin/' . $pageName . '.html');
        }

        if (file_exists($htmlPath)) {
            $html = file_get_contents($htmlPath);
            preg_match('/<title>(.*?)<\/title>/si', $html, $titleMatch);
            preg_match('/<body[^>]*data-cms-page="([^"]*)"[^>]*data-cms-mode="([^"]*)"[^>]*>/si', $html, $bodyMatch);
            preg_match('/<main[^>]*>(.*?)<\/main>/si', $html, $contentMatch);
            preg_match_all('/<script\b[^>]*>.*?<\/script>/si', $html, $scriptMatches);

            $scripts = array_filter($scriptMatches[0] ?? [], function ($script) {
                return !str_contains($script, '/assets/js/data.js')
                    && !str_contains($script, '/assets/js/api-core.js')
                    && !str_contains($script, '/assets/js/api.js')
                    && !str_contains($script, '/assets/js/app.js')
                    && !str_contains($script, '/assets/js/lib/ui.js')
                    && !str_contains($script, '/assets/js/admin/admin.js');
            });

            $scripts = array_map(function ($script) {
                $script = str_replace(
                    ['../assets/js/admin/cms.js', '/assets/js/admin/cms.js'],
                    '/assets/js/dist/admin/cms.js?v=20260617a',
                    $script
                );
                $script = str_replace(
                    ['../assets/js/admin/settings.js', '/assets/js/admin/settings.js'],
                    '/assets/js/dist/admin/settings.js?v=20260617a',
                    $script
                );
                $script = str_replace(
                    ['../assets/js/admin/page.js', '/assets/js/admin/page.js'],
                    '/assets/js/dist/admin/page.js?v=20260617a',
                    $script
                );
                $script = str_replace(
                    ['../assets/js/admin/genbi-point.js', '/assets/js/admin/genbi-point.js'],
                    '/assets/js/dist/admin/genbi-point.js?v=20260617a',
                    $script
                );
                $script = str_replace(
                    ['../assets/js/admin/presensi.js', '/assets/js/admin/presensi.js'],
                    '/assets/js/dist/admin/presensi.js?v=20260617a',
                    $script
                );
                if (str_contains($script, ' src=') && !preg_match('/\sdefer\b/i', $script)) {
                    return preg_replace('/<script\b/i', '<script defer', $script, 1) ?? $script;
                }
                return $script;
            }, $scripts);

            return view('admin.static-shell', [
                'title' => trim(strip_tags($titleMatch[1] ?? 'Admin GenBI')),
                'cmsPage' => $bodyMatch[1] ?? $pageName,
                'cmsMode' => $bodyMatch[2] ?? 'list',
                'staticContent' => trim($contentMatch[1] ?? ''),
                'scripts' => implode('', $scripts),
            ]);
        }

        if (view()->exists($viewName)) {
            return view($viewName);
        }

        return abort(404);
    }
}
