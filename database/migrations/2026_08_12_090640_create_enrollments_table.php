<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();

            $table->date('start_date')->nullable();

            $table->string('status')->default('active');

            $table->timestamps();

            $table->unique([
                'student_id',
                'subject_id',
                'teacher_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};