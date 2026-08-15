<?php

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\PaymentSignalement;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

function activeEnrollment(Student $student, Subject $subject, Teacher $teacher): Enrollment
{
    return Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'payment_type' => 'monthly',
    ]);
}

test('un utilisateur authentifié peut supprimer une présence', function () {
    $user = User::factory()->create();
    $attendance = Attendance::factory()->create([
        'student_id' => Student::factory()->create()->id,
        'subject_id' => Subject::factory()->create()->id,
        'teacher_id' => Teacher::factory()->create()->id,
        'date' => '2026-08-10',
        'status' => 'present',
    ]);

    $response = $this->actingAs($user)->delete(route('attendances.destroy', $attendance));

    $response->assertRedirect(route('attendances.index'))
        ->assertSessionHas('success');

    expect(Attendance::find($attendance->id))->toBeNull();
});

test('la suppression conserve les filtres de la liste', function () {
    $user = User::factory()->create();
    $attendance = Attendance::factory()->create([
        'student_id' => Student::factory()->create()->id,
        'subject_id' => Subject::factory()->create()->id,
        'teacher_id' => Teacher::factory()->create()->id,
        'date' => '2026-08-10',
        'status' => 'present',
    ]);

    $response = $this->actingAs($user)->delete(route('attendances.destroy', $attendance).'?status=present&date=2026-08-10');

    $response->assertRedirect(route('attendances.index', [
        'status' => 'present',
        'date' => '2026-08-10',
    ]));
});

test('supprimer une présence inexistante renvoie une 404', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->delete(route('attendances.destroy', 9999))
        ->assertNotFound();
});

test('l\'endpoint students ne renvoie que les élèves à enrollment actif pour matière + enseignant', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher1 = Teacher::factory()->create();
    $teacher2 = Teacher::factory()->create();

    $enrolled = Student::factory()->create();
    activeEnrollment($enrolled, $subject, $teacher1);

    $otherTeacher = Student::factory()->create();
    activeEnrollment($otherTeacher, $subject, $teacher2);

    $otherSubject = Subject::factory()->create();
    $otherSubjectStudent = Student::factory()->create();
    activeEnrollment($otherSubjectStudent, $otherSubject, $teacher1);

    $notEnrolled = Student::factory()->create();

    $inactive = Student::factory()->create();
    Enrollment::factory()->inactive()->create([
        'student_id' => $inactive->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher1->id,
    ]);

    $response = $this->actingAs($user)->getJson(route('attendances.students', [
        'subject_id' => $subject->id,
        'teacher_id' => $teacher1->id,
    ]));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['id' => $enrolled->id, 'first_name' => $enrolled->first_name, 'last_name' => $enrolled->last_name])
        ->assertJsonMissing(['id' => $otherTeacher->id])
        ->assertJsonMissing(['id' => $otherSubjectStudent->id])
        ->assertJsonMissing(['id' => $notEnrolled->id])
        ->assertJsonMissing(['id' => $inactive->id]);
});

test('l\'endpoint students exige matière et enseignant', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    $this->actingAs($user)->getJson(route('attendances.students'))
        ->assertUnprocessable();

    $this->actingAs($user)->getJson(route('attendances.students', [
        'subject_id' => $subject->id,
    ]))->assertUnprocessable();
});

test('la page de création affiche le message et aucun élève par défaut', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create([
        'first_name' => 'NomUniqueTest',
    ]);

    $response = $this->actingAs($user)->get(route('attendances.create'));

    $response->assertOk()
        ->assertSee('Sélectionnez une matière et un enseignant')
        ->assertDontSee($student->first_name);
});

test('store rejette un élève non inscrit à cette matière avec cet enseignant', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create();
    $notEnrolled = Student::factory()->create();

    activeEnrollment($student, $subject, $teacher);

    $response = $this->actingAs($user)->from(route('attendances.create'))->post(route('attendances.store'), [
        'date' => '2026-08-15',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'students' => [
            ['id' => $notEnrolled->id, 'status' => 'present'],
        ],
    ]);

    $response->assertSessionHasErrors('students.0.id');

    expect(Attendance::count())->toBe(0);
});

test('store exige un enseignant', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    $this->actingAs($user)->post(route('attendances.store'), [
        'date' => '2026-08-15',
        'subject_id' => $subject->id,
        'students' => [
            ['id' => Student::factory()->create()->id, 'status' => 'present'],
        ],
    ])->assertSessionHasErrors('teacher_id');

    expect(Attendance::count())->toBe(0);
});

test('store enregistre la présence d\'un élève inscrit et préserve la logique de paiement', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create();

    activeEnrollment($student, $subject, $teacher);

    $response = $this->actingAs($user)->post(route('attendances.store'), [
        'date' => '2026-08-15',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'students' => [
            ['id' => $student->id, 'status' => 'present', 'note' => 'ok'],
        ],
    ]);

    $response->assertRedirect(route('attendances.index'))
        ->assertSessionHas('success');

    $attendance = Attendance::first();

    expect($attendance)->not->toBeNull()
        ->and($attendance->student_id)->toBe($student->id)
        ->and($attendance->subject_id)->toBe($subject->id)
        ->and($attendance->teacher_id)->toBe($teacher->id)
        ->and($attendance->status)->toBe('present')
        ->and($attendance->note)->toBe('ok');

    expect(PaymentSignalement::where('student_id', $student->id)
        ->where('subject_id', $subject->id)
        ->where('status', 'pending')
        ->count())->toBe(1);
});

test('update rejette l\'ajout d\'un élève non inscrit', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $enrolled = Student::factory()->create();
    $notEnrolled = Student::factory()->create();

    activeEnrollment($enrolled, $subject, $teacher);

    $attendance = Attendance::factory()->create([
        'student_id' => $enrolled->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-08-10',
        'status' => 'present',
    ]);

    $response = $this->actingAs($user)->put(route('attendances.update', $attendance), [
        'date' => '2026-08-10',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'students' => [
            ['id' => $enrolled->id, 'status' => 'absent'],
            ['id' => $notEnrolled->id, 'status' => 'present'],
        ],
    ]);

    $response->assertSessionHasErrors('students.1.id');

    expect(Attendance::where('student_id', $notEnrolled->id)->count())->toBe(0);
});
