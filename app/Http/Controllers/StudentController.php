<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /**
     * Liste des élèves
     */
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('parent_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        $students = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('students.index', compact('students'));
    }


    /**
     * Liste des comptes élèves
     */
    public function accounts()
    {
        $students = Student::with('user')
            ->latest()
            ->paginate(10);

        return view('students.accounts', compact('students'));
    }


    /**
     * Formulaire d'ajout
     */
    public function create()
    {
        $subjects = Subject::where('active', true)
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        return view('students.create', compact('subjects'));
    }


    /**
     * Récupérer les matières selon le niveau
     */
    public function getSubjectsByLevel($level)
    {
        $subjects = Subject::where('level', $level)
            ->where('active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'level',
            ]);

        return response()->json($subjects);
    }


    /**
     * Récupérer les enseignants selon la matière
     */
    public function getTeachersBySubject(Subject $subject)
    {
        $teachers = $subject->teachers()
            ->where('teachers.active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get([
                'teachers.id',
                'teachers.first_name',
                'teachers.last_name',
                'teachers.speciality',
            ]);

        return response()->json($teachers);
    }


   /**
 * Enregistrer un nouvel élève
 */
public function store(Request $request)
{
    $validated = $request->validate([
        // =========================
        // INFORMATIONS ÉLÈVE
        // =========================
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'date_of_birth' => 'nullable|date',
        'phone' => 'nullable|string|max:30',
        'address' => 'nullable|string',
        'level' => 'required|string|max:100',
        'parent_name' => 'nullable|string|max:150',
        'parent_phone' => 'nullable|string|max:30',

        // =========================
        // INSCRIPTIONS
        // =========================
        'enrollments' => 'required|array|min:1',

        'enrollments.*.subject_id' => [
            'required',
            'exists:subjects,id',
        ],

        'enrollments.*.teacher_id' => [
            'required',
            'exists:teachers,id',
        ],

        'enrollments.*.payment_type' => [
            'required',
            'in:monthly,vip',
        ],
    ], [
        'enrollments.required' =>
            'Veuillez ajouter au moins une matière.',

        'enrollments.*.subject_id.required' =>
            'Veuillez sélectionner une matière.',

        'enrollments.*.teacher_id.required' =>
            'Veuillez sélectionner un enseignant.',

        'enrollments.*.payment_type.required' =>
            'Veuillez sélectionner un type d’abonnement.',
    ]);


    // =====================================================
    // VÉRIFIER CHAQUE INSCRIPTION
    // =====================================================

    foreach ($validated['enrollments'] as $index => $enrollment) {

        // =========================
        // Vérifier la matière
        // =========================

        $subject = Subject::where('id', $enrollment['subject_id'])
            ->where('level', $validated['level'])
            ->where('active', true)
            ->first();

        if (!$subject) {

            return back()
                ->withErrors([
                    "enrollments.$index.subject_id" =>
                        "La matière sélectionnée pour l'inscription " .
                        ($index + 1) .
                        " ne correspond pas au niveau scolaire choisi."
                ])
                ->withInput();
        }


        // =========================
        // Vérifier l'enseignant
        // =========================

        $teacherExists = $subject->teachers()
            ->where('teachers.id', $enrollment['teacher_id'])
            ->where('teachers.active', true)
            ->exists();

        if (!$teacherExists) {

            return back()
                ->withErrors([
                    "enrollments.$index.teacher_id" =>
                        "L'enseignant sélectionné pour l'inscription " .
                        ($index + 1) .
                        " ne correspond pas à cette matière."
                ])
                ->withInput();
        }
    }


    // =====================================================
    // CRÉER L'ÉLÈVE
    // =====================================================

    $student = Student::create([
        'first_name' => $validated['first_name'],
        'last_name' => $validated['last_name'],
        'date_of_birth' => $validated['date_of_birth'] ?? null,
        'phone' => $validated['phone'] ?? null,
        'address' => $validated['address'] ?? null,
        'level' => $validated['level'],
        'parent_name' => $validated['parent_name'] ?? null,
        'parent_phone' => $validated['parent_phone'] ?? null,
    ]);


    // =====================================================
    // CRÉER LES INSCRIPTIONS
    // =====================================================

    foreach ($validated['enrollments'] as $enrollment) {

        Enrollment::create([
            'student_id' => $student->id,
            'subject_id' => $enrollment['subject_id'],
            'teacher_id' => $enrollment['teacher_id'],
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'payment_type' => $enrollment['payment_type'],
        ]);
    }


    // =====================================================
    // REDIRECTION
    // =====================================================

    return redirect()
        ->route('students.index')
        ->with(
            'success',
            'Élève et inscriptions ajoutés avec succès.'
        );
}

    /**
     * Afficher un élève
     */
    public function show(Student $student)
    {
        $student->load([
            'enrollments.subject',
            'enrollments.teacher',
        ]);

        return view(
            'students.show',
            compact('student')
        );
    }


    /**
     * Formulaire création compte
     */
    public function createAccount(Student $student)
    {
        if ($student->user) {

            return redirect()
                ->route('students.index')
                ->with(
                    'error',
                    'Cet élève possède déjà un compte.'
                );
        }

        return view(
            'students.create-account',
            compact('student')
        );
    }


    /**
     * Créer compte utilisateur
     */
    public function storeAccount(
        Request $request,
        Student $student
    ) {

        if ($student->user) {

            return redirect()
                ->route('students.index')
                ->with(
                    'error',
                    'Cet élève possède déjà un compte.'
                );
        }


        $validated = $request->validate([

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);


        $user = User::create([

            'name' =>
                $student->first_name
                . ' '
                . $student->last_name,

            'email' =>
                $validated['email'],

            'password' =>
                Hash::make($validated['password']),

            'role' =>
                'student',
        ]);


        $student->update([

            'user_id' =>
                $user->id,
        ]);


        return redirect()
            ->route('students.accounts')
            ->with(
                'success',
                'Le compte élève a été créé avec succès.'
            );
    }


    /**
     * Formulaire modification
     */
    public function edit(Student $student)
    {
        return view(
            'students.edit',
            compact('student')
        );
    }


    /**
     * Mettre à jour un élève
     */
    public function update(
        Request $request,
        Student $student
    ) {

        $validated = $request->validate([

            'first_name' =>
                'required|string|max:100',

            'last_name' =>
                'required|string|max:100',

            'date_of_birth' =>
                'nullable|date',

            'phone' =>
                'nullable|string|max:30',

            'address' =>
                'nullable|string',

            'level' =>
                'required|string|max:100',

            'parent_name' =>
                'nullable|string|max:150',

            'parent_phone' =>
                'nullable|string|max:30',
        ]);


        $student->update($validated);


        return redirect()
            ->route('students.index')
            ->with(
                'success',
                'Élève modifié avec succès.'
            );
    }


    /**
     * Supprimer un élève
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()
            ->route('students.index')
            ->with(
                'success',
                'Élève supprimé avec succès.'
            );
    }
}