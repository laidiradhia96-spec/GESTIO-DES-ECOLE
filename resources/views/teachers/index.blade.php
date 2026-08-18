<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div class="flex items-center gap-4">

                <div class="w-14 h-14 rounded-2xl bg-[#0B2A55]
                            flex items-center justify-center
                            shadow-lg">

                    <span class="text-3xl">👨‍🏫</span>

                </div>

                <div>

                    <h2 class="text-2xl font-extrabold text-[#0B2A55]
                               dark:text-white">

                        Enseignants

                    </h2>

                    <p class="text-sm text-gray-500 mt-1">

                        Gestion de l'équipe pédagogique

                    </p>

                </div>

            </div>


            <a href="{{ route('teachers.create') }}"
               class="group inline-flex items-center justify-center gap-2
                      rounded-xl bg-[#0B2A55]
                      px-5 py-3
                      text-sm font-bold text-white
                      shadow-lg
                      border-b-4 border-[#C89B3C]
                      hover:bg-[#061A33]
                      hover:-translate-y-0.5
                      transition-all duration-200">

                <span class="text-xl group-hover:scale-110 transition">

                    +

                </span>

                Ajouter un enseignant

            </a>

        </div>

    </x-slot>


    <div class="min-h-screen py-10
                bg-gradient-to-br
                from-gray-50 via-white to-blue-50
                dark:from-gray-900 dark:to-gray-800">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">


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
                            md:justify-between gap-6">

                    <div class="flex items-center gap-4">

                        <div class="w-16 h-16 rounded-2xl
                                    bg-white/10
                                    backdrop-blur
                                    border border-white/10
                                    flex items-center justify-center
                                    text-3xl">

                            👨‍🏫

                        </div>

                        <div>

                            <p class="text-[#C89B3C]
                                      text-xs font-extrabold
                                      tracking-[0.2em]">

                                ACADÉMIE EL TAFAWOK

                            </p>

                            <h1 class="text-2xl md:text-3xl
                                       font-extrabold text-white mt-1">

                                Équipe pédagogique

                            </h1>

                            <p class="text-blue-100 text-sm mt-1">

                                Gérez facilement vos enseignants.

                            </p>

                        </div>

                    </div>


                    <div class="hidden md:flex
                                items-center gap-3
                                bg-white/10
                                rounded-2xl
                                px-5 py-4
                                border border-white/10">

                        <div class="text-2xl">
                            🎓
                        </div>

                        <div>

                            <p class="text-white font-bold">
                                Enseignants
                            </p>

                            <p class="text-blue-100 text-xs">
                                Gestion académique
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ================= SUCCESS ================= -->

            @if(session('success'))

                <div class="mb-6 flex items-center gap-4
                            rounded-2xl
                            border border-green-200
                            bg-green-50
                            px-5 py-4
                            text-green-700
                            shadow-sm">

                    <div class="w-10 h-10 rounded-full
                                bg-green-100
                                flex items-center justify-center
                                text-lg">

                        ✓

                    </div>

                    <div>

                        <p class="font-bold">
                            Opération réussie
                        </p>

                        <p class="text-sm">
                            {{ session('success') }}
                        </p>

                    </div>

                </div>

            @endif


            <!-- ================= ERROR ================= -->

            @if(session('error'))

                <div class="mb-6 flex items-center gap-4
                            rounded-2xl
                            border border-red-200
                            bg-red-50
                            px-5 py-4
                            text-red-700
                            shadow-sm">

                    <div class="w-10 h-10 rounded-full
                                bg-red-100
                                flex items-center justify-center
                                text-lg">

                        ⚠

                    </div>

                    <div>

                        <p class="font-bold">
                            Action impossible
                        </p>

                        <p class="text-sm">
                            {{ session('error') }}
                        </p>

                    </div>

                </div>

            @endif


            <!-- ================= SEARCH ================= -->

            <div class="bg-white dark:bg-gray-800
                        rounded-3xl
                        shadow-sm
                        border border-gray-100
                        dark:border-gray-700
                        p-6 md:p-7 mb-7">

                <div class="flex items-center gap-3 mb-6">

                    <div class="w-11 h-11 rounded-xl
                                bg-[#0B2A55]/10
                                flex items-center justify-center">

                        🔎

                    </div>

                    <div>

                        <h2 class="text-lg font-extrabold
                                   text-[#0B2A55]
                                   dark:text-white">

                            Rechercher un enseignant

                        </h2>

                        <p class="text-sm text-gray-500">

                            Filtrez la liste rapidement.

                        </p>

                    </div>

                </div>


                <form method="GET"
                      action="{{ route('teachers.index') }}">

                    <div class="grid grid-cols-1 md:grid-cols-3
                                gap-5">


                        <!-- Recherche -->

                        <div>

                            <label class="block text-sm font-bold
                                          text-[#0B2A55]
                                          dark:text-gray-200 mb-2">

                                Recherche

                            </label>

                            <div class="relative">

                                <span class="absolute left-4 top-1/2
                                             -translate-y-1/2 text-lg">

                                    🔍

                                </span>

                                <input
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Nom, prénom, spécialité..."

                                    class="w-full rounded-xl
                                           border-2 border-gray-200
                                           bg-gray-50
                                           py-3.5 pl-12 pr-4
                                           text-gray-700
                                           outline-none
                                           transition

                                           focus:border-[#C89B3C]
                                           focus:ring-4
                                           focus:ring-[#C89B3C]/10"
                                >

                            </div>

                        </div>


                        <!-- Année scolaire -->

                        <div>

                            <label class="block text-sm font-bold
                                          text-[#0B2A55]
                                          dark:text-gray-200 mb-2">

                                Année scolaire

                            </label>

                            <select
                                name="school_year_id"

                                class="w-full rounded-xl
                                       border-2 border-gray-200
                                       bg-gray-50
                                       py-3.5 px-4
                                       text-gray-700
                                       outline-none
                                       transition

                                       focus:border-[#C89B3C]
                                       focus:ring-4
                                       focus:ring-[#C89B3C]/10">

                                <option value=""
                                    {{ $schoolYearId === null ? 'selected' : '' }}>
                                    Toutes les années
                                </option>

                                @foreach($schoolYears as $schoolYear)

                                    <option value="{{ $schoolYear->id }}"
                                        {{ $schoolYearId === $schoolYear->id ? 'selected' : '' }}>
                                        {{ $schoolYear->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <!-- Statut -->

                        <div>

                            <label class="block text-sm font-bold
                                          text-[#0B2A55]
                                          dark:text-gray-200 mb-2">

                                Statut

                            </label>

                            <select
                                name="active"

                                class="w-full rounded-xl
                                       border-2 border-gray-200
                                       bg-gray-50
                                       py-3.5 px-4
                                       text-gray-700
                                       outline-none
                                       transition

                                       focus:border-[#C89B3C]
                                       focus:ring-4
                                       focus:ring-[#C89B3C]/10">

                                <option value="">
                                    Tous les enseignants
                                </option>

                                <option value="1"
                                    {{ request('active') === '1' ? 'selected' : '' }}>

                                    ✓ Actifs

                                </option>

                                <option value="0"
                                    {{ request('active') === '0' ? 'selected' : '' }}>

                                    ✕ Inactifs

                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- Buttons -->

                    <div class="mt-6 flex flex-col sm:flex-row gap-3">

                        <button
                            type="submit"

                            class="inline-flex items-center
                                   justify-center gap-2
                                   rounded-xl
                                   bg-[#0B2A55]
                                   px-6 py-3.5
                                   text-sm font-bold text-white
                                   shadow-md
                                   hover:bg-[#061A33]
                                   hover:-translate-y-0.5
                                   transition">

                            🔍

                            Rechercher

                        </button>


                        <a
                            href="{{ route('teachers.index') }}"

                            class="inline-flex items-center
                                   justify-center gap-2
                                   rounded-xl
                                   bg-gray-100
                                   px-6 py-3.5
                                   text-sm font-bold
                                   text-gray-700
                                   hover:bg-gray-200
                                   transition">

                            ↻

                            Réinitialiser

                        </a>

                    </div>

                </form>

            </div>


            <!-- ================= TABLE ================= -->

            <div class="bg-white dark:bg-gray-800
                        rounded-3xl
                        shadow-sm
                        border border-gray-100
                        dark:border-gray-700
                        overflow-hidden">


                <div class="px-6 md:px-7 py-6
                            border-b border-gray-100
                            dark:border-gray-700
                            flex flex-col sm:flex-row
                            sm:items-center
                            sm:justify-between gap-3">

                    <div>

                        <h2 class="text-xl font-extrabold
                                   text-[#0B2A55]
                                   dark:text-white">

                            Liste des enseignants

                        </h2>

                        <p class="text-sm text-gray-500 mt-1">

                            Consultez et gérez votre équipe pédagogique.

                        </p>

                    </div>


                    @if($teachers->count() > 0)

                        <div class="inline-flex items-center
                                    gap-2 px-4 py-2
                                    rounded-xl
                                    bg-[#C89B3C]/10
                                    text-[#8B691F]
                                    text-sm font-bold">

                            👥

                            {{ $teachers->total() }} enseignant(s)

                        </div>

                    @endif

                </div>


                <div class="p-4 md:p-6">

                    @if($teachers->count() > 0)

                        <div class="overflow-x-auto
                                    rounded-2xl
                                    border border-gray-100">

                            <table class="min-w-full">

                                <thead>

                                    <tr class="bg-[#0B2A55]">

                                        <th class="px-5 py-4
                                                   text-left
                                                   text-xs font-bold
                                                   uppercase
                                                   tracking-wider
                                                   text-white">

                                            Enseignant

                                        </th>

                                        <th class="px-5 py-4
                                                   text-left
                                                   text-xs font-bold
                                                   uppercase
                                                   tracking-wider
                                                   text-white">

                                            Spécialité

                                        </th>

                                        <th class="px-5 py-4
                                                   text-left
                                                   text-xs font-bold
                                                   uppercase
                                                   tracking-wider
                                                   text-white">

                                            Téléphone

                                        </th>

                                        <th class="px-5 py-4
                                                   text-left
                                                   text-xs font-bold
                                                   uppercase
                                                   tracking-wider
                                                   text-white">

                                            Statut

                                        </th>

                                        <th class="px-5 py-4
                                                   text-right
                                                   text-xs font-bold
                                                   uppercase
                                                   tracking-wider
                                                   text-white">

                                            Actions

                                        </th>

                                    </tr>

                                </thead>


                                <tbody class="divide-y divide-gray-100">

                                    @foreach($teachers as $teacher)

                                        <tr class="group
                                                   hover:bg-blue-50/50
                                                   transition duration-200">


                                            <!-- Enseignant -->

                                            <td class="px-5 py-5">

                                                <div class="flex items-center gap-4">

                                                    <div class="w-11 h-11
                                                                rounded-xl
                                                                bg-[#0B2A55]
                                                                flex items-center
                                                                justify-center
                                                                text-white
                                                                font-bold
                                                                shadow-sm
                                                                group-hover:scale-105
                                                                transition">

                                                        {{ strtoupper(substr($teacher->first_name, 0, 1)) }}

                                                    </div>

                                                    <div>

                                                        <p class="font-extrabold
                                                                  text-gray-900">

                                                            {{ $teacher->first_name }}
                                                            {{ $teacher->last_name }}

                                                        </p>

                                                        <p class="text-xs
                                                                  text-gray-400
                                                                  mt-1">

                                                            Enseignant

                                                        </p>

                                                    </div>

                                                </div>

                                            </td>


                                            <!-- Spécialité -->

                                            <td class="px-5 py-5">

                                                <span class="inline-flex
                                                             items-center
                                                             px-3 py-1.5
                                                             rounded-lg
                                                             bg-[#C89B3C]/10
                                                             text-[#8B691F]
                                                             text-sm
                                                             font-semibold">

                                                    {{ $teacher->subjects->pluck('name')->join(', ') ?: $teacher->speciality }}

                                                </span>

                                            </td>


                                            <!-- Téléphone -->

                                            <td class="px-5 py-5
                                                       text-gray-600">

                                                <div class="flex items-center gap-2">

                                                    <span>📞</span>

                                                    {{ $teacher->phone ?? '-' }}

                                                </div>

                                            </td>


                                            <!-- Statut -->

                                            <td class="px-5 py-5">

                                                @if($teacher->active)

                                                    <span class="inline-flex
                                                                 items-center
                                                                 gap-2
                                                                 rounded-full
                                                                 bg-green-100
                                                                 px-3 py-1.5
                                                                 text-xs
                                                                 font-bold
                                                                 text-green-700">

                                                        <span class="w-2 h-2
                                                                     rounded-full
                                                                     bg-green-500">
                                                        </span>

                                                        Actif

                                                    </span>

                                                @else

                                                    <span class="inline-flex
                                                                 items-center
                                                                 gap-2
                                                                 rounded-full
                                                                 bg-red-100
                                                                 px-3 py-1.5
                                                                 text-xs
                                                                 font-bold
                                                                 text-red-700">

                                                        <span class="w-2 h-2
                                                                     rounded-full
                                                                     bg-red-500">
                                                        </span>

                                                        Inactif

                                                    </span>

                                                @endif

                                            </td>


                                            <!-- Actions -->

                                            <td class="px-5 py-5">

                                                <div class="flex items-center
                                                            justify-end gap-2">


                                                    <a
                                                        href="{{ route('teachers.show', $teacher) }}"

                                                        class="w-10 h-10
                                                               rounded-xl
                                                               bg-blue-50
                                                               text-blue-700
                                                               flex items-center
                                                               justify-center
                                                               hover:bg-blue-100
                                                               hover:-translate-y-0.5
                                                               transition"

                                                        title="Voir">

                                                        👁️

                                                    </a>


                                                    <a
                                                        href="{{ route('teachers.edit', $teacher) }}"

                                                        class="w-10 h-10
                                                               rounded-xl
                                                               bg-green-50
                                                               text-green-700
                                                               flex items-center
                                                               justify-center
                                                               hover:bg-green-100
                                                               hover:-translate-y-0.5
                                                               transition"

                                                        title="Modifier">

                                                        ✏️

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

                                                            onclick="return confirm('Voulez-vous supprimer cet enseignant ?')"

                                                            class="w-10 h-10
                                                                   rounded-xl
                                                                   bg-red-50
                                                                   text-red-600
                                                                   flex items-center
                                                                   justify-center
                                                                   hover:bg-red-100
                                                                   hover:-translate-y-0.5
                                                                   transition"

                                                            title="Supprimer">

                                                            🗑️

                                                        </button>

                                                    </form>

                                                </div>

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>


                        <!-- Pagination -->

                        <div class="mt-6">

                            {{ $teachers->links() }}

                        </div>


                    @else


                        <!-- ================= EMPTY ================= -->

                        <div class="py-16 text-center">

                            <div class="mx-auto mb-6
                                        w-20 h-20
                                        rounded-3xl
                                        bg-[#0B2A55]/10
                                        flex items-center
                                        justify-center
                                        text-4xl">

                                👨‍🏫

                            </div>


                            <h3 class="text-xl font-extrabold
                                       text-[#0B2A55]">

                                Aucun enseignant enregistré

                            </h3>


                            <p class="mt-2 text-gray-500">

                                Commencez par ajouter votre premier enseignant.

                            </p>


                            <a
                                href="{{ route('teachers.create') }}"

                                class="mt-7 inline-flex
                                       items-center
                                       justify-center
                                       gap-2
                                       rounded-xl
                                       bg-[#0B2A55]
                                       px-6 py-3.5
                                       text-sm font-bold
                                       text-white
                                       shadow-lg
                                       border-b-4
                                       border-[#C89B3C]
                                       hover:bg-[#061A33]
                                       hover:-translate-y-0.5
                                       transition">

                                +

                                Ajouter le premier enseignant

                            </a>

                        </div>

                    @endif

                </div>

            </div>


            <!-- ================= FOOTER ================= -->

            <div class="mt-6 text-center">

                <p class="text-xs text-gray-400">

                    Académie El Tafawok • Gestion des enseignants

                </p>

            </div>


        </div>

    </div>

</x-app-layout>