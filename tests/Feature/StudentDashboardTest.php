<?php

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function studentDashboardWeek(): array
{
    $frenchMonths = [
        1 => 'Janvier',
        2 => 'Février',
        3 => 'Mars',
        4 => 'Avril',
        5 => 'Mai',
        6 => 'Juin',
        7 => 'Juillet',
        8 => 'Août',
        9 => 'Septembre',
        10 => 'Octobre',
        11 => 'Novembre',
        12 => 'Décembre',
    ];

    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id, 'level' => '2AM']);

    return [$user, $student, $frenchMonths[now()->month]];
}

function studentDashboardPaidPayment(Student $student, Subject $subject, string $period): Payment
{
    return Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'period' => $period,
        'amount_due' => 2000,
        'amount_paid' => 2000,
        'remaining_amount' => 0,
        'payment_date' => now()->toDateString(),
    ]);
}

test('toutes les matières de l\'élève restent visibles dans le dashboard', function () {
    [$user, $student] = studentDashboardWeek();

    $teacher = Teacher::factory()->create(['last_name' => 'ProfPrincipal']);
    $year2025 = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $math = Subject::factory()->create(['name' => 'MATIMATIQUE']);
    $physic = Subject::factory()->create(['name' => 'PHYSIC']);
    $sansAnnee = Subject::factory()->create(['name' => 'SANSANNEE']);
    $ancienne = Subject::factory()->create(['name' => 'HISTOIRE']);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $math->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'school_year_id' => $year2025->id,
    ]);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $physic->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
    ]);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $sansAnnee->id,
        'teacher_id' => $teacher->id,
    ]);

    Enrollment::factory()->inactive()->create([
        'student_id' => $student->id,
        'subject_id' => $ancienne->id,
        'teacher_id' => $teacher->id,
    ]);

    // Aucune présence ni aucun paiement cette semaine : les matières restent affichées
    $this->actingAs($user)->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('MATIMATIQUE')
        ->assertSee('PHYSIC')
        ->assertSee('SANSANNEE')
        ->assertSee('HISTOIRE')
        ->assertSee('🟢 Actif')
        ->assertSee('🔴 Inactif')
        ->assertViewHas('subjectsCount', 4);
});

test('une matière Mensuel reste visible même quand la note de paiement change', function () {
    [$user, $student, $currentMonth] = studentDashboardWeek();

    $teacher = Teacher::factory()->create();

    $paidSubject = Subject::factory()->create(['name' => 'MATIMATIQUE']);
    $unpaidSubject = Subject::factory()->create(['name' => 'PHYSIC']);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $paidSubject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'payment_type' => 'monthly',
    ]);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $unpaidSubject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'payment_type' => 'monthly',
    ]);

    studentDashboardPaidPayment($student, $paidSubject, $currentMonth);

    $this->actingAs($user)->get(route('student.dashboard'))
        ->assertOk()
        // Les deux matières restent visibles quel que soit le statut
        ->assertSee('MATIMATIQUE')
        ->assertSee('PHYSIC')
        ->assertSee('Payé pour ce mois')
        ->assertSee('Non payé ce mois');
});

test('le paiement mensuel est calculé sur le mois courant', function () {
    [$user, $student, $currentMonth] = studentDashboardWeek();

    $otherMonth = now()->month === 1 ? 'Février' : 'Janvier';

    $teacher = Teacher::factory()->create();

    $paidSubject = Subject::factory()->create(['name' => 'MATIMATIQUE']);
    $unpaidSubject = Subject::factory()->create(['name' => 'PHYSIC']);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $paidSubject->id,
        'teacher_id' => $teacher->id,
        'payment_type' => 'monthly',
    ]);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $unpaidSubject->id,
        'teacher_id' => $teacher->id,
        'payment_type' => 'monthly',
    ]);

    studentDashboardPaidPayment($student, $paidSubject, $currentMonth);

    $this->actingAs($user)->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('Payé pour ce mois');

    // L'autre matière payée pour un autre mois reste non payée pour le mois courant
    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $unpaidSubject->id,
        'period' => $otherMonth,
        'amount_due' => 2000,
        'amount_paid' => 2000,
        'remaining_amount' => 0,
        'payment_date' => now()->toDateString(),
    ]);

    $this->actingAs($user)->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('Payé pour ce mois')
        ->assertSee('Non payé ce mois');
});

test('la logique VIP du dashboard est conservée', function () {
    [$user, $student] = studentDashboardWeek();

    $teacher = Teacher::factory()->create();

    $paidVip = Subject::factory()->create(['name' => 'ENGLES']);
    $unpaidVip = Subject::factory()->create(['name' => 'FRANCAIS']);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $paidVip->id,
        'teacher_id' => $teacher->id,
        'payment_type' => 'vip',
    ]);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $unpaidVip->id,
        'teacher_id' => $teacher->id,
        'payment_type' => 'vip',
    ]);

    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $paidVip->id,
        'period' => 'VIP-AUJOURDHUI',
        'amount_due' => 500,
        'amount_paid' => 500,
        'remaining_amount' => 0,
        'payment_date' => now()->toDateString(),
    ]);

    $this->actingAs($user)->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('ENGLES')
        ->assertSee('FRANCAIS')
        ->assertSee('⭐ VIP')
        ->assertSee("Payé aujourd'hui")
        ->assertSee("Non payé aujourd'hui");
});

test('les présences affichées dans le dashboard sont celles de la semaine en cours', function () {
    [$user, $student] = studentDashboardWeek();

    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['name' => 'MATIMATIQUE']);

    $thisWeekDate = now()->startOfWeek()->toDateString();
    $lastWeekDate = now()->subWeek()->startOfWeek()->toDateString();

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => $thisWeekDate,
        'status' => 'present',
    ]);

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => $lastWeekDate,
        'status' => 'absent',
    ]);

    $this->actingAs($user)->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee(now()->parse($thisWeekDate)->format('d/m/Y'))
        ->assertDontSee(now()->parse($lastWeekDate)->format('d/m/Y'))
        ->assertViewHas('presentCount', 1)
        ->assertViewHas('absentCount', 0);
});

test('le dernier paiement affiché est celui de la semaine en cours', function () {
    [$user, $student] = studentDashboardWeek();

    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['name' => 'MATIMATIQUE']);

    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'period' => 'CETTESEMAINE',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => now()->startOfWeek()->toDateString(),
    ]);

    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'period' => 'ANCIEN',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => now()->subWeeks(3)->toDateString(),
    ]);

    $this->actingAs($user)->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('CETTESEMAINE')
        ->assertDontSee('ANCIEN');

    $response = $this->actingAs($user)->get(route('student.dashboard'))
        ->assertOk();

    expect($response->viewData('payments')->count())->toBe(1);
});

test('l\'élève ne voit jamais les données d\'un autre élève', function () {
    [$user, $student] = studentDashboardWeek();

    $teacher = Teacher::factory()->create();

    $secretSubject = Subject::factory()->create(['name' => 'MATIÈRESECRÈTE']);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $secretSubject->id,
        'teacher_id' => $teacher->id,
    ]);

    $other = Student::factory()->create(['first_name' => 'Autre', 'last_name' => 'Eleve']);
    $otherTeacher = Teacher::factory()->create(['last_name' => 'ProfCache']);

    Attendance::factory()->create([
        'student_id' => $other->id,
        'subject_id' => $secretSubject->id,
        'teacher_id' => $otherTeacher->id,
        'date' => now()->startOfWeek()->toDateString(),
        'status' => 'present',
    ]);

    Payment::factory()->create([
        'student_id' => $other->id,
        'subject_id' => $secretSubject->id,
        'period' => 'PAIEMENTSECRET',
        'amount_due' => 900,
        'amount_paid' => 900,
        'remaining_amount' => 0,
        'payment_date' => now()->startOfWeek()->toDateString(),
    ]);

    $this->actingAs($user)->get(route('student.dashboard'))
        ->assertOk()
        ->assertDontSee('ProfCache')
        ->assertDontSee('PAIEMENTSECRET')
        ->assertDontSee('Autre Eleve')
        ->assertViewHas('presentCount', 0);
});

test('la consultation du dashboard n\'écrit aucune donnée', function () {
    [$user, $student] = studentDashboardWeek();

    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['name' => 'MATIMATIQUE']);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
    ]);

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => now()->startOfWeek()->toDateString(),
        'status' => 'present',
    ]);

    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'period' => 'MOIS',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => now()->startOfWeek()->toDateString(),
    ]);

    expect(Announcement::count())->toBe(0);

    $before = [
        'enrollments' => Enrollment::count(),
        'attendances' => Attendance::count(),
        'payments' => Payment::count(),
        'announcement_views' => DB::table('announcement_user')->count(),
    ];

    $this->actingAs($user)->get(route('student.dashboard'))
        ->assertOk();

    expect(Enrollment::count())->toBe($before['enrollments'])
        ->and(Attendance::count())->toBe($before['attendances'])
        ->and(Payment::count())->toBe($before['payments'])
        ->and(DB::table('announcement_user')->count())->toBe($before['announcement_views']);
});
