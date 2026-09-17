<x-app-layout>

    {{-- =========================================================
        HEADER DE LA PAGE
    ========================================================== --}}
    <x-slot name="header">

        <div class="flex items-center justify-between print:hidden">

            <div>
                <h2 class="font-extrabold text-2xl text-[#0B2A55]">
                    Reçu de paiement
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Détails du paiement
                </p>
            </div>

            <div class="flex gap-3">

                <a href="{{ route('payments.index') }}"
                   class="px-5 py-3 rounded-xl bg-gray-100
                          hover:bg-gray-200 text-gray-700 font-bold">
                    ← Retour
                </a>

                <button onclick="window.print()"
                        class="px-5 py-3 rounded-xl bg-[#0B2A55]
                               hover:bg-[#061A33] text-white font-bold">
                    🖨️ Imprimer
                </button>

            </div>

        </div>

    </x-slot>


    {{-- =========================================================
        CONTENU
    ========================================================== --}}
    <div class="py-8 bg-gray-50 min-h-screen print:bg-white print:p-0">

        <div class="max-w-4xl mx-auto px-4 print:max-w-none print:px-0">


            {{-- =====================================================
                FEUILLE A4
                CONTIENT 2 REÇUS
            ====================================================== --}}
            <div id="print-sheet"
                 class="bg-white">


                {{-- =================================================
                    REÇU 1 : ÉLÈVE
                ================================================== --}}
                <div class="receipt-copy">

                    {{-- HEADER --}}
                    <div class="receipt-header">

                        <div>

                            <p class="academy-name">
                                ACADÉMIE EL TAFAWOK
                            </p>

                            <h1 class="receipt-title">
                                REÇU DE PAIEMENT
                            </h1>

                        </div>

                        <div class="receipt-number">

                            <p>
                                N° Reçu
                            </p>

                            <strong>
                                {{ $payment->receipt_number }}
                            </strong>

                        </div>

                    </div>


                    {{-- INFORMATIONS --}}
                    <div class="receipt-body">

                        <div class="info-grid">

                            {{-- Élève --}}
                            <div class="info-item">

                                <span>
                                    Élève
                                </span>

                                <strong>
                                    {{ $payment->student->last_name }}
                                    {{ $payment->student->first_name }}
                                </strong>

                            </div>


                            {{-- Niveau --}}
                            <div class="info-item">

                                <span>
                                    Niveau
                                </span>

                                <strong>
                                    {{ $payment->student->level ?? '-' }}
                                </strong>

                            </div>


                            {{-- Groupe --}}
                            <div class="info-item">

                                <span>
                                    Groupe
                                </span>

                                <strong>
                                    {{ $payment->group->name ?? '-' }}
                                </strong>

                            </div>


                            {{-- Période --}}
                            <div class="info-item">

                                <span>
                                    Période
                                </span>

                                <strong>
                                    {{ $payment->period }}
                                </strong>

                            </div>


                            {{-- Mode paiement --}}
                            <div class="info-item">

                                <span>
                                    Mode de paiement
                                </span>

                                <strong>
                                    {{ $payment->payment_method }}
                                </strong>

                            </div>


                            {{-- Matière --}}
                            <div class="info-item">

                                <span>
                                    Matière
                                </span>

                                <strong>
                                    {{ $payment->subject->name ?? '-' }}
                                </strong>

                            </div>


                            {{-- Enseignant --}}
                            <div class="info-item">

                                <span>
                                    Enseignant
                                </span>

                                <strong>
                                    {{ $payment->group?->teacher?->last_name }}
                                    {{ $payment->group?->teacher?->first_name }}
                                </strong>

                            </div>

                        </div>


                        {{-- MONTANTS --}}
                        <div class="amounts">

                            <div class="amount-row">

                                <span>
                                    Montant demandé
                                </span>

                                <strong>
                                    {{ number_format($payment->amount_due, 2, ',', ' ') }}
                                    DA
                                </strong>

                            </div>


                            <div class="amount-row">

                                <span>
                                    Montant payé
                                </span>

                                <strong class="paid">
                                    {{ number_format($payment->amount_paid, 2, ',', ' ') }}
                                    DA
                                </strong>

                            </div>


                            <div class="remaining-row">

                                <span>
                                    Reste à payer
                                </span>

                                <strong>
                                    {{ number_format($payment->remaining_amount, 2, ',', ' ') }}
                                    DA
                                </strong>

                            </div>

                        </div>


                        {{-- DATE / HEURE --}}
                        <div class="date-grid">

                            <div>

                                <span>
                                    Date
                                </span>

                                <strong>
                                    {{ $payment->payment_date->format('d/m/Y') }}
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Heure
                                </span>

                                <strong>
                                    {{ $payment->payment_time }}
                                </strong>

                            </div>

                        </div>


                        {{-- OBSERVATION --}}
                        @if($payment->note)

                            <div class="note-box">

                                <span>
                                    Observation
                                </span>

                                <p>
                                    {{ $payment->note }}
                                </p>

                            </div>

                        @endif


                        {{-- FOOTER --}}
                        <div class="receipt-footer">

                            <p>
                                Merci pour votre confiance.
                            </p>

                            <small>
                                Académie El Tafawok
                            </small>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                    LIGNE DE DÉCOUPAGE
                ================================================== --}}
                <div class="cut-line">

                    <span>
                        ✂ Découper ici
                    </span>

                </div>


                {{-- =================================================
                    REÇU 2 : ADMINISTRATION
                ================================================== --}}
                <div class="receipt-copy">

                    {{-- HEADER --}}
                    <div class="receipt-header">

                        <div>

                            <p class="academy-name">
                                ACADÉMIE EL TAFAWOK
                            </p>

                            <h1 class="receipt-title">
                                REÇU DE PAIEMENT
                            </h1>

                        </div>

                        <div class="receipt-number">

                            <p>
                                N° Reçu
                            </p>

                            <strong>
                                {{ $payment->receipt_number }}
                            </strong>

                        </div>

                    </div>


                    {{-- INFORMATIONS --}}
                    <div class="receipt-body">

                        <div class="info-grid">

                            <div class="info-item">

                                <span>
                                    Élève
                                </span>

                                <strong>
                                    {{ $payment->student->last_name }}
                                    {{ $payment->student->first_name }}
                                </strong>

                            </div>


                            <div class="info-item">

                                <span>
                                    Niveau
                                </span>

                                <strong>
                                    {{ $payment->student->level ?? '-' }}
                                </strong>

                            </div>


                            <div class="info-item">

                                <span>
                                    Groupe
                                </span>

                                <strong>
                                    {{ $payment->group->name ?? '-' }}
                                </strong>

                            </div>


                            <div class="info-item">

                                <span>
                                    Période
                                </span>

                                <strong>
                                    {{ $payment->period }}
                                </strong>

                            </div>


                            <div class="info-item">

                                <span>
                                    Mode de paiement
                                </span>

                                <strong>
                                    {{ $payment->payment_method }}
                                </strong>

                            </div>


                            {{-- Matière --}}
                            <div class="info-item">

                                <span>
                                    Matière
                                </span>

                                <strong>
                                    {{ $payment->subject->name ?? '-' }}
                                </strong>

                            </div>


                            {{-- Enseignant --}}
                            <div class="info-item">

                                <span>
                                    Enseignant
                                </span>

                                <strong>
                                    {{ $payment->group?->teacher?->last_name }}
                                    {{ $payment->group?->teacher?->first_name }}
                                </strong>

                            </div>

                        </div>


                        {{-- MONTANTS --}}
                        <div class="amounts">

                            <div class="amount-row">

                                <span>
                                    Montant demandé
                                </span>

                                <strong>
                                    {{ number_format($payment->amount_due, 2, ',', ' ') }}
                                    DA
                                </strong>

                            </div>


                            <div class="amount-row">

                                <span>
                                    Montant payé
                                </span>

                                <strong class="paid">
                                    {{ number_format($payment->amount_paid, 2, ',', ' ') }}
                                    DA
                                </strong>

                            </div>


                            <div class="remaining-row">

                                <span>
                                    Reste à payer
                                </span>

                                <strong>
                                    {{ number_format($payment->remaining_amount, 2, ',', ' ') }}
                                    DA
                                </strong>

                            </div>

                        </div>


                        {{-- DATE / HEURE --}}
                        <div class="date-grid">

                            <div>

                                <span>
                                    Date
                                </span>

                                <strong>
                                    {{ $payment->payment_date->format('d/m/Y') }}
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Heure
                                </span>

                                <strong>
                                    {{ $payment->payment_time }}
                                </strong>

                            </div>

                        </div>


                        {{-- OBSERVATION --}}
                        @if($payment->note)

                            <div class="note-box">

                                <span>
                                    Observation
                                </span>

                                <p>
                                    {{ $payment->note }}
                                </p>

                            </div>

                        @endif


                        {{-- FOOTER --}}
                        <div class="receipt-footer">

                            <p>
                                Merci pour votre confiance.
                            </p>

                            <small>
                                Académie El Tafawok
                            </small>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
        DESIGN + IMPRESSION
    ========================================================== --}}
    <style>

        /* ============================================
           DESIGN ÉCRAN
        ============================================ */

        .receipt-copy {

            background: white;

            border-radius: 24px;

            overflow: hidden;

            border: 1px solid #e5e7eb;

            box-shadow:
                0 10px 30px rgba(11, 42, 85, 0.08);

            margin-bottom: 20px;
        }


        .receipt-header {

            background: #0B2A55;

            color: white;

            padding: 25px 32px;

            display: flex;

            justify-content: space-between;

            align-items: flex-start;
        }


        .academy-name {

            color: #C89B3C;

            font-size: 13px;

            font-weight: 800;

            letter-spacing: 1px;

            margin: 0;
        }


        .receipt-title {

            color: white;

            font-size: 25px;

            font-weight: 900;

            margin-top: 5px;

            margin-bottom: 0;
        }


        .receipt-number {

            text-align: right;
        }


        .receipt-number p {

            color: #bfdbfe;

            font-size: 12px;

            margin: 0 0 3px 0;
        }


        .receipt-number strong {

            color: white;

            font-size: 16px;

            font-weight: 900;
        }


        .receipt-body {

            padding: 25px 32px 20px;
        }


        .info-grid {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 18px 35px;

            margin-bottom: 20px;
        }


        .info-item span,
        .date-grid span,
        .note-box span {

            display: block;

            color: #6b7280;

            font-size: 11px;

            margin-bottom: 3px;
        }


        .info-item strong {

            display: block;

            color: #0B2A55;

            font-size: 14px;

            font-weight: 800;
        }


        .amounts {

            border-top: 1px solid #e5e7eb;

            border-bottom: 1px solid #e5e7eb;

            padding: 13px 0;

            margin-bottom: 18px;
        }


        .amount-row {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 7px 0;

            color: #6b7280;

            font-size: 12px;
        }


        .amount-row strong {

            color: #111827;

            font-size: 13px;
        }


        .amount-row .paid {

            color: #16a34a;

        }


        .remaining-row {

            display: flex;

            justify-content: space-between;

            align-items: center;

            background: #eff6ff;

            border-radius: 10px;

            padding: 10px 13px;

            margin-top: 3px;

            color: #0B2A55;

            font-size: 12px;

            font-weight: 800;
        }


        .remaining-row strong {

            color: #0B2A55;

            font-size: 14px;

        }


        .date-grid {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 30px;

            margin-bottom: 15px;

        }


        .date-grid strong {

            color: #111827;

            font-size: 12px;

        }


        .note-box {

            background: #f9fafb;

            border-radius: 10px;

            padding: 10px 13px;

            margin-bottom: 15px;
        }


        .note-box p {

            margin: 3px 0 0;

            color: #374151;

            font-size: 11px;

        }


        .receipt-footer {

            border-top: 1px solid #e5e7eb;

            padding-top: 12px;

            text-align: center;

        }


        .receipt-footer p {

            color: #6b7280;

            font-size: 10px;

            margin: 0;
        }


        .receipt-footer small {

            color: #9ca3af;

            font-size: 9px;

        }


        .cut-line {

            height: 28px;

            display: flex;

            align-items: center;

            justify-content: center;

            position: relative;

            color: #9ca3af;

            font-size: 10px;

        }


        .cut-line::before {

            content: "";

            position: absolute;

            left: 0;

            right: 0;

            top: 50%;

            border-top: 1px dashed #9ca3af;

        }


        .cut-line span {

            position: relative;

            background: #f9fafb;

            padding: 0 12px;

        }


        /* ============================================
           IMPRESSION A4
           2 REÇUS = 1 FEUILLE
        ============================================ */

        @media print {

            @page {

                size: A4 portrait;

                margin: 0;
            }


            html,
            body {

                width: 210mm;

                height: 297mm;

                margin: 0 !important;

                padding: 0 !important;

                background: white !important;
            }


            body {

                -webkit-print-color-adjust: exact !important;

                print-color-adjust: exact !important;
            }


            /* cacher tout sauf les reçus */

            body * {

                visibility: hidden;
            }


            #print-sheet,
            #print-sheet * {

                visibility: visible;
            }


            #print-sheet {

                position: absolute;

                left: 0;

                top: 0;

                width: 210mm;

                height: 297mm;

                margin: 0;

                padding: 7mm 10mm;

                background: white;

                box-sizing: border-box;
            }


            /* Chaque reçu prend environ la moitié */

            .receipt-copy {

                width: 190mm;

                height: 136mm;

                margin: 0;

                padding: 0;

                border: 1px solid #d1d5db;

                border-radius: 0;

                box-shadow: none;

                overflow: hidden;

                box-sizing: border-box;
            }


            .receipt-header {

                height: 30mm;

                padding: 7mm 8mm;

                box-sizing: border-box;
            }


            .academy-name {

                font-size: 10px;

                letter-spacing: .8px;
            }


            .receipt-title {

                font-size: 20px;

                margin-top: 3px;
            }


            .receipt-number p {

                font-size: 9px;
            }


            .receipt-number strong {

                font-size: 12px;
            }


            .receipt-body {

                padding: 7mm 8mm 5mm;
            }


            .info-grid {

                gap: 8mm 15mm;

                margin-bottom: 6mm;
            }


            .info-item span,
            .date-grid span,
            .note-box span {

                font-size: 8px;

                margin-bottom: 1px;
            }


            .info-item strong {

                font-size: 11px;
            }


            .amounts {

                padding: 3mm 0;

                margin-bottom: 5mm;
            }


            .amount-row {

                padding: 2mm 0;

                font-size: 9px;
            }


            .amount-row strong {

                font-size: 10px;
            }


            .remaining-row {

                padding: 3mm 4mm;

                font-size: 9px;
            }


            .remaining-row strong {

                font-size: 11px;
            }


            .date-grid {

                gap: 15mm;

                margin-bottom: 4mm;
            }


            .date-grid strong {

                font-size: 9px;
            }


            .note-box {

                padding: 2.5mm 3mm;

                margin-bottom: 4mm;
            }


            .note-box p {

                font-size: 8px;
            }


            .receipt-footer {

                padding-top: 3mm;
            }


            .receipt-footer p {

                font-size: 8px;
            }


            .receipt-footer small {

                font-size: 7px;
            }


            /* ligne de découpage */

            .cut-line {

                height: 11mm;

                font-size: 8px;

                color: #6b7280;
            }


            .cut-line span {

                background: white;

                padding: 0 8px;
            }


            /* Aucun bouton/lien */

            button,
            a,
            nav,
            header {

                display: none !important;
            }

        }

    </style>

</x-app-layout>