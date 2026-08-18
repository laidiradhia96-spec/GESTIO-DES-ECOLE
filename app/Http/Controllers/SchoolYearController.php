<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SchoolYearController extends Controller
{
    /**
     * Liste des années scolaires avec leurs compteurs de données.
     */
    public function index()
    {
        $schoolYears = SchoolYear::withCount([
            'attendances',
            'enrollments',
            'payments',
            'classSessions',
            'paymentSignalements',
            'unpaidSignalements',
            'paymentSchedules',
        ])->orderByDesc('start_date')->get();

        return view('school-years.index', compact('schoolYears'));
    }

    /**
     * Formulaire d'ajout
     */
    public function create()
    {
        return view('school-years.create');
    }

    /**
     * Enregistrer une année scolaire
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $this->assertNoOverlap($validated['start_date'], $validated['end_date']);

        // Première année créée → automatiquement courante (sinon aucun
        // défaut n'existerait tant que l'admin n'a rien choisi).
        $validated['is_current'] = SchoolYear::doesntExist();

        $year = SchoolYear::create($validated);

        return redirect()
            ->route('school-years.show', $year)
            ->with('success', "L'année scolaire {$year->name} a été créée avec succès.");
    }

    /**
     * Afficher une année scolaire
     */
    public function show(SchoolYear $schoolYear)
    {
        $counts = $schoolYear->relatedCounts();

        return view('school-years.show', compact('schoolYear', 'counts'));
    }

    /**
     * Formulaire de modification
     */
    public function edit(SchoolYear $schoolYear)
    {
        return view('school-years.edit', compact('schoolYear'));
    }

    /**
     * Mettre à jour une année scolaire
     */
    public function update(Request $request, SchoolYear $schoolYear)
    {
        $validated = $request->validate($this->rules($schoolYear), $this->messages());

        $this->assertNoOverlap($validated['start_date'], $validated['end_date'], $schoolYear);

        $schoolYear->update($validated);

        return redirect()
            ->route('school-years.show', $schoolYear)
            ->with('success', "L'année scolaire {$schoolYear->name} a été modifiée avec succès.");
    }

    /**
     * Définir l'année courante (une seule à is_current = true).
     */
    public function setCurrent(SchoolYear $schoolYear)
    {
        SchoolYear::setCurrent($schoolYear);

        return back()
            ->with('success', "L'année scolaire {$schoolYear->name} est maintenant l'année courante.");
    }

    /**
     * Supprimer une année scolaire, uniquement si elle ne contient
     * aucune donnée et n'est pas l'année courante. Jamais de cascade.
     */
    public function destroy(SchoolYear $schoolYear)
    {
        if ($schoolYear->is_current) {
            return back()
                ->with('error', 'Impossible de supprimer l\'année scolaire courante. Définissez d\'abord une autre année courante.');
        }

        if ($schoolYear->hasRelatedData()) {
            return back()
                ->with('error', 'Impossible de supprimer cette année scolaire : elle contient des données ('.$this->dataSummary($schoolYear->relatedCounts()).').');
        }

        try {
            $schoolYear->delete();
        } catch (QueryException) {
            return back()
                ->with('error', 'Impossible de supprimer cette année scolaire : elle est référencée par des données existantes.');
        }

        return redirect()
            ->route('school-years.index')
            ->with('success', "L'année scolaire {$schoolYear->name} a été supprimée avec succès.");
    }

    /**
     * Règles de validation (nom unique + format, dates cohérentes).
     */
    private function rules(?SchoolYear $year = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d{4}-\d{4}$/',
                Rule::unique('school_years', 'name')->ignore($year?->id),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ];
    }

    /**
     * Messages de validation en français.
     */
    private function messages(): array
    {
        return [
            'name.required' => 'Le nom de l\'année scolaire est obligatoire.',
            'name.regex' => 'Le nom doit être au format « 2025-2026 ».',
            'name.unique' => 'Ce nom d\'année scolaire existe déjà.',
            'name.max' => 'Le nom ne doit pas dépasser 20 caractères.',
            'start_date.required' => 'La date de début est obligatoire.',
            'start_date.date' => 'La date de début est invalide.',
            'end_date.required' => 'La date de fin est obligatoire.',
            'end_date.date' => 'La date de fin est invalide.',
            'end_date.after' => 'La date de fin doit être postérieure à la date de début.',
        ];
    }

    /**
     * Refuser tout chevauchement d'intervalle avec une année existante
     * (hors de l'année elle-même en modification).
     */
    private function assertNoOverlap(string $startDate, string $endDate, ?SchoolYear $except = null): void
    {
        $overlap = SchoolYear::where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->when($except, fn ($query) => $query->where('id', '!=', $except->id))
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_date' => 'L\'intervalle de cette année scolaire chevauche une année existante.',
            ]);
        }
    }

    /**
     * Résumé lisible des données liées pour le message d'erreur.
     */
    private function dataSummary(array $counts): string
    {
        $labels = [
            'attendances' => 'présences',
            'enrollments' => 'inscriptions',
            'payments' => 'paiements',
            'class_sessions' => 'séances',
            'payment_signalements' => 'signalements',
            'unpaid_signalements' => 'signalements d\'impayés',
            'payment_schedules' => 'échéanciers',
        ];

        return collect($counts)
            ->filter()
            ->map(fn ($count, $table) => "{$count} {$labels[$table]}")
            ->implode(', ');
    }
}
