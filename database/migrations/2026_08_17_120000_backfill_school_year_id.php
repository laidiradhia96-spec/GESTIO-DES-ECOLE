<?php

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\PaymentSignalement;
use App\Models\SchoolYear;
use App\Models\UnpaidSignalement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BackfillSchoolYearId extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            // Pas d'injection de données dans la base de test : les tests
            // créent leurs propres fixtures (évite le conflit sur name unique).
            if (! app()->environment('testing')) {
                static::ensureSchoolYears();
            }

            static::backfill();
        });
    }

    public function down(): void
    {
        // Migration de données : aucune structure à annuler, aucune donnée à effacer.
    }

    /**
     * Garantir l'existence des trois années de démarrage (sans jamais
     * écraser une année existante), miroir de SchoolYearSeeder.
     */
    public static function ensureSchoolYears(): void
    {
        foreach ([
            ['2025-2026', '2025-09-01', '2026-08-31'],
            ['2026-2027', '2026-09-01', '2027-08-31'],
            ['2027-2028', '2027-09-01', '2028-08-31'],
        ] as [$name, $startDate, $endDate]) {
            SchoolYear::firstOrCreate(
                ['name' => $name],
                [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_current' => false,
                ]
            );
        }
    }

    /**
     * Remplir school_year_id sur les 7 tables périodiques (idempotent :
     * ne traite que les lignes encore non rattachées).
     */
    public static function backfill(): void
    {
        static::backfillTable(Attendance::query(), fn ($row) => $row->date);

        static::backfillTable(
            Enrollment::query(),
            fn ($row) => $row->start_date ?? $row->created_at
        );

        static::backfillTable(
            Payment::query(),
            fn ($row) => static::paymentReferenceDate($row)
        );

        static::backfillTable(
            ClassSession::query(),
            fn ($row) => $row->start_date ?? $row->end_date ?? $row->created_at
        );

        static::backfillTable(
            PaymentSignalement::query(),
            fn ($row) => static::signalementReferenceDate($row)
        );

        static::backfillTable(
            UnpaidSignalement::query(),
            fn ($row) => static::unpaidReferenceDate($row)
        );

        static::backfillTable(
            PaymentSchedule::query(),
            fn ($row) => static::scheduleReferenceDate($row)
        );
    }

    private static function backfillTable(Builder $query, callable $referenceDate): void
    {
        $linked = 0;
        $nulls = 0;

        $query->whereNull('school_year_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($referenceDate, &$linked, &$nulls) {
                foreach ($rows as $row) {
                    $date = $referenceDate($row);
                    $year = $date ? SchoolYear::forDate($date) : null;

                    $row->update(['school_year_id' => $year?->id]);

                    if ($year === null) {
                        $nulls++;
                        logger()->warning("Backfill school_year_id : {$row->getTable()} #{$row->id} sans année (référence : ".($date ? Carbon::parse($date)->toDateString() : 'aucune').')');
                    } else {
                        $linked++;
                    }
                }
            });

        logger()->info("Backfill school_year_id : {$query->getModel()->getTable()} — {$linked} rattachées, {$nulls} NULL");
    }

    /**
     * Mois couvert par une période datée "Y-m" ou "Y-m-d", sinon null
     * (miroir de PaymentSignalementService::resolvePaymentMonth).
     */
    private static function periodMonthDate(?string $period): ?string
    {
        $period = $period === null ? null : trim($period);

        if (preg_match('/^\d{4}-\d{2}$/', (string) $period)) {
            return Carbon::createFromFormat('Y-m', $period)->startOfMonth()->toDateString();
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $period)) {
            return Carbon::parse($period)->startOfMonth()->toDateString();
        }

        return null;
    }

    /**
     * Mois couvert par une période nommée legacy ("Octobre") : le vrai mois,
     * avec l'année portée par la date source. Sans date source valide :
     * retour null (jamais de devinette sur l'année).
     */
    private static function namedMonthDate(?string $period, ?string $yearSourceDate): ?string
    {
        $month = static::frenchMonthNumber($period);

        if ($month === null || $yearSourceDate === null) {
            return null;
        }

        try {
            $year = (int) Carbon::parse($yearSourceDate)->format('Y');
        } catch (Throwable) {
            return null;
        }

        return Carbon::create($year, $month, 1)->toDateString();
    }

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
     * Date de référence d'un paiement : mois de période (datée ou nommée),
     * sinon payment_date.
     */
    private static function paymentReferenceDate(object $row): ?string
    {
        return static::periodMonthDate($row->period)
            ?? static::namedMonthDate($row->period, $row->payment_date)
            ?? $row->payment_date;
    }

    /**
     * Date de référence d'un signalement : mois de période (datée ou nommée
     * avec l'année de signalement_date), sinon signalement_date.
     */
    private static function signalementReferenceDate(object $row): ?string
    {
        return static::periodMonthDate($row->period)
            ?? static::namedMonthDate($row->period, $row->signalement_date)
            ?? $row->signalement_date;
    }

    /**
     * Date de référence d'un impayé : période datée uniquement, sinon created_at.
     */
    private static function unpaidReferenceDate(object $row): ?string
    {
        return static::periodMonthDate($row->period) ?? $row->created_at;
    }

    /**
     * Date de référence d'un échéancier : due_date, sinon période datée, sinon created_at.
     */
    private static function scheduleReferenceDate(object $row): ?string
    {
        return $row->due_date
            ?? static::periodMonthDate($row->period)
            ?? $row->created_at;
    }
}
