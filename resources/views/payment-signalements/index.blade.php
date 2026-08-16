<x-app-layout>

    {{-- =========================
        HEADER
    ========================== --}}
    <x-slot name="header">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div class="flex items-center gap-4">

                <div class="w-16 h-16 rounded-2xl bg-[#0B2A55]
                            flex items-center justify-center
                            text-3xl shadow-md">
                    🚨
                </div>

                <div>
                    <h2 class="font-bold text-2xl md:text-3xl text-[#0B2A55]">
                        Impayés
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Gestion et suivi des paiements en retard des élèves.
                    </p>
                </div>

            </div>

            {{-- Générer automatiquement --}}
            <form method="POST"
                  action="{{ route('payment-signalements.generate') }}">

                @csrf

                <button type="submit"
                        class="inline-flex items-center gap-2
                               px-6 py-3
                               bg-[#0B2A55]
                               text-white
                               font-semibold
                               rounded-xl
                               shadow-md
                               border-b-4 border-[#C89B3C]
                               hover:bg-[#17477F]
                               transition">

                    <span class="text-lg">⚡</span>

                    Générer les impayés

                </button>

            </form>

        </div>

    </x-slot>


    {{-- =========================
        PAGE
    ========================== --}}

    <div class="py-8 bg-gray-50 min-h-screen">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- =========================
                MESSAGE SUCCESS
            ========================== --}}

            @if(session('success'))

                <div class="mb-6 p-4 rounded-xl
                            bg-green-50
                            border border-green-200
                            text-green-700
                            flex items-center gap-3">

                    <span class="text-xl">✅</span>

                    <span class="font-medium">
                        {{ session('success') }}
                    </span>

                </div>

            @endif


            {{-- =========================
                BANNER
            ========================== --}}

            <div class="relative overflow-hidden
                        bg-gradient-to-r from-[#0B2A55] to-[#17477F]
                        rounded-3xl
                        p-8
                        mb-8
                        shadow-lg">

                {{-- Décor --}}
                <div class="absolute -right-20 -top-20
                            w-64 h-64
                            rounded-full
                            border border-white/20">
                </div>

                <div class="absolute right-10 -bottom-24
                            w-72 h-72
                            rounded-full
                            bg-white/5">
                </div>


                <div class="relative z-10">

                    <p class="text-[#C89B3C]
                              font-semibold
                              uppercase
                              tracking-wide
                              text-sm">

                        ACADÉMIE EL TAFAWOK

                    </p>


                    <h1 class="text-3xl md:text-4xl
                               font-bold
                               text-white
                               mt-3">

                        Gestion des impayés 🚨

                    </h1>


                    <p class="text-blue-100
                              text-base md:text-lg
                              mt-3
                              max-w-3xl">

                        Suivez les élèves ayant un montant restant à payer
                        et gérez facilement les signalements.

                    </p>

                </div>

            </div>



            {{-- =========================
                STATISTIQUES
            ========================== --}}

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4
                        gap-6 mb-8">


                {{-- EN ATTENTE --}}
                <div class="relative overflow-hidden
                            bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            p-6">

                    <div class="absolute right-0 top-0
                                w-32 h-32
                                bg-yellow-50
                                rounded-bl-full">
                    </div>

                    <div class="relative z-10">

                        <div class="w-14 h-14
                                    rounded-xl
                                    bg-yellow-100
                                    flex items-center justify-center
                                    text-2xl mb-5">

                            ⏳

                        </div>

                        <p class="text-sm text-gray-500">
                            En attente
                        </p>

                        <p class="text-3xl font-bold
                                  text-[#0B2A55]
                                  mt-2">

                            {{ $pendingCount }}

                        </p>

                        <p class="text-sm text-yellow-600 mt-2 font-medium">
                            Signalements à traiter
                        </p>

                    </div>

                </div>



                {{-- ENVOYÉS --}}
                <div class="relative overflow-hidden
                            bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            p-6">

                    <div class="absolute right-0 top-0
                                w-32 h-32
                                bg-blue-50
                                rounded-bl-full">
                    </div>

                    <div class="relative z-10">

                        <div class="w-14 h-14
                                    rounded-xl
                                    bg-blue-100
                                    flex items-center justify-center
                                    text-2xl mb-5">

                            📩

                        </div>

                        <p class="text-sm text-gray-500">
                            Signalés
                        </p>

                        <p class="text-3xl font-bold
                                  text-[#0B2A55]
                                  mt-2">

                            {{ $sentCount }}

                        </p>

                        <p class="text-sm text-blue-600 mt-2 font-medium">
                            Signalements envoyés
                        </p>

                    </div>

                </div>



                {{-- RÉSOLUS --}}
                <div class="relative overflow-hidden
                            bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            p-6">

                    <div class="absolute right-0 top-0
                                w-32 h-32
                                bg-green-50
                                rounded-bl-full">
                    </div>

                    <div class="relative z-10">

                        <div class="w-14 h-14
                                    rounded-xl
                                    bg-green-100
                                    flex items-center justify-center
                                    text-2xl mb-5">

                            ✅

                        </div>

                        <p class="text-sm text-gray-500">
                            Résolus
                        </p>

                        <p class="text-3xl font-bold
                                  text-[#0B2A55]
                                  mt-2">

                            {{ $resolvedCount }}

                        </p>

                        <p class="text-sm text-green-600 mt-2 font-medium">
                            Impayés réglés
                        </p>

                    </div>

                </div>



                {{-- TOTAL RESTANT --}}
                <div class="relative overflow-hidden
                            bg-white
                            rounded-3xl
                            border border-red-100
                            shadow-sm
                            p-6">

                    <div class="absolute right-0 top-0
                                w-32 h-32
                                bg-red-50
                                rounded-bl-full">
                    </div>

                    <div class="relative z-10">

                        <div class="w-14 h-14
                                    rounded-xl
                                    bg-red-100
                                    flex items-center justify-center
                                    text-2xl mb-5">

                            💰

                        </div>

                        <p class="text-sm text-gray-500">
                            Total restant
                        </p>

                        <p class="text-3xl font-bold
                                  text-red-600
                                  mt-2">

                            {{ number_format($totalRemaining, 2, ',', ' ') }}

                            <span class="text-lg">DA</span>

                        </p>

                        <p class="text-sm text-red-500 mt-2 font-medium">
                            Montant à récupérer
                        </p>

                    </div>

                </div>

            </div>



            {{-- =========================
                RECHERCHE + FILTRES
            ========================== --}}

            <div class="bg-white
                        rounded-3xl
                        border border-gray-100
                        shadow-sm
                        p-6 mb-8">

                <div class="flex items-center gap-3 mb-6">

                    <div class="w-10 h-10
                                rounded-xl
                                bg-blue-50
                                flex items-center justify-center">

                        🔎

                    </div>

                    <div>

                        <h2 class="font-bold text-lg text-[#0B2A55]">
                            Recherche et filtres
                        </h2>

                        <p class="text-sm text-gray-500">
                            Rechercher un impayé rapidement
                        </p>

                    </div>

                </div>


                <form method="GET"
                      action="{{ route('payment-signalements.index') }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">


                        {{-- Recherche --}}
                        <div>

                            <label class="block text-sm font-medium
                                          text-gray-700 mb-2">

                                Élève / Parent

                            </label>

                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Nom de l'élève ou parent..."

                                   class="w-full
                                          rounded-xl
                                          border-gray-200
                                          focus:border-[#0B2A55]
                                          focus:ring-[#0B2A55]">

                        </div>


                        {{-- Année --}}
                        <div>

                            <label class="block text-sm font-medium
                                          text-gray-700 mb-2">

                                Année

                            </label>

                            <select name="year"
                                    class="w-full
                                           rounded-xl
                                           border-gray-200
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]">

                                @foreach($years as $availableYear)

                                    <option value="{{ $availableYear }}"
                                        {{ (int) request('year', now()->format('Y')) === (int) $availableYear ? 'selected' : '' }}>

                                        {{ $availableYear }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Période --}}
                        <div>

                            <label class="block text-sm font-medium
                                          text-gray-700 mb-2">

                                Période

                            </label>

                            <select name="period"
                                    class="w-full
                                           rounded-xl
                                           border-gray-200
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]">

                                <option value="">
                                    Tous les mois
                                </option>

                                @foreach($months as $monthOption)

                                    <option value="{{ $monthOption['value'] }}"
                                        {{ request('period') === $monthOption['value'] ? 'selected' : '' }}>

                                        {{ $monthOption['label'] }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Statut --}}
                        <div>

                            <label class="block text-sm font-medium
                                          text-gray-700 mb-2">

                                Statut

                            </label>

                            <select name="status"
                                    class="w-full
                                           rounded-xl
                                           border-gray-200
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]">

                                <option value="">
                                    Tous les statuts
                                </option>

                                <option value="pending"
                                    {{ request('status') === 'pending' ? 'selected' : '' }}>
                                    ⏳ En attente
                                </option>

                                <option value="sent"
                                    {{ request('status') === 'sent' ? 'selected' : '' }}>
                                    📩 Envoyé
                                </option>

                                <option value="resolved"
                                    {{ request('status') === 'resolved' ? 'selected' : '' }}>
                                    ✅ Résolu
                                </option>

                            </select>

                        </div>


                        {{-- Boutons --}}
                        <div class="flex items-end gap-2">

                            <button type="submit"
                                    class="flex-1
                                           px-5 py-3
                                           bg-[#0B2A55]
                                           text-white
                                           font-semibold
                                           rounded-xl
                                           hover:bg-[#17477F]
                                           transition">

                                🔎 Rechercher

                            </button>


                            <a href="{{ route('payment-signalements.index') }}"
                               class="px-5 py-3
                                      bg-gray-100
                                      text-gray-700
                                      font-semibold
                                      rounded-xl
                                      hover:bg-gray-200
                                      transition">

                                ↻

                            </a>

                        </div>

                    </div>

                </form>

            </div>



            {{-- =========================
                TABLEAU
            ========================== --}}

            <div class="bg-white
                        rounded-3xl
                        border border-gray-100
                        shadow-sm
                        overflow-hidden">


                <div class="p-6
                            border-b border-gray-100
                            flex flex-col md:flex-row
                            md:items-center
                            md:justify-between
                            gap-3">

                    <div>

                        <h2 class="text-xl font-bold text-[#0B2A55]">
                            Liste des impayés
                        </h2>

                        <p class="text-sm text-gray-500 mt-1">
                            Suivi des montants restant à payer
                        </p>

                    </div>

                    <div class="text-sm text-gray-500">

                        {{ $signalements->total() }}
                        signalement(s)

                    </div>

                </div>


                @if($signalements->count() > 0)

                    <div class="overflow-x-auto">

                        <table class="w-full text-left">

                            <thead class="bg-gray-50">

                                <tr>

                                    <th class="px-6 py-4
                                               text-xs font-semibold
                                               text-gray-500 uppercase">
                                        Élève
                                    </th>

                                    <th class="px-6 py-4
                                               text-xs font-semibold
                                               text-gray-500 uppercase">
                                        Parent
                                    </th>

                                    <th class="px-6 py-4
                                               text-xs font-semibold
                                               text-gray-500 uppercase">
                                        Matière
                                    </th>

                                    <th class="px-6 py-4
                                               text-xs font-semibold
                                               text-gray-500 uppercase">
                                        Type
                                    </th>

                                    <th class="px-6 py-4
                                               text-xs font-semibold
                                               text-gray-500 uppercase">
                                        Période
                                    </th>

                                    <th class="px-6 py-4
                                               text-xs font-semibold
                                               text-gray-500 uppercase">
                                        Montant restant
                                    </th>

                                    <th class="px-6 py-4
                                               text-xs font-semibold
                                               text-gray-500 uppercase">
                                        Statut
                                    </th>

                                    <th class="px-6 py-4
                                               text-xs font-semibold
                                               text-gray-500 uppercase
                                               text-right">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-100">

                                @foreach($signalements as $signalement)

                                    <tr class="hover:bg-gray-50 transition">


                                        {{-- Élève --}}
                                        <td class="px-6 py-5">

                                            <div class="flex items-center gap-3">

                                                <div class="w-11 h-11
                                                            rounded-xl
                                                            bg-blue-50
                                                            flex items-center justify-center
                                                            text-xl">

                                                    👨‍🎓

                                                </div>

                                                <div>

                                                    <p class="font-semibold text-gray-800">

                                                        {{ $signalement->student->first_name ?? '' }}

                                                        {{ $signalement->student->last_name ?? '' }}

                                                    </p>

                                                </div>

                                            </div>

                                        </td>


                                        {{-- Parent --}}
                                        <td class="px-6 py-5">

                                            <span class="text-sm text-gray-500">

                                                {{ $signalement->student->parent_name ?? '-' }}

                                            </span>

                                        </td>


                                        {{-- Matière --}}
                                        <td class="px-6 py-5">

                                            <span class="text-sm font-medium text-gray-700">

                                                {{ $signalement->subject->name ?? '-' }}

                                            </span>

                                        </td>


                                        {{-- Type --}}
                                        <td class="px-6 py-5">

                                            @if($signalement->type === 'vip')

                                                <span class="inline-flex items-center px-3 py-1.5
                                                             rounded-full
                                                             bg-purple-100
                                                             text-purple-700
                                                             text-xs
                                                             font-semibold">

                                                    VIP

                                                </span>

                                            @else

                                                <span class="inline-flex items-center px-3 py-1.5
                                                             rounded-full
                                                             bg-indigo-100
                                                             text-indigo-700
                                                             text-xs
                                                             font-semibold">

                                                    Mensuel

                                                </span>

                                            @endif

                                        </td>


                                        {{-- Période --}}
                                        <td class="px-6 py-5">

                                            <span class="text-sm font-medium text-gray-700">

                                                {{ $signalement->period_label ?? $signalement->period }}

                                            </span>

                                        </td>


                                        {{-- Montant --}}
                                        <td class="px-6 py-5">

                                            <span class="font-bold text-red-600">

                                                {{ number_format($signalement->amount_remaining, 2, ',', ' ') }}

                                                DA

                                            </span>

                                        </td>


                                        {{-- Statut --}}
                                        <td class="px-6 py-5">

                                            @if($signalement->status === 'pending')

                                                <span class="inline-flex items-center gap-1
                                                             px-3 py-1.5
                                                             rounded-full
                                                             bg-yellow-100
                                                             text-yellow-700
                                                             text-xs
                                                             font-semibold">

                                                    ⏳ En attente

                                                </span>

                                            @elseif($signalement->status === 'sent')

                                                <span class="inline-flex items-center gap-1
                                                             px-3 py-1.5
                                                             rounded-full
                                                             bg-blue-100
                                                             text-blue-700
                                                             text-xs
                                                             font-semibold">

                                                    📩 Envoyé

                                                </span>

                                            @else

                                                <span class="inline-flex items-center gap-1
                                                             px-3 py-1.5
                                                             rounded-full
                                                             bg-green-100
                                                             text-green-700
                                                             text-xs
                                                             font-semibold">

                                                    ✅ Résolu

                                                </span>

                                            @endif

                                        </td>


                                        {{-- Actions --}}
                                        <td class="px-6 py-5">

                                            <div class="flex items-center justify-end gap-2">


                                                {{-- Voir --}}
                                                @if($signalement->signalement)

                                                    <a href="{{ route(
                                                        'payment-signalements.show',
                                                        $signalement->signalement
                                                    ) }}"
                                                       class="w-10 h-10
                                                              rounded-xl
                                                              bg-blue-50
                                                              text-[#0B2A55]
                                                              flex items-center justify-center
                                                              hover:bg-blue-100
                                                              transition"
                                                       title="Voir">

                                                        👁️

                                                    </a>

                                                @endif


                                                {{-- Envoyer --}}
                                                @if($signalement->status === 'pending')

                                                    <form method="POST"
                                                          action="{{ route(
                                                              'payment-signalements.sent'
                                                          ) }}">

                                                        @csrf
                                                        @method('PATCH')

                                                        <input type="hidden"
                                                               name="student_id"
                                                               value="{{ $signalement->student?->id }}">

                                                        <input type="hidden"
                                                               name="subject_id"
                                                               value="{{ $signalement->subject?->id }}">

                                                        <input type="hidden"
                                                               name="period"
                                                               value="{{ $signalement->period }}">

                                                        <input type="hidden"
                                                               name="amount_remaining"
                                                               value="{{ $signalement->amount_remaining }}">

                                                        <button type="submit"
                                                                class="w-10 h-10
                                                                       rounded-xl
                                                                       bg-yellow-50
                                                                       text-yellow-700
                                                                       flex items-center justify-center
                                                                       hover:bg-yellow-100
                                                                       transition"
                                                                title="Marquer comme envoyé">

                                                            📩

                                                        </button>

                                                    </form>

                                                @endif


                                                {{-- Résoudre --}}
                                                @if($signalement->status !== 'resolved')

                                                    <form method="POST"
                                                          action="{{ route(
                                                              'payment-signalements.resolved'
                                                          ) }}">

                                                        @csrf
                                                        @method('PATCH')

                                                        <input type="hidden"
                                                               name="student_id"
                                                               value="{{ $signalement->student?->id }}">

                                                        <input type="hidden"
                                                               name="subject_id"
                                                               value="{{ $signalement->subject?->id }}">

                                                        <input type="hidden"
                                                               name="period"
                                                               value="{{ $signalement->period }}">

                                                        <button type="submit"
                                                                class="w-10 h-10
                                                                       rounded-xl
                                                                       bg-green-50
                                                                       text-green-700
                                                                       flex items-center justify-center
                                                                       hover:bg-green-100
                                                                       transition"
                                                                title="Marquer comme résolu">

                                                            ✅

                                                        </button>

                                                    </form>

                                                @endif

                                            </div>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>


                    {{-- =========================
                        PAGINATION
                    ========================== --}}

                    @if($signalements->hasPages())

                        <div class="p-6 border-t border-gray-100">

                            {{ $signalements->links() }}

                        </div>

                    @endif


                @else

                    {{-- =========================
                        AUCUN IMPAYÉ
                    ========================== --}}

                    <div class="text-center py-16 px-6">

                        <div class="w-20 h-20
                                    mx-auto
                                    rounded-2xl
                                    bg-green-50
                                    flex items-center justify-center
                                    text-4xl mb-5">

                            🎉

                        </div>

                        <h3 class="text-xl font-bold text-[#0B2A55]">

                            Aucun impayé

                        </h3>

                        <p class="text-gray-500 mt-2">

                            Aucun signalement ne correspond à votre recherche.

                        </p>

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>