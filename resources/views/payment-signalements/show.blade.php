<x-app-layout>

    {{-- =========================
        HEADER
    ========================== --}}
    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">

            <div class="flex items-center gap-3">

                <div class="w-11 h-11 rounded-xl bg-[#0B2A55]
                            flex items-center justify-center shadow-md">
                    <span class="text-xl">🚨</span>
                </div>

                <div>
                    <h2 class="font-extrabold text-2xl text-[#0B2A55]">
                        Détail de l'impayé
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Consultation des informations du signalement.
                    </p>
                </div>

            </div>

            <a href="{{ route('payment-signalements.index') }}"
               class="inline-flex items-center justify-center gap-2
                      px-5 py-3 rounded-xl
                      bg-gray-100 hover:bg-gray-200
                      text-gray-700 font-bold transition">

                ← Retour aux impayés

            </a>

        </div>

    </x-slot>


    {{-- =========================
        PAGE
    ========================== --}}
    <div class="py-8 bg-gray-50 min-h-screen">

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- =========================
                SUCCESS
            ========================== --}}

            @if(session('success'))

                <div class="mb-6 flex items-center gap-3
                            bg-green-50 border border-green-200
                            text-green-700 rounded-2xl px-5 py-4">

                    <span class="text-xl">✅</span>

                    <p class="font-semibold">
                        {{ session('success') }}
                    </p>

                </div>

            @endif


            {{-- =========================
                BANNER
            ========================== --}}

            <div class="relative overflow-hidden
                        bg-gradient-to-r from-[#061A33]
                        via-[#0B2A55]
                        to-[#17477F]
                        rounded-3xl shadow-xl
                        p-7 md:p-9 mb-8">

                <div class="absolute -right-16 -top-24
                            w-80 h-80 rounded-full
                            border border-[#C89B3C]/30">
                </div>

                <div class="absolute right-[-30px] top-[-30px]
                            w-60 h-60 rounded-full
                            bg-white/5">
                </div>

                <div class="relative z-10">

                    <p class="text-[#C89B3C] text-sm
                              font-extrabold tracking-wider">
                        ACADÉMIE EL TAFAWOK
                    </p>

                    <h1 class="text-3xl md:text-4xl
                               font-extrabold text-white mt-2">
                        Détail de l'impayé 🚨
                    </h1>

                    <p class="text-blue-100 mt-3 text-base md:text-lg">
                        Consultez les informations du paiement et
                        le montant restant à régler.
                    </p>

                </div>

            </div>


            {{-- =========================
                ÉLÈVE + STATUT
            ========================== --}}

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">


                {{-- ÉLÈVE --}}
                <div class="lg:col-span-2
                            bg-white rounded-3xl
                            border border-gray-100
                            shadow-sm p-6">

                    <div class="flex items-center gap-4">

                        <div class="w-16 h-16 rounded-2xl
                                    bg-blue-50
                                    flex items-center justify-center
                                    text-3xl">
                            👨‍🎓
                        </div>

                        <div>

                            <p class="text-sm text-gray-500">
                                Élève
                            </p>

                            <h2 class="text-2xl font-extrabold
                                       text-[#0B2A55] mt-1">

                                {{ $paymentSignalement->student->first_name ?? '' }}
                                {{ $paymentSignalement->student->last_name ?? '' }}

                            </h2>

                            @if($paymentSignalement->student?->parent_name)

                                <p class="text-sm text-gray-500 mt-1">
                                    Parent :
                                    {{ $paymentSignalement->student->parent_name }}
                                </p>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- STATUT --}}
                <div class="bg-white rounded-3xl
                            border border-gray-100
                            shadow-sm p-6">

                    <p class="text-sm text-gray-500 mb-3">
                        Statut du signalement
                    </p>

                    @if($paymentSignalement->status === 'pending')

                        <div class="inline-flex items-center gap-2
                                    px-4 py-2 rounded-full
                                    bg-amber-100 text-amber-700
                                    font-extrabold">

                            ⏳ En attente

                        </div>

                    @elseif($paymentSignalement->status === 'sent')

                        <div class="inline-flex items-center gap-2
                                    px-4 py-2 rounded-full
                                    bg-blue-100 text-blue-700
                                    font-extrabold">

                            📤 Envoyé

                        </div>

                    @elseif($paymentSignalement->status === 'resolved')

                        <div class="inline-flex items-center gap-2
                                    px-4 py-2 rounded-full
                                    bg-green-100 text-green-700
                                    font-extrabold">

                            ✅ Résolu

                        </div>

                    @endif

                </div>

            </div>


            {{-- =========================
                MONTANT RESTANT
            ========================== --}}

            <div class="relative overflow-hidden
                        bg-white rounded-3xl
                        border border-red-100
                        shadow-sm p-7 mb-6">

                <div class="absolute right-0 top-0
                            w-64 h-64 rounded-bl-full
                            bg-red-50">
                </div>

                <div class="relative z-10
                            flex flex-col md:flex-row
                            md:items-center
                            md:justify-between gap-6">

                    <div>

                        <p class="text-sm text-gray-500">
                            Montant restant à payer
                        </p>

                        <p class="text-4xl font-extrabold
                                  text-red-600 mt-2">

                            {{ number_format(
                                $paymentSignalement->amount_remaining,
                                2,
                                ',',
                                ' '
                            ) }}

                            <span class="text-xl">
                                DA
                            </span>

                        </p>

                    </div>

                    <div class="w-16 h-16 rounded-2xl
                                bg-red-100
                                flex items-center justify-center
                                text-3xl">

                        ⚠️

                    </div>

                </div>

            </div>


            {{-- =========================
                INFORMATIONS
            ========================== --}}

            <div class="bg-white rounded-3xl
                        border border-gray-100
                        shadow-sm overflow-hidden mb-6">

                <div class="px-6 py-5
                            border-b border-gray-100">

                    <h2 class="text-xl font-extrabold
                               text-[#0B2A55]">

                        📋 Informations du signalement

                    </h2>

                </div>


                <div class="p-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                        {{-- PÉRIODE --}}
                        <div class="p-5 rounded-2xl bg-gray-50">

                            <p class="text-sm text-gray-500">
                                Période
                            </p>

                            <p class="font-extrabold
                                      text-[#0B2A55] text-lg mt-1">

                                {{ $paymentSignalement->period }}

                            </p>

                        </div>


                        {{-- DATE --}}
                        <div class="p-5 rounded-2xl bg-gray-50">

                            <p class="text-sm text-gray-500">
                                Date du signalement
                            </p>

                            <p class="font-extrabold
                                      text-[#0B2A55] text-lg mt-1">

                                {{ $paymentSignalement->signalement_date
                                    ? \Carbon\Carbon::parse(
                                        $paymentSignalement->signalement_date
                                      )->format('d/m/Y')
                                    : '—'
                                }}

                            </p>

                        </div>


                        {{-- DATE ENVOI --}}
                        <div class="p-5 rounded-2xl bg-gray-50">

                            <p class="text-sm text-gray-500">
                                Date d'envoi
                            </p>

                            <p class="font-extrabold
                                      text-[#0B2A55] text-lg mt-1">

                                @if($paymentSignalement->sent_at)

                                    {{ \Carbon\Carbon::parse(
                                        $paymentSignalement->sent_at
                                    )->format('d/m/Y H:i') }}

                                @else

                                    Non envoyé

                                @endif

                            </p>

                        </div>


                        {{-- ID PAYMENT --}}
                        <div class="p-5 rounded-2xl bg-gray-50">

                            <p class="text-sm text-gray-500">
                                Référence du paiement
                            </p>

                            <p class="font-extrabold
                                      text-[#0B2A55] text-lg mt-1">

                                @if($paymentSignalement->payment)

                                    {{ $paymentSignalement->payment->receipt_number }}

                                @else

                                    —

                                @endif

                            </p>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =========================
                PAIEMENT ORIGINAL
            ========================== --}}

            @if($paymentSignalement->payment)

                <div class="bg-white rounded-3xl
                            border border-gray-100
                            shadow-sm overflow-hidden mb-6">

                    <div class="px-6 py-5
                                border-b border-gray-100">

                        <h2 class="text-xl font-extrabold
                                   text-[#0B2A55]">

                            💳 Paiement concerné

                        </h2>

                    </div>


                    <div class="p-6">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">


                            {{-- DÛ --}}
                            <div class="p-5 rounded-2xl bg-blue-50">

                                <p class="text-sm text-gray-500">
                                    Montant demandé
                                </p>

                                <p class="text-2xl font-extrabold
                                          text-[#0B2A55] mt-1">

                                    {{ number_format(
                                        $paymentSignalement->payment->amount_due,
                                        2,
                                        ',',
                                        ' '
                                    ) }}

                                    <span class="text-sm">
                                        DA
                                    </span>

                                </p>

                            </div>


                            {{-- PAYÉ --}}
                            <div class="p-5 rounded-2xl bg-green-50">

                                <p class="text-sm text-gray-500">
                                    Montant payé
                                </p>

                                <p class="text-2xl font-extrabold
                                          text-green-600 mt-1">

                                    {{ number_format(
                                        $paymentSignalement->payment->amount_paid,
                                        2,
                                        ',',
                                        ' '
                                    ) }}

                                    <span class="text-sm">
                                        DA
                                    </span>

                                </p>

                            </div>


                            {{-- RESTE --}}
                            <div class="p-5 rounded-2xl bg-red-50">

                                <p class="text-sm text-gray-500">
                                    Reste
                                </p>

                                <p class="text-2xl font-extrabold
                                          text-red-600 mt-1">

                                    {{ number_format(
                                        $paymentSignalement->payment->remaining_amount,
                                        2,
                                        ',',
                                        ' '
                                    ) }}

                                    <span class="text-sm">
                                        DA
                                    </span>

                                </p>

                            </div>


                            {{-- MODE --}}
                            <div class="p-5 rounded-2xl bg-gray-50">

                                <p class="text-sm text-gray-500">
                                    Mode de paiement
                                </p>

                                <p class="font-extrabold
                                          text-[#0B2A55] mt-1">

                                    {{ $paymentSignalement->payment->payment_method }}

                                </p>

                            </div>


                            {{-- DATE --}}
                            <div class="p-5 rounded-2xl bg-gray-50">

                                <p class="text-sm text-gray-500">
                                    Date du paiement
                                </p>

                                <p class="font-extrabold
                                          text-[#0B2A55] mt-1">

                                    {{ $paymentSignalement->payment->payment_date
                                        ? \Carbon\Carbon::parse(
                                            $paymentSignalement->payment->payment_date
                                          )->format('d/m/Y')
                                        : '—'
                                    }}

                                </p>

                            </div>


                            {{-- HEURE --}}
                            <div class="p-5 rounded-2xl bg-gray-50">

                                <p class="text-sm text-gray-500">
                                    Heure
                                </p>

                                <p class="font-extrabold
                                          text-[#0B2A55] mt-1">

                                    {{ $paymentSignalement->payment->payment_time ?? '—' }}

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =========================
                NOTE
            ========================== --}}

            @if($paymentSignalement->note)

                <div class="bg-amber-50
                            border border-amber-200
                            rounded-3xl p-6 mb-6">

                    <div class="flex gap-3">

                        <span class="text-2xl">
                            📝
                        </span>

                        <div>

                            <h3 class="font-extrabold
                                       text-amber-800">
                                Observation
                            </h3>

                            <p class="text-amber-700 mt-2">
                                {{ $paymentSignalement->note }}
                            </p>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =========================
                ACTIONS
            ========================== --}}

            <div class="bg-white rounded-3xl
                        border border-gray-100
                        shadow-sm p-6">

                <div class="flex flex-col sm:flex-row
                            justify-end gap-3">


                    {{-- RETOUR --}}
                    <a href="{{ route('payment-signalements.index') }}"
                       class="inline-flex items-center
                              justify-center
                              px-6 py-3 rounded-xl
                              bg-gray-100
                              hover:bg-gray-200
                              text-gray-700
                              font-bold transition">

                        ← Retour

                    </a>


                    {{-- ENVOYÉ --}}
                    @if($paymentSignalement->status === 'pending')

                        <form method="POST"
                              action="{{ route(
    'payment-signalements.resolved',
    $paymentSignalement
) }}">

                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="w-full sm:w-auto
                                           inline-flex items-center
                                           justify-center gap-2
                                           px-6 py-3 rounded-xl
                                           bg-blue-600
                                           hover:bg-blue-700
                                           text-white font-bold
                                           shadow-md transition">

                                📤 Marquer comme envoyé

                            </button>

                        </form>

                    @endif


                    {{-- RÉSOLU --}}
                    @if($paymentSignalement->status !== 'resolved')

                        <form method="POST"
                              action="{{ route(
    'payment-signalements.resolved',
    $paymentSignalement
) }}"
                              >

                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    onclick="return confirm(
                                        'Voulez-vous vraiment marquer cet impayé comme résolu ?'
                                    )"
                                    class="w-full sm:w-auto
                                           inline-flex items-center
                                           justify-center gap-2
                                           px-6 py-3 rounded-xl
                                           bg-[#0B2A55]
                                           hover:bg-[#061A33]
                                           text-white font-bold
                                           shadow-lg
                                           border-b-4
                                           border-[#C89B3C]
                                           transition">

                                ✅ Marquer comme résolu

                            </button>

                        </form>

                    @endif

                </div>

            </div>


        </div>

    </div>

</x-app-layout>