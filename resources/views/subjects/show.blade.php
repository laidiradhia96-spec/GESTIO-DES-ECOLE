<x-app-layout>

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <x-slot name="header">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                <div class="flex items-center gap-4">

                    <div class="w-14 h-14 rounded-2xl bg-[#0B2A55]
                                flex items-center justify-center shadow-md">

                        <span class="text-2xl">📚</span>

                    </div>

                    <div>

                        <h2 class="font-extrabold text-2xl text-[#0B2A55]">
                            Détails de la matière
                        </h2>

                        <p class="text-sm text-gray-500 mt-1">
                            Informations sur la matière
                        </p>

                    </div>

                </div>


                <a href="{{ route('subjects.index') }}"
                   class="inline-flex items-center justify-center
                          px-5 py-3
                          rounded-xl
                          bg-gray-100
                          text-[#0B2A55]
                          font-bold
                          hover:bg-gray-200
                          transition">

                    ← Retour aux matières

                </a>

            </div>

        </div>

    </x-slot>


    {{-- =========================================================
        MAIN
    ========================================================== --}}
    <div class="py-8 bg-gray-50 min-h-screen">

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- =================================================
                CARD PRINCIPALE
            ================================================== --}}
            <div class="bg-white rounded-3xl shadow-sm
                        border border-gray-100 overflow-hidden">


                {{-- HEADER CARD --}}
                <div class="relative overflow-hidden
                            bg-gradient-to-r
                            from-[#0B2A55]
                            to-[#173E73]">

                    <div class="absolute -right-16 -top-20
                                w-64 h-64
                                rounded-full
                                bg-white/10">
                    </div>

                    <div class="relative p-8 md:p-10">

                        <div class="flex flex-col md:flex-row
                                    md:items-center
                                    md:justify-between gap-6">


                            <div>

                                <span class="inline-flex
                                             px-4 py-2
                                             rounded-full
                                             bg-white/10
                                             text-[#C89B3C]
                                             text-xs
                                             font-extrabold
                                             uppercase
                                             tracking-wide">

                                    Matière

                                </span>


                                <h1 class="mt-4
                                           text-3xl md:text-4xl
                                           font-extrabold
                                           text-white">

                                    {{ $subject->name }}

                                </h1>


                                @if($subject->code)

                                    <p class="mt-2
                                              text-blue-100">

                                        Code :
                                        <span class="font-bold text-white">
                                            {{ $subject->code }}
                                        </span>

                                    </p>

                                @endif

                            </div>


                            {{-- Statut --}}
                            <div>

                                @if($subject->active)

                                    <span class="inline-flex
                                                 items-center gap-2
                                                 px-5 py-3
                                                 rounded-2xl
                                                 bg-green-500/20
                                                 border border-green-300/30
                                                 text-green-100
                                                 font-bold">

                                        <span>●</span>
                                        Matière active

                                    </span>

                                @else

                                    <span class="inline-flex
                                                 items-center gap-2
                                                 px-5 py-3
                                                 rounded-2xl
                                                 bg-red-500/20
                                                 border border-red-300/30
                                                 text-red-100
                                                 font-bold">

                                        <span>●</span>
                                        Matière inactive

                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                    INFORMATIONS
                ================================================== --}}
                <div class="p-6 md:p-8">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                        {{-- Niveau --}}
                        <div class="p-6 rounded-2xl
                                    bg-blue-50
                                    border border-blue-100">

                            <div class="flex items-center gap-4">

                                <div class="w-12 h-12
                                            rounded-2xl
                                            bg-[#0B2A55]
                                            text-white
                                            flex items-center
                                            justify-center
                                            text-xl">

                                    🎓

                                </div>

                                <div>

                                    <p class="text-sm text-gray-500">
                                        Niveau scolaire
                                    </p>

                                    <p class="mt-1
                                              text-lg
                                              font-extrabold
                                              text-[#0B2A55]">

                                        {{ $subject->level }}

                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- Code --}}
                        <div class="p-6 rounded-2xl
                                    bg-yellow-50
                                    border border-yellow-100">

                            <div class="flex items-center gap-4">

                                <div class="w-12 h-12
                                            rounded-2xl
                                            bg-[#C89B3C]
                                            text-white
                                            flex items-center
                                            justify-center
                                            text-xl">

                                    🔖

                                </div>

                                <div>

                                    <p class="text-sm text-gray-500">
                                        Code matière
                                    </p>

                                    <p class="mt-1
                                              text-lg
                                              font-extrabold
                                              text-[#0B2A55]">

                                        {{ $subject->code ?? 'Non défini' }}

                                    </p>

                                </div>

                            </div>

                        </div>


                    </div>


                    {{-- =================================================
                        ACTIONS
                    ================================================== --}}
                    <div class="mt-8
                                pt-6
                                border-t border-gray-100">

                        <div class="flex flex-col sm:flex-row gap-4">


                            {{-- Modifier --}}
                            <a href="{{ route('subjects.edit', $subject) }}"
                               class="flex-1
                                      inline-flex
                                      items-center
                                      justify-center
                                      gap-2
                                      px-6 py-4
                                      rounded-2xl
                                      bg-[#0B2A55]
                                      text-white
                                      font-bold
                                      hover:bg-[#061A33]
                                      transition
                                      shadow-sm">

                                ✏️
                                Modifier la matière

                            </a>


                            {{-- Retour --}}
                            <a href="{{ route('subjects.index') }}"
                               class="flex-1
                                      inline-flex
                                      items-center
                                      justify-center
                                      gap-2
                                      px-6 py-4
                                      rounded-2xl
                                      bg-gray-100
                                      text-[#0B2A55]
                                      font-bold
                                      hover:bg-gray-200
                                      transition">

                                📚
                                Liste des matières

                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>