<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'type',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Les utilisateurs qui ont vu cette annonce
     */
    public function viewers()
    {
        return $this->belongsToMany(
            User::class,
            'announcement_user'
        )
        ->withPivot('seen_at')
        ->withTimestamps();
    }
}