<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSignalement extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'group_id',
        'payment_id',
        'period',
        'amount_remaining',
        'status',
        'signalement_date',
        'attendance_date',
        'sent_at',
        'note',
        'school_year_id',
    ];

    protected $casts = [
        'amount_remaining' => 'decimal:2',
        'signalement_date' => 'date',
        'attendance_date' => 'date',
        'sent_at' => 'datetime',
    ];

    /**
     * الطالب
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * المادة
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Groupe
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * الدفع
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Année scolaire
     */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
