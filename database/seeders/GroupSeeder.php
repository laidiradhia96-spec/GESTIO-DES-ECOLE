<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\GroupSchedule;
use App\Models\GroupTariff;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Données de démonstration pour les groupes.
     *
     * Exécution manuelle :
     * php artisan db:seed --class=GroupSeeder
     */
    public function run(): void
    {
        // =====================================================
        // ENSEIGNANT : Ahmed — Mathématiques
        // =====================================================

        $ahmed = Teacher::firstOrCreate(
            ['first_name' => 'Ahmed', 'last_name' => 'Bouzid'],
            [
                'speciality' => 'Mathématiques',
                'phone' => '0550 10 20 30',
                'email' => 'ahmed.bouzid@email.com',
                'active' => true,
            ]
        );

        $math = Subject::firstOrCreate(
            ['code' => 'MATH'],
            [
                'name' => 'Mathématiques',
                'description' => 'Enseignement des mathématiques',
                'level' => 'all',
                'primaire' => true,
                'moyen' => true,
                'lycee' => true,
                'hours_per_week' => 4,
                'active' => true,
            ]
        );

        $year = SchoolYear::firstOrCreate(
            ['name' => '2026-2027'],
            [
                'start_date' => '2026-09-01',
                'end_date' => '2027-06-30',
                'is_current' => true,
            ]
        );

        // --- Groupes Ahmed / Math / 1AS ---

        $g1 = Group::create([
            'teacher_id' => $ahmed->id,
            'subject_id' => $math->id,
            'level' => '1AS',
            'school_year_id' => $year->id,
            'name' => 'Groupe 1',
            'mode' => 'normal',
            'is_active' => true,
        ]);

        GroupTariff::create([
            'group_id' => $g1->id,
            'billing_type' => 'monthly',
            'student_price' => 1500,
            'teacher_share' => 1000,
            'academy_share' => 500,
            'effective_from' => '2026-09-01',
            'is_active' => true,
        ]);

        GroupSchedule::create([
            'group_id' => $g1->id,
            'day' => 'Dimanche',
            'start_time' => '09:00',
            'end_time' => '10:30',
            'room' => 'Salle 01',
            'is_active' => true,
        ]);

        GroupSchedule::create([
            'group_id' => $g1->id,
            'day' => 'Mardi',
            'start_time' => '09:00',
            'end_time' => '10:30',
            'room' => 'Salle 01',
            'is_active' => true,
        ]);

        $g2 = Group::create([
            'teacher_id' => $ahmed->id,
            'subject_id' => $math->id,
            'level' => '1AS',
            'school_year_id' => $year->id,
            'name' => 'Groupe 2',
            'mode' => 'normal',
            'is_active' => true,
        ]);

        GroupTariff::create([
            'group_id' => $g2->id,
            'billing_type' => 'monthly',
            'student_price' => 1500,
            'teacher_share' => 1000,
            'academy_share' => 500,
            'effective_from' => '2026-09-01',
            'is_active' => true,
        ]);

        GroupSchedule::create([
            'group_id' => $g2->id,
            'day' => 'Dimanche',
            'start_time' => '11:00',
            'end_time' => '12:30',
            'room' => 'Salle 02',
            'is_active' => true,
        ]);

        // --- Groupes Ahmed / Math / 2AS ---

        $g3 = Group::create([
            'teacher_id' => $ahmed->id,
            'subject_id' => $math->id,
            'level' => '2AS',
            'school_year_id' => $year->id,
            'name' => 'Groupe 1',
            'mode' => 'normal',
            'is_active' => true,
        ]);

        GroupTariff::create([
            'group_id' => $g3->id,
            'billing_type' => 'monthly',
            'student_price' => 1800,
            'teacher_share' => 1200,
            'academy_share' => 600,
            'effective_from' => '2026-09-01',
            'is_active' => true,
        ]);

        GroupSchedule::create([
            'group_id' => $g3->id,
            'day' => 'Lundi',
            'start_time' => '14:00',
            'end_time' => '15:30',
            'room' => 'Salle 03',
            'is_active' => true,
        ]);

        // --- Groupes Ahmed / Math / 3AS ---

        $g4 = Group::create([
            'teacher_id' => $ahmed->id,
            'subject_id' => $math->id,
            'level' => '3AS',
            'school_year_id' => $year->id,
            'name' => 'Groupe Normal',
            'mode' => 'normal',
            'is_active' => true,
        ]);

        GroupTariff::create([
            'group_id' => $g4->id,
            'billing_type' => 'monthly',
            'student_price' => 2000,
            'teacher_share' => 1500,
            'academy_share' => 500,
            'effective_from' => '2026-09-01',
            'is_active' => true,
        ]);

        GroupSchedule::create([
            'group_id' => $g4->id,
            'day' => 'Dimanche',
            'start_time' => '14:00',
            'end_time' => '15:30',
            'room' => 'Salle 01',
            'is_active' => true,
        ]);

        GroupSchedule::create([
            'group_id' => $g4->id,
            'day' => 'Jeudi',
            'start_time' => '14:00',
            'end_time' => '15:30',
            'room' => 'Salle 01',
            'is_active' => true,
        ]);

        $g5 = Group::create([
            'teacher_id' => $ahmed->id,
            'subject_id' => $math->id,
            'level' => '3AS',
            'school_year_id' => $year->id,
            'name' => 'Groupe Spécial',
            'mode' => 'special',
            'is_active' => true,
        ]);

        GroupTariff::create([
            'group_id' => $g5->id,
            'billing_type' => 'monthly',
            'student_price' => 6000,
            'teacher_share' => 4000,
            'academy_share' => 2000,
            'effective_from' => '2026-09-01',
            'is_active' => true,
        ]);

        GroupSchedule::create([
            'group_id' => $g5->id,
            'day' => 'Mercredi',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'room' => 'Salle VIP',
            'is_active' => true,
        ]);

        $g6 = Group::create([
            'teacher_id' => $ahmed->id,
            'subject_id' => $math->id,
            'level' => '3AS',
            'school_year_id' => $year->id,
            'name' => 'Groupe VIP',
            'mode' => 'vip',
            'is_active' => true,
        ]);

        GroupTariff::create([
            'group_id' => $g6->id,
            'billing_type' => 'per_session',
            'student_price' => 2000,
            'teacher_share' => 1500,
            'academy_share' => 500,
            'effective_from' => '2026-09-01',
            'is_active' => true,
        ]);

        GroupSchedule::create([
            'group_id' => $g6->id,
            'day' => 'Samedi',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'room' => 'Salle VIP',
            'is_active' => true,
        ]);

        // =====================================================
        // ENSEIGNANT : Sara — Physique
        // =====================================================

        $sara = Teacher::firstOrCreate(
            ['first_name' => 'Sara', 'last_name' => 'Benali'],
            [
                'speciality' => 'Physique',
                'phone' => '0550 40 50 60',
                'email' => 'sara.benali@email.com',
                'active' => true,
            ]
        );

        $physique = Subject::firstOrCreate(
            ['code' => 'PHYS'],
            [
                'name' => 'Physique',
                'description' => 'Enseignement de la physique',
                'level' => 'all',
                'primaire' => false,
                'moyen' => true,
                'lycee' => true,
                'hours_per_week' => 3,
                'active' => true,
            ]
        );

        $g7 = Group::create([
            'teacher_id' => $sara->id,
            'subject_id' => $physique->id,
            'level' => '2AS',
            'school_year_id' => $year->id,
            'name' => 'Groupe 1',
            'mode' => 'normal',
            'is_active' => true,
        ]);

        GroupTariff::create([
            'group_id' => $g7->id,
            'billing_type' => 'monthly',
            'student_price' => 2000,
            'teacher_share' => 1500,
            'academy_share' => 500,
            'effective_from' => '2026-09-01',
            'is_active' => true,
        ]);

        GroupSchedule::create([
            'group_id' => $g7->id,
            'day' => 'Mardi',
            'start_time' => '14:00',
            'end_time' => '15:30',
            'room' => 'Salle 04',
            'is_active' => true,
        ]);

        $g8 = Group::create([
            'teacher_id' => $sara->id,
            'subject_id' => $physique->id,
            'level' => '3AS',
            'school_year_id' => $year->id,
            'name' => 'Groupe 1',
            'mode' => 'normal',
            'is_active' => true,
        ]);

        GroupTariff::create([
            'group_id' => $g8->id,
            'billing_type' => 'monthly',
            'student_price' => 2200,
            'teacher_share' => 1600,
            'academy_share' => 600,
            'effective_from' => '2026-09-01',
            'is_active' => true,
        ]);

        GroupSchedule::create([
            'group_id' => $g8->id,
            'day' => 'Jeudi',
            'start_time' => '16:00',
            'end_time' => '17:30',
            'room' => 'Salle 04',
            'is_active' => true,
        ]);

        $this->command->info('Groupes de démonstration créés avec succès.');
    }
}
