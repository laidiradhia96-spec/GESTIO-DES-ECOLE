<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_signalements', function (Blueprint $table) {
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
            $table->decimal('amount_remaining', 10, 2);

            // Statut du signalement
            $table->enum('status', [
                'pending',
                'sent',
                'resolved'
            ])->default('pending');

            // Date du signalement
            $table->date('signalement_date');

            // Date d'envoi
            $table->dateTime('sent_at')->nullable();

            // Observation
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_signalements');
    }
};