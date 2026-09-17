<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('teacher_share', 10, 2)
                ->nullable()
                ->after('remaining_amount');

            $table->decimal('academy_share', 10, 2)
                ->nullable()
                ->after('teacher_share');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['teacher_share', 'academy_share']);
        });
    }
};
