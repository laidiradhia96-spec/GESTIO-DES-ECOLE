<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Enseignants — {{ $subject->name }}
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Sélectionnez les enseignants qui enseignent cette matière.
                </p>

            </div>

            <a
                href="{{ route('subjects.index') }}"
                class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg"
            >
                ← Retour aux matières
            </a>

        </div>

    </x-slot>


    <div class="py-10 bg-gray-100 dark:bg-gray-900 min-h-screen">

        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">


                {{-- Informations matière --}}

                <div class="mb-8 p-5 bg-blue-50 rounded-xl border border-blue-100">

                    <div class="flex items-center justify-between">

                        <div>

                            <h1 class="text-2xl font-bold text-gray-800">
                                {{ $subject->name }}
                            </h1>

                            <p class="text-gray-500 mt-1">
                                Code : {{ $subject->code }}
                            </p>

                        </div>

                        <div class="text-right">

                            <p class="text-sm text-gray-500">
                                Niveau
                            </p>

                            <p class="font-semibold text-gray-800">
                                {{ $subject->level }}
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Message de succès --}}

                @if(session('success'))

                    <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-700">

                        {{ session('success') }}

                    </div>

                @endif


                {{-- Erreurs --}}

                @if ($errors->any())

                    <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-700">

                        <div class="font-semibold mb-2">
                            Veuillez corriger les erreurs suivantes :
                        </div>

                        <ul class="list-disc list-inside text-sm">

                            @foreach ($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                @endif


                {{-- Formulaire --}}

                <form
                    method="POST"
                    action="{{ route('subjects.teachers.update', $subject) }}"
                >

                    @csrf

                    @method('PUT')


                    <div class="mb-6">

                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">
                            Enseignants disponibles
                        </h2>

                        <p class="text-sm text-gray-500 mb-5">
                            Cochez les enseignants qui peuvent enseigner cette matière.
                        </p>


                        @if($teachers->count() > 0)

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                @foreach($teachers as $teacher)

                                    @php

                                        $isSelected = $subject->teachers
                                            ->contains('id', $teacher->id);

                                    @endphp


                                    <label
                                        class="flex items-center gap-4 p-4 border rounded-xl cursor-pointer
                                               hover:bg-blue-50 hover:border-blue-300 transition
                                               {{ $isSelected ? 'bg-blue-50 border-blue-400' : 'bg-white border-gray-200' }}"
                                    >

                                        <input
                                            type="checkbox"
                                            name="teachers[]"
                                            value="{{ $teacher->id }}"
                                            {{ $isSelected ? 'checked' : '' }}
                                            class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        >


                                        <div class="flex-1">

                                            <div class="font-semibold text-gray-800">

                                                {{ $teacher->first_name }}
                                                {{ $teacher->last_name }}

                                            </div>


                                            @if($teacher->subjects->isNotEmpty())

                                                <div class="text-sm text-gray-500 mt-1">

                                                    {{ $teacher->subjects->pluck('name')->join(', ') }}

                                                </div>

                                            @endif


                                            @if($teacher->email)

                                                <div class="text-xs text-gray-400 mt-1">

                                                    {{ $teacher->email }}

                                                </div>

                                            @endif

                                        </div>


                                        @if($isSelected)

                                            <span
                                                class="px-2.5 py-1 text-xs font-medium
                                                       bg-green-100 text-green-700 rounded-full"
                                            >
                                                Assigné
                                            </span>

                                        @endif

                                    </label>

                                @endforeach

                            </div>

                        @else

                            <div class="text-center py-10 bg-gray-50 rounded-xl">

                                <div class="text-4xl mb-3">
                                    👨‍🏫
                                </div>

                                <h3 class="font-semibold text-gray-700">
                                    Aucun enseignant disponible
                                </h3>

                                <p class="text-sm text-gray-500 mt-2">
                                    Ajoutez d'abord un enseignant.
                                </p>

                                <a
                                    href="{{ route('teachers.create') }}"
                                    class="inline-block mt-4 px-5 py-2 bg-blue-600
                                           hover:bg-blue-700 text-white rounded-lg"
                                >
                                    + Ajouter un enseignant
                                </a>

                            </div>

                        @endif

                    </div>


                    {{-- Boutons --}}

                    <div class="flex justify-end gap-3 pt-5 border-t">

                        <a
                            href="{{ route('subjects.index') }}"
                            class="px-5 py-2.5 bg-gray-500 hover:bg-gray-600
                                   text-white rounded-lg font-semibold"
                        >
                            Annuler
                        </a>


                        <button
                            type="submit"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700
                                   text-white rounded-lg font-semibold"
                        >
                            💾 Enregistrer
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>