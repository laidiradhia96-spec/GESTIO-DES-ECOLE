<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Level;

class Teacher extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'speciality',
        'phone',
        'email',
        'address',
        'hire_date',
        'active',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'active' => 'boolean',
    ];
    public function enrollments()
{
    return $this->hasMany(Enrollment::class);
}

public function subjects()
{
    return $this->belongsToMany(Subject::class, 'subject_teacher')
        ->withTimestamps();
}

public function levels()
{
    return $this->belongsToMany(Level::class, 'teacher_level')
        ->withTimestamps();
}
}