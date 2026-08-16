<?php

use App\Models\PaymentSignalement;
use App\Models\Subject;
use App\Models\User;

test('envoyer une dette calculée crée un signalement envoyé sans doublon', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'monthly');
    debtAttendance($student, $subject, '2026-08-08', 'present');

    expect(PaymentSignalement::count())->toBe(0);

    $this->actingAs($user)
        ->patch(route('payment-signalements.sent'), [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'period' => '2026-08',
        ])
        ->assertRedirect(route('payment-signalements.index'));

    $signalements = PaymentSignalement::where('student_id', $student->id)
        ->where('subject_id', $subject->id)
        ->get();

    expect($signalements)->toHaveCount(1)
        ->and($signalements->first()->status)->toBe('sent');

    $this->actingAs($user)->get(route('payment-signalements.index'))
        ->assertOk()
        ->assertSee('Envoyé');
});

test('résoudre une dette calculée la matérialise et la retire de la liste active', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'monthly');
    debtAttendance($student, $subject, '2026-08-08', 'present');

    expect(PaymentSignalement::count())->toBe(0);

    $this->actingAs($user)
        ->patch(route('payment-signalements.resolved'), [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'period' => '2026-08',
        ])
        ->assertRedirect(route('payment-signalements.index'));

    $signalements = PaymentSignalement::where('student_id', $student->id)
        ->where('subject_id', $subject->id)
        ->get();

    expect($signalements)->toHaveCount(1)
        ->and($signalements->first()->status)->toBe('resolved');

    $this->actingAs($user)->get(route('payment-signalements.index'))
        ->assertOk()
        ->assertSee('Aucun impayé');
});

test('résoudre un signalement stocké existant ne crée pas de doublon', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'monthly');
    debtAttendance($student, $subject, '2026-08-08', 'present');
    debtStoredSignalement($student, $subject);

    expect(PaymentSignalement::count())->toBe(1);

    $this->actingAs($user)
        ->patch(route('payment-signalements.resolved'), [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'period' => '2026-08',
        ])
        ->assertRedirect(route('payment-signalements.index'));

    expect(PaymentSignalement::count())->toBe(1)
        ->and(PaymentSignalement::first()->status)->toBe('resolved');
});

test('voir un signalement stocké fonctionne', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    $signalement = debtStoredSignalement($student, $subject);

    $this->actingAs($user)->get(route('payment-signalements.show', $signalement))
        ->assertOk()
        ->assertSee('Détail de l\'impayé', false);
});
