<?php

use App\Models\Enrollment;
use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\PaymentSignalement;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class C2PaymentSignalementGroupIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    private function createSubject(): Subject
    {
        return Subject::factory()->create(['active' => true]);
    }

    private function createTeacher(): Teacher
    {
        return Teacher::factory()->create(['active' => true]);
    }

    private function createGroup(
        string $name,
        int $subjectId,
        int $teacherId,
        int $schoolYearId,
        string $mode,
        string $billingType,
        float $price,
    ): Group {
        $group = Group::create([
            'name' => $name,
            'subject_id' => $subjectId,
            'teacher_id' => $teacherId,
            'level' => '3eme',
            'school_year_id' => $schoolYearId,
            'mode' => $mode,
            'is_active' => true,
        ]);

        GroupTariff::create([
            'group_id' => $group->id,
            'student_price' => $price,
            'teacher_share' => $price * 0.6,
            'academy_share' => $price * 0.4,
            'billing_type' => $billingType,
            'is_active' => true,
            'effective_from' => now()->subMonth(),
        ]);

        return $group;
    }

    private function createEnrollment(
        Student $student,
        int $subjectId,
        int $teacherId,
        int $groupId,
        int $schoolYearId,
    ): Enrollment {
        return $student->enrollments()->create([
            'subject_id' => $subjectId,
            'teacher_id' => $teacherId,
            'group_id' => $groupId,
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'school_year_id' => $schoolYearId,
        ]);
    }

    // =============================================================
    // TEST 1 : Cross-group isolation (different school years)
    //
    // Student A — Math
    //   Teacher Ahmed → Group Normal  (2025-2026, monthly, 3000 DA)
    //   Teacher Youssef → Group VIP   (2026-2027, per_session, 500 DA)
    //
    // Create a debt signalement for Group VIP (September 2026).
    // Verify Group Normal (September 2025) is untouched.
    //
    // NOTE: Enrollment unique constraint is (student_id, subject_id,
    // teacher_id) — so different teachers are required for different
    // groups with the same student+subject.
    // =============================================================

    public function test_cross_group_signalement_preserves_group_id(): void
    {
        $admin = $this->createAdmin();
        $student = Student::factory()->create();
        $subject = $this->createSubject();
        $teacherNormal = $this->createTeacher();
        $teacherVip = $this->createTeacher();

        // --- School year 2025-2026 + Group Normal ---
        $year2025 = SchoolYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_current' => false,
        ]);

        $groupNormal = $this->createGroup(
            'Math Normal',
            $subject->id,
            $teacherNormal->id,
            $year2025->id,
            'normal',
            'monthly',
            3000.00,
        );

        $this->createEnrollment($student, $subject->id, $teacherNormal->id, $groupNormal->id, $year2025->id);

        // --- School year 2026-2027 + Group VIP ---
        $year2026 = SchoolYear::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'is_current' => true,
        ]);

        $groupVip = $this->createGroup(
            'Math VIP',
            $subject->id,
            $teacherVip->id,
            $year2026->id,
            'vip',
            'per_session',
            500.00,
        );

        $this->createEnrollment($student, $subject->id, $teacherVip->id, $groupVip->id, $year2026->id);

        // --- Create a legacy signalement for Group Normal (September 2025) ---
        $signalementNormal = PaymentSignalement::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'group_id' => $groupNormal->id,
            'period' => 'Septembre 2025',
            'amount_remaining' => 3000.00,
            'status' => 'pending',
            'signalement_date' => '2025-09-30',
            'note' => 'Dette Normal.',
            'school_year_id' => $year2025->id,
        ]);

        // --- Act as admin : mark as sent via Group VIP + September 2026 ---
        $this->actingAs($admin);

        $response = $this->patch(route('payment-signalements.sent'), [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'group_id' => $groupVip->id,
            'period' => '2026-09',
            'amount_remaining' => 500.00,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // --- Verify the new signalement has Group VIP ---
        $signalementVip = PaymentSignalement::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('group_id', $groupVip->id)
            ->where('period', '2026-09')
            ->first();

        $this->assertNotNull($signalementVip, 'Group VIP signalement must exist');
        $this->assertEquals($groupVip->id, $signalementVip->group_id);
        $this->assertEquals($year2026->id, $signalementVip->school_year_id);
        $this->assertEquals('sent', $signalementVip->status);

        // --- Verify Group Normal signalement is UNTOUCHED ---
        $signalementNormal->refresh();
        $this->assertEquals($groupNormal->id, $signalementNormal->group_id);
        $this->assertEquals('pending', $signalementNormal->status, 'Group Normal signalement must remain pending');
        $this->assertEquals(3000.00, (float) $signalementNormal->amount_remaining);
    }

    // =============================================================
    // TEST 2 : Same-year isolation (different teachers for same subject)
    //
    // Student A — Math — School year 2025-2026
    //   Teacher Ahmed  → Group Normal  (monthly, 3000 DA)
    //   Teacher Youssef → Group VIP    (per_session, 500 DA)
    //
    // Create a debt for Group Normal, then resolve Group VIP.
    // Verify Group Normal remains pending.
    // =============================================================

    public function test_same_year_group_vip_does_not_resolve_normal(): void
    {
        $admin = $this->createAdmin();
        $student = Student::factory()->create();
        $subject = $this->createSubject();
        $teacherNormal = $this->createTeacher();
        $teacherVip = $this->createTeacher();

        $schoolYear = SchoolYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_current' => true,
        ]);

        $groupNormal = $this->createGroup(
            'Math Normal',
            $subject->id,
            $teacherNormal->id,
            $schoolYear->id,
            'normal',
            'monthly',
            3000.00,
        );

        $groupVip = $this->createGroup(
            'Math VIP',
            $subject->id,
            $teacherVip->id,
            $schoolYear->id,
            'vip',
            'per_session',
            500.00,
        );

        $this->createEnrollment($student, $subject->id, $teacherNormal->id, $groupNormal->id, $schoolYear->id);
        $this->createEnrollment($student, $subject->id, $teacherVip->id, $groupVip->id, $schoolYear->id);

        // --- Create pending signalement for Group Normal (September) ---
        $signalementNormal = PaymentSignalement::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'group_id' => $groupNormal->id,
            'period' => '2025-09',
            'amount_remaining' => 3000.00,
            'status' => 'pending',
            'signalement_date' => now()->toDateString(),
            'note' => 'Dette Normal.',
            'school_year_id' => $schoolYear->id,
        ]);

        $this->actingAs($admin);

        // --- Resolve Group VIP for September ---
        $response = $this->patch(route('payment-signalements.resolved'), [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'group_id' => $groupVip->id,
            'period' => '2025-09',
            'amount_remaining' => 500.00,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // --- Verify Group VIP signalement was created and resolved ---
        $signalementVip = PaymentSignalement::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('group_id', $groupVip->id)
            ->where('period', '2025-09')
            ->first();

        $this->assertNotNull($signalementVip, 'Group VIP signalement must exist');
        $this->assertEquals('resolved', $signalementVip->status);
        $this->assertEquals(0.00, (float) $signalementVip->amount_remaining);

        // --- Verify Group Normal signalement is UNTOUCHED ---
        $signalementNormal->refresh();
        $this->assertEquals($groupNormal->id, $signalementNormal->group_id);
        $this->assertEquals('pending', $signalementNormal->status, 'Group Normal signalement must remain pending');
        $this->assertEquals(3000.00, (float) $signalementNormal->amount_remaining);
    }

    // =============================================================
    // TEST 3 : Legacy group_id=NULL still works
    //
    // When group_id is not provided, the controller falls back to
    // the legacy behavior (search without group_id).
    // =============================================================

    public function test_legacy_null_group_id_still_works(): void
    {
        $admin = $this->createAdmin();
        $student = Student::factory()->create();
        $subject = $this->createSubject();
        $teacher = $this->createTeacher();

        $schoolYear = SchoolYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_current' => true,
        ]);

        // --- Create a legacy signalement without group_id ---
        $signalement = PaymentSignalement::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'group_id' => null,
            'period' => '2025-09',
            'amount_remaining' => 3000.00,
            'status' => 'pending',
            'signalement_date' => now()->toDateString(),
            'note' => 'Legacy debt.',
            'school_year_id' => $schoolYear->id,
        ]);

        $this->actingAs($admin);

        // --- Mark as sent WITHOUT group_id ---
        $response = $this->patch(route('payment-signalements.sent'), [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'period' => '2025-09',
            'amount_remaining' => 3000.00,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // --- Verify the legacy signalement was found and updated ---
        $signalement->refresh();
        $this->assertNull($signalement->group_id, 'Legacy signalement must keep group_id=NULL');
        $this->assertEquals('sent', $signalement->status);
    }

    // =============================================================
    // TEST 4 : group_id resolves school_year_id from Group
    //
    // When group_id is provided, school_year_id comes from the Group,
    // not from SchoolYear::forPeriod().
    // =============================================================

    public function test_group_id_resolves_school_year_from_group(): void
    {
        $admin = $this->createAdmin();
        $student = Student::factory()->create();
        $subject = $this->createSubject();
        $teacher = $this->createTeacher();

        $schoolYear = SchoolYear::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'is_current' => true,
        ]);

        $group = $this->createGroup(
            'Math VIP',
            $subject->id,
            $teacher->id,
            $schoolYear->id,
            'vip',
            'per_session',
            500.00,
        );

        $this->actingAs($admin);

        // --- Create a signalement with group_id ---
        $response = $this->patch(route('payment-signalements.sent'), [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'group_id' => $group->id,
            'period' => '2026-09',
            'amount_remaining' => 500.00,
        ]);

        $response->assertRedirect();

        $signalement = PaymentSignalement::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('group_id', $group->id)
            ->where('period', '2026-09')
            ->first();

        $this->assertNotNull($signalement);
        $this->assertEquals($schoolYear->id, $signalement->school_year_id, 'school_year_id must come from Group');
    }
}
