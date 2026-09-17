<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Group;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class AttendanceArchiveController extends Controller
{
    /**
     * Archive des présences — recherche par élève + filtres.
     */
    public function index(Request $request)
    {
        $schoolYears = SchoolYear::orderByDesc('start_date')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::orderBy('last_name')->get();

        $student = null;
        $students = collect();
        $attendances = null;
        $groups = collect();

        $filters = [
            'search' => $request->filled('search') ? $request->search : null,
            'student_id' => $request->filled('student_id') ? (int) $request->student_id : null,
            'school_year_id' => $request->filled('school_year_id') ? (int) $request->school_year_id : null,
            'group_id' => $request->filled('group_id') ? (int) $request->group_id : null,
            'subject_id' => $request->filled('subject_id') ? (int) $request->subject_id : null,
            'teacher_id' => $request->filled('teacher_id') ? (int) $request->teacher_id : null,
            'status' => $request->filled('status') ? $request->status : null,
            'date_from' => $request->filled('date_from') ? $request->date_from : null,
            'date_to' => $request->filled('date_to') ? $request->date_to : null,
        ];

        // =========================
        // ÉTAPE 1 : Recherche d'élèves
        // =========================

        if ($filters['student_id']) {
            $student = Student::find($filters['student_id']);
        } elseif ($filters['search']) {
            $students = Student::search($filters['search'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->limit(20)
                ->get();
        }

        // =========================
        // ÉTAPE 2 : Historique si élève sélectionné
        // =========================

        if ($student) {
            // Groupes de l'élève pour le filtre (via enrollments)
            $groups = Group::whereHas('enrollments', function ($q) use ($student) {
                $q->where('student_id', $student->id)
                    ->where('status', 'active');
            })->orderBy('name')->get();

            $query = Attendance::with(['student', 'group', 'subject', 'teacher', 'schoolYear'])
                ->where('attendances.student_id', $student->id);

            // Filtre année scolaire
            if ($filters['school_year_id']) {
                $query->where('attendances.school_year_id', $filters['school_year_id']);
            }

            // Filtre groupe
            if ($filters['group_id']) {
                $query->where('attendances.group_id', $filters['group_id']);
            }

            // Filtre matière
            if ($filters['subject_id']) {
                $query->where('attendances.subject_id', $filters['subject_id']);
            }

            // Filtre enseignant
            if ($filters['teacher_id']) {
                $query->where('attendances.teacher_id', $filters['teacher_id']);
            }

            // Filtre statut
            if ($filters['status']) {
                $query->where('attendances.status', $filters['status']);
            }

            // Filtre date début
            if ($filters['date_from']) {
                $query->whereDate('attendances.date', '>=', $filters['date_from']);
            }

            // Filtre date fin
            if ($filters['date_to']) {
                $query->whereDate('attendances.date', '<=', $filters['date_to']);
            }

            $attendances = $query->orderByDesc('attendances.date')
                ->orderByDesc('attendances.id')
                ->paginate(25)
                ->withQueryString();
        }

        return view('attendances.archive', compact(
            'student',
            'students',
            'attendances',
            'groups',
            'schoolYears',
            'subjects',
            'teachers',
            'filters'
        ));
    }
}
