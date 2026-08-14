<x-app-layout>

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <x-slot name="header">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex items-center gap-4">

                {{-- Icon --}}
                <div class="w-14 h-14 rounded-2xl bg-[#0B2A55]
                            flex items-center justify-center shadow-md">

                    <span class="text-2xl">🎓</span>

                </div>

                <div>

                    <h2 class="font-extrabold text-2xl text-[#0B2A55]">
                        Tableau de bord
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Bienvenue dans votre espace de gestion —
                        Académie El Tafawok
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
                        rounded-3xl
                        shadow-xl
                        mb-8">

                {{-- Decorative circles --}}
                <div class="absolute -right-16 -top-20
                            w-64 h-64
                            rounded-full
                            bg-white/10">
                </div>

                <div class="absolute right-20 -bottom-28
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

                            Bonjour, {{ Auth::user()->name }} 👋

                        </h1>

                        <span class="text-3xl">
                            🎓
                        </span>

                    </div>

                    <p class="mt-3
                              text-blue-100
                              text-base md:text-lg">

                        Gérez facilement vos élèves,
                        enseignants et matières.

                    </p>

                </div>

            </div>


            {{-- =================================================
                STATISTIQUES PRINCIPALES
            ================================================== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">


                {{-- Élèves --}}
                <div class="relative overflow-hidden
                            bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            hover:shadow-md
                            hover:-translate-y-1
                            transition-all duration-300">

                    <div class="absolute right-0 top-0
                                w-32 h-32
                                bg-blue-50
                                rounded-bl-full">
                    </div>

                    <div class="relative p-6">

                        <div class="flex items-center justify-between">

                            <div class="w-14 h-14
                                        rounded-2xl
                                        bg-blue-100
                                        flex items-center
                                        justify-center
                                        text-2xl">

                                👨‍🎓

                            </div>

                            <span class="px-4 py-2
                                         rounded-full
                                         bg-blue-50
                                         text-blue-600
                                         text-xs
                                         font-extrabold
                                         uppercase">

                                Élèves

                            </span>

                        </div>

                        <p class="mt-6 text-sm text-gray-500">
                            Total Élèves
                        </p>

                        <p class="mt-1
                                  text-3xl
                                  font-extrabold
                                  text-[#0B2A55]">

                            {{ $studentsCount }}

                        </p>

                        <a href="{{ route('students.index') }}"
                           class="inline-flex items-center
                                  mt-3
                                  text-sm
                                  font-bold
                                  text-[#C89B3C]
                                  hover:text-[#0B2A55]
                                  transition">

                            Voir les élèves

                            <span class="ml-1">
                                →
                            </span>

                        </a>

                    </div>

                </div>


                {{-- Enseignants --}}
                <div class="relative overflow-hidden
                            bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            hover:shadow-md
                            hover:-translate-y-1
                            transition-all duration-300">

                    <div class="absolute right-0 top-0
                                w-32 h-32
                                bg-yellow-50
                                rounded-bl-full">
                    </div>

                    <div class="relative p-6">

                        <div class="flex items-center justify-between">

                            <div class="w-14 h-14
                                        rounded-2xl
                                        bg-yellow-100
                                        flex items-center
                                        justify-center
                                        text-2xl">

                                👨‍🏫

                            </div>

                            <span class="px-4 py-2
                                         rounded-full
                                         bg-yellow-50
                                         text-yellow-700
                                         text-xs
                                         font-extrabold
                                         uppercase">

                                Équipe

                            </span>

                        </div>

                        <p class="mt-6 text-sm text-gray-500">
                            Enseignants
                        </p>

                        <p class="mt-1
                                  text-3xl
                                  font-extrabold
                                  text-[#0B2A55]">

                            {{ $teachersCount }}

                        </p>

                        <a href="{{ route('teachers.index') }}"
                           class="inline-flex items-center
                                  mt-3
                                  text-sm
                                  font-bold
                                  text-[#C89B3C]
                                  hover:text-[#0B2A55]
                                  transition">

                            Voir les enseignants

                            <span class="ml-1">
                                →
                            </span>

                        </a>

                    </div>

                </div>


                {{-- Matières --}}
                <div class="relative overflow-hidden
                            bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            hover:shadow-md
                            hover:-translate-y-1
                            transition-all duration-300">

                    <div class="absolute right-0 top-0
                                w-32 h-32
                                bg-purple-50
                                rounded-bl-full">
                    </div>

                    <div class="relative p-6">

                        <div class="flex items-center justify-between">

                            <div class="w-14 h-14
                                        rounded-2xl
                                        bg-purple-100
                                        flex items-center
                                        justify-center
                                        text-2xl">

                                📚

                            </div>

                            <span class="px-4 py-2
                                         rounded-full
                                         bg-purple-50
                                         text-purple-600
                                         text-xs
                                         font-extrabold
                                         uppercase">

                                Cours

                            </span>

                        </div>

                        <p class="mt-6 text-sm text-gray-500">
                            Matières
                        </p>

                        <p class="mt-1
                                  text-3xl
                                  font-extrabold
                                  text-[#0B2A55]">

                            {{ $subjectsCount }}

                        </p>

                        <a href="{{ route('subjects.index') }}"
                           class="inline-flex items-center
                                  mt-3
                                  text-sm
                                  font-bold
                                  text-[#C89B3C]
                                  hover:text-[#0B2A55]
                                  transition">

                            Voir les matières

                            <span class="ml-1">
                                →
                            </span>

                        </a>

                    </div>

                </div>


                {{-- Inscriptions --}}
                <div class="relative overflow-hidden
                            bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            hover:shadow-md
                            hover:-translate-y-1
                            transition-all duration-300">

                    <div class="absolute right-0 top-0
                                w-32 h-32
                                bg-green-50
                                rounded-bl-full">
                    </div>

                    <div class="relative p-6">

                        <div class="flex items-center justify-between">

                            <div class="w-14 h-14
                                        rounded-2xl
                                        bg-green-100
                                        flex items-center
                                        justify-center
                                        text-2xl">

                                📝

                            </div>

                            <span class="px-4 py-2
                                         rounded-full
                                         bg-green-50
                                         text-green-600
                                         text-xs
                                         font-extrabold
                                         uppercase">

                                Actives

                            </span>

                        </div>

                        <p class="mt-6 text-sm text-gray-500">
                            Inscriptions
                        </p>

                        <p class="mt-1
                                  text-3xl
                                  font-extrabold
                                  text-[#0B2A55]">

                            {{ $enrollmentsCount }}

                        </p>

                        <span class="inline-flex items-center
                                     mt-3
                                     text-sm
                                     text-green-600
                                     font-bold">

                            ● Inscriptions actives

                        </span>

                    </div>

                </div>

            </div>


            {{-- =================================================
                PARTIE INFÉRIEURE
            ================================================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">


                {{-- =================================================
                    DERNIÈRES INSCRIPTIONS
                ================================================== --}}
                <div class="bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            overflow-hidden">

                    <div class="p-6 border-b border-gray-100">

                        <div class="flex items-center justify-between">

                            <div>

                                <h2 class="text-lg
                                           font-extrabold
                                           text-[#0B2A55]">

                                    Dernières inscriptions

                                </h2>

                                <p class="text-sm text-gray-500 mt-1">

                                    Les dernières inscriptions enregistrées

                                </p>

                            </div>

                            <div class="w-11 h-11
                                        rounded-xl
                                        bg-[#0B2A55]
                                        flex items-center
                                        justify-center
                                        text-xl">

                                📝

                            </div>

                        </div>

                    </div>


                    <div class="p-6">

                        @if($latestEnrollments->count() > 0)

                            <div class="space-y-3">

                                @foreach($latestEnrollments as $enrollment)

                                    <div class="flex items-center justify-between
                                                p-4
                                                rounded-2xl
                                                bg-gray-50
                                                border border-gray-100
                                                hover:bg-blue-50
                                                hover:border-blue-100
                                                transition">

                                        <div>

                                            <p class="font-bold text-gray-800">

                                                {{ $enrollment->student->first_name }}
                                                {{ $enrollment->student->last_name }}

                                            </p>

                                            <p class="text-sm text-gray-500 mt-1">

                                                {{ $enrollment->subject->name }}

                                                @if($enrollment->teacher)

                                                    —
                                                    {{ $enrollment->teacher->first_name }}
                                                    {{ $enrollment->teacher->last_name }}

                                                @endif

                                            </p>

                                        </div>

                                        <div class="text-right">

                                            <span class="px-3 py-1
                                                         rounded-full
                                                         bg-green-100
                                                         text-green-700
                                                         text-xs
                                                         font-bold">

                                                {{ ucfirst($enrollment->status) }}

                                            </span>

                                            <p class="text-xs text-gray-400 mt-2">

                                                {{ $enrollment->start_date?->format('d/m/Y') }}

                                            </p>

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        @else

                            <div class="text-center py-10">

                                <div class="w-20 h-20
                                            rounded-3xl
                                            bg-gray-100
                                            flex items-center
                                            justify-center
                                            text-4xl
                                            mx-auto mb-4">

                                    📝

                                </div>

                                <h3 class="text-lg
                                           font-extrabold
                                           text-[#0B2A55]">

                                    Aucune inscription

                                </h3>

                                <p class="text-sm text-gray-500 mt-1">

                                    Aucune inscription pour le moment.

                                </p>

                            </div>

                        @endif

                    </div>

                </div>


                {{-- =================================================
                    ACTIONS RAPIDES
                ================================================== --}}
                <div class="bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            overflow-hidden">

                    <div class="p-6 border-b border-gray-100">

                        <h2 class="text-lg
                                   font-extrabold
                                   text-[#0B2A55]">

                            Actions rapides

                        </h2>

                        <p class="text-sm text-gray-500 mt-1">

                            Accédez rapidement aux fonctions principales

                        </p>

                    </div>


                    <div class="p-6 space-y-4">


                        {{-- Ajouter élève --}}
                        <a href="{{ route('students.create') }}"
                           class="group flex items-center gap-4
                                  p-4
                                  rounded-2xl
                                  bg-blue-50
                                  border border-blue-100
                                  hover:bg-blue-100
                                  hover:-translate-y-0.5
                                  transition-all duration-300">

                            <div class="w-12 h-12
                                        rounded-2xl
                                        bg-[#0B2A55]
                                        text-white
                                        flex items-center
                                        justify-center
                                        text-xl
                                        shadow-sm
                                        group-hover:scale-105
                                        transition">

                                👨‍🎓

                            </div>

                            <div>

                                <p class="font-bold text-[#0B2A55]">
                                    Ajouter un élève
                                </p>

                                <p class="text-sm text-gray-500">
                                    Enregistrer un nouvel élève
                                </p>

                            </div>

                        </a>


                        {{-- Ajouter enseignant --}}
                        <a href="{{ route('teachers.create') }}"
                           class="group flex items-center gap-4
                                  p-4
                                  rounded-2xl
                                  bg-yellow-50
                                  border border-yellow-100
                                  hover:bg-yellow-100
                                  hover:-translate-y-0.5
                                  transition-all duration-300">

                            <div class="w-12 h-12
                                        rounded-2xl
                                        bg-[#C89B3C]
                                        text-white
                                        flex items-center
                                        justify-center
                                        text-xl
                                        shadow-sm
                                        group-hover:scale-105
                                        transition">

                                👨‍🏫

                            </div>

                            <div>

                                <p class="font-bold text-[#0B2A55]">
                                    Ajouter un enseignant
                                </p>

                                <p class="text-sm text-gray-500">
                                    Enregistrer un nouvel enseignant
                                </p>

                            </div>

                        </a>


                        {{-- Ajouter matière --}}
                        <a href="{{ route('subjects.create') }}"
                           class="group flex items-center gap-4
                                  p-4
                                  rounded-2xl
                                  bg-purple-50
                                  border border-purple-100
                                  hover:bg-purple-100
                                  hover:-translate-y-0.5
                                  transition-all duration-300">

                            <div class="w-12 h-12
                                        rounded-2xl
                                        bg-purple-600
                                        text-white
                                        flex items-center
                                        justify-center
                                        text-xl
                                        shadow-sm
                                        group-hover:scale-105
                                        transition">

                                📚

                            </div>

                            <div>

                                <p class="font-bold text-[#0B2A55]">
                                    Ajouter une matière
                                </p>

                                <p class="text-sm text-gray-500">
                                    Créer une nouvelle matière
                                </p>

                            </div>

                        </a>


                        {{-- Gérer les annonces --}}
                        <a href="{{ route('announcements.index') }}"
                           class="group flex items-center gap-4
                                  p-4
                                  rounded-2xl
                                  bg-red-50
                                  border border-red-100
                                  hover:bg-red-100
                                  hover:-translate-y-0.5
                                  transition-all duration-300">

                            <div class="w-12 h-12
                                        rounded-2xl
                                        bg-red-600
                                        text-white
                                        flex items-center
                                        justify-center
                                        text-xl
                                        shadow-sm
                                        group-hover:scale-105
                                        transition">

                                📢

                            </div>

                            <div>

                                <p class="font-bold text-[#0B2A55]">
                                    Gérer les annonces
                                </p>

                                <p class="text-sm text-gray-500">
                                    Publier une annonce pour les élèves
                                </p>

                            </div>

                        </a>


                        {{-- =================================================
                            GESTION DES ADMINISTRATEURS
                        ================================================== --}}
                        <a href="{{ route('admins.index') }}"
                           class="group flex items-center gap-4
                                  p-4
                                  rounded-2xl
                                  bg-indigo-50
                                  border border-indigo-100
                                  hover:bg-indigo-100
                                  hover:-translate-y-0.5
                                  transition-all duration-300">

                            <div class="w-12 h-12
                                        rounded-2xl
                                        bg-indigo-600
                                        text-white
                                        flex items-center
                                        justify-center
                                        text-xl
                                        shadow-sm
                                        group-hover:scale-105
                                        transition">

                                👨‍💼

                            </div>

                            <div>

                                <p class="font-bold text-[#0B2A55]">
                                    Gestion des administrateurs
                                </p>

                                <p class="text-sm text-gray-500">
                                    Gérer les comptes administrateurs
                                </p>

                            </div>

                        </a>


                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>