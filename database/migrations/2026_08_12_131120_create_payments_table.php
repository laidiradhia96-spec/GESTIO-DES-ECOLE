<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Numéro automatique du reçu
            $table->string('receipt_number')->unique();

            // Élève
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            // Période de paiement
            $table->string('period');

            // Montants
            $table->decimal('amount_due', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2)->default(0);

            // Mode de paiement
            $table->string('payment_method')->nullable();

            // Date et heure du paiement
            $table->date('payment_date');
            $table->time('payment_time');

            // Observation
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};