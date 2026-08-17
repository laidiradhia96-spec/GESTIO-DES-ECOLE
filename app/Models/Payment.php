<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'student_id',
        'subject_id',
        'payment_type',
        'period',
        'amount_due',
        'amount_paid',
        'remaining_amount',
        'payment_method',
        'payment_date',
        'payment_time',
        'note',
        'school_year_id',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Élève
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Matière
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Signalements
     */
    public function signalements()
    {
        return $this->hasMany(PaymentSignalement::class);
    }

    /**
     * Année scolaire
     */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
