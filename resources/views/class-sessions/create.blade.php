<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Ajouter une séance
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Ajouter une nouvelle séance à l'emploi du temps
                </p>
            </div>

            <a
                href="{{ route('class-sessions.index') }}"
                class="inline-flex items-center justify-center gap-2
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
                ← Retour aux séances
            </a>

        </div>

    </x-slot>


    <div class="py-10 bg-gray-100 dark:bg-gray-900 min-h-screen">

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Erreurs --}}
            @if ($errors->any())

                <div class="mb-6 rounded-2xl
                            bg-red-50 dark:bg-red-900/20
                            border border-red-200 dark:border-red-800
                            px-6 py-5">

                    <div class="font-bold text-red-700 dark:text-red-300 mb-2">
                        Veuillez corriger les erreurs suivantes :
                    </div>

                    <ul class="list-disc list-inside text-sm
                               text-red-600 dark:text-red-300">

                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- Carte principale --}}
            <div class="bg-white dark:bg-gray-800
                        rounded-2xl shadow-sm overflow-hidden">

                {{-- En-tête --}}
                <div class="px-6 py-6
                            border-b border-gray-200 dark:border-gray-700">

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12 rounded-xl
                                    bg-[#0B2A55]
                                    flex items-center justify-center
                                    border-b-4 border-[#C89B3C]">

                            <span class="text-2xl text-white">
                                📅
                            </span>

                        </div>

                        <div>

                            <h3 class="text-lg font-bold
                                       text-gray-800 dark:text-gray-100">

                                Informations de la séance

                            </h3>

                            <p class="text-sm text-gray-500 dark:text-gray-400">

                                Remplissez les informations de la nouvelle séance

                            </p>

                        </div>

                    </div>

                </div>


                {{-- Formulaire --}}
                <form
                    method="POST"
                    action="{{ route('class-sessions.store') }}"
                    class="p-6"
                >

                    @csrf


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                        {{-- Élève --}}
                        <div>

                            <label
                                for="student_id"
                                class="block text-sm font-semibold
                                       text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Élève
                            </label>

                            <select
                                name="student_id"
                                id="student_id"
                                required
                                class="w-full rounded-xl
                                       border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#C89B3C]"
                            >

                                <option value="">
                                    Sélectionner un élève
                                </option>

                                @foreach($students as $student)

                                    <option
                                        value="{{ $student->id }}"
                                        {{ old('student_id') == $student->id ? 'selected' : '' }}
                                    >
                                        {{ $student->first_name }}
                                        {{ $student->last_name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Matière --}}
                        <div>

                            <label
                                for="subject_id"
                                class="block text-sm font-semibold
                                       text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Matière
                            </label>

                            <select
                                name="subject_id"
                                id="subject_id"
                                required
                                class="w-full rounded-xl
                                       border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#C89B3C]"
                            >

                                <option value="">
                                    Sélectionner une matière
                                </option>

                                @foreach($subjects as $subject)

                                    <option
                                        value="{{ $subject->id }}"
                                        {{ old('subject_id') == $subject->id ? 'selected' : '' }}
                                    >
                                        {{ $subject->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Enseignant --}}
                        <div>

                            <label
                                for="teacher_id"
                                class="block text-sm font-semibold
                                       text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Enseignant
                            </label>

                            <select
                                name="teacher_id"
                                id="teacher_id"
                                required
                                class="w-full rounded-xl
                                       border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#C89B3C]"
                            >

                                <option value="">
                                    Sélectionner un enseignant
                                </option>

                                @foreach($teachers as $teacher)

                                    <option
                                        value="{{ $teacher->id }}"
                                        {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}
                                    >
                                        {{ $teacher->first_name }}
                                        {{ $teacher->last_name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Jour --}}
                        <div>

                            <label
                                for="day"
                                class="block text-sm font-semibold
                                       text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Jour
                            </label>

                            <select
                                name="day"
                                id="day"
                                required
                                class="w-full rounded-xl
                                       border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#C89B3C]"
                            >

                                <option value="">
                                    Sélectionner un jour
                                </option>

                                @foreach([
                                    'Lundi',
                                    'Mardi',
                                    'Mercredi',
                                    'Jeudi',
                                    'Vendredi',
                                    'Samedi',
                                    'Dimanche'
                                ] as $day)

                                    <option
                                        value="{{ $day }}"
                                        {{ old('day') == $day ? 'selected' : '' }}
                                    >
                                        {{ $day }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Heure début --}}
                        <div>

                            <label
                                for="start_time"
                                class="block text-sm font-semibold
                                       text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Heure de début
                            </label>

                            <input
                                type="time"
                                name="start_time"
                                id="start_time"
                                value="{{ old('start_time') }}"
                                required
                                class="w-full rounded-xl
                                       border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#C89B3C]"
                            >

                        </div>


                        {{-- Heure fin --}}
                        <div>

                            <label
                                for="end_time"
                                class="block text-sm font-semibold
                                       text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Heure de fin
                            </label>

                            <input
                                type="time"
                                name="end_time"
                                id="end_time"
                                value="{{ old('end_time') }}"
                                required
                                class="w-full rounded-xl
                                       border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#C89B3C]"
                            >

                        </div>


                        {{-- Date début --}}
                        <div>

                            <label
                                for="start_date"
                                class="block text-sm font-semibold
                                       text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Date de début
                            </label>

                            <input
                                type="date"
                                name="start_date"
                                id="start_date"
                                value="{{ old('start_date') }}"
                                required
                                class="w-full rounded-xl
                                       border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#C89B3C]"
                            >

                        </div>


                        {{-- Date fin --}}
                        <div>

                            <label
                                for="end_date"
                                class="block text-sm font-semibold
                                       text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Date de fin
                            </label>

                            <input
                                type="date"
                                name="end_date"
                                id="end_date"
                                value="{{ old('end_date') }}"
                                class="w-full rounded-xl
                                       border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#C89B3C]"
                            >

                        </div>


                        {{-- Statut --}}
                        <div>

                            <label
                                for="status"
                                class="block text-sm font-semibold
                                       text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Statut
                            </label>

                            <select
                                name="status"
                                id="status"
                                required
                                class="w-full rounded-xl
                                       border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#C89B3C]"
                            >

                                <option value="active"
                                    {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                    Active
                                </option>

                                <option value="inactive"
                                    {{ old('status') === 'inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>

                            </select>

                        </div>


                        {{-- Note --}}
                        <div class="md:col-span-2">

                            <label
                                for="note"
                                class="block text-sm font-semibold
                                       text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Note
                            </label>

                            <textarea
                                name="note"
                                id="note"
                                rows="4"
                                placeholder="Ajouter une remarque concernant cette séance..."
                                class="w-full rounded-xl
                                       border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-gray-200
                                       focus:border-[#0B2A55]
                                       focus:ring-[#C89B3C]"
                            >{{ old('note') }}</textarea>

                        </div>

                    </div>


                    {{-- Boutons --}}
                    <div class="flex flex-col sm:flex-row
                                justify-end gap-3
                                mt-8 pt-6
                                border-t border-gray-200 dark:border-gray-700">

                        <a
                            href="{{ route('class-sessions.index') }}"
                            class="inline-flex items-center justify-center
                                   px-5 py-3
                                   rounded-xl
                                   bg-gray-100 dark:bg-gray-700
                                   border border-gray-300 dark:border-gray-600
                                   text-gray-700 dark:text-gray-200
                                   font-semibold
                                   hover:bg-gray-200 dark:hover:bg-gray-600
                                   transition"
                        >
                            Annuler
                        </a>


                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2
                                   px-6 py-3
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

                            <span class="text-xl">
                                +
                            </span>

                            Ajouter la séance

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>