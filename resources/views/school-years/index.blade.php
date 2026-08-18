<x-app-layout>

    {{-- HEADER --}}
    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div class="flex items-center gap-3">

                <div class="w-11 h-11 rounded-xl
                            bg-[#0B2A55]
                            flex items-center justify-center
                            text-white text-xl shadow-sm">

                    🗓️

                </div>

                <div>

                    <h2 class="text-2xl font-extrabold text-[#0B2A55]">
                        Années scolaires
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Gestion des années scolaires du système
                    </p>

                </div>

            </div>


            <a href="{{ route('school-years.create') }}"
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

                Ajouter une année

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
                                Total années
                            </p>

                            <p class="text-3xl font-extrabold text-[#0B2A55] mt-1">
                                {{ $schoolYears->count() }}
                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl
                                    bg-blue-50
                                    flex items-center justify-center
                                    text-2xl">

                            🗓️

                        </div>

                    </div>

                </div>


                {{-- COURANTE --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm font-semibold text-gray-500">
                                Année courante
                            </p>

                            <p class="text-2xl font-extrabold text-green-600 mt-1">
                                {{ $schoolYears->firstWhere('is_current', true)?->name ?? 'Aucune' }}
                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl
                                    bg-green-50
                                    flex items-center justify-center
                                    text-2xl">

                            ✅

                        </div>

                    </div>

                </div>


                {{-- AVEC DONNÉES --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm font-semibold text-gray-500">
                                Années avec données
                            </p>

                            <p class="text-3xl font-extrabold text-amber-500 mt-1">
                                {{ $schoolYears->filter(fn ($year) => $year->attendances_count + $year->enrollments_count + $year->payments_count + $year->class_sessions_count + $year->payment_signalements_count + $year->unpaid_signalements_count + $year->payment_schedules_count > 0)->count() }}
                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl
                                    bg-amber-50
                                    flex items-center justify-center
                                    text-2xl">

                            📊

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
                                Liste des années scolaires
                            </h3>

                            <p class="text-sm text-gray-500 mt-1">
                                Consultez, modifiez ou supprimez les années scolaires.
                            </p>

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

                                    Année

                                </th>

                                <th class="px-6 py-4 text-left
                                           text-xs font-extrabold
                                           uppercase tracking-wider
                                           text-gray-500">

                                    Statut

                                </th>

                                <th class="px-6 py-4 text-left
                                           text-xs font-extrabold
                                           uppercase tracking-wider
                                           text-gray-500">

                                    Données liées

                                </th>

                                <th class="px-6 py-4 text-right
                                           text-xs font-extrabold
                                           uppercase tracking-wider
                                           text-gray-500">

                                    Actions

                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100">


                            @forelse($schoolYears as $year)

                                <tr class="hover:bg-blue-50/40 transition">

                                    {{-- ANNÉE --}}
                                    <td class="px-6 py-5">

                                        <div class="flex items-center gap-3">

                                            <div class="w-11 h-11 rounded-xl
                                                        bg-blue-50
                                                        flex items-center
                                                        justify-center
                                                        text-xl">

                                                🗓️

                                            </div>

                                            <div>

                                                <div class="font-extrabold text-[#0B2A55]">

                                                    {{ $year->name }}

                                                </div>

                                                <div class="text-xs text-gray-400 mt-1">

                                                    {{ $year->start_date->format('d/m/Y') }} → {{ $year->end_date->format('d/m/Y') }}

                                                </div>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- STATUT --}}
                                    <td class="px-6 py-5">

                                        @if($year->is_current)

                                            <span class="inline-flex items-center gap-1.5
                                                         px-3 py-1.5
                                                         rounded-full
                                                         bg-green-50
                                                         text-green-700
                                                         text-xs
                                                         font-extrabold">

                                                <span class="w-2 h-2 rounded-full bg-green-500"></span>

                                                Année courante

                                            </span>

                                        @else

                                            <span class="inline-flex items-center gap-1.5
                                                         px-3 py-1.5
                                                         rounded-full
                                                         bg-gray-100
                                                         text-gray-600
                                                         text-xs
                                                         font-extrabold">

                                                <span class="w-2 h-2 rounded-full bg-gray-400"></span>

                                                Non courante

                                            </span>

                                        @endif

                                    </td>


                                    {{-- DONNÉES --}}
                                    <td class="px-6 py-5">

                                        @php
                                            $total = $year->attendances_count + $year->enrollments_count
                                                + $year->payments_count + $year->class_sessions_count
                                                + $year->payment_signalements_count + $year->unpaid_signalements_count
                                                + $year->payment_schedules_count;
                                        @endphp

                                        @if($total > 0)

                                            <span class="inline-flex px-3 py-1
                                                         rounded-lg
                                                         bg-amber-50
                                                         text-amber-700
                                                         font-bold
                                                         text-sm">

                                                {{ $total }} donnée(s)

                                            </span>

                                        @else

                                            <span class="inline-flex px-3 py-1
                                                         rounded-lg
                                                         bg-gray-100
                                                         text-gray-500
                                                         font-bold
                                                         text-sm">

                                                Aucune donnée

                                            </span>

                                        @endif

                                    </td>


                                    {{-- ACTIONS --}}
                                    <td class="px-6 py-5">

                                        <div class="flex items-center justify-end gap-2">


                                            {{-- VOIR --}}
                                            <a href="{{ route('school-years.show', $year) }}"
                                               title="Voir l'année"
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
                                            <a href="{{ route('school-years.edit', $year) }}"
                                               title="Modifier l'année"
                                               class="inline-flex items-center justify-center
                                                      w-10 h-10
                                                      rounded-lg
                                                      bg-blue-50
                                                      hover:bg-blue-100
                                                      text-blue-700
                                                      transition">

                                                ✏️

                                            </a>


                                            {{-- DÉFINIR COURANTE --}}
                                            @if(! $year->is_current)

                                                <form method="POST"
                                                      action="{{ route('school-years.set-current', $year) }}">

                                                    @csrf

                                                    <button type="submit"
                                                            title="Définir comme année courante"
                                                            class="inline-flex items-center justify-center
                                                                   w-10 h-10
                                                                   rounded-lg
                                                                   bg-green-50
                                                                   hover:bg-green-100
                                                                   text-green-700
                                                                   transition">

                                                        ✅

                                                    </button>

                                                </form>

                                            @endif


                                            {{-- SUPPRIMER --}}
                                            @if($year->is_current || $total > 0)

                                                <button type="button"
                                                        title="{{ $year->is_current ? 'Suppression refusée : année courante' : 'Suppression refusée : année contenant des données' }}"
                                                        disabled
                                                        class="inline-flex items-center justify-center
                                                               w-10 h-10
                                                               rounded-lg
                                                               bg-gray-100
                                                               text-gray-300
                                                               cursor-not-allowed
                                                               transition">

                                                    🗑

                                                </button>

                                            @else

                                                <form method="POST"
                                                      action="{{ route('school-years.destroy', $year) }}"
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer l\'année {{ $year->name }} ?');">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            title="Supprimer l'année"
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

                                            @endif

                                        </div>

                                    </td>

                                </tr>


                            @empty

                                <tr>

                                    <td colspan="4"
                                        class="px-6 py-16 text-center">

                                        <div class="flex flex-col items-center">

                                            <div class="w-16 h-16 rounded-2xl
                                                        bg-blue-50
                                                        flex items-center
                                                        justify-center
                                                        text-3xl mb-4">

                                                🗓️

                                            </div>

                                            <h3 class="text-lg font-extrabold text-[#0B2A55]">

                                                Aucune année scolaire

                                            </h3>

                                            <p class="text-sm text-gray-500 mt-1 mb-5">

                                                Aucune année scolaire n'est encore enregistrée.

                                            </p>

                                            <a href="{{ route('school-years.create') }}"
                                               class="px-5 py-3
                                                      rounded-xl
                                                      bg-[#2563EB]
                                                      hover:bg-[#1D4ED8]
                                                      text-white
                                                      font-bold
                                                      transition">

                                                + Ajouter une année

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

</x-app-layout>
