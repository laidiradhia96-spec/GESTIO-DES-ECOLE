<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center justify-between gap-4">

            <div class="flex items-center gap-4">

                <div class="w-14 h-14 rounded-2xl
                            bg-[#0B2A55]
                            flex items-center justify-center
                            shadow-lg">

                    <span class="text-3xl">👥</span>

                </div>

                <div>

                    <h2 class="text-2xl font-extrabold
                               text-[#0B2A55]
                               dark:text-white">

                        Groupes pédagogiques

                    </h2>

                    <p class="text-sm text-gray-500 mt-1">

                        {{ $groups->total() }} groupe(s) au total

                    </p>

                </div>

            </div>

            <a href="{{ route('groups.create') }}"
               class="inline-flex items-center gap-2
                      rounded-xl
                      bg-[#0B2A55]
                      px-5 py-3
                      text-sm font-bold text-white
                      shadow-md
                      border-b-4 border-[#C89B3C]
                      hover:bg-[#061A33]
                      hover:-translate-y-0.5
                      transition">

                <span class="text-xl">＋</span>

                Ajouter un groupe

            </a>

        </div>

    </x-slot>


    <div class="min-h-screen py-10
                bg-gradient-to-br
                from-gray-50 via-white to-blue-50
                dark:from-gray-900 dark:to-gray-800">

        <div class="max-w-7xl mx-auto
                    px-4 sm:px-6 lg:px-8">


            <!-- ================================================= -->
            <!-- FILTRES -->
            <!-- ================================================= -->

            <form method="GET"
                  action="{{ route('groups.index') }}"
                  class="bg-white dark:bg-gray-800
                         rounded-3xl
                         shadow-sm
                         border border-gray-100
                         dark:border-gray-700
                         p-5 md:p-6 mb-6">

                <div class="grid grid-cols-1
                            sm:grid-cols-2
                            lg:grid-cols-4
                            gap-4">

                    <!-- Année scolaire -->
                    <div>
                        <label class="block text-xs font-bold
                                      uppercase tracking-wider
                                      text-gray-400 mb-2">
                            Année scolaire
                        </label>
                        <select name="school_year_id"
                                class="w-full px-4 py-3
                                       rounded-xl
                                       border-2 border-gray-200
                                       bg-gray-50
                                       text-sm
                                       outline-none
                                       focus:border-[#C89B3C]">
                            <option value="">Toutes</option>
                            @foreach($schoolYears as $year)
                                <option value="{{ $year->id }}"
                                    {{ $schoolYearId == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Niveau -->
                    <div>
                        <label class="block text-xs font-bold
                                      uppercase tracking-wider
                                      text-gray-400 mb-2">
                            Niveau
                        </label>
                        <select name="level"
                                class="w-full px-4 py-3
                                       rounded-xl
                                       border-2 border-gray-200
                                       bg-gray-50
                                       text-sm
                                       outline-none
                                       focus:border-[#C89B3C]">
                            <option value="">Tous</option>
                            <optgroup label="Primaire">
                                @foreach(['1AP','2AP','3AP','4AP','5AP'] as $l)
                                    <option value="{{ $l }}" {{ request('level') == $l ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Moyen">
                                @foreach(['1AM','2AM','3AM','4AM'] as $l)
                                    <option value="{{ $l }}" {{ request('level') == $l ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Lycée">
                                @foreach(['1AS','2AS','3AS'] as $l)
                                    <option value="{{ $l }}" {{ request('level') == $l ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <!-- Mode -->
                    <div>
                        <label class="block text-xs font-bold
                                      uppercase tracking-wider
                                      text-gray-400 mb-2">
                            Mode
                        </label>
                        <select name="mode"
                                class="w-full px-4 py-3
                                       rounded-xl
                                       border-2 border-gray-200
                                       bg-gray-50
                                       text-sm
                                       outline-none
                                       focus:border-[#C89B3C]">
                            <option value="">Tous</option>
                            <option value="normal" {{ request('mode') == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="special" {{ request('mode') == 'special' ? 'selected' : '' }}>Spécial</option>
                            <option value="vip" {{ request('mode') == 'vip' ? 'selected' : '' }}>VIP</option>
                        </select>
                    </div>

                    <!-- Statut -->
                    <div>
                        <label class="block text-xs font-bold
                                      uppercase tracking-wider
                                      text-gray-400 mb-2">
                            Statut
                        </label>
                        <select name="is_active"
                                class="w-full px-4 py-3
                                       rounded-xl
                                       border-2 border-gray-200
                                       bg-gray-50
                                       text-sm
                                       outline-none
                                       focus:border-[#C89B3C]">
                            <option value="">Tous</option>
                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Actif</option>
                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>

                </div>

                <div class="flex items-center gap-3 mt-4">

                    <!-- Recherche -->
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Rechercher un groupe, enseignant, matière..."
                           class="flex-1 px-4 py-3
                                  rounded-xl
                                  border-2 border-gray-200
                                  bg-gray-50
                                  text-sm
                                  outline-none
                                  focus:border-[#C89B3C]">

                    <button type="submit"
                            class="px-6 py-3
                                   rounded-xl
                                   bg-[#0B2A55]
                                   text-white
                                   font-bold
                                   text-sm
                                   hover:bg-[#061A33]
                                   transition">
                        Filtrer
                    </button>

                    <a href="{{ route('groups.index') }}"
                       class="px-6 py-3
                              rounded-xl
                              bg-gray-100
                              text-gray-700
                              font-bold
                              text-sm
                              hover:bg-gray-200
                              transition">
                        Réinitialiser
                    </a>

                </div>

            </form>


            <!-- ================================================= -->
            <!-- TABLEAU -->
            <!-- ================================================= -->

            <div class="bg-white dark:bg-gray-800
                        rounded-3xl
                        shadow-sm
                        border border-gray-100
                        dark:border-gray-700
                        overflow-hidden">

                @if($groups->count())

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-900">
                                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase tracking-wider">Groupe</th>
                                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase tracking-wider">Enseignant</th>
                                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase tracking-wider">Matière</th>
                                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase tracking-wider">Niveau</th>
                                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase tracking-wider">Année</th>
                                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase tracking-wider">Mode</th>
                                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase tracking-wider">Tarif</th>
                                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase tracking-wider">Créneaux</th>
                                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($groups as $group)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="font-extrabold text-[#0B2A55]">
                                                {{ $group->name }}
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1">
                                                ID: {{ $group->id }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $group->teacher->first_name }} {{ $group->teacher->last_name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $group->subject->name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-3 py-1
                                                         rounded-full text-xs font-bold
                                                         bg-blue-100 text-blue-800">
                                                {{ $group->level }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500">
                                            {{ $group->schoolYear->name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($group->mode === 'vip')
                                                <span class="inline-flex items-center px-3 py-1
                                                             rounded-full text-xs font-bold
                                                             bg-purple-100 text-purple-800">
                                                    VIP
                                                </span>
                                            @elseif($group->mode === 'special')
                                                <span class="inline-flex items-center px-3 py-1
                                                             rounded-full text-xs font-bold
                                                             bg-amber-100 text-amber-800">
                                                    Spécial
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1
                                                             rounded-full text-xs font-bold
                                                             bg-green-100 text-green-800">
                                                    Normal
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($group->currentTariff)
                                                <div class="font-bold text-[#0B2A55]">
                                                    {{ number_format($group->currentTariff->student_price, 0, ',', ' ') }} DA
                                                </div>
                                                <div class="text-xs text-gray-400">
                                                    {{ $group->currentTariff->billing_type === 'monthly' ? '/mois' : '/séance' }}
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-[#0B2A55]">
                                                {{ $group->schedules->count() }}
                                            </span>
                                            <span class="text-gray-400 text-xs"> créneau(x)</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($group->is_active)
                                                <span class="inline-flex items-center gap-1.5
                                                             px-3 py-1
                                                             rounded-full text-xs font-bold
                                                             bg-green-100 text-green-800">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                    Actif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5
                                                             px-3 py-1
                                                             rounded-full text-xs font-bold
                                                             bg-red-100 text-red-800">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                    Inactif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('groups.show', $group) }}"
                                                   class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition"
                                                   title="Voir">
                                                    👁️
                                                </a>
                                                <a href="{{ route('groups.edit', $group) }}"
                                                   class="p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition"
                                                   title="Modifier">
                                                    ✏️
                                                </a>
                                                <form action="{{ route('groups.toggle-status', $group) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="p-2 rounded-lg {{ $group->is_active ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100' }} transition"
                                                            title="{{ $group->is_active ? 'Désactiver' : 'Activer' }}">
                                                        {{ $group->is_active ? '🔴' : '🟢' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $groups->withQueryString() }}
                    </div>

                @else

                    <div class="p-12 text-center">
                        <div class="text-5xl mb-4">👥</div>
                        <h3 class="text-lg font-bold text-gray-600 mb-2">
                            Aucun groupe trouvé
                        </h3>
                        <p class="text-gray-400 mb-6">
                            @if(request()->hasAny(['search', 'level', 'mode', 'is_active', 'school_year_id', 'teacher_id', 'subject_id']))
                                Aucun résultat pour les filtres appliqués.
                            @else
                                Créez votre premier groupe pédagogique.
                            @endif
                        </p>
                        <a href="{{ route('groups.create') }}"
                           class="inline-flex items-center gap-2
                                  rounded-xl bg-[#0B2A55]
                                  px-6 py-3
                                  text-sm font-bold text-white
                                  hover:bg-[#061A33] transition">
                            ＋ Créer un groupe
                        </a>
                    </div>

                @endif

            </div>


            <!-- Footer -->
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-400">
                    Académie El Tafawok • Groupes pédagogiques
                </p>
            </div>

        </div>

    </div>

</x-app-layout>
