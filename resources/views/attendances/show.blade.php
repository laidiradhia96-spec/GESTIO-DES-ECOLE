<x-app-layout>

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <x-slot name="header">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row
                        sm:items-center sm:justify-between gap-4">

                <div>

                    <h2 class="font-extrabold text-2xl text-[#0B2A55]">
                        Détails de la présence
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Consulter les détails de la séance et les présences des élèves.
                    </p>

                </div>
<a
    href="{{ route('attendances.edit', $attendance) }}"
    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl
           text-white font-semibold shadow-md hover:opacity-90 transition"
    style="background-color: #C89B3C;">

    ✏️ Modifier

</a>

{{-- Impression --}}
    <a
        href="{{ route('attendances.print', $attendance) }}"
        target="_blank"
        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl
               text-white font-semibold shadow-md hover:opacity-90 transition"
        style="background-color: #2563EB;">

        🖨️ Imprimer

    </a>

<form
        method="POST"
        action="{{ route('attendances.destroy', $attendance) }}"
        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette présence ?');"
    >

        @csrf
        @method('DELETE')

        <button
            type="submit"
            class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl
                   bg-red-600 text-white font-semibold
                   shadow-md hover:bg-red-700 transition">

            🗑️ Supprimer

        </button>

    </form>

                <a href="{{ route('attendances.index') }}"
                   class="inline-flex items-center justify-center
                          gap-2 px-5 py-3 rounded-xl
                          bg-[#0B2A55] text-white
                          font-bold shadow-md
                          border-b-4 border-[#C89B3C]
                          hover:bg-[#061A33]
                          transition">

                    ← Retour aux présences

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

                {{-- Decorative circles --}}
                <div class="absolute -right-16 -top-20
                            w-64 h-64
                            rounded-full
                            bg-white/10">
                </div>

                <div class="absolute right-24 -bottom-28
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
                            Détails de la séance
                        </h1>

                        <span class="text-3xl">
                            📋
                        </span>

                    </div>

                    <p class="mt-3
                              text-blue-100
                              text-base md:text-lg">
                        Consultez les informations de la séance,
                        les statistiques et le statut des élèves.
                    </p>

                </div>

            </div>


            {{-- =================================================
                INFORMATIONS DE LA SÉANCE
            ================================================== --}}
            <div class="bg-white
                        rounded-3xl
                        border border-gray-100
                        shadow-sm
                        p-6 mb-8">

                <div class="flex items-center gap-3 mb-6">

                    <div class="w-12 h-12
                                rounded-2xl
                                bg-[#0B2A55]
                                flex items-center justify-center
                                text-2xl">
                        📋
                    </div>

                    <div>

                        <h3 class="text-lg
                                   font-extrabold
                                   text-[#0B2A55]">
                            Informations de la séance
                        </h3>

                        <p class="text-sm text-gray-500">
                            Détails généraux de la séance.
                        </p>

                    </div>

                </div>


                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">


                    {{-- DATE --}}
                    <div class="relative overflow-hidden
                                rounded-2xl
                                bg-blue-50
                                border border-blue-100
                                p-5">

                        <div class="flex items-center gap-3">

                            <div class="w-11 h-11
                                        rounded-xl
                                        bg-white
                                        flex items-center justify-center
                                        text-xl
                                        shadow-sm">
                                📅
                            </div>

                            <div>

                                <p class="text-xs
                                          font-bold
                                          uppercase
                                          text-blue-600">
                                    Date
                                </p>

                                <p class="text-lg
                                          font-extrabold
                                          text-[#0B2A55]
                                          mt-1">

                                    {{ $attendance->date->format('d/m/Y') }}

                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- MATIÈRE --}}
                    <div class="relative overflow-hidden
                                rounded-2xl
                                bg-purple-50
                                border border-purple-100
                                p-5">

                        <div class="flex items-center gap-3">

                            <div class="w-11 h-11
                                        rounded-xl
                                        bg-white
                                        flex items-center justify-center
                                        text-xl
                                        shadow-sm">
                                📚
                            </div>

                            <div>

                                <p class="text-xs
                                          font-bold
                                          uppercase
                                          text-purple-600">
                                    Matière
                                </p>

                                <p class="text-lg
                                          font-extrabold
                                          text-[#0B2A55]
                                          mt-1">

                                    {{ $attendance->subject->name }}

                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- ENSEIGNANT --}}
                    <div class="relative overflow-hidden
                                rounded-2xl
                                bg-yellow-50
                                border border-yellow-100
                                p-5">

                        <div class="flex items-center gap-3">

                            <div class="w-11 h-11
                                        rounded-xl
                                        bg-white
                                        flex items-center justify-center
                                        text-xl
                                        shadow-sm">
                                👨‍🏫
                            </div>

                            <div>

                                <p class="text-xs
                                          font-bold
                                          uppercase
                                          text-yellow-600">
                                    Enseignant
                                </p>

                                <p class="text-lg
                                          font-extrabold
                                          text-[#0B2A55]
                                          mt-1">

                                    @if ($attendance->teacher)

                                        {{ $attendance->teacher->first_name }}
                                        {{ $attendance->teacher->last_name }}

                                    @else

                                        —

                                    @endif

                                </p>

                            </div>

                        </div>

                    </div>

                    {{-- GROUPE --}}
                    <div class="relative overflow-hidden
                                rounded-2xl
                                bg-green-50
                                border border-green-100
                                p-5">

                        <div class="flex items-center gap-3">

                            <div class="w-11 h-11
                                        rounded-xl
                                        bg-white
                                        flex items-center justify-center
                                        text-xl
                                        shadow-sm">
                                👥
                            </div>

                            <div>

                                <p class="text-xs
                                          font-bold
                                          uppercase
                                          text-green-600">
                                    Groupe
                                </p>

                                <p class="text-lg
                                          font-extrabold
                                          text-[#0B2A55]
                                          mt-1">

                                    @if ($attendance->group)
                                        {{ $attendance->group->name }}
                                        <span class="text-sm font-normal text-gray-500">
                                            — {{ $attendance->group->level }}
                                        </span>
                                    @else
                                        —
                                    @endif

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =================================================
                STATISTIQUES
            ================================================== --}}

            @php

                $total = $attendances->count();

                $present = $attendances->where('status', 'present')->count();

                $absent = $attendances->where('status', 'absent')->count();

                $late = $attendances->where('status', 'late')->count();

                $justified = $attendances->where('status', 'justified')->count();

                $attendanceRate = $total > 0
                    ? round(($present / $total) * 100)
                    : 0;

            @endphp


            <div class="grid grid-cols-1 sm:grid-cols-2
                        lg:grid-cols-5 gap-5 mb-8">


                {{-- TOTAL --}}
                <div class="bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            p-5
                            hover:shadow-md
                            transition">

                    <div class="flex items-center justify-between">

                        <div class="w-12 h-12
                                    rounded-2xl
                                    bg-blue-50
                                    flex items-center justify-center
                                    text-2xl">
                            👥
                        </div>

                        <span class="text-xs
                                     font-bold
                                     uppercase
                                     text-gray-400">
                            Total
                        </span>

                    </div>

                    <p class="text-sm text-gray-500 mt-5">
                        Élèves
                    </p>

                    <p class="text-3xl
                              font-extrabold
                              text-[#0B2A55]
                              mt-1">

                        {{ $total }}

                    </p>

                </div>


                {{-- PRÉSENTS --}}
                <div class="bg-green-50
                            rounded-3xl
                            border border-green-100
                            p-5
                            hover:shadow-md
                            transition">

                    <div class="flex items-center justify-between">

                        <div class="w-12 h-12
                                    rounded-2xl
                                    bg-green-100
                                    flex items-center justify-center
                                    text-2xl">
                            ✅
                        </div>

                        <span class="text-xs
                                     font-bold
                                     uppercase
                                     text-green-600">
                            Présents
                        </span>

                    </div>

                    <p class="text-sm text-green-700 mt-5">
                        Élèves présents
                    </p>

                    <p class="text-3xl
                              font-extrabold
                              text-green-700
                              mt-1">

                        {{ $present }}

                    </p>

                </div>


                {{-- ABSENTS --}}
                <div class="bg-red-50
                            rounded-3xl
                            border border-red-100
                            p-5
                            hover:shadow-md
                            transition">

                    <div class="flex items-center justify-between">

                        <div class="w-12 h-12
                                    rounded-2xl
                                    bg-red-100
                                    flex items-center justify-center
                                    text-2xl">
                            ❌
                        </div>

                        <span class="text-xs
                                     font-bold
                                     uppercase
                                     text-red-600">
                            Absents
                        </span>

                    </div>

                    <p class="text-sm text-red-700 mt-5">
                        Élèves absents
                    </p>

                    <p class="text-3xl
                              font-extrabold
                              text-red-700
                              mt-1">

                        {{ $absent }}

                    </p>

                </div>


                {{-- RETARDS --}}
                <div class="bg-yellow-50
                            rounded-3xl
                            border border-yellow-100
                            p-5
                            hover:shadow-md
                            transition">

                    <div class="flex items-center justify-between">

                        <div class="w-12 h-12
                                    rounded-2xl
                                    bg-yellow-100
                                    flex items-center justify-center
                                    text-2xl">
                            🟠
                        </div>

                        <span class="text-xs
                                     font-bold
                                     uppercase
                                     text-yellow-600">
                            Retards
                        </span>

                    </div>

                    <p class="text-sm text-yellow-700 mt-5">
                        Élèves en retard
                    </p>

                    <p class="text-3xl
                              font-extrabold
                              text-yellow-700
                              mt-1">

                        {{ $late }}

                    </p>

                </div>


                {{-- JUSTIFIÉS --}}
                <div class="bg-blue-50
                            rounded-3xl
                            border border-blue-100
                            p-5
                            hover:shadow-md
                            transition">

                    <div class="flex items-center justify-between">

                        <div class="w-12 h-12
                                    rounded-2xl
                                    bg-blue-100
                                    flex items-center justify-center
                                    text-2xl">
                            📄
                        </div>

                        <span class="text-xs
                                     font-bold
                                     uppercase
                                     text-blue-600">
                            Justifiés
                        </span>

                    </div>

                    <p class="text-sm text-blue-700 mt-5">
                        Absences justifiées
                    </p>

                    <p class="text-3xl
                              font-extrabold
                              text-blue-700
                              mt-1">

                        {{ $justified }}

                    </p>

                </div>

            </div>


            {{-- =================================================
                TAUX DE PRÉSENCE
            ================================================== --}}
            @if ($total > 0)

                <div class="bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            p-6 mb-8">

                    <div class="flex flex-col sm:flex-row
                                sm:items-center
                                sm:justify-between
                                gap-4 mb-4">

                        <div>

                            <h3 class="text-lg
                                       font-extrabold
                                       text-[#0B2A55]">
                                Taux de présence
                            </h3>

                            <p class="text-sm text-gray-500 mt-1">
                                Pourcentage des élèves présents durant cette séance.
                            </p>

                        </div>

                        <div class="text-3xl
                                    font-extrabold
                                    text-[#0B2A55]">

                            {{ $attendanceRate }}%

                        </div>

                    </div>


                    <div class="w-full
                                bg-gray-100
                                rounded-full
                                h-4
                                overflow-hidden">

                        <div
                            class="h-4
                                   rounded-full
                                   bg-gradient-to-r
                                   from-[#C89B3C]
                                   to-[#0B2A55]
                                   transition-all duration-700"
                            style="width: {{ $attendanceRate }}%;">
                        </div>

                    </div>

                </div>

            @endif


            {{-- =================================================
                LISTE DES ÉLÈVES
            ================================================== --}}
            <div class="bg-white
                        rounded-3xl
                        border border-gray-100
                        shadow-sm
                        overflow-hidden">


                {{-- HEADER TABLE --}}
                <div class="p-6
                            border-b border-gray-100">

                    <div class="flex flex-col sm:flex-row
                                sm:items-center
                                sm:justify-between gap-4">

                        <div>

                            <h3 class="text-lg
                                       font-extrabold
                                       text-[#0B2A55]">
                                Présences des élèves
                            </h3>

                            <p class="text-sm text-gray-500 mt-1">
                                Liste des élèves et leur statut.
                            </p>

                        </div>

                        <span class="inline-flex items-center
                                     px-4 py-2
                                     rounded-full
                                     bg-[#C89B3C]
                                     text-white
                                     text-sm
                                     font-extrabold">

                            {{ $total }} élèves

                        </span>

                    </div>

                </div>


                @if ($attendances->count() > 0)

                    <div class="overflow-x-auto">

                        <table class="min-w-full">

                            <thead class="bg-[#0B2A55]">

                                <tr class="text-white text-sm">

                                    <th class="px-6 py-4 text-left">
                                        #
                                    </th>

                                    <th class="px-6 py-4 text-left">
                                        Élève
                                    </th>

                                    <th class="px-6 py-4 text-center">
                                        Statut
                                    </th>

                                    <th class="px-6 py-4 text-left">
                                        Note
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-100">

                                @foreach ($attendances as $record)

                                    <tr class="hover:bg-blue-50/50 transition">


                                        {{-- # --}}
                                        <td class="px-6 py-5">

                                            <span class="text-sm
                                                         font-semibold
                                                         text-gray-400">

                                                {{ $loop->iteration }}

                                            </span>

                                        </td>


                                        {{-- ÉLÈVE --}}
                                        <td class="px-6 py-5">

                                            <div class="flex items-center gap-3">

                                                <div class="w-10 h-10
                                                            rounded-xl
                                                            bg-blue-50
                                                            flex items-center
                                                            justify-center
                                                            text-lg">
                                                    👨‍🎓
                                                </div>

                                                <div>

                                                    <p class="font-bold
                                                              text-gray-800">

                                                        {{ $record->student->first_name }}
                                                        {{ $record->student->last_name }}

                                                    </p>

                                                    <p class="text-xs
                                                              text-gray-400
                                                              mt-1">

                                                        Élève

                                                    </p>

                                                </div>

                                            </div>

                                        </td>


                                        {{-- STATUT --}}
                                        <td class="px-6 py-5 text-center">

                                            @if ($record->status === 'present')

                                                <span class="inline-flex items-center gap-1
                                                             px-4 py-2
                                                             rounded-full
                                                             bg-green-100
                                                             text-green-700
                                                             text-xs
                                                             font-extrabold">

                                                    🟢 Présent

                                                </span>

                                            @elseif ($record->status === 'absent')

                                                <span class="inline-flex items-center gap-1
                                                             px-4 py-2
                                                             rounded-full
                                                             bg-red-100
                                                             text-red-700
                                                             text-xs
                                                             font-extrabold">

                                                    🔴 Absent

                                                </span>

                                            @elseif ($record->status === 'late')

                                                <span class="inline-flex items-center gap-1
                                                             px-4 py-2
                                                             rounded-full
                                                             bg-yellow-100
                                                             text-yellow-700
                                                             text-xs
                                                             font-extrabold">

                                                    🟠 Retard

                                                </span>

                                            @elseif ($record->status === 'justified')

                                                <span class="inline-flex items-center gap-1
                                                             px-4 py-2
                                                             rounded-full
                                                             bg-blue-100
                                                             text-blue-700
                                                             text-xs
                                                             font-extrabold">

                                                    🔵 Justifié

                                                </span>

                                            @endif

                                        </td>


                                        {{-- NOTE --}}
                                        <td class="px-6 py-5">

                                            @if ($record->note)

                                                <span class="text-sm
                                                             font-medium
                                                             text-gray-600">

                                                    {{ $record->note }}

                                                </span>

                                            @else

                                                <span class="text-gray-300">
                                                    —
                                                </span>

                                            @endif

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @else

                    <div class="p-16 text-center">

                        <div class="w-20 h-20
                                    mx-auto
                                    rounded-3xl
                                    bg-gray-100
                                    flex items-center
                                    justify-center
                                    text-4xl
                                    mb-5">

                            📋

                        </div>

                        <h3 class="text-lg
                                   font-extrabold
                                   text-[#0B2A55]">

                            Aucune présence trouvée

                        </h3>

                        <p class="text-sm
                                  text-gray-500
                                  mt-2">

                            Aucun élève n'est enregistré pour cette séance.

                        </p>

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>