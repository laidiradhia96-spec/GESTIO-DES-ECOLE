<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'level',
        'school_year_id',
        'name',
        'mode',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =====================================================
    // RELATIONS
    // =====================================================

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function tariffs(): HasMany
    {
        return $this->hasMany(GroupTariff::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(GroupSchedule::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_group')
            ->withPivot(['joined_at', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Inscriptions liées à ce groupe
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Tarif actif le plus récent du groupe (relation eager-loadable).
     */
    public function currentTariff(): HasOne
    {
        return $this->hasOne(GroupTariff::class)
            ->where('is_active', true)
            ->latest('effective_from');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSchoolYear($query, int $schoolYearId)
    {
        return $query->where('school_year_id', $schoolYearId);
    }

    public function scopeForTeacher($query, int $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForLevel($query, string $level)
    {
        return $query->where('level', $level);
    }
}
