<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
            Modifier l'élève
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('students.update', $student) }}" method="POST">

                    @csrf
                    @method('PUT')

                    @php
                        $activeEnrollments = $student->enrollments
                            ->where('status', 'active')
                            ->map(fn ($enrollment) => [
                                'id' => $enrollment->id,
                                'subject_id' => $enrollment->subject_id,
                                'teacher_id' => $enrollment->teacher_id,
                                'group_id' => $enrollment->group_id,
                            ])
                            ->values();
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Nom -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Nom
                            </label>

                            <input type="text"
                                   name="last_name"
                                   value="{{ old('last_name', $student->last_name) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">

                            @error('last_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Prénom -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Prénom
                            </label>

                            <input type="text"
                                   name="first_name"
                                   value="{{ old('first_name', $student->first_name) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">

                            @error('first_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date naissance -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Date de naissance
                            </label>

                            <input type="date"
                                   name="date_of_birth"
                                   value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">

                            @error('date_of_birth')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Niveau -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Niveau
                            </label>

                            <select
                                name="level"
                                id="level"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300">

                                <option value="">
                                    Sélectionner un niveau
                                </option>

                                <optgroup label="Primaire">

                                    @foreach ([
                                        '1AP' => '1ère année primaire',
                                        '2AP' => '2ème année primaire',
                                        '3AP' => '3ème année primaire',
                                        '4AP' => '4ème année primaire',
                                        '5AP' => '5ème année primaire',
                                    ] as $levelValue => $levelLabel)

                                        <option value="{{ $levelValue }}"
                                            {{ old('level', $student->level) == $levelValue ? 'selected' : '' }}>
                                            {{ $levelLabel }}
                                        </option>

                                    @endforeach

                                </optgroup>

                                <optgroup label="CEM">

                                    @foreach ([
                                        '1AM' => '1ère année CEM',
                                        '2AM' => '2ème année CEM',
                                        '3AM' => '3ème année CEM',
                                        '4AM' => '4ème année CEM',
                                    ] as $levelValue => $levelLabel)

                                        <option value="{{ $levelValue }}"
                                            {{ old('level', $student->level) == $levelValue ? 'selected' : '' }}>
                                            {{ $levelLabel }}
                                        </option>

                                    @endforeach

                                </optgroup>

                                <optgroup label="Lycée">

                                    @foreach ([
                                        '1AS' => '1ère année secondaire',
                                        '2AS' => '2ème année secondaire',
                                        '3AS' => '3ème année secondaire',
                                    ] as $levelValue => $levelLabel)

                                        <option value="{{ $levelValue }}"
                                            {{ old('level', $student->level) == $levelValue ? 'selected' : '' }}>
                                            {{ $levelLabel }}
                                        </option>

                                    @endforeach

                                </optgroup>

                            </select>

                            @error('level')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Téléphone -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Téléphone
                            </label>

                            <input type="text"
                                   name="phone"
                                   value="{{ old('phone', $student->phone) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">
                        </div>

                        <!-- Parent -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Nom du parent
                            </label>

                            <input type="text"
                                   name="parent_name"
                                   value="{{ old('parent_name', $student->parent_name) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">
                        </div>

                        <!-- Téléphone parent -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Téléphone du parent
                            </label>

                            <input type="text"
                                   name="parent_phone"
                                   value="{{ old('parent_phone', $student->parent_phone) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">
                        </div>

                        <!-- Adresse -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Adresse
                            </label>

                            <input type="text"
                                   name="address"
                                   value="{{ old('address', $student->address) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">
                        </div>

                    </div>

                    <!-- ================================================= -->
                    <!-- CHANGER LE MOT DE PASSE (optionnel) -->
                    <!-- ================================================= -->

                    @if($student->user)
                    <div class="mt-8">

                        <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200">
                            Changer le mot de passe
                        </h2>

                        <p class="text-sm text-gray-500 mb-4">
                            Laissez vide pour conserver le mot de passe actuel.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Nouveau mot de passe
                                </label>

                                <input type="password"
                                       name="password"
                                       autocomplete="new-password"
                                       placeholder="Minimum 8 caractères"
                                       class="mt-1 block w-full rounded-md border-gray-300">

                                @error('password')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Confirmer le nouveau mot de passe
                                </label>

                                <input type="password"
                                       name="password_confirmation"
                                       autocomplete="new-password"
                                       placeholder="Retaper le mot de passe"
                                       class="mt-1 block w-full rounded-md border-gray-300">
                            </div>

                        </div>

                    </div>
                    @endif

                    <!-- ================================================= -->
                    <!-- INSCRIPTION PÉDAGOGIQUE -->
                    <!-- ================================================= -->

                    <div class="mt-8">

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                            <div>

                                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200">
                                    Inscription pédagogique
                                </h2>

                                <p class="text-sm text-gray-500">
                                    Modifiez les matières, les enseignants et le type d'abonnement.
                                </p>

                            </div>

                            <button
                                type="button"
                                id="add-enrollment"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-bold shadow-sm transition">

                                <span class="text-xl">＋</span>

                                Ajouter une matière

                            </button>

                        </div>

                        <div id="enrollments-container"
                             class="mt-5 space-y-5">
                        </div>

                        @error('enrollments')

                            <p class="mt-3 text-sm text-red-600">
                                {{ $message }}
                            </p>

                        @enderror

                        @foreach ([
                            'enrollments.*.subject_id',
                            'enrollments.*.teacher_id',
                        ] as $errorKey)

                            @foreach ($errors->get($errorKey) as $messages)

                                @foreach ($messages as $message)

                                    <p class="mt-3 text-sm text-red-600">
                                        {{ $message }}
                                    </p>

                                @endforeach

                            @endforeach

                        @endforeach

                    </div>

                    <div class="mt-8 flex gap-3">

                        <button type="submit"
                                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                            Enregistrer les modifications
                        </button>

                        <a href="{{ route('students.index') }}"
                           class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">
                            Annuler
                        </a>

                    </div>

                </form>

            </div>

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
        // CHARGER LES GROUPES PAR ENSEIGNANT + MATIÈRE + NIVEAU
        // =====================================================

        function loadGroupsForTeacher(
            subjectSelect,
            teacherSelect,
            groupSelect,
            restoreGroupId = null,
            row = null
        ) {

            const subjectId = subjectSelect.value;
            const teacherId = teacherSelect.value;
            const level = levelSelect.value;

            groupSelect.disabled = true;

            groupSelect.innerHTML = `
                <option value="">
                    Chargement des groupes...
                </option>
            `;

            if (!subjectId || !teacherId || !level) {

                groupSelect.innerHTML = `
                    <option value="">
                        Sélectionner un enseignant d'abord
                    </option>
                `;

                return;

            }

            const url = "{{ route('groups.by-subject-level') }}"
                + '?subject_id=' + subjectId
                + '&level=' + level
                + '&teacher_id=' + teacherId;

            fetch(url)

                .then(response => {

                    if (!response.ok) {

                        throw new Error(
                            'Erreur lors du chargement'
                        );

                    }

                    return response.json();

                })

                .then(groups => {

                    groupSelect.innerHTML = `
                        <option value="">
                            Sélectionner un groupe
                        </option>
                    `;

                    if (
                        !Array.isArray(groups) ||
                        groups.length === 0
                    ) {

                        groupSelect.innerHTML = `
                            <option value="">
                                Aucun groupe disponible
                            </option>
                        `;

                        return;

                    }

                    groups.forEach(group => {

                        const option =
                            document.createElement('option');

                        option.value =
                            group.id;

                        // Cache group data
                        option.dataset.groupData =
                            JSON.stringify(group);

                        const modeLabel = group.mode === 'vip'
                            ? 'VIP'
                            : (group.mode === 'special' ? 'Spécial' : 'Normal');

                        option.textContent =
                            group.name +
                            ' [' +
                            modeLabel +
                            ']';

                        if (
                            restoreGroupId &&
                            String(restoreGroupId) ===
                            String(group.id)
                        ) {

                            option.selected = true;

                        }

                        groupSelect.appendChild(option);

                    });

                    groupSelect.disabled = false;

                    // Restaurer ancien groupe et afficher les détails
                    if (groupSelect.value) {

                        showGroupDetails(
                            row,
                            groupSelect.value,
                            groups,
                            teacherSelect
                        );

                    }

                })

                .catch(error => {

                    console.error(error);

                    groupSelect.innerHTML = `
                        <option value="">
                            Erreur de chargement
                        </option>
                    `;

                });

        }


        // =====================================================
        // AFFICHER LES DÉTAILS DU GROUPE SÉLECTIONNÉ
        // =====================================================

        function showGroupDetails(row, groupId, groups, teacherSelect) {

            const group = groups
                ? groups.find(g => String(g.id) === String(groupId))
                : null;

            const paymentTypeDisplay =
                row.querySelector('.payment-type-display');

            const tariffDisplay =
                row.querySelector('.tariff-display');

            if (!group) {

                paymentTypeDisplay.textContent = '—';
                tariffDisplay.textContent = '—';
                return;

            }

            // Auto-fill enseignant depuis le groupe
            if (teacherSelect && group.teacher_id) {
                teacherSelect.value = group.teacher_id;
            }

            // Tarif actif
            const tariffs = group.tariffs || [];
            const activeTariff = tariffs.length > 0
                ? tariffs[0]
                : null;

            if (activeTariff) {

                const price = parseFloat(activeTariff.student_price)
                    .toLocaleString('fr-FR');

                const billingLabel = activeTariff.billing_type === 'monthly'
                    ? '/mois'
                    : '/séance';

                tariffDisplay.textContent =
                    price + ' DA ' + billingLabel;

                // Type de paiement basé sur mode + billing_type
                if (group.mode === 'vip') {

                    if (activeTariff.billing_type === 'monthly') {
                        paymentTypeDisplay.textContent =
                            'VIP mensuel';
                    } else {
                        paymentTypeDisplay.textContent =
                            'VIP par séance';
                    }

                } else if (group.mode === 'special') {

                    paymentTypeDisplay.textContent =
                        'Special mensuel';

                } else {

                    paymentTypeDisplay.textContent =
                        'Normal mensuel';

                }

            } else {

                paymentTypeDisplay.textContent = 'Aucun tarif configuré';
                tariffDisplay.textContent = '—';

            }

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

                <input
                    type="hidden"
                    name="enrollments[${index}][id]"
                    value="${data.id ?? ''}"
                >

                <!-- MATIÈRE + GROUPE -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">

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

                    <!-- GROUPE -->

                    <div>

                        <label class="block text-sm font-bold
                                      text-[#0B2A55]
                                      dark:text-gray-200 mb-2">

                            Groupe *

                        </label>

                        <select
                            name="enrollments[${index}][group_id]"
                            class="group-select w-full px-4 py-3.5
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

                    </div>

                </div>

                <!-- ENSEIGNANT + TYPE + TARIF -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5
                            p-4
                            rounded-xl
                            bg-white
                            border border-gray-200
                            dark:bg-gray-800
                            dark:border-gray-600">

                    <!-- ENSEIGNANT (select) -->

                    <div>

                        <label class="block text-xs font-bold
                                      text-gray-500
                                      dark:text-gray-400
                                      uppercase
                                      tracking-wide
                                      mb-1">

                            👨‍🏫 Enseignant (défini par le groupe)

                        </label>

                        <select
                            name="enrollments[${index}][teacher_id]"
                            class="teacher-select w-full px-3 py-2.5
                                   rounded-lg
                                   border-2 border-gray-200
                                   bg-white
                                   outline-none
                                   transition
                                   text-sm font-bold
                                   focus:border-[#C89B3C]
                                   focus:ring-4
                                   focus:ring-[#C89B3C]/10"
                            required
                            disabled>

                            <option value="">

                                Sélectionner un groupe d'abord

                            </option>

                        </select>

                        <p class="teacher-warning
                                  mt-1 text-sm text-amber-600
                                  hidden">

                            Aucun enseignant actif.

                        </p>

                        <p class="duplicate-warning
                                  mt-1 text-sm text-red-600
                                  hidden">

                            Ce groupe est déjà sélectionné pour une autre inscription.

                        </p>

                    </div>

                    <!-- TYPE D'ABONNEMENT (read-only) -->

                    <div>

                        <label class="block text-xs font-bold
                                      text-gray-500
                                      dark:text-gray-400
                                      uppercase
                                      tracking-wide
                                      mb-1">

                            💳 Type d'abonnement

                        </label>

                        <p class="payment-type-display
                                  text-sm font-bold
                                  text-[#0B2A55]
                                  dark:text-white">

                            —

                        </p>

                    </div>

                    <!-- TARIF (read-only) -->

                    <div>

                        <label class="block text-xs font-bold
                                      text-gray-500
                                      dark:text-gray-400
                                      uppercase
                                      tracking-wide
                                      mb-1">

                            💰 Tarif

                        </label>

                        <p class="tariff-display
                                  text-sm font-bold
                                  text-[#0B2A55]
                                  dark:text-white">

                            —

                        </p>

                    </div>

                </div>
            `;

            container.appendChild(row);

            const subjectSelect =
                row.querySelector('.subject-select');

            const teacherSelect =
                row.querySelector('.teacher-select');

            const groupSelect =
                row.querySelector('.group-select');

            // =================================================
            // RESTAURER ANCIENNES VALEURS
            // =================================================

            if (data.teacher_id) {

                teacherSelect.dataset.restore =
                    data.teacher_id;

            }

            if (data.group_id) {

                groupSelect.dataset.restore =
                    data.group_id;

            }

            // =================================================
            // CHARGER LES MATIÈRES SELON LE NIVEAU
            // =================================================

            loadSubjectsForLevel(
                subjectSelect,
                data.subject_id ?? null
            );

            // =================================================
            // CHANGEMENT MATIÈRE → recharger enseignant + groupe
            // =================================================

            subjectSelect.addEventListener(
                'change',
                function () {

                    // Réinitialiser groupe + détails
                    groupSelect.value = '';
                    groupSelect.disabled = true;
                    groupSelect.innerHTML = `
                        <option value="">
                            Sélectionner une matière d'abord
                        </option>
                    `;

                    const teacherDisplay =
                        row.querySelector('.teacher-select');
                    teacherDisplay.value = '';
                    teacherDisplay.disabled = true;
                    teacherDisplay.innerHTML = `
                        <option value="">
                            Sélectionner un groupe d'abord
                        </option>
                    `;

                    row.querySelector('.payment-type-display').textContent = '—';
                    row.querySelector('.tariff-display').textContent = '—';

                    // Charger les enseignants pour cette matière
                    loadTeachers(
                        subjectSelect,
                        teacherSelect
                    );

                }
            );

            // =================================================
            // CHANGEMENT ENSEIGNANT → filtrer les groupes
            // =================================================

            teacherSelect.addEventListener(
                'change',
                function () {

                    const teacherId = teacherSelect.value;

                    // Réinitialiser groupe + détails
                    groupSelect.value = '';
                    groupSelect.disabled = true;
                    groupSelect.innerHTML = `
                        <option value="">
                            Chargement des groupes...
                        </option>
                    `;

                    row.querySelector('.payment-type-display').textContent = '—';
                    row.querySelector('.tariff-display').textContent = '—';

                    if (!teacherId) {
                        groupSelect.innerHTML = `
                            <option value="">
                                Sélectionner un enseignant d'abord
                            </option>
                        `;
                        return;
                    }

                    // Charger les groupes filtrés par enseignant
                    loadGroupsForTeacher(
                        subjectSelect,
                        teacherSelect,
                        groupSelect,
                        data.group_id,
                        row
                    );

                    checkDuplicates();
                }
            );

            // =================================================
            // CHANGEMENT GROUPE → afficher détails + auto enseignant
            // =================================================

            groupSelect.addEventListener(
                'change',
                function () {

                    const groupId = groupSelect.value;

                    if (!groupId) {
                        row.querySelector('.payment-type-display').textContent = '—';
                        row.querySelector('.tariff-display').textContent = '—';
                        return;
                    }

                    // Récupérer les groupes cachés dans les options
                    const groups = [];
                    const options = groupSelect.querySelectorAll('option');

                    options.forEach(opt => {
                        if (opt.value && opt.dataset.groupData) {
                            groups.push(JSON.parse(opt.dataset.groupData));
                        }
                    });

                    if (groups.length === 0) {
                        // Fallback : fetch
                        const url = "{{ route('groups.by-subject-level') }}"
                            + '?subject_id=' + subjectSelect.value
                            + '&level=' + levelSelect.value
                            + '&teacher_id=' + teacherSelect.value;

                        fetch(url)
                            .then(r => r.json())
                            .then(fetchedGroups => {
                                showGroupDetails(row, groupId, fetchedGroups, teacherSelect);
                            });
                    } else {
                        showGroupDetails(row, groupId, groups, teacherSelect);
                    }

                }
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
        // VÉRIFIER LES DOUBLONS GROUPE
        // =====================================================

        function checkDuplicates() {

            const rows =
                container.querySelectorAll(
                    '.enrollment-row'
                );

            const seen = new Map();

            let hasDuplicate = false;

            rows.forEach(row => {

                const groupSelect =
                    row.querySelector(
                        '.group-select'
                    );

                const warning =
                    row.querySelector(
                        '.duplicate-warning'
                    );

                warning.classList.add('hidden');

                row.classList.remove(
                    'border-red-400'
                );

                const groupId =
                    groupSelect.value;

                if (!groupId) {

                    return;

                }

                const key =
                    groupId;

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

                        teacherSelect.dispatchEvent(
                            new Event('change')
                        );

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

                    const groupSelect =
                        row.querySelector(
                            '.group-select'
                        );

                    // Reset groupe
                    groupSelect.value = '';
                    groupSelect.disabled = true;
                    groupSelect.innerHTML = `
                        <option value="">
                            Sélectionner une matière d'abord
                        </option>
                    `;

                    // Reset enseignant
                    teacherSelect.value = '';
                    teacherSelect.disabled = true;
                    teacherSelect.innerHTML = `
                        <option value="">
                            Sélectionner un groupe d'abord
                        </option>
                    `;

                    teacherSelect
                        .parentElement
                        .querySelector(
                            '.teacher-warning'
                        )
                        .classList.add('hidden');

                    // Reset détails
                    row.querySelector('.payment-type-display').textContent = '—';
                    row.querySelector('.tariff-display').textContent = '—';

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

                if (!levelSelect.value) {

                    alert(
                        'Veuillez sélectionner le niveau scolaire avant d’ajouter une matière.'
                    );

                    levelSelect.focus();

                    return;

                }

                container.querySelector(
                    '.enrollments-empty'
                )?.remove();

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

        }

        // =====================================================
        // PRÉ-REMPLIR LES INSCRIPTIONS EXISTANTES
        // =====================================================

        const existingEnrollments =
            @json($activeEnrollments);

        const oldEnrollments =
            @json(old('enrollments', []));

        const prefill =
            Array.isArray(oldEnrollments) &&
            oldEnrollments.length > 0
                ? oldEnrollments
                : existingEnrollments;

        if (
            Array.isArray(prefill) &&
            prefill.length > 0
        ) {

            prefill.forEach(
                enrollment => {

                    createEnrollmentRow(
                        enrollment
                    );

                }
            );

        } else {

            container.innerHTML = `
                <p class="enrollments-empty
                          text-sm text-gray-500">
                    Aucune matière inscrite pour le moment.
                </p>
            `;

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

                    enrollmentForm
                        .querySelectorAll('.group-select:disabled')
                        .forEach(function (el) {
                            el.disabled = false;
                        });

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