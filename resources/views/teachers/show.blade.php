<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center justify-between gap-4">

            <div class="flex items-center gap-4">

                <div class="w-14 h-14 rounded-2xl
                            bg-[#0B2A55]
                            flex items-center justify-center
                            shadow-lg">

                    <span class="text-3xl">👨‍🏫</span>

                </div>

                <div>

                    <h2 class="text-2xl font-extrabold
                               text-[#0B2A55]
                               dark:text-white">

                        Profil enseignant

                    </h2>

                    <p class="text-sm text-gray-500 mt-1">

                        Informations détaillées de l'enseignant

                    </p>

                </div>

            </div>


            <a href="{{ route('teachers.index') }}"
               class="hidden sm:inline-flex items-center gap-2
                      rounded-xl bg-gray-100
                      px-5 py-3
                      text-sm font-bold text-gray-700
                      hover:bg-gray-200
                      transition">

                ← Retour

            </a>

        </div>

    </x-slot>


    <div class="min-h-screen py-10
                bg-gradient-to-br
                from-gray-50 via-white to-blue-50
                dark:from-gray-900 dark:to-gray-800">

        <div class="max-w-5xl mx-auto
                    px-4 sm:px-6 lg:px-8">


            <!-- ================= HERO ================= -->

            <div class="relative overflow-hidden
                        rounded-3xl
                        bg-gradient-to-r
                        from-[#061A33]
                        via-[#0B2A55]
                        to-[#163E73]
                        shadow-xl
                        p-7 md:p-9 mb-7">

                <div class="absolute -right-20 -top-24
                            w-64 h-64 rounded-full
                            bg-[#C89B3C]/10">
                </div>

                <div class="absolute -right-10 -bottom-32
                            w-72 h-72 rounded-full
                            border border-[#C89B3C]/20">
                </div>


                <div class="relative z-10
                            flex flex-col md:flex-row
                            md:items-center
                            md:justify-between
                            gap-6">


                    <div class="flex items-center gap-5">

                        <div class="w-20 h-20 rounded-3xl
                                    bg-white/10
                                    backdrop-blur
                                    border border-white/10
                                    flex items-center
                                    justify-center
                                    text-4xl
                                    shadow-lg">

                            {{ strtoupper(substr($teacher->first_name, 0, 1)) }}

                        </div>


                        <div>

                            <p class="text-[#C89B3C]
                                      text-xs font-extrabold
                                      tracking-[0.2em]">

                                ACADÉMIE EL TAFAWOK

                            </p>

                            <h1 class="text-2xl md:text-3xl
                                       font-extrabold
                                       text-white mt-1">

                                {{ $teacher->first_name }}
                                {{ $teacher->last_name }}

                            </h1>

                            <p class="text-blue-100
                                      text-sm mt-2">

                                {{ $teacher->speciality }}

                            </p>

                        </div>

                    </div>


                    <!-- Statut -->

                    @if($teacher->active)

                        <div class="inline-flex items-center gap-2
                                    self-start md:self-auto
                                    rounded-full
                                    bg-green-500/20
                                    border border-green-300/20
                                    px-4 py-2
                                    text-sm font-bold
                                    text-green-100">

                            <span class="w-2.5 h-2.5
                                         rounded-full
                                         bg-green-400">
                            </span>

                            Enseignant actif

                        </div>

                    @else

                        <div class="inline-flex items-center gap-2
                                    self-start md:self-auto
                                    rounded-full
                                    bg-red-500/20
                                    border border-red-300/20
                                    px-4 py-2
                                    text-sm font-bold
                                    text-red-100">

                            <span class="w-2.5 h-2.5
                                         rounded-full
                                         bg-red-400">
                            </span>

                            Enseignant inactif

                        </div>

                    @endif

                </div>

            </div>


            <!-- ================= INFORMATIONS ================= -->

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">


                <!-- Informations principales -->

                <div class="lg:col-span-2
                            bg-white dark:bg-gray-800
                            rounded-3xl
                            shadow-sm
                            border border-gray-100
                            dark:border-gray-700
                            p-6 md:p-8">

                    <div class="flex items-center gap-4 mb-7">

                        <div class="w-12 h-12 rounded-xl
                                    bg-[#0B2A55]/10
                                    flex items-center justify-center
                                    text-xl">

                            👤

                        </div>

                        <div>

                            <h2 class="text-xl font-extrabold
                                       text-[#0B2A55]
                                       dark:text-white">

                                Informations personnelles

                            </h2>

                            <p class="text-sm text-gray-500 mt-1">

                                Données professionnelles

                            </p>

                        </div>

                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                        <!-- Prénom -->

                        <div class="rounded-2xl
                                    bg-gray-50
                                    border border-gray-100
                                    p-5">

                            <p class="text-xs font-bold
                                      uppercase tracking-wider
                                      text-gray-400">

                                Prénom

                            </p>

                            <p class="mt-2 text-lg font-extrabold
                                      text-gray-800">

                                {{ $teacher->first_name }}

                            </p>

                        </div>


                        <!-- Nom -->

                        <div class="rounded-2xl
                                    bg-gray-50
                                    border border-gray-100
                                    p-5">

                            <p class="text-xs font-bold
                                      uppercase tracking-wider
                                      text-gray-400">

                                Nom

                            </p>

                            <p class="mt-2 text-lg font-extrabold
                                      text-gray-800">

                                {{ $teacher->last_name }}

                            </p>

                        </div>


                        <!-- Spécialité -->

                        <div class="rounded-2xl
                                    bg-[#C89B3C]/10
                                    border border-[#C89B3C]/20
                                    p-5">

                            <p class="text-xs font-bold
                                      uppercase tracking-wider
                                      text-[#8B691F]">

                                Spécialité

                            </p>

                            <p class="mt-2 text-lg font-extrabold
                                      text-[#0B2A55]">

                                {{ $teacher->speciality }}

                            </p>

                        </div>


                        <!-- Date recrutement -->

                        <div class="rounded-2xl
                                    bg-gray-50
                                    border border-gray-100
                                    p-5">

                            <p class="text-xs font-bold
                                      uppercase tracking-wider
                                      text-gray-400">

                                Date de recrutement

                            </p>

                            <p class="mt-2 text-lg font-extrabold
                                      text-gray-800">

                                {{ $teacher->hire_date
                                    ? \Carbon\Carbon::parse($teacher->hire_date)->format('d/m/Y')
                                    : '-' }}

                            </p>

                        </div>

                    </div>

                </div>


                <!-- Contact -->

                <div class="bg-white dark:bg-gray-800
                            rounded-3xl
                            shadow-sm
                            border border-gray-100
                            dark:border-gray-700
                            p-6 md:p-7">

                    <div class="flex items-center gap-3 mb-6">

                        <div class="w-11 h-11 rounded-xl
                                    bg-[#C89B3C]/10
                                    flex items-center
                                    justify-center">

                            📞

                        </div>

                        <div>

                            <h2 class="text-lg font-extrabold
                                       text-[#0B2A55]
                                       dark:text-white">

                                Contact

                            </h2>

                            <p class="text-xs text-gray-500">

                                Coordonnées

                            </p>

                        </div>

                    </div>


                    <div class="space-y-4">


                        <!-- Téléphone -->

                        <div class="rounded-2xl
                                    bg-gray-50
                                    p-4">

                            <p class="text-xs font-bold
                                      text-gray-400">

                                Téléphone

                            </p>

                            <p class="mt-1 font-bold
                                      text-gray-800">

                                {{ $teacher->phone ?? '-' }}

                            </p>

                        </div>


                        <!-- Email -->

                        <div class="rounded-2xl
                                    bg-gray-50
                                    p-4">

                            <p class="text-xs font-bold
                                      text-gray-400">

                                Email

                            </p>

                            <p class="mt-1 font-bold
                                      text-gray-800 break-words">

                                {{ $teacher->email ?? '-' }}

                            </p>

                        </div>


                        <!-- Adresse -->

                        <div class="rounded-2xl
                                    bg-gray-50
                                    p-4">

                            <p class="text-xs font-bold
                                      text-gray-400">

                                Adresse

                            </p>

                            <p class="mt-1 font-bold
                                      text-gray-800">

                                {{ $teacher->address ?? '-' }}

                            </p>

                        </div>

                    </div>

                </div>

            </div>

{{-- ================= NIVEAUX D'ENSEIGNEMENT ================= --}}

<div class="bg-white dark:bg-gray-800
            rounded-3xl
            shadow-sm
            border border-gray-100
            dark:border-gray-700
            p-6 md:p-8 mb-6">

    <div class="flex items-center gap-4 mb-6">

        <div class="w-12 h-12 rounded-xl
                    bg-blue-100
                    flex items-center
                    justify-center
                    text-xl">

            🎓

        </div>

        <div>

            <h2 class="text-xl font-extrabold
                       text-[#0B2A55]
                       dark:text-white">

                Niveaux d'enseignement

            </h2>

            <p class="text-sm text-gray-500 mt-1">

                Niveaux dans lesquels cet enseignant peut enseigner.

            </p>

        </div>

    </div>


    @if($teacher->levels->count())

        <div class="grid grid-cols-1
                    sm:grid-cols-2
                    lg:grid-cols-3
                    gap-4">

            @foreach($teacher->levels as $level)

                <div class="flex items-center gap-4
                            rounded-2xl
                            border border-blue-100
                            bg-blue-50
                            p-4">

                    <div class="w-11 h-11
                                rounded-xl
                                bg-white
                                flex items-center
                                justify-center
                                text-xl
                                shadow-sm">

                        🎓

                    </div>

                    <div>

                        <div class="font-extrabold
                                    text-[#0B2A55]">

                            {{ $level->name }}

                        </div>

                        <div class="text-xs
                                    font-bold
                                    text-blue-600
                                    mt-1">

                            {{ $level->code }}

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    @else

        <div class="rounded-2xl
                    border border-yellow-100
                    bg-yellow-50
                    p-5">

            <div class="flex items-center gap-3">

                <span class="text-xl">⚠️</span>

                <div>

                    <p class="font-bold text-yellow-800">

                        Aucun niveau attribué

                    </p>

                    <p class="text-sm text-yellow-700 mt-1">

                        Cet enseignant n'a encore aucun niveau d'enseignement.

                    </p>

                </div>

            </div>

        </div>

    @endif

</div>
            <!-- ================= ACTIONS ================= -->

            <div class="mt-6
                        bg-white dark:bg-gray-800
                        rounded-3xl
                        shadow-sm
                        border border-gray-100
                        dark:border-gray-700
                        p-6">

                <div class="flex flex-col sm:flex-row
                            items-stretch sm:items-center
                            justify-between gap-4">


                    <a
                        href="{{ route('teachers.index') }}"

                        class="inline-flex
                               items-center
                               justify-center
                               gap-2
                               rounded-xl
                               bg-gray-100
                               px-6 py-3.5
                               text-sm font-bold
                               text-gray-700
                               hover:bg-gray-200
                               transition">

                        ← Retour à la liste

                    </a>


                    <div class="flex flex-col sm:flex-row gap-3">


                        <a
                            href="{{ route('teachers.edit', $teacher) }}"

                            class="inline-flex
                                   items-center
                                   justify-center
                                   gap-2
                                   rounded-xl
                                   bg-[#0B2A55]
                                   px-6 py-3.5
                                   text-sm font-bold
                                   text-white
                                   shadow-md
                                   border-b-4
                                   border-[#C89B3C]
                                   hover:bg-[#061A33]
                                   hover:-translate-y-0.5
                                   transition">

                            ✏️

                            Modifier

                        </a>


                        <form
                            action="{{ route('teachers.destroy', $teacher) }}"
                            method="POST"
                            class="inline"
                        >

                            @csrf

                            @method('DELETE')

                            <button
                                type="submit"

                                onclick="return confirm('Voulez-vous vraiment supprimer cet enseignant ?')"

                                class="w-full inline-flex
                                       items-center
                                       justify-center
                                       gap-2
                                       rounded-xl
                                       bg-red-50
                                       px-6 py-3.5
                                       text-sm font-bold
                                       text-red-600
                                       hover:bg-red-100
                                       transition">

                                🗑️

                                Supprimer

                            </button>

                        </form>

                    </div>

                </div>

            </div>


            <!-- Footer -->

            <div class="mt-6 text-center">

                <p class="text-xs text-gray-400">

                    Académie El Tafawok • Profil enseignant

                </p>

            </div>


        </div>

    </div>

</x-app-layout>