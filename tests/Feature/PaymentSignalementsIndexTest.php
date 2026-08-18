<?php

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentSignalement;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

function debtSchoolYears(): array
{
    $year2025 = SchoolYear::firstOrCreate(
        ['name' => '2025-2026'],
        ['start_date' => '2025-09-01', 'end_date' => '2026-08-31', 'is_current' => true]
    );

    $year2026 = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-08-31']
    );

    return [$year2025, $year2026];
}

function debtStudent(string $firstName, string $lastName): Student
{
    return Student::factory()->create([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'parent_name' => 'Parent '.$firstName,
    ]);
}

function debtEnrollment(Student $student, Subject $subject, string $paymentType): Enrollment
{
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);

    return Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'start_date' => '2026-08-01',
        'status' => 'active',
        'payment_type' => $paymentType,
    ]);
}

function debtAttendance(Student $student, Subject $subject, string $date, string $status = 'present'): Attendance
{
    debtSchoolYears();

    return Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => Teacher::factory()->create()->id,
        'date' => $date,
        'status' => $status,
        'school_year_id' => SchoolYear::forDate($date)?->id,
    ]);
}

function debtPayment(Student $student, Subject $subject, array $attributes = []): Payment
{
    debtSchoolYears();

    $period = $attributes['period'] ?? '2026-08';
    $paymentDate = $attributes['payment_date'] ?? '2026-08-20';

    return Payment::factory()->create(array_merge([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'payment_type' => 'monthly',
        'period' => $period,
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => $paymentDate,
        'school_year_id' => SchoolYear::forPeriod($period, $paymentDate, $paymentDate)?->id,
    ], $attributes));
}

function debtStoredSignalement(Student $student, Subject $subject, array $attributes = []): PaymentSignalement
{
    debtSchoolYears();

    $period = $attributes['period'] ?? '2026-08';
    $signalementDate = $attributes['signalement_date'] ?? '2026-08-15';

    return PaymentSignalement::create(array_merge([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'period' => $period,
        'amount_remaining' => 500,
        'status' => 'pending',
        'signalement_date' => $signalementDate,
        'school_year_id' => SchoolYear::forPeriod($period, $signalementDate, $signalementDate)?->id,
        'note' => null,
    ], $attributes));
}

test('mensuel : plusieurs présences dans le mois = UNE seule ligne d\'impayé, même sans signalement stocké', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create(['name' => 'MATIMATIQUE']);
    debtEnrollment($student, $subject, 'monthly');

    debtAttendance($student, $subject, '2026-08-08', 'present');
    debtAttendance($student, $subject, '2026-08-13', 'present');
    debtAttendance($student, $subject, '2026-08-15', 'present');

    $response = $this->actingAs($user)->get(route('payment-signalements.index'));

    $response->assertOk();

    $content = $response->getContent();

    expect(substr_count($content, 'Mensuel'))->toBe(1)
        ->and($content)->toContain('Laidi')
        ->and($content)->toContain('MATIMATIQUE')
        ->and($content)->toContain('Août 2026');

    // L'affichage ne crée aucun enregistrement
    expect(PaymentSignalement::count())->toBe(0);
});

test('vip : chaque jour de présence = une ligne indépendante', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create(['name' => 'MATIMATIQUE']);
    debtEnrollment($student, $subject, 'vip');

    debtAttendance($student, $subject, '2026-08-08', 'present');
    debtAttendance($student, $subject, '2026-08-13', 'present');
    debtAttendance($student, $subject, '2026-08-15', 'present');

    $response = $this->actingAs($user)->get(route('payment-signalements.index'));

    $response->assertOk();

    $content = $response->getContent();

    expect(substr_count($content, 'VIP'))->toBe(3)
        ->and($content)->toContain('08 Août 2026')
        ->and($content)->toContain('13 Août 2026')
        ->and($content)->toContain('15 Août 2026');
});

test('un statut absent ne génère jamais d\'obligation d\'impayé', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'monthly');
    debtAttendance($student, $subject, '2026-08-05', 'absent');

    $this->actingAs($user)->get(route('payment-signalements.index'))
        ->assertOk()
        ->assertSee('Aucun impayé');
});

test('mensuel : un paiement couvrant le mois masque la dette', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'monthly');
    debtAttendance($student, $subject, '2026-08-08', 'present');
    debtPayment($student, $subject, [
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-20',
    ]);

    $this->actingAs($user)->get(route('payment-signalements.index'))
        ->assertOk()
        ->assertSee('Aucun impayé');
});

test('mensuel : un paiement partiel affiche le montant restant', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'monthly');
    debtAttendance($student, $subject, '2026-08-08', 'present');
    debtPayment($student, $subject, [
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1000,
        'remaining_amount' => 500,
        'payment_date' => '2026-08-14',
    ]);

    $this->actingAs($user)->get(route('payment-signalements.index'))
        ->assertOk()
        ->assertSee('500,00');
});

test('un paiement mensuel de 2026 ne couvre jamais un mois de 2027', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'monthly');
    debtAttendance($student, $subject, '2026-08-08', 'present');
    debtAttendance($student, $subject, '2027-08-10', 'present');
    debtPayment($student, $subject, [
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-20',
    ]);

    $response = $this->actingAs($user)->get(route('payment-signalements.index', ['school_year_id' => debtSchoolYears()[1]->id]));

    $response->assertOk()
        ->assertSee('Août 2027');
});

test('une période legacy française ne couvre que la même année que payment_date', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'monthly');
    debtAttendance($student, $subject, '2026-08-08', 'present');
    debtAttendance($student, $subject, '2027-08-10', 'present');
    debtPayment($student, $subject, [
        'period' => 'Août',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-20',
    ]);

    // 2026 couvert (masqué)
    $this->actingAs($user)->get(route('payment-signalements.index', ['school_year_id' => debtSchoolYears()[0]->id]))
        ->assertOk()
        ->assertSee('Aucun impayé');

    // 2027 non couvert → dette affichée
    $this->actingAs($user)->get(route('payment-signalements.index', ['school_year_id' => debtSchoolYears()[1]->id]))
        ->assertOk()
        ->assertSee('Août 2027');
});

test('vip : chaque jour est indépendant, le paiement d\'un jour ne paie pas les autres', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'vip');
    debtAttendance($student, $subject, '2026-08-08', 'present');
    debtAttendance($student, $subject, '2026-08-13', 'present');
    debtAttendance($student, $subject, '2026-08-15', 'present');
    debtPayment($student, $subject, [
        'payment_type' => 'vip',
        'period' => '2026-08-15',
        'amount_due' => 500,
        'amount_paid' => 500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-15',
    ]);

    $content = $this->actingAs($user)->get(route('payment-signalements.index'))
        ->assertOk()
        ->getContent();

    expect(substr_count($content, 'VIP'))->toBe(2)
        ->and($content)->toContain('08 Août 2026')
        ->and($content)->toContain('13 Août 2026')
        ->and($content)->not->toContain('15 Août 2026');
});

test('vip : une dette de 2026 n\'apparaît jamais dans l\'année 2027', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'vip');
    debtAttendance($student, $subject, '2026-08-05', 'present');
    debtAttendance($student, $subject, '2027-01-10', 'present');

    $this->actingAs($user)->get(route('payment-signalements.index', ['school_year_id' => debtSchoolYears()[0]->id]))
        ->assertOk()
        ->assertSee('05 Août 2026')
        ->assertDontSee('10 Janvier 2027');

    $this->actingAs($user)->get(route('payment-signalements.index', ['school_year_id' => debtSchoolYears()[1]->id]))
        ->assertOk()
        ->assertSee('10 Janvier 2027')
        ->assertDontSee('05 Août 2026');
});

test('le select période suit l\'année sélectionnée', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'monthly');
    debtAttendance($student, $subject, '2027-08-10', 'present');

    $response = $this->actingAs($user)->get(route('payment-signalements.index', ['school_year_id' => debtSchoolYears()[1]->id]));

    $response->assertOk()
        ->assertSee('value="2026-09"', false)
        ->assertSee('value="2027-01"', false)
        ->assertSee('value="2027-08"', false)
        ->assertDontSee('value="2026-08"', false)
        ->assertDontSee('value="2027-12"', false)
        ->assertSee('Août 2027');
});

test('le select année scolaire liste les années créées et l\'option Toutes les années', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'monthly');
    debtAttendance($student, $subject, '2027-08-10', 'present');

    $year2025 = debtSchoolYears()[0];
    $year2026 = debtSchoolYears()[1];

    $response = $this->actingAs($user)->get(route('payment-signalements.index'));

    $response->assertOk()
        ->assertSee('Toutes les années')
        ->assertSee('value="'.$year2025->id.'"', false)
        ->assertSee('value="'.$year2026->id.'"', false)
        ->assertSee('2025-2026')
        ->assertSee('2026-2027');
});

test('un signalement stocké ouvert est réutilisé (statut + actions) sans en créer', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'monthly');
    debtAttendance($student, $subject, '2026-08-08', 'present');
    debtStoredSignalement($student, $subject, ['status' => 'sent']);

    $response = $this->actingAs($user)->get(route('payment-signalements.index'));

    $response->assertOk()
        ->assertSee('Envoyé')
        ->assertSee('payment-signalements');

    expect(PaymentSignalement::count())->toBe(1);
});

test('la recherche et la période fonctionnent ensemble', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    $laidi = debtStudent('Laidi', 'Radhia');
    debtEnrollment($laidi, $subject, 'monthly');
    debtAttendance($laidi, $subject, '2026-08-08', 'present');

    $laidiSeptember = debtStudent('Laidi', 'Meriem');
    debtEnrollment($laidiSeptember, $subject, 'monthly');
    debtAttendance($laidiSeptember, $subject, '2026-09-08', 'present');

    $ahmed = debtStudent('Ahmed', 'Ali');
    debtEnrollment($ahmed, $subject, 'monthly');
    debtAttendance($ahmed, $subject, '2026-08-08', 'present');

    $response = $this->actingAs($user)->get(route('payment-signalements.index', [
        'school_year_id' => debtSchoolYears()[0]->id,
        'period' => '2026-08',
        'search' => 'Laidi',
    ]));

    $response->assertOk()
        ->assertSee('Radhia')
        ->assertDontSee('Meriem')
        ->assertDontSee('Ali');
});

test('le filtre statut resolved affiche uniquement l\'historique stocké résolu', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    $laidi = debtStudent('Laidi', 'Radhia');
    debtEnrollment($laidi, $subject, 'monthly');
    debtAttendance($laidi, $subject, '2026-08-08', 'present');
    debtStoredSignalement($laidi, $subject);

    $sami = debtStudent('Sami', 'Bouzid');
    debtStoredSignalement($sami, $subject, ['status' => 'resolved']);

    $response = $this->actingAs($user)->get(route('payment-signalements.index', ['status' => 'resolved']));

    $response->assertOk()
        ->assertSee('Bouzid')
        ->assertDontSee('Radhia');
});

test('une présence sans enrollment ni paiement génère une dette mensuelle par défaut', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create(['name' => 'MATIMATIQUE']);
    debtAttendance($student, $subject, '2026-08-15', 'present');

    $response = $this->actingAs($user)->get(route('payment-signalements.index'));

    $response->assertOk()
        ->assertSee('Laidi')
        ->assertSee('MATIMATIQUE')
        ->assertSee('Mensuel')
        ->assertSee('Août 2026')
        ->assertSee('En attente');

    // Aucun signalement stocké créé pendant l'affichage
    expect(PaymentSignalement::count())->toBe(0);
});

test('une présence sans enrollment avec paiement mensuel partiel affiche le restant', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtAttendance($student, $subject, '2026-08-15', 'present');
    debtPayment($student, $subject, [
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1000,
        'remaining_amount' => 500,
        'payment_date' => '2026-08-14',
    ]);

    $this->actingAs($user)->get(route('payment-signalements.index'))
        ->assertOk()
        ->assertSee('500,00')
        ->assertSee('En attente');
});

test('une présence est une dette même si l\'enrollment concerne une autre matière', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $matimatique = Subject::factory()->create(['name' => 'MATIMATIQUE']);
    $englais = Subject::factory()->create(['name' => 'englais']);
    debtEnrollment($student, $englais, 'monthly');
    debtAttendance($student, $matimatique, '2026-08-15', 'present');

    $this->actingAs($user)->get(route('payment-signalements.index'))
        ->assertOk()
        ->assertSee('MATIMATIQUE')
        ->assertSee('Mensuel')
        ->assertSee('Août 2026');
});

test('un paiement d\'octobre ne paie jamais la dette d\'août', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'monthly');
    debtAttendance($student, $subject, '2026-08-08', 'present');
    debtPayment($student, $subject, [
        'period' => '2026-10',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-14',
    ]);

    $this->actingAs($user)->get(route('payment-signalements.index'))
        ->assertOk()
        ->assertSee('Août 2026')
        ->assertSee('En attente');
});

test('un paiement complet d\'août paie la dette d\'août', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'monthly');
    debtAttendance($student, $subject, '2026-08-08', 'present');
    debtPayment($student, $subject, [
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-20',
    ]);

    $this->actingAs($user)->get(route('payment-signalements.index'))
        ->assertOk()
        ->assertSee('Aucun impayé');
});

test('un paiement partiel d\'août laisse le montant restant correct', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'monthly');
    debtAttendance($student, $subject, '2026-08-08', 'present');
    debtPayment($student, $subject, [
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1000,
        'remaining_amount' => 500,
        'payment_date' => '2026-08-14',
    ]);

    $this->actingAs($user)->get(route('payment-signalements.index'))
        ->assertOk()
        ->assertSee('500,00');
});

test('un paiement sur une autre matière ne paie pas la dette', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $matimatique = Subject::factory()->create(['name' => 'MATIMATIQUE']);
    $englais = Subject::factory()->create(['name' => 'englais']);
    debtEnrollment($student, $englais, 'monthly');
    debtAttendance($student, $matimatique, '2026-08-15', 'present');
    debtPayment($student, $englais, [
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-20',
    ]);

    $this->actingAs($user)->get(route('payment-signalements.index'))
        ->assertOk()
        ->assertSee('MATIMATIQUE')
        ->assertSee('Mensuel')
        ->assertSee('Août 2026');
});

test('vip : le paiement d\'une autre journée ne paie pas la journée même si payment_date correspond', function () {
    $user = User::factory()->create();
    $student = debtStudent('Laidi', 'LAIDI');
    $subject = Subject::factory()->create();
    debtEnrollment($student, $subject, 'vip');
    debtAttendance($student, $subject, '2026-08-08', 'present');
    debtPayment($student, $subject, [
        'payment_type' => 'vip',
        'period' => '2026-08-10',
        'amount_due' => 500,
        'amount_paid' => 500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-08',
    ]);

    $content = $this->actingAs($user)->get(route('payment-signalements.index'))
        ->assertOk()
        ->getContent();

    expect($content)->toContain('08 Août 2026');
});

test('les statistiques sont calculées au scope année/période uniquement', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    $a = debtStudent('Laidi', 'Radhia');
    debtEnrollment($a, $subject, 'monthly');
    debtAttendance($a, $subject, '2026-08-08', 'present');

    $b = debtStudent('Sami', 'Bouzid');
    debtEnrollment($b, $subject, 'monthly');
    debtAttendance($b, $subject, '2026-08-10', 'present');
    debtAttendance($b, $subject, '2026-09-10', 'present');

    $this->actingAs($user)->get(route('payment-signalements.index', ['school_year_id' => debtSchoolYears()[0]->id, 'period' => '2026-08']))
        ->assertOk()
        ->assertViewHas('pendingCount', 2)
        ->assertViewHas('sentCount', 0)
        ->assertViewHas('resolvedCount', 0);
});

test('le total restant correspond aux dettes actives de la période', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    $a = debtStudent('Laidi', 'Radhia');
    debtEnrollment($a, $subject, 'monthly');
    debtAttendance($a, $subject, '2026-08-08', 'present');
    debtPayment($a, $subject, [
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1000,
        'remaining_amount' => 500,
        'payment_date' => '2026-08-14',
    ]);

    $this->actingAs($user)->get(route('payment-signalements.index', ['school_year_id' => debtSchoolYears()[0]->id, 'period' => '2026-08']))
        ->assertOk()
        ->assertViewHas('totalRemaining', 500.0);
});

test('les statistiques incluent l\'historique résolu de l\'année/période', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    $a = debtStudent('Laidi', 'Radhia');
    debtEnrollment($a, $subject, 'monthly');
    debtAttendance($a, $subject, '2026-08-08', 'present');

    $b = debtStudent('Sami', 'Bouzid');
    debtStoredSignalement($b, $subject, ['status' => 'resolved']);

    $this->actingAs($user)->get(route('payment-signalements.index', ['school_year_id' => debtSchoolYears()[0]->id]))
        ->assertOk()
        ->assertViewHas('pendingCount', 1)
        ->assertViewHas('resolvedCount', 1);
});
