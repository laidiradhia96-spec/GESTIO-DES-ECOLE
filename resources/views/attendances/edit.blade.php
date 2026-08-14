<x-app-layout>

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <x-slot name="header">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row
                        sm:items-center sm:justify-between gap-4">

                <div>

                    <h2 class="font-extrabold text-2xl text-[#0B2A55]">
                        Modifier une séance
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Modifier les informations et les présences de la séance.
                    </p>

                </div>

                <a href="{{ route('attendances.show', $attendance) }}"
                   class="inline-flex items-center justify-center
                          gap-2 px-5 py-3 rounded-xl
                          bg-[#0B2A55] text-white
                          font-bold shadow-md
                          border-b-4 border-[#C89B3C]
                          hover:bg-[#061A33]
                          transition">

                    ← Retour aux détails

                </a>

            </div>

        </div>

    </x-slot>


    {{-- =========================================================
        MAIN
    ========================================================== --}}
    <div class="py-8 bg-gray-50 min-h-screen">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- =================================================
                BANNER
            ================================================== --}}
            <div class="relative overflow-hidden
                        bg-gradient-to-r from-[#0B2A55] to-[#173E73]
                        rounded-3xl
                        shadow-xl
                        mb-8">

                <div class="absolute -right-16 -top-20
                            w-64 h-64
                            rounded-full
                            bg-white/10">
                </div>

                <div class="absolute right-24 -bottom-28
                            w-72 h-72
                            rounded-full
                            border border-[#C89B3C]/30">
                </div>

                <div class="relative p-8 md:p-10">

                    <p class="text-[#C89B3C]
                              font-bold
                              tracking-wide
                              uppercase
                              text-sm">

                        Académie El Tafawok

                    </p>

                    <div class="flex items-center gap-3 mt-2">

                        <h1 class="text-3xl md:text-4xl
                                   font-extrabold
                                   text-white">

                            Modifier une séance

                        </h1>

                        <span class="text-3xl">
                            ✏️
                        </span>

                    </div>

                    <p class="mt-3
                              text-blue-100
                              text-base md:text-lg">

                        Modifiez les informations de la séance
                        et le statut de chaque élève.

                    </p>

                </div>

            </div>


            {{-- =================================================
                ERREURS DE VALIDATION
            ================================================== --}}
            @if ($errors->any())

                <div class="mb-6
                            rounded-2xl
                            bg-red-50
                            border border-red-200
                            p-5">

                    <div class="flex items-start gap-3">

                        <span class="text-xl">
                            ⚠️
                        </span>

                        <div>

                            <h3 class="font-extrabold text-red-800">
                                Vérifiez les informations
                            </h3>

                            <ul class="mt-2
                                       text-sm
                                       text-red-700
                                       list-disc
                                       list-inside">

                                @foreach ($errors->all() as $error)

                                    <li>
                                        {{ $error }}
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =================================================
                FORMULAIRE
            ================================================== --}}
            <div class="bg-white
                        rounded-3xl
                        border border-gray-100
                        shadow-sm
                        overflow-hidden">


                {{-- Header formulaire --}}
                <div class="p-6
                            border-b border-gray-100">

                    <div class="flex items-center gap-3">

                        <div class="w-12 h-12
                                    rounded-2xl
                                    bg-[#0B2A55]
                                    flex items-center
                                    justify-center
                                    text-2xl">

                            📋

                        </div>

                        <div>

                            <h3 class="text-lg
                                       font-extrabold
                                       text-[#0B2A55]">

                                Informations de la séance

                            </h3>

                            <p class="text-sm text-gray-500">

                                Modifiez les informations générales
                                de la séance.

                            </p>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                    FORM
                ================================================== --}}
                <form method="POST"
                      action="{{ route('attendances.update', $attendance) }}">

                    @csrf

                    @method('PUT')


                    <div class="p-6 md:p-8">


                        {{-- =================================================
                            INFORMATIONS SÉANCE
                        ================================================== --}}
                        <div class="grid grid-cols-1
                                    md:grid-cols-3
                                    gap-6
                                    mb-10">


                            {{-- Date --}}
                            <div>

                                <label for="date"
                                       class="block
                                              text-sm
                                              font-bold
                                              text-gray-700
                                              mb-2">

                                    Date de la séance

                                </label>

                                <input
                                    type="date"
                                    id="date"
                                    name="date"
                                    value="{{ old('date', \Carbon\Carbon::parse($attendance->date)->format('Y-m-d')) }}"
                                    required
                                    class="w-full
                                           rounded-xl
                                           border-gray-200
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]"
                                >

                                @error('date')

                                    <p class="text-sm text-red-600 mt-2">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>


                            {{-- Matière --}}
                            <div>

                                <label for="subject_id"
                                       class="block
                                              text-sm
                                              font-bold
                                              text-gray-700
                                              mb-2">

                                    Matière

                                </label>

                                <select
                                    id="subject_id"
                                    name="subject_id"
                                    required
                                    class="w-full
                                           rounded-xl
                                           border-gray-200
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]"
                                >

                                    @foreach($subjects as $subject)

                                        <option
                                            value="{{ $subject->id }}"
                                            {{ old('subject_id', $attendance->subject_id) == $subject->id ? 'selected' : '' }}>

                                            {{ $subject->name }}

                                        </option>

                                    @endforeach

                                </select>

                                @error('subject_id')

                                    <p class="text-sm text-red-600 mt-2">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>


                            {{-- Enseignant --}}
                            <div>

                                <label for="teacher_id"
                                       class="block
                                              text-sm
                                              font-bold
                                              text-gray-700
                                              mb-2">

                                    Enseignant

                                </label>

                                <select
                                    id="teacher_id"
                                    name="teacher_id"
                                    class="w-full
                                           rounded-xl
                                           border-gray-200
                                           focus:border-[#0B2A55]
                                           focus:ring-[#0B2A55]"
                                >

                                    <option value="">
                                        Aucun enseignant
                                    </option>

                                    @foreach($teachers as $teacher)

                                        <option
                                            value="{{ $teacher->id }}"
                                            {{ old('teacher_id', $attendance->teacher_id) == $teacher->id ? 'selected' : '' }}>

                                            {{ $teacher->first_name }}
                                            {{ $teacher->last_name }}

                                        </option>

                                    @endforeach

                                </select>

                                @error('teacher_id')

                                    <p class="text-sm text-red-600 mt-2">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                        </div>


                        {{-- =================================================
                            LISTE DES ÉLÈVES
                        ================================================== --}}
                        <div>

                            <div class="flex flex-col sm:flex-row
                                        sm:items-center
                                        sm:justify-between
                                        gap-3
                                        mb-5">

                                <div>

                                    <div class="flex items-center gap-2">

                                        <span class="text-xl">
                                            👨‍🎓
                                        </span>

                                        <h3 class="font-extrabold
                                                   text-xl
                                                   text-[#0B2A55]">

                                            Présences des élèves

                                        </h3>

                                    </div>

                                    <p class="text-sm text-gray-500 mt-1">

                                        Modifiez le statut et la remarque
                                        de chaque élève.

                                    </p>

                                </div>

                                <div class="px-4 py-2
                                            rounded-xl
                                            bg-blue-50
                                            text-[#0B2A55]
                                            font-bold">

                                    {{ $attendances->count() }} élève(s)

                                </div>

                            </div>


                            {{-- =================================================
                                TABLE
                            ================================================== --}}
                            <div class="overflow-x-auto
                                        rounded-2xl
                                        border border-gray-200">

                                <table class="w-full text-sm">

                                    <thead class="bg-[#0B2A55] text-white">

                                        <tr>

                                            <th class="px-5 py-4 text-left">
                                                #
                                            </th>

                                            <th class="px-5 py-4 text-left">
                                                Élève
                                            </th>

                                            <th class="px-5 py-4 text-left">
                                                Statut
                                            </th>

                                            <th class="px-5 py-4 text-left">
                                                Note / Observation
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody class="divide-y divide-gray-100">

                                        @foreach($attendances as $index => $record)

                                            <tr class="hover:bg-gray-50 transition">

                                                {{-- Numéro --}}
                                                <td class="px-5 py-4
                                                           font-bold
                                                           text-gray-500">

                                                    {{ $index + 1 }}

                                                </td>


                                                {{-- Élève --}}
                                                <td class="px-5 py-4">

                                                    <div class="flex items-center gap-3">

                                                        <div class="w-10 h-10
                                                                    rounded-xl
                                                                    bg-blue-50
                                                                    flex items-center
                                                                    justify-center">

                                                            👨‍🎓

                                                        </div>

                                                        <div>

                                                            <p class="font-extrabold
                                                                      text-[#0B2A55]">

                                                                {{ $record->student->first_name }}
                                                                {{ $record->student->last_name }}

                                                            </p>

                                                        </div>

                                                    </div>


                                                    {{-- ID élève --}}
                                                    <input
                                                        type="hidden"
                                                        name="students[{{ $index }}][id]"
                                                        value="{{ $record->student_id }}"
                                                    >

                                                </td>


                                                {{-- Statut --}}
                                                <td class="px-5 py-4">

                                                    <select
                                                        name="students[{{ $index }}][status]"
                                                        required
                                                        class="w-full
                                                               min-w-[160px]
                                                               rounded-xl
                                                               border-gray-200
                                                               focus:border-[#0B2A55]
                                                               focus:ring-[#0B2A55]"
                                                    >

                                                        <option
                                                            value="present"
                                                            {{ old("students.$index.status", $record->status) == 'present' ? 'selected' : '' }}>

                                                            🟢 Présent

                                                        </option>

                                                        <option
                                                            value="absent"
                                                            {{ old("students.$index.status", $record->status) == 'absent' ? 'selected' : '' }}>

                                                            🔴 Absent

                                                        </option>

                                                        <option
                                                            value="late"
                                                            {{ old("students.$index.status", $record->status) == 'late' ? 'selected' : '' }}>

                                                            🟠 Retard

                                                        </option>

                                                        <option
                                                            value="justified"
                                                            {{ old("students.$index.status", $record->status) == 'justified' ? 'selected' : '' }}>

                                                            🔵 Justifié

                                                        </option>

                                                    </select>

                                                    @error("students.$index.status")

                                                        <p class="text-xs text-red-600 mt-1">
                                                            {{ $message }}
                                                        </p>

                                                    @enderror

                                                </td>


                                                {{-- Note --}}
                                                <td class="px-5 py-4">

                                                    <textarea
                                                        name="students[{{ $index }}][note]"
                                                        rows="2"
                                                        placeholder="Ajouter une remarque..."
                                                        class="w-full
                                                               min-w-[220px]
                                                               rounded-xl
                                                               border-gray-200
                                                               focus:border-[#0B2A55]
                                                               focus:ring-[#0B2A55]"
                                                    >{{ old("students.$index.note", $record->note) }}</textarea>

                                                    @error("students.$index.note")

                                                        <p class="text-xs text-red-600 mt-1">
                                                            {{ $message }}
                                                        </p>

                                                    @enderror

                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        </div>


                        {{-- =================================================
                            ACTIONS
                        ================================================== --}}
                        <div class="flex flex-col sm:flex-row
                                    sm:justify-end
                                    gap-3
                                    pt-8
                                    mt-8
                                    border-t border-gray-100">


                            <a href="{{ route('attendances.show', $attendance) }}"
                               class="inline-flex
                                      items-center
                                      justify-center
                                      px-6 py-3
                                      rounded-xl
                                      bg-gray-100
                                      text-gray-700
                                      font-bold
                                      hover:bg-gray-200
                                      transition">

                                Annuler

                            </a>


                            <button
                                type="submit"
                                class="inline-flex
                                       items-center
                                       justify-center
                                       gap-2
                                       px-7 py-3
                                       rounded-xl
                                       bg-[#0B2A55]
                                       text-white
                                       font-bold
                                       shadow-md
                                       border-b-4
                                       border-[#C89B3C]
                                       hover:bg-[#061A33]
                                       transition">

                                💾 Enregistrer les modifications

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>