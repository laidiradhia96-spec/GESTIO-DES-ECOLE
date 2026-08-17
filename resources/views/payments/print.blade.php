<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <title>Reçu {{ $payment->receipt_number }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #f3f4f6;
            font-family: Arial, sans-serif;
            color: #111827;
        }

        .receipt {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            border: 1px solid #ddd;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #0B2A55;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .academy {
            font-size: 26px;
            font-weight: bold;
            color: #0B2A55;
        }

        .subtitle {
            margin-top: 8px;
            color: #666;
            font-size: 14px;
        }

        .receipt-title {
            text-align: center;
            margin: 25px 0;
            font-size: 24px;
            font-weight: bold;
            color: #0B2A55;
        }

        .receipt-number {
            text-align: center;
            margin-bottom: 25px;
            color: #C89B3C;
            font-weight: bold;
        }

        .info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        .info-box {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
        }

        .label {
            font-size: 12px;
            color: #777;
            margin-bottom: 6px;
        }

        .value {
            font-size: 16px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #0B2A55;
            color: white;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
        }

        .paid {
            color: #15803d;
            font-weight: bold;
        }

        .remaining {
            color: #dc2626;
            font-weight: bold;
        }

        .total {
            margin-top: 25px;
            padding: 20px;
            background: #f8fafc;
            border-left: 5px solid #C89B3C;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #777;
            font-size: 13px;
        }

        .print-button {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 12px;
            background: #0B2A55;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .receipt {
                border: none;
                border-radius: 0;
                max-width: none;
            }

            .print-button {
                display: none;
            }
        }
    </style>
</head>

<body>

    <button
        class="print-button"
        onclick="window.print()"
    >
        🖨️ Imprimer le reçu
    </button>

    <div class="receipt">

        <div class="header">
            <div class="academy">
                ACADÉMIE EL TAFAWOK
            </div>

            <div class="subtitle">
                Reçu de paiement
            </div>
        </div>

        <div class="receipt-title">
            REÇU DE PAIEMENT
        </div>

        <div class="receipt-number">
            {{ $payment->receipt_number }}
        </div>

        <div class="info">

            <div class="info-box">
                <div class="label">
                    Élève
                </div>

                <div class="value">
                    {{ $payment->student->last_name }}
                    {{ $payment->student->first_name }}
                </div>
            </div>

            <div class="info-box">
                <div class="label">
                    Parent
                </div>

                <div class="value">
                    {{ $payment->student->parent_name ?? '-' }}
                </div>
            </div>

            <div class="info-box">
                <div class="label">
                    Période
                </div>

                <div class="value">
                    {{ $payment->period }}
                </div>
            </div>

            <div class="info-box">
                <div class="label">
                    Date du paiement
                </div>

                <div class="value">
                    {{ $payment->payment_date?->format('d/m/Y') }}
                    à
                    {{ $payment->payment_time }}
                </div>
            </div>

            <div class="info-box">
                <div class="label">
                    Matière
                </div>

                <div class="value">
                    {{ $payment->subject->name ?? '-' }}
                </div>
            </div>

            <div class="info-box">
                <div class="label">
                    Enseignant
                </div>

                <div class="value">
                    {{ $enrollment?->teacher->last_name }}
                    {{ $enrollment?->teacher->first_name }}
                </div>
            </div>

        </div>

        <table>

            <thead>
                <tr>
                    <th>Description</th>
                    <th>Montant</th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <td>
                        Montant demandé
                    </td>

                    <td>
                        {{ number_format($payment->amount_due, 2, ',', ' ') }}
                        DA
                    </td>
                </tr>

                <tr>
                    <td>
                        Montant payé
                    </td>

                    <td class="paid">
                        {{ number_format($payment->amount_paid, 2, ',', ' ') }}
                        DA
                    </td>
                </tr>

                <tr>
                    <td>
                        Reste à payer
                    </td>

                    <td class="remaining">
                        {{ number_format($payment->remaining_amount, 2, ',', ' ') }}
                        DA
                    </td>
                </tr>

            </tbody>

        </table>

        <div class="total">

            <div class="total-line">
                <strong>Mode de paiement :</strong>

                <span>
                    {{ $payment->payment_method }}
                </span>
            </div>

            <div class="total-line">
                <strong>Montant payé :</strong>

                <strong class="paid">
                    {{ number_format($payment->amount_paid, 2, ',', ' ') }}
                    DA
                </strong>
            </div>

            <div class="total-line">
                <strong>Reste :</strong>

                <strong class="remaining">
                    {{ number_format($payment->remaining_amount, 2, ',', ' ') }}
                    DA
                </strong>
            </div>

        </div>

        @if($payment->note)

            <div class="info-box" style="margin-top: 20px;">
                <div class="label">
                    Note
                </div>

                <div class="value">
                    {{ $payment->note }}
                </div>
            </div>

        @endif

        <div class="footer">
            Merci pour votre confiance.
            <br>
            ACADÉMIE EL TAFAWOK
        </div>

    </div>

</body>
</html>
