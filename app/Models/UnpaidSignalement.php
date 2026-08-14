<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnpaidSignalement extends Model
{
    protected $fillable = [
        'student_id',
        'payment_id',
        'period',
        'amount_due',
        'amount_remaining',
        'status',
        'reminder_count',
        'last_reminder_at',
        'note',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_remaining' => 'decimal:2',
        'last_reminder_at' => 'datetime',
    ];

    /**
     * Élève concerné
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Paiement concerné
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}