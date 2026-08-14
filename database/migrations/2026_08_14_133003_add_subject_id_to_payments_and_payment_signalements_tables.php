
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. تصحيح payments.subject_id
        |--------------------------------------------------------------------------
        |
        | العمود أُضيف في المحاولة السابقة كـ bigint عادي.
        | subjects.id هو bigint unsigned.
        |
        */

        DB::statement("
            ALTER TABLE payments
            MODIFY subject_id BIGINT UNSIGNED NULL
        ");


        /*
        |--------------------------------------------------------------------------
        | 2. إضافة نوع الاشتراك
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('payments', 'payment_type')) {

            Schema::table('payments', function (Blueprint $table) {

                $table->enum('payment_type', [
                    'monthly',
                    'vip'
                ])
                ->default('monthly')
                ->after('period');
            });
        }


        /*
        |--------------------------------------------------------------------------
        | 3. إضافة subject_id إلى payment_signalements
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('payment_signalements', 'subject_id')) {

            Schema::table('payment_signalements', function (Blueprint $table) {

                $table->unsignedBigInteger('subject_id')
                    ->nullable()
                    ->after('student_id');
            });
        }


        /*
        |--------------------------------------------------------------------------
        | 4. تاريخ الحضور الذي تسبب في signalement
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('payment_signalements', 'attendance_date')) {

            Schema::table('payment_signalements', function (Blueprint $table) {

                $table->date('attendance_date')
                    ->nullable()
                    ->after('signalement_date');
            });
        }


        /*
        |--------------------------------------------------------------------------
        | 5. Foreign Key payments.subject_id
        |--------------------------------------------------------------------------
        */

        Schema::table('payments', function (Blueprint $table) {

            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects')
                ->nullOnDelete();
        });


        /*
        |--------------------------------------------------------------------------
        | 6. Foreign Key payment_signalements.subject_id
        |--------------------------------------------------------------------------
        */

        Schema::table('payment_signalements', function (Blueprint $table) {

            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects')
                ->nullOnDelete();
        });
    }


    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | payment_signalements
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('payment_signalements', 'subject_id')) {

            Schema::table('payment_signalements', function (Blueprint $table) {

                $table->dropForeign(['subject_id']);
            });

            Schema::table('payment_signalements', function (Blueprint $table) {

                $table->dropColumn('subject_id');
            });
        }


        if (Schema::hasColumn('payment_signalements', 'attendance_date')) {

            Schema::table('payment_signalements', function (Blueprint $table) {

                $table->dropColumn('attendance_date');
            });
        }


        /*
        |--------------------------------------------------------------------------
        | payments
        |--------------------------------------------------------------------------
        */

        Schema::table('payments', function (Blueprint $table) {

            $table->dropForeign(['subject_id']);
        });


        if (Schema::hasColumn('payments', 'payment_type')) {

            Schema::table('payments', function (Blueprint $table) {

                $table->dropColumn('payment_type');
            });
        }

        /*
         * لا نحذف subject_id من payments
         * لأنه أُضيف قبل هذه migration.
         */
    }
};