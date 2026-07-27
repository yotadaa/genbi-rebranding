<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeamMember;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 12;
        $page = $request->input('page', 1);

        $query = TeamMember::with(['komsatRelation', 'divisiRelation']);
        
        $activeDivision = $request->input('division', '');
        if ($activeDivision !== '') {
            $query->whereHas('divisiRelation', function($q) use ($activeDivision) {
                $q->where('nama', $activeDivision);
            });
        }

        $activeCampus = $request->input('campus', '');
        if ($activeCampus !== '') {
            $query->whereHas('komsatRelation', function($q) use ($activeCampus) {
                $q->where('nama', $activeCampus);
            });
        }

        $resolveImageUrl = function($path) {
            if (empty($path)) return '';
            if (str_starts_with($path, 'http')) return $path;
            if (str_starts_with($path, '/uploads/')) return url($path);
            if (str_starts_with($path, 'uploads/')) return url('/' . $path);
            return url('/uploads/' . ltrim($path, '/'));
        };

        $activeYear = $request->input('year', '');
        if ($activeYear !== '') {
            $query->where('tahun', $activeYear);
        }

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
            ];
        })->toArray();

        // Get filter options safely
        $divisions = \App\Models\Divisi::pluck('nama')->filter()->unique()->values()->toArray();
        sort($divisions);
        $campuses = \App\Models\Komsat::pluck('nama')->filter()->unique()->values()->toArray();
        sort($campuses);
        $years = TeamMember::select('tahun')->distinct()->pluck('tahun')->filter()->toArray();
        rsort($years);

        return view('public.team.index', [
            'members' => $members,
            'filterOptions' => [
                'divisions' => $divisions,
                'campuses' => $campuses,
                'years' => $years,
            ],
            'activeDivision' => $activeDivision,
            'activeCampus' => $activeCampus,
            'activeYear' => $activeYear,
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
            'totalPages' => $paginator->lastPage(),
            'scripts' => '<script defer src="/assets/js/dist/pages/team.js"></script>',
        ]);
    }

    public function show($id)
    {
        $resolveImageUrl = function($path) {
            if (empty($path)) return '';
            if (str_starts_with($path, 'http')) return $path;
            if (str_starts_with($path, '/uploads/')) return url($path);
            if (str_starts_with($path, 'uploads/')) return url('/' . $path);
            return url('/uploads/' . ltrim($path, '/'));
        };

        $memberModel = TeamMember::find($id);
        
        if (!$memberModel) {
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
            'bio' => $memberModel->bio ?? '',
        ];

        return view('public.team.show', [
            'member' => $member
        ]);
    }
}
