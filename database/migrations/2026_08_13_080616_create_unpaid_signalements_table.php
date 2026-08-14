<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unpaid_signalements', function (Blueprint $table) {
            $table->id();

            // Élève concerné
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            // Paiement concerné
            $table->foreignId('payment_id')
                ->nullable()
                ->constrained('payments')
                ->nullOnDelete();

            // Période impayée
            $table->string('period');

            // Montant restant
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->decimal('amount_remaining', 10, 2)->default(0);

            // Statut du signalement
            $table->enum('status', [
                'pending',
                'contacted',
                'paid',
                'cancelled'
            ])->default('pending');

            // Nombre de relances
            $table->unsignedInteger('reminder_count')->default(0);

            // Dernière relance
            $table->dateTime('last_reminder_at')->nullable();

            // Observation
            $table->text('note')->nullable();

            $table->timestamps();

            // Empêcher plusieurs signalements actifs
            // pour le même élève et la même période.
            $table->unique(
                ['student_id', 'period'],
                'student_period_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unpaid_signalements');
    }
};