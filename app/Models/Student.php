<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'date_of_birth',
        'level',
        'parent_name',
        'parent_phone',
        'phone',
        'address',
        'user_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Recherche multi-termes : nom, prénom, nom complet, téléphone.
     *
     * Chaque terme (séparé par des espaces) doit correspondre à au moins
     * un champ de l'élève (AND entre les termes), ce qui permet les
     * recherches "Nom Prénom", "Nom" seul ou "Prénom" seul.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $terms = preg_split(
            '/\s+/',
            trim($search),
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        foreach ($terms as $term) {

            $query->where(function (Builder $q) use ($term) {

                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('parent_name', 'like', "%{$term}%");
            });
        }

        return $query;
    }

    /**
     * Compte utilisateur de l'élève
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Paiements de l'élève
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Inscriptions de l'élève
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Présences de l'élève
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Signalements de paiement
     */
    public function paymentSignalements()
    {
        return $this->hasMany(PaymentSignalement::class);
    }

    /**
     * Groupes pédagogiques de l'élève
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'student_group')
            ->withPivot(['joined_at', 'is_active'])
            ->withTimestamps();
    }
}
