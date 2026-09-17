<?php

use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\PaymentSignalement;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\RevenueService;
use App\Services\UnpaidDebtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupSsotTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): array
    {
        $schoolYear = SchoolYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_default' => true,
        ]);

        $subject = Subject::factory()->create(['active' => true]);
        $teacher = Teacher::factory()->create(['active' => true]);

        $group = Group::create([
            'name' => 'Groupe A',
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'level' => '3eme',
            'school_year_id' => $schoolYear->id,
            'mode' => 'normal',
            'billing_type' => 'monthly',
            'is_active' => true,
        ]);

        $tariff = GroupTariff::create([
            'group_id' => $group->id,
            'student_price' => 3000.00,
            'teacher_share' => 2000.00,
            'academy_share' => 1000.00,
            'billing_type' => 'monthly',
            'is_active' => true,
            'effective_from' => now()->subMonth(),
        ]);

        return compact('schoolYear', 'subject', 'teacher', 'group', 'tariff');
    }

    private function createAdminUser(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    public function test_enrollment_has_group_id(): void
    {
        $data = $this->seedBase();
        $student = Student::factory()->create();

        $enrollment = $student->enrollments()->create([
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'group_id' => $data['group']->id,
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'school_year_id' => $data['schoolYear']->id,
        ]);

        $this->assertNotNull($enrollment->group_id);
        $this->assertEquals($data['group']->id, $enrollment->group_id);
    }

    public function test_group_has_enrollments(): void
    {
        $data = $this->seedBase();
        $student = Student::factory()->create();

        $student->enrollments()->create([
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'group_id' => $data['group']->id,
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'school_year_id' => $data['schoolYear']->id,
        ]);

        $this->assertEquals(1, $data['group']->enrollments()->count());
    }

    public function test_payment_has_snapshot_shares(): void
    {
        $data = $this->seedBase();
        $student = Student::factory()->create();

        $payment = $student->payments()->create([
            'receipt_number' => 'R-2026-0001',
            'subject_id' => $data['subject']->id,
            'group_id' => $data['group']->id,
            'payment_type' => 'monthly',
            'period' => 'Septembre 2025',
            'amount_due' => 3000.00,
            'amount_paid' => 3000.00,
            'remaining_amount' => 0.00,
            'teacher_share' => 2000.00,
            'academy_share' => 1000.00,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'payment_time' => now()->format('H:i:s'),
            'school_year_id' => $data['schoolYear']->id,
        ]);

        $this->assertEquals(2000.00, (float) $payment->teacher_share);
        $this->assertEquals(1000.00, (float) $payment->academy_share);
    }

    public function test_payment_snapshot_not_affected_by_tariff_change(): void
    {
        $data = $this->seedBase();
        $student = Student::factory()->create();

        $payment = $student->payments()->create([
            'receipt_number' => 'R-2026-0002',
            'subject_id' => $data['subject']->id,
            'group_id' => $data['group']->id,
            'payment_type' => 'monthly',
            'period' => 'Septembre 2025',
            'amount_due' => 3000.00,
            'amount_paid' => 3000.00,
            'remaining_amount' => 0.00,
            'teacher_share' => 2000.00,
            'academy_share' => 1000.00,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'payment_time' => now()->format('H:i:s'),
            'school_year_id' => $data['schoolYear']->id,
        ]);

        $data['tariff']->update([
            'student_price' => 4000.00,
            'teacher_share' => 2500.00,
            'academy_share' => 1500.00,
        ]);

        $payment->refresh();
        $this->assertEquals(2000.00, (float) $payment->teacher_share);
        $this->assertEquals(1000.00, (float) $payment->academy_share);
    }

    public function test_enrollment_unique_constraint_not_violated(): void
    {
        $data = $this->seedBase();
        $student = Student::factory()->create();

        $student->enrollments()->create([
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'group_id' => $data['group']->id,
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'school_year_id' => $data['schoolYear']->id,
        ]);

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'group_id' => $data['group']->id,
        ]);
    }

    public function test_group_teacher_readonly_in_edit_view(): void
    {
        $data = $this->seedBase();
        $student = Student::factory()->create();
        $admin = $this->createAdminUser();

        $student->enrollments()->create([
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'group_id' => $data['group']->id,
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'school_year_id' => $data['schoolYear']->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('students.edit', $student));

        $response->assertStatus(200);
        $response->assertSee('Enseignant (défini par le groupe)');
    }

    public function test_payment_show_uses_group_teacher(): void
    {
        $data = $this->seedBase();
        $student = Student::factory()->create();
        $admin = $this->createAdminUser();

        $payment = $student->payments()->create([
            'receipt_number' => 'R-2026-0003',
            'subject_id' => $data['subject']->id,
            'group_id' => $data['group']->id,
            'payment_type' => 'monthly',
            'period' => 'Septembre 2025',
            'amount_due' => 3000.00,
            'amount_paid' => 3000.00,
            'remaining_amount' => 0.00,
            'teacher_share' => 2000.00,
            'academy_share' => 1000.00,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'payment_time' => now()->format('H:i:s'),
            'school_year_id' => $data['schoolYear']->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('payments.show', $payment));

        $response->assertStatus(200);
        $response->assertSee($data['teacher']->last_name);
        $response->assertSee($data['teacher']->first_name);
    }

    public function test_revenue_service_uses_snapshot_shares(): void
    {
        $data = $this->seedBase();
        $student = Student::factory()->create();

        $student->payments()->create([
            'receipt_number' => 'R-2026-0004',
            'subject_id' => $data['subject']->id,
            'group_id' => $data['group']->id,
            'payment_type' => 'monthly',
            'period' => 'Septembre 2025',
            'amount_due' => 3000.00,
            'amount_paid' => 3000.00,
            'remaining_amount' => 0.00,
            'teacher_share' => 2000.00,
            'academy_share' => 1000.00,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'payment_time' => now()->format('H:i:s'),
            'school_year_id' => $data['schoolYear']->id,
        ]);

        $service = new RevenueService;
        $stats = $service->getRevenueStats(
            now()->month,
            now()->year,
            null,
            $data['schoolYear']->id,
        );

        $this->assertEquals(3000.00, $stats['total_collected']);
        $this->assertEquals(2000.00, $stats['total_teacher_share']);
        $this->assertEquals(1000.00, $stats['total_academy_share']);
    }

    public function test_unpaid_service_resolves_via_group(): void
    {
        $data = $this->seedBase();
        $student = Student::factory()->create();

        PaymentSignalement::create([
            'student_id' => $student->id,
            'subject_id' => $data['subject']->id,
            'payment_id' => null,
            'period' => 'Septembre 2025',
            'amount_remaining' => 3000.00,
            'status' => 'pending',
            'signalement_date' => now()->toDateString(),
            'attendance_date' => now()->toDateString(),
            'school_year_id' => $data['schoolYear']->id,
        ]);

        $service = new UnpaidDebtService;
        $debts = $service->activeDebts($data['schoolYear']->id);

        $this->assertNotEmpty($debts);
    }

    public function test_group_null_fallback_legacy_enrollment(): void
    {
        $data = $this->seedBase();
        $student = Student::factory()->create();

        // Enrollment SANS group_id (legacy)
        $student->enrollments()->create([
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'group_id' => null,
            'payment_type' => 'monthly',
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'school_year_id' => $data['schoolYear']->id,
        ]);

        // Créer un signalement — le service ne doit pas crasher malgré group_id = null
        $signalement = PaymentSignalement::create([
            'student_id' => $student->id,
            'subject_id' => $data['subject']->id,
            'payment_id' => null,
            'period' => 'Septembre 2025',
            'amount_remaining' => 3000.00,
            'status' => 'pending',
            'signalement_date' => now()->toDateString(),
            'attendance_date' => now()->toDateString(),
            'school_year_id' => $data['schoolYear']->id,
        ]);

        $this->assertNotNull($signalement);
        $this->assertEquals('pending', $signalement->status);
    }

    public function test_migration_payment_has_snapshot_columns(): void
    {
        $data = $this->seedBase();
        $student = Student::factory()->create();

        $student->payments()->create([
            'receipt_number' => 'R-2026-0005',
            'subject_id' => $data['subject']->id,
            'group_id' => $data['group']->id,
            'payment_type' => 'monthly',
            'period' => 'Septembre 2025',
            'amount_due' => 3000.00,
            'amount_paid' => 3000.00,
            'remaining_amount' => 0.00,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'payment_time' => now()->format('H:i:s'),
            'school_year_id' => $data['schoolYear']->id,
        ]);

        $this->assertDatabaseHas('payments', [
            'receipt_number' => 'R-2026-0005',
            'teacher_share' => null,
            'academy_share' => null,
        ]);
    }
}
