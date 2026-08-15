<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Compte utilisateur de l'élève
     */
    public function user()
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
}
