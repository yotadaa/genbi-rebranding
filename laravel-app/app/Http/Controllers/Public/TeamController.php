<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeamMember;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', $request->input('limit', 12));
        $page = $request->input('page', 1);

        $query = TeamMember::with(['komsatRelation', 'divisiRelation']);
        
        $activeQ = $request->input('q');
        if ($activeQ !== null && $activeQ !== '' && $activeQ !== 'Semua' && $activeQ !== 'All') {
            $query->where(function($q) use ($activeQ) {
                $q->where('name', 'like', '%' . $activeQ . '%')
                  ->orWhere('designation', 'like', '%' . $activeQ . '%')
                  ->orWhere('jabatan_komsat', 'like', '%' . $activeQ . '%')
                  ->orWhere('jabatan_wilayah', 'like', '%' . $activeQ . '%')
                  ->orWhere('divisi_komsat', 'like', '%' . $activeQ . '%')
                  ->orWhere('divisi_wilayah', 'like', '%' . $activeQ . '%');
            });
        }

        $activeDivision = $request->input('division');
        if ($activeDivision !== null && $activeDivision !== '' && $activeDivision !== 'Semua' && $activeDivision !== 'All') {
            $query->whereHas('divisiRelation', function($q) use ($activeDivision) {
                $q->where('nama', $activeDivision);
            });
        }

        $activeCampus = $request->input('campus');
        if ($activeCampus !== null && $activeCampus !== '' && $activeCampus !== 'Semua' && $activeCampus !== 'All') {
            $query->whereHas('komsatRelation', function($q) use ($activeCampus) {
                $q->where('nama', $activeCampus);
            });
        }

        $activeYear = $request->input('year');
        if ($activeYear !== null && $activeYear !== '' && $activeYear !== 'Semua' && $activeYear !== 'All') {
            $query->where('tahun', $activeYear);
        }

        $resolveImageUrl = function($path) {
            return \App\Services\ImageResolver::resolve($path, '/uploads/slider-1.png');
        };

        $paginator = $query->paginate($perPage);

        $members = $paginator->map(function($member) use ($resolveImageUrl) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'role' => current(array_filter([$member->designation, $member->jabatan_komsat, $member->jabatan_wilayah, 'Anggota'])),
                'campus' => current(array_filter([$member->komsatRelation?->nama, $member->komsat, ''])),
                'division' => current(array_filter([$member->divisiRelation?->nama, $member->divisi_komsat, $member->divisi_wilayah, ''])),
                'year' => $member->tahun,
                'photo' => $resolveImageUrl($member->photo),
                'email' => $member->email ?? '',
                'instagram' => $member->instagram ?? '',
                'bio' => $member->bio ?? '',
            ];
        })->values()->toArray();

        // Get filter options safely
        $divisions = \App\Models\Divisi::pluck('nama')->filter()->unique()->values()->toArray();
        sort($divisions);
        $campuses = \App\Models\Komsat::pluck('nama')->filter()->unique()->values()->toArray();
        sort($campuses);
        $years = TeamMember::select('tahun')->distinct()->pluck('tahun')->filter()->toArray();
        rsort($years);

        if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
            $bpiMembers = TeamMember::with(['komsatRelation', 'divisiRelation'])->bpiCore()->get()->map(function($member) use ($resolveImageUrl) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'role' => current(array_filter([$member->designation, $member->jabatan_komsat, $member->jabatan_wilayah, 'Anggota'])),
                    'campus' => current(array_filter([$member->komsatRelation?->nama, $member->komsat, ''])),
                    'division' => current(array_filter([$member->divisiRelation?->nama, $member->divisi_komsat, $member->divisi_wilayah, ''])),
                    'year' => $member->tahun,
                    'photo' => $resolveImageUrl($member->photo),
                    'email' => $member->email ?? '',
                    'instagram' => $member->instagram ?? '',
                    'bio' => $member->bio ?? '',
                ];
            })->values()->toArray();

            return response()->json([
                'members' => $members,
                'bpi' => $bpiMembers,
                'filters' => [
                    'divisions' => $divisions,
                    'campuses' => $campuses,
                    'years' => $years,
                ],
                'meta' => [
                    'page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ]
            ]);
        }

        return view('public.team.index', [
            'members' => $members,
            'filterOptions' => [
                'divisions' => $divisions,
                'campuses' => $campuses,
                'years' => $years,
            ],
            'activeDivision' => $activeDivision ?? 'Semua',
            'activeCampus' => $activeCampus ?? 'Semua',
            'activeYear' => $activeYear ?? 'Semua',
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
            'totalPages' => $paginator->lastPage(),
            'scripts' => '<script defer src="/assets/js/dist/pages/team.js"></script>',
        ]);
    }

    public function show(Request $request, $id)
    {
        $resolveImageUrl = function($path) {
            if (empty($path)) return '';
            if (str_starts_with($path, 'http')) return $path;
            if (str_starts_with($path, '/uploads/')) return url($path);
            if (str_starts_with($path, 'uploads/')) return url('/' . $path);
            return url('/uploads/' . ltrim($path, '/'));
        };

        $memberModel = TeamMember::with(['komsatRelation', 'divisiRelation'])->find($id);
        
        if (!$memberModel) {
            if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
                return response()->json(['error' => 'Not found'], 404);
            }
            abort(404);
        }

        $member = [
            'id' => $memberModel->id,
            'name' => $memberModel->name,
            'role' => current(array_filter([$memberModel->designation, $memberModel->jabatan_komsat, $memberModel->jabatan_wilayah, 'Anggota'])),
            'campus' => current(array_filter([$memberModel->komsatRelation?->nama, $memberModel->komsat, ''])),
            'division' => current(array_filter([$memberModel->divisiRelation?->nama, $memberModel->divisi_komsat, $memberModel->divisi_wilayah, ''])),
            'year' => $memberModel->tahun,
            'photo' => $resolveImageUrl($memberModel->photo),
            'email' => $memberModel->email ?? '',
            'instagram' => $memberModel->instagram ?? '',
            'bio' => $memberModel->bio ?? '',
        ];

        if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
            return response()->json(['data' => $member]);
        }

        return view('public.team.show', [
            'member' => $member
        ]);
    }
}
