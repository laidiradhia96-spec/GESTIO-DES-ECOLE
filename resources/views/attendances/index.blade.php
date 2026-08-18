<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Liste des présences
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Consulter et filtrer les présences des élèves
                </p>
            </div>

            <a href="{{ route('attendances.create') }}"
               class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl
                      text-white font-semibold shadow-md hover:opacity-90 transition"
               style="background-color: #0B2A55;">

                + Enregistrer une présence

            </a>

        </div>

    </x-slot>


    <div class="py-10 bg-gray-100 dark:bg-gray-900 min-h-screen">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- Message succès --}}
            @if (session('success'))

                <div class="mb-6 bg-green-100 border border-green-300
                            text-green-700 px-5 py-4 rounded-xl">

                    {{ session('success') }}

                </div>

            @endif


            {{-- Filtres --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 mb-6">

                <div class="flex items-center gap-3 mb-6">

                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center text-white"
                        style="background-color: #0B2A55;">

                        🔎

                    </div>

                    <div>

                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            Recherche et filtres
                        </h3>

                        <p class="text-sm text-gray-500">
                            Filtrer les présences
                        </p>

                    </div>

                </div>


                <form method="GET" action="{{ route('attendances.index') }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5">


                        {{-- Recherche élève --}}
                        <div>

                            <label
                                for="student"
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">

                                Élève

                            </label>

                            <input
                                type="text"
                                id="student"
                                name="student"
                                value="{{ request('student') }}"
                                placeholder="Nom ou prénom..."
                                class="w-full rounded-xl border-gray-300
                                       dark:border-gray-600
                                       dark:bg-gray-700
                                       dark:text-white
                                       focus:border-[#0B2A55]
                                       focus:ring-[#0B2A55]"
                            >

                        </div>


                        {{-- Date --}}
                        <div>

                            <label
                                for="date"
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">

                                Date

                            </label>

                            <input
                                type="date"
                                id="date"
                                name="date"
                                value="{{ request('date') }}"
                                class="w-full rounded-xl border-gray-300
                                       dark:border-gray-600
                                       dark:bg-gray-700
                                       dark:text-white
                                       focus:border-[#0B2A55]
                                       focus:ring-[#0B2A55]"
                            >

                        </div>


                        {{-- Matière --}}
                        <div>

                            <label
                                for="subject_id"
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">

                                Matière

                            </label>

                            <select
                                id="subject_id"
                                name="subject_id"
                                class="w-full rounded-xl border-gray-300
                                       dark:border-gray-600
                                       dark:bg-gray-700
                                       dark:text-white
                                       focus:border-[#0B2A55]
                                       focus:ring-[#0B2A55]"
                            >

                                <option value="">
                                    Toutes les matières
                                </option>

                                @foreach ($subjects as $subject)

                                    <option
                                        value="{{ $subject->id }}"
                                        {{ request('subject_id') == $subject->id ? 'selected' : '' }}>

                                        {{ $subject->name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Statut --}}
                        <div>

                            <label
                                for="status"
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">

                                Statut

                            </label>

                            <select
                                id="status"
                                name="status"
                                class="w-full rounded-xl border-gray-300
                                       dark:border-gray-600
                                       dark:bg-gray-700
                                       dark:text-white
                                       focus:border-[#0B2A55]
                                       focus:ring-[#0B2A55]"
                            >

                                <option value="">
                                    Tous les statuts
                                </option>

                                <option
                                    value="present"
                                    {{ request('status') == 'present' ? 'selected' : '' }}>

                                    Présent

                                </option>

                                <option
                                    value="absent"
                                    {{ request('status') == 'absent' ? 'selected' : '' }}>

                                    Absent

                                </option>

                                <option
                                    value="late"
                                    {{ request('status') == 'late' ? 'selected' : '' }}>

                                    Retard

                                </option>

                                <option
                                    value="justified"
                                    {{ request('status') == 'justified' ? 'selected' : '' }}>

                                    Justifié

                                </option>

                            </select>

                        </div>


                        {{-- Année scolaire --}}
                        <div>

                            <label
                                for="school_year_id"
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">

                                Année scolaire

                            </label>

                            <select
                                id="school_year_id"
                                name="school_year_id"
                                class="w-full rounded-xl border-gray-300
                                       dark:border-gray-600
                                       dark:bg-gray-700
                                       dark:text-white
                                       focus:border-[#0B2A55]
                                       focus:ring-[#0B2A55]"
                            >

                                <option value=""
                                    {{ $schoolYearId === null ? 'selected' : '' }}>
                                    Toutes les années
                                </option>

                                @foreach ($schoolYears as $schoolYear)

                                    <option
                                        value="{{ $schoolYear->id }}"
                                        {{ $schoolYearId === $schoolYear->id ? 'selected' : '' }}>

                                        {{ $schoolYear->name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                    </div>


                    <div class="flex flex-wrap gap-3 mt-6">

                        <button
                            type="submit"
                            class="px-5 py-2.5 rounded-xl
                                   text-white font-semibold
                                   shadow hover:opacity-90 transition"
                            style="background-color: #0B2A55;">

                            🔎 Rechercher

                        </button>


                        <a
                            href="{{ route('attendances.index') }}"
                            class="px-5 py-2.5 rounded-xl
                                   font-semibold
                                   border border-gray-300
                                   dark:border-gray-600
                                   text-gray-700
                                   dark:text-gray-200
                                   hover:bg-gray-100
                                   dark:hover:bg-gray-700
                                   transition">

                            Réinitialiser

                        </a>

                    </div>

                </form>

            </div>


            {{-- Tableau --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">


                {{-- Header --}}
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">


                        {{-- Titre --}}
                        <div class="flex items-center gap-3">

                            <div
                                class="w-11 h-11 rounded-xl
                                       flex items-center justify-center
                                       text-white text-xl shadow-sm"
                                style="background-color: #0B2A55;">

                                📋

                            </div>


                            <div>

                                <h3 class="text-lg font-extrabold text-[#0B2A55] dark:text-gray-100">

                                    Présences enregistrées

                                </h3>

                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">

                                    Consultez, modifiez ou supprimez les présences.

                                </p>

                            </div>

                        </div>


                        {{-- Nombre d'enregistrements --}}
                        <div
                            class="inline-flex items-center justify-center
                                   px-4 py-2 rounded-xl
                                   font-semibold text-sm
                                   shadow-sm whitespace-nowrap"
                            style="background-color: #C89B3C; color: white;">

                            {{ $attendances->total() }} enregistrements

                        </div>

                    </div>

                </div>


                @if ($attendances->count() > 0)


                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead style="background-color: #0B2A55;">

                                <tr class="text-white text-sm">

                                    <th class="px-6 py-4 text-left">
                                        #
                                    </th>

                                    <th class="px-6 py-4 text-left">
                                        Élève
                                    </th>

                                    <th class="px-6 py-4 text-left">
                                        Matière
                                    </th>

                                    <th class="px-6 py-4 text-left">
                                        Enseignant
                                    </th>

                                    <th class="px-6 py-4 text-center">
                                        Date
                                    </th>

                                    <th class="px-6 py-4 text-center">
                                        Statut
                                    </th>

                                    <th class="px-6 py-4 text-center">
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">


                                @foreach ($attendances as $attendance)

                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">


                                        {{-- Numéro --}}
                                        <td class="px-6 py-4 text-sm text-gray-500">

                                            {{ $loop->iteration + ($attendances->currentPage() - 1) * $attendances->perPage() }}

                                        </td>


                                        {{-- Élève --}}
                                        <td class="px-6 py-4">

                                            <div class="font-semibold text-gray-800 dark:text-gray-100">

                                                {{ $attendance->student->first_name }}
                                                {{ $attendance->student->last_name }}

                                            </div>

                                        </td>


                                        {{-- Matière --}}
                                        <td class="px-6 py-4">

                                            <span class="text-gray-700 dark:text-gray-300">

                                                {{ $attendance->subject->name }}

                                            </span>

                                        </td>


                                        {{-- Enseignant --}}
                                        <td class="px-6 py-4">

                                            @if ($attendance->teacher)

                                                <span class="text-gray-700 dark:text-gray-300">

                                                    {{ $attendance->teacher->first_name }}
                                                    {{ $attendance->teacher->last_name }}

                                                </span>

                                            @else

                                                <span class="text-gray-400">
                                                    —
                                                </span>

                                            @endif

                                        </td>


                                        {{-- Date --}}
                                        <td class="px-6 py-4 text-center">

                                            <span class="text-gray-700 dark:text-gray-300">

                                                {{ $attendance->date->format('d/m/Y') }}

                                            </span>

                                        </td>


                                        {{-- Statut --}}
                                        <td class="px-6 py-4 text-center">


                                            @if ($attendance->status === 'present')

                                                <span
                                                    class="inline-flex items-center
                                                           px-3 py-1 rounded-full
                                                           text-xs font-semibold
                                                           bg-green-100 text-green-700">

                                                    🟢 Présent

                                                </span>


                                            @elseif ($attendance->status === 'absent')

                                                <span
                                                    class="inline-flex items-center
                                                           px-3 py-1 rounded-full
                                                           text-xs font-semibold
                                                           bg-red-100 text-red-700">

                                                    🔴 Absent

                                                </span>


                                            @elseif ($attendance->status === 'late')

                                                <span
                                                    class="inline-flex items-center
                                                           px-3 py-1 rounded-full
                                                           text-xs font-semibold
                                                           bg-yellow-100 text-yellow-700">

                                                    🟠 Retard

                                                </span>


                                            @elseif ($attendance->status === 'justified')

                                                <span
                                                    class="inline-flex items-center
                                                           px-3 py-1 rounded-full
                                                           text-xs font-semibold
                                                           bg-blue-100 text-blue-700">

                                                    🔵 Justifié

                                                </span>

                                            @endif

                                        </td>


                                        {{-- Action --}}
                                        <td class="px-6 py-4 text-center">

                                            <div class="flex items-center justify-center gap-2">

                                                <a
                                                    href="{{ route('attendances.show', $attendance) }}"
                                                    class="inline-flex items-center
                                                           px-3 py-2 rounded-lg
                                                           text-white text-sm
                                                           font-semibold
                                                           hover:opacity-90
                                                           transition"
                                                    style="background-color: #0B2A55;">

                                                    👁️ Voir

                                                </a>

                                                <form
                                                    method="POST"
                                                    action="{{ route('attendances.destroy', $attendance) }}"
                                                    onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette présence ?');"
                                                >

                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center
                                                               px-3 py-2 rounded-lg
                                                               bg-red-600 text-white text-sm
                                                               font-semibold
                                                               hover:bg-red-700
                                                               transition">

                                                        🗑️ Supprimer

                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>


                    {{-- Pagination --}}
                    <div class="p-6 border-t border-gray-200 dark:border-gray-700">

                        {{ $attendances->withQueryString()->links() }}

                    </div>


                @else


                    {{-- Empty State --}}
                    <div class="p-12 text-center">

                        <div class="text-5xl mb-4">
                            📋
                        </div>

                        <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200">

                            Aucune présence trouvée

                        </h3>

                        <p class="text-sm text-gray-500 mt-2">

                            Aucun enregistrement ne correspond aux critères sélectionnés.

                        </p>


                        <a
                            href="{{ route('attendances.create') }}"
                            class="inline-flex mt-6
                                   px-5 py-2.5 rounded-xl
                                   text-white font-semibold
                                   shadow hover:opacity-90 transition"
                            style="background-color: #0B2A55;">

                            + Enregistrer une présence

                        </a>

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>