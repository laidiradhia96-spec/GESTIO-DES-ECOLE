<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\RevenueService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RevenueController extends Controller
{
    public function __construct(
        private RevenueService $revenueService,
    ) {}

    /**
     * Page principale : Revenus des enseignants.
     *
     * Filtres : mois, année, enseignant, groupe, matière, année scolaire.
     */
    public function index(Request $request)
    {
        // Données pour les filtres
        $teachers = Teacher::where('active', true)->orderBy('last_name')->get();
        $groups = Group::where('is_active', true)->with('subject')->orderBy('name')->get();
        $subjects = Subject::where('active', true)->orderBy('name')->get();
        $schoolYears = SchoolYear::orderByDesc('start_date')->get();

        // Résolution année scolaire
        $schoolYearId = $request->filled('school_year_id')
            ? (int) $request->school_year_id
            : SchoolYear::defaultId();

        // Filtres
        $month = $request->filled('month') ? (int) $request->month : null;
        $year = $request->filled('year') ? (int) $request->year : (int) now()->format('Y');
        $teacherId = $request->filled('teacher_id') ? (int) $request->teacher_id : null;
        $groupId = $request->filled('group_id') ? (int) $request->group_id : null;
        $subjectId = $request->filled('subject_id') ? (int) $request->subject_id : null;

        // Données
        $stats = $this->revenueService->getRevenueStats($month, $year, $schoolYearId);
        $teacherRevenues = $this->revenueService->getTeacherRevenues(
            $month, $year, $teacherId, $groupId, $subjectId, $schoolYearId
        );

        // Pagination
        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginated = new LengthAwarePaginator(
            $teacherRevenues->forPage($page, $perPage)->values(),
            $teacherRevenues->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        // Mois pour le select
        $months = collect(range(1, 12))->map(fn ($m) => [
            'value' => $m,
            'label' => Carbon::create()->month($m)->translatedFormat('F'),
        ]);

        // Années (de 2024 à now+1)
        $years = range(now()->addYear()->year, 2024, -1);

        return view('revenues.index', compact(
            'stats',
            'teacherRevenues',
            'paginated',
            'teachers',
            'groups',
            'subjects',
            'schoolYears',
            'schoolYearId',
            'month',
            'year',
            'teacherId',
            'groupId',
            'subjectId',
            'months',
            'years',
        ));
    }

    /**
     * Fiche individuelle d'un enseignant.
     */
    public function fiche(Teacher $teacher, Request $request)
    {
        $schoolYears = SchoolYear::orderByDesc('start_date')->get();

        $schoolYearId = $request->filled('school_year_id')
            ? (int) $request->school_year_id
            : SchoolYear::defaultId();

        $month = $request->filled('month') ? (int) $request->month : null;
        $year = $request->filled('year') ? (int) $request->year : (int) now()->format('Y');

        // Paiements de l'enseignant
        $payments = $this->revenueService->getTeacherPayments(
            $teacher->id, $month, $year, $schoolYearId
        );

        // Résumé
        $totalCollected = $payments->sum('teacher_share') + $payments->sum('academy_share');
        $totalTeacherShare = $payments->sum('teacher_share');
        $totalAcademyShare = $payments->sum('academy_share');
        $paymentsCount = $payments->count();
        $studentsCount = $payments->pluck('student.id')->unique()->count();

        // Répartition par groupe
        $groupRevenue = $this->revenueService->getTeacherRevenueByGroup(
            $teacher->id, $month, $year, $schoolYearId
        );

        // Matières de l'enseignant
        $teacherSubjects = $teacher->subjects->pluck('name')->implode(', ') ?: '—';

        // Mois pour le select
        $months = collect(range(1, 12))->map(fn ($m) => [
            'value' => $m,
            'label' => Carbon::create()->month($m)->translatedFormat('F'),
        ]);

        $years = range(now()->addYear()->year, 2024, -1);

        $periodLabel = $month
            ? Carbon::create($year, $month, 1)->translatedFormat('F Y')
            : 'Année '.$year;

        return view('revenues.teacher-fiche', compact(
            'teacher',
            'payments',
            'totalCollected',
            'totalTeacherShare',
            'totalAcademyShare',
            'paymentsCount',
            'studentsCount',
            'teacherSubjects',
            'groupRevenue',
            'schoolYears',
            'schoolYearId',
            'month',
            'year',
            'months',
            'years',
            'periodLabel',
        ));
    }

    /**
     * Version imprimable de la fiche enseignant.
     */
    public function print(Teacher $teacher, Request $request)
    {
        $schoolYearId = $request->filled('school_year_id')
            ? (int) $request->school_year_id
            : SchoolYear::defaultId();

        $month = $request->filled('month') ? (int) $request->month : null;
        $year = $request->filled('year') ? (int) $request->year : (int) now()->format('Y');

        $payments = $this->revenueService->getTeacherPayments(
            $teacher->id, $month, $year, $schoolYearId
        );

        $totalCollected = $payments->sum('teacher_share') + $payments->sum('academy_share');
        $totalTeacherShare = $payments->sum('teacher_share');
        $totalAcademyShare = $payments->sum('academy_share');
        $paymentsCount = $payments->count();
        $studentsCount = $payments->pluck('student.id')->unique()->count();
        $teacherSubjects = $teacher->subjects->pluck('name')->implode(', ') ?: '—';
        $periodLabel = $month
            ? Carbon::create($year, $month, 1)->translatedFormat('F Y')
            : 'Année '.$year;
        $schoolYear = SchoolYear::find($schoolYearId);

        // Répartition par groupe
        $groupRevenue = $this->revenueService->getTeacherRevenueByGroup(
            $teacher->id, $month, $year, $schoolYearId
        );

        return view('revenues.teacher-print', compact(
            'teacher',
            'payments',
            'totalCollected',
            'totalTeacherShare',
            'totalAcademyShare',
            'paymentsCount',
            'studentsCount',
            'teacherSubjects',
            'groupRevenue',
            'periodLabel',
            'schoolYear',
        ));
    }
}
