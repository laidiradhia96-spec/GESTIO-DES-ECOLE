<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('group_id')
                ->nullable()
                ->after('teacher_id')
                ->constrained('groups')
                ->nullOnDelete();
        });

        if ($driver === 'mysql') {
            // MySQL uses raw SQL because the unique index is needed by FK constraints
            DB::statement('ALTER TABLE attendances DROP INDEX attendances_student_id_subject_id_date_unique');
            DB::statement('ALTER TABLE attendances ADD UNIQUE INDEX attendances_student_subject_group_date_unique (student_id, subject_id, group_id, date)');
        } else {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropUnique(['student_id', 'subject_id', 'date']);
                $table->unique(['student_id', 'subject_id', 'group_id', 'date']);
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE attendances DROP INDEX attendances_student_subject_group_date_unique');
            DB::statement('ALTER TABLE attendances ADD UNIQUE INDEX attendances_student_id_subject_id_date_unique (student_id, subject_id, date)');
        } else {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropUnique(['student_id', 'subject_id', 'group_id', 'date']);
                $table->unique(['student_id', 'subject_id', 'date']);
            });
        }

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
    }
};
