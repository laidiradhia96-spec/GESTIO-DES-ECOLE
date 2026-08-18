<?php

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

test('forPeriod résout une période Y-m vers la bonne année', function () {
    $year2025 = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $year2026 = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-08-31',
    ]);

    expect(SchoolYear::forPeriod('2026-08')->id)->toBe($year2025->id)
        ->and(SchoolYear::forPeriod('2026-09')->id)->toBe($year2026->id);
});

test('forPeriod résout une période Y-m-d vers la bonne année', function () {
    $year2025 = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $year2026 = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-08-31',
    ]);

    expect(SchoolYear::forPeriod('2026-08-20')->id)->toBe($year2025->id)
        ->and(SchoolYear::forPeriod('2026-09-01')->id)->toBe($year2026->id);
});

test('forPeriod résout un nom de mois français avec l\'année de la date source', function () {
    $year2025 = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $year2026 = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-08-31',
    ]);

    expect(SchoolYear::forPeriod('Août', '2026-08-15')->id)->toBe($year2025->id)
        ->and(SchoolYear::forPeriod('Septembre', '2026-08-12')->id)->toBe($year2026->id);
});

test('forPeriod ne devine jamais l\'année d\'un nom de mois sans date source', function () {
    SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    expect(SchoolYear::forPeriod('Octobre'))->toBeNull()
        ->and(SchoolYear::forPeriod('Octobre', null, '2026-08-14'))->toBeNull()
        ->and(SchoolYear::forPeriod('Octobre', 'pas-une-date'))->toBeNull();
});

test('forPeriod utilise la date de secours pour une période vide ou inconnue', function () {
    $year2025 = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    expect(SchoolYear::forPeriod('', '2026-08-14', '2026-08-15')->id)->toBe($year2025->id)
        ->and(SchoolYear::forPeriod('inconnu', '2026-08-14', '2026-08-15')->id)->toBe($year2025->id)
        ->and(SchoolYear::forPeriod('', null, null))->toBeNull()
        ->and(SchoolYear::forPeriod('2026-08', null, '2026-08-15')->id)->toBe($year2025->id);
});

test('forPeriod retourne null quand aucune année ne couvre la période ou la date de secours', function () {
    SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    expect(SchoolYear::forPeriod('2024-08'))->toBeNull()
        ->and(SchoolYear::forPeriod('', null, '2024-01-01'))->toBeNull()
        ->and(SchoolYear::forPeriod('2028-09'))->toBeNull();
});

test('le dashboard étudiant ne montre que l\'année scolaire courante', function () {
    $year2025 = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $year2026 = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-08-31',
    ]);

    SchoolYear::setCurrent($year2026);

    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $teacher = Teacher::factory()->create();

    $currentSubject = Subject::factory()->create();
    $otherSubject = Subject::factory()->create();

    $currentEnrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $currentSubject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'school_year_id' => $year2026->id,
    ]);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $otherSubject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'school_year_id' => $year2025->id,
    ]);

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $currentSubject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-09-15',
        'status' => 'present',
        'school_year_id' => $year2026->id,
    ]);

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $otherSubject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-08-20',
        'status' => 'absent',
        'school_year_id' => $year2025->id,
    ]);

    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $currentSubject->id,
        'period' => '2026-09',
        'payment_date' => '2026-09-10',
        'school_year_id' => $year2026->id,
    ]);

    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $otherSubject->id,
        'period' => '2026-08',
        'payment_date' => '2026-08-10',
        'school_year_id' => $year2025->id,
    ]);

    $response = $this->actingAs($user)->get(route('student.dashboard'));

    $response->assertOk()
        ->assertViewHas('subjectsCount', 1)
        ->assertViewHas('presentCount', 1)
        ->assertViewHas('absentCount', 0);

    expect($response->viewData('enrollments'))->toHaveCount(1)
        ->and($response->viewData('enrollments')->first()->id)->toBe($currentEnrollment->id)
        ->and($response->viewData('attendances'))->toHaveCount(1)
        ->and($response->viewData('payments'))->toHaveCount(1);
});
