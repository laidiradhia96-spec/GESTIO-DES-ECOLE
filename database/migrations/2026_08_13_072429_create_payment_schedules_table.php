<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_schedules', function (Blueprint $table) {

            $table->id();

            // Élève
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            // Période
            $table->string('period');

            // Montant à payer
            $table->decimal('amount_due', 10, 2);

            // Montant payé
            $table->decimal('amount_paid', 10, 2)
                ->default(0);

            // Reste
            $table->decimal('remaining_amount', 10, 2)
                ->default(0);

            // Statut
            $table->enum('status', [
                'unpaid',
                'partial',
                'paid'
            ])->default('unpaid');

            // Date limite
            $table->date('due_date')->nullable();

            $table->timestamps();

            // Un seul échéancier par élève et période
            $table->unique([
                'student_id',
                'period'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};