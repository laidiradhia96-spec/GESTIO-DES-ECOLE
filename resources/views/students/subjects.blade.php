<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-8 rounded-full" style="background-color:#C89B3C;"></span>
                    <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100">
                        Mes matières
                    </h2>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-4">
                    Historique complet de vos matières
                </p>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- FILTRES --}}
            <div class="rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm bg-white dark:bg-gray-800 p-6">

                <div class="flex items-center justify-between gap-4 mb-5">

                    <div>

                        <h3 class="text-lg
                                   font-extrabold
                                   text-[#0B2A55] dark:text-gray-100">
                            Filtrer vos matières
                        </h3>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Filtrez par année scolaire.
                        </p>

                    </div>

                </div>


                <form method="GET"
                      action="{{ route('student.subjects') }}">

                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">


                        {{-- Année scolaire --}}
                        <div class="md:col-span-3">

                            <label class="block
                                          text-sm
                                          font-bold
                                          text-gray-700
                                          dark:text-gray-200
                                          mb-2">
                                Année scolaire
                            </label>

                            <select
                                name="school_year_id"
                                class="w-full
                                       rounded-xl
                                       border-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#0B2A55]"
                            >

                                <option value=""
                                    {{ request('school_year_id') === null ? 'selected' : '' }}>
                                    Toutes les années
                                </option>

                                @foreach($schoolYears as $schoolYear)

                                    <option value="{{ $schoolYear->id }}"
                                        {{ request('school_year_id') == $schoolYear->id ? 'selected' : '' }}>
                                        {{ $schoolYear->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Actions --}}
                        <div class="flex items-end md:col-span-3">

                            <div class="flex flex-wrap gap-3">

                                <button
                                    type="submit"
                                    class="px-6 py-3
                                           rounded-xl
                                           bg-[#0B2A55]
                                           text-white
                                           font-bold
                                           hover:bg-[#061A33]
                                           transition">

                                    🔎 Rechercher

                                </button>


                                <a href="{{ route('student.subjects') }}"
                                   class="px-6 py-3
                                          rounded-xl
                                          bg-gray-100
                                          text-gray-700
                                          font-bold
                                          hover:bg-gray-200
                                          transition">

                                    Réinitialiser les filtres

                                </a>

                            </div>

                        </div>

                    </div>

                </form>

            </div>


            {{-- TABLEAU --}}
            <div class="mt-6 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm bg-white dark:bg-gray-800">

                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-extrabold text-gray-800 dark:text-gray-100">
                                📚 Historique des matières
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Toutes vos inscriptions, passées et actuelles
                            </p>
                        </div>

                        <span class="hidden sm:inline-flex px-3 py-1.5 rounded-xl text-xs font-bold"
                              style="background-color:#EEF3F9;color:#0B2A55;">
                            {{ $enrollments->total() }} enregistrement(s)
                        </span>
                    </div>
                </div>

                @if ($enrollments->count() > 0)

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead style="background-color:#0B2A55;">
                                <tr class="text-white text-xs uppercase tracking-wide">
                                    <th class="px-6 py-4 text-left">Matière</th>
                                    <th class="px-6 py-4 text-left">Enseignant</th>
                                    <th class="px-6 py-4 text-left">Niveau</th>
                                    <th class="px-6 py-4 text-left">Année scolaire</th>
                                    <th class="px-6 py-4 text-center">Statut</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">

                                @foreach ($enrollments as $enrollment)

                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">

                                        <td class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-200">
                                            {{ $enrollment->subject->name }}
                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            @if ($enrollment->teacher)
                                                {{ $enrollment->teacher->first_name }}
                                                {{ $enrollment->teacher->last_name }}
                                            @else
                                                —
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $student->level }}
                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $enrollment->schoolYear?->name ?? '—' }}
                                        </td>

                                        <td class="px-6 py-4 text-center">

                                            @if ($enrollment->status === 'active')
                                                <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                                    ✅ Active
                                                </span>
                                            @else
                                                <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                                    ⏳ Ancienne
                                                </span>
                                            @endif

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                        {{ $enrollments->links() }}
                    </div>

                @else

                    <div class="p-10 text-center">
                        <div class="text-5xl mb-4">📚</div>
                        <p class="font-semibold text-gray-700 dark:text-gray-200">
                            Aucune matière enregistrée
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            Modifiez ou réinitialisez vos filtres pour afficher vos matières.
                        </p>
                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>