<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupSchedule;
use App\Models\GroupTariff;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    /**
     * Liste des groupes avec filtres
     */
    public function index(Request $request)
    {
        $query = Group::with([
            'teacher',
            'subject',
            'schoolYear',
            'currentTariff',
            'schedules',
        ]);

        // Filtre année scolaire
        $schoolYears = SchoolYear::orderByDesc('start_date')->get();

        $schoolYearId = $request->filled('school_year_id')
            ? (int) $request->school_year_id
            : ($request->has('school_year_id')
                ? null
                : SchoolYear::defaultId());

        $query->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId));

        // Filtre niveau
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Filtre enseignant
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', (int) $request->teacher_id);
        }

        // Filtre matière
        if ($request->filled('subject_id')) {
            $query->where('subject_id', (int) $request->subject_id);
        }

        // Filtre mode
        if ($request->filled('mode')) {
            $query->where('mode', $request->mode);
        }

        // Filtre statut
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Recherche par nom
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('teacher', fn ($t) => $t
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%"))
                    ->orWhereHas('subject', fn ($s) => $s
                        ->where('name', 'like', "%{$search}%"));
            });
        }

        $groups = $query->latest()->paginate(15)->withQueryString();

        $teachers = Teacher::where('active', true)->orderBy('first_name')->get();
        $subjects = Subject::where('active', true)->orderBy('name')->get();

        return view('groups.index', compact(
            'groups',
            'schoolYears',
            'schoolYearId',
            'teachers',
            'subjects',
        ));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $schoolYears = SchoolYear::orderByDesc('start_date')->get();
        $currentSchoolYearId = SchoolYear::defaultId();
        $teachers = Teacher::where('active', true)->orderBy('first_name')->get();
        $subjects = Subject::where('active', true)->orderBy('name')->get();

        return view('groups.create', compact(
            'schoolYears',
            'currentSchoolYearId',
            'teachers',
            'subjects',
        ));
    }

    /**
     * Enregistrer un groupe
     */
    public function store(Request $request)
    {
        $validated = $this->validateGroup($request);

        DB::transaction(function () use ($validated, $request) {

            $group = Group::create([
                'teacher_id' => $validated['teacher_id'],
                'subject_id' => $validated['subject_id'],
                'level' => $validated['level'],
                'school_year_id' => $validated['school_year_id'],
                'name' => $validated['name'],
                'mode' => $validated['mode'],
                'is_active' => true,
            ]);

            // Créer le tarif
            GroupTariff::create([
                'group_id' => $group->id,
                'billing_type' => $validated['billing_type'],
                'student_price' => $validated['student_price'],
                'teacher_share' => $validated['teacher_share'],
                'academy_share' => $validated['academy_share'],
                'effective_from' => $validated['effective_from'],
                'effective_to' => null,
                'is_active' => true,
            ]);

            // Créer les créneaux
            foreach ($request->input('schedules', []) as $schedule) {
                if (empty($schedule['day']) || empty($schedule['start_time']) || empty($schedule['end_time'])) {
                    continue;
                }

                GroupSchedule::create([
                    'group_id' => $group->id,
                    'day' => $schedule['day'],
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                    'room' => ! empty($schedule['room']) ? $schedule['room'] : null,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()
            ->route('groups.index', ['school_year_id' => $validated['school_year_id']])
            ->with('success', 'Le groupe a été créé avec succès.');
    }

    /**
     * Afficher un groupe
     */
    public function show(Group $group)
    {
        $group->load([
            'teacher',
            'subject',
            'schoolYear',
            'tariffs',
            'schedules',
        ]);

        return view('groups.show', compact('group'));
    }

    /**
     * Formulaire de modification
     */
    public function edit(Group $group)
    {
        $group->load([
            'tariffs' => fn ($q) => $q->latest('effective_from'),
            'schedules',
        ]);

        $schoolYears = SchoolYear::orderByDesc('start_date')->get();
        $teachers = Teacher::where('active', true)->orderBy('first_name')->get();
        $subjects = Subject::where('active', true)->orderBy('name')->get();

        return view('groups.edit', compact(
            'group',
            'schoolYears',
            'teachers',
            'subjects',
        ));
    }

    /**
     * Mettre à jour un groupe
     */
    public function update(Request $request, Group $group)
    {
        // TEMPORAIRE — diagnostic navigateur (à supprimer après le test)
        \Log::info('GROUP_UPDATE_BROWSER_REQUEST', [
            'group_id' => $group->id,
            'name' => $request->input('name'),
            'effective_from' => $request->input('effective_from'),
            'school_year_id' => $request->input('school_year_id'),
            'teacher_id' => $request->input('teacher_id'),
            'subject_id' => $request->input('subject_id'),
            'level' => $request->input('level'),
            'mode' => $request->input('mode'),
            'billing_type' => $request->input('billing_type'),
            'student_price' => $request->input('student_price'),
            'teacher_share' => $request->input('teacher_share'),
            'academy_share' => $request->input('academy_share'),
            'schedules' => $request->input('schedules'),
        ]);

        $validated = $this->validateGroup($request, $group->id);

        $effectiveSchoolYear = SchoolYear::forDate($validated['effective_from']);

        if (! $effectiveSchoolYear) {
            return back()
                ->withErrors([
                    'effective_from' => 'La date d\'effet ne correspond à aucune année scolaire existante.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($request, $group, $validated, $effectiveSchoolYear) {

            $group->update([
                'teacher_id' => $validated['teacher_id'],
                'subject_id' => $validated['subject_id'],
                'level' => $validated['level'],
                'school_year_id' => $effectiveSchoolYear->id,
                'name' => $validated['name'],
                'mode' => $validated['mode'],
            ]);

            // Synchroniser les tarifs : créer un nouveau tarif si les montants ont changé
            $currentTariff = $group->currentTariff;

            $priceChanged = ! $currentTariff
                || round((float) $currentTariff->student_price, 2) !== round((float) $validated['student_price'], 2)
                || round((float) $currentTariff->teacher_share, 2) !== round((float) $validated['teacher_share'], 2)
                || round((float) $currentTariff->academy_share, 2) !== round((float) $validated['academy_share'], 2)
                || $currentTariff->billing_type !== $validated['billing_type'];

            if ($priceChanged) {
                // Désactiver l'ancien tarif
                if ($currentTariff) {
                    $currentTariff->update([
                        'is_active' => false,
                        'effective_to' => now()->subDay()->toDateString(),
                    ]);
                }

                // Créer le nouveau tarif
                GroupTariff::create([
                    'group_id' => $group->id,
                    'billing_type' => $validated['billing_type'],
                    'student_price' => $validated['student_price'],
                    'teacher_share' => $validated['teacher_share'],
                    'academy_share' => $validated['academy_share'],
                    'effective_from' => $validated['effective_from'],
                    'effective_to' => null,
                    'is_active' => true,
                ]);
            }

            // Synchroniser les créneaux
            // Supprimer tous les créneaux existants puis recréer depuis les données soumises.
            $group->schedules()->delete();

            foreach ($request->input('schedules', []) as $scheduleData) {

                if (empty($scheduleData['day']) || empty($scheduleData['start_time']) || empty($scheduleData['end_time'])) {
                    continue;
                }

                GroupSchedule::create([
                    'group_id' => $group->id,
                    'day' => $scheduleData['day'],
                    'start_time' => $scheduleData['start_time'],
                    'end_time' => $scheduleData['end_time'],
                    'room' => ! empty($scheduleData['room']) ? $scheduleData['room'] : null,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()
            ->route('groups.index', ['school_year_id' => $effectiveSchoolYear->id])
            ->with('success', 'Le groupe a été modifié avec succès.');
    }

    /**
     * Supprimer un groupe
     */
    public function destroy(Group $group)
    {
        $hasStudents = $group->students()->wherePivot('is_active', true)->exists();

        if ($hasStudents) {
            return back()->with(
                'error',
                'Ce groupe contient des étudiants actifs. Veuillez le désactiver au lieu de le supprimer.'
            );
        }

        DB::transaction(function () use ($group) {
            $group->schedules()->delete();
            $group->tariffs()->delete();
            $group->delete();
        });

        return redirect()
            ->route('groups.index')
            ->with('success', 'Le groupe a été supprimé avec succès.');
    }

    /**
     * Activer / Désactiver un groupe
     */
    public function toggleStatus(Group $group)
    {
        $group->update(['is_active' => ! $group->is_active]);

        $status = $group->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Le groupe a été {$status} avec succès.");
    }

    // =====================================================
    // ENDPOINTS AJAX
    // =====================================================

    /**
     * Matières d'un enseignant (JSON)
     */
    public function getSubjectsByTeacher(int $teacherId): JsonResponse
    {
        $teacher = Teacher::findOrFail($teacherId);

        $subjects = $teacher->subjects()
            ->where('subjects.active', true)
            ->orderBy('subjects.name')
            ->get(['subjects.id', 'subjects.name', 'subjects.code']);

        return response()->json($subjects);
    }

    /**
     * Niveaux d'un enseignant (JSON)
     *
     * Retourne les niveaux-classe (1AP, 2AS...) compatibles
     * avec les cycles enseignés par l'enseignant via teacher_level.
     */
    public function getLevelsByTeacher(int $teacherId): JsonResponse
    {
        $teacher = Teacher::with('levels')->findOrFail($teacherId);

        $cycleCodes = $teacher->levels->pluck('code')->toArray();

        // Mapping cycle → niveaux-classe
        $cycleToLevels = [
            'PRI' => ['1AP', '2AP', '3AP', '4AP', '5AP'],
            'MOY' => ['1AM', '2AM', '3AM', '4AM'],
            'SEC' => ['1AS', '2AS', '3AS'],
        ];

        $levels = [];

        foreach ($cycleCodes as $code) {
            if (isset($cycleToLevels[$code])) {
                $levels = array_merge($levels, $cycleToLevels[$code]);
            }
        }

        sort($levels);

        return response()->json($levels);
    }

    /**
     * Groupes actifs par niveau pour l'année scolaire courante (JSON)
     */
    public function getGroupsByLevel(string $level): JsonResponse
    {
        $schoolYearId = SchoolYear::defaultId();

        $groups = Group::where('level', $level)
            ->where('is_active', true)
            ->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId))
            ->with(['teacher', 'subject', 'tariffs'])
            ->orderBy('name')
            ->get();

        return response()->json($groups);
    }

    /**
     * AJAX : groupes filtrés par matière + niveau + année scolaire.
     *
     * Utilisé lors de l'inscription pédagogique pour n'afficher
     * que les groupes compatibles avec la matière et le niveau.
     */
    public function getGroupsBySubjectAndLevel(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'level' => 'required|string',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $schoolYearId = SchoolYear::defaultId();

        $groups = Group::where('subject_id', $request->subject_id)
            ->where('level', $request->level)
            ->where('is_active', true)
            ->when($request->filled('teacher_id'), fn ($q) => $q->where('teacher_id', $request->teacher_id))
            ->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId))
            ->with(['teacher', 'tariffs' => function ($q) {
                $q->where('is_active', true)
                    ->whereDate('effective_from', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('effective_to')
                            ->orWhereDate('effective_to', '>=', now());
                    })
                    ->orderByDesc('effective_from');
            }])
            ->orderBy('name')
            ->get();

        return response()->json($groups);
    }

    // =====================================================
    // VALIDATION
    // =====================================================

    private function validateGroup(Request $request, ?int $exceptGroupId = null): array
    {
        $validated = $request->validate([

            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'level' => 'required|string|in:1AP,2AP,3AP,4AP,5AP,1AM,2AM,3AM,4AM,1AS,2AS,3AS',
            'school_year_id' => 'required|exists:school_years,id',
            'name' => 'required|string|max:255',
            'mode' => 'required|in:normal,special,vip',

            // Tarif
            'effective_from' => 'required|date',
            'billing_type' => 'required|in:monthly,per_session',
            'student_price' => 'required|numeric|min:0',
            'teacher_share' => 'required|numeric|min:0',
            'academy_share' => 'required|numeric|min:0',

            // Planning
            'schedules' => 'required|array|min:1',
            'schedules.*.day' => 'required|string|in:Dimanche,Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i|after:schedules.*.start_time',
            'schedules.*.room' => 'nullable|string|max:255',

        ], [
            'teacher_id.required' => 'Veuillez sélectionner un enseignant.',
            'teacher_id.exists' => 'L\'enseignant sélectionné est invalide.',
            'subject_id.required' => 'Veuillez sélectionner une matière.',
            'subject_id.exists' => 'La matière sélectionnée est invalide.',
            'level.required' => 'Veuillez sélectionner un niveau.',
            'level.in' => 'Le niveau sélectionné est invalide.',
            'school_year_id.required' => 'Veuillez sélectionner une année scolaire.',
            'school_year_id.exists' => 'L\'année scolaire sélectionnée est invalide.',
            'name.required' => 'Le nom du groupe est obligatoire.',
            'mode.required' => 'Veuillez sélectionner un mode.',
            'mode.in' => 'Le mode sélectionné est invalide.',
            'effective_from.required' => 'La date d\'effet est obligatoire.',
            'effective_from.date' => 'La date d\'effet doit être une date valide.',
            'billing_type.required' => 'Veuillez sélectionner un type de facturation.',
            'student_price.required' => 'Le prix étudiant est obligatoire.',
            'student_price.numeric' => 'Le prix étudiant doit être un nombre.',
            'teacher_share.required' => 'La part professeur est obligatoire.',
            'academy_share.required' => 'La part académie est obligatoire.',
            'schedules.required' => 'Veuillez ajouter au moins un créneau horaire.',
            'schedules.min' => 'Veuillez ajouter au moins un créneau horaire.',
        ]);

        // =====================================================
        // VALIDATIONS MÉTIER
        // =====================================================

        $validator = Validator::make(
            $request->all(),
            []
        );

        $validator->after(function ($validator) use ($validated, $request, $exceptGroupId) {

            // 1. Vérifier que student_price == teacher_share + academy_share
            $sum = round((float) $validated['teacher_share'] + (float) $validated['academy_share'], 2);
            $price = round((float) $validated['student_price'], 2);

            if (abs($sum - $price) > 0.01) {
                $validator->errors()->add(
                    'student_price',
                    "Le prix étudiant ({$price} DA) doit être égal à la somme des parts : professeur ({$validated['teacher_share']} DA) + académie ({$validated['academy_share']} DA) = {$sum} DA."
                );
            }

            // 2. Vérifier que l'enseignant est lié à la matière
            $teacher = Teacher::find($validated['teacher_id']);
            $subjectLinked = $teacher->subjects()->where('subjects.id', $validated['subject_id'])->exists();

            if (! $subjectLinked) {
                $validator->errors()->add(
                    'teacher_id',
                    'Cet enseignant n\'est pas lié à la matière sélectionnée.'
                );
            }

            // 3. Vérifier que l'enseignant est lié au cycle du niveau
            $cycleCode = $this->cycleCodeForLevel($validated['level']);

            if ($cycleCode) {
                $levelLinked = $teacher->levels()->where('code', $cycleCode)->exists();

                if (! $levelLinked) {
                    $validator->errors()->add(
                        'level',
                        'Cet enseignant n\'enseigne pas dans le cycle correspondant à ce niveau.'
                    );
                }
            }

            // 4. Vérifier unicité teacher+subject+level+school_year+name
            $duplicateQuery = Group::where([
                'teacher_id' => $validated['teacher_id'],
                'subject_id' => $validated['subject_id'],
                'level' => $validated['level'],
                'school_year_id' => $validated['school_year_id'],
                'name' => $validated['name'],
            ]);

            if ($exceptGroupId) {
                $duplicateQuery->where('id', '!=', $exceptGroupId);
            }

            if ($duplicateQuery->exists()) {
                $validator->errors()->add(
                    'name',
                    'Un groupe avec ce nom existe déjà pour cet enseignant, cette matière, ce niveau et cette année scolaire.'
                );
            }

            // 5. Vérifier les conflits de planning
            if ($validator->errors()->isEmpty()) {
                $schedules = $request->input('schedules', []);
                $excludeGroup = $exceptGroupId ? Group::find($exceptGroupId) : null;

                foreach ($schedules as $index => $schedule) {

                    if (empty($schedule['day']) || empty($schedule['start_time']) || empty($schedule['end_time'])) {
                        continue;
                    }

                    $conflict = $this->hasScheduleConflict(
                        $excludeGroup,
                        (int) $validated['teacher_id'],
                        $schedule['day'],
                        $schedule['start_time'],
                        $schedule['end_time'],
                        $schedule['room'] ?? null
                    );

                    if ($conflict) {
                        $validator->errors()->add(
                            "schedules.{$index}.start_time",
                            $conflict
                        );
                    }
                }
            }
        });

        $validator->validate();

        return $validated;
    }

    // =====================================================
    // HELPERS
    // =====================================================

    private function getCycleFromLevel(string $level): ?string
    {
        $level = strtoupper(trim($level));

        if (in_array($level, ['1AP', '2AP', '3AP', '4AP', '5AP'])) {
            return 'primaire';
        }

        if (in_array($level, ['1AM', '2AM', '3AM', '4AM'])) {
            return 'moyen';
        }

        if (in_array($level, ['1AS', '2AS', '3AS'])) {
            return 'lycee';
        }

        return null;
    }

    private function cycleCodeForLevel(string $level): ?string
    {
        return match ($this->getCycleFromLevel($level)) {
            'primaire' => 'PRI',
            'moyen' => 'MOY',
            'lycee' => 'SEC',
            default => null,
        };
    }

    private function hasScheduleConflict(
        ?Group $excludeGroup,
        int $teacherId,
        string $day,
        string $start,
        string $end,
        ?string $room,
    ): ?string {
        // Conflit enseignant : même enseignant, même jour, horaires chevauchants
        $teacherConflict = GroupSchedule::whereHas('group', function ($q) use ($excludeGroup, $teacherId) {
            $q->where('teacher_id', $teacherId)
                ->where('is_active', true);

            if ($excludeGroup) {
                $q->where('groups.id', '!=', $excludeGroup->id);
            }
        })
            ->where('day', $day)
            ->where('is_active', true)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                    ->orWhereBetween('end_time', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_time', '<=', $start)
                            ->where('end_time', '>=', $end);
                    });
            })
            ->first();

        if ($teacherConflict) {
            $g = $teacherConflict->group;

            return "Conflit avec le groupe \"{$g->name}\" ({$teacherConflict->day} {$teacherConflict->start_time} → {$teacherConflict->end_time}).";
        }

        // Conflit salle : même salle, même jour, horaires chevauchants (seulement si salle renseignée)
        if ($room) {
            $roomConflict = GroupSchedule::whereHas('group', function ($q) use ($excludeGroup) {
                $q->where('is_active', true);

                if ($excludeGroup) {
                    $q->where('groups.id', '!=', $excludeGroup->id);
                }
            })
                ->where('day', $day)
                ->where('room', $room)
                ->where('is_active', true)
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_time', [$start, $end])
                        ->orWhereBetween('end_time', [$start, $end])
                        ->orWhere(function ($q2) use ($start, $end) {
                            $q2->where('start_time', '<=', $start)
                                ->where('end_time', '>=', $end);
                        });
                })
                ->first();

            if ($roomConflict) {
                $g = $roomConflict->group;

                return "Conflit de salle \"{$room}\" avec le groupe \"{$g->name}\" ({$roomConflict->day} {$roomConflict->start_time} → {$roomConflict->end_time}).";
            }
        }

        return null;
    }
}
