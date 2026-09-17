<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->index('student_id', 'enrollments_student_id_index');
            $table->dropIndex('enrollments_student_id_subject_id_teacher_id_unique');
            $table->unique(['student_id', 'group_id'], 'enrollments_student_id_group_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex('enrollments_student_id_group_id_unique');
            $table->dropIndex('enrollments_student_id_index');
            $table->unique(['student_id', 'subject_id', 'teacher_id'], 'enrollments_student_id_subject_id_teacher_id_unique');
        });
    }
};
