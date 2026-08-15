<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [

        'student_id',

        'subject_id',

        'teacher_id',

        'start_date',

        'status',

        'payment_type',
    ];


    protected $casts = [

        'start_date' => 'date',

    ];


    public function student()
    {
        return $this->belongsTo(
            Student::class
        );
    }


    public function subject()
    {
        return $this->belongsTo(
            Subject::class
        );
    }


    public function teacher()
    {
        return $this->belongsTo(
            Teacher::class
        );
    }
}