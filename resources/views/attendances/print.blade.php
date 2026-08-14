<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Présences - {{ $attendance->date->format('d/m/Y') }}
    </title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>

        @media print {

            @page {
                size: A4;
                margin: 15mm;
            }

            body {
                background: white !important;
            }

            .no-print {
                display: none !important;
            }

        }

    </style>

</head>


<body class="bg-gray-100 text-gray-800">


    {{-- Boutons --}}
    <div class="no-print max-w-5xl mx-auto px-4 py-6">

        <div class="flex justify-end gap-3">

            <button
                onclick="window.print()"
                class="px-5 py-2.5 rounded-xl
                       bg-[#0B2A55]
                       text-white
                       font-bold
                       shadow-md
                       hover:bg-[#061A33]
                       transition">

                🖨️ Imprimer

            </button>


            <button
                onclick="window.close()"
                class="px-5 py-2.5 rounded-xl
                       bg-gray-200
                       text-gray-700
                       font-bold
                       hover:bg-gray-300
                       transition">

                Fermer

            </button>

        </div>

    </div>


    {{-- Document --}}
    <div class="max-w-5xl mx-auto bg-white shadow-lg
                px-8 py-10 print:shadow-none">


        {{-- Header --}}
        <div class="flex items-center justify-between
                    border-b-2 border-[#0B2A55]
                    pb-6">


            <div class="flex items-center gap-4">

                <img
                    src="{{ asset('images/logo.jpeg') }}"
                    alt="Académie El Tafawok"
                    class="w-20 h-20 object-contain"
                >


                <div>

                    <h1 class="text-2xl font-extrabold text-[#0B2A55]">
                        Académie El Tafawok
                    </h1>

                    <p class="text-sm text-gray-500 mt-1">
                        Feuille de présence
                    </p>

                </div>

            </div>


            <div class="text-right">

                <p class="text-sm text-gray-500">
                    Date
                </p>

                <p class="text-xl font-extrabold text-[#0B2A55]">
                    {{ $attendance->date->format('d/m/Y') }}
                </p>

            </div>

        </div>


        {{-- Informations --}}
        <div class="grid grid-cols-1 md:grid-cols-3
                    gap-4 mt-8">


            <div class="border border-gray-200 rounded-xl p-4">

                <p class="text-xs font-bold text-gray-500 uppercase">
                    Matière
                </p>

                <p class="font-bold text-[#0B2A55] mt-1">
                    {{ $attendance->subject->name }}
                </p>

            </div>


            <div class="border border-gray-200 rounded-xl p-4">

                <p class="text-xs font-bold text-gray-500 uppercase">
                    Enseignant
                </p>

                <p class="font-bold text-[#0B2A55] mt-1">

                    @if($attendance->teacher)

                        {{ $attendance->teacher->first_name }}
                        {{ $attendance->teacher->last_name }}

                    @else

                        —

                    @endif

                </p>

            </div>


            <div class="border border-gray-200 rounded-xl p-4">

                <p class="text-xs font-bold text-gray-500 uppercase">
                    Nombre d'élèves
                </p>

                <p class="font-bold text-[#0B2A55] mt-1">
                    {{ $attendances->count() }}
                </p>

            </div>

        </div>


        {{-- Statistiques --}}
        @php

            $total = $attendances->count();

            $present = $attendances
                ->where('status', 'present')
                ->count();

            $absent = $attendances
                ->where('status', 'absent')
                ->count();

            $late = $attendances
                ->where('status', 'late')
                ->count();

            $justified = $attendances
                ->where('status', 'justified')
                ->count();

            $attendanceRate = $total > 0
                ? round(($present / $total) * 100)
                : 0;

        @endphp


        <div class="grid grid-cols-5 gap-3 mt-8">


            <div class="text-center bg-gray-50
                        border rounded-xl p-3">

                <p class="text-xs text-gray-500">
                    Total
                </p>

                <p class="text-xl font-extrabold text-[#0B2A55]">
                    {{ $total }}
                </p>

            </div>


            <div class="text-center bg-green-50
                        border border-green-200
                        rounded-xl p-3">

                <p class="text-xs text-green-700">
                    Présents
                </p>

                <p class="text-xl font-extrabold text-green-700">
                    {{ $present }}
                </p>

            </div>


            <div class="text-center bg-red-50
                        border border-red-200
                        rounded-xl p-3">

                <p class="text-xs text-red-700">
                    Absents
                </p>

                <p class="text-xl font-extrabold text-red-700">
                    {{ $absent }}
                </p>

            </div>


            <div class="text-center bg-yellow-50
                        border border-yellow-200
                        rounded-xl p-3">

                <p class="text-xs text-yellow-700">
                    Retards
                </p>

                <p class="text-xl font-extrabold text-yellow-700">
                    {{ $late }}
                </p>

            </div>


            <div class="text-center bg-blue-50
                        border border-blue-200
                        rounded-xl p-3">

                <p class="text-xs text-blue-700">
                    Justifiés
                </p>

                <p class="text-xl font-extrabold text-blue-700">
                    {{ $justified }}
                </p>

            </div>

        </div>


        {{-- Tableau --}}
        <div class="mt-8">

            <h2 class="text-lg font-extrabold text-[#0B2A55] mb-4">
                Liste des élèves
            </h2>


            <table class="w-full border-collapse">


                <thead>

                    <tr class="bg-[#0B2A55] text-white">

                        <th class="border px-4 py-3 text-left">
                            #
                        </th>

                        <th class="border px-4 py-3 text-left">
                            Élève
                        </th>

                        <th class="border px-4 py-3 text-center">
                            Statut
                        </th>

                        <th class="border px-4 py-3 text-left">
                            Note
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach($attendances as $record)

                        <tr>

                            <td class="border px-4 py-3">
                                {{ $loop->iteration }}
                            </td>


                            <td class="border px-4 py-3 font-semibold">

                                {{ $record->student->first_name }}
                                {{ $record->student->last_name }}

                            </td>


                            <td class="border px-4 py-3 text-center">

                                @if($record->status === 'present')

                                    <span class="font-bold text-green-700">
                                        Présent
                                    </span>

                                @elseif($record->status === 'absent')

                                    <span class="font-bold text-red-700">
                                        Absent
                                    </span>

                                @elseif($record->status === 'late')

                                    <span class="font-bold text-yellow-700">
                                        Retard
                                    </span>

                                @elseif($record->status === 'justified')

                                    <span class="font-bold text-blue-700">
                                        Justifié
                                    </span>

                                @endif

                            </td>


                            <td class="border px-4 py-3">

                                {{ $record->note ?: '—' }}

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>


        {{-- Taux --}}
        <div class="mt-8">

            <div class="flex items-center justify-between mb-2">

                <p class="font-bold text-[#0B2A55]">
                    Taux de présence
                </p>

                <p class="font-extrabold text-[#C89B3C]">
                    {{ $attendanceRate }}%
                </p>

            </div>


            <div class="w-full h-3 bg-gray-200 rounded-full">

                <div
                    class="h-3 bg-[#C89B3C] rounded-full"
                    style="width: {{ $attendanceRate }}%;">
                </div>

            </div>

        </div>


        {{-- Signature --}}
        <div class="flex justify-end mt-16">

            <div class="text-center w-56">

                <p class="font-bold text-[#0B2A55]">
                    Signature de l'enseignant
                </p>

                <div class="border-b border-gray-400 mt-12"></div>

            </div>

        </div>


        {{-- Footer --}}
        <div class="border-t border-gray-200
                    mt-10 pt-4
                    text-center">

            <p class="text-xs text-gray-400">
                Académie El Tafawok — Feuille de présence
            </p>

        </div>

    </div>

</body>

</html>