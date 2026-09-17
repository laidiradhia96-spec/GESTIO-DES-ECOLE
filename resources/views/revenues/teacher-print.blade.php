<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <title>Fiche de paie — {{ $teacher->first_name }} {{ $teacher->last_name }}</title>

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

        .payslip {
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

        .payslip-title {
            text-align: center;
            margin: 25px 0;
            font-size: 22px;
            font-weight: bold;
            color: #0B2A55;
        }

        .period {
            text-align: center;
            margin-bottom: 25px;
            color: #C89B3C;
            font-weight: bold;
            font-size: 16px;
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

        .summary {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        .summary-box {
            border: 2px solid #0B2A55;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .summary-box.teacher {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .summary-box.academy {
            border-color: #C89B3C;
            background: #fffbeb;
        }

        .summary-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .summary-value {
            font-size: 20px;
            font-weight: bold;
            color: #0B2A55;
        }

        .summary-box.teacher .summary-value {
            color: #2563eb;
        }

        .summary-box.academy .summary-value {
            color: #C89B3C;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #0B2A55;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .amount {
            font-weight: bold;
            color: #0B2A55;
        }

        .teacher-share {
            font-weight: bold;
            color: #2563eb;
        }

        .academy-share {
            font-weight: bold;
            color: #C89B3C;
        }

        .totals {
            margin-top: 20px;
            padding: 15px;
            background: #f8fafc;
            border-left: 5px solid #C89B3C;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        .total-line {
            text-align: center;
        }

        .total-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .total-value {
            font-size: 18px;
            font-weight: bold;
        }

        .total-value.green {
            color: #0B2A55;
        }

        .total-value.blue {
            color: #2563eb;
        }

        .total-value.gold {
            color: #C89B3C;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #777;
            font-size: 12px;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #0B2A55;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            z-index: 100;
        }

        .print-btn:hover {
            background: #061A33;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }

            .payslip {
                border: none;
                border-radius: 0;
                padding: 20px;
            }

            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()">
        🖨️ Imprimer
    </button>


    <div class="payslip">

        {{-- HEADER --}}
        <div class="header">
            <div class="academy">ACADÉMIE EL TAFAWOK</div>
            <div class="subtitle">Fiche de paie de l'enseignant</div>
        </div>


        {{-- INFOS --}}
        <div class="info">

            <div class="info-box">
                <div class="label">Enseignant</div>
                <div class="value">{{ $teacher->first_name }} {{ $teacher->last_name }}</div>
            </div>

            <div class="info-box">
                <div class="label">Spécialité</div>
                <div class="value">{{ $teacherSubjects }}</div>
            </div>

            <div class="info-box">
                <div class="label">Période</div>
                <div class="value">{{ $periodLabel }}</div>
            </div>

            @if($schoolYear)
                <div class="info-box">
                    <div class="label">Année scolaire</div>
                    <div class="value">{{ $schoolYear->name }}</div>
                </div>
            @endif

        </div>


        {{-- PÉRIODE --}}
        <div class="period">
            Période : {{ $periodLabel }}
        </div>


        {{-- RÉSUMÉ --}}
        <div class="summary">

            <div class="summary-box">
                <div class="summary-label">Total encaissé</div>
                <div class="summary-value">{{ number_format($totalCollected, 2, ',', ' ') }} DA</div>
            </div>

            <div class="summary-box teacher">
                <div class="summary-label">Part enseignant</div>
                <div class="summary-value">{{ number_format($totalTeacherShare, 2, ',', ' ') }} DA</div>
            </div>

            <div class="summary-box academy">
                <div class="summary-label">Part académie</div>
                <div class="summary-value">{{ number_format($totalAcademyShare, 2, ',', ' ') }} DA</div>
            </div>

        </div>


        {{-- RÉPARTITION PAR GROUPE --}}
        @if($groupRevenue->count() > 0)

            <div style="margin-bottom: 25px;">
                <h3 style="font-size: 16px; font-weight: bold; color: #0B2A55; margin-bottom: 12px;">
                    Répartition par groupe
                </h3>

                <table>

                    <thead>
                        <tr>
                            <th>Groupe</th>
                            <th>Matière</th>
                            <th class="text-center">Niveau · Mode</th>
                            <th class="text-center">Paiements</th>
                            <th class="text-right">Encaissé</th>
                            <th class="text-right">Part enseignant</th>
                            <th class="text-right">Part académie</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($groupRevenue as $row)

                            <tr>
                                <td><strong>{{ $row->group->name ?? '—' }}</strong></td>
                                <td>{{ $row->subject->name ?? '—' }}</td>
                                <td class="text-center">{{ $row->group->level ?? '' }} · {{ ucfirst($row->group->mode ?? '') }}</td>
                                <td class="text-center">{{ $row->payments_count }}</td>
                                <td class="text-right amount">{{ number_format($row->total_collected, 2, ',', ' ') }} DA</td>
                                <td class="text-right teacher-share">{{ number_format($row->teacher_share, 2, ',', ' ') }} DA</td>
                                <td class="text-right academy-share">{{ number_format($row->academy_share, 2, ',', ' ') }} DA</td>
                            </tr>

                        @endforeach

                    </tbody>

                </table>

                {{-- Totaux --}}
                <div class="totals">

                    <div class="total-line">
                        <div class="total-label">Total encaissé</div>
                        <div class="total-value green">{{ number_format($totalCollected, 2, ',', ' ') }} DA</div>
                    </div>

                    <div class="total-line">
                        <div class="total-label">Part enseignant</div>
                        <div class="total-value blue">{{ number_format($totalTeacherShare, 2, ',', ' ') }} DA</div>
                    </div>

                    <div class="total-line">
                        <div class="total-label">Part académie</div>
                        <div class="total-value gold">{{ number_format($totalAcademyShare, 2, ',', ' ') }} DA</div>
                    </div>

                </div>
            </div>

        @endif


        {{-- DÉTAIL --}}
        @if($payments->count() > 0)

            <table>

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Élève</th>
                        <th>Groupe</th>
                        <th>Matière</th>
                        <th class="text-center">Type</th>
                        <th class="text-right">Montant</th>
                        <th class="text-right">Part prof</th>
                        <th class="text-right">Part acad.</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($payments as $row)

                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->payment->payment_date)->format('d/m/Y') }}</td>
                            <td><strong>{{ $row->student->first_name }} {{ $row->student->last_name }}</strong></td>
                            <td>{{ $row->group->name ?? '—' }}</td>
                            <td>{{ $row->subject->name ?? '—' }}</td>
                            <td class="text-center">
                                @php
                                    $typeLabels = [
                                        'monthly' => 'Mensuel',
                                        'special_monthly' => 'Spécial',
                                        'vip_monthly' => 'VIP',
                                        'vip_per_session' => 'VIP/S',
                                    ];
                                @endphp
                                {{ $typeLabels[$row->payment->payment_type] ?? $row->payment->payment_type }}
                            </td>
                            <td class="text-right amount">{{ number_format((float) $row->payment->amount_paid, 2, ',', ' ') }} DA</td>
                            <td class="text-right teacher-share">{{ number_format($row->teacher_share, 2, ',', ' ') }} DA</td>
                            <td class="text-right academy-share">{{ number_format($row->academy_share, 2, ',', ' ') }} DA</td>
                        </tr>

                    @endforeach

                </tbody>

            </table>


            {{-- TOTAUX --}}
            <div class="totals">

                <div class="total-line">
                    <div class="total-label">Total encaissé</div>
                    <div class="total-value green">{{ number_format($totalCollected, 2, ',', ' ') }} DA</div>
                </div>

                <div class="total-line">
                    <div class="total-label">Part enseignant</div>
                    <div class="total-value blue">{{ number_format($totalTeacherShare, 2, ',', ' ') }} DA</div>
                </div>

                <div class="total-line">
                    <div class="total-label">Part académie</div>
                    <div class="total-value gold">{{ number_format($totalAcademyShare, 2, ',', ' ') }} DA</div>
                </div>

            </div>

        @else

            <div style="text-align: center; padding: 40px; color: #999;">
                Aucun paiement enregistré pour cette période.
            </div>

        @endif


        {{-- FOOTER --}}
        <div class="footer">
            <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
            <p>Académie El Tafawok — Fiche de paie</p>
        </div>

    </div>

</body>
</html>
