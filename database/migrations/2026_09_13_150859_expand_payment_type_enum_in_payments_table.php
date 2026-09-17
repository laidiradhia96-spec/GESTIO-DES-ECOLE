<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE payments MODIFY COLUMN payment_type ENUM('monthly', 'special_monthly', 'vip_monthly', 'vip_per_session') DEFAULT 'monthly' NOT NULL"
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE payments MODIFY COLUMN payment_type ENUM('monthly', 'vip') DEFAULT 'monthly' NOT NULL"
            );
        }
    }
};
