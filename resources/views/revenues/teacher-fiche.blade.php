
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
                            Fiche de paie — {{ $teacher->first_name }} {{ $teacher->last_name }}
                        </h2>

                        <p class="text-sm text-gray-500 mt-1">
                            {{ $periodLabel }} · {{ $teacherSubjects }}
                        </p>
                    </div>

                </div>

                {{-- Imprimer --}}
                <a href="{{ route('revenus.print', $teacher) }}?month={{ $month }}&year={{ $year }}&school_year_id={{ $schoolYearId }}"
                   target="_blank"
                   class="inline-flex items-center gap-2
                          px-6 py-3
                          rounded-xl
                          bg-[#C89B3C]
                          text-white
                          font-bold
                          shadow-md
                          border-b-4 border-[#0B2A55]
                          hover:bg-[#A67B1F]
                          transition">

                    🖨️ Imprimer

                </a>

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
                            Fiche de paie
                        </h1>

                        <span class="text-3xl">
                            💰
                        </span>

                    </div>

                    <p class="mt-3
                              text-blue-100
                              text-base md:text-lg">
                        Période : {{ $periodLabel }}
                    </p>

                </div>

            </div>


            {{-- =================================================
                FILTRES PÉRIODE
            ================================================== --}}
            <div class="bg-white
                        rounded-3xl
                        border border-gray-100
                        shadow-sm
                        p-6
                        mb-8">

                <form method="GET"
                      action="{{ route('revenus.fiche', $teacher) }}">

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">


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

                                @foreach($schoolYears as $sy)
                                    <option value="{{ $sy->id }}"
                                        {{ (int) $schoolYearId === (int) $sy->id ? 'selected' : '' }}>
                                        {{ $sy->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>


                        {{-- Filtrer --}}
                        <div class="flex items-end">

                            <button type="submit"
                                    class="px-6 py-3
                                           bg-[#0B2A55]
                                           text-white
                                           rounded-xl
                                           font-bold
                                           shadow-md
                                           border-b-4 border-[#C89B3C]
                                           hover:bg-[#061A33]
                                           transition w-full">
                                Filtrer
                            </button>

                        </div>


                    </div>

                </form>

            </div>


            {{-- =================================================
                RÉSUMÉ
            ================================================== --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">


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
                            {{ number_format($totalCollected, 2, ',', ' ') }}
                            <span class="text-base font-bold">DA</span>
                        </p>

                    </div>

                </div>


                {{-- PART ENSEIGNANT --}}
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
                                Part prof
                            </span>

                        </div>

                        <p class="mt-6 text-sm text-gray-500">
                            Part enseignant
                        </p>

                        <p class="mt-1
                                  text-3xl
                                  font-extrabold
                                  text-[#0B2A55]">
                            {{ number_format($totalTeacherShare, 2, ',', ' ') }}
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
                                Part acad.
                            </span>

                        </div>

                        <p class="mt-6 text-sm text-gray-500">
                            Part académie
                        </p>

                        <p class="mt-1
                                  text-3xl
                                  font-extrabold
                                  text-[#0B2A55]">
                            {{ number_format($totalAcademyShare, 2, ',', ' ') }}
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
                                {{ $paymentsCount }}
                            </span>

                        </div>

                        <p class="mt-6 text-sm text-gray-500">
                            Paiements
                        </p>

                        <p class="mt-1
                                  text-3xl
                                  font-extrabold
                                  text-[#0B2A55]">
                            {{ $paymentsCount }}
                            <span class="text-base font-bold">paiement(s)</span>
                        </p>

                    </div>

                </div>


            </div>


            {{-- =================================================
                RÉPARTITION PAR GROUPE
            ================================================== --}}
            @if($groupRevenue->count() > 0)

                <div class="bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            overflow-hidden
                            mb-8">

                    <div class="p-6 border-b border-gray-100">

                        <h3 class="text-lg font-extrabold text-[#0B2A55]">
                            Répartition par groupe
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            Revenus réels par groupe — basés sur les paiements enregistrés.
                        </p>

                    </div>

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Groupe
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Matière
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Paiements
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Encaissé
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Part enseignant
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Part académie
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-50">

                                @foreach($groupRevenue as $row)

                                    <tr class="hover:bg-gray-50 transition">

                                        {{-- Groupe --}}
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-[#0B2A55]/10 text-[#0B2A55] text-xs font-bold">
                                                {{ $row->group->name ?? '—' }}
                                            </span>
                                            <span class="block text-xs text-gray-400 mt-1">
                                                {{ $row->group->level ?? '' }} · {{ ucfirst($row->group->mode ?? '') }}
                                            </span>
                                        </td>

                                        {{-- Matière --}}
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-gray-600">
                                                {{ $row->subject->name ?? '—' }}
                                            </span>
                                        </td>

                                        {{-- Paiements --}}
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-sm font-bold text-gray-700">
                                                {{ $row->payments_count }}
                                            </span>
                                        </td>

                                        {{-- Encaissé --}}
                                        <td class="px-6 py-4 text-right">
                                            <span class="font-extrabold text-[#0B2A55]">
                                                {{ number_format($row->total_collected, 2, ',', ' ') }} DA
                                            </span>
                                        </td>

                                        {{-- Part enseignant --}}
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

                                    </tr>

                                @endforeach

                            </tbody>

                            {{-- TOTAUX --}}
                            <tfoot>
                                <tr class="bg-gray-50 border-t-2 border-gray-200">
                                    <td colspan="3" class="px-6 py-4 text-right">
                                        <span class="font-extrabold text-gray-700 text-sm uppercase">
                                            Total
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-extrabold text-[#0B2A55] text-lg">
                                            {{ number_format($totalCollected, 2, ',', ' ') }} DA
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-extrabold text-blue-600 text-lg">
                                            {{ number_format($totalTeacherShare, 2, ',', ' ') }} DA
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-extrabold text-[#C89B3C] text-lg">
                                            {{ number_format($totalAcademyShare, 2, ',', ' ') }} DA
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>

                        </table>

                    </div>

                </div>

            @endif


            {{-- =================================================
                DÉTAIL DES PAIEMENTS
            ================================================== --}}
            <div class="bg-white
                        rounded-3xl
                        border border-gray-100
                        shadow-sm
                        overflow-hidden">

                <div class="p-6 border-b border-gray-100">

                    <h3 class="text-lg font-extrabold text-[#0B2A55]">
                        Détail des paiements
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Chaque ligne = un paiement enregistré avec le calcul de la part.
                    </p>

                </div>


                @if($payments->count() > 0)

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Élève
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Groupe
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Matière
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Montant payé
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Part prof
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Part académie
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-50">

                                @foreach($payments as $row)

                                    <tr class="hover:bg-gray-50 transition">

                                        {{-- Date --}}
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-gray-600">
                                                {{ \Carbon\Carbon::parse($row->payment->payment_date)->format('d/m/Y') }}
                                            </span>
                                        </td>

                                        {{-- Élève --}}
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-gray-800">
                                                {{ $row->student->first_name }} {{ $row->student->last_name }}
                                            </span>
                                        </td>

                                        {{-- Groupe --}}
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-[#0B2A55]/10 text-[#0B2A55] text-xs font-bold">
                                                {{ $row->group->name ?? '—' }}
                                            </span>
                                        </td>

                                        {{-- Matière --}}
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-gray-600">
                                                {{ $row->subject->name ?? '—' }}
                                            </span>
                                        </td>

                                        {{-- Type --}}
                                        <td class="px-6 py-4 text-center">
                                            @php
                                                $typeLabels = [
                                                    'monthly' => 'Mensuel',
                                                    'special_monthly' => 'Spécial',
                                                    'vip_monthly' => 'VIP Mensuel',
                                                    'vip_per_session' => 'VIP Séance',
                                                ];
                                                $type = $row->payment->payment_type;
                                                $typeColors = [
                                                    'monthly' => 'bg-blue-100 text-blue-700',
                                                    'special_monthly' => 'bg-purple-100 text-purple-700',
                                                    'vip_monthly' => 'bg-[#C89B3C]/20 text-[#C89B3C]',
                                                    'vip_per_session' => 'bg-[#C89B3C]/20 text-[#C89B3C]',
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center px-3 py-1 rounded-full {{ $typeColors[$type] ?? 'bg-gray-100 text-gray-700' }} text-xs font-bold">
                                                {{ $typeLabels[$type] ?? $type }}
                                            </span>
                                        </td>

                                        {{-- Montant payé --}}
                                        <td class="px-6 py-4 text-right">
                                            <span class="font-extrabold text-[#0B2A55]">
                                                {{ number_format((float) $row->payment->amount_paid, 2, ',', ' ') }} DA
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

                                    </tr>

                                @endforeach

                            </tbody>

                            {{-- TOTAUX --}}
                            <tfoot>
                                <tr class="bg-gray-50 border-t-2 border-gray-200">
                                    <td colspan="5" class="px-6 py-4 text-right">
                                        <span class="font-extrabold text-gray-700 text-sm uppercase">
                                            Totaux
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-extrabold text-[#0B2A55] text-lg">
                                            {{ number_format($totalCollected, 2, ',', ' ') }} DA
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-extrabold text-blue-600 text-lg">
                                            {{ number_format($totalTeacherShare, 2, ',', ' ') }} DA
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-extrabold text-[#C89B3C] text-lg">
                                            {{ number_format($totalAcademyShare, 2, ',', ' ') }} DA
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>

                        </table>

                    </div>

                @else

                    <div class="p-12 text-center">

                        <div class="text-6xl mb-4">📭</div>

                        <h3 class="text-lg font-bold text-gray-500 mb-2">
                            Aucun paiement trouvé
                        </h3>

                        <p class="text-sm text-gray-400">
                            Aucun paiement enregistré pour {{ $teacher->first_name }} {{ $teacher->last_name }} à cette période.
                        </p>

                    </div>

                @endif


            </div>


        </div>

    </div>

</x-app-layout>
