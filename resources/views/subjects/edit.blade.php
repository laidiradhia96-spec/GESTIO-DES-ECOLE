<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Modifier une matière
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Modifier les informations de la matière
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


                    <form method="POST"
                          action="{{ route('subjects.update', $subject) }}">

                        @csrf
                        @method('PUT')


                        {{-- Nom + Code --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div>

                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nom de la matière *
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    value="{{ old('name', $subject->name) }}"
                                    required
                                    class="w-full rounded-lg border-gray-300
                                           focus:border-blue-500 focus:ring-blue-500"
                                >

                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            <div>

                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Code *
                                </label>

                                <input
                                    type="text"
                                    name="code"
                                    value="{{ old('code', $subject->code) }}"
                                    required
                                    class="w-full rounded-lg border-gray-300
                                           focus:border-blue-500 focus:ring-blue-500"
                                >

                                @error('code')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>


                        {{-- Cycles --}}
                        <div class="mt-6">

                            <label class="block text-sm font-bold
                                          text-[#0B2A55] mb-2">

                                Cycles concernés *

                            </label>

                            <p class="text-sm text-gray-500 mb-4">
                                Sélectionnez un ou plusieurs cycles.
                            </p>


                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">


                                {{-- Primaire --}}
                                <label
                                    class="flex items-center gap-3
                                           p-4 rounded-xl
                                           border-2 border-gray-200
                                           bg-gray-50
                                           cursor-pointer
                                           hover:border-[#C89B3C]
                                           hover:bg-[#C89B3C]/5
                                           transition"
                                >

                                    <input
                                        type="checkbox"
                                        name="primaire"
                                        value="1"

                                        {{ old(
                                            'primaire',
                                            $subject->primaire
                                        ) ? 'checked' : '' }}

                                        class="rounded border-gray-300
                                               text-[#C89B3C]
                                               focus:ring-[#C89B3C]"
                                    >

                                    <div>

                                        <div class="font-bold text-[#0B2A55]">
                                            Primaire
                                        </div>

                                        <div class="text-xs text-gray-500">
                                            1AP → 5AP
                                        </div>

                                    </div>

                                </label>


                                {{-- Moyen --}}
                                <label
                                    class="flex items-center gap-3
                                           p-4 rounded-xl
                                           border-2 border-gray-200
                                           bg-gray-50
                                           cursor-pointer
                                           hover:border-[#C89B3C]
                                           hover:bg-[#C89B3C]/5
                                           transition"
                                >

                                    <input
                                        type="checkbox"
                                        name="moyen"
                                        value="1"

                                        {{ old(
                                            'moyen',
                                            $subject->moyen
                                        ) ? 'checked' : '' }}

                                        class="rounded border-gray-300
                                               text-[#C89B3C]
                                               focus:ring-[#C89B3C]"
                                    >

                                    <div>

                                        <div class="font-bold text-[#0B2A55]">
                                            Moyen
                                        </div>

                                        <div class="text-xs text-gray-500">
                                            1AM → 4AM
                                        </div>

                                    </div>

                                </label>


                                {{-- Lycée --}}
                                <label
                                    class="flex items-center gap-3
                                           p-4 rounded-xl
                                           border-2 border-gray-200
                                           bg-gray-50
                                           cursor-pointer
                                           hover:border-[#C89B3C]
                                           hover:bg-[#C89B3C]/5
                                           transition"
                                >

                                    <input
                                        type="checkbox"
                                        name="lycee"
                                        value="1"

                                        {{ old(
                                            'lycee',
                                            $subject->lycee
                                        ) ? 'checked' : '' }}

                                        class="rounded border-gray-300
                                               text-[#C89B3C]
                                               focus:ring-[#C89B3C]"
                                    >

                                    <div>

                                        <div class="font-bold text-[#0B2A55]">
                                            Lycée
                                        </div>

                                        <div class="text-xs text-gray-500">
                                            1AS → 3AS
                                        </div>

                                    </div>

                                </label>

                            </div>


                            @error('cycle')
                                <p class="mt-2 text-sm text-red-600">
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
                                value="{{ old(
                                    'hours_per_week',
                                    $subject->hours_per_week
                                ) }}"
                                min="1"
                                max="40"
                                required
                                class="w-full rounded-lg border-gray-300
                                       focus:border-blue-500 focus:ring-blue-500"
                            >

                            @error('hours_per_week')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Description --}}
                        <div class="mt-6">

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>

                            <textarea
                                name="description"
                                rows="4"
                                class="w-full rounded-lg border-gray-300
                                       focus:border-blue-500 focus:ring-blue-500"
                            >{{ old(
                                'description',
                                $subject->description
                            ) }}</textarea>

                        </div>


                        {{-- Active --}}
                        <div class="mt-6">

                            <label class="inline-flex items-center">

                                <input
                                    type="checkbox"
                                    name="active"
                                    value="1"

                                    {{ old(
                                        'active',
                                        $subject->active
                                    ) ? 'checked' : '' }}

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
                                class="px-5 py-2.5 bg-[#0B2A55]
                                       text-white rounded-lg
                                       hover:bg-[#091f3f] transition"
                            >
                                Enregistrer les modifications
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>