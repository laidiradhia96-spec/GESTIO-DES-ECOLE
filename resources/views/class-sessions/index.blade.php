<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            {{-- Titre --}}
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Gestion des séances
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Organiser et gérer les séances des élèves
                </p>
            </div>


            {{-- Bouton Ajouter --}}
            <a
                href="{{ route('class-sessions.create') }}"
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
                       hover:-translate-y-0.5"
            >

                <span
                    class="text-xl
                           group-hover:rotate-90
                           transition-transform duration-300"
                >
                    +
                </span>

                Ajouter une séance

            </a>

        </div>

    </x-slot>


    <div class="py-10 bg-gray-100 dark:bg-gray-900 min-h-screen">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- Message succès --}}
            @if(session('success'))

                <div
                    class="mb-6 rounded-xl
                           bg-green-50 dark:bg-green-900/20
                           border border-green-200 dark:border-green-800
                           px-5 py-4
                           text-green-700 dark:text-green-300"
                >
                    {{ session('success') }}
                </div>

            @endif


            {{-- Message erreur --}}
            @if(session('error'))

                <div
                    class="mb-6 rounded-xl
                           bg-red-50 dark:bg-red-900/20
                           border border-red-200 dark:border-red-800
                           px-5 py-4
                           text-red-700 dark:text-red-300"
                >
                    {{ session('error') }}
                </div>

            @endif


            {{-- Carte principale --}}
            <div
                class="bg-white dark:bg-gray-800
                       rounded-2xl
                       shadow-sm
                       overflow-hidden"
            >


                {{-- En-tête --}}
                <div
                    class="px-6 py-6
                           border-b
                           border-gray-200
                           dark:border-gray-700"
                >

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">


                        {{-- Informations --}}
                        <div class="flex items-center gap-4">

                            <div
                                class="w-12 h-12
                                       rounded-xl
                                       bg-[#0B2A55]
                                       flex items-center
                                       justify-center
                                       shadow-sm"
                            >

                                <span class="text-xl text-white">
                                    📅
                                </span>

                            </div>


                            <div>

                                <h3
                                    class="text-lg font-bold
                                           text-gray-800
                                           dark:text-gray-100"
                                >
                                    Liste des séances
                                </h3>

                                <p
                                    class="text-sm
                                           text-gray-500
                                           dark:text-gray-400"
                                >
                                    {{ $sessions->total() }} séance(s)
                                </p>

                            </div>

                        </div>


                        {{-- Petit indicateur --}}
                        <div
                            class="hidden sm:flex items-center gap-2
                                   px-4 py-2
                                   rounded-xl
                                   bg-gray-50
                                   dark:bg-gray-700/50
                                   border
                                   border-gray-200
                                   dark:border-gray-600"
                        >

                            <span
                                class="w-2.5 h-2.5
                                       rounded-full
                                       bg-[#C89B3C]"
                            ></span>

                            <span
                                class="text-sm font-semibold
                                       text-gray-600
                                       dark:text-gray-300"
                            >
                                Planning des élèves
                            </span>

                        </div>

                    </div>

                </div>


                {{-- Tableau --}}
                <div class="overflow-x-auto">

                    <table
                        class="min-w-full
                               divide-y
                               divide-gray-200
                               dark:divide-gray-700"
                    >

                        <thead class="bg-gray-50 dark:bg-gray-700/40">

                            <tr>

                                <th
                                    class="px-6 py-4
                                           text-left
                                           text-xs
                                           font-semibold
                                           text-gray-500
                                           dark:text-gray-300
                                           uppercase
                                           tracking-wider"
                                >
                                    Élève
                                </th>


                                <th
                                    class="px-6 py-4
                                           text-left
                                           text-xs
                                           font-semibold
                                           text-gray-500
                                           dark:text-gray-300
                                           uppercase
                                           tracking-wider"
                                >
                                    Matière
                                </th>


                                <th
                                    class="px-6 py-4
                                           text-left
                                           text-xs
                                           font-semibold
                                           text-gray-500
                                           dark:text-gray-300
                                           uppercase
                                           tracking-wider"
                                >
                                    Enseignant
                                </th>


                                <th
                                    class="px-6 py-4
                                           text-left
                                           text-xs
                                           font-semibold
                                           text-gray-500
                                           dark:text-gray-300
                                           uppercase
                                           tracking-wider"
                                >
                                    Jour
                                </th>


                                <th
                                    class="px-6 py-4
                                           text-left
                                           text-xs
                                           font-semibold
                                           text-gray-500
                                           dark:text-gray-300
                                           uppercase
                                           tracking-wider"
                                >
                                    Horaire
                                </th>


                                <th
                                    class="px-6 py-4
                                           text-left
                                           text-xs
                                           font-semibold
                                           text-gray-500
                                           dark:text-gray-300
                                           uppercase
                                           tracking-wider"
                                >
                                    Période
                                </th>


                                <th
                                    class="px-6 py-4
                                           text-center
                                           text-xs
                                           font-semibold
                                           text-gray-500
                                           dark:text-gray-300
                                           uppercase
                                           tracking-wider"
                                >
                                    Statut
                                </th>


                                <th
                                    class="px-6 py-4
                                           text-right
                                           text-xs
                                           font-semibold
                                           text-gray-500
                                           dark:text-gray-300
                                           uppercase
                                           tracking-wider"
                                >
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody
                            class="bg-white
                                   dark:bg-gray-800
                                   divide-y
                                   divide-gray-200
                                   dark:divide-gray-700"
                        >


                            @forelse($sessions as $session)

                                <tr
                                    class="hover:bg-gray-50
                                           dark:hover:bg-gray-700/30
                                           transition"
                                >


                                    {{-- Élève --}}
                                    <td class="px-6 py-5 whitespace-nowrap">

                                        @if($session->student)

                                            <div class="flex items-center gap-3">

                                                <div
                                                    class="w-10 h-10
                                                           rounded-full
                                                           bg-[#0B2A55]
                                                           flex items-center
                                                           justify-center"
                                                >

                                                    <span
                                                        class="text-sm
                                                               font-bold
                                                               text-white"
                                                    >
                                                        {{ strtoupper(
                                                            substr(
                                                                $session->student->first_name,
                                                                0,
                                                                1
                                                            )
                                                        ) }}
                                                    </span>

                                                </div>


                                                <div>

                                                    <p
                                                        class="font-semibold
                                                               text-gray-800
                                                               dark:text-gray-100"
                                                    >
                                                        {{ $session->student->first_name }}
                                                        {{ $session->student->last_name }}
                                                    </p>

                                                    <p
                                                        class="text-xs
                                                               text-gray-500
                                                               dark:text-gray-400"
                                                    >
                                                        Élève #{{ $session->student->id }}
                                                    </p>

                                                </div>

                                            </div>

                                        @else

                                            <span class="text-gray-400">
                                                Élève supprimé
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Matière --}}
                                    <td class="px-6 py-5 whitespace-nowrap">

                                        @if($session->subject)

                                            <span
                                                class="inline-flex
                                                       items-center
                                                       px-3 py-1
                                                       rounded-full
                                                       text-xs
                                                       font-semibold
                                                       bg-[#0B2A55]/10
                                                       text-[#0B2A55]
                                                       dark:bg-blue-900/30
                                                       dark:text-blue-300"
                                            >
                                                {{ $session->subject->name }}
                                            </span>

                                        @else

                                            <span class="text-gray-400">
                                                Matière supprimée
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Enseignant --}}
                                    <td class="px-6 py-5 whitespace-nowrap">

                                        @if($session->teacher)

                                            <p
                                                class="text-sm
                                                       font-semibold
                                                       text-gray-800
                                                       dark:text-gray-100"
                                            >
                                                {{ $session->teacher->first_name }}
                                                {{ $session->teacher->last_name }}
                                            </p>

                                        @else

                                            <span class="text-gray-400">
                                                Enseignant supprimé
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Jour --}}
                                    <td class="px-6 py-5 whitespace-nowrap">

                                        <span
                                            class="inline-flex
                                                   items-center
                                                   px-3 py-1.5
                                                   rounded-lg
                                                   bg-gray-100
                                                   dark:bg-gray-700
                                                   text-sm
                                                   font-semibold
                                                   text-gray-700
                                                   dark:text-gray-200"
                                        >
                                            {{ $session->day }}
                                        </span>

                                    </td>


                                    {{-- Horaire --}}
                                    <td class="px-6 py-5 whitespace-nowrap">

                                        <div
                                            class="flex items-center gap-2
                                                   text-sm
                                                   font-semibold
                                                   text-gray-700
                                                   dark:text-gray-200"
                                        >

                                            <span>
                                                🕐
                                            </span>

                                            {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}

                                            <span class="text-gray-400">
                                                →
                                            </span>

                                            {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}

                                        </div>

                                    </td>


                                    {{-- Période --}}
                                    <td class="px-6 py-5 whitespace-nowrap">

                                        <div
                                            class="text-sm
                                                   text-gray-700
                                                   dark:text-gray-200"
                                        >

                                            <div class="font-medium">

                                                {{ $session->start_date?->format('d/m/Y') }}

                                            </div>


                                            @if($session->end_date)

                                                <div
                                                    class="text-xs
                                                           text-gray-400
                                                           mt-1"
                                                >
                                                    → {{ $session->end_date->format('d/m/Y') }}
                                                </div>

                                            @else

                                                <div
                                                    class="text-xs
                                                           text-gray-400
                                                           mt-1"
                                                >
                                                    Sans date de fin
                                                </div>

                                            @endif

                                        </div>

                                    </td>


                                    {{-- Statut --}}
                                    <td
                                        class="px-6 py-5
                                               text-center
                                               whitespace-nowrap"
                                    >

                                        @if($session->status === 'active')

                                            <span
                                                class="inline-flex
                                                       items-center
                                                       gap-1.5
                                                       px-3 py-1.5
                                                       rounded-full
                                                       text-xs
                                                       font-semibold
                                                       bg-green-100
                                                       dark:bg-green-900/30
                                                       text-green-700
                                                       dark:text-green-300"
                                            >

                                                <span
                                                    class="w-2 h-2
                                                           rounded-full
                                                           bg-green-500"
                                                ></span>

                                                Active

                                            </span>

                                        @else

                                            <span
                                                class="inline-flex
                                                       items-center
                                                       gap-1.5
                                                       px-3 py-1.5
                                                       rounded-full
                                                       text-xs
                                                       font-semibold
                                                       bg-gray-100
                                                       dark:bg-gray-700
                                                       text-gray-600
                                                       dark:text-gray-300"
                                            >

                                                <span
                                                    class="w-2 h-2
                                                           rounded-full
                                                           bg-gray-400"
                                                ></span>

                                                Inactive

                                            </span>

                                        @endif

                                    </td>


                                    {{-- Actions --}}
                                    <td
                                        class="px-6 py-5
                                               text-right
                                               whitespace-nowrap"
                                    >

                                        <div
                                            class="flex items-center
                                                   justify-end
                                                   gap-2"
                                        >

                                            {{-- Modifier --}}
                                            <a
                                                href="{{ route(
                                                    'class-sessions.edit',
                                                    $session
                                                ) }}"
                                                class="inline-flex
                                                       items-center
                                                       justify-center
                                                       px-4 py-2
                                                       rounded-xl
                                                       bg-[#0B2A55]
                                                       hover:bg-[#061A33]
                                                       text-white
                                                       text-sm
                                                       font-semibold
                                                       border-b-2
                                                       border-[#C89B3C]
                                                       transition"
                                            >
                                                Modifier
                                            </a>


                                            {{-- Supprimer --}}
                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'class-sessions.destroy',
                                                    $session
                                                ) }}"
                                                onsubmit="return confirm(
                                                    'Voulez-vous vraiment supprimer cette séance ?'
                                                );"
                                            >

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex
                                                           items-center
                                                           justify-center
                                                           px-4 py-2
                                                           rounded-xl
                                                           bg-red-600
                                                           hover:bg-red-700
                                                           text-white
                                                           text-sm
                                                           font-semibold
                                                           transition"
                                                >
                                                    Supprimer
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>


                            @empty

                                {{-- Aucun résultat --}}
                                <tr>

                                    <td
                                        colspan="8"
                                        class="px-6 py-16 text-center"
                                    >

                                        <div
                                            class="flex flex-col
                                                   items-center
                                                   justify-center"
                                        >

                                            <div
                                                class="w-16 h-16
                                                       rounded-2xl
                                                       bg-[#0B2A55]/10
                                                       dark:bg-gray-700
                                                       flex items-center
                                                       justify-center
                                                       mb-4"
                                            >

                                                <span class="text-2xl">
                                                    📅
                                                </span>

                                            </div>


                                            <h3
                                                class="text-lg
                                                       font-semibold
                                                       text-gray-700
                                                       dark:text-gray-200"
                                            >
                                                Aucune séance
                                            </h3>


                                            <p
                                                class="text-sm
                                                       text-gray-500
                                                       dark:text-gray-400
                                                       mt-1"
                                            >
                                                Aucune séance n'a été programmée pour le moment.
                                            </p>


                                            <a
                                                href="{{ route('class-sessions.create') }}"
                                                class="group
                                                       mt-5
                                                       inline-flex
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

                                                <span
                                                    class="text-xl
                                                           group-hover:rotate-90
                                                           transition-transform
                                                           duration-300"
                                                >
                                                    +
                                                </span>

                                                Ajouter une séance

                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            @endforelse


                        </tbody>

                    </table>

                </div>


                {{-- Pagination --}}
                @if($sessions->hasPages())

                    <div
                        class="px-6 py-4
                               border-t
                               border-gray-200
                               dark:border-gray-700"
                    >
                        {{ $sessions->links() }}
                    </div>

                @endif


            </div>

        </div>

    </div>

</x-app-layout>