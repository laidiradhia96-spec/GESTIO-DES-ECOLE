<?php

use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\RevenueService;

// =========================================================
// HELPERS
// =========================================================

function createRevenueGroup(Teacher $teacher, Subject $subject, array $tariffData = []): Group
{
    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $group = Group::create([
        'name' => 'Groupe Normal',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'max_students' => 30,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    GroupTariff::create(array_merge([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
        'effective_from' => '2026-01-01',
    ], $tariffData));

    return $group;
}

function createRevenuePayment(Student $student, Subject $subject, Group $group, array $overrides = []): Payment
{
    return Payment::create(array_merge([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'school_year_id' => $group->school_year_id,
        'receipt_number' => 'RCP-'.uniqid(),
        'payment_type' => 'monthly',
        'period' => 'Septembre',
        'amount_due' => 2000,
        'amount_paid' => 2000,
        'remaining_amount' => 0,
        'payment_method' => 'Espèces',
        'payment_date' => now()->toDateString(),
        'payment_time' => now()->format('H:i:s'),
    ], $overrides));
}

// =========================================================
// TEST 1: Paiement complet → parts correctes
// =========================================================

test('full payment: teacher and academy shares are correct', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createRevenueGroup($teacher, $subject, [
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
    ]);

    createRevenuePayment($student, $subject, $group, [
        'amount_paid' => 2000,
    ]);

    $response = $this->actingAs($user)->get(route('revenus.index'));

    $response->assertStatus(200);

    // Vérifier les stats dans la vue
    $response->assertSeeText('1 200,00 DA');   // part prof
    $response->assertSeeText('800,00 DA');     // part académie
    $response->assertSeeText('2 000,00 DA');   // total encaissé
});

// =========================================================
// TEST 2: Paiement partiel → parts proportionnelles
// =========================================================

test('partial payment: shares are proportional', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createRevenueGroup($teacher, $subject, [
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
    ]);

    // Paye 1000 sur 2000 → 50%
    createRevenuePayment($student, $subject, $group, [
        'amount_paid' => 1000,
    ]);

    $response = $this->actingAs($user)->get(route('revenus.index'));

    $response->assertStatus(200);

    // 1000 × (1200/2000) = 600 prof
    // 1000 × (800/2000) = 400 académie
    $response->assertSeeText('600,00 DA');    // part prof
    $response->assertSeeText('400,00 DA');    // part académie
    $response->assertSeeText('1 000,00 DA');  // total encaissé
});

// =========================================================
// TEST 3: Plusieurs paiements partiels → agrégation
// =========================================================

test('multiple partial payments: aggregated correctly', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createRevenueGroup($teacher, $subject, [
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
    ]);

    // 2 paiements de 500 chacun
    createRevenuePayment($student, $subject, $group, [
        'amount_paid' => 500,
        'period' => 'Septembre',
    ]);

    createRevenuePayment($student, $subject, $group, [
        'amount_paid' => 500,
        'period' => 'Octobre',
    ]);

    $response = $this->actingAs($user)->get(route('revenus.index'));

    $response->assertStatus(200);

    // Total encaissé = 1000
    // 1000 × (1200/2000) = 600 prof
    // 1000 × (800/2000) = 400 académie
    $response->assertSeeText('1 000,00 DA');
    $response->assertSeeText('600,00 DA');
    $response->assertSeeText('400,00 DA');
    $response->assertSeeText('2');
});

// =========================================================
// TEST 4: Paiements VIP → indépendants par séance
// =========================================================

test('vip payments: each session counted independently', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createRevenueGroup($teacher, $subject, [
        'student_price' => 500,
        'teacher_share' => 300,
        'academy_share' => 200,
    ]);

    // 3 séances VIP
    createRevenuePayment($student, $subject, $group, [
        'payment_type' => 'vip_per_session',
        'amount_paid' => 500,
        'period' => 'Séance 1',
    ]);

    createRevenuePayment($student, $subject, $group, [
        'payment_type' => 'vip_per_session',
        'amount_paid' => 500,
        'period' => 'Séance 2',
    ]);

    createRevenuePayment($student, $subject, $group, [
        'payment_type' => 'vip_per_session',
        'amount_paid' => 500,
        'period' => 'Séance 3',
    ]);

    $response = $this->actingAs($user)->get(route('revenus.index'));

    $response->assertStatus(200);

    // Total = 1500 → prof = 900, acad = 600
    $response->assertSeeText('1 500,00 DA');
    $response->assertSeeText('900,00 DA');
    $response->assertSeeText('600,00 DA');
    $response->assertSeeText('3');
});

// =========================================================
// TEST 5: Plusieurs groupes → chaque paiement au bon prof
// =========================================================

test('multiple groups: each payment assigned to correct teacher', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher1 = Teacher::factory()->create();
    $teacher2 = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group1 = createRevenueGroup($teacher1, $subject, [
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
    ]);

    $group2 = createRevenueGroup($teacher2, $subject, [
        'student_price' => 3000,
        'teacher_share' => 1800,
        'academy_share' => 1200,
    ]);

    createRevenuePayment($student, $subject, $group1, ['amount_paid' => 2000]);
    createRevenuePayment($student, $subject, $group2, ['amount_paid' => 3000]);

    $response = $this->actingAs($user)->get(route('revenus.index'));

    $response->assertStatus(200);

    // Teacher 1: 2000 collected, Teacher 2: 3000 collected
    $response->assertSeeText($teacher1->last_name);
    $response->assertSeeText($teacher2->last_name);
    // Total encaissé = 5 000
    $response->assertSeeText('5 000,00');
});

// =========================================================
// TEST 6: Plusieurs enseignants → pas de contamination croisée
// =========================================================

test('multiple teachers: no cross-contamination', function () {
    $user = User::factory()->create();
    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();
    $teacher1 = Teacher::factory()->create();
    $teacher2 = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group1 = createRevenueGroup($teacher1, $subject, [
        'student_price' => 1000,
        'teacher_share' => 600,
        'academy_share' => 400,
    ]);

    $group2 = createRevenueGroup($teacher2, $subject, [
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
    ]);

    createRevenuePayment($student1, $subject, $group1, ['amount_paid' => 1000]);
    createRevenuePayment($student2, $subject, $group2, ['amount_paid' => 2000]);

    $response = $this->actingAs($user)->get(route('revenus.fiche', $teacher1));

    $response->assertStatus(200);

    // Teacher 1: total = 1000, part prof = 600, part acad = 400
    $response->assertSeeText('1 000,00 DA');
    $response->assertSeeText('600,00 DA');
    $response->assertSeeText('400,00 DA');

    // Teacher 1's name
    $response->assertSeeText($teacher1->first_name);
    $response->assertSeeText($teacher1->last_name);
});

// =========================================================
// TEST 7: Plusieurs matières → filtre par matière
// =========================================================

test('filter by subject: only matching payments shown', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject1 = Subject::factory()->create();
    $subject2 = Subject::factory()->create();

    $group1 = createRevenueGroup($teacher, $subject1, [
        'student_price' => 1000,
        'teacher_share' => 600,
        'academy_share' => 400,
    ]);

    $group2 = createRevenueGroup($teacher, $subject2, [
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
    ]);

    createRevenuePayment($student, $subject1, $group1, ['amount_paid' => 1000]);
    createRevenuePayment($student, $subject2, $group2, ['amount_paid' => 2000]);

    // Filtrer par matière 1
    $response = $this->actingAs($user)->get(route('revenus.index', [
        'subject_id' => $subject1->id,
    ]));

    $response->assertStatus(200);

    // Ne montrer que le paiement de la matière 1
    $response->assertSeeText('1 000,00 DA');
    $response->assertSeeText('600,00 DA');
    $response->assertSeeText('400,00 DA');
});

// =========================================================
// TEST 8: Filtre mois → exclusions
// =========================================================

test('filter by month: payments from other months excluded', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createRevenueGroup($teacher, $subject, [
        'student_price' => 1000,
        'teacher_share' => 600,
        'academy_share' => 400,
    ]);

    // Paiement en septembre (mois 9)
    createRevenuePayment($student, $subject, $group, [
        'amount_paid' => 1000,
        'payment_date' => '2026-09-15',
        'period' => 'Septembre',
    ]);

    // Paiement en octobre (mois 10)
    createRevenuePayment($student, $subject, $group, [
        'amount_paid' => 1000,
        'payment_date' => '2026-10-15',
        'period' => 'Octobre',
    ]);

    // Filtrer septembre
    $response = $this->actingAs($user)->get(route('revenus.index', [
        'month' => 9,
        'year' => 2026,
    ]));

    $response->assertStatus(200);

    // Un seul paiement
    $response->assertSeeText('1 000,00 DA');
    $response->assertSeeText('600,00 DA');
    $response->assertSeeText('400,00 DA');
});

// =========================================================
// TEST 9: Tarif historique → ancien paiement utilise ancien tarif
// =========================================================

test('historical tariff: old payment uses old tariff', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $group = Group::create([
        'name' => 'Groupe Normal',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'max_students' => 30,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    // Ancien tarif (septembre)
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 1000,
        'teacher_share' => 600,
        'academy_share' => 400,
        'effective_from' => '2026-09-01',
        'effective_to' => '2026-09-30',
    ]);

    // Nouveau tarif (octobre)
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 900,
        'academy_share' => 600,
        'effective_from' => '2026-10-01',
    ]);

    // Paiement septembre (ancien tarif)
    createRevenuePayment($student, $subject, $group, [
        'amount_paid' => 1000,
        'payment_date' => '2026-09-15',
        'period' => 'Septembre',
    ]);

    // Paiement octobre (nouveau tarif)
    createRevenuePayment($student, $subject, $group, [
        'amount_paid' => 1500,
        'payment_date' => '2026-10-15',
        'period' => 'Octobre',
    ]);

    // Vérifier fiche enseignant
    $response = $this->actingAs($user)->get(route('revenus.fiche', $teacher));

    $response->assertStatus(200);

    // Septembre: 1000 × (600/1000) = 600 prof, 1000 × (400/1000) = 400 acad
    // Octobre: 1500 × (900/1500) = 900 prof, 1500 × (600/1500) = 600 acad
    // Total: prof = 1500, acad = 1000, encaissé = 2500
    $response->assertSeeText('2 500,00');
    $response->assertSeeText('1 500,00');
    $response->assertSeeText('1 000,00');
});

// =========================================================
// TEST 10: Aucun paiement → revenus = 0
// =========================================================

test('no payments: revenue is zero', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();

    $response = $this->actingAs($user)->get(route('revenus.fiche', $teacher));

    $response->assertStatus(200);

    $response->assertSeeText('0,00');
    $response->assertSeeText('Aucun paiement');
});

// =========================================================
// TEST 11: Dashboard stats → total = teacher + academy
// =========================================================

test('dashboard stats: total equals teacher share plus academy share', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createRevenueGroup($teacher, $subject, [
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
    ]);

    createRevenuePayment($student, $subject, $group, ['amount_paid' => 2000]);

    $response = $this->actingAs($user)->get(route('revenus.index'));

    $response->assertStatus(200);

    // Stats: 2000 = 1200 + 800
    $response->assertSeeText('2 000,00');
    $response->assertSeeText('1 200,00');
    $response->assertSeeText('800,00');
});

// =========================================================
// TEST 12: Page print → renders correctly
// =========================================================

test('print page: renders correctly', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createRevenueGroup($teacher, $subject, [
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
    ]);

    createRevenuePayment($student, $subject, $group, ['amount_paid' => 2000]);

    $response = $this->actingAs($user)->get(route('revenus.print', $teacher));

    $response->assertStatus(200);

    // Vérifier le contenu de la page d'impression
    $response->assertSeeText('ACADÉMIE EL TAFAWOK');
    $response->assertSeeText('Fiche de paie');
    $response->assertSeeText($teacher->first_name);
    $response->assertSeeText($teacher->last_name);
    $response->assertSeeText('1 200,00');
    $response->assertSeeText('800,00');
    $response->assertSeeText('2 000,00');
});

// =========================================================
// TEST 13: Auth required → redirect si non connecté
// =========================================================

test('auth required: redirect if not authenticated', function () {
    $teacher = Teacher::factory()->create();

    $response = $this->get(route('revenus.index'));

    $response->assertRedirect(route('login'));
});

// =========================================================
// TEST 14: Admin required → 403 si non admin
// =========================================================

test('admin required: 403 if not admin', function () {
    $user = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($user)->get(route('revenus.index'));

    $response->assertForbidden();
});

// =========================================================
// TEST 15: Mise à jour d'un ancien paiement → tarif historique
// =========================================================

test('update old payment: historical tariff is used not current', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $group = Group::create([
        'name' => 'Groupe Normal',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'max_students' => 30,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    // January tariff: student_price=2000, teacher_share=1500, academy_share=500
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
        'effective_from' => '2026-01-01',
        'effective_to' => '2026-01-31',
        'is_active' => true,
    ]);

    // February tariff: student_price=3000, teacher_share=2000, academy_share=1000
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 3000,
        'teacher_share' => 2000,
        'academy_share' => 1000,
        'effective_from' => '2026-02-01',
        'effective_to' => null,
        'is_active' => true,
    ]);

    // Create payment dated January 15
    $payment = Payment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'school_year_id' => $year->id,
        'receipt_number' => 'REC-2026-00001',
        'payment_type' => 'monthly',
        'period' => 'Janvier',
        'amount_due' => 2000,
        'amount_paid' => 1000,
        'remaining_amount' => 1000,
        'payment_method' => 'Espèces',
        'payment_date' => '2026-01-15',
        'payment_time' => '10:00:00',
    ]);

    // Update the payment (simulating current date = February)
    $response = $this->actingAs($user)->put(route('payments.update', $payment), [
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'payment_type' => 'monthly',
        'period' => 'Janvier',
        'amount_paid' => 1000,
        'payment_method' => 'Espèces',
        'payment_date' => '2026-01-15',
    ]);

    $response->assertRedirect();

    $payment->refresh();

    // January tariff: 1000 × (1500/2000) = 750 teacher, 1000 × (500/2000) = 250 academy
    expect((float) $payment->teacher_share)->toBe(750.0);
    expect((float) $payment->academy_share)->toBe(250.0);

    // Assert February tariff was NOT used
    // February: 1000 × (2000/3000) ≈ 666.67 teacher, 1000 × (1000/3000) ≈ 333.33 academy
    expect((float) $payment->teacher_share)->not->toBe(666.67);
    expect((float) $payment->academy_share)->not->toBe(333.33);
});

// =========================================================
// TEST 16: Mise à jour → pas de contamination croisée groupes
// =========================================================

test('update payment: correct group tariff used not other group', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    // Group A with January tariff
    $groupA = Group::create([
        'name' => 'Groupe A',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'max_students' => 30,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $groupA->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
        'effective_from' => '2026-01-01',
        'effective_to' => '2026-01-31',
        'is_active' => true,
    ]);

    // Group B with different tariff
    $groupB = Group::create([
        'name' => 'Groupe B',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'max_students' => 30,
        'mode' => 'vip',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $groupB->id,
        'billing_type' => 'per_session',
        'student_price' => 500,
        'teacher_share' => 300,
        'academy_share' => 200,
        'effective_from' => '2026-01-01',
        'effective_to' => null,
        'is_active' => true,
    ]);

    // Create payment for Group A
    $payment = Payment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $groupA->id,
        'school_year_id' => $year->id,
        'receipt_number' => 'REC-2026-00002',
        'payment_type' => 'monthly',
        'period' => 'Janvier',
        'amount_due' => 2000,
        'amount_paid' => 1000,
        'remaining_amount' => 1000,
        'payment_method' => 'Espèces',
        'payment_date' => '2026-01-15',
        'payment_time' => '10:00:00',
    ]);

    // Update payment keeping it in Group A
    $response = $this->actingAs($user)->put(route('payments.update', $payment), [
        'subject_id' => $subject->id,
        'group_id' => $groupA->id,
        'payment_type' => 'monthly',
        'period' => 'Janvier',
        'amount_paid' => 1000,
        'payment_method' => 'Espèces',
        'payment_date' => '2026-01-15',
    ]);

    $response->assertRedirect();

    $payment->refresh();

    // Group A tariff: 1000 × (1500/2000) = 750 teacher, 1000 × (500/2000) = 250 academy
    expect((float) $payment->teacher_share)->toBe(750.0);
    expect((float) $payment->academy_share)->toBe(250.0);

    // Group B tariff: 1000 × (300/500) = 600 teacher, 1000 × (200/500) = 400 academy
    // Assert Group B's tariff was NOT used
    expect((float) $payment->teacher_share)->not->toBe(600.0);
    expect((float) $payment->academy_share)->not->toBe(400.0);
});

// =========================================================
// TEST A: Répartition par groupe → deux groupes séparés
// =========================================================

test('group aggregation: two groups produce separate rows', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $groupA = Group::create([
        'name' => '1AS G1',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $groupA->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
        'effective_from' => now()->toDateString(),
    ]);

    $groupB = Group::create([
        'name' => '1AS G2',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $groupB->id,
        'billing_type' => 'monthly',
        'student_price' => 1000,
        'teacher_share' => 750,
        'academy_share' => 250,
        'effective_from' => now()->toDateString(),
    ]);

    createRevenuePayment($student, $subject, $groupA, ['amount_paid' => 10000]);
    createRevenuePayment($student, $subject, $groupB, ['amount_paid' => 6000]);

    $revenueService = app(RevenueService::class);
    $groupRevenue = $revenueService->getTeacherRevenueByGroup($teacher->id);

    expect($groupRevenue->count())->toBe(2);

    // Group A: 10000 × (1500/2000) = 7500 teacher, 10000 × (500/2000) = 2500 academy
    $rowA = $groupRevenue->firstWhere('group.name', '1AS G1');
    expect($rowA->total_collected)->toBe(10000.0);
    expect($rowA->teacher_share)->toBe(7500.0);
    expect($rowA->academy_share)->toBe(2500.0);

    // Group B: 6000 × (750/1000) = 4500 teacher, 6000 × (250/1000) = 1500 academy
    $rowB = $groupRevenue->firstWhere('group.name', '1AS G2');
    expect($rowB->total_collected)->toBe(6000.0);
    expect($rowB->teacher_share)->toBe(4500.0);
    expect($rowB->academy_share)->toBe(1500.0);

    // Totals: 16000 / 12000 / 4000
    expect($groupRevenue->sum('total_collected'))->toBe(16000.0);
    expect($groupRevenue->sum('teacher_share'))->toBe(12000.0);
    expect($groupRevenue->sum('academy_share'))->toBe(4000.0);
});

// =========================================================
// TEST B: Même prof + même matière + groupes différents
// =========================================================

test('same teacher same subject different groups: rows stay separate', function () {
    $user = User::factory()->create();
    $studentA = Student::factory()->create();
    $studentB = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $groupA = Group::create([
        'name' => '1AS G1',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $groupA->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
        'effective_from' => now()->toDateString(),
    ]);

    $groupB = Group::create([
        'name' => '1AS G2',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $groupB->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
        'effective_from' => now()->toDateString(),
    ]);

    createRevenuePayment($studentA, $subject, $groupA, ['amount_paid' => 2000]);
    createRevenuePayment($studentB, $subject, $groupB, ['amount_paid' => 2000]);

    $revenueService = app(RevenueService::class);
    $groupRevenue = $revenueService->getTeacherRevenueByGroup($teacher->id);

    expect($groupRevenue->count())->toBe(2);

    $rowA = $groupRevenue->firstWhere('group.name', '1AS G1');
    expect($rowA->total_collected)->toBe(2000.0);
    expect($rowA->teacher_share)->toBe(1500.0);
    expect($rowA->academy_share)->toBe(500.0);

    $rowB = $groupRevenue->firstWhere('group.name', '1AS G2');
    expect($rowB->total_collected)->toBe(2000.0);
    expect($rowB->teacher_share)->toBe(1500.0);
    expect($rowB->academy_share)->toBe(500.0);

    // Verify they are NOT merged
    expect($rowA->total_collected)->not->toBe(4000.0);
});

// =========================================================
// TEST C: Tarifs différents par groupe
// =========================================================

test('different tariffs per group: each group uses its own snapshot', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $groupA = Group::create([
        'name' => '1AS G1',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $groupA->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
        'effective_from' => now()->toDateString(),
    ]);

    $groupB = Group::create([
        'name' => '3AS VIP',
        'level' => '3AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'vip',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $groupB->id,
        'billing_type' => 'per_session',
        'student_price' => 5000,
        'teacher_share' => 3000,
        'academy_share' => 2000,
        'effective_from' => now()->toDateString(),
    ]);

    createRevenuePayment($student, $subject, $groupA, ['amount_paid' => 2000]);
    createRevenuePayment($student, $subject, $groupB, ['amount_paid' => 5000]);

    $revenueService = app(RevenueService::class);
    $groupRevenue = $revenueService->getTeacherRevenueByGroup($teacher->id);

    // Group A: 1500 teacher / 500 academy
    $rowA = $groupRevenue->firstWhere('group.name', '1AS G1');
    expect($rowA->teacher_share)->toBe(1500.0);
    expect($rowA->academy_share)->toBe(500.0);

    // Group B: 3000 teacher / 2000 academy
    $rowB = $groupRevenue->firstWhere('group.name', '3AS VIP');
    expect($rowB->teacher_share)->toBe(3000.0);
    expect($rowB->academy_share)->toBe(2000.0);

    // Total: 4500 / 2500
    expect($groupRevenue->sum('teacher_share'))->toBe(4500.0);
    expect($groupRevenue->sum('academy_share'))->toBe(2500.0);
});

// =========================================================
// TEST D: Paiement partiel → parts proportionnelles par groupe
// =========================================================

test('partial payment: group revenue shows proportional shares', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createRevenueGroup($teacher, $subject, [
        'name' => '1AS G1',
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
    ]);

    createRevenuePayment($student, $subject, $group, [
        'amount_paid' => 1000,
    ]);

    $revenueService = app(RevenueService::class);
    $groupRevenue = $revenueService->getTeacherRevenueByGroup($teacher->id);

    $row = $groupRevenue->first();

    // 1000 × (1500/2000) = 750 teacher
    // 1000 × (500/2000) = 250 academy
    expect($row->total_collected)->toBe(1000.0);
    expect($row->teacher_share)->toBe(750.0);
    expect($row->academy_share)->toBe(250.0);

    // Also verify via fiche route
    $response = $this->actingAs($user)->get(route('revenus.fiche', $teacher));
    $response->assertStatus(200);
    $response->assertSeeText('1 000,00');
    $response->assertSeeText('750,00');
    $response->assertSeeText('250,00');
});

// =========================================================
// TEST E: Paiement VIP partiel → seulement l'encaissé
// =========================================================

test('vip partial payment: payslip shows only collected amount not obligation', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createRevenueGroup($teacher, $subject, [
        'name' => '3AS VIP',
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
    ]);

    // 4 sessions × 2000 = 8000 obligation, but only 2000 paid
    createRevenuePayment($student, $subject, $group, [
        'payment_type' => 'vip_per_session',
        'amount_paid' => 2000,
    ]);

    $revenueService = app(RevenueService::class);
    $groupRevenue = $revenueService->getTeacherRevenueByGroup($teacher->id);

    $row = $groupRevenue->first();

    // Must show 2000, NOT 8000
    expect($row->total_collected)->toBe(2000.0);
    expect($row->teacher_share)->toBe(1500.0);
    expect($row->academy_share)->toBe(500.0);

    // Verify the total collected is 2000, not 8000
    $response = $this->actingAs($user)->get(route('revenus.fiche', $teacher));
    $response->assertStatus(200);
    $response->assertSeeText('2 000,00');
    $response->assertDontSeeText('8 000,00');
});

// =========================================================
// TEST F: Tarif historique → fiche utilise le snapshot, pas le tarif actuel
// =========================================================

test('historical tariff: fiche uses payment snapshot not current tariff', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $group = Group::create([
        'name' => '1AS G1',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'max_students' => 30,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    // January tariff: student_price=2000, teacher=1500, academy=500
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
        'effective_from' => '2026-01-01',
        'effective_to' => '2026-01-31',
        'is_active' => true,
    ]);

    // February tariff: student_price=3000, teacher=2000, academy=1000
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 3000,
        'teacher_share' => 2000,
        'academy_share' => 1000,
        'effective_from' => '2026-02-01',
        'effective_to' => null,
        'is_active' => true,
    ]);

    // Payment in January (snapshot saved: 750 teacher / 250 academy)
    $payment = Payment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'school_year_id' => $year->id,
        'receipt_number' => 'REC-HIST-001',
        'payment_type' => 'monthly',
        'period' => 'Janvier',
        'amount_due' => 2000,
        'amount_paid' => 1000,
        'remaining_amount' => 1000,
        'teacher_share' => 750,
        'academy_share' => 250,
        'payment_method' => 'Espèces',
        'payment_date' => '2026-01-15',
        'payment_time' => '10:00:00',
    ]);

    // Fiche for January
    $response = $this->actingAs($user)->get(route('revenus.fiche', $teacher, [
        'month' => 1,
        'year' => 2026,
    ]));

    $response->assertStatus(200);

    // Must use snapshot: 1000 collected, 750 teacher, 250 academy
    $response->assertSeeText('1 000,00');
    $response->assertSeeText('750,00');
    $response->assertSeeText('250,00');

    // Must NOT use February tariff: 1000 × (2000/3000) ≈ 666.67
    $response->assertDontSeeText('666,67');

    // Also verify via service
    $revenueService = app(RevenueService::class);
    $groupRevenue = $revenueService->getTeacherRevenueByGroup($teacher->id, 1, 2026);
    $row = $groupRevenue->first();
    expect($row->teacher_share)->toBe(750.0);
    expect($row->academy_share)->toBe(250.0);
});

// =========================================================
// TEST G: Réconciliation → somme des groupes = totaux enseignant
// =========================================================

test('reconciliation: sum of group totals equals teacher totals', function () {
    $user = User::factory()->create();
    $studentA = Student::factory()->create();
    $studentB = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $groupA = Group::create([
        'name' => '1AS G1',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $groupA->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
        'effective_from' => now()->toDateString(),
    ]);

    $groupB = Group::create([
        'name' => '1AS G2',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $groupB->id,
        'billing_type' => 'monthly',
        'student_price' => 1000,
        'teacher_share' => 750,
        'academy_share' => 250,
        'effective_from' => now()->toDateString(),
    ]);

    createRevenuePayment($studentA, $subject, $groupA, ['amount_paid' => 2000]);
    createRevenuePayment($studentA, $subject, $groupA, ['amount_paid' => 2000, 'period' => 'Octobre']);
    createRevenuePayment($studentB, $subject, $groupB, ['amount_paid' => 1000]);

    $revenueService = app(RevenueService::class);

    // Group breakdown
    $groupRevenue = $revenueService->getTeacherRevenueByGroup($teacher->id);

    $sumCollected = $groupRevenue->sum('total_collected');
    $sumTeacherShare = $groupRevenue->sum('teacher_share');
    $sumAcademyShare = $groupRevenue->sum('academy_share');

    // Teacher totals (from individual payments)
    $payments = $revenueService->getTeacherPayments($teacher->id);
    $totalCollected = $payments->sum('teacher_share') + $payments->sum('academy_share');
    $totalTeacherShare = $payments->sum('teacher_share');
    $totalAcademyShare = $payments->sum('academy_share');

    // Must reconcile exactly
    expect($sumCollected)->toBe($totalCollected);
    expect($sumTeacherShare)->toBe($totalTeacherShare);
    expect($sumAcademyShare)->toBe($totalAcademyShare);

    // Verify via the fiche route
    $response = $this->actingAs($user)->get(route('revenus.fiche', $teacher));
    $response->assertStatus(200);

    // Both the summary and group breakdown totals must appear identically
    // GroupA: 4000 collected / 3000 teacher / 1000 academy
    // GroupB: 1000 collected / 750 teacher / 250 academy
    // Total: 5000 / 3750 / 1250
    $response->assertSeeText('5 000,00');
    $response->assertSeeText('3 750,00');
    $response->assertSeeText('1 250,00');
});
