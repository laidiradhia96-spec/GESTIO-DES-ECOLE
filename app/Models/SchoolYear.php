<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
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
}
