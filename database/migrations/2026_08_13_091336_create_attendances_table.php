<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {

            $table->id();

            // Élève
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            // Matière
            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            // Enseignant
            $table->foreignId('teacher_id')
                ->nullable()
                ->constrained('teachers')
                ->nullOnDelete();

            // Date de la séance
            $table->date('date');

            // Statut
            $table->enum('status', [
                'present',
                'absent',
                'late',
                'justified',
            ])->default('present');

            // Observation
            $table->text('note')->nullable();

            $table->timestamps();

            // منع تسجيل نفس التلميذ مرتين في نفس الحصة
            $table->unique([
                'student_id',
                'subject_id',
                'date'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};