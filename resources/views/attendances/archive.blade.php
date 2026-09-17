<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div class="flex items-center gap-4">

                <div class="w-14 h-14 rounded-2xl bg-[#0B2A55]
                            flex items-center justify-center shadow-md">
                    <span class="text-2xl">📋</span>
                </div>

                <div>
                    <h2 class="font-extrabold text-2xl text-[#0B2A55]">
                        Archive des présences
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Rechercher et consulter l'historique de présence des élèves.
                    </p>
                </div>

            </div>

            <a href="{{ route('attendances.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                      bg-gray-100 text-gray-700 font-semibold
                      hover:bg-gray-200 transition">

                ← Retour aux présences

            </a>

        </div>

    </x-slot>


    <div class="py-8 bg-gray-50 min-h-screen">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- =========================
                RECHERCHE D'ÉLÈVE
            ========================== --}}

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-8">

                <div class="flex items-center gap-3 mb-5">

                    <div class="w-10 h-10 rounded-xl bg-[#0B2A55]
                                flex items-center justify-center text-xl">
                        🔎
                    </div>

                    <div>
                        <h3 class="text-lg font-extrabold text-[#0B2A55]">
                            Rechercher un élève
                        </h3>
                        <p class="text-sm text-gray-500">
                            Tapez le nom ou prénom pour trouver un élève.
                        </p>
                    </div>

                </div>

                <form method="GET" action="{{ route('attendances.archive') }}">

                    <div class="flex gap-3">

                        <input type="text"
                               name="search"
                               value="{{ $filters['search'] ?? '' }}"
                               placeholder="Nom ou prénom de l'élève..."
                               class="flex-1 rounded-xl border-gray-300
                                      focus:border-[#0B2A55]
                                      focus:ring-[#0B2A55]">

                        <button type="submit"
                                class="px-6 py-3 rounded-xl bg-[#0B2A55]
                                       text-white font-bold
                                       hover:bg-[#061A33] transition">
                            🔎 Rechercher
                        </button>

                    </div>

                </form>

            </div>



            {{-- =========================
                RÉSULTATS DE RECHERCHE
            ========================== --}}

            @if($students->count() > 0 && !$student)

                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-8">

                    <div class="flex items-center gap-3 mb-5">

                        <div class="w-10 h-10 rounded-xl bg-blue-50
                                    flex items-center justify-center text-xl">
                            👨‍🎓
                        </div>

                        <div>
                            <h3 class="text-lg font-extrabold text-[#0B2A55]">
                                {{ $students->count() }} élève(s) trouvé(s)
                            </h3>
                            <p class="text-sm text-gray-500">
                                Sélectionnez un élève pour voir son historique.
                            </p>
                        </div>

                    </div>

                    <div class="divide-y divide-gray-100">

                        @foreach($students as $s)

                            <a href="{{ route('attendances.archive', array_merge(
                                $filters,
                                ['student_id' => $s->id, 'search' => '']
                            )) }}"
                               class="flex items-center justify-between py-4 px-4
                                      rounded-xl hover:bg-gray-50 transition">

                                <div class="flex items-center gap-4">

                                    <div class="w-11 h-11 rounded-xl bg-blue-50
                                                flex items-center justify-center text-xl">
                                        👨‍🎓
                                    </div>

                                    <div>
                                        <p class="font-bold text-gray-800">
                                            {{ $s->first_name }} {{ $s->last_name }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $s->level ?? '-' }}
                                            @if($s->parent_name)
                                                · Parent : {{ $s->parent_name }}
                                            @endif
                                        </p>
                                    </div>

                                </div>

                                <span class="text-sm text-[#0B2A55] font-semibold">
                                    Voir l'historique →
                                </span>

                            </a>

                        @endforeach

                    </div>

                </div>

            @endif



            {{-- =========================
                ÉLÈVE SÉLECTIONNÉ + FILTRES
            ========================== --}}

            @if($student)

                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-8">

                    <div class="flex items-center justify-between mb-5">

                        <div class="flex items-center gap-4">

                            <div class="w-12 h-12 rounded-xl bg-[#C89B3C]
                                        flex items-center justify-center text-2xl">
                                👨‍🎓
                            </div>

                            <div>
                                <h3 class="text-lg font-extrabold text-[#0B2A55]">
                                    {{ $student->first_name }} {{ $student->last_name }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ $student->level ?? '' }}
                                    @if($student->parent_name)
                                        · Parent : {{ $student->parent_name }}
                                    @endif
                                </p>
                            </div>

                        </div>

                        <a href="{{ route('attendances.archive', ['search' => $filters['search'] ?? '']) }}"
                           class="text-sm text-gray-500 hover:text-gray-700 font-semibold">
                            ✕ Changer d'élève
                        </a>

                    </div>


                    {{-- Filtres --}}
                    <form method="GET" action="{{ route('attendances.archive') }}">

                        <input type="hidden" name="student_id" value="{{ $student->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">


                            {{-- Année scolaire --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Année scolaire
                                </label>
                                <select name="school_year_id"
                                        class="w-full rounded-xl border-gray-300
                                               focus:border-[#0B2A55]
                                               focus:ring-[#0B2A55]">
                                    <option value="">Toutes les années</option>
                                    @foreach($schoolYears as $year)
                                        <option value="{{ $year->id }}"
                                            {{ ($filters['school_year_id'] ?? '') == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            {{-- Groupe --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Groupe
                                </label>
                                <select name="group_id"
                                        class="w-full rounded-xl border-gray-300
                                               focus:border-[#0B2A55]
                                               focus:ring-[#0B2A55]">
                                    <option value="">Tous les groupes</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}"
                                            {{ ($filters['group_id'] ?? '') == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            {{-- Matière --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Matière
                                </label>
                                <select name="subject_id"
                                        class="w-full rounded-xl border-gray-300
                                               focus:border-[#0B2A55]
                                               focus:ring-[#0B2A55]">
                                    <option value="">Toutes les matières</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}"
                                            {{ ($filters['subject_id'] ?? '') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            {{-- Enseignant --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Enseignant
                                </label>
                                <select name="teacher_id"
                                        class="w-full rounded-xl border-gray-300
                                               focus:border-[#0B2A55]
                                               focus:ring-[#0B2A55]">
                                    <option value="">Tous les enseignants</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}"
                                            {{ ($filters['teacher_id'] ?? '') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->first_name }} {{ $teacher->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            {{-- Date début --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Date début
                                </label>
                                <input type="date"
                                       name="date_from"
                                       value="{{ $filters['date_from'] ?? '' }}"
                                       class="w-full rounded-xl border-gray-300
                                              focus:border-[#0B2A55]
                                              focus:ring-[#0B2A55]">
                            </div>


                            {{-- Date fin --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Date fin
                                </label>
                                <input type="date"
                                       name="date_to"
                                       value="{{ $filters['date_to'] ?? '' }}"
                                       class="w-full rounded-xl border-gray-300
                                              focus:border-[#0B2A55]
                                              focus:ring-[#0B2A55]">
                            </div>


                            {{-- Statut --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Statut
                                </label>
                                <select name="status"
                                        class="w-full rounded-xl border-gray-300
                                               focus:border-[#0B2A55]
                                               focus:ring-[#0B2A55]">
                                    <option value="">Tous les statuts</option>
                                    <option value="present"
                                        {{ ($filters['status'] ?? '') === 'present' ? 'selected' : '' }}>
                                        🟢 Présent
                                    </option>
                                    <option value="absent"
                                        {{ ($filters['status'] ?? '') === 'absent' ? 'selected' : '' }}>
                                        🔴 Absent
                                    </option>
                                    <option value="late"
                                        {{ ($filters['status'] ?? '') === 'late' ? 'selected' : '' }}>
                                        🟠 Retard
                                    </option>
                                    <option value="justified"
                                        {{ ($filters['status'] ?? '') === 'justified' ? 'selected' : '' }}>
                                        🔵 Justifié
                                    </option>
                                </select>
                            </div>


                            {{-- Actions --}}
                            <div class="flex items-end gap-2">

                                <button type="submit"
                                        class="px-5 py-3 rounded-xl bg-[#0B2A55]
                                               text-white font-bold
                                               hover:bg-[#061A33] transition">
                                    🔎 Filtrer
                                </button>

                                <a href="{{ route('attendances.archive', ['student_id' => $student->id]) }}"
                                   class="px-5 py-3 rounded-xl bg-gray-100
                                          text-gray-700 font-bold
                                          hover:bg-gray-200 transition">
                                    ↻
                                </a>

                            </div>

                        </div>

                    </form>

                </div>



                {{-- =========================
                    TABLEAU DES PRÉSENCES
                ========================== --}}

                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">

                    <div class="p-6 border-b border-gray-100
                                flex flex-col md:flex-row
                                md:items-center md:justify-between gap-3">

                        <div>
                            <h3 class="text-xl font-bold text-[#0B2A55]">
                                Historique des présences
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </p>
                        </div>

                        <div class="text-sm text-gray-500">
                            {{ $attendances->total() }} enregistrement(s)
                        </div>

                    </div>


                    @if($attendances->count() > 0)

                        <div class="overflow-x-auto">

                            <table class="w-full text-left">

                                <thead class="bg-gray-50">

                                    <tr>

                                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                            Date
                                        </th>

                                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                            Groupe
                                        </th>

                                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                            Matière
                                        </th>

                                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                            Enseignant
                                        </th>

                                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                            Statut
                                        </th>

                                    </tr>

                                </thead>

                                <tbody class="divide-y divide-gray-100">

                                    @foreach($attendances as $attendance)

                                        <tr class="hover:bg-gray-50 transition">

                                            <td class="px-6 py-5 text-sm text-gray-700 whitespace-nowrap">
                                                {{ $attendance->date->format('d/m/Y') }}
                                            </td>

                                            <td class="px-6 py-5">
                                                @if($attendance->group)
                                                    <span class="inline-flex px-3 py-1 rounded-full
                                                                 bg-green-50 text-green-700
                                                                 text-xs font-bold">
                                                        {{ $attendance->group->name }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-5 font-semibold text-gray-700">
                                                {{ $attendance->subject->name ?? '-' }}
                                            </td>

                                            <td class="px-6 py-5 text-sm text-gray-600">
                                                @if($attendance->teacher)
                                                    {{ $attendance->teacher->first_name }}
                                                    {{ $attendance->teacher->last_name }}
                                                @else
                                                    —
                                                @endif
                                            </td>

                                            <td class="px-6 py-5">
                                                @if($attendance->status === 'present')
                                                    <span class="inline-flex items-center gap-1
                                                                 px-3 py-1.5
                                                                 rounded-full
                                                                 bg-green-100
                                                                 text-green-700
                                                                 text-xs font-bold">
                                                        🟢 Présent
                                                    </span>
                                                @elseif($attendance->status === 'absent')
                                                    <span class="inline-flex items-center gap-1
                                                                 px-3 py-1.5
                                                                 rounded-full
                                                                 bg-red-100
                                                                 text-red-700
                                                                 text-xs font-bold">
                                                        🔴 Absent
                                                    </span>
                                                @elseif($attendance->status === 'late')
                                                    <span class="inline-flex items-center gap-1
                                                                 px-3 py-1.5
                                                                 rounded-full
                                                                 bg-yellow-100
                                                                 text-yellow-700
                                                                 text-xs font-bold">
                                                        🟠 Retard
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1
                                                                 px-3 py-1.5
                                                                 rounded-full
                                                                 bg-blue-100
                                                                 text-blue-700
                                                                 text-xs font-bold">
                                                        🔵 Justifié
                                                    </span>
                                                @endif
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>


                        <div class="p-6 border-t border-gray-100">
                            {{ $attendances->links() }}
                        </div>


                    @else

                        <div class="text-center py-16 px-6">

                            <div class="w-20 h-20 mx-auto rounded-2xl bg-gray-100
                                        flex items-center justify-center text-4xl mb-5">
                                📋
                            </div>

                            <h3 class="text-xl font-bold text-[#0B2A55]">
                                Aucune présence trouvée
                            </h3>

                            <p class="text-gray-500 mt-2">
                                Aucun enregistrement ne correspond aux critères sélectionnés.
                            </p>

                        </div>

                    @endif

                </div>

            @endif



            {{-- =========================
                ÉTAT INITIAL (aucune recherche)
            ========================== --}}

            @if(!$student && $students->isEmpty() && !$filters['search'])

                <div class="text-center py-16">

                    <div class="w-20 h-20 mx-auto rounded-2xl bg-gray-100
                                flex items-center justify-center text-4xl mb-5">
                        📋
                    </div>

                    <h3 class="text-xl font-bold text-[#0B2A55]">
                        Archive des présences
                    </h3>

                    <p class="text-gray-500 mt-2 max-w-md mx-auto">
                        Recherchez un élève par son nom ou prénom pour afficher
                        son historique complet de présence.
                    </p>

                </div>

            @endif


        </div>

    </div>

</x-app-layout>
