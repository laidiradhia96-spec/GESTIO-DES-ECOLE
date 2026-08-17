<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables périodiques reliées aux années scolaires.
     */
    private const TABLES = [
        'attendances',
        'enrollments',
        'payments',
        'class_sessions',
        'payment_signalements',
        'unpaid_signalements',
        'payment_schedules',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {

            Schema::table($table, function (Blueprint $table) {

                $table->foreignId('school_year_id')
                    ->nullable()
                    ->constrained('school_years')
                    ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {

            Schema::table($table, function (Blueprint $table) {

                $table->dropConstrainedForeignId('school_year_id');
            });
        }
    }
};
