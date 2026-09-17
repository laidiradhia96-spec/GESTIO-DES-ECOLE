<x-app-layout>

{{-- HEADER --}}
<x-slot name="header">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div>

            <div class="flex items-center gap-3">

                <div class="w-11 h-11 rounded-xl bg-[#0B2A55] flex items-center justify-center text-white text-xl shadow-sm">
                    📚
                </div>

                <div>

                    <h2 class="text-2xl font-extrabold text-[#0B2A55]">
                        Matières
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Gestion des matières de l'académie
                    </p>

                </div>

            </div>

        </div>


        <a href="{{ route('subjects.create') }}"
           class="inline-flex items-center justify-center gap-2
                  px-5 py-3
                  rounded-xl
                  bg-[#2563EB]
                  hover:bg-[#1D4ED8]
                  text-white
                  font-bold
                  shadow-sm
                  transition">

            <span class="text-lg">+</span>

            Ajouter une matière

        </a>

    </div>

</x-slot>


{{-- MAIN --}}
<div class="min-h-screen bg-gray-50 py-8">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


        {{-- SUCCESS --}}
        @if(session('success'))

            <div class="mb-6 flex items-center gap-3
                        rounded-xl
                        border border-green-200
                        bg-green-50
                        px-5 py-4
                        text-green-700">

                <span class="text-xl">
                    ✓
                </span>

                <span class="font-semibold">
                    {{ session('success') }}
                </span>

            </div>

        @endif


        {{-- ERROR --}}
        @if(session('error'))

            <div class="mb-6 flex items-center gap-3
                        rounded-xl
                        border border-red-200
                        bg-red-50
                        px-5 py-4
                        text-red-700">

                <span class="text-xl">
                    ⚠
                </span>

                <span class="font-semibold">
                    {{ session('error') }}
                </span>

            </div>

        @endif


        {{-- FILTRE ANNÉE SCOLAIRE --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 mb-8">

            <form method="GET" action="{{ route('subjects.index') }}">

                <div class="flex flex-col sm:flex-row gap-4 items-end">

                    <div class="w-full sm:w-64">

                        <label class="block text-sm font-bold text-[#0B2A55] mb-2">
                            Année scolaire
                        </label>

                        <select
                            name="school_year_id"
                            class="w-full rounded-xl border-2 border-gray-200
                                   bg-gray-50 px-4 py-2.5
                                   text-gray-700
                                   outline-none
                                   transition
                                   focus:border-[#C89B3C]
                                   focus:ring-4
                                   focus:ring-[#C89B3C]/10">

                            <option value=""
                                {{ $schoolYearId === null ? 'selected' : '' }}>
                                Toutes les années
                            </option>

                            @foreach($schoolYears as $schoolYear)

                                <option value="{{ $schoolYear->id }}"
                                    {{ $schoolYearId === $schoolYear->id ? 'selected' : '' }}>
                                    {{ $schoolYear->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2
                               rounded-xl
                               bg-[#0B2A55]
                               px-6 py-3
                               text-sm font-bold text-white
                               shadow-md
                               hover:bg-[#061A33]
                               transition">

                        🔎 Rechercher

                    </button>

                    <a
                        href="{{ route('subjects.index') }}"
                        class="inline-flex items-center justify-center gap-2
                               rounded-xl
                               bg-gray-100
                               px-6 py-3
                               text-sm font-bold text-gray-700
                               hover:bg-gray-200
                               transition">

                        ↻ Réinitialiser

                    </a>

                </div>

            </form>

        </div>


        {{-- STATISTICS --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">


            {{-- TOTAL --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm font-semibold text-gray-500">
                            Total matières
                        </p>

                        <p class="text-3xl font-extrabold text-[#0B2A55] mt-1">
                            {{ $totalSubjects }}
                        </p>

                    </div>

                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-2xl">
                        📚
                    </div>

                </div>

            </div>


            {{-- ACTIVE --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm font-semibold text-gray-500">
                            Matières actives
                        </p>

                        <p class="text-3xl font-extrabold text-green-600 mt-1">

                            {{ $activeSubjects }}

                        </p>

                    </div>

                    <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center text-2xl">
                        ✓
                    </div>

                </div>

            </div>


            {{-- INACTIVE --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm font-semibold text-gray-500">
                            Matières inactives
                        </p>

                        <p class="text-3xl font-extrabold text-red-500 mt-1">

                            {{ $inactiveSubjects }}

                        </p>

                    </div>

                    <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center text-2xl">
                        !
                    </div>

                </div>

            </div>

        </div>


        {{-- MAIN CARD --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">


            {{-- CARD HEADER --}}
            <div class="px-6 py-5 border-b border-gray-100">

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">


                    <div>

                        <h3 class="text-xl font-extrabold text-[#0B2A55]">
                            Liste des matières
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            Consultez et gérez les matières de l'académie.
                        </p>

                    </div>


                    {{-- SEARCH --}}
                    <form method="GET"
                          action="{{ route('subjects.index') }}"
                          id="subjectSearchForm"
                          class="relative w-full lg:w-80">

                        <input type="hidden"
                               name="school_year_id"
                               value="{{ $schoolYearId }}">

                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            🔎
                        </span>

                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Rechercher une matière..."
                               class="w-full pl-10 pr-4 py-3
                                      rounded-xl
                                      border border-gray-200
                                      bg-gray-50
                                      focus:bg-white
                                      focus:border-blue-400
                                      focus:ring-2
                                      focus:ring-blue-100
                                      outline-none
                                      text-sm">

                    </form>

                </div>

            </div>


            {{-- TABLE --}}
            <div class="overflow-x-auto">

                <table class="w-full">


                    {{-- TABLE HEAD --}}
                    <thead class="bg-[#F8FAFC]">

                        <tr>

                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500">
                                Matière
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500">
                                Code
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500">
                                Niveau
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500">
                                Heures
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500">
                                Statut
                            </th>

                            <th class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-wider text-gray-500">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    {{-- TABLE BODY --}}
                    <tbody id="subjectsTable"
                           class="divide-y divide-gray-100">


                        @forelse($subjects as $subject)

                            <tr class="subject-row hover:bg-blue-50/40 transition">


                                {{-- MATIERE --}}
                                <td class="px-6 py-5">

                                    <div class="flex items-center gap-3">

                                        <div class="w-11 h-11 rounded-xl
                                                    bg-blue-50
                                                    flex items-center justify-center
                                                    text-xl">

                                            📘

                                        </div>

                                        <div>

                                            <div class="font-extrabold text-[#0B2A55]">

                                                {{ strtoupper($subject->name) }}

                                            </div>

                                            <div class="text-xs text-gray-400 mt-1">

                                                Matière académique

                                            </div>

                                        </div>

                                    </div>

                                </td>


                                {{-- CODE --}}
                                <td class="px-6 py-5">

                                    <span class="inline-flex
                                                 px-3 py-1
                                                 rounded-lg
                                                 bg-blue-50
                                                 text-blue-700
                                                 font-bold
                                                 text-sm">

                                        {{ $subject->code }}

                                    </span>

                                </td>


                                {{-- NIVEAU --}}
                                <td class="px-6 py-5">

                                    <span class="text-gray-700 font-medium">

                                        <div class="flex flex-wrap gap-1">

    @if($subject->primaire)
        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
            Primaire
        </span>
    @endif

    @if($subject->moyen)
        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">
            Moyen
        </span>
    @endif

    @if($subject->lycee)
        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700">
            Lycée
        </span>
    @endif

</div>

                                    </span>

                                </td>


                                {{-- HEURES --}}
                                <td class="px-6 py-5">

                                    <span class="font-semibold text-gray-700">

                                        {{ $subject->hours_per_week ?? 0 }}

                                        <span class="text-gray-400 text-sm">
                                            h / semaine
                                        </span>

                                    </span>

                                </td>


                                {{-- STATUT --}}
                                <td class="px-6 py-5">

                                    @if($subject->is_active ?? true)

                                        <span class="inline-flex items-center gap-1.5
                                                     px-3 py-1.5
                                                     rounded-full
                                                     bg-green-50
                                                     text-green-700
                                                     text-xs
                                                     font-extrabold">

                                            <span class="w-2 h-2 rounded-full bg-green-500"></span>

                                            Active

                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5
                                                     px-3 py-1.5
                                                     rounded-full
                                                     bg-red-50
                                                     text-red-700
                                                     text-xs
                                                     font-extrabold">

                                            <span class="w-2 h-2 rounded-full bg-red-500"></span>

                                            Inactive

                                        </span>

                                    @endif

                                </td>


                                {{-- ACTIONS --}}
                                <td class="px-6 py-5">

                                    <div class="flex items-center justify-end gap-2">


                                        {{-- ENSEIGNANTS --}}
                                        <a href="{{ route('subjects.teachers.edit', $subject) }}"
                                           title="Gérer les enseignants"
                                           class="inline-flex items-center gap-1.5
                                                  px-3 py-2
                                                  rounded-lg
                                                  bg-green-50
                                                  hover:bg-green-100
                                                  text-green-700
                                                  font-bold
                                                  text-sm
                                                  transition">

                                            👨‍🏫
                                            Enseignants

                                        </a>


                                        {{-- VOIR --}}
                                        <a href="{{ route('subjects.show', $subject) }}"
                                           title="Voir la matière"
                                           class="inline-flex items-center justify-center
                                                  w-10 h-10
                                                  rounded-lg
                                                  bg-gray-100
                                                  hover:bg-gray-200
                                                  text-gray-700
                                                  transition">

                                            👁

                                        </a>


                                        {{-- MODIFIER --}}
                                        <a href="{{ route('subjects.edit', $subject) }}"
                                           title="Modifier la matière"
                                           class="inline-flex items-center justify-center
                                                  w-10 h-10
                                                  rounded-lg
                                                  bg-blue-50
                                                  hover:bg-blue-100
                                                  text-blue-700
                                                  transition">

                                            ✏️

                                        </a>


                                        {{-- SUPPRIMER --}}
                                        <form method="POST"
                                              action="{{ route('subjects.destroy', $subject) }}"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette matière ?');">

                                            @csrf

                                            @method('DELETE')

                                            <button type="submit"
                                                    title="Supprimer la matière"
                                                    class="inline-flex items-center justify-center
                                                           w-10 h-10
                                                           rounded-lg
                                                           bg-red-50
                                                           hover:bg-red-100
                                                           text-red-600
                                                           transition">

                                                🗑

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td colspan="6"
                                    class="px-6 py-16 text-center">

                                    <div class="flex flex-col items-center">

                                        <div class="w-16 h-16 rounded-2xl
                                                    bg-blue-50
                                                    flex items-center justify-center
                                                    text-3xl mb-4">

                                            📚

                                        </div>

                                        <h3 class="text-lg font-extrabold text-[#0B2A55]">
                                            Aucune matière
                                        </h3>

                                        <p class="text-sm text-gray-500 mt-1 mb-5">
                                            Aucune matière n'est encore enregistrée.
                                        </p>

                                        <a href="{{ route('subjects.create') }}"
                                           class="px-5 py-3
                                                  rounded-xl
                                                  bg-[#2563EB]
                                                  hover:bg-[#1D4ED8]
                                                  text-white
                                                  font-bold
                                                  transition">

                                            + Ajouter une matière

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION --}}
            <div class="px-6 py-5 border-t border-gray-100">

                {{ $subjects->links() }}

            </div>


        </div>

    </div>

</div>


{{-- SEARCH SCRIPT --}}
<script>

    document.addEventListener('DOMContentLoaded', function () {

        const searchForm = document.getElementById('subjectSearchForm');

        const searchInput = searchForm ? searchForm.querySelector('input[name="search"]') : null;


        if (!searchForm || !searchInput) {
            return;
        }


        let debounceTimer;

        searchInput.addEventListener('input', function () {

            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(function () {

                searchForm.submit();

            }, 250);

        });

    });

</script>


</x-app-layout>
