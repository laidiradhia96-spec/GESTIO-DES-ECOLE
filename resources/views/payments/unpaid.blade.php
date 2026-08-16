
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
                        <span class="text-2xl">⚠️</span>
                    </div>

                    <div>
                        <h2 class="font-extrabold text-2xl text-[#0B2A55]">
                            Paiements impayés
                        </h2>

                        <p class="text-sm text-gray-500 mt-1">
                            Paiements partiels ou non soldés des élèves.
                        </p>
                    </div>

                </div>


                {{-- Nouveau paiement --}}
                <a href="{{ route('payments.create') }}"
                   class="inline-flex items-center gap-2
                          px-6 py-3
                          rounded-xl
                          bg-[#0B2A55]
                          text-white
                          font-bold
                          shadow-md
                          border-b-4 border-[#C89B3C]
                          hover:bg-[#061A33]
                          transition">

                    <span class="text-lg">+</span>
                    Nouveau paiement

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
                            Paiements non soldés
                        </h1>

                        <span class="text-3xl">
                            ⚠️
                        </span>

                    </div>

                    <p class="mt-3
                              text-blue-100
                              text-base md:text-lg">
                        Les paiements avec un reste à payer apparaissent ici.
                    </p>

                </div>

            </div>


            {{-- =================================================
                RECHERCHE & FILTRES
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
                            Rechercher un paiement impayé
                        </h3>

                        <p class="text-sm text-gray-500">
                            Recherchez par élève ou filtrez par période et statut.
                        </p>

                    </div>

                </div>


                <form method="GET"
                      action="{{ route('payments.unpaid') }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- Recherche --}}
                        <div class="md:col-span-2">

                            <label class="block
                                          text-sm
                                          font-bold
                                          text-gray-700
                                          mb-2">
                                Élève
                            </label>

                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Nom, prénom ou parent..."
                                class="w-full
                                       rounded-xl
                                       border-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#0B2A55]"
                            >

                        </div>


                        {{-- Période --}}
                        <div>

                            <label class="block
                                          text-sm
                                          font-bold
                                          text-gray-700
                                          mb-2">
                                Période
                            </label>

                            <select
                                name="period"
                                class="w-full
                                       rounded-xl
                                       border-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#0B2A55]"
                            >

                                <option value="">
                                    Toutes les périodes
                                </option>

                                <option value="Septembre"
                                    {{ request('period') == 'Septembre' ? 'selected' : '' }}>
                                    Septembre
                                </option>

                                <option value="Octobre"
                                    {{ request('period') == 'Octobre' ? 'selected' : '' }}>
                                    Octobre
                                </option>

                                <option value="Novembre"
                                    {{ request('period') == 'Novembre' ? 'selected' : '' }}>
                                    Novembre
                                </option>

                                <option value="Décembre"
                                    {{ request('period') == 'Décembre' ? 'selected' : '' }}>
                                    Décembre
                                </option>

                                <option value="Janvier"
                                    {{ request('period') == 'Janvier' ? 'selected' : '' }}>
                                    Janvier
                                </option>

                                <option value="Février"
                                    {{ request('period') == 'Février' ? 'selected' : '' }}>
                                    Février
                                </option>

                                <option value="Mars"
                                    {{ request('period') == 'Mars' ? 'selected' : '' }}>
                                    Mars
                                </option>

                                <option value="Avril"
                                    {{ request('period') == 'Avril' ? 'selected' : '' }}>
                                    Avril
                                </option>

                                <option value="Mai"
                                    {{ request('period') == 'Mai' ? 'selected' : '' }}>
                                    Mai
                                </option>

                                <option value="Juin"
                                    {{ request('period') == 'Juin' ? 'selected' : '' }}>
                                    Juin
                                </option>

                            </select>

                        </div>

                    </div>


                    <div class="flex flex-wrap gap-3 mt-5">

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


                        <a href="{{ route('payments.unpaid') }}"
                           class="px-6 py-3
                                  rounded-xl
                                  bg-gray-100
                                  text-gray-700
                                  font-bold
                                  hover:bg-gray-200
                                  transition">

                            Réinitialiser

                        </a>

                    </div>

                </form>

            </div>


            {{-- =================================================
                TABLEAU
            ================================================== --}}
            <div class="bg-white
                        rounded-3xl
                        border border-gray-100
                        shadow-sm
                        overflow-hidden">

                <div class="overflow-x-auto">

                    <table class="min-w-full">

                        <thead class="bg-gray-50
                                      border-b border-gray-200">

                            <tr>

                                <th class="px-6 py-4
                                           text-left
                                           text-sm
                                           font-extrabold
                                           text-[#0B2A55]">
                                    Reçu
                                </th>

                                <th class="px-6 py-4
                                           text-left
                                           text-sm
                                           font-extrabold
                                           text-[#0B2A55]">
                                    Élève
                                </th>

                                <th class="px-6 py-4
                                           text-left
                                           text-sm
                                           font-extrabold
                                           text-[#0B2A55]">
                                    Période
                                </th>

                                <th class="px-6 py-4
                                           text-left
                                           text-sm
                                           font-extrabold
                                           text-[#0B2A55]">
                                    Montant dû
                                </th>

                                <th class="px-6 py-4
                                           text-left
                                           text-sm
                                           font-extrabold
                                           text-[#0B2A55]">
                                    Payé
                                </th>

                                <th class="px-6 py-4
                                           text-left
                                           text-sm
                                           font-extrabold
                                           text-[#0B2A55]">
                                    Reste
                                </th>

                                <th class="px-6 py-4
                                           text-right
                                           text-sm
                                           font-extrabold
                                           text-[#0B2A55]">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100">

                            @forelse($payments as $payment)

                                @php

                                    if ($payment->amount_paid > 0 && $payment->remaining_amount > 0) {
                                        $status = 'partial';
                                    } else {
                                        $status = 'unpaid';
                                    }

                                @endphp


                                <tr class="hover:bg-gray-50 transition">


                                    {{-- Reçu --}}
                                    <td class="px-6 py-5">

                                        <span class="font-bold
                                                     text-[#0B2A55]">
                                            {{ $payment->receipt_number }}
                                        </span>

                                        <p class="text-xs text-gray-400 mt-1">
                                            {{ optional($payment->payment_date)->format('d/m/Y') }}
                                        </p>

                                    </td>


                                    {{-- Élève --}}
                                    <td class="px-6 py-5">

                                        @if($payment->student)

                                            <div class="font-bold text-gray-800">
                                                {{ $payment->student->last_name }}
                                                {{ $payment->student->first_name }}
                                            </div>

                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $payment->student->level ?? '-' }}
                                            </div>

                                        @else

                                            <span class="text-gray-400">
                                                Élève supprimé
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Période --}}
                                    <td class="px-6 py-5">

                                        <span class="px-3 py-1
                                                     rounded-full
                                                     bg-blue-50
                                                     text-blue-700
                                                     text-sm
                                                     font-semibold">
                                            {{ $payment->period }}
                                        </span>

                                    </td>


                                    {{-- Dû --}}
                                    <td class="px-6 py-5">

                                        <span class="font-semibold text-gray-700">
                                            {{ number_format($payment->amount_due, 2, ',', ' ') }}
                                            DA
                                        </span>

                                    </td>


                                    {{-- Payé --}}
                                    <td class="px-6 py-5">

                                        <span class="font-bold text-green-600">
                                            {{ number_format($payment->amount_paid, 2, ',', ' ') }}
                                            DA
                                        </span>

                                    </td>


                                    {{-- Reste --}}
                                    <td class="px-6 py-5">

                                        <span class="font-bold text-red-600">
                                            {{ number_format($payment->remaining_amount, 2, ',', ' ') }}
                                            DA
                                        </span>

                                    </td>


                                    {{-- Actions --}}
                                    <td class="px-6 py-5">

                                        <div class="flex justify-end items-center gap-2">

                                            <a href="{{ route('payments.show', $payment) }}"
                                               class="px-4 py-2
                                                      rounded-xl
                                                      bg-gray-100
                                                      text-gray-700
                                                      font-semibold
                                                      text-sm
                                                      hover:bg-gray-200
                                                      transition">
                                                Voir
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="7"
                                        class="px-6 py-16 text-center">

                                        <div class="flex flex-col
                                                    items-center
                                                    justify-center">

                                            <div class="w-20 h-20
                                                        rounded-3xl
                                                        bg-gray-100
                                                        flex items-center
                                                        justify-center
                                                        text-4xl
                                                        mb-4">
                                                💳
                                            </div>

                                            <h3 class="text-lg
                                                       font-extrabold
                                                       text-[#0B2A55]">
                                                Aucun paiement impayé
                                            </h3>

                                            <p class="text-sm
                                                      text-gray-500
                                                      mt-1">
                                                Tous les paiements sont soldés.
                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- Pagination --}}
                @if($payments->hasPages())

                    <div class="px-6 py-5
                                border-t border-gray-100">

                        {{ $payments->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>
