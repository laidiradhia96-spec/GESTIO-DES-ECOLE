
<x-app-layout>

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <x-slot name="header">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex items-center justify-between">

                <div class="flex items-center gap-4">

                    {{-- Icon --}}
                    <div class="w-14 h-14 rounded-2xl bg-[#0B2A55]
                                flex items-center justify-center shadow-md">
                        <span class="text-2xl">💰</span>
                    </div>

                    <div>
                        <h2 class="font-extrabold text-2xl text-[#0B2A55]">
                            Revenus des enseignants
                        </h2>

                        <p class="text-sm text-gray-500 mt-1">
                            Suivi des parts enseignants et académie.
                        </p>
                    </div>

                </div>

            </div>

        </div>

    </x-slot>


    {{-- =========================================================
        MAIN
    ========================================================== --}}
    <div class="py-8 bg-gray-50 min-h-screen">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- =================================================
                BANNER
            ================================================== --}}
            <div class="relative overflow-hidden
                        bg-gradient-to-r from-[#0B2A55] to-[#173E73]
                        rounded-3xl
                        shadow-xl
                        mb-8">

                {{-- Decorative circles --}}
                <div class="absolute -right-16 -top-20
                            w-64 h-64
                            rounded-full
                            bg-white/10">
                </div>

                <div class="absolute right-20 -bottom-28
                            w-72 h-72
                            rounded-full
                            border border-[#C89B3C]/30">
                </div>


                <div class="relative p-8 md:p-10">

                    <p class="text-[#C89B3C]
                              font-bold
                              tracking-wide
                              uppercase
                              text-sm">
                        Académie El Tafawok
                    </p>

                    <div class="flex items-center gap-3 mt-2">

                        <h1 class="text-3xl md:text-4xl
                                   font-extrabold
                                   text-white">
                            Revenus des enseignants
                        </h1>

                        <span class="text-3xl">
                            💰
                        </span>

                    </div>

                    <p class="mt-3
                              text-blue-100
                              text-base md:text-lg">
                        Consultez les parts enseignants et académie
                        pour chaque paiement enregistré.
                    </p>

                </div>

            </div>


            {{-- =================================================
                STATISTIQUES PRINCIPALES
            ================================================== --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">


                {{-- TOTAL ENCAISSÉ --}}
                <div class="relative overflow-hidden
                            bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            hover:shadow-md
                            transition">

                    <div class="absolute right-0 top-0
                                w-32 h-32
                                bg-green-50
                                rounded-bl-full">
                    </div>

                    <div class="relative p-6">

                        <div class="flex items-center justify-between">

                            <div class="w-14 h-14
                                        rounded-2xl
                                        bg-green-100
                                        flex items-center justify-center
                                        text-2xl">
                                💰
                            </div>

                            <span class="px-4 py-2
                                         rounded-full
                                         bg-green-50
                                         text-green-600
                                         text-xs
                                         font-extrabold
                                         uppercase">
                                Encaissé
                            </span>

                        </div>

                        <p class="mt-6 text-sm text-gray-500">
                            Total encaissé
                        </p>

                        <p class="mt-1
                                  text-3xl
                                  font-extrabold
                                  text-[#0B2A55]">
                            {{ number_format($stats['total_collected'], 2, ',', ' ') }}
                            <span class="text-base font-bold">DA</span>
                        </p>

                    </div>

                </div>


                {{-- PART ENSEIGNANTS --}}
                <div class="relative overflow-hidden
                            bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            hover:shadow-md
                            transition">

                    <div class="absolute right-0 top-0
                                w-32 h-32
                                bg-blue-50
                                rounded-bl-full">
                    </div>

                    <div class="relative p-6">

                        <div class="flex items-center justify-between">

                            <div class="w-14 h-14
                                        rounded-2xl
                                        bg-blue-100
                                        flex items-center justify-center
                                        text-2xl">
                                👨‍🏫
                            </div>

                            <span class="px-4 py-2
                                         rounded-full
                                         bg-blue-50
                                         text-blue-600
                                         text-xs
                                         font-extrabold
                                         uppercase">
                                Enseignants
                            </span>

                        </div>

                        <p class="mt-6 text-sm text-gray-500">
                            Part enseignants
                        </p>

                        <p class="mt-1
                                  text-3xl
                                  font-extrabold
                                  text-[#0B2A55]">
                            {{ number_format($stats['total_teacher_share'], 2, ',', ' ') }}
                            <span class="text-base font-bold">DA</span>
                        </p>

                    </div>

                </div>


                {{-- PART ACADÉMIE --}}
                <div class="relative overflow-hidden
                            bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            hover:shadow-md
                            transition">

                    <div class="absolute right-0 top-0
                                w-32 h-32
                                bg-[#C89B3C]/10
                                rounded-bl-full">
                    </div>

                    <div class="relative p-6">

                        <div class="flex items-center justify-between">

                            <div class="w-14 h-14
                                        rounded-2xl
                                        bg-[#C89B3C]/20
                                        flex items-center justify-center
                                        text-2xl">
                                🏛️
                            </div>

                            <span class="px-4 py-2
                                         rounded-full
                                         bg-[#C89B3C]/10
                                         text-[#C89B3C]
                                         text-xs
                                         font-extrabold
                                         uppercase">
                                Académie
                            </span>

                        </div>

                        <p class="mt-6 text-sm text-gray-500">
                            Part académie
                        </p>

                        <p class="mt-1
                                  text-3xl
                                  font-extrabold
                                  text-[#0B2A55]">
                            {{ number_format($stats['total_academy_share'], 2, ',', ' ') }}
                            <span class="text-base font-bold">DA</span>
                        </p>

                    </div>

                </div>


                {{-- NOMBRE DE PAIEMENTS --}}
                <div class="relative overflow-hidden
                            bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            hover:shadow-md
                            transition">

                    <div class="absolute right-0 top-0
                                w-32 h-32
                                bg-purple-50
                                rounded-bl-full">
                    </div>

                    <div class="relative p-6">

                        <div class="flex items-center justify-between">

                            <div class="w-14 h-14
                                        rounded-2xl
                                        bg-purple-100
                                        flex items-center justify-center
                                        text-2xl">
                                📊
                            </div>

                            <span class="px-4 py-2
                                         rounded-full
                                         bg-purple-50
                                         text-purple-600
                                         text-xs
                                         font-extrabold
                                         uppercase">
                                {{ $stats['payments_count'] }}
                            </span>

                        </div>

                        <p class="mt-6 text-sm text-gray-500">
                            Paiements
                        </p>

                        <p class="mt-1
                                  text-3xl
                                  font-extrabold
                                  text-[#0B2A55]">
                            {{ $stats['payments_count'] }}
                            <span class="text-base font-bold">paiement(s)</span>
                        </p>

                        <p class="text-sm text-gray-400 mt-1">
                            {{ $stats['teachers_count'] }} enseignant(s)
                        </p>

                    </div>

                </div>


            </div>


            {{-- =================================================
                FILTRES
            ================================================== --}}
            <div class="bg-white
                        rounded-3xl
                        border border-gray-100
                        shadow-sm
                        p-6
                        mb-8">

                <div class="flex items-center gap-3 mb-5">

                    <div class="w-11 h-11
                                rounded-xl
                                bg-[#0B2A55]
                                flex items-center justify-center
                                text-xl">
                        🔎
                    </div>

                    <div>

                        <h3 class="text-lg
                                   font-extrabold
                                   text-[#0B2A55]">
                            Filtrer les revenus
                        </h3>

                        <p class="text-sm text-gray-500">
                            Par période, enseignant, groupe ou matière.
                        </p>

                    </div>

                </div>


                <form method="GET"
                      action="{{ route('revenus.index') }}">

                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">


                        {{-- Mois --}}
                        <div>

                            <label class="block
                                          text-sm
                                          font-bold
                                          text-gray-700
                                          mb-2">
                                Mois
                            </label>

                            <select name="month"
                                    class="w-full
                                           rounded-xl
                                           border-gray-200
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]">

                                <option value="">
                                    Tous les mois
                                </option>

                                @foreach($months as $m)
                                    <option value="{{ $m['value'] }}"
                                        {{ (int) $month === (int) $m['value'] ? 'selected' : '' }}>
                                        {{ $m['label'] }}
                                    </option>
                                @endforeach

                            </select>

                        </div>


                        {{-- Année --}}
                        <div>

                            <label class="block
                                          text-sm
                                          font-bold
                                          text-gray-700
                                          mb-2">
                                Année
                            </label>

                            <select name="year"
                                    class="w-full
                                           rounded-xl
                                           border-gray-200
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]">

                                @foreach($years as $y)
                                    <option value="{{ $y }}"
                                        {{ (int) $year === (int) $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach

                            </select>

                        </div>


                        {{-- Année scolaire --}}
                        <div>

                            <label class="block
                                          text-sm
                                          font-bold
                                          text-gray-700
                                          mb-2">
                                Année scolaire
                            </label>

                            <select name="school_year_id"
                                    class="w-full
                                           rounded-xl
                                           border-gray-200
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]">

                                <option value="">
                                    Toutes
                                </option>

                                @foreach($schoolYears as $sy)
                                    <option value="{{ $sy->id }}"
                                        {{ (int) $schoolYearId === (int) $sy->id ? 'selected' : '' }}>
                                        {{ $sy->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>


                        {{-- Enseignant --}}
                        <div>

                            <label class="block
                                          text-sm
                                          font-bold
                                          text-gray-700
                                          mb-2">
                                Enseignant
                            </label>

                            <select name="teacher_id"
                                    class="w-full
                                           rounded-xl
                                           border-gray-200
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]">

                                <option value="">
                                    Tous
                                </option>

                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}"
                                        {{ (int) $teacherId === (int) $t->id ? 'selected' : '' }}>
                                        {{ $t->first_name }} {{ $t->last_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>


                        {{-- Groupe --}}
                        <div>

                            <label class="block
                                          text-sm
                                          font-bold
                                          text-gray-700
                                          mb-2">
                                Groupe
                            </label>

                            <select name="group_id"
                                    class="w-full
                                           rounded-xl
                                           border-gray-200
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]">

                                <option value="">
                                    Tous
                                </option>

                                @foreach($groups as $g)
                                    <option value="{{ $g->id }}"
                                        {{ (int) $groupId === (int) $g->id ? 'selected' : '' }}>
                                        {{ $g->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>


                        {{-- Matière --}}
                        <div>

                            <label class="block
                                          text-sm
                                          font-bold
                                          text-gray-700
                                          mb-2">
                                Matière
                            </label>

                            <select name="subject_id"
                                    class="w-full
                                           rounded-xl
                                           border-gray-200
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]">

                                <option value="">
                                    Toutes
                                </option>

                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}"
                                        {{ (int) $subjectId === (int) $s->id ? 'selected' : '' }}>
                                        {{ $s->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>


                    </div>


                    {{-- Actions --}}
                    <div class="flex items-center gap-3 mt-5">

                        <button type="submit"
                                class="px-6 py-3
                                       bg-[#0B2A55]
                                       text-white
                                       rounded-xl
                                       font-bold
                                       shadow-md
                                       border-b-4 border-[#C89B3C]
                                       hover:bg-[#061A33]
                                       transition">
                            Filtrer
                        </button>

                        <a href="{{ route('revenus.index') }}"
                           class="px-6 py-3
                                  bg-gray-100
                                  text-gray-600
                                  rounded-xl
                                  font-bold
                                  hover:bg-gray-200
                                  transition">
                            Réinitialiser
                        </a>

                    </div>

                </form>

            </div>


            {{-- =================================================
                TABLEAU DES REVENUS PAR ENSEIGNANT
            ================================================== --}}
            <div class="bg-white
                        rounded-3xl
                        border border-gray-100
                        shadow-sm
                        overflow-hidden">

                <div class="p-6 border-b border-gray-100">

                    <h3 class="text-lg font-extrabold text-[#0B2A55]">
                        Détail par enseignant
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Cliquez sur "Voir" pour la fiche détaillée.
                    </p>

                </div>


                @if($paginated->count() > 0)

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Enseignant
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Paiements
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Élèves payeurs
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Total encaissé
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Part prof
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Part académie
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-50">

                                @foreach($paginated as $row)

                                    <tr class="hover:bg-gray-50 transition">

                                        {{-- Enseignant --}}
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-[#0B2A55] flex items-center justify-center text-white font-bold text-sm">
                                                    {{ substr($row->teacher->first_name, 0, 1) }}{{ substr($row->teacher->last_name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-800">
                                                        {{ $row->teacher->first_name }} {{ $row->teacher->last_name }}
                                                    </p>
                                                    @if($row->teacher->speciality)
                                                        <p class="text-xs text-gray-400">
                                                            {{ $row->teacher->speciality }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Paiements --}}
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-bold text-sm">
                                                {{ $row->payments_count }}
                                            </span>
                                        </td>

                                        {{-- Élèves payeurs --}}
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-purple-100 text-purple-700 font-bold text-sm">
                                                {{ $row->students_count }}
                                            </span>
                                        </td>

                                        {{-- Total encaissé --}}
                                        <td class="px-6 py-4 text-right">
                                            <span class="font-extrabold text-[#0B2A55]">
                                                {{ number_format($row->total_collected, 2, ',', ' ') }} DA
                                            </span>
                                        </td>

                                        {{-- Part prof --}}
                                        <td class="px-6 py-4 text-right">
                                            <span class="font-extrabold text-blue-600">
                                                {{ number_format($row->teacher_share, 2, ',', ' ') }} DA
                                            </span>
                                        </td>

                                        {{-- Part académie --}}
                                        <td class="px-6 py-4 text-right">
                                            <span class="font-extrabold text-[#C89B3C]">
                                                {{ number_format($row->academy_share, 2, ',', ' ') }} DA
                                            </span>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="px-6 py-4 text-center">
                                            <a href="{{ route('revenus.fiche', $row->teacher) }}"
                                               class="inline-flex items-center gap-1
                                                      px-4 py-2
                                                      bg-[#0B2A55]
                                                      text-white
                                                      rounded-xl
                                                      text-sm
                                                      font-bold
                                                      hover:bg-[#061A33]
                                                      transition">
                                                👁️ Voir
                                            </a>
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @else

                    <div class="p-12 text-center">

                        <div class="text-6xl mb-4">📭</div>

                        <h3 class="text-lg font-bold text-gray-500 mb-2">
                            Aucun revenu trouvé
                        </h3>

                        <p class="text-sm text-gray-400">
                            Aucun paiement enregistré pour cette période.
                        </p>

                    </div>

                @endif


                {{-- Pagination --}}
                @if($paginated->hasPages())

                    <div class="p-6 border-t border-gray-100">
                        {{ $paginated->links() }}
                    </div>

                @endif


            </div>


        </div>

    </div>

</x-app-layout>
