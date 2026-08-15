<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Enregistrer les présences
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Enregistrer la présence des élèves pour une séance
                </p>
            </div>

            <a href="{{ route('attendances.index') }}"
               class="px-4 py-2 rounded-lg text-white font-semibold shadow"
               style="background-color: #0B2A55;">
                ← Liste des présences
            </a>
        </div>
    </x-slot>


    <div class="py-10 bg-gray-100 dark:bg-gray-900 min-h-screen">

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Message d'erreur --}}
            @if ($errors->any())
                <div class="mb-6 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-xl">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <form method="POST" action="{{ route('attendances.store') }}">
                @csrf

                {{-- Informations de la séance --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 mb-6">

                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-10 h-10 rounded-xl flex items-center justify-center text-white"
                            style="background-color: #0B2A55;">
                            📚
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                                Informations de la séance
                            </h3>

                            <p class="text-sm text-gray-500">
                                Sélectionnez les informations de la séance
                            </p>
                        </div>
                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                        {{-- Date --}}
                        <div>
                            <label for="date"
                                   class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Date de la séance
                            </label>

                            <input
                                type="date"
                                id="date"
                                name="date"
                                value="{{ old('date', now()->format('Y-m-d')) }}"
                                required
                                class="w-full rounded-xl border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-white
                                       focus:border-[#0B2A55] focus:ring-[#0B2A55]"
                            >
                        </div>


                        {{-- Matière --}}
                        <div>
                            <label for="subject_id"
                                   class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Matière
                            </label>

                            <select
                                id="subject_id"
                                name="subject_id"
                                required
                                class="w-full rounded-xl border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-white
                                       focus:border-[#0B2A55] focus:ring-[#0B2A55]"
                            >
                                <option value="">-- Sélectionner une matière --</option>

                                @foreach ($subjects as $subject)
                                    <option
                                        value="{{ $subject->id }}"
                                        {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>


                        {{-- Enseignant --}}
                        <div>
                            <label for="teacher_id"
                                   class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Enseignant
                            </label>

                            <select
                                id="teacher_id"
                                name="teacher_id"
                                required
                                class="w-full rounded-xl border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-white
                                       focus:border-[#0B2A55] focus:ring-[#0B2A55]"
                            >
                                <option value="">-- Sélectionner un enseignant --</option>

                                @foreach ($teachers as $teacher)
                                    <option
                                        value="{{ $teacher->id }}"
                                        {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->first_name }} {{ $teacher->last_name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                    </div>

                </div>


                {{-- Liste des élèves --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">

                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">

                        <div class="flex items-center justify-between">

                            <div>
                                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                                    Liste des élèves
                                </h3>

                                <p class="text-sm text-gray-500 mt-1">
                                    Sélectionnez le statut de chaque élève
                                </p>
                            </div>

                            <div
                                class="px-4 py-2 rounded-xl font-semibold text-sm"
                                style="background-color: #C89B3C; color: white;">
                                <span id="students-count">0</span> élève(s)
                            </div>

                        </div>

                    </div>


                    {{-- Message : sélectionner matière + enseignant --}}
                    <div id="students-prompt" class="p-10 text-center">

                        <div class="text-5xl mb-4">
                            👨‍🎓
                        </div>

                        <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200">
                            Sélectionnez une matière et un enseignant
                        </h3>

                        <p class="text-sm text-gray-500 mt-2">
                            La liste des élèves sera affichée automatiquement après la sélection.
                        </p>

                    </div>


                    {{-- Message : aucun élève inscrit --}}
                    <div id="students-empty" class="p-10 text-center hidden">

                        <div class="text-5xl mb-4">
                            🕵️
                        </div>

                        <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200">
                            Aucun élève inscrit
                        </h3>

                        <p class="text-sm text-gray-500 mt-2">
                            Aucun élève n'est inscrit à cette matière avec cet enseignant.
                        </p>

                    </div>


                    {{-- Tableau (rempli automatiquement) --}}
                    <div id="students-table" class="hidden">

                        <div class="overflow-x-auto">

                            <table class="w-full">

                                <thead style="background-color: #0B2A55;">

                                    <tr class="text-white text-sm">

                                        <th class="px-6 py-4 text-left">
                                            #
                                        </th>

                                        <th class="px-6 py-4 text-left">
                                            Élève
                                        </th>

                                        <th class="px-6 py-4 text-center">
                                            Présent
                                        </th>

                                        <th class="px-6 py-4 text-center">
                                            Absent
                                        </th>

                                        <th class="px-6 py-4 text-center">
                                            Retard
                                        </th>

                                        <th class="px-6 py-4 text-center">
                                            Justifié
                                        </th>

                                        <th class="px-6 py-4 text-left">
                                            Note
                                        </th>

                                    </tr>

                                </thead>


                                <tbody id="students-tbody"
                                       class="divide-y divide-gray-200 dark:divide-gray-700">
                                </tbody>

                            </table>

                        </div>


                        {{-- Bouton --}}
                        <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end">

                            <button
                                type="submit"
                                class="px-6 py-3 rounded-xl text-white font-bold shadow-md
                                       hover:opacity-90 transition"
                                style="background-color: #0B2A55;"
                            >
                                💾 Enregistrer les présences
                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const subjectSelect =
        document.getElementById('subject_id');

    const teacherSelect =
        document.getElementById('teacher_id');

    const tbody =
        document.getElementById('students-tbody');

    const prompt =
        document.getElementById('students-prompt');

    const empty =
        document.getElementById('students-empty');

    const table =
        document.getElementById('students-table');

    const countBadge =
        document.getElementById('students-count');


    function escapeHtml(value) {

        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }


    function setCount(count) {

        countBadge.textContent = count;
    }


    function showPrompt() {

        prompt.classList.remove('hidden');

        empty.classList.add('hidden');

        table.classList.add('hidden');

        tbody.innerHTML = '';

        setCount(0);
    }


    function showEmpty() {

        prompt.classList.add('hidden');

        empty.classList.remove('hidden');

        table.classList.add('hidden');

        tbody.innerHTML = '';

        setCount(0);
    }


    function renderRows(students) {

        tbody.innerHTML = '';

        setCount(students.length);

        students.forEach(function (student, index) {

            const row = document.createElement('tr');

            row.className =
                'hover:bg-gray-50 dark:hover:bg-gray-700 transition';

            row.innerHTML = `
                <input
                    type="hidden"
                    name="students[${student.id}][id]"
                    value="${student.id}"
                >
                <td class="px-6 py-4 text-sm text-gray-500">
                    ${index + 1}
                </td>
                <td class="px-6 py-4">
                    <div class="font-semibold text-gray-800 dark:text-gray-100">
                        ${escapeHtml(student.first_name)}
                        ${escapeHtml(student.last_name)}
                    </div>
                </td>
                <td class="px-6 py-4 text-center">
                    <input
                        type="radio"
                        name="students[${student.id}][status]"
                        value="present"
                        required
                        class="w-5 h-5"
                    >
                </td>
                <td class="px-6 py-4 text-center">
                    <input
                        type="radio"
                        name="students[${student.id}][status]"
                        value="absent"
                        class="w-5 h-5"
                    >
                </td>
                <td class="px-6 py-4 text-center">
                    <input
                        type="radio"
                        name="students[${student.id}][status]"
                        value="late"
                        class="w-5 h-5"
                    >
                </td>
                <td class="px-6 py-4 text-center">
                    <input
                        type="radio"
                        name="students[${student.id}][status]"
                        value="justified"
                        class="w-5 h-5"
                    >
                </td>
                <td class="px-6 py-4">
                    <input
                        type="text"
                        name="students[${student.id}][note]"
                        placeholder="Note..."
                        class="w-full rounded-lg border-gray-300
                               dark:border-gray-600
                               dark:bg-gray-700
                               dark:text-white text-sm
                               focus:border-[#0B2A55]
                               focus:ring-[#0B2A55]"
                    >
                </td>
            `;

            tbody.appendChild(row);
        });
    }


    function loadStudents() {

        const subjectId = subjectSelect.value;

        const teacherId = teacherSelect.value;


        if (!subjectId || !teacherId) {

            showPrompt();

            return;
        }


        const url =
            "{{ route('attendances.students') }}"
            + '?subject_id=' + encodeURIComponent(subjectId)
            + '&teacher_id=' + encodeURIComponent(teacherId);


        fetch(url)

            .then(function (response) {

                if (!response.ok) {

                    throw new Error('Erreur lors du chargement');
                }

                return response.json();
            })

            .then(function (students) {

                if (!Array.isArray(students) || students.length === 0) {

                    showEmpty();

                    return;
                }

                prompt.classList.add('hidden');

                empty.classList.add('hidden');

                table.classList.remove('hidden');

                renderRows(students);
            })

            .catch(function () {

                showPrompt();
            });
    }


    subjectSelect.addEventListener('change', loadStudents);

    teacherSelect.addEventListener('change', loadStudents);

    loadStudents();
});
</script>
