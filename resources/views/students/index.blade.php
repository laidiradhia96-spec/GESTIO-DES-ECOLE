<x-app-layout>

    <!-- ================= HEADER ================= -->

    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center
                    justify-between gap-4">

            <div>

                <div class="flex items-center gap-3">

                    <div class="w-11 h-11 rounded-xl
                                bg-[#0B2A55]
                                flex items-center justify-center
                                shadow-md">

                        <span class="text-xl">🎓</span>

                    </div>

                    <div>

                        <h2 class="font-extrabold text-2xl
                                   text-[#0B2A55]
                                   dark:text-white">

                            Élèves

                        </h2>

                        <p class="text-sm text-gray-500 mt-1">

                            Gestion et suivi des élèves de l'académie.

                        </p>

                    </div>

                </div>

            </div>


            <!-- Ajouter -->
{{-- Ajouter un élève --}}
<a
    href="{{ route('students.create') }}"

    class="group inline-flex items-center
           justify-center gap-2
           px-5 py-3
           rounded-xl
           bg-[#0B2A55]
           hover:bg-[#061A33]
           text-white
           font-bold
           shadow-lg
           border-b-4
           border-[#C89B3C]
           transition-all duration-200
           hover:-translate-y-0.5">

    <span class="text-xl
                 group-hover:rotate-90
                 transition-transform duration-300">

        +

    </span>

    Ajouter un élève
</a>


{{-- Comptes élèves --}}
<a
    href="{{ route('students.accounts') }}"

    class="group inline-flex items-center
           justify-center gap-2
           px-5 py-3
           rounded-xl
           bg-[#0B2A55]
           hover:bg-[#061A33]
           text-white
           font-bold
           shadow-lg
           border-b-4
           border-[#C89B3C]
           transition-all duration-200
           hover:-translate-y-0.5">

    <span class="text-xl
                 group-hover:scale-110
                 transition-transform duration-300">

        👤

    </span>

    Comptes élèves
</a>
            

        </div>
</a>
            
    </x-slot>


    <!-- ================= MAIN ================= -->

    <div class="py-8
                bg-gradient-to-br
                from-gray-50
                via-white
                to-blue-50
                dark:from-gray-900
                dark:to-gray-800
                min-h-screen">


        <div class="max-w-7xl mx-auto
                    px-4 sm:px-6 lg:px-8">


            <!-- ================= SUCCESS ================= -->

            @if(session('success'))

                <div
                    class="mb-6
                           flex items-center gap-3
                           bg-green-50
                           border border-green-200
                           text-green-700
                           px-5 py-4
                           rounded-2xl
                           shadow-sm
                           animate-[fadeIn_0.4s_ease-out]">

                    <div class="w-9 h-9 rounded-full
                                bg-green-100
                                flex items-center justify-center">

                        ✓

                    </div>

                    <span class="font-medium">

                        {{ session('success') }}

                    </span>

                </div>

            @endif


            <!-- ================= ERREURS ================= -->

            @if($errors->any())

                <div
                    class="mb-6
                           flex items-start gap-3
                           bg-red-50
                           border border-red-200
                           text-red-700
                           px-5 py-4
                           rounded-2xl
                           shadow-sm">

                    <div class="w-9 h-9 rounded-full
                                bg-red-100
                                flex items-center justify-center">

                        ⚠️

                    </div>

                    <div class="font-medium">

                        @foreach($errors->all() as $error)

                            <p class="{{ !$loop->first ? 'mt-1' : '' }}">

                                {{ $error }}

                            </p>

                        @endforeach

                    </div>

                </div>

            @endif


            <!-- ================= TOP INTRO ================= -->

            <div class="relative overflow-hidden
                        bg-gradient-to-r
                        from-[#061A33]
                        via-[#0B2A55]
                        to-[#163E73]
                        rounded-3xl
                        shadow-xl
                        p-6 md:p-8
                        mb-7">


                <div class="absolute -right-16 -top-20
                            w-56 h-56
                            rounded-full
                            bg-[#C89B3C]/10">
                </div>


                <div class="absolute -right-20 -bottom-24
                            w-64 h-64
                            rounded-full
                            border border-[#C89B3C]/20">
                </div>


                <div class="relative z-10">

                    <p class="text-[#C89B3C]
                              text-sm
                              font-bold
                              tracking-wider">

                        ACADÉMIE EL TAFAWOK

                    </p>

                    <h1 class="text-2xl md:text-3xl
                               text-white
                               font-extrabold
                               mt-1">

                        Liste des élèves 🎓

                    </h1>

                    <p class="text-blue-100
                              text-sm md:text-base
                              mt-2">

                        Recherchez, consultez et gérez
                        facilement les élèves inscrits.

                    </p>

                </div>

            </div>


            <!-- ================= SEARCH CARD ================= -->

            <div class="bg-white
                        dark:bg-gray-800
                        rounded-3xl
                        border border-gray-100
                        dark:border-gray-700
                        shadow-sm
                        p-6 md:p-7
                        mb-7">


                <div class="flex items-center gap-3 mb-5">

                    <div class="w-10 h-10
                                rounded-xl
                                bg-[#C89B3C]/15
                                flex items-center
                                justify-center">

                        🔎

                    </div>

                    <div>

                        <h3 class="text-lg
                                   font-extrabold
                                   text-[#0B2A55]
                                   dark:text-white">

                            Rechercher un élève

                        </h3>

                        <p class="text-sm text-gray-500">

                            Utilisez le nom, prénom, téléphone ou niveau.

                        </p>

                    </div>

                </div>


                <form
                    method="GET"
                    action="{{ route('students.index') }}">

                    <div class="grid grid-cols-1
                                md:grid-cols-3
                                gap-4">


                        <!-- Recherche -->

                        <div>

                            <label
                                class="block text-sm
                                       font-bold
                                       text-[#0B2A55]
                                       dark:text-gray-200
                                       mb-2">

                                Recherche

                            </label>


                            <div class="relative">

                                <span
                                    class="absolute left-4 top-1/2
                                           -translate-y-1/2
                                           text-gray-400">

                                    🔎

                                </span>


                                <input
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Nom, prénom, téléphone..."

                                    class="w-full
                                           pl-11 pr-4 py-3
                                           rounded-xl
                                           border-2
                                           border-gray-200
                                           bg-gray-50
                                           outline-none
                                           transition

                                           focus:border-[#C89B3C]
                                           focus:ring-4
                                           focus:ring-[#C89B3C]/10"
                                >

                            </div>

                        </div>


                        <!-- Niveau -->

                        <div>

                            <label
                                class="block text-sm
                                       font-bold
                                       text-[#0B2A55]
                                       dark:text-gray-200
                                       mb-2">

                                Niveau

                            </label>


                            <select
                                name="level"

                                class="w-full
                                       px-4 py-3
                                       rounded-xl
                                       border-2
                                       border-gray-200
                                       bg-gray-50
                                       outline-none
                                       transition

                                       focus:border-[#C89B3C]
                                       focus:ring-4
                                       focus:ring-[#C89B3C]/10">

                                <option value="">
                                    Tous les niveaux
                                </option>

                                <option value="1AM"
                                    {{ request('level') == '1AM' ? 'selected' : '' }}>
                                    1AM
                                </option>

                                <option value="2AM"
                                    {{ request('level') == '2AM' ? 'selected' : '' }}>
                                    2AM
                                </option>

                                <option value="3AM"
                                    {{ request('level') == '3AM' ? 'selected' : '' }}>
                                    3AM
                                </option>

                                <option value="4AM"
                                    {{ request('level') == '4AM' ? 'selected' : '' }}>
                                    4AM
                                </option>

                                <option value="1AS"
                                    {{ request('level') == '1AS' ? 'selected' : '' }}>
                                    1AS
                                </option>

                                <option value="2AS"
                                    {{ request('level') == '2AS' ? 'selected' : '' }}>
                                    2AS
                                </option>

                                <option value="3AS"
                                    {{ request('level') == '3AS' ? 'selected' : '' }}>
                                    3AS
                                </option>

                            </select>

                        </div>


                        <!-- Buttons -->

                        <div class="flex items-end gap-3">

                            <button
                                type="submit"

                                class="flex-1
                                       py-3
                                       rounded-xl
                                       bg-[#0B2A55]
                                       hover:bg-[#061A33]
                                       text-white
                                       font-bold
                                       shadow-md
                                       border-b-4
                                       border-[#C89B3C]
                                       transition
                                       hover:-translate-y-0.5">

                                🔎 Rechercher

                            </button>


                            <a
                                href="{{ route('students.index') }}"

                                class="px-5 py-3
                                       rounded-xl
                                       bg-gray-100
                                       hover:bg-gray-200
                                       text-gray-600
                                       font-semibold
                                       transition">

                                Réinitialiser

                            </a>

                        </div>

                    </div>

                </form>

            </div>


            <!-- ================= STUDENTS TABLE ================= -->

            <div class="bg-white
                        dark:bg-gray-800
                        rounded-3xl
                        border border-gray-100
                        dark:border-gray-700
                        shadow-sm
                        overflow-hidden">


                <!-- Table Header -->

                <div class="px-6 py-5
                            border-b
                            border-gray-100
                            dark:border-gray-700
                            flex flex-col sm:flex-row
                            sm:items-center
                            justify-between gap-3">

                    <div>

                        <h3 class="text-xl
                                   font-extrabold
                                   text-[#0B2A55]
                                   dark:text-white">

                            Liste des élèves

                        </h3>

                        <p class="text-sm text-gray-500 mt-1">

                            Élèves enregistrés dans l'académie.

                        </p>

                    </div>


                    @if($students->count() > 0)

                        <div class="px-4 py-2
                                    rounded-xl
                                    bg-[#C89B3C]/10
                                    text-[#0B2A55]
                                    font-bold
                                    text-sm">

                            {{ $students->count() }} résultat(s)

                        </div>

                    @endif

                </div>


                @if($students->count() > 0)

                    <!-- ================= TABLE ================= -->

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead>

                                <tr class="bg-[#0B2A55] text-white">

                                    <th class="px-5 py-4 text-left
                                               text-sm font-bold">
                                        #
                                    </th>

                                    <th class="px-5 py-4 text-left
                                               text-sm font-bold">
                                        Nom
                                    </th>

                                    <th class="px-5 py-4 text-left
                                               text-sm font-bold">
                                        Prénom
                                    </th>

                                    <th class="px-5 py-4 text-left
                                               text-sm font-bold">
                                        Niveau
                                    </th>

                                    <th class="px-5 py-4 text-left
                                               text-sm font-bold">
                                        Téléphone
                                    </th>

                                    <th class="px-5 py-4 text-left
                                               text-sm font-bold">
                                        Parent
                                    </th>

                                    <th class="px-5 py-4 text-left
                                               text-sm font-bold">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y
                                         divide-gray-100
                                         dark:divide-gray-700">


                                @foreach($students as $student)

                                    <tr
                                        class="group
                                               hover:bg-blue-50/50
                                               dark:hover:bg-gray-700/50
                                               transition duration-200">


                                        <!-- ID -->

                                        <td class="px-5 py-4">

                                            <span
                                                class="inline-flex
                                                       items-center
                                                       justify-center
                                                       w-9 h-9
                                                       rounded-lg
                                                       bg-gray-100
                                                       text-gray-600
                                                       font-bold
                                                       text-sm">

                                                {{ $student->id }}

                                            </span>

                                        </td>


                                        <!-- NOM -->

                                        <td class="px-5 py-4">

                                            <span
                                                class="font-bold
                                                       text-[#0B2A55]
                                                       dark:text-white">

                                                {{ $student->last_name }}

                                            </span>

                                        </td>


                                        <!-- PRÉNOM -->

                                        <td class="px-5 py-4">

                                            <span class="text-gray-700
                                                         dark:text-gray-200">

                                                {{ $student->first_name }}

                                            </span>

                                        </td>


                                        <!-- NIVEAU -->

                                        <td class="px-5 py-4">

                                            <span
                                                class="inline-flex
                                                       px-3 py-1
                                                       rounded-full
                                                       bg-[#C89B3C]/15
                                                       text-[#0B2A55]
                                                       font-bold
                                                       text-sm">

                                                {{ $student->level }}

                                            </span>

                                        </td>


                                        <!-- TÉLÉPHONE -->

                                        <td class="px-5 py-4">

                                            <span class="text-gray-600
                                                         dark:text-gray-300">

                                                {{ $student->phone ?? '-' }}

                                            </span>

                                        </td>


                                        <!-- PARENT -->

                                        <td class="px-5 py-4">

                                            <span class="text-gray-600
                                                         dark:text-gray-300">

                                                {{ $student->parent_name ?? '-' }}

                                            </span>

                                        </td>


                                        <!-- ACTIONS -->

                                        <td class="px-5 py-4">

                                            <div class="flex items-center gap-2">


                                                <!-- Voir -->

                                                <a
                                                    href="{{ route('students.show', $student) }}"

                                                    class="inline-flex
                                                           items-center
                                                           justify-center
                                                           w-9 h-9
                                                           rounded-lg
                                                           bg-blue-50
                                                           text-blue-700
                                                           hover:bg-[#0B2A55]
                                                           hover:text-white
                                                           transition
                                                           hover:scale-105"

                                                    title="Voir">

                                                    👁️

                                                </a>


                                                <!-- Modifier -->

                                                <a
                                                    href="{{ route('students.edit', $student) }}"

                                                    class="inline-flex
                                                           items-center
                                                           justify-center
                                                           w-9 h-9
                                                           rounded-lg
                                                           bg-amber-50
                                                           text-amber-700
                                                           hover:bg-[#C89B3C]
                                                           hover:text-white
                                                           transition
                                                           hover:scale-105"

                                                    title="Modifier">

                                                    ✏️

                                                </a>

                                                <!-- Compte élève -->
<a
    href="{{ route('students.account.create', $student) }}"
    class="inline-flex
           items-center
           justify-center
           w-9 h-9
           rounded-lg
           bg-green-50
           text-green-700
           hover:bg-green-600
           hover:text-white
           transition
           hover:scale-105"
    title="Créer un compte élève">

    👤

</a>

                                                <!-- Supprimer -->

                                                <form
                                                    action="{{ route('students.destroy', $student) }}"
                                                    method="POST"
                                                    class="inline">

                                                    @csrf

                                                    @method('DELETE')


                                                    <button
                                                        type="submit"

                                                        class="inline-flex
                                                               items-center
                                                               justify-center
                                                               w-9 h-9
                                                               rounded-lg
                                                               bg-red-50
                                                               text-red-600
                                                               hover:bg-red-600
                                                               hover:text-white
                                                               transition
                                                               hover:scale-105"

                                                        title="Supprimer"

                                                        onclick="return confirm('Voulez-vous supprimer cet élève ?')">

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


                    <!-- ================= PAGINATION ================= -->

                    <div class="px-6 py-5
                                border-t
                                border-gray-100
                                dark:border-gray-700">

                        {{ $students->links() }}

                    </div>


                @else

                    <!-- ================= EMPTY STATE ================= -->

                    <div class="text-center py-16 px-6">

                        <div class="w-20 h-20
                                    mx-auto
                                    rounded-3xl
                                    bg-[#0B2A55]/10
                                    flex items-center
                                    justify-center
                                    text-4xl
                                    mb-5">

                            🎓

                        </div>


                        <h3 class="text-xl
                                   font-extrabold
                                   text-[#0B2A55]
                                   dark:text-white">

                            Aucun élève enregistré.

                        </h3>


                        <p class="text-gray-500 mt-2">

                            Commencez par ajouter votre premier élève.

                        </p>


                        <a
                            href="{{ route('students.create') }}"

                            class="inline-flex
                                   items-center
                                   gap-2
                                   mt-6
                                   px-6 py-3
                                   rounded-xl
                                   bg-[#0B2A55]
                                   hover:bg-[#061A33]
                                   text-white
                                   font-bold
                                   shadow-lg
                                   border-b-4
                                   border-[#C89B3C]
                                   transition
                                   hover:-translate-y-0.5">

                            <span>+</span>

                            Ajouter le premier élève

                        </a>

                    </div>

                @endif

            </div>

        </div>

    </div>


    <!-- ================= ANIMATION ================= -->

    <style>

        @keyframes fadeIn {

            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }

    </style>

</x-app-layout>