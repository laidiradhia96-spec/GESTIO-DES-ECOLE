<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center gap-4">

            <div class="w-12 h-12 rounded-2xl bg-[#0B2A55]
                        flex items-center justify-center shadow-lg">

                <span class="text-2xl">🎓</span>

            </div>

            <div>

                <h2 class="font-extrabold text-2xl text-[#0B2A55]
                           dark:text-white">

                    Ajouter un élève

                </h2>

                <p class="text-sm text-gray-500 mt-1">

                    Enregistrer un nouvel élève dans l'académie.

                </p>

            </div>

        </div>

    </x-slot>


    <div class="py-10 min-h-screen
                bg-gradient-to-br
                from-gray-50 via-white to-blue-50
                dark:from-gray-900 dark:to-gray-800">

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">


            <!-- ================================================= -->
            <!-- HERO -->
            <!-- ================================================= -->

            <div class="relative overflow-hidden
                        rounded-3xl
                        bg-gradient-to-r
                        from-[#061A33]
                        via-[#0B2A55]
                        to-[#163E73]
                        shadow-xl
                        p-7 md:p-9 mb-7">

                <div class="absolute -right-20 -top-24
                            w-64 h-64 rounded-full
                            bg-[#C89B3C]/10">
                </div>

                <div class="absolute -right-10 -bottom-32
                            w-72 h-72 rounded-full
                            border border-[#C89B3C]/20">
                </div>

                <div class="relative z-10">

                    <div class="flex items-center gap-4">

                        <div class="w-16 h-16 rounded-2xl
                                    bg-white/10
                                    backdrop-blur
                                    flex items-center justify-center
                                    text-3xl
                                    border border-white/10">

                            👨‍🎓

                        </div>

                        <div>

                            <p class="text-[#C89B3C]
                                      text-xs font-extrabold
                                      tracking-[0.2em]">

                                ACADÉMIE EL TAFAWOK

                            </p>

                            <h1 class="text-2xl md:text-3xl
                                       font-extrabold text-white mt-1">

                                Nouvel élève

                            </h1>

                            <p class="text-blue-100 text-sm mt-1">

                                Remplissez les informations ci-dessous.

                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- ERRORS -->
            <!-- ================================================= -->

            @if ($errors->any())

                <div class="mb-6 p-5 rounded-2xl
                            bg-red-50 border border-red-200
                            text-red-700 shadow-sm">

                    <div class="flex items-center gap-3 mb-3">

                        <div class="w-9 h-9 rounded-full
                                    bg-red-100
                                    flex items-center justify-center">

                            ⚠️

                        </div>

                        <div class="font-bold">

                            Veuillez corriger les erreurs suivantes :

                        </div>

                    </div>

                    <ul class="list-disc list-inside text-sm space-y-1">

                        @foreach ($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <!-- ================================================= -->
            <!-- SUCCESS -->
            <!-- ================================================= -->

            @if (session('success'))

                <div class="mb-6 p-5 rounded-2xl
                            bg-green-50 border border-green-200
                            text-green-700 shadow-sm">

                    <div class="flex items-center gap-3">

                        <div class="w-9 h-9 rounded-full
                                    bg-green-100
                                    flex items-center justify-center">

                            ✓

                        </div>

                        <span class="font-semibold">

                            {{ session('success') }}

                        </span>

                    </div>

                </div>

            @endif


            <!-- ================================================= -->
            <!-- FORM -->
            <!-- ================================================= -->

            <form method="POST"
                  action="{{ route('students.store') }}">

                @csrf

                <div class="space-y-6">


                    <!-- ================================================= -->
                    <!-- INFORMATIONS PERSONNELLES -->
                    <!-- ================================================= -->

                    <div class="bg-white dark:bg-gray-800
                                rounded-3xl shadow-sm
                                border border-gray-100
                                dark:border-gray-700
                                overflow-hidden">

                        <div class="px-6 py-5
                                    border-b border-gray-100
                                    dark:border-gray-700">

                            <div class="flex items-center gap-3">

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

                                    <p class="text-sm text-gray-500">

                                        Identité et niveau scolaire.

                                    </p>

                                </div>

                            </div>

                        </div>


                        <div class="p-6 md:p-7">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                                <!-- Prénom -->

                                <div>

                                    <label class="block text-sm font-bold
                                                  text-[#0B2A55]
                                                  dark:text-gray-200 mb-2">

                                        Prénom *

                                    </label>

                                    <input
                                        type="text"
                                        name="first_name"
                                        value="{{ old('first_name') }}"
                                        required
                                        placeholder="Ex : Ahmed"
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

                                    @error('first_name')

                                        <p class="mt-1 text-sm text-red-600">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <!-- Nom -->

                                <div>

                                    <label class="block text-sm font-bold
                                                  text-[#0B2A55]
                                                  dark:text-gray-200 mb-2">

                                        Nom *

                                    </label>

                                    <input
                                        type="text"
                                        name="last_name"
                                        value="{{ old('last_name') }}"
                                        required
                                        placeholder="Ex : Benali"
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

                                    @error('last_name')

                                        <p class="mt-1 text-sm text-red-600">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <!-- Date naissance -->

                                <div>

                                    <label class="block text-sm font-bold
                                                  text-[#0B2A55]
                                                  dark:text-gray-200 mb-2">

                                        Date de naissance

                                    </label>

                                    <input
                                        type="date"
                                        name="date_of_birth"
                                        value="{{ old('date_of_birth') }}"
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

                                    @error('date_of_birth')

                                        <p class="mt-1 text-sm text-red-600">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <!-- Niveau -->

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

                                            <option value="1AP"
                                                {{ old('level') == '1AP' ? 'selected' : '' }}>
                                                1ère année primaire
                                            </option>

                                            <option value="2AP"
                                                {{ old('level') == '2AP' ? 'selected' : '' }}>
                                                2ème année primaire
                                            </option>

                                            <option value="3AP"
                                                {{ old('level') == '3AP' ? 'selected' : '' }}>
                                                3ème année primaire
                                            </option>

                                            <option value="4AP"
                                                {{ old('level') == '4AP' ? 'selected' : '' }}>
                                                4ème année primaire
                                            </option>

                                            <option value="5AP"
                                                {{ old('level') == '5AP' ? 'selected' : '' }}>
                                                5ème année primaire
                                            </option>

                                        </optgroup>

                                        <optgroup label="CEM">

                                            <option value="1AM"
                                                {{ old('level') == '1AM' ? 'selected' : '' }}>
                                                1ère année CEM
                                            </option>

                                            <option value="2AM"
                                                {{ old('level') == '2AM' ? 'selected' : '' }}>
                                                2ème année CEM
                                            </option>

                                            <option value="3AM"
                                                {{ old('level') == '3AM' ? 'selected' : '' }}>
                                                3ème année CEM
                                            </option>

                                            <option value="4AM"
                                                {{ old('level') == '4AM' ? 'selected' : '' }}>
                                                4ème année CEM
                                            </option>

                                        </optgroup>

                                        <optgroup label="Lycée">

                                            <option value="1AS"
                                                {{ old('level') == '1AS' ? 'selected' : '' }}>
                                                1ère année secondaire
                                            </option>

                                            <option value="2AS"
                                                {{ old('level') == '2AS' ? 'selected' : '' }}>
                                                2ème année secondaire
                                            </option>

                                            <option value="3AS"
                                                {{ old('level') == '3AS' ? 'selected' : '' }}>
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

                            </div>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- CONTACT -->
                    <!-- ================================================= -->

                    <div class="bg-white dark:bg-gray-800
                                rounded-3xl shadow-sm
                                border border-gray-100
                                dark:border-gray-700
                                overflow-hidden">

                        <div class="px-6 py-5
                                    border-b border-gray-100
                                    dark:border-gray-700">

                            <div class="flex items-center gap-3">

                                <div class="w-11 h-11 rounded-xl
                                            bg-[#C89B3C]/15
                                            flex items-center justify-center">

                                    📞

                                </div>

                                <div>

                                    <h2 class="text-lg font-extrabold
                                               text-[#0B2A55]
                                               dark:text-white">

                                        Informations de contact

                                    </h2>

                                    <p class="text-sm text-gray-500">

                                        Coordonnées de l'élève.

                                    </p>

                                </div>

                            </div>

                        </div>

                        <div class="p-6 md:p-7">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <!-- Téléphone -->

                                <div>

                                    <label class="block text-sm font-bold
                                                  text-[#0B2A55]
                                                  dark:text-gray-200 mb-2">

                                        Téléphone

                                    </label>

                                    <input
                                        type="text"
                                        name="phone"
                                        value="{{ old('phone') }}"
                                        placeholder="Ex : 0550 00 00 00"
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

                                    @error('phone')

                                        <p class="mt-1 text-sm text-red-600">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <!-- Adresse -->

                                <div>

                                    <label class="block text-sm font-bold
                                                  text-[#0B2A55]
                                                  dark:text-gray-200 mb-2">

                                        Adresse

                                    </label>

                                    <input
                                        type="text"
                                        name="address"
                                        value="{{ old('address') }}"
                                        placeholder="Adresse de l'élève"
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

                                    @error('address')

                                        <p class="mt-1 text-sm text-red-600">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- PARENT -->
                    <!-- ================================================= -->

                    <div class="bg-white dark:bg-gray-800
                                rounded-3xl shadow-sm
                                border border-gray-100
                                dark:border-gray-700
                                overflow-hidden">

                        <div class="px-6 py-5
                                    border-b border-gray-100
                                    dark:border-gray-700">

                            <div class="flex items-center gap-3">

                                <div class="w-11 h-11 rounded-xl
                                            bg-blue-100
                                            flex items-center justify-center">

                                    👨‍👩‍👧

                                </div>

                                <div>

                                    <h2 class="text-lg font-extrabold
                                               text-[#0B2A55]
                                               dark:text-white">

                                        Informations du parent / tuteur

                                    </h2>

                                    <p class="text-sm text-gray-500">

                                        Coordonnées du responsable.

                                    </p>

                                </div>

                            </div>

                        </div>

                        <div class="p-6 md:p-7">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <!-- Nom parent -->

                                <div>

                                    <label class="block text-sm font-bold
                                                  text-[#0B2A55]
                                                  dark:text-gray-200 mb-2">

                                        Nom du parent

                                    </label>

                                    <input
                                        type="text"
                                        name="parent_name"
                                        value="{{ old('parent_name') }}"
                                        placeholder="Ex : Mohamed Benali"
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

                                    @error('parent_name')

                                        <p class="mt-1 text-sm text-red-600">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <!-- Téléphone parent -->

                                <div>

                                    <label class="block text-sm font-bold
                                                  text-[#0B2A55]
                                                  dark:text-gray-200 mb-2">

                                        Téléphone du parent

                                    </label>

                                    <input
                                        type="text"
                                        name="parent_phone"
                                        value="{{ old('parent_phone') }}"
                                        placeholder="Ex : 0550 00 00 00"
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

                                    @error('parent_phone')

                                        <p class="mt-1 text-sm text-red-600">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- INSCRIPTION PÉDAGOGIQUE -->
                    <!-- ================================================= -->

                    <div class="bg-white dark:bg-gray-800
                                rounded-3xl shadow-sm
                                border border-gray-100
                                dark:border-gray-700
                                overflow-hidden">

                        <div class="px-6 py-5
                                    border-b border-gray-100
                                    dark:border-gray-700">

                            <div class="flex flex-col sm:flex-row
                                        sm:items-center
                                        sm:justify-between
                                        gap-4">

                                <div class="flex items-center gap-3">

                                    <div class="w-11 h-11 rounded-xl
                                                bg-green-100
                                                flex items-center justify-center">

                                        📚

                                    </div>

                                    <div>

                                        <h2 class="text-lg font-extrabold
                                                   text-[#0B2A55]
                                                   dark:text-white">

                                            Inscription pédagogique

                                        </h2>

                                        <p class="text-sm text-gray-500">

                                            Choisissez les matières et le type d'abonnement.

                                        </p>

                                    </div>

                                </div>


                                <button
                                    type="button"
                                    id="add-enrollment"
                                    class="inline-flex items-center justify-center gap-2
                                           px-5 py-3
                                           rounded-xl
                                           bg-green-600
                                           hover:bg-green-700
                                           text-white
                                           font-bold
                                           shadow-md
                                           transition">

                                    <span class="text-xl">
                                        ＋
                                    </span>

                                    Ajouter une matière

                                </button>

                            </div>

                        </div>


                        <div class="p-6 md:p-7">

                            <div id="enrollments-container"
                                 class="space-y-5">
                            </div>


                            @error('enrollments')

                                <p class="mt-3 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror


                            @foreach ($errors->get('enrollments.*.subject_id') as $messages)

                                @foreach ($messages as $message)

                                    <p class="mt-3 text-sm text-red-600">
                                        {{ $message }}
                                    </p>

                                @endforeach

                            @endforeach


                            @foreach ($errors->get('enrollments.*.payment_type') as $messages)

                                @foreach ($messages as $message)

                                    <p class="mt-3 text-sm text-red-600">
                                        {{ $message }}
                                    </p>

                                @endforeach

                            @endforeach


                            @foreach ($errors->get('enrollments.*.teacher_id') as $messages)

                                @foreach ($messages as $message)

                                    <p class="mt-3 text-sm text-red-600">
                                        {{ $message }}
                                    </p>

                                @endforeach

                            @endforeach

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- BUTTONS -->
                    <!-- ================================================= -->

                    <div class="flex flex-col-reverse
                                sm:flex-row
                                items-stretch sm:items-center
                                justify-between
                                gap-4
                                bg-white dark:bg-gray-800
                                rounded-3xl
                                p-5 md:p-6
                                shadow-sm
                                border border-gray-100
                                dark:border-gray-700">

                        <a
                            href="{{ route('students.index') }}"
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
                            class="group inline-flex
                                   items-center
                                   justify-center
                                   gap-3
                                   px-8 py-3.5
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
                                         transition">

                                💾

                            </span>

                            Enregistrer l'élève

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- ================================================= -->
    <!-- JAVASCRIPT -->
    <!-- ================================================= -->

    <script>

    document.addEventListener('DOMContentLoaded', function () {

        const container =
            document.getElementById('enrollments-container');

        const addButton =
            document.getElementById('add-enrollment');

        const levelSelect =
            document.getElementById('level');


        let enrollmentIndex = 0;


        // =====================================================
        // CHARGER LES MATIÈRES SELON LE NIVEAU
        // =====================================================

        function loadSubjectsForLevel(
            select,
            restoreSubjectId = null
        ) {

            const level =
                levelSelect.value;


            select.disabled = true;


            select.innerHTML = `
                <option value="">
                    ${
                        level
                            ? 'Chargement des matières...'
                            : 'Sélectionner d’abord un niveau'
                    }
                </option>
            `;


            if (!level) {

                return;

            }


            const url =
                "{{ route('subjects.by-level', '__LEVEL__') }}"
                    .replace('__LEVEL__', level);


            fetch(url)

                .then(response => {

                    if (!response.ok) {

                        throw new Error(
                            'Erreur lors du chargement'
                        );

                    }

                    return response.json();

                })

                .then(subjects => {

                    select.innerHTML = `
                        <option value="">
                            Sélectionner une matière
                        </option>
                    `;


                    if (
                        !Array.isArray(subjects) ||
                        subjects.length === 0
                    ) {

                        select.innerHTML = `
                            <option value="">
                                Aucune matière disponible pour ${level}
                            </option>
                        `;

                        return;

                    }


                    subjects.forEach(subject => {

                        const option =
                            document.createElement('option');


                        option.value =
                            subject.id;


                        option.textContent =
                            subject.name +
                            (
                                subject.code
                                    ? ` (${subject.code})`
                                    : ''
                            );


                        if (
                            restoreSubjectId &&
                            String(restoreSubjectId) ===
                            String(subject.id)
                        ) {

                            option.selected = true;

                        }


                        select.appendChild(option);

                    });


                    select.disabled = false;


                    // Si ancienne matière trouvée
                    if (select.value) {

                        const row =
                            select.closest(
                                '.enrollment-row'
                            );


                        const teacherSelect =
                            row.querySelector(
                                '.teacher-select'
                            );


                        loadTeachers(
                            select,
                            teacherSelect
                        );

                    }

                })

                .catch(error => {

                    console.error(error);


                    select.innerHTML = `
                        <option value="">
                            Erreur de chargement des matières
                        </option>
                    `;

                });

        }


        // =====================================================
        // CRÉER UNE LIGNE
        // =====================================================

        function createEnrollmentRow(data = {}) {

            const index =
                enrollmentIndex++;


            const row =
                document.createElement('div');


            row.className = `
                enrollment-row
                p-5
                rounded-2xl
                border-2 border-gray-200
                bg-gray-50
                dark:bg-gray-900
                dark:border-gray-700
                relative
            `;


            row.innerHTML = `

                <div class="flex items-center justify-between mb-4">

                    <div class="flex items-center gap-2">

                        <div class="enrollment-number
                                    w-8 h-8 rounded-lg
                                    bg-[#0B2A55]
                                    text-white
                                    flex items-center justify-center
                                    font-bold">

                            ${index + 1}

                        </div>

                        <span class="enrollment-title
                                     font-bold
                                     text-[#0B2A55]
                                     dark:text-white">

                            Matière ${index + 1}

                        </span>

                    </div>


                    <button
                        type="button"
                        class="remove-enrollment
                               inline-flex
                               items-center
                               justify-center
                               w-9 h-9
                               rounded-lg
                               bg-red-100
                               hover:bg-red-200
                               text-red-600
                               font-bold
                               transition">

                        🗑️

                    </button>

                </div>


                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">


                    <!-- MATIÈRE -->

                    <div>

                        <label class="block text-sm font-bold
                                      text-[#0B2A55]
                                      dark:text-gray-200 mb-2">

                            Matière *

                        </label>


                        <select
                            name="enrollments[${index}][subject_id]"
                            class="subject-select w-full px-4 py-3.5
                                   rounded-xl
                                   border-2 border-gray-200
                                   bg-white
                                   outline-none
                                   transition
                                   focus:border-[#C89B3C]
                                   focus:ring-4
                                   focus:ring-[#C89B3C]/10"
                            required>

                            <option value="">

                                Sélectionner une matière

                            </option>

                        </select>

                    </div>


                    <!-- ENSEIGNANT -->

                    <div>

                        <label class="block text-sm font-bold
                                      text-[#0B2A55]
                                      dark:text-gray-200 mb-2">

                            Enseignant *

                        </label>


                        <select
                            name="enrollments[${index}][teacher_id]"
                            class="teacher-select w-full px-4 py-3.5
                                   rounded-xl
                                   border-2 border-gray-200
                                   bg-white
                                   outline-none
                                   transition
                                   focus:border-[#C89B3C]
                                   focus:ring-4
                                   focus:ring-[#C89B3C]/10"
                            required
                            disabled>

                            <option value="">

                                Sélectionner une matière d'abord

                            </option>

                        </select>


                        <p class="teacher-warning
                                  mt-1 text-sm text-amber-600
                                  hidden">

                            Aucun enseignant actif pour cette matière.

                        </p>


                        <p class="duplicate-warning
                                  mt-1 text-sm text-red-600
                                  hidden">

                            Cet enseignant est déjà sélectionné pour cette matière.

                        </p>

                    </div>


                    <!-- TYPE ABONNEMENT -->

                    <div>

                        <label class="block text-sm font-bold
                                      text-[#0B2A55]
                                      dark:text-gray-200 mb-2">

                            Type d'abonnement *

                        </label>


                        <select
                            name="enrollments[${index}][payment_type]"
                            class="payment-select w-full px-4 py-3.5
                                   rounded-xl
                                   border-2 border-gray-200
                                   bg-white
                                   outline-none
                                   transition
                                   focus:border-[#C89B3C]
                                   focus:ring-4
                                   focus:ring-[#C89B3C]/10"
                            required>

                            <option value="">

                                Sélectionner

                            </option>


                            <option value="monthly">

                                Mensuel

                            </option>


                            <option value="vip">

                                VIP

                            </option>

                        </select>

                    </div>

                </div>
            `;


            container.appendChild(row);


            const subjectSelect =
                row.querySelector('.subject-select');


            const paymentSelect =
                row.querySelector('.payment-select');


            const teacherSelect =
                row.querySelector('.teacher-select');


            // =================================================
            // RESTAURER ANCIENNES VALEURS
            // =================================================

            if (data.payment_type) {

                paymentSelect.value =
                    data.payment_type;

            }


            if (data.teacher_id) {

                teacherSelect.dataset.restore =
                    data.teacher_id;

            }


            // =================================================
            // CHARGER LES MATIÈRES SELON LE NIVEAU
            // =================================================

            loadSubjectsForLevel(
                subjectSelect,
                data.subject_id ?? null
            );


            // =================================================
            // CHANGEMENT MATIÈRE
            // =================================================

            subjectSelect.addEventListener(
                'change',
                function () {

                    loadTeachers(
                        subjectSelect,
                        teacherSelect
                    );

                }
            );


            // =================================================
            // CHANGEMENT ENSEIGNANT
            // =================================================

            teacherSelect.addEventListener(
                'change',
                checkDuplicates
            );


            // =================================================
            // SUPPRIMER
            // =================================================

            row.querySelector(
                '.remove-enrollment'
            ).addEventListener(
                'click',
                function () {

                    row.remove();

                    updateNumbers();

                    checkDuplicates();

                }
            );

        }


        // =====================================================
        // VÉRIFIER LES DOUBLONS MATIÈRE + ENSEIGNANT
        // =====================================================

        function checkDuplicates() {

            const rows =
                container.querySelectorAll(
                    '.enrollment-row'
                );

            const seen = new Map();

            let hasDuplicate = false;

            rows.forEach(row => {

                const subjectSelect =
                    row.querySelector(
                        '.subject-select'
                    );

                const teacherSelect =
                    row.querySelector(
                        '.teacher-select'
                    );

                const warning =
                    row.querySelector(
                        '.duplicate-warning'
                    );

                warning.classList.add('hidden');

                row.classList.remove(
                    'border-red-400'
                );

                const subjectId =
                    subjectSelect.value;

                const teacherId =
                    teacherSelect.value;

                if (!subjectId || !teacherId) {

                    return;

                }

                const key =
                    subjectId + '-' + teacherId;

                if (seen.has(key)) {

                    hasDuplicate = true;

                    const previousRow =
                        seen.get(key);

                    previousRow.classList.add(
                        'border-red-400'
                    );

                    previousRow.querySelector(
                        '.duplicate-warning'
                    ).classList.remove('hidden');

                    row.classList.add(
                        'border-red-400'
                    );

                    warning.classList.remove(
                        'hidden'
                    );

                } else {

                    seen.set(key, row);

                }

            });

            return !hasDuplicate;

        }


        // =====================================================
        // CHARGER LES ENSEIGNANTS
        // =====================================================

        function loadTeachers(
            subjectSelect,
            teacherSelect
        ) {

            const subjectId =
                subjectSelect.value;


            const warning =
                teacherSelect
                    .parentElement
                    .querySelector('.teacher-warning');


            teacherSelect.disabled = true;

            warning.classList.add('hidden');


            if (!subjectId) {

                teacherSelect.innerHTML = `
                    <option value="">
                        Sélectionner une matière d'abord
                    </option>
                `;

                return;

            }


            teacherSelect.innerHTML = `
                <option value="">
                    Chargement...
                </option>
            `;


            const url =
                "{{ route('students.subjects.teachers', ['subject' => '__SUBJECT_ID__', 'level' => '__LEVEL__']) }}"
                    .replace(
                        '__SUBJECT_ID__',
                        subjectId
                    )
                    .replace(
                        '__LEVEL__',
                        levelSelect.value
                    );


            fetch(url)

                .then(response => {

                    if (!response.ok) {

                        throw new Error(
                            'Erreur lors du chargement'
                        );

                    }

                    return response.json();

                })

                .then(teachers => {

                    if (
                        !Array.isArray(teachers) ||
                        teachers.length === 0
                    ) {

                        teacherSelect.innerHTML = `
                            <option value="">
                                Aucun enseignant disponible
                            </option>
                        `;

                        warning.classList.remove('hidden');

                        return;

                    }


                    teacherSelect.innerHTML = `
                        <option value="">
                            Sélectionner un enseignant
                        </option>
                    `;


                    teachers.forEach(teacher => {

                        const option =
                            document.createElement('option');


                        option.value =
                            teacher.id;


                        option.textContent =
                            teacher.first_name +
                            ' ' +
                            teacher.last_name;


                        teacherSelect.appendChild(
                            option
                        );

                    });


                    teacherSelect.disabled = false;


                    const restore =
                        teacherSelect.dataset.restore;


                    if (restore) {

                        teacherSelect.value =
                            restore;

                        delete teacherSelect.dataset.restore;

                    }


                    checkDuplicates();

                })

                .catch(error => {

                    console.error(error);


                    teacherSelect.innerHTML = `
                        <option value="">
                            Erreur de chargement
                        </option>
                    `;

                });

        }


        // =====================================================
        // CHANGEMENT DU NIVEAU
        // =====================================================

        levelSelect.addEventListener(
            'change',
            function () {

                const selects =
                    container.querySelectorAll(
                        '.subject-select'
                    );


                selects.forEach(select => {

                    const row =
                        select.closest(
                            '.enrollment-row'
                        );


                    const teacherSelect =
                        row.querySelector(
                            '.teacher-select'
                        );


                    // Reset enseignant

                    teacherSelect.value = '';

                    teacherSelect.disabled = true;

                    teacherSelect.innerHTML = `
                        <option value="">
                            Sélectionner une matière d'abord
                        </option>
                    `;


                    teacherSelect
                        .parentElement
                        .querySelector(
                            '.teacher-warning'
                        )
                        .classList.add('hidden');


                    // Recharger les matières

                    loadSubjectsForLevel(
                        select
                    );

                });

            }
        );


        // =====================================================
        // AJOUTER UNE MATIÈRE
        // =====================================================

        addButton.addEventListener(
            'click',
            function () {

                // Il faut choisir un niveau avant
                if (!levelSelect.value) {

                    alert(
                        'Veuillez sélectionner le niveau scolaire avant d’ajouter une matière.'
                    );

                    levelSelect.focus();

                    return;

                }


                createEnrollmentRow();

            }
        );


        // =====================================================
        // NUMÉROS
        // =====================================================

        function updateNumbers() {

            const rows =
                container.querySelectorAll(
                    '.enrollment-row'
                );


            rows.forEach(
                (row, index) => {

                    const number =
                        row.querySelector(
                            '.enrollment-number'
                        );


                    const title =
                        row.querySelector(
                            '.enrollment-title'
                        );


                    if (number) {

                        number.textContent =
                            index + 1;

                    }


                    if (title) {

                        title.textContent =
                            `Matière ${index + 1}`;

                    }

                }
            );


            if (rows.length === 0) {

                createEnrollmentRow();

            }

        }


        // =====================================================
        // RESTAURER OLD()
        // =====================================================

        const oldEnrollments =
            @json(old('enrollments', []));


        if (
            Array.isArray(oldEnrollments) &&
            oldEnrollments.length > 0
        ) {

            oldEnrollments.forEach(
                enrollment => {

                    createEnrollmentRow(
                        enrollment
                    );

                }
            );

        } else {

            createEnrollmentRow();

        }


        // =====================================================
        // BLOQUER L'ENVOI SI DOUBLON
        // =====================================================

        const enrollmentForm =
            container.closest('form');


        if (enrollmentForm) {

            enrollmentForm.addEventListener(
                'submit',
                function (event) {

                    if (!checkDuplicates()) {

                        event.preventDefault();

                        enrollmentForm
                            .querySelector(
                                '.enrollment-row.border-red-400'
                            )
                            ?.querySelector(
                                '.subject-select'
                            )
                            ?.focus();

                    }

                }
            );

        }

    });

    </script>


</x-app-layout>