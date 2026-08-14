<x-app-layout>

    {{-- HEADER --}}
    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div class="flex items-center gap-3">

                <div class="w-11 h-11 rounded-xl
                            bg-[#0B2A55]
                            flex items-center justify-center
                            text-white text-xl shadow-sm">

                    🎓

                </div>

                <div>

                    <h2 class="text-2xl font-extrabold text-[#0B2A55]">
                        Niveaux scolaires
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Gestion des niveaux d'enseignement
                    </p>

                </div>

            </div>


            <a href="{{ route('levels.create') }}"
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

                Ajouter un niveau

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

                    <span class="text-xl">✓</span>

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

                    <span class="text-xl">⚠</span>

                    <span class="font-semibold">
                        {{ session('error') }}
                    </span>

                </div>

            @endif


            {{-- STATISTICS --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">


                {{-- TOTAL --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm font-semibold text-gray-500">
                                Total niveaux
                            </p>

                            <p class="text-3xl font-extrabold text-[#0B2A55] mt-1">
                                {{ $levels->count() }}
                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl
                                    bg-blue-50
                                    flex items-center justify-center
                                    text-2xl">

                            🎓

                        </div>

                    </div>

                </div>


                {{-- ACTIVE --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm font-semibold text-gray-500">
                                Niveaux actifs
                            </p>

                            <p class="text-3xl font-extrabold text-green-600 mt-1">
                                {{ $levels->where('active', true)->count() }}
                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl
                                    bg-green-50
                                    flex items-center justify-center
                                    text-2xl">

                            ✓

                        </div>

                    </div>

                </div>


                {{-- INACTIVE --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm font-semibold text-gray-500">
                                Niveaux inactifs
                            </p>

                            <p class="text-3xl font-extrabold text-red-500 mt-1">
                                {{ $levels->where('active', false)->count() }}
                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl
                                    bg-red-50
                                    flex items-center justify-center
                                    text-2xl">

                            !

                        </div>

                    </div>

                </div>

            </div>


            {{-- TABLE CARD --}}
            <div class="bg-white rounded-2xl
                        border border-gray-200
                        shadow-sm
                        overflow-hidden">


                {{-- CARD HEADER --}}
                <div class="px-6 py-5 border-b border-gray-100">

                    <div class="flex flex-col lg:flex-row
                                lg:items-center
                                lg:justify-between
                                gap-4">

                        <div>

                            <h3 class="text-xl font-extrabold text-[#0B2A55]">
                                Liste des niveaux
                            </h3>

                            <p class="text-sm text-gray-500 mt-1">
                                Consultez et gérez les niveaux d'enseignement.
                            </p>

                        </div>


                        {{-- SEARCH --}}
                        <div class="relative w-full lg:w-80">

                            <span class="absolute left-3 top-1/2
                                         -translate-y-1/2
                                         text-gray-400">

                                🔎

                            </span>

                            <input type="text"
                                   id="levelSearch"
                                   placeholder="Rechercher un niveau..."
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

                        </div>

                    </div>

                </div>


                {{-- TABLE --}}
                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-[#F8FAFC]">

                            <tr>

                                <th class="px-6 py-4 text-left
                                           text-xs font-extrabold
                                           uppercase tracking-wider
                                           text-gray-500">

                                    Niveau

                                </th>

                                <th class="px-6 py-4 text-left
                                           text-xs font-extrabold
                                           uppercase tracking-wider
                                           text-gray-500">

                                    Code

                                </th>

                                <th class="px-6 py-4 text-left
                                           text-xs font-extrabold
                                           uppercase tracking-wider
                                           text-gray-500">

                                    Description

                                </th>

                                <th class="px-6 py-4 text-left
                                           text-xs font-extrabold
                                           uppercase tracking-wider
                                           text-gray-500">

                                    Statut

                                </th>

                                <th class="px-6 py-4 text-right
                                           text-xs font-extrabold
                                           uppercase tracking-wider
                                           text-gray-500">

                                    Actions

                                </th>

                            </tr>

                        </thead>


                        <tbody id="levelsTable"
                               class="divide-y divide-gray-100">


                            @forelse($levels as $level)

                                <tr class="level-row hover:bg-blue-50/40 transition">


                                    {{-- NAME --}}
                                    <td class="px-6 py-5">

                                        <div class="flex items-center gap-3">

                                            <div class="w-11 h-11 rounded-xl
                                                        bg-blue-50
                                                        flex items-center
                                                        justify-center
                                                        text-xl">

                                                🎓

                                            </div>

                                            <div>

                                                <div class="font-extrabold text-[#0B2A55]">

                                                    {{ $level->name }}

                                                </div>

                                                <div class="text-xs text-gray-400 mt-1">

                                                    Niveau scolaire

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

                                            {{ $level->code }}

                                        </span>

                                    </td>


                                    {{-- DESCRIPTION --}}
                                    <td class="px-6 py-5">

                                        <span class="text-gray-600">

                                            {{ $level->description ?? '-' }}

                                        </span>

                                    </td>


                                    {{-- STATUS --}}
                                    <td class="px-6 py-5">

                                        @if($level->active)

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


                                            {{-- VOIR --}}
                                            <a href="{{ route('levels.show', $level) }}"
                                               title="Voir le niveau"
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
                                            <a href="{{ route('levels.edit', $level) }}"
                                               title="Modifier le niveau"
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
                                                  action="{{ route('levels.destroy', $level) }}"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce niveau ?');">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        title="Supprimer le niveau"
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

                                    <td colspan="5"
                                        class="px-6 py-16 text-center">

                                        <div class="flex flex-col items-center">

                                            <div class="w-16 h-16 rounded-2xl
                                                        bg-blue-50
                                                        flex items-center
                                                        justify-center
                                                        text-3xl mb-4">

                                                🎓

                                            </div>

                                            <h3 class="text-lg font-extrabold text-[#0B2A55]">

                                                Aucun niveau

                                            </h3>

                                            <p class="text-sm text-gray-500 mt-1 mb-5">

                                                Aucun niveau n'est encore enregistré.

                                            </p>

                                            <a href="{{ route('levels.create') }}"
                                               class="px-5 py-3
                                                      rounded-xl
                                                      bg-[#2563EB]
                                                      hover:bg-[#1D4ED8]
                                                      text-white
                                                      font-bold
                                                      transition">

                                                + Ajouter un niveau

                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>


    {{-- SEARCH SCRIPT --}}
    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const searchInput = document.getElementById('levelSearch');

            const rows = document.querySelectorAll('.level-row');

            if (!searchInput) return;

            searchInput.addEventListener('input', function () {

                const search = this.value.toLowerCase().trim();

                rows.forEach(row => {

                    const text = row.innerText.toLowerCase();

                    row.style.display =
                        text.includes(search) ? '' : 'none';

                });

            });

        });

    </script>

</x-app-layout>