<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class SchoolYear extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * Année scolaire courante (unique is_current = true).
     */
    public static function current(): ?self
    {
        return static::where('is_current', true)->first();
    }

    /**
     * Année scolaire par défaut pour les filtres admin :
     * courante → année contenant aujourd'hui → dernière année → null.
     *
     * null signifie « aucune année » : les filtres retombent sur "Toutes".
     */
    public static function defaultId(): ?int
    {
        return static::current()?->id
            ?? static::forDate(now())?->id
            ?? static::orderByDesc('start_date')->first()?->id
            ?? null;
    }

    /**
     * Année scolaire dont l'intervalle [start_date, end_date]
     * contient la date donnée (bornes incluses), sinon null.
     */
    public static function forDate(Carbon|string $date): ?self
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        return static::whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->orderBy('start_date')
            ->first();
    }

    /**
     * Année scolaire couverte par une période de paiement/signalement :
     * "Y-m" ou "Y-m-d" (année portée par la période), nom de mois français
     * legacy (année portée par la date source), sinon via la date de secours.
     *
     * Jamais de devinette : nom de mois sans date source valide ou période
     * inconnue sans date de secours → null.
     */
    public static function forPeriod(?string $period, ?string $yearSourceDate = null, ?string $fallbackDate = null): ?self
    {
        $period = $period === null ? null : trim($period);

        if (preg_match('/^\d{4}-\d{2}$/', (string) $period)) {
            return static::forDate(Carbon::createFromFormat('Y-m', $period)->startOfMonth()->toDateString());
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $period)) {
            return static::forDate(Carbon::parse($period)->startOfMonth()->toDateString());
        }

        if ($period) {
            $month = static::frenchMonthNumber($period);

            if ($month !== null) {
                if ($yearSourceDate === null) {
                    return null;
                }

                try {
                    $year = (int) Carbon::parse($yearSourceDate)->format('Y');
                } catch (\Throwable) {
                    return null;
                }

                return static::forDate(Carbon::create($year, $month, 1)->toDateString());
            }
        }

        return $fallbackDate !== null ? static::forDate($fallbackDate) : null;
    }

    /**
     * Numéro de mois à partir d'un nom français legacy, sinon null.
     */
    private static function frenchMonthNumber(?string $name): ?int
    {
        $months = [
            'janvier' => 1,
            'février' => 2,
            'fevrier' => 2,
            'mars' => 3,
            'avril' => 4,
            'mai' => 5,
            'juin' => 6,
            'juillet' => 7,
            'août' => 8,
            'aout' => 8,
            'septembre' => 9,
            'octobre' => 10,
            'novembre' => 11,
            'décembre' => 12,
            'decembre' => 12,
        ];

        return $name ? ($months[mb_strtolower(trim($name))] ?? null) : null;
    }

    /**
     * Définir l'année courante : une seule année is_current = true.
     */
    public static function setCurrent(self $year): self
    {
        DB::transaction(function () use ($year) {
            static::query()->update(['is_current' => false]);

            $year->update(['is_current' => true]);
        });

        return $year->refresh();
    }

    /**
     * Présences de l'année
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Inscriptions de l'année
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Paiements de l'année
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Séances de l'année
     */
    public function classSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }

    /**
     * Signalements de paiement de l'année
     */
    public function paymentSignalements(): HasMany
    {
        return $this->hasMany(PaymentSignalement::class);
    }

    /**
     * Signalements d'impayés de l'année
     */
    public function unpaidSignalements(): HasMany
    {
        return $this->hasMany(UnpaidSignalement::class);
    }

    /**
     * Échéanciers de l'année
     */
    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    /**
     * Nombre de lignes liées par table (protection anti-suppression).
     */
    public function relatedCounts(): array
    {
        return [
            'attendances' => $this->attendances()->count(),
            'enrollments' => $this->enrollments()->count(),
            'payments' => $this->payments()->count(),
            'class_sessions' => $this->classSessions()->count(),
            'payment_signalements' => $this->paymentSignalements()->count(),
            'unpaid_signalements' => $this->unpaidSignalements()->count(),
            'payment_schedules' => $this->paymentSchedules()->count(),
        ];
    }

    /**
     * L'année scolaire contient-elle au moins une donnée liée ?
     */
    public function hasRelatedData(): bool
    {
        return array_sum($this->relatedCounts()) > 0;
    }
}
