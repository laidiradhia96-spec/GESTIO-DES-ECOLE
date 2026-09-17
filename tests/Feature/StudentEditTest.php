<?php

use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Level;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

function editHelperLevel(string $code, string $name): Level
{
    return Level::create([
        'name' => $name,
        'code' => $code,
        'active' => true,
    ]);
}

function editHelperSubject(string $level, string $cycle): Subject
{
    return Subject::factory()->create([
        'level' => $level,
        $cycle => true,
        'active' => true,
    ]);
}

function editHelperTeacher(Subject $subject, Level ...$levels): Teacher
{
    $teacher = Teacher::factory()->create(['active' => true]);

    $teacher->levels()->attach($levels);

    $subject->teachers()->attach($teacher);

    return $teacher;
}

function editHelperGroup(Subject $subject, Teacher $teacher, string $level, string $mode = 'normal'): Group
{
    static $groupCounter = 0;

    return Group::factory()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'level' => $level,
        'mode' => $mode,
        'name' => 'Groupe Test '.++$groupCounter,
        'is_active' => true,
    ]);
}

function editHelperEnroll(
    Student $student,
    Subject $subject,
    Teacher $teacher,
    string $paymentType = 'monthly'
): Enrollment {
    return $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'payment_type' => $paymentType,
    ]);
}

function editHelperPayload(Student $student, array $overrides = []): array
{
    return array_merge([
        'first_name' => $student->first_name,
        'last_name' => $student->last_name,
        'date_of_birth' => $student->date_of_birth?->format('Y-m-d') ?? '',
        'phone' => $student->phone,
        'address' => $student->address,
        'level' => $student->level,
        'parent_name' => $student->parent_name,
        'parent_phone' => $student->parent_phone,
    ], $overrides);
}

test('la date de naissance existante est affichée au format YYYY-MM-DD', function () {
    $user = User::factory()->create();

    $student = Student::factory()->create([
        'date_of_birth' => '2012-05-10',
    ]);

    $this->actingAs($user)
        ->get(route('students.edit', $student))
        ->assertOk()
        ->assertSee('value="2012-05-10"', false)
        ->assertDontSee('value="2012-05-10 00:00:00"', false);
});

test('modifier un élève sans modifier la date conserve la date', function () {
    $user = User::factory()->create();

    $student = Student::factory()->create([
        'date_of_birth' => '2012-05-10',
        'level' => '1AP',
    ]);

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'first_name' => 'Nouveau',
            'date_of_birth' => '',
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($student->refresh()->date_of_birth?->format('Y-m-d'))->toBe('2012-05-10');
});

test('modifier la date de naissance persiste la nouvelle date', function () {
    $user = User::factory()->create();

    $student = Student::factory()->create([
        'date_of_birth' => '2012-05-10',
        'level' => '1AP',
    ]);

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'date_of_birth' => '2009-01-15',
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($student->refresh()->date_of_birth?->format('Y-m-d'))->toBe('2009-01-15');
});

test('le niveau actuel est sélectionné dans le formulaire', function () {
    $user = User::factory()->create();

    $student = Student::factory()->create([
        'level' => '3AM',
    ]);

    $response = $this->actingAs($user)
        ->get(route('students.edit', $student))
        ->assertOk();

    $content = $response->getContent();

    expect((bool) preg_match('/<option value="3AM"\s+selected>/', $content))->toBeTrue();
});

test('changer le niveau fonctionne lorsque la nouvelle configuration est compatible', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');
    $sec = editHelperLevel('SEC', 'Secondaire');

    $subject = editHelperSubject('1AP', 'primaire');
    $subject->update(['lycee' => true]);

    $teacher = editHelperTeacher($subject, $pri, $sec);

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollment = editHelperEnroll($student, $subject, $teacher);

    $group = editHelperGroup($subject, $teacher, '1AS');

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'level' => '1AS',
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($student->refresh()->level)->toBe('1AS')
        ->and($enrollment->refresh()->status)->toBe('active');
});

test('changer le niveau et la matière simultanément fonctionne quand la nouvelle configuration est compatible', function () {
    $user = User::factory()->create();

    $moy = editHelperLevel('MOY', 'Moyen');
    $sec = editHelperLevel('SEC', 'Secondaire');

    $oldSubject = editHelperSubject('3AM', 'moyen');
    $oldTeacher = editHelperTeacher($oldSubject, $moy);

    $newSubject = editHelperSubject('1AS', 'lycee');
    $newTeacher = editHelperTeacher($newSubject, $sec);

    $student = Student::factory()->create(['level' => '3AM']);

    $oldEnrollment = editHelperEnroll($student, $oldSubject, $oldTeacher);

    $newGroup = editHelperGroup($newSubject, $newTeacher, '1AS');

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'level' => '1AS',
            'enrollments' => [
                [
                    'id' => $oldEnrollment->id,
                    'subject_id' => $newSubject->id,
                    'teacher_id' => $newTeacher->id,
                    'group_id' => $newGroup->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $student->refresh();

    expect($student->level)->toBe('1AS')
        ->and($oldEnrollment->refresh()->status)->toBe('active')
        ->and($student->enrollments()->where('status', 'active')->count())->toBe(1);

    expect($oldEnrollment->refresh()->subject_id)->toBe($newSubject->id)
        ->and($oldEnrollment->teacher_id)->toBe($newTeacher->id)
        ->and($oldEnrollment->group_id)->toBe($newGroup->id);
});

test('ajouter une matière crée l\'inscription correspondante', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');

    $subjectA = editHelperSubject('1AP', 'primaire');
    $teacherA = editHelperTeacher($subjectA, $pri);

    $subjectB = editHelperSubject('1AP', 'primaire');
    $teacherB = editHelperTeacher($subjectB, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollmentA = editHelperEnroll($student, $subjectA, $teacherA);

    $groupA = editHelperGroup($subjectA, $teacherA, '1AP');
    $groupB = editHelperGroup($subjectB, $teacherB, '1AP');

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enrollmentA->id,
                    'subject_id' => $subjectA->id,
                    'teacher_id' => $teacherA->id,
                    'group_id' => $groupA->id,
                ],
                [
                    'subject_id' => $subjectB->id,
                    'teacher_id' => $teacherB->id,
                    'group_id' => $groupB->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($student->enrollments()->count())->toBe(2)
        ->and($student->enrollments()->where('subject_id', $subjectB->id)->where('status', 'active')->exists())->toBeTrue();
});

test('modifier une matière met a jour l\'inscription existante', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');

    $subjectA = editHelperSubject('1AP', 'primaire');
    $teacherA = editHelperTeacher($subjectA, $pri);

    $subjectB = editHelperSubject('1AP', 'primaire');
    $teacherB = editHelperTeacher($subjectB, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $oldEnrollment = editHelperEnroll($student, $subjectA, $teacherA);

    $groupB = editHelperGroup($subjectB, $teacherB, '1AP');

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $oldEnrollment->id,
                    'subject_id' => $subjectB->id,
                    'teacher_id' => $teacherB->id,
                    'group_id' => $groupB->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $oldEnrollment->refresh();

    expect($oldEnrollment->status)->toBe('active')
        ->and($oldEnrollment->subject_id)->toBe($subjectB->id)
        ->and($oldEnrollment->teacher_id)->toBe($teacherB->id)
        ->and($oldEnrollment->group_id)->toBe($groupB->id)
        ->and($student->enrollments()->where('status', 'active')->count())->toBe(1);
});

test('modifier l\'enseignant met à jour correctement l\'inscription', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');

    $subject = editHelperSubject('1AP', 'primaire');
    $teacherA = editHelperTeacher($subject, $pri);
    $teacherB = editHelperTeacher($subject, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $oldEnrollment = editHelperEnroll($student, $subject, $teacherA);

    $groupB = editHelperGroup($subject, $teacherB, '1AP');

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $oldEnrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacherB->id,
                    'group_id' => $groupB->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($oldEnrollment->refresh()->status)->toBe('active')
        ->and($oldEnrollment->subject_id)->toBe($subject->id)
        ->and($oldEnrollment->teacher_id)->toBe($teacherB->id)
        ->and($oldEnrollment->group_id)->toBe($groupB->id);
});

test('modifier une inscription met à jour l\'inscription', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');

    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollment = editHelperEnroll($student, $subject, $teacher);

    $group = editHelperGroup($subject, $teacher, '1AP');

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($enrollment->refresh()->status)->toBe('active');
});

test('une inscription historique n\'est pas supprimée physiquement', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');

    $subjectA = editHelperSubject('1AP', 'primaire');
    $teacherA = editHelperTeacher($subjectA, $pri);

    $subjectB = editHelperSubject('1AP', 'primaire');
    $teacherB = editHelperTeacher($subjectB, $pri);

    $subjectC = editHelperSubject('1AP', 'primaire');
    $teacherC = editHelperTeacher($subjectC, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $historical = editHelperEnroll($student, $subjectA, $teacherA);
    $removed = editHelperEnroll($student, $subjectB, $teacherB);
    $kept = editHelperEnroll($student, $subjectC, $teacherC);

    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subjectA->id,
        'payment_type' => 'monthly',
        'payment_date' => now(),
    ]);

    $groupC = editHelperGroup($subjectC, $teacherC, '1AP');

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $kept->id,
                    'subject_id' => $subjectC->id,
                    'teacher_id' => $teacherC->id,
                    'group_id' => $groupC->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    // Inscription avec historique → désactivée, jamais supprimée
    $this->assertDatabaseHas('enrollments', ['id' => $historical->id]);

    expect($historical->refresh()->status)->toBe('inactive');

    // Inscription sans historique → supprimée
    $this->assertDatabaseMissing('enrollments', ['id' => $removed->id]);

    // Inscription conservée → active
    expect($kept->refresh()->status)->toBe('active');
});

test('les règles d\'incompatibilité restent bloquantes quand la nouvelle configuration est incompatible', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');

    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollment = editHelperEnroll($student, $subject, $teacher);

    $group = editHelperGroup($subject, $teacher, '2AM');

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'level' => '2AM',
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group->id,
                ],
            ],
        ]))
        ->assertSessionHasErrors('enrollments.0.subject_id');

    expect($student->refresh()->level)->toBe('1AP')
        ->and($enrollment->refresh()->status)->toBe('active');
});

test('la transaction empêche un état partiellement modifié en cas d\'erreur', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');

    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $student = Student::factory()->create([
        'first_name' => 'Ahmed',
        'last_name' => 'Benali',
        'level' => '1AP',
        'date_of_birth' => '2012-05-10',
    ]);

    $boomSubjectId = $subject->id;

    Event::listen('eloquent.creating: App\Models\Enrollment', function ($enrollment) use ($boomSubjectId) {
        if ((int) $enrollment->subject_id === (int) $boomSubjectId) {
            throw new RuntimeException('boom');
        }
    });

    $group = editHelperGroup($subject, $teacher, '1AP');

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'first_name' => 'NouveauNom',
            'enrollments' => [
                [
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group->id,
                ],
            ],
        ]))
        ->assertServerError();

    expect($student->refresh()->first_name)->toBe('Ahmed')
        ->and(Enrollment::count())->toBe(0);
});

// =====================================================
// TESTS DE RÉGRESSION — Modification groupe / enrollment
// =====================================================

test('changer de groupe seul met à jour le group_id de l\'inscription', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');

    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollment = editHelperEnroll($student, $subject, $teacher);

    $groupA = editHelperGroup($subject, $teacher, '1AP', 'normal');
    $groupB = editHelperGroup($subject, $teacher, '1AP', 'special');

    $enrollment->update(['group_id' => $groupA->id]);

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $groupB->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($enrollment->refresh()->group_id)->toBe($groupB->id);
});

test('changer de groupe avec nouvel enseignant désactive l\'ancienne inscription et en crée une nouvelle', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');

    $subject = editHelperSubject('1AP', 'primaire');
    $teacherA = editHelperTeacher($subject, $pri);
    $teacherB = editHelperTeacher($subject, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollment = editHelperEnroll($student, $subject, $teacherA);

    $groupA = editHelperGroup($subject, $teacherA, '1AP', 'normal');
    $groupB = editHelperGroup($subject, $teacherB, '1AP', 'special');

    $enrollment->update(['group_id' => $groupA->id]);

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacherB->id,
                    'group_id' => $groupB->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($enrollment->refresh()->status)->toBe('active')
        ->and($enrollment->group_id)->toBe($groupB->id)
        ->and($enrollment->teacher_id)->toBe($teacherB->id);
});

test('changer matière + groupe désactive l\'ancienne inscription et crée une nouvelle avec les bons IDs', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');

    $subjectA = editHelperSubject('1AP', 'primaire');
    $teacherA = editHelperTeacher($subjectA, $pri);

    $subjectB = editHelperSubject('1AP', 'primaire');
    $teacherB = editHelperTeacher($subjectB, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $oldEnrollment = editHelperEnroll($student, $subjectA, $teacherA);

    $groupA = editHelperGroup($subjectA, $teacherA, '1AP');
    $oldEnrollment->update(['group_id' => $groupA->id]);

    $groupB = editHelperGroup($subjectB, $teacherB, '1AP');

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $oldEnrollment->id,
                    'subject_id' => $subjectB->id,
                    'teacher_id' => $teacherB->id,
                    'group_id' => $groupB->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $oldEnrollment->refresh();

    expect($oldEnrollment->status)->toBe('active')
        ->and($oldEnrollment->subject_id)->toBe($subjectB->id)
        ->and($oldEnrollment->teacher_id)->toBe($teacherB->id)
        ->and($oldEnrollment->group_id)->toBe($groupB->id)
        ->and($student->enrollments()->where('status', 'active')->count())->toBe(1);
});

test('le group_id est pré-rempli dans le formulaire d\'édition', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollment = editHelperEnroll($student, $subject, $teacher);
    $group = editHelperGroup($subject, $teacher, '1AP');
    $enrollment->update(['group_id' => $group->id]);

    $response = $this->actingAs($user)
        ->get(route('students.edit', $student))
        ->assertOk();

    $content = $response->getContent();

    expect($content)->toContain('"group_id":'.$group->id);
});

test('les autres inscriptions ne sont pas modifiées quand on en change une seule', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');

    $subjectA = editHelperSubject('1AP', 'primaire');
    $teacherA = editHelperTeacher($subjectA, $pri);

    $subjectB = editHelperSubject('1AP', 'primaire');
    $teacherB = editHelperTeacher($subjectB, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollA = editHelperEnroll($student, $subjectA, $teacherA);
    $enrollB = editHelperEnroll($student, $subjectB, $teacherB);

    $groupA = editHelperGroup($subjectA, $teacherA, '1AP');
    $groupB = editHelperGroup($subjectB, $teacherB, '1AP');

    $enrollA->update(['group_id' => $groupA->id]);
    $enrollB->update(['group_id' => $groupB->id]);

    $newGroupA = editHelperGroup($subjectA, $teacherA, '1AP', 'special');

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enrollA->id,
                    'subject_id' => $subjectA->id,
                    'teacher_id' => $teacherA->id,
                    'group_id' => $newGroupA->id,
                ],
                [
                    'id' => $enrollB->id,
                    'subject_id' => $subjectB->id,
                    'teacher_id' => $teacherB->id,
                    'group_id' => $groupB->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($enrollB->refresh()->status)->toBe('active')
        ->and($enrollB->group_id)->toBe($groupB->id);
});

test('retirer une inscription avec historique la désactive sans la supprimer', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');

    $subjectA = editHelperSubject('1AP', 'primaire');
    $teacherA = editHelperTeacher($subjectA, $pri);

    $subjectB = editHelperSubject('1AP', 'primaire');
    $teacherB = editHelperTeacher($subjectB, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollA = editHelperEnroll($student, $subjectA, $teacherA);
    $enrollB = editHelperEnroll($student, $subjectB, $teacherB);

    $groupA = editHelperGroup($subjectA, $teacherA, '1AP');
    $enrollA->update(['group_id' => $groupA->id]);

    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subjectB->id,
        'payment_type' => 'monthly',
        'payment_date' => now(),
    ]);

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enrollA->id,
                    'subject_id' => $subjectA->id,
                    'teacher_id' => $teacherA->id,
                    'group_id' => $groupA->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($enrollB->refresh()->status)->toBe('inactive');
});

test('group_id est défini sur une nouvelle inscription ajoutée via le formulaire', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');

    $subjectA = editHelperSubject('1AP', 'primaire');
    $teacherA = editHelperTeacher($subjectA, $pri);

    $subjectB = editHelperSubject('1AP', 'primaire');
    $teacherB = editHelperTeacher($subjectB, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollA = editHelperEnroll($student, $subjectA, $teacherA);
    $groupA = editHelperGroup($subjectA, $teacherA, '1AP');
    $enrollA->update(['group_id' => $groupA->id]);

    $groupB = editHelperGroup($subjectB, $teacherB, '1AP');

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enrollA->id,
                    'subject_id' => $subjectA->id,
                    'teacher_id' => $teacherA->id,
                    'group_id' => $groupA->id,
                ],
                [
                    'subject_id' => $subjectB->id,
                    'teacher_id' => $teacherB->id,
                    'group_id' => $groupB->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $newEnrollment = $student->enrollments()
        ->where('subject_id', $subjectB->id)
        ->where('status', 'active')
        ->first();

    expect($newEnrollment)->not->toBeNull()
        ->and($newEnrollment->group_id)->toBe($groupB->id)
        ->and($newEnrollment->teacher_id)->toBe($teacherB->id);
});

test('erreur de validation retourne au formulaire sans modification', function () {
    $user = User::factory()->create();

    $student = Student::factory()->create([
        'first_name' => 'Ahmed',
        'level' => '1AP',
    ]);

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'first_name' => '',
        ]))
        ->assertRedirect()
        ->assertSessionHasErrors('first_name');

    expect($student->refresh()->first_name)->toBe('Ahmed');
});

test('les pivot student_group sont maintenus après modification d\'une inscription', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');

    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollment = editHelperEnroll($student, $subject, $teacher);
    $group = editHelperGroup($subject, $teacher, '1AP');
    $enrollment->update(['group_id' => $group->id]);

    $student->groups()->attach($group->id, [
        'joined_at' => now()->toDateString(),
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($student->groups()->where('groups.id', $group->id)->wherePivot('is_active', true)->exists())->toBeTrue();
});

test('la page d\'édition contient le group_id de l\'inscription dans le JSON pré-rempli', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollment = editHelperEnroll($student, $subject, $teacher);
    $group = editHelperGroup($subject, $teacher, '1AP');
    $enrollment->update(['group_id' => $group->id]);

    $response = $this->actingAs($user)
        ->get(route('students.edit', $student))
        ->assertOk();

    $content = $response->getContent();

    expect($content)->toContain('"group_id":'.$group->id);
});

test('modifier uniquement les infos personnelles préserve les inscriptions existantes', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $student = Student::factory()->create([
        'first_name' => 'Ahmed',
        'level' => '1AP',
    ]);

    $enrollment = editHelperEnroll($student, $subject, $teacher);
    $group = editHelperGroup($subject, $teacher, '1AP');
    $enrollment->update(['group_id' => $group->id]);

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'first_name' => 'Mohamed',
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($student->refresh()->first_name)->toBe('Mohamed')
        ->and($enrollment->refresh()->status)->toBe('active')
        ->and($enrollment->group_id)->toBe($group->id);
});

// =====================================================
// TESTS D'UNICITÉ ENROLLMENT (unique student_id + group_id)
// =====================================================

test('un meme groupe ne peut pas avoir deux enrollments actifs pour le meme eleve', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);
    $group = editHelperGroup($subject, $teacher, '1AP');

    $student = Student::factory()->create(['level' => '1AP']);

    // Premier enrollment dans le groupe
    $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'status' => 'active',
    ]);

    // Tenter un deuxième enrollment dans le même groupe → doit échouer
    $response = $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group->id,
                ],
            ],
        ]));

    // La contrainte DB ou la validation doit rejeter
    $response->assertSessionHasErrors('enrollments');
});

test('deux enrollments meme matiere meme prof groupes differents est autorise', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $group1 = editHelperGroup($subject, $teacher, '1AP');
    $group2 = editHelperGroup($subject, $teacher, '1AP');

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollment1 = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group1->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enrollment1->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group1->id,
                ],
                [
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group2->id,
                ],
            ],
        ]));

    $response->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('enrollments', [
        'student_id' => $student->id,
        'group_id' => $group1->id,
        'status' => 'active',
    ]);
    $this->assertDatabaseHas('enrollments', [
        'student_id' => $student->id,
        'group_id' => $group2->id,
        'status' => 'active',
    ]);
});

test('changement de groupe sur un enrollment existant est accepte', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $group1 = editHelperGroup($subject, $teacher, '1AP');
    $group2 = editHelperGroup($subject, $teacher, '1AP');

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollment = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group1->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group2->id,
                ],
            ],
        ]));

    $response->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($enrollment->refresh()->group_id)->toBe($group2->id);
});

test('suppression du groupe sur un enrollment (group_id null) est autorise', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $group1 = editHelperGroup($subject, $teacher, '1AP');

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollment = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group1->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => null,
                ],
            ],
        ]));

    $response->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($enrollment->refresh()->group_id)->toBeNull();
});

test('ajout enrollment meme groupe autre matiere echoue', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');
    $subject1 = editHelperSubject('1AP', 'primaire');
    $subject2 = editHelperSubject('2AP', 'primaire');
    $teacher = editHelperTeacher($subject1, $pri);
    $subject2->teachers()->attach($teacher);

    // Un seul groupe pour subject1
    $group = editHelperGroup($subject1, $teacher, '1AP');

    $student = Student::factory()->create(['level' => '1AP']);

    // Premier enrollment dans ce groupe
    $student->enrollments()->create([
        'subject_id' => $subject1->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'status' => 'active',
    ]);

    // Tenter un deuxième enrollment dans le même groupe avec subject2 → échoue
    // (car le groupe appartient à subject1, pas subject2)
    $response = $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'subject_id' => $subject1->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group->id,
                ],
                [
                    'subject_id' => $subject2->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group->id,
                ],
            ],
        ]));

    // Doit échouer : doublon groupe OU groupe incompatible avec la matière
    $response->assertSessionHasErrors();
});

test('syncEnrollments detecte le doublon meme groupe meme si prof et matiere changent', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $group1 = editHelperGroup($subject, $teacher, '1AP');
    $group2 = editHelperGroup($subject, $teacher, '1AP');

    $student = Student::factory()->create(['level' => '1AP']);

    $enrollment1 = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group1->id,
        'status' => 'active',
    ]);

    $enrollment2 = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group2->id,
        'status' => 'active',
    ]);

    // Essayer de mettre enrollment1 dans le même groupe que enrollment2 → échoue
    $response = $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enrollment1->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group2->id,
                ],
                [
                    'id' => $enrollment2->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group2->id,
                ],
            ],
        ]));

    $response->assertSessionHasErrors('enrollments');
});

// =====================================================
// TESTS — Modification infos personnelles préserve groupes
// =====================================================

test('modifier uniquement le phone préserve le group_id de l\'inscription', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $student = Student::factory()->create([
        'phone' => '0600000000',
        'level' => '1AP',
    ]);

    $enrollment = editHelperEnroll($student, $subject, $teacher);
    $group = editHelperGroup($subject, $teacher, '1AP');
    $enrollment->update(['group_id' => $group->id]);

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'phone' => '0699999999',
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($student->refresh()->phone)->toBe('0699999999')
        ->and($enrollment->refresh()->group_id)->toBe($group->id);
});

test('modifier le phone sans group_id soumis préserve le group_id existant', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $student = Student::factory()->create([
        'phone' => '0600000000',
        'level' => '1AP',
    ]);

    $enrollment = editHelperEnroll($student, $subject, $teacher);
    $group = editHelperGroup($subject, $teacher, '1AP');
    $enrollment->update(['group_id' => $group->id]);

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'phone' => '0699999999',
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($student->refresh()->phone)->toBe('0699999999')
        ->and($enrollment->refresh()->group_id)->toBe($group->id);
});

test('modifier le phone sans group_id soumis préserve les associations student_group', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $student = Student::factory()->create([
        'phone' => '0600000000',
        'level' => '1AP',
    ]);

    $enrollment = editHelperEnroll($student, $subject, $teacher);
    $group = editHelperGroup($subject, $teacher, '1AP');
    $enrollment->update(['group_id' => $group->id]);

    $student->groups()->attach($group->id, [
        'joined_at' => now()->toDateString(),
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'phone' => '0699999999',
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($student->refresh()->phone)->toBe('0699999999');

    expect($student->groups()
        ->where('groups.id', $group->id)
        ->wherePivot('is_active', true)
        ->exists())->toBeTrue();
});

test('changer le phone sans inscriptions soumises préserve les groupes existants', function () {
    $user = User::factory()->create();

    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $student = Student::factory()->create([
        'phone' => '0600000000',
        'level' => '1AP',
    ]);

    $enrollment = editHelperEnroll($student, $subject, $teacher);
    $group = editHelperGroup($subject, $teacher, '1AP');
    $enrollment->update(['group_id' => $group->id]);

    $student->groups()->attach($group->id, [
        'joined_at' => now()->toDateString(),
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'phone' => '0699999999',
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($student->refresh()->phone)->toBe('0699999999')
        ->and($enrollment->refresh()->group_id)->toBe($group->id);

    expect($student->groups()
        ->where('groups.id', $group->id)
        ->wherePivot('is_active', true)
        ->exists())->toBeTrue();
});

// =====================================================
// TESTS — Réactivation / swap / historique / payment_type
// =====================================================

test('reactivation dun enrollment inactif via nouvelle ligne sans ID', function () {
    $user = User::factory()->create();
    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);
    $group = editHelperGroup($subject, $teacher, '1AP');
    $student = Student::factory()->create(['level' => '1AP']);

    $inactive = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'status' => 'inactive',
        'payment_type' => 'monthly',
    ]);

    $response = $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group->id,
                ],
            ],
        ]));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(Enrollment::where('student_id', $student->id)->count())->toBe(1);

    $inactive->refresh();
    expect($inactive->status)->toBe('active')
        ->and($inactive->subject_id)->toBe($subject->id)
        ->and($inactive->teacher_id)->toBe($teacher->id);
});

test('un enrollment actif empeche la creation dun doublon meme groupe', function () {
    $user = User::factory()->create();
    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);
    $group = editHelperGroup($subject, $teacher, '1AP');
    $student = Student::factory()->create(['level' => '1AP']);

    $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group->id,
                ],
            ],
        ]));

    $response->assertSessionHasErrors('enrollments');
    expect(Enrollment::where('student_id', $student->id)->count())->toBe(1);
});

test('changer le groupe vers un groupe occupe par un inactif genere une erreur sans swap', function () {
    $user = User::factory()->create();
    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);
    $group1 = editHelperGroup($subject, $teacher, '1AP');
    $group2 = editHelperGroup($subject, $teacher, '1AP');
    $student = Student::factory()->create(['level' => '1AP']);

    $enroll1 = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group1->id,
        'status' => 'active',
    ]);
    $enroll2 = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group2->id,
        'status' => 'inactive',
    ]);

    $response = $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enroll1->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group2->id,
                ],
            ],
        ]));

    $response->assertSessionHasErrors('enrollments');

    $enroll1->refresh();
    expect($enroll1->group_id)->toBe($group1->id)
        ->and($enroll1->status)->toBe('active');

    $enroll2->refresh();
    expect($enroll2->group_id)->toBe($group2->id)
        ->and($enroll2->status)->toBe('inactive');

    expect(Enrollment::where('student_id', $student->id)->count())->toBe(2);
});

test('les enrollment IDs existants ne sont pas supprimes et recrees', function () {
    $user = User::factory()->create();
    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);
    $group = editHelperGroup($subject, $teacher, '1AP');
    $student = Student::factory()->create(['level' => '1AP']);

    $enrollment = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'status' => 'active',
    ]);
    $originalId = $enrollment->id;

    $response = $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group->id,
                ],
            ],
        ]));

    $response->assertRedirect();
    expect(Enrollment::where('student_id', $student->id)->count())->toBe(1);
    expect(Enrollment::where('student_id', $student->id)->first()->id)->toBe($originalId);
});

test('deux enrollments meme prof meme matiere groupes differents est autorise', function () {
    $user = User::factory()->create();
    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);
    $group1 = editHelperGroup($subject, $teacher, '1AP');
    $group2 = editHelperGroup($subject, $teacher, '1AP');
    $student = Student::factory()->create(['level' => '1AP']);

    $enroll1 = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group1->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enroll1->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group1->id,
                ],
                [
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group2->id,
                ],
            ],
        ]));

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect(Enrollment::where('student_id', $student->id)->count())->toBe(2);
});

test('une inscription avec historique ne peut pas changer de groupe', function () {
    $user = User::factory()->create();
    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);
    $groupA = editHelperGroup($subject, $teacher, '1AP');
    $groupB = editHelperGroup($subject, $teacher, '1AP');
    $student = Student::factory()->create(['level' => '1AP']);

    $enroll = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $groupA->id,
        'status' => 'active',
    ]);

    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'payment_type' => 'monthly',
        'payment_date' => now(),
    ]);

    $response = $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enroll->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $groupB->id,
                ],
            ],
        ]));

    $response->assertSessionHasErrors('enrollments');

    $enroll->refresh();
    expect($enroll->group_id)->toBe($groupA->id)
        ->and($enroll->status)->toBe('active');

    expect(Enrollment::where('student_id', $student->id)->count())->toBe(1);
});

test('une inscription sans historique peut changer de groupe', function () {
    $user = User::factory()->create();
    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);
    $groupA = editHelperGroup($subject, $teacher, '1AP');
    $groupB = editHelperGroup($subject, $teacher, '1AP');
    $student = Student::factory()->create(['level' => '1AP']);

    $enroll = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $groupA->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'enrollments' => [
                [
                    'id' => $enroll->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $groupB->id,
                ],
            ],
        ]));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $enroll->refresh();
    expect($enroll->group_id)->toBe($groupB->id)
        ->and($enroll->status)->toBe('active');

    expect(Enrollment::where('student_id', $student->id)->count())->toBe(1);
});

// =====================================================
// TESTS — Changement mot de passe du compte étudiant
// =====================================================

test('changer le mot de passe du compte étudiant fonctionne', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $user = User::factory()->create(['role' => 'student', 'password' => Hash::make('OldPassword1')]);
    $student = Student::factory()->create(['level' => '1AP']);
    $student->update(['user_id' => $user->id]);

    $this->actingAs($admin)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $user->refresh();

    expect(Hash::check('NewPassword123', $user->password))->toBeTrue()
        ->and(Hash::check('OldPassword1', $user->password))->toBeFalse()
        ->and($user->id)->toBe($user->id)
        ->and($student->refresh()->user_id)->toBe($user->id)
        ->and($user->email)->not->toBeEmpty()
        ->and($user->role)->toBe('student');
});

test('password vide ne modifie pas le mot de passe existant', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $originalHash = Hash::make('ExistingPassword1');
    $user = User::factory()->create(['role' => 'student', 'password' => $originalHash]);
    $student = Student::factory()->create(['level' => '1AP']);
    $student->update(['user_id' => $user->id]);

    $this->actingAs($admin)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'first_name' => 'NouveauPrenom',
            'password' => '',
            'password_confirmation' => '',
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $user->refresh();

    expect($user->password)->toBe($originalHash)
        ->and($student->refresh()->first_name)->toBe('NouveauPrenom');
});

test('confirmation incorrecte échoue', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $user = User::factory()->create(['role' => 'student', 'password' => Hash::make('OldPassword1')]);
    $student = Student::factory()->create(['level' => '1AP']);
    $student->update(['user_id' => $user->id]);

    $this->actingAs($admin)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'password' => 'NewPassword123',
            'password_confirmation' => 'DifferentPassword123',
        ]))
        ->assertRedirect()
        ->assertSessionHasErrors('password_confirmation');

    $user->refresh();

    expect(Hash::check('OldPassword1', $user->password))->toBeTrue();
});

test('password inférieur à 8 caractères échoue', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $user = User::factory()->create(['role' => 'student', 'password' => Hash::make('OldPassword1')]);
    $student = Student::factory()->create(['level' => '1AP']);
    $student->update(['user_id' => $user->id]);

    $this->actingAs($admin)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]))
        ->assertRedirect()
        ->assertSessionHasErrors('password');

    $user->refresh();

    expect(Hash::check('OldPassword1', $user->password))->toBeTrue();
});

test('étudiant sans compte ne peut pas recevoir de mot de passe', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $student = Student::factory()->create(['level' => '1AP']);

    expect($student->user)->toBeNull();

    $this->actingAs($admin)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]))
        ->assertRedirect()
        ->assertSessionHasErrors('password');

    $student->refresh();

    expect($student->user_id)->toBeNull();
    expect(User::where('role', 'student')->count())->toBe(0);
});

test('changer le mot de passe ne modifie pas les inscriptions', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $pri = editHelperLevel('PRI', 'Primaire');
    $subject = editHelperSubject('1AP', 'primaire');
    $teacher = editHelperTeacher($subject, $pri);

    $user = User::factory()->create(['role' => 'student', 'password' => Hash::make('OldPassword1')]);
    $student = Student::factory()->create(['level' => '1AP']);
    $student->update(['user_id' => $user->id]);

    $enrollment = editHelperEnroll($student, $subject, $teacher);
    $group = editHelperGroup($subject, $teacher, '1AP');
    $enrollment->update(['group_id' => $group->id]);

    $this->actingAs($admin)
        ->put(route('students.update', $student), editHelperPayload($student, [
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'group_id' => $group->id,
                ],
            ],
        ]))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $user->refresh();

    expect(Hash::check('NewPassword123', $user->password))->toBeTrue()
        ->and($enrollment->refresh()->status)->toBe('active')
        ->and($enrollment->group_id)->toBe($group->id);
});
