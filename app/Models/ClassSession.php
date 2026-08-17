<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSession extends Model
{
    protected $table = 'class_sessions';

    protected $fillable = [
        'student_id',
        'subject_id',
        'teacher_id',
        'day',
        'start_time',
        'end_time',
        'start_date',
        'end_date',
        'status',
        'note',
        'school_year_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Élève
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Matière
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Enseignant
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Année scolaire
     */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
