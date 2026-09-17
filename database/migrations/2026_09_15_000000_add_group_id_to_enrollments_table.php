<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('group_id')
                ->nullable()
                ->after('teacher_id')
                ->constrained('groups')
                ->nullOnDelete();
        });

        $this->backfillGroupIds();
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
    }

    /**
     * Map enrollment.group_id from student_group pivot.
     *
     * For each enrollment:
     *   1. Find student_group entries for this student
     *   2. Load the Group
     *   3. Verify Group.subject_id matches enrollment.subject_id
     *   4. If exactly ONE reliable match → set group_id
     *   5. If zero or multiple → leave NULL
     */
    private function backfillGroupIds(): void
    {
        $enrollments = DB::table('enrollments')
            ->whereNull('group_id')
            ->get();

        $mapped = 0;
        $unresolved = 0;

        foreach ($enrollments as $enrollment) {
            $studentId = $enrollment->student_id;
            $subjectId = $enrollment->subject_id;

            // Find all active groups this student belongs to via student_group
            $studentGroups = DB::table('student_group')
                ->where('student_id', $studentId)
                ->where('is_active', 1)
                ->pluck('group_id')
                ->toArray();

            if (empty($studentGroups)) {
                $unresolved++;

                continue;
            }

            // Find groups matching this enrollment's subject
            $matchingGroups = DB::table('groups')
                ->whereIn('id', $studentGroups)
                ->where('subject_id', $subjectId)
                ->where('is_active', 1)
                ->get();

            if ($matchingGroups->count() === 1) {
                DB::table('enrollments')
                    ->where('id', $enrollment->id)
                    ->update(['group_id' => $matchingGroups->first()->id]);

                $mapped++;
            } else {
                // Zero or multiple matches → leave NULL
                $unresolved++;
            }
        }

        // Log the results (visible in migration output)
        echo "Enrollment backfill: {$mapped} mapped, {$unresolved} unresolved (legacy)\n";
    }
};
