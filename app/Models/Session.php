<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;

class Session extends Model
{
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
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
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
}