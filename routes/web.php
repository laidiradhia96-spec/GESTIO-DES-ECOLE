<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassSessionController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentSignalementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Facades\Route;

// ==========================================================
// PAGE D'ACCUEIL
// ==========================================================

Route::get('/', function () {
    return view('welcome');
});

// ==========================================================
// DASHBOARD ADMIN
// ==========================================================

Route::get('/dashboard', function () {

    $studentsCount = Student::count();

    $teachersCount = Teacher::count();

    $subjectsCount = Subject::count();

    $enrollmentsCount = Enrollment::where(
        'status',
        'active'
    )->count();

    // Dernières inscriptions
    $latestEnrollments = Enrollment::with([
        'student',
        'subject',
        'teacher',
    ])
        ->latest()
        ->take(5)
        ->get();

    return view('dashboard', compact(
        'studentsCount',
        'teachersCount',
        'subjectsCount',
        'enrollmentsCount',
        'latestEnrollments'
    ));

})->middleware(['auth', 'verified'])
    ->name('dashboard');

// ==========================================================
// ROUTES AUTHENTIFIÉES
// ==========================================================

Route::middleware('auth')->group(function () {

    // ======================================================
    // PAIEMENTS
    // ======================================================

    Route::get(
        '/payments/unpaid',
        [PaymentController::class, 'unpaid']
    )->name('payments.unpaid');

    Route::resource(
        'payments',
        PaymentController::class
    )->only([
        'index',
        'create',
        'store',
        'show',
    ]);

    Route::get(
        '/payments/{payment}/print',
        [PaymentController::class, 'print']
    )->name('payments.print');

    // ======================================================
    // PROFILE
    // ======================================================

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');

    // ======================================================
    // ENSEIGNANTS
    // ======================================================

    Route::resource(
        'teachers',
        TeacherController::class
    );

    // ======================================================
    // MATIÈRES
    // ======================================================

    Route::resource(
        'subjects',
        SubjectController::class
    );

    // Gestion des enseignants d'une matière
    Route::get(
        '/subjects/{subject}/teachers/manage',
        [SubjectController::class, 'editTeachers']
    )->name('subjects.teachers.edit');

    Route::put(
        '/subjects/{subject}/teachers',
        [SubjectController::class, 'updateTeachers']
    )->name('subjects.teachers.update');

    // ======================================================
    // ÉLÈVES
    // ======================================================

    // Liste des comptes élèves
    // IMPORTANT : avant Route::resource('students')
    Route::get(
        '/students/accounts',
        [StudentController::class, 'accounts']
    )->name('students.accounts');

    // Création du compte élève
    Route::get(
        '/students/{student}/account/create',
        [StudentController::class, 'createAccount']
    )->name('students.account.create');

    Route::post(
        '/students/{student}/account',
        [StudentController::class, 'storeAccount']
    )->name('students.account.store');

    // Ressource élèves
    Route::resource(
        'students',
        StudentController::class
    );

    // ======================================================
    // MATIÈRES SELON LE NIVEAU SCOLAIRE
    // ======================================================
    Route::get(
        '/levels/{level}/subjects',
        [StudentController::class, 'getSubjectsByLevel']
    )->name('subjects.by-level');

    // ======================================================
    // ENSEIGNANTS SELON LA MATIÈRE
    // ======================================================

    Route::get(
        '/subjects/{subject}/teachers',
        [StudentController::class, 'getTeachersBySubject']
    )->name('students.subjects.teachers');

    // ======================================================
    // PAYMENT SIGNALEMENTS
    // ======================================================

    Route::get(
        '/payment-signalements',
        [PaymentSignalementController::class, 'index']
    )->name('payment-signalements.index');

    Route::post(
        '/payment-signalements/generate',
        [PaymentSignalementController::class, 'generate']
    )->name('payment-signalements.generate');

    Route::get(
        '/payment-signalements/{paymentSignalement}',
        [PaymentSignalementController::class, 'show']
    )->name('payment-signalements.show');

    Route::patch(
        '/payment-signalements/sent',
        [PaymentSignalementController::class, 'markAsSent']
    )->name('payment-signalements.sent');

    Route::patch(
        '/payment-signalements/resolved',
        [PaymentSignalementController::class, 'markAsResolved']
    )->name('payment-signalements.resolved');

    // ======================================================
    // PRÉSENCES
    // ======================================================

    Route::get(
        '/attendances',
        [AttendanceController::class, 'index']
    )->name('attendances.index');

    Route::get(
        '/attendances/create',
        [AttendanceController::class, 'create']
    )->name('attendances.create');

    Route::post(
        '/attendances',
        [AttendanceController::class, 'store']
    )->name('attendances.store');

    // Élèves inscrits à une matière + un enseignant
    // IMPORTANT : avant Route::get('/attendances/{attendance}')
    Route::get(
        '/attendances/students',
        [AttendanceController::class, 'students']
    )->name('attendances.students');

    Route::get(
        '/attendances/{attendance}',
        [AttendanceController::class, 'show']
    )->name('attendances.show');

    Route::get(
        '/attendances/{attendance}/edit',
        [AttendanceController::class, 'edit']
    )->name('attendances.edit');

    Route::put(
        '/attendances/{attendance}',
        [AttendanceController::class, 'update']
    )->name('attendances.update');

    Route::delete(
        '/attendances/{attendance}',
        [AttendanceController::class, 'destroy']
    )->name('attendances.destroy');

    Route::get(
        '/attendances/{attendance}/print',
        [AttendanceController::class, 'print']
    )->name('attendances.print');

    // ======================================================
    // SÉANCES / PLANNING
    // ======================================================

    Route::resource(
        'class-sessions',
        ClassSessionController::class
    )->except(['show']);

    // ======================================================
    // ANNONCES
    // ======================================================

    Route::resource(
        'announcements',
        AnnouncementController::class
    );

    // ======================================================
    // NIVEAUX SCOLAIRES
    // ======================================================

    Route::resource(
        'levels',
        LevelController::class
    );

});

// ==========================================================
// ESPACE ÉLÈVE
// ==========================================================

Route::middleware('auth')->group(function () {

    Route::get(
        '/student/dashboard',
        [StudentDashboardController::class, 'index']
    )->name('student.dashboard');

});

// ======================================================
// GESTION DES ADMINISTRATEURS
// ======================================================

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get(
        '/admins',
        [AdminController::class, 'index']
    )->name('admins.index');

    Route::get(
        '/admins/create',
        [AdminController::class, 'create']
    )->name('admins.create');

    Route::post(
        '/admins',
        [AdminController::class, 'store']
    )->name('admins.store');

    Route::delete(
        '/admins/{user}',
        [AdminController::class, 'destroy']
    )->name('admins.destroy');

});

require __DIR__.'/auth.php';
