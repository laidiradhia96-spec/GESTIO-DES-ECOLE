<?php

use App\Models\Level;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

test('an authenticated user can create a student with enrollments', function () {
    $user = User::factory()->create();

    $level = Level::create([
        'name' => 'Primaire',
        'code' => 'PRI',
        'active' => true,
    ]);

    $subject = Subject::factory()->create([
        'level' => '1AP',
        'primaire' => true,
    ]);

    $teacher = Teacher::factory()->create();
    $teacher->levels()->attach($level);
    $subject->teachers()->attach($teacher);

    $response = $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Ahmed',
        'last_name' => 'Benali',
        'date_of_birth' => '2012-05-10',
        'phone' => '0550 00 00 00',
        'address' => 'Alger',
        'level' => '1AP',
        'parent_name' => 'Mohamed Benali',
        'parent_phone' => '0550 11 11 11',
        'enrollments' => [
            [
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'payment_type' => 'monthly',
            ],
        ],
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.index'));

    $student = Student::where('first_name', 'Ahmed')->first();

    expect($student)->not->toBeNull()
        ->and($student->level)->toBe('1AP')
        ->and($student->address)->toBe('Alger')
        ->and($student->enrollments)->toHaveCount(1)
        ->and($student->enrollments->first()->teacher_id)->toBe($teacher->id);

    $this->assertDatabaseHas('enrollments', [
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'payment_type' => 'monthly',
        'status' => 'active',
    ]);
});

test('student creation is rejected when the teacher does not match the subject', function () {
    $user = User::factory()->create();

    $subject = Subject::factory()->create([
        'level' => '1AP',
        'primaire' => true,
    ]);

    $otherSubject = Subject::factory()->create([
        'level' => '1AP',
        'primaire' => true,
    ]);

    $teacher = Teacher::factory()->create();
    $otherSubject->teachers()->attach($teacher);

    $this->actingAs($user)
        ->post(route('students.store'), [
            'first_name' => 'Ahmed',
            'last_name' => 'Benali',
            'level' => '1AP',
            'enrollments' => [
                [
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'payment_type' => 'monthly',
                ],
            ],
        ])
        ->assertSessionHasErrors('enrollments.0.teacher_id');

    expect(Student::count())->toBe(0);
});

test('student creation is rejected when the subject does not match the level', function () {
    $user = User::factory()->create();

    $subject = Subject::factory()->create([
        'level' => '2AM',
        'moyen' => true,
    ]);
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);

    $this->actingAs($user)
        ->post(route('students.store'), [
            'first_name' => 'Ahmed',
            'last_name' => 'Benali',
            'level' => '1AP',
            'enrollments' => [
                [
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'payment_type' => 'vip',
                ],
            ],
        ])
        ->assertSessionHasErrors('enrollments.0.subject_id');

    expect(Student::count())->toBe(0);
});
