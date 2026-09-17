<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupTariff extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'billing_type',
        'student_price',
        'teacher_share',
        'academy_share',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected $casts = [
        'student_price' => 'decimal:2',
        'teacher_share' => 'decimal:2',
        'academy_share' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
