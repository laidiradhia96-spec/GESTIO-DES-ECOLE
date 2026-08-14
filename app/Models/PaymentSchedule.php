<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    protected $fillable = [
        'student_id',
        'period',
        'amount_due',
        'amount_paid',
        'remaining_amount',
        'status',
        'due_date',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /**
     * L'échéance appartient à un élève
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}