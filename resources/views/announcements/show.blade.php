<x-app-layout>

    {{-- HEADER --}}
    <x-slot name="header">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="text-2xl font-extrabold text-[#0B2A55]">
                    Détails de l'annonce
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Consulter les informations de l'annonce
                </p>

            </div>

            <a href="{{ route('announcements.index') }}"
               class="inline-flex items-center gap-2
                      px-4 py-2
                      rounded-xl
                      bg-gray-100
                      hover:bg-gray-200
                      text-gray-700
                      font-bold
                      transition">

                ← Retour

            </a>

        </div>

    </x-slot>


    {{-- MAIN --}}
    <div class="min-h-screen bg-gray-50 py-10">

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- CARD --}}
            <div class="bg-white
                        rounded-2xl
                        border border-gray-200
                        shadow-sm
                        overflow-hidden">


                {{-- TOP --}}
                <div class="px-6 py-6
                            border-b border-gray-100
                            bg-[#F8FAFC]">

                    <div class="flex items-start gap-4">

                        <div class="w-14 h-14
                                    rounded-2xl
                                    bg-blue-50
                                    flex items-center justify-center
                                    text-3xl">

                            📢

                        </div>

                        <div class="flex-1">

                            <h1 class="text-2xl font-extrabold text-[#0B2A55]">

                                {{ $announcement->title }}

                            </h1>

                            <p class="text-sm text-gray-500 mt-2">

                                Publié le
                                {{ optional($announcement->created_at)->format('d/m/Y à H:i') }}

                            </p>

                        </div>


                        {{-- STATUS --}}
                       @if($announcement->is_active)

                            <span class="inline-flex items-center gap-1.5
                                         px-3 py-1.5
                                         rounded-full
                                         bg-green-50
                                         text-green-700
                                         text-xs
                                         font-extrabold">

                                <span class="w-2 h-2 rounded-full bg-green-500"></span>

                                Active

                            </span>

                        @else

                            <span class="inline-flex items-center gap-1.5
                                         px-3 py-1.5
                                         rounded-full
                                         bg-red-50
                                         text-red-700
                                         text-xs
                                         font-extrabold">

                                <span class="w-2 h-2 rounded-full bg-red-500"></span>

                                Inactive

                            </span>

                        @endif

                    </div>

                </div>


                {{-- CONTENT --}}
                <div class="px-6 py-8">

                    <div class="mb-6">

                        <h3 class="text-sm font-extrabold
                                   uppercase tracking-wide
                                   text-gray-500 mb-3">

                            Contenu

                        </h3>

                        <div class="rounded-xl
                                    bg-gray-50
                                    border border-gray-100
                                    p-5
                                    text-gray-700
                                    leading-7
                                    whitespace-pre-line">

                            {{ $announcement->content ?? $announcement->description ?? '-' }}

                        </div>

                    </div>


                    {{-- INFORMATIONS --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                        <div class="rounded-xl
                                    border border-gray-200
                                    p-4">

                            <p class="text-xs
                                      uppercase
                                      tracking-wide
                                      font-bold
                                      text-gray-400">

                                Date de création

                            </p>

                            <p class="font-semibold text-gray-700 mt-1">

                                {{ optional($announcement->created_at)->format('d/m/Y H:i') }}

                            </p>

                        </div>


                        <div class="rounded-xl
                                    border border-gray-200
                                    p-4">

                            <p class="text-xs
                                      uppercase
                                      tracking-wide
                                      font-bold
                                      text-gray-400">

                                Dernière modification

                            </p>

                            <p class="font-semibold text-gray-700 mt-1">

                                {{ optional($announcement->updated_at)->format('d/m/Y H:i') }}

                            </p>

                        </div>

                    </div>

                </div>
{{-- VIEWS --}}
<div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50/50 p-5">

    <div class="flex items-center justify-between gap-4">

        <div class="flex items-center gap-3">

            <div class="w-11 h-11 rounded-xl
                        bg-blue-100
                        flex items-center justify-center
                        text-xl">

                👁️

            </div>

            <div>

                <h3 class="font-extrabold text-[#0B2A55]">
                    Vues de l'annonce
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Nombre de personnes ayant consulté cette annonce.
                </p>

            </div>

        </div>

        <div class="text-3xl font-extrabold text-blue-600">
            {{ $announcement->viewers->count() }}
        </div>

    </div>


    @if($announcement->viewers->count() > 0)

        <div class="mt-5 pt-5 border-t border-blue-100">

            <h4 class="text-sm font-extrabold text-gray-600 mb-3">
                Personnes ayant vu l'annonce
            </h4>

            <div class="space-y-2">

                @foreach($announcement->viewers as $viewer)

                    <div class="flex items-center justify-between
                                bg-white
                                rounded-xl
                                border border-gray-100
                                px-4 py-3">

                        <div class="flex items-center gap-3">

                            <div class="w-9 h-9 rounded-full
                                        bg-[#0B2A55]
                                        text-white
                                        flex items-center justify-center
                                        font-bold">

                                {{ strtoupper(substr($viewer->name, 0, 1)) }}

                            </div>

                            <div>

                                <p class="font-bold text-gray-700">
                                    {{ $viewer->name }}
                                </p>

                                <p class="text-xs text-gray-400">
                                    {{ $viewer->email }}
                                </p>

                            </div>

                        </div>

                        <div class="text-xs text-gray-400">
                            {{ optional($viewer->pivot->seen_at)->format('d/m/Y H:i') }}
                        </div>

                    </div>

                @endforeach

            </div>

        </div>

    @else

        <div class="mt-5 pt-5 border-t border-blue-100">

            <p class="text-sm text-gray-500 text-center py-3">
                👁️ Aucun utilisateur n'a encore consulté cette annonce.
            </p>

        </div>

    @endif

</div>

                {{-- ACTIONS --}}
                <div class="px-6 py-5
                            border-t border-gray-100
                            flex flex-wrap
                            items-center
                            justify-end
                            gap-3">

                    <a href="{{ route('announcements.index') }}"
                       class="px-4 py-2
                              rounded-lg
                              bg-gray-100
                              hover:bg-gray-200
                              text-gray-700
                              font-semibold
                              transition">

                        Retour

                    </a>

                    <a href="{{ route('announcements.edit', $announcement) }}"
                       class="px-4 py-2
                              rounded-lg
                              bg-blue-50
                              hover:bg-blue-100
                              text-blue-700
                              font-semibold
                              transition">

                        ✏️ Modifier

                    </a>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>