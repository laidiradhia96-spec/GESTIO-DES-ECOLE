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
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

/**
 * Créer un enregistrement périodique, rattaché éventuellement
 * à une année scolaire (factory ou create direct).
 */
function periodicRecord(string $model, ?int $schoolYearId = null)
{
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();

    return match ($model) {
        Attendance::class => Attendance::factory()->create(['school_year_id' => $schoolYearId]),
        Enrollment::class => Enrollment::factory()->create(['school_year_id' => $schoolYearId]),
        Payment::class => Payment::factory()->create(['school_year_id' => $schoolYearId]),
        ClassSession::class => ClassSession::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day' => 'Lundi',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'school_year_id' => $schoolYearId,
        ]),
        PaymentSignalement::class => PaymentSignalement::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'period' => now()->format('Y-m'),
            'amount_remaining' => 0,
            'signalement_date' => now()->toDateString(),
            'school_year_id' => $schoolYearId,
        ]),
        UnpaidSignalement::class => UnpaidSignalement::create([
            'student_id' => $student->id,
            'period' => now()->format('Y-m'),
            'school_year_id' => $schoolYearId,
        ]),
        PaymentSchedule::class => PaymentSchedule::create([
            'student_id' => $student->id,
            'period' => now()->format('Y-m'),
            'amount_due' => 100,
            'school_year_id' => $schoolYearId,
        ]),
    };
}

test('la migration ajoute school_year_id aux sept tables périodiques', function () {
    $tables = [
        'attendances',
        'enrollments',
        'payments',
        'class_sessions',
        'payment_signalements',
        'unpaid_signalements',
        'payment_schedules',
    ];

    foreach ($tables as $table) {
        expect(Schema::hasColumn($table, 'school_year_id'))->toBeTrue();
    }
});

test('school_year_id accepte NULL sur les sept tables', function () {
    $models = [
        Attendance::class,
        Enrollment::class,
        Payment::class,
        ClassSession::class,
        PaymentSignalement::class,
        UnpaidSignalement::class,
        PaymentSchedule::class,
    ];

    foreach ($models as $model) {
        $record = periodicRecord($model);

        expect($record->school_year_id)->toBeNull();
    }
});

test('la FK school_year_id est reliée à la table school_years', function () {
    $year = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $attendance = Attendance::factory()->create([
        'school_year_id' => $year->id,
    ]);

    expect($attendance->schoolYear()->first()->id)->toBe($year->id);
});

test('une valeur school_year_id inexistante est rejetée par la FK', function () {
    expect(fn () => Attendance::factory()->create([
        'school_year_id' => 9999,
    ]))->toThrow(QueryException::class);
});

test('les relations Eloquent fonctionnent dans les deux sens', function () {
    $year = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $relations = [
        Attendance::class => 'attendances',
        Enrollment::class => 'enrollments',
        Payment::class => 'payments',
        ClassSession::class => 'classSessions',
        PaymentSignalement::class => 'paymentSignalements',
        UnpaidSignalement::class => 'unpaidSignalements',
        PaymentSchedule::class => 'paymentSchedules',
    ];

    foreach ($relations as $model => $relation) {
        $record = periodicRecord($model, $year->id);

        expect($record->schoolYear()->first()->id)->toBe($year->id)
            ->and($year->{$relation}->contains('id', $record->id))->toBeTrue();
    }
});
