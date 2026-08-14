<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ajouter une matière
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Ajouter une nouvelle matière à l'école
            </p>
        </div>
    </x-slot>


    <div class="py-8">

        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6">

                    {{-- Erreurs --}}
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg">

                            <ul class="list-disc list-inside">

                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach

                            </ul>

                        </div>
                    @endif


                    <form method="POST" action="{{ route('subjects.store') }}">

                        @csrf


                        {{-- Nom + Code --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nom de la matière *
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="Ex : Mathématiques"
                                    required
                                    class="w-full rounded-lg border-gray-300
                                           focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>


                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Code *
                                </label>

                                <input
                                    type="text"
                                    name="code"
                                    value="{{ old('code') }}"
                                    placeholder="Ex : MAT"
                                    required
                                    class="w-full rounded-lg border-gray-300
                                           focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>

                        </div>


                        {{-- Niveau --}}
                        <!-- Niveau scolaire -->
<div>

    <label class="block text-sm font-bold
                  text-[#0B2A55]
                  dark:text-gray-200 mb-2">
        Niveau scolaire *
    </label>

    <select
        name="level"
        id="level"
        required
        class="w-full px-4 py-3.5
               rounded-xl
               border-2 border-gray-200
               bg-gray-50
               outline-none
               transition
               focus:border-[#C89B3C]
               focus:ring-4
               focus:ring-[#C89B3C]/10"
    >

        <option value="">
            Sélectionner un niveau
        </option>

        <optgroup label="Primaire">

            <option value="1AP" {{ old('level') == '1AP' ? 'selected' : '' }}>
                1ère année primaire
            </option>

            <option value="2AP" {{ old('level') == '2AP' ? 'selected' : '' }}>
                2ème année primaire
            </option>

            <option value="3AP" {{ old('level') == '3AP' ? 'selected' : '' }}>
                3ème année primaire
            </option>

            <option value="4AP" {{ old('level') == '4AP' ? 'selected' : '' }}>
                4ème année primaire
            </option>

            <option value="5AP" {{ old('level') == '5AP' ? 'selected' : '' }}>
                5ème année primaire
            </option>

        </optgroup>

        <optgroup label="CEM">

            <option value="1AM" {{ old('level') == '1AM' ? 'selected' : '' }}>
                1ère année CEM
            </option>

            <option value="2AM" {{ old('level') == '2AM' ? 'selected' : '' }}>
                2ème année CEM
            </option>

            <option value="3AM" {{ old('level') == '3AM' ? 'selected' : '' }}>
                3ème année CEM
            </option>

            <option value="4AM" {{ old('level') == '4AM' ? 'selected' : '' }}>
                4ème année CEM
            </option>

        </optgroup>

        <optgroup label="Lycée">

            <option value="1AS" {{ old('level') == '1AS' ? 'selected' : '' }}>
                1ère année secondaire
            </option>

            <option value="2AS" {{ old('level') == '2AS' ? 'selected' : '' }}>
                2ème année secondaire
            </option>

            <option value="3AS" {{ old('level') == '3AS' ? 'selected' : '' }}>
                3ème année secondaire
            </option>

        </optgroup>

    </select>

    @error('level')
        <p class="mt-1 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror

</div>


                        {{-- Heures --}}
                        <div class="mt-6">

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Heures par semaine *
                            </label>

                            <input
                                type="number"
                                name="hours_per_week"
                                value="{{ old('hours_per_week', 1) }}"
                                min="1"
                                max="40"
                                required
                                class="w-full rounded-lg border-gray-300
                                       focus:border-blue-500 focus:ring-blue-500"
                            >

                        </div>


                        {{-- Description --}}
                        <div class="mt-6">

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>

                            <textarea
                                name="description"
                                rows="4"
                                placeholder="Description de la matière..."
                                class="w-full rounded-lg border-gray-300
                                       focus:border-blue-500 focus:ring-blue-500"
                            >{{ old('description') }}</textarea>

                        </div>


                        {{-- Active --}}
                        <div class="mt-6">

                            <label class="inline-flex items-center">

                                <input
                                    type="checkbox"
                                    name="active"
                                    value="1"
                                    checked
                                    class="rounded border-gray-300
                                           text-blue-600 shadow-sm
                                           focus:ring-blue-500"
                                >

                                <span class="ms-2 text-sm text-gray-700">
                                    Matière active
                                </span>

                            </label>

                        </div>


                        {{-- Buttons --}}
                        <div class="flex justify-end gap-3 mt-8">

                            <a
                                href="{{ route('subjects.index') }}"
                                class="px-5 py-2.5 bg-gray-100
                                       text-gray-700 rounded-lg
                                       hover:bg-gray-200 transition"
                            >
                                Annuler
                            </a>


                            <button
                                type="submit"
                                class="px-5 py-2.5 bg-blue-600
                                       text-white rounded-lg
                                       hover:bg-blue-700 transition"
                            >
                                Enregistrer
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>