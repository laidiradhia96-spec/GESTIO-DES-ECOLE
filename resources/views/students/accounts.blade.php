<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div>
                <h2 class="font-bold text-2xl text-[#0B2A55]">
                    👤 Comptes élèves
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Gérer les comptes et les accès des élèves à leur espace personnel
                </p>
            </div>

            <div class="flex flex-wrap gap-3">

                {{-- Retour aux élèves --}}
                <a
                    href="{{ route('students.index') }}"
                    class="inline-flex items-center justify-center gap-2
                           px-5 py-3
                           rounded-xl
                           bg-gray-100
                           hover:bg-gray-200
                           text-gray-700
                           font-bold
                           shadow-sm
                           border-b-4
                           border-gray-300
                           transition-all duration-200
                           hover:-translate-y-0.5"
                >
                    ← Retour aux élèves
                </a>

            </div>

        </div>
    </x-slot>


    <div class="py-10 bg-gray-100 min-h-screen">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- ================================================= --}}
            {{-- MESSAGES --}}
            {{-- ================================================= --}}

            @if(session('success'))

                <div class="mb-6 rounded-2xl
                            bg-green-50
                            border border-green-200
                            px-5 py-4
                            text-green-700
                            shadow-sm">

                    <div class="flex items-center gap-3">

                        <span class="text-xl">✅</span>

                        <span class="font-semibold">
                            {{ session('success') }}
                        </span>

                    </div>

                </div>

            @endif


            @if(session('error'))

                <div class="mb-6 rounded-2xl
                            bg-red-50
                            border border-red-200
                            px-5 py-4
                            text-red-700
                            shadow-sm">

                    <div class="flex items-center gap-3">

                        <span class="text-xl">⚠️</span>

                        <span class="font-semibold">
                            {{ session('error') }}
                        </span>

                    </div>

                </div>

            @endif


            {{-- ================================================= --}}
            {{-- BANNER --}}
            {{-- ================================================= --}}

            <div class="bg-gradient-to-r
                        from-[#0B2A55]
                        to-[#17477F]
                        rounded-3xl
                        p-6
                        mb-8
                        shadow-lg">

                <div class="flex flex-col md:flex-row
                            md:items-center
                            md:justify-between
                            gap-5">

                    <div>

                        <h1 class="text-2xl md:text-3xl
                                   font-bold text-white">

                            Gestion des comptes élèves 👤

                        </h1>

                        <p class="text-blue-100 mt-2">

                            Créez et gérez les accès personnels des élèves
                            à leur espace.

                        </p>

                    </div>

                    <div class="text-5xl">
                        🎓
                    </div>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- STATISTIQUES --}}
            {{-- ================================================= --}}

            @php

                $totalStudents = $students->total();

                $studentsWithAccount = \App\Models\Student::whereNotNull('user_id')->count();

                $studentsWithoutAccount = \App\Models\Student::whereNull('user_id')->count();

            @endphp


            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">


                {{-- Total --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm
                            border border-gray-100
                            hover:shadow-md transition">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm text-gray-500">
                                Total élèves
                            </p>

                            <p class="text-3xl font-bold
                                      text-[#0B2A55] mt-2">

                                {{ $totalStudents }}

                            </p>

                        </div>

                        <div class="w-14 h-14 rounded-xl
                                    bg-blue-50
                                    flex items-center justify-center
                                    text-3xl">

                            👨‍🎓

                        </div>

                    </div>

                </div>


                {{-- Avec compte --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm
                            border border-gray-100
                            hover:shadow-md transition">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm text-gray-500">
                                Comptes créés
                            </p>

                            <p class="text-3xl font-bold
                                      text-green-600 mt-2">

                                {{ $studentsWithAccount }}

                            </p>

                        </div>

                        <div class="w-14 h-14 rounded-xl
                                    bg-green-50
                                    flex items-center justify-center
                                    text-3xl">

                            ✅

                        </div>

                    </div>

                </div>


                {{-- Sans compte --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm
                            border border-gray-100
                            hover:shadow-md transition">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm text-gray-500">
                                Sans compte
                            </p>

                            <p class="text-3xl font-bold
                                      text-[#C89B3C] mt-2">

                                {{ $studentsWithoutAccount }}

                            </p>

                        </div>

                        <div class="w-14 h-14 rounded-xl
                                    bg-yellow-50
                                    flex items-center justify-center
                                    text-3xl">

                            ⚠️

                        </div>

                    </div>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- TABLEAU --}}
            {{-- ================================================= --}}

            <div class="bg-white rounded-3xl
                        shadow-sm
                        border border-gray-100
                        overflow-hidden">


                {{-- Header tableau --}}
                <div class="p-6 border-b border-gray-100">

                    <div class="flex flex-col sm:flex-row
                                sm:items-center
                                sm:justify-between
                                gap-4">

                        <div>

                            <h2 class="text-lg font-bold
                                       text-[#0B2A55]">

                                Liste des comptes

                            </h2>

                            <p class="text-sm text-gray-500 mt-1">

                                {{ $students->total() }}
                                élève(s) enregistré(s)

                            </p>

                        </div>

                        <div class="text-2xl">
                            👤
                        </div>

                    </div>

                </div>


                {{-- Tableau --}}
                <div class="overflow-x-auto">

                    <table class="min-w-full">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="px-6 py-4 text-left
                                           text-xs font-bold
                                           text-gray-500 uppercase">

                                    Élève

                                </th>

                                <th class="px-6 py-4 text-left
                                           text-xs font-bold
                                           text-gray-500 uppercase">

                                    Niveau

                                </th>

                                <th class="px-6 py-4 text-left
                                           text-xs font-bold
                                           text-gray-500 uppercase">

                                    Adresse e-mail

                                </th>

                                <th class="px-6 py-4 text-center
                                           text-xs font-bold
                                           text-gray-500 uppercase">

                                    Statut

                                </th>

                                <th class="px-6 py-4 text-right
                                           text-xs font-bold
                                           text-gray-500 uppercase">

                                    Action

                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100">

                            @forelse($students as $student)

                                <tr class="hover:bg-blue-50/40 transition">


                                    {{-- Élève --}}
                                    <td class="px-6 py-5">

                                        <div class="flex items-center gap-3">

                                            <div class="w-11 h-11 rounded-xl
                                                        bg-blue-50
                                                        flex items-center
                                                        justify-center">

                                                <span class="font-bold
                                                             text-[#0B2A55]">

                                                    {{ strtoupper(substr($student->first_name, 0, 1)) }}

                                                </span>

                                            </div>

                                            <div>

                                                <p class="font-bold
                                                          text-gray-800">

                                                    {{ $student->first_name }}
                                                    {{ $student->last_name }}

                                                </p>

                                                <p class="text-xs text-gray-400 mt-1">

                                                    Élève #{{ $student->id }}

                                                </p>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- Niveau --}}
                                    <td class="px-6 py-5">

                                        <span class="inline-flex
                                                     px-3 py-1
                                                     rounded-full
                                                     text-xs font-semibold
                                                     bg-blue-50
                                                     text-[#0B2A55]">

                                            {{ $student->level }}

                                        </span>

                                    </td>


                                    {{-- Email --}}
                                    <td class="px-6 py-5">

                                        @if($student->user)

                                            <span class="text-sm
                                                         font-medium
                                                         text-gray-700">

                                                {{ $student->user->email }}

                                            </span>

                                        @else

                                            <span class="text-sm
                                                         text-gray-400">

                                                —

                                            </span>

                                        @endif

                                    </td>


                                    {{-- Statut --}}
                                    <td class="px-6 py-5 text-center">

                                        @if($student->user)

                                            <span class="inline-flex
                                                         items-center gap-2
                                                         px-3 py-1.5
                                                         rounded-full
                                                         text-xs font-bold
                                                         bg-green-100
                                                         text-green-700">

                                                <span class="w-2 h-2
                                                             rounded-full
                                                             bg-green-500">
                                                </span>

                                                Compte actif

                                            </span>

                                        @else

                                            <span class="inline-flex
                                                         items-center gap-2
                                                         px-3 py-1.5
                                                         rounded-full
                                                         text-xs font-bold
                                                         bg-yellow-100
                                                         text-yellow-700">

                                                <span class="w-2 h-2
                                                             rounded-full
                                                             bg-yellow-500">
                                                </span>

                                                Sans compte

                                            </span>

                                        @endif

                                    </td>


                                    {{-- Action --}}
                                    <td class="px-6 py-5 text-right">

                                        @if($student->user)

                                            <span class="inline-flex
                                                         items-center gap-2
                                                         px-4 py-2
                                                         rounded-xl
                                                         bg-green-50
                                                         text-green-700
                                                         text-sm
                                                         font-semibold">

                                                ✅ Compte créé

                                            </span>

                                        @else

                                            <a
                                                href="{{ route('students.account.create', $student) }}"

                                                class="group inline-flex
                                                       items-center
                                                       justify-center
                                                       gap-2
                                                       px-5 py-3
                                                       rounded-xl
                                                       bg-[#0B2A55]
                                                       hover:bg-[#061A33]
                                                       text-white
                                                       font-bold
                                                       shadow-lg
                                                       border-b-4
                                                       border-[#C89B3C]
                                                       transition-all
                                                       duration-200
                                                       hover:-translate-y-0.5"
                                            >

                                                <span class="text-xl
                                                             group-hover:rotate-90
                                                             transition-transform
                                                             duration-300">

                                                    +

                                                </span>

                                                Créer le compte

                                            </a>

                                        @endif

                                    </td>

                                </tr>


                            @empty

                                <tr>

                                    <td colspan="5"
                                        class="px-6 py-16 text-center">

                                        <div class="text-5xl mb-4">
                                            👨‍🎓
                                        </div>

                                        <h3 class="text-lg font-bold
                                                   text-gray-700">

                                            Aucun élève trouvé

                                        </h3>

                                        <p class="text-sm text-gray-500 mt-1">

                                            Aucun élève n'est disponible
                                            pour le moment.

                                        </p>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- Pagination --}}
                @if($students->hasPages())

                    <div class="px-6 py-5
                                border-t border-gray-100">

                        {{ $students->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>