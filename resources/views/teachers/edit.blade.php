<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">

            <div>
                <h2 class="text-2xl font-extrabold text-[#0B2A55] dark:text-white">
                    Modifier un enseignant
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Modifiez les informations de l'enseignant.
                </p>
            </div>

            <a href="{{ route('teachers.show', $teacher) }}"
               class="hidden sm:inline-flex items-center gap-2
                      px-5 py-2.5 rounded-xl
                      bg-gray-100 hover:bg-gray-200
                      text-gray-700 font-semibold transition">

                ← Retour

            </a>

        </div>
    </x-slot>


    <div class="min-h-screen py-10
                bg-gradient-to-br from-gray-50 via-white to-blue-50
                dark:from-gray-900 dark:to-gray-800">

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800
                        rounded-3xl
                        shadow-xl
                        border border-gray-100
                        dark:border-gray-700
                        overflow-hidden">


                <!-- Header -->

                <div class="relative
                            bg-gradient-to-r
                            from-[#061A33]
                            via-[#0B2A55]
                            to-[#163E73]
                            px-7 py-8">

                    <div class="absolute -right-16 -top-20
                                w-52 h-52 rounded-full
                                bg-[#C89B3C]/10">
                    </div>

                    <div class="relative flex items-center gap-5">

                        <div class="w-16 h-16 rounded-2xl
                                    bg-white/10
                                    border border-white/10
                                    flex items-center justify-center
                                    text-3xl">

                            ✏️

                        </div>

                        <div>

                            <p class="text-xs font-bold
                                      tracking-[0.2em]
                                      text-[#C89B3C]">

                                ACADÉMIE EL TAFAWOK

                            </p>

                            <h1 class="text-2xl font-extrabold
                                       text-white mt-1">

                                {{ $teacher->first_name }}
                                {{ $teacher->last_name }}

                            </h1>

                            <p class="text-blue-100 text-sm mt-1">

                                {{ $teacher->subjects->pluck('name')->join(', ') ?: $teacher->speciality }}

                            </p>

                        </div>

                    </div>

                </div>


                <!-- Form -->

                <form method="POST"
                      action="{{ route('teachers.update', $teacher) }}"
                      class="p-7 md:p-9">

                    @csrf
                    @method('PUT')


                    <!-- Erreurs -->

                    @if ($errors->any())

                        <div class="mb-7 p-5 rounded-2xl
                                    bg-red-50
                                    border border-red-200
                                    text-red-700">

                            <p class="font-bold mb-2">
                                Veuillez corriger les erreurs suivantes :
                            </p>

                            <ul class="list-disc list-inside text-sm space-y-1">

                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach

                            </ul>

                        </div>

                    @endif


                    <!-- Informations personnelles -->

                    <div class="mb-9">

                        <div class="flex items-center gap-3 mb-5">

                            <div class="w-11 h-11 rounded-xl
                                        bg-[#0B2A55]/10
                                        flex items-center justify-center">

                                👤

                            </div>

                            <div>

                                <h2 class="text-lg font-extrabold
                                           text-[#0B2A55]
                                           dark:text-white">

                                    Informations personnelles

                                </h2>

                                <p class="text-xs text-gray-500">
                                    Identité et spécialité
                                </p>

                            </div>

                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                            <!-- Prénom -->

                            <div>

                                <label class="block text-sm font-bold
                                              text-gray-700
                                              dark:text-gray-300 mb-2">

                                    Prénom *

                                </label>

                                <input
                                    type="text"
                                    name="first_name"
                                    value="{{ old('first_name', $teacher->first_name) }}"
                                    required
                                    class="w-full rounded-xl
                                           border-gray-300
                                           px-4 py-3
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]"
                                >

                            </div>


                            <!-- Nom -->

                            <div>

                                <label class="block text-sm font-bold
                                              text-gray-700
                                              dark:text-gray-300 mb-2">

                                    Nom *

                                </label>

                                <input
                                    type="text"
                                    name="last_name"
                                    value="{{ old('last_name', $teacher->last_name) }}"
                                    required
                                    class="w-full rounded-xl
                                           border-gray-300
                                           px-4 py-3
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]"
                                >

                            </div>


                            <!-- Matières enseignées -->

                            <div class="md:col-span-2">

                                <label class="block text-sm font-bold
                                              text-gray-700
                                              dark:text-gray-300 mb-2">

                                    Matières enseignées

                                </label>

                                <div class="grid grid-cols-1
                                            sm:grid-cols-2
                                            lg:grid-cols-3
                                            gap-3">

                                    @php

                                        $selectedSubjects = old(
                                            'subjects',
                                            $teacher->subjects->pluck('id')->toArray()
                                        );

                                    @endphp

                                    @foreach($subjects as $subject)

                                        <label class="relative cursor-pointer">

                                            <input
                                                type="checkbox"
                                                name="subjects[]"
                                                value="{{ $subject->id }}"
                                                class="peer sr-only"
                                                {{ in_array($subject->id, $selectedSubjects) ? 'checked' : '' }}
                                            >

                                            <div class="p-4 rounded-2xl
                                                        border-2 border-gray-200
                                                        bg-gray-50
                                                        peer-checked:border-[#0B2A55]
                                                        peer-checked:bg-blue-50
                                                        transition">

                                                <div class="flex items-center gap-3">

                                                    <div class="w-10 h-10 rounded-xl
                                                                bg-white
                                                                flex items-center justify-center
                                                                shadow-sm">

                                                        📚

                                                    </div>

                                                    <div>

                                                        <div class="font-extrabold
                                                                    text-[#0B2A55]">

                                                            {{ $subject->name }}

                                                        </div>

                                                        @if($subject->code)

                                                            <div class="text-xs text-gray-500 mt-1">

                                                                {{ $subject->code }}

                                                            </div>

                                                        @endif

                                                    </div>

                                                </div>

                                            </div>

                                        </label>

                                    @endforeach

                                </div>

                                @error('subjects')

                                    <p class="mt-3 text-sm text-red-600 font-semibold">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>


                            <!-- Date recrutement -->

                            <div>

                                <label class="block text-sm font-bold
                                              text-gray-700
                                              dark:text-gray-300 mb-2">

                                    Date de recrutement

                                </label>

                                <input
                                    type="date"
                                    name="hire_date"
                                    value="{{ old('hire_date', $teacher->hire_date) }}"
                                    class="w-full rounded-xl
                                           border-gray-300
                                           px-4 py-3
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]"
                                >

                            </div>

                        </div>

                    </div>


                    <!-- Contact -->

                    <div class="mb-9">

                        <div class="flex items-center gap-3 mb-5">

                            <div class="w-11 h-11 rounded-xl
                                        bg-[#C89B3C]/10
                                        flex items-center justify-center">

                                📞

                            </div>

                            <div>

                                <h2 class="text-lg font-extrabold
                                           text-[#0B2A55]
                                           dark:text-white">

                                    Informations de contact

                                </h2>

                                <p class="text-xs text-gray-500">
                                    Coordonnées de l'enseignant
                                </p>

                            </div>

                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                            <!-- Téléphone -->

                            <div>

                                <label class="block text-sm font-bold
                                              text-gray-700
                                              dark:text-gray-300 mb-2">

                                    Téléphone

                                </label>

                                <input
                                    type="text"
                                    name="phone"
                                    value="{{ old('phone', $teacher->phone) }}"
                                    placeholder="Ex : 0550 00 00 00"
                                    class="w-full rounded-xl
                                           border-gray-300
                                           px-4 py-3
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]"
                                >

                            </div>


                            <!-- Email -->

                            <div>

                                <label class="block text-sm font-bold
                                              text-gray-700
                                              dark:text-gray-300 mb-2">

                                    Email

                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('email', $teacher->email) }}"
                                    placeholder="enseignant@email.com"
                                    class="w-full rounded-xl
                                           border-gray-300
                                           px-4 py-3
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]"
                                >

                            </div>


                            <!-- Adresse -->

                            <div class="md:col-span-2">

                                <label class="block text-sm font-bold
                                              text-gray-700
                                              dark:text-gray-300 mb-2">

                                    Adresse

                                </label>

                                <textarea
                                    name="address"
                                    rows="3"
                                    placeholder="Adresse de l'enseignant..."
                                    class="w-full rounded-xl
                                           border-gray-300
                                           px-4 py-3
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]"
                                >{{ old('address', $teacher->address) }}</textarea>

                            </div>

                        </div>

                    </div>

<!-- ================= NIVEAUX ================= -->

<div class="bg-white dark:bg-gray-800
            rounded-3xl
            shadow-sm
            border border-gray-100
            dark:border-gray-700
            p-6 md:p-8 mb-6">

    <div class="flex items-center gap-4 mb-7">

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

                Sélectionnez les niveaux dans lesquels cet enseignant peut enseigner.

            </p>

        </div>

    </div>


    @php
        $selectedLevels = old(
            'levels',
            $teacher->levels->pluck('id')->toArray()
        );
    @endphp


    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        @foreach($levels as $level)

            <label class="relative cursor-pointer">

                <input
                    type="checkbox"
                    name="levels[]"
                    value="{{ $level->id }}"
                    class="peer sr-only"

                    {{ in_array($level->id, $selectedLevels) ? 'checked' : '' }}
                >

                <div class="p-5 rounded-2xl
                            border-2 border-gray-200
                            bg-gray-50
                            peer-checked:border-[#0B2A55]
                            peer-checked:bg-blue-50
                            transition">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10 rounded-xl
                                    bg-white
                                    flex items-center
                                    justify-center
                                    shadow-sm">

                            🎓

                        </div>

                        <div>

                            <div class="font-extrabold
                                        text-[#0B2A55]">

                                {{ $level->name }}

                            </div>

                            <div class="text-xs text-gray-500 mt-1">

                                {{ $level->code }}

                            </div>

                        </div>

                    </div>

                    <div class="mt-3 text-xs text-gray-500">

                        {{ $level->description }}

                    </div>

                </div>

            </label>

        @endforeach

    </div>

</div>
                    <!-- Statut -->

                    <div class="mb-9">

                        <div class="rounded-2xl
                                    bg-gray-50
                                    border border-gray-100
                                    p-5">

                            <label class="flex items-center gap-4 cursor-pointer">

                                <input
                                    type="checkbox"
                                    name="active"
                                    value="1"
                                    {{ old('active', $teacher->active) ? 'checked' : '' }}
                                    class="w-5 h-5 rounded
                                           border-gray-300
                                           text-[#0B2A55]
                                           focus:ring-[#0B2A55]"
                                >

                                <div>

                                    <p class="font-bold text-gray-800">
                                        Enseignant actif
                                    </p>

                                    <p class="text-sm text-gray-500">
                                        Cet enseignant est actuellement actif dans l'académie.
                                    </p>

                                </div>

                            </label>

                        </div>

                    </div>


                    <!-- Buttons -->

                    <div class="flex flex-col-reverse sm:flex-row
                                items-stretch sm:items-center
                                justify-between gap-3
                                pt-6
                                border-t border-gray-200">


                        <a
                            href="{{ route('teachers.show', $teacher) }}"
                            class="inline-flex items-center
                                   justify-center gap-2
                                   px-6 py-3.5
                                   rounded-xl
                                   bg-gray-100
                                   hover:bg-gray-200
                                   text-gray-700
                                   font-bold
                                   transition">

                            ← Annuler

                        </a>


                        <button
                            type="submit"
                            class="inline-flex items-center
                                   justify-center gap-2
                                   px-7 py-3.5
                                   rounded-xl
                                   bg-[#0B2A55]
                                   hover:bg-[#061A33]
                                   text-white
                                   font-bold
                                   shadow-lg
                                   border-b-4
                                   border-[#C89B3C]
                                   hover:-translate-y-0.5
                                   transition">

                            💾 Enregistrer les modifications

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>