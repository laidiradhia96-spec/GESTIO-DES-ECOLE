<?php

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\PaymentSignalement;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\UnpaidSignalement;

test('backfill rattache chaque table à la bonne année scolaire', function () {
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

    $year2027 = SchoolYear::create([
        'name' => '2027-2028',
        'start_date' => '2027-09-01',
        'end_date' => '2028-08-31',
    ]);

    // attendances : date de séance.
    $a1 = Attendance::factory()->create(['date' => '2026-08-20']);
    $a2 = Attendance::factory()->create(['date' => '2026-09-01']);

    // enrollments : start_date, sinon created_at.
    $e1 = Enrollment::factory()->create(['start_date' => '2026-08-12']);
    $e2 = Enrollment::factory()->create(['start_date' => '2026-09-10']);
    $e3 = Enrollment::factory()->create(['start_date' => null]);

    // payments : mois de période (datée, nommée legacy ou vide → payment_date).
    $p1 = Payment::factory()->create(['period' => 'Août', 'payment_date' => '2026-08-15']);
    $p2 = Payment::factory()->create(['period' => 'Septembre', 'payment_date' => '2026-08-12']);
    $p3 = Payment::factory()->create(['period' => '2026-08', 'payment_date' => '2026-08-15']);
    $p4 = Payment::factory()->create(['period' => '2026-08-20', 'payment_date' => '2026-08-20']);
    $p5 = Payment::factory()->create(['period' => '', 'payment_date' => '2026-08-15']);
    $p6 = Payment::factory()->create(['period' => 'Janvier', 'payment_date' => '2026-08-15']);

    // class_sessions : start_date.
    $c1 = ClassSession::create([
        'student_id' => Student::factory()->create()->id,
        'subject_id' => Subject::factory()->create()->id,
        'teacher_id' => Teacher::factory()->create()->id,
        'day' => 'Lundi',
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
        'start_date' => '2026-08-10',
    ]);

    $c2 = ClassSession::create([
        'student_id' => Student::factory()->create()->id,
        'subject_id' => Subject::factory()->create()->id,
        'teacher_id' => Teacher::factory()->create()->id,
        'day' => 'Mardi',
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
        'start_date' => '2026-09-01',
    ]);

    // payment_signalements : période datée, nommée (année = signalement_date),
    // sinon signalement_date.
    $s1 = PaymentSignalement::create([
        'student_id' => Student::factory()->create()->id,
        'period' => '2026-08',
        'amount_remaining' => 0,
        'signalement_date' => '2026-08-15',
    ]);

    $s2 = PaymentSignalement::create([
        'student_id' => Student::factory()->create()->id,
        'period' => 'Octobre',
        'amount_remaining' => 0,
        'signalement_date' => '2026-08-14',
    ]);

    $s3 = PaymentSignalement::create([
        'student_id' => Student::factory()->create()->id,
        'period' => '',
        'amount_remaining' => 0,
        'signalement_date' => '2026-08-10',
    ]);

    // unpaid_signalements : période datée uniquement, sinon created_at.
    $u1 = UnpaidSignalement::create([
        'student_id' => Student::factory()->create()->id,
        'period' => '2026-08',
    ]);

    $u2 = UnpaidSignalement::create([
        'student_id' => Student::factory()->create()->id,
        'period' => 'Octobre',
    ]);

    // payment_schedules : due_date, sinon période datée, sinon created_at.
    $h1 = PaymentSchedule::create([
        'student_id' => Student::factory()->create()->id,
        'period' => '2026-08',
        'amount_due' => 100,
        'due_date' => '2026-08-25',
    ]);

    $h2 = PaymentSchedule::create([
        'student_id' => Student::factory()->create()->id,
        'period' => '2026-09',
        'amount_due' => 100,
    ]);

    $h3 = PaymentSchedule::create([
        'student_id' => Student::factory()->create()->id,
        'period' => 'Août',
        'amount_due' => 100,
    ]);

    BackfillSchoolYearId::backfill();

    expect($a1->refresh()->school_year_id)->toBe($year2025->id)
        ->and($a2->refresh()->school_year_id)->toBe($year2026->id)
        ->and($e1->refresh()->school_year_id)->toBe($year2025->id)
        ->and($e2->refresh()->school_year_id)->toBe($year2026->id)
        ->and($e3->refresh()->school_year_id)->toBe($year2025->id)
        ->and($p1->refresh()->school_year_id)->toBe($year2025->id)
        ->and($p2->refresh()->school_year_id)->toBe($year2026->id)
        ->and($p3->refresh()->school_year_id)->toBe($year2025->id)
        ->and($p4->refresh()->school_year_id)->toBe($year2025->id)
        ->and($p5->refresh()->school_year_id)->toBe($year2025->id)
        ->and($p6->refresh()->school_year_id)->toBe($year2025->id)
        ->and($c1->refresh()->school_year_id)->toBe($year2025->id)
        ->and($c2->refresh()->school_year_id)->toBe($year2026->id)
        ->and($s1->refresh()->school_year_id)->toBe($year2025->id)
        ->and($s2->refresh()->school_year_id)->toBe($year2026->id)
        ->and($s3->refresh()->school_year_id)->toBe($year2025->id)
        ->and($u1->refresh()->school_year_id)->toBe($year2025->id)
        ->and($u2->refresh()->school_year_id)->toBe($year2025->id)
        ->and($h1->refresh()->school_year_id)->toBe($year2025->id)
        ->and($h2->refresh()->school_year_id)->toBe($year2026->id)
        ->and($h3->refresh()->school_year_id)->toBe($year2025->id)
        ->and(SchoolYear::count())->toBe(3);
});

test('backfill laisse NULL quand aucune année ne couvre la date de référence', function () {
    SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $attendance = Attendance::factory()->create(['date' => '2024-06-15']);

    $payment = Payment::factory()->create([
        'period' => '',
        'payment_date' => '2024-01-01',
    ]);

    $unpaid = UnpaidSignalement::create([
        'student_id' => Student::factory()->create()->id,
        'period' => 'legacy',
    ]);

    $unpaid->forceFill(['created_at' => '2024-06-15 10:00:00'])->save();

    $schedule = PaymentSchedule::create([
        'student_id' => Student::factory()->create()->id,
        'period' => 'legacy',
        'amount_due' => 100,
    ]);

    $schedule->forceFill(['created_at' => '2024-06-15 10:00:00'])->save();

    BackfillSchoolYearId::backfill();

    expect($attendance->refresh()->school_year_id)->toBeNull()
        ->and($payment->refresh()->school_year_id)->toBeNull()
        ->and($unpaid->refresh()->school_year_id)->toBeNull()
        ->and($schedule->refresh()->school_year_id)->toBeNull();
});

test('backfill est idempotent et ne réécrit pas un school_year_id déjà défini', function () {
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

    $attendance = Attendance::factory()->create(['date' => '2026-08-20']);

    BackfillSchoolYearId::backfill();

    expect($attendance->refresh()->school_year_id)->toBe($year2025->id);

    $attendance->update(['school_year_id' => $year2026->id]);

    $newAttendance = Attendance::factory()->create(['date' => '2026-09-15']);

    BackfillSchoolYearId::backfill();

    expect($attendance->refresh()->school_year_id)->toBe($year2026->id)
        ->and($newAttendance->refresh()->school_year_id)->toBe($year2026->id);
});

test('ensureSchoolYears recrée les trois années de démarrage si la table est vide', function () {
    SchoolYear::query()->delete();

    BackfillSchoolYearId::ensureSchoolYears();

    expect(SchoolYear::count())->toBe(3)

        ->and(SchoolYear::where('name', '2025-2026')->value('start_date')->toDateString())->toBe('2025-09-01')
        ->and(SchoolYear::where('name', '2025-2026')->value('end_date')->toDateString())->toBe('2026-08-31')

        ->and(SchoolYear::where('name', '2026-2027')->value('start_date')->toDateString())->toBe('2026-09-01')
        ->and(SchoolYear::where('name', '2026-2027')->value('end_date')->toDateString())->toBe('2027-08-31')

        ->and(SchoolYear::where('name', '2027-2028')->value('start_date')->toDateString())->toBe('2027-09-01')
        ->and(SchoolYear::where('name', '2027-2028')->value('end_date')->toDateString())->toBe('2028-08-31')

        ->and(SchoolYear::where('is_current', true)->count())->toBe(0);
});
