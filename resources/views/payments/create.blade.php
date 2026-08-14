<x-app-layout>


<x-slot name="header">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">

        <div class="flex items-center gap-3">

            <div class="w-11 h-11 rounded-xl bg-[#0B2A55] flex items-center justify-center shadow-md">
                <span class="text-xl">💳</span>
            </div>

            <div>
                <h2 class="font-extrabold text-2xl text-[#0B2A55] dark:text-white">
                    Nouveau paiement
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Enregistrer le paiement d'un élève.
                </p>
            </div>

        </div>

        <a href="{{ route('payments.index') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold transition">

            ← Retour aux paiements

        </a>

    </div>
</x-slot>


<div class="py-8 bg-gradient-to-br from-gray-50 via-white to-blue-50 dark:from-gray-900 dark:to-gray-800 min-h-screen">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">


        {{-- ERREURS --}}
        @if ($errors->any())

            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-2xl p-5">

                <h3 class="font-bold mb-2">
                    ⚠️ Veuillez corriger les erreurs suivantes
                </h3>

                <ul class="list-disc list-inside text-sm">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif


        {{-- SUCCÈS --}}
        @if (session('success'))

            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 rounded-2xl p-5">

                {{ session('success') }}

            </div>

        @endif


        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-[#061A33] via-[#0B2A55] to-[#163E73] rounded-3xl shadow-xl p-6 md:p-8 mb-7">

            <p class="text-[#C89B3C] text-sm font-bold tracking-wider">
                ACADÉMIE EL TAFAWOK
            </p>

            <h1 class="text-2xl md:text-3xl text-white font-extrabold mt-1">
                Enregistrer un paiement 💳
            </h1>

            <p class="text-blue-100 text-sm md:text-base mt-2">
                Paiement mensuel ou VIP journalier.
            </p>

        </div>


        {{-- FORMULAIRE --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">

            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">

                <h3 class="text-xl font-extrabold text-[#0B2A55] dark:text-white">
                    💰 Informations du paiement
                </h3>

            </div>


            <form method="POST"
                  action="{{ route('payments.store') }}"
                  class="p-6 md:p-8">

                @csrf


                {{-- ===================================================== --}}
                {{-- ELEVE --}}
                {{-- ===================================================== --}}

                <div class="mb-6">

                    <label for="student_id"
                           class="block text-sm font-bold text-[#0B2A55] dark:text-gray-200 mb-2">

                        Élève <span class="text-red-500">*</span>

                    </label>

                    <select id="student_id"
                            name="student_id"
                            required
                            class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-gray-50 outline-none focus:border-[#C89B3C]">

                        <option value="">
                            -- Sélectionner un élève --
                        </option>

                        @foreach($students as $student)

                            <option value="{{ $student->id }}"
                                {{ old('student_id') == $student->id ? 'selected' : '' }}>

                                {{ $student->last_name }}
                                {{ $student->first_name }}

                                @if($student->level)
                                    — {{ $student->level }}
                                @endif

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- ===================================================== --}}
                {{-- MATIERE --}}
                {{-- ===================================================== --}}

                <div class="mb-6">

                    <label for="subject_id"
                           class="block text-sm font-bold text-[#0B2A55] dark:text-gray-200 mb-2">

                        Matière <span class="text-red-500">*</span>

                    </label>

                    <select id="subject_id"
                            name="subject_id"
                            required
                            class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-gray-50 outline-none focus:border-[#C89B3C]">

                        <option value="">
                            -- Sélectionner une matière --
                        </option>

                        @foreach($subjects as $subject)

                            <option value="{{ $subject->id }}"
                                {{ old('subject_id') == $subject->id ? 'selected' : '' }}>

                                {{ $subject->name }}

                                @if($subject->code)
                                    — {{ $subject->code }}
                                @endif

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- ===================================================== --}}
                {{-- TYPE DE PAIEMENT --}}
                {{-- ===================================================== --}}

                <div class="mb-6">

                    <label for="payment_type"
                           class="block text-sm font-bold text-[#0B2A55] dark:text-gray-200 mb-2">

                        Type d'abonnement <span class="text-red-500">*</span>

                    </label>


                    <select id="payment_type"
                            name="payment_type"
                            required
                            class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-gray-50 outline-none focus:border-[#C89B3C]">

                        <option value="">
                            -- Sélectionner le type --
                        </option>

                        <option value="monthly"
                            {{ old('payment_type') == 'monthly' ? 'selected' : '' }}>

                            📅 Abonnement mensuel

                        </option>

                        <option value="vip"
                            {{ old('payment_type') == 'vip' ? 'selected' : '' }}>

                            ⭐ VIP / Paiement journalier

                        </option>

                    </select>


                    <p class="text-xs text-gray-500 mt-2">

                        💡 Un élève peut avoir un abonnement mensuel dans une matière
                        et VIP dans une autre.

                    </p>

                </div>


                {{-- ===================================================== --}}
                {{-- PERIODE MENSUELLE --}}
                {{-- ===================================================== --}}

                <div id="monthlyPeriod" class="mb-6">

                    <label for="period_month"
                           class="block text-sm font-bold text-[#0B2A55] dark:text-gray-200 mb-2">

                        Mois <span class="text-red-500">*</span>

                    </label>

                    <select id="period_month"
                            class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-gray-50 outline-none focus:border-[#C89B3C]">

                        <option value="">
                            -- Sélectionner le mois --
                        </option>

                        @foreach([
                            'Septembre',
                            'Octobre',
                            'Novembre',
                            'Décembre',
                            'Janvier',
                            'Février',
                            'Mars',
                            'Avril',
                            'Mai',
                            'Juin'
                        ] as $month)

                            <option value="{{ $month }}"
                                {{ old('period') == $month ? 'selected' : '' }}>

                                {{ $month }}

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- ===================================================== --}}
                {{-- PERIODE VIP --}}
                {{-- ===================================================== --}}

                <div id="vipPeriod" class="mb-6 hidden">

                    <label for="period_vip"
                           class="block text-sm font-bold text-[#0B2A55] dark:text-gray-200 mb-2">

                        Date du paiement VIP <span class="text-red-500">*</span>

                    </label>

                    <input type="date"
                           id="period_vip"
                           class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-gray-50 outline-none focus:border-[#C89B3C]"
                           value="{{ old('period') && preg_match('/^\d{4}-\d{2}-\d{2}$/', old('period')) ? old('period') : now()->format('Y-m-d') }}">

                    <p class="text-xs text-gray-500 mt-2">

                        ⭐ Le paiement VIP concerne uniquement cette journée.

                    </p>

                </div>


                {{-- Champ caché réel --}}
                <input type="hidden"
                       name="period"
                       id="period"
                       value="{{ old('period') }}">


                {{-- ===================================================== --}}
                {{-- MONTANTS --}}
                {{-- ===================================================== --}}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">


                    {{-- MONTANT DEMANDE --}}

                    <div>

                        <label for="amount_due"
                               class="block text-sm font-bold text-[#0B2A55] dark:text-gray-200 mb-2">

                            Montant demandé <span class="text-red-500">*</span>

                        </label>

                        <div class="relative">

                            <input type="number"
                                   id="amount_due"
                                   name="amount_due"
                                   value="{{ old('amount_due') }}"
                                   min="0"
                                   step="0.01"
                                   required
                                   placeholder="Ex : 3000"
                                   class="w-full px-4 py-3 pr-16 rounded-xl border-2 border-gray-200 bg-gray-50 outline-none focus:border-[#C89B3C]">

                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">
                                DA
                            </span>

                        </div>

                    </div>


                    {{-- MONTANT PAYE --}}

                    <div>

                        <label for="amount_paid"
                               class="block text-sm font-bold text-[#0B2A55] dark:text-gray-200 mb-2">

                            Montant payé <span class="text-red-500">*</span>

                        </label>

                        <div class="relative">

                            <input type="number"
                                   id="amount_paid"
                                   name="amount_paid"
                                   value="{{ old('amount_paid') }}"
                                   min="0"
                                   step="0.01"
                                   required
                                   placeholder="Ex : 3000"
                                   class="w-full px-4 py-3 pr-16 rounded-xl border-2 border-gray-200 bg-gray-50 outline-none focus:border-[#C89B3C]">

                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">
                                DA
                            </span>

                        </div>

                    </div>

                </div>


                {{-- ===================================================== --}}
                {{-- RESTE --}}
                {{-- ===================================================== --}}

                <div class="mb-6 p-5 rounded-2xl bg-blue-50 border border-blue-100">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm text-gray-500">
                                Reste à payer
                            </p>

                            <p id="remainingPreview"
                               class="text-2xl font-extrabold text-[#0B2A55] mt-1">

                                0.00 DA

                            </p>

                        </div>


                        <div id="paymentStatus"
                             class="px-4 py-2 rounded-full bg-gray-100 text-gray-600 font-bold text-sm">

                            En attente

                        </div>

                    </div>

                </div>


                {{-- ===================================================== --}}
                {{-- MODE PAIEMENT --}}
                {{-- ===================================================== --}}

                <div class="mb-6">

                    <label for="payment_method"
                           class="block text-sm font-bold text-[#0B2A55] dark:text-gray-200 mb-2">

                        Mode de paiement <span class="text-red-500">*</span>

                    </label>

                    <select id="payment_method"
                            name="payment_method"
                            required
                            class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-gray-50 outline-none focus:border-[#C89B3C]">

                        <option value="">
                            -- Sélectionner --
                        </option>

                        <option value="Espèces"
                            {{ old('payment_method') == 'Espèces' ? 'selected' : '' }}>
                            💵 Espèces
                        </option>

                        <option value="Virement bancaire"
                            {{ old('payment_method') == 'Virement bancaire' ? 'selected' : '' }}>
                            🏦 Virement bancaire
                        </option>

                        <option value="Carte bancaire"
                            {{ old('payment_method') == 'Carte bancaire' ? 'selected' : '' }}>
                            💳 Carte bancaire
                        </option>

                        <option value="Chèque"
                            {{ old('payment_method') == 'Chèque' ? 'selected' : '' }}>
                            🧾 Chèque
                        </option>

                    </select>

                </div>


                {{-- ===================================================== --}}
                {{-- NOTE --}}
                {{-- ===================================================== --}}

                <div class="mb-8">

                    <label for="note"
                           class="block text-sm font-bold text-[#0B2A55] dark:text-gray-200 mb-2">

                        Observation

                    </label>

                    <textarea id="note"
                              name="note"
                              rows="4"
                              placeholder="Ajouter une remarque si nécessaire..."
                              class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-gray-50 outline-none focus:border-[#C89B3C] resize-none">{{ old('note') }}</textarea>

                </div>


                {{-- ===================================================== --}}
                {{-- BOUTONS --}}
                {{-- ===================================================== --}}

                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-gray-100">

                    <a href="{{ route('payments.index') }}"
                       class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold">

                        Annuler

                    </a>


                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-7 py-3 rounded-xl bg-[#0B2A55] hover:bg-[#061A33] text-white font-bold shadow-lg border-b-4 border-[#C89B3C]">

                        💾 Enregistrer le paiement

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


{{-- ========================================================= --}}
{{-- JAVASCRIPT --}}
{{-- ========================================================= --}}

<script>

    document.addEventListener('DOMContentLoaded', function () {

        const paymentType = document.getElementById('payment_type');

        const monthlyPeriod = document.getElementById('monthlyPeriod');
        const vipPeriod = document.getElementById('vipPeriod');

        const periodMonth = document.getElementById('period_month');
        const periodVip = document.getElementById('period_vip');
        const period = document.getElementById('period');

        const amountDue = document.getElementById('amount_due');
        const amountPaid = document.getElementById('amount_paid');

        const remainingPreview =
            document.getElementById('remainingPreview');

        const paymentStatus =
            document.getElementById('paymentStatus');


        /*
        |--------------------------------------------------------------------------
        | TYPE D'ABONNEMENT
        |--------------------------------------------------------------------------
        */

        function updatePaymentType() {

            if (paymentType.value === 'monthly') {

                monthlyPeriod.classList.remove('hidden');
                vipPeriod.classList.add('hidden');

                periodMonth.required = true;
                periodVip.required = false;

                period.value = periodMonth.value;

            }

            else if (paymentType.value === 'vip') {

                monthlyPeriod.classList.add('hidden');
                vipPeriod.classList.remove('hidden');

                periodMonth.required = false;
                periodVip.required = true;

                period.value = periodVip.value;

            }

            else {

                monthlyPeriod.classList.remove('hidden');
                vipPeriod.classList.add('hidden');

                periodMonth.required = false;
                periodVip.required = false;

                period.value = '';

            }

        }


        /*
        |--------------------------------------------------------------------------
        | SYNCHRONISER LA PERIODE
        |--------------------------------------------------------------------------
        */

        periodMonth.addEventListener('change', function () {

            if (paymentType.value === 'monthly') {

                period.value = this.value;

            }

        });


        periodVip.addEventListener('change', function () {

            if (paymentType.value === 'vip') {

                period.value = this.value;

            }

        });


        paymentType.addEventListener('change', updatePaymentType);


        /*
        |--------------------------------------------------------------------------
        | CALCUL DU RESTE
        |--------------------------------------------------------------------------
        */

        function calculateRemaining() {

            const due =
                parseFloat(amountDue.value) || 0;

            const paid =
                parseFloat(amountPaid.value) || 0;

            const remaining =
                Math.max(due - paid, 0);


            remainingPreview.textContent =
                remaining.toFixed(2) + ' DA';


            if (due === 0 && paid === 0) {

                paymentStatus.textContent =
                    'En attente';

                paymentStatus.className =
                    'px-4 py-2 rounded-full bg-gray-100 text-gray-600 font-bold text-sm';

            }

            else if (paid === 0) {

                paymentStatus.textContent =
                    'Non payé';

                paymentStatus.className =
                    'px-4 py-2 rounded-full bg-red-100 text-red-700 font-bold text-sm';

            }

            else if (paid < due) {

                paymentStatus.textContent =
                    'Paiement partiel';

                paymentStatus.className =
                    'px-4 py-2 rounded-full bg-amber-100 text-amber-700 font-bold text-sm';

            }

            else {

                paymentStatus.textContent =
                    'Payé';

                paymentStatus.className =
                    'px-4 py-2 rounded-full bg-green-100 text-green-700 font-bold text-sm';

            }

        }


        amountDue.addEventListener(
            'input',
            calculateRemaining
        );

        amountPaid.addEventListener(
            'input',
            calculateRemaining
        );


        /*
        |--------------------------------------------------------------------------
        | INITIALISATION
        |--------------------------------------------------------------------------
        */

        updatePaymentType();

        calculateRemaining();

    });

</script>


</x-app-layout>
