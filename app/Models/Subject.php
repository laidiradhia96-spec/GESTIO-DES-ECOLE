<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'level',
        'primaire',
        'moyen',
        'lycee',
        'hours_per_week',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'primaire' => 'boolean',
        'moyen' => 'boolean',
        'lycee' => 'boolean',
    ];

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'subject_teacher')
            ->withTimestamps();
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollments')
            ->withPivot('teacher_id', 'start_date', 'status')
            ->withTimestamps();
    }
}
