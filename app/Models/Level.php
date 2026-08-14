<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Les enseignants de ce niveau
     */
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_level')
            ->withTimestamps();
    }
}