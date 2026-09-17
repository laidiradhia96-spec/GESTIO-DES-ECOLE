<?php

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\UnpaidDebtService;

test('resolveTypeForPair utilise le bon groupe quand un eleve a deux enrollments dans la meme matiere', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);

    $year = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
        'is_default' => true,
    ]);

    $groupNormal = Group::factory()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'level' => '1AP',
        'mode' => 'normal',
        'name' => 'Multi Normal',
        'school_year_id' => $year->id,
    ]);
    GroupTariff::create([
        'group_id' => $groupNormal->id,
        'student_price' => 500,
        'teacher_share' => 400,
        'academy_share' => 100,
        'billing_type' => 'monthly',
        'is_active' => true,
        'effective_from' => '2025-09-01',
    ]);

    $groupVip = Group::factory()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'level' => '1AP',
        'mode' => 'vip',
        'name' => 'Multi VIP',
        'school_year_id' => $year->id,
    ]);
    GroupTariff::create([
        'group_id' => $groupVip->id,
        'student_price' => 100,
        'teacher_share' => 80,
        'academy_share' => 20,
        'billing_type' => 'per_session',
        'is_active' => true,
        'effective_from' => '2025-09-01',
    ]);

    Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $groupNormal->id,
        'status' => 'active',
        'start_date' => '2025-09-01',
    ]);
    Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $groupVip->id,
        'status' => 'active',
        'start_date' => '2025-09-01',
    ]);

    $service = app(UnpaidDebtService::class);

    // Présence dans le groupe VIP → type vip_per_session
    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $groupVip->id,
        'date' => '2026-01-10',
        'status' => 'present',
        'school_year_id' => $year->id,
    ]);

    $debts = $service->activeDebts();
    $debt = $debts->first(function ($d) use ($student) {
        return $d->student->id === $student->id;
    });

    expect($debt)->not->toBeNull()
        ->and($debt->type)->toBe('vip');
});

test('getObligationAmount retourne le bon tarif quand deux enrollments existent pour la meme matiere', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);

    $year = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
        'is_default' => true,
    ]);

    $group1 = Group::factory()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'level' => '1AP',
        'mode' => 'normal',
        'name' => 'Obligation G1',
        'school_year_id' => $year->id,
    ]);
    GroupTariff::create([
        'group_id' => $group1->id,
        'student_price' => 500,
        'teacher_share' => 400,
        'academy_share' => 100,
        'billing_type' => 'monthly',
        'is_active' => true,
        'effective_from' => '2025-09-01',
    ]);

    $group2 = Group::factory()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'level' => '1AP',
        'mode' => 'normal',
        'name' => 'Obligation G2',
        'school_year_id' => $year->id,
    ]);
    GroupTariff::create([
        'group_id' => $group2->id,
        'student_price' => 800,
        'teacher_share' => 640,
        'academy_share' => 160,
        'billing_type' => 'monthly',
        'is_active' => true,
        'effective_from' => '2025-09-01',
    ]);

    Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group1->id,
        'status' => 'active',
        'start_date' => '2025-09-01',
    ]);
    Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group2->id,
        'status' => 'active',
        'start_date' => '2025-09-01',
    ]);

    $service = app(UnpaidDebtService::class);

    // Présence dans le groupe1 → obligation 500
    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group1->id,
        'date' => '2026-01-10',
        'status' => 'present',
        'school_year_id' => $year->id,
    ]);

    $debts = $service->activeDebts();
    $debt = $debts->first(function ($d) use ($student) {
        return $d->student->id === $student->id;
    });

    expect($debt)->not->toBeNull()
        ->and($debt->amount_due)->toBe(500.0);
});
