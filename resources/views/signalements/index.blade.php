<x-app-layout>

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <x-slot name="header">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex items-center gap-4">

                <div class="w-14 h-14 rounded-2xl bg-[#0B2A55]
                            flex items-center justify-center shadow-md">

                    <span class="text-2xl">🔔</span>

                </div>

                <div>

                    <h2 class="font-extrabold text-2xl text-[#0B2A55]">
                        Signalement des impayés
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Suivi et relance des élèves ayant des paiements impayés.
                    </p>

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
                        rounded-3xl shadow-xl mb-8">

                <div class="absolute -right-16 -top-20
                            w-64 h-64 rounded-full bg-white/10">
                </div>

                <div class="relative p-8 md:p-10">

                    <p class="text-[#C89B3C]
                              font-bold tracking-wide
                              uppercase text-sm">

                        Académie El Tafawok

                    </p>

                    <div class="flex items-center gap-3 mt-2">

                        <h1 class="text-3xl md:text-4xl
                                   font-extrabold text-white">

                            Suivi des impayés

                        </h1>

                        <span class="text-3xl">
                            ⚠️
                        </span>

                    </div>

                    <p class="mt-3 text-blue-100
                              text-base md:text-lg">

                        Identifiez rapidement les élèves concernés
                        et suivez les relances de paiement.

                    </p>

                </div>

            </div>


            {{-- =================================================
                STATISTIQUES
            ================================================== --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">


                {{-- TOTAL IMPAYÉ --}}
                <div class="relative overflow-hidden
                            bg-white rounded-3xl
                            border border-gray-100
                            shadow-sm">

                    <div class="absolute right-0 top-0
                                w-32 h-32
                                bg-red-50 rounded-bl-full">
                    </div>

                    <div class="relative p-6">

                        <div class="flex items-center justify-between">

                            <div class="w-14 h-14 rounded-2xl
                                        bg-red-100
                                        flex items-center justify-center
                                        text-2xl">

                                💰

                            </div>

                            <span class="px-4 py-2 rounded-full
                                         bg-red-50 text-red-600
                                         text-xs font-extrabold uppercase">

                                Impayé

                            </span>

                        </div>

                        <p class="mt-6 text-sm text-gray-500">
                            Total restant
                        </p>

                        <p class="mt-1 text-3xl
                                  font-extrabold text-red-600">

                            {{ number_format($totalUnpaid ?? 0, 2, ',', ' ') }}

                            <span class="text-base font-bold">
                                DA
                            </span>

                        </p>

                    </div>

                </div>


                {{-- À RELANCER --}}
                <div class="bg-yellow-50
                            border border-yellow-200
                            rounded-3xl p-6
                            flex items-center justify-between">

                    <div>

                        <p class="text-yellow-700 font-bold">
                            À relancer
                        </p>

                        <p class="text-3xl font-extrabold
                                  text-yellow-700 mt-2">

                            {{ $pendingCount ?? 0 }}

                        </p>

                        <p class="text-sm text-yellow-600 mt-1">
                            signalement(s)
                        </p>

                    </div>

                    <div class="w-14 h-14 rounded-2xl
                                bg-yellow-100
                                flex items-center justify-center
                                text-3xl">

                        🔔

                    </div>

                </div>


                {{-- CONTACTÉS --}}
                <div class="bg-blue-50
                            border border-blue-200
                            rounded-3xl p-6
                            flex items-center justify-between">

                    <div>

                        <p class="text-blue-700 font-bold">
                            Contactés
                        </p>

                        <p class="text-3xl font-extrabold
                                  text-blue-700 mt-2">

                            {{ $contactedCount ?? 0 }}

                        </p>

                        <p class="text-sm text-blue-600 mt-1">
                            signalement(s)
                        </p>

                    </div>

                    <div class="w-14 h-14 rounded-2xl
                                bg-blue-100
                                flex items-center justify-center
                                text-3xl">

                        📞

                    </div>

                </div>

            </div>


            {{-- =================================================
                MESSAGE SUCCESS
            ================================================== --}}
            @if(session('success'))

                <div class="mb-6
                            bg-green-50
                            border border-green-200
                            text-green-700
                            rounded-2xl
                            px-5 py-4
                            flex items-center gap-3">

                    <span class="text-xl">
                        ✅
                    </span>

                    <span class="font-semibold">
                        {{ session('success') }}
                    </span>

                </div>

            @endif


            {{-- =================================================
                RECHERCHE
            ================================================== --}}
            <div class="bg-white rounded-3xl
                        border border-gray-100
                        shadow-sm p-6 mb-8">

                <div class="flex items-center gap-3 mb-5">

                    <div class="w-11 h-11 rounded-xl
                                bg-[#0B2A55]
                                flex items-center justify-center
                                text-xl">

                        🔎

                    </div>

                    <div>

                        <h3 class="text-lg font-extrabold
                                   text-[#0B2A55]">

                            Rechercher un impayé

                        </h3>

                        <p class="text-sm text-gray-500">

                            Recherchez par élève, parent,
                            période ou statut.

                        </p>

                    </div>

                </div>


                <form method="GET"
                      action="{{ route('signalements.index') }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">


                        {{-- Recherche --}}
                        <div>

                            <label class="block text-sm
                                          font-bold text-gray-700 mb-2">

                                Élève

                            </label>

                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Nom, prénom ou parent..."
                                class="w-full rounded-xl
                                       border-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#0B2A55]"
                            >

                        </div>


                        {{-- Période --}}
                        <div>

                            <label class="block text-sm
                                          font-bold text-gray-700 mb-2">

                                Période

                            </label>

                            <input
                                type="text"
                                name="period"
                                value="{{ request('period') }}"
                                placeholder="Ex : Septembre"
                                class="w-full rounded-xl
                                       border-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#0B2A55]"
                            >

                        </div>


                        {{-- Statut --}}
                        <div>

                            <label class="block text-sm
                                          font-bold text-gray-700 mb-2">

                                Statut

                            </label>

                            <select
                                name="status"
                                class="w-full rounded-xl
                                       border-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#0B2A55]"
                            >

                                <option value="">
                                    Tous les statuts
                                </option>

                                <option value="pending"
                                    {{ request('status') == 'pending' ? 'selected' : '' }}>

                                    🔔 À relancer

                                </option>

                                <option value="contacted"
                                    {{ request('status') == 'contacted' ? 'selected' : '' }}>

                                    📞 Contacté

                                </option>

                                <option value="paid"
                                    {{ request('status') == 'paid' ? 'selected' : '' }}>

                                    ✅ Payé

                                </option>

                                <option value="cancelled"
                                    {{ request('status') == 'cancelled' ? 'selected' : '' }}>

                                    ❌ Annulé

                                </option>

                            </select>

                        </div>

                    </div>


                    <div class="flex gap-3 mt-5">

                        <button
                            type="submit"
                            class="px-6 py-3 rounded-xl
                                   bg-[#0B2A55]
                                   text-white font-bold
                                   hover:bg-[#061A33]">

                            🔎 Rechercher

                        </button>

                        <a href="{{ route('signalements.index') }}"
                           class="px-6 py-3 rounded-xl
                                  bg-gray-100
                                  text-gray-700
                                  font-bold
                                  hover:bg-gray-200">

                            Réinitialiser

                        </a>

                    </div>

                </form>

            </div>


            {{-- =================================================
                TABLEAU
            ================================================== --}}
            <div class="bg-white rounded-3xl
                        border border-gray-100
                        shadow-sm overflow-hidden">

                <div class="overflow-x-auto">

                    <table class="min-w-full">

                        <thead class="bg-gray-50
                                      border-b border-gray-200">

                            <tr>

                                <th class="px-6 py-4 text-left
                                           text-sm font-extrabold
                                           text-[#0B2A55]">

                                    Élève

                                </th>

                                <th class="px-6 py-4 text-left
                                           text-sm font-extrabold
                                           text-[#0B2A55]">

                                    Période

                                </th>

                                <th class="px-6 py-4 text-left
                                           text-sm font-extrabold
                                           text-[#0B2A55]">

                                    Montant restant

                                </th>

                                <th class="px-6 py-4 text-left
                                           text-sm font-extrabold
                                           text-[#0B2A55]">

                                    Relances

                                </th>

                                <th class="px-6 py-4 text-left
                                           text-sm font-extrabold
                                           text-[#0B2A55]">

                                    Statut

                                </th>

                                <th class="px-6 py-4 text-right
                                           text-sm font-extrabold
                                           text-[#0B2A55]">

                                    Actions

                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100">

                            @forelse($signalements as $signalement)

                                <tr class="hover:bg-gray-50 transition">


                                    {{-- Élève --}}
                                    <td class="px-6 py-5">

                                        @if($signalement->student)

                                            <div class="font-bold text-gray-800">

                                                {{ $signalement->student->last_name }}
                                                {{ $signalement->student->first_name }}

                                            </div>

                                            <div class="text-xs text-gray-500 mt-1">

                                                {{ $signalement->student->level ?? '-' }}

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

                                            {{ $signalement->period }}

                                        </span>

                                    </td>


                                    {{-- Montant --}}
                                    <td class="px-6 py-5">

                                        <span class="font-extrabold
                                            text-red-600">

                                            {{ number_format(
                                                $signalement->amount_remaining,
                                                2,
                                                ',',
                                                ' '
                                            ) }}

                                            DA

                                        </span>

                                    </td>


                                    {{-- Relances --}}
                                    <td class="px-6 py-5">

                                        <span class="font-bold
                                                     text-[#0B2A55]">

                                            {{ $signalement->reminder_count }}

                                        </span>

                                        @if($signalement->last_reminder_at)

                                            <p class="text-xs text-gray-400 mt-1">

                                                {{ $signalement->last_reminder_at->format('d/m/Y H:i') }}

                                            </p>

                                        @else

                                            <p class="text-xs text-gray-400 mt-1">
                                                Jamais
                                            </p>

                                        @endif

                                    </td>


                                    {{-- Statut --}}
                                    <td class="px-6 py-5">

                                        @if($signalement->status === 'pending')

                                            <span class="inline-flex
                                                         items-center gap-1
                                                         px-3 py-1
                                                         rounded-full
                                                         bg-yellow-100
                                                         text-yellow-700
                                                         text-sm font-bold">

                                                🔔 À relancer

                                            </span>

                                        @elseif($signalement->status === 'contacted')

                                            <span class="inline-flex
                                                         items-center gap-1
                                                         px-3 py-1
                                                         rounded-full
                                                         bg-blue-100
                                                         text-blue-700
                                                         text-sm font-bold">

                                                📞 Contacté

                                            </span>

                                        @elseif($signalement->status === 'paid')

                                            <span class="inline-flex
                                                         items-center gap-1
                                                         px-3 py-1
                                                         rounded-full
                                                         bg-green-100
                                                         text-green-700
                                                         text-sm font-bold">

                                                ✅ Payé

                                            </span>

                                        @else

                                            <span class="inline-flex
                                                         items-center gap-1
                                                         px-3 py-1
                                                         rounded-full
                                                         bg-gray-100
                                                         text-gray-600
                                                         text-sm font-bold">

                                                ❌ Annulé

                                            </span>

                                        @endif

                                    </td>


                                    {{-- Actions --}}
                                    <td class="px-6 py-5">

                                        <div class="flex justify-end
                                                    items-center gap-2">

                                            <a href="{{ route(
                                                'signalements.show',
                                                $signalement
                                            ) }}"
                                               class="px-4 py-2
                                                      rounded-xl
                                                      bg-gray-100
                                                      text-gray-700
                                                      font-semibold
                                                      text-sm
                                                      hover:bg-gray-200">

                                                Voir

                                            </a>


                                            @if(
                                                $signalement->status === 'pending'
                                                ||
                                                $signalement->status === 'contacted'
                                            )

                                                <form method="POST"
                                                      action="{{ route(
                                                          'signalements.contacted',
                                                          $signalement
                                                      ) }}">

                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="px-4 py-2
                                                               rounded-xl
                                                               bg-blue-100
                                                               text-blue-700
                                                               font-semibold
                                                               text-sm
                                                               hover:bg-blue-200">

                                                        📞 Relancer

                                                    </button>

                                                </form>

                                            @endif

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6"
                                        class="px-6 py-16 text-center">

                                        <div class="flex flex-col
                                                    items-center">

                                            <div class="w-20 h-20
                                                        rounded-3xl
                                                        bg-green-100
                                                        flex items-center
                                                        justify-center
                                                        text-4xl mb-4">

                                                ✅

                                            </div>

                                            <h3 class="text-lg
                                                       font-extrabold
                                                       text-[#0B2A55]">

                                                Aucun impayé signalé

                                            </h3>

                                            <p class="text-sm
                                                      text-gray-500 mt-1">

                                                Tous les paiements sont à jour.

                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- Pagination --}}
                @if($signalements->hasPages())

                    <div class="px-6 py-5 border-t border-gray-100">

                        {{ $signalements->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>