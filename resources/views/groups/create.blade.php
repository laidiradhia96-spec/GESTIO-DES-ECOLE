<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('groups.index') }}"
               class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition">
                ←
            </a>
            <div>
                <h2 class="text-2xl font-extrabold text-[#0B2A55] dark:text-white">
                    Créer un groupe
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Nouveau groupe pédagogique
                </p>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen py-10 bg-gradient-to-br from-gray-50 via-white to-blue-50 dark:from-gray-900 dark:to-gray-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <form action="{{ route('groups.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- ═══════════════════════════════════════ -->
                <!-- INFORMATIONS GÉNÉRALES -->
                <!-- ═══════════════════════════════════════ -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-extrabold text-[#0B2A55] mb-5 flex items-center gap-2">
                        📋 Informations générales
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Nom du groupe -->
                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
                                Nom du groupe <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                   class="w-full px-4 py-3 rounded-xl border-2 @error('name') border-red-500 @else border-gray-200 @enderror bg-gray-50 text-sm outline-none focus:border-[#C89B3C]"
                                   placeholder="Ex : Groupe 1" required>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Année scolaire -->
                        <div>
                            <label for="school_year_id" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
                                Année scolaire <span class="text-red-500">*</span>
                            </label>
                            <select id="school_year_id" name="school_year_id" required
                                    class="w-full px-4 py-3 rounded-xl border-2 @error('school_year_id') border-red-500 @else border-gray-200 @enderror bg-gray-50 text-sm outline-none focus:border-[#C89B3C]">
                                <option value="">Choisir une année</option>
                                @foreach($schoolYears as $year)
                                    <option value="{{ $year->id }}" {{ old('school_year_id', request('school_year_id', $currentSchoolYearId)) == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }} {{ $year->is_current ? '(courante)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('school_year_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Enseignant -->
                        <div>
                            <label for="teacher_id" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
                                Enseignant <span class="text-red-500">*</span>
                            </label>
                            <select id="teacher_id" name="teacher_id" required
                                    class="w-full px-4 py-3 rounded-xl border-2 @error('teacher_id') border-red-500 @else border-gray-200 @enderror bg-gray-50 text-sm outline-none focus:border-[#C89B3C]"
                                    onchange="loadSubjectsAndLevels()">
                                <option value="">Choisir un enseignant</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>
                                        {{ $t->first_name }} {{ $t->last_name }} — {{ $t->speciality }}
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Matière -->
                        <div>
                            <label for="subject_id" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
                                Matière <span class="text-red-500">*</span>
                            </label>
                            <select id="subject_id" name="subject_id" required
                                    class="w-full px-4 py-3 rounded-xl border-2 @error('subject_id') border-red-500 @else border-gray-200 @enderror bg-gray-50 text-sm outline-none focus:border-[#C89B3C]">
                                <option value="">Sélectionner un enseignant d'abord</option>
                            </select>
                            @error('subject_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Niveau -->
                        <div>
                            <label for="level" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
                                Niveau <span class="text-red-500">*</span>
                            </label>
                            <select id="level" name="level" required
                                    class="w-full px-4 py-3 rounded-xl border-2 @error('level') border-red-500 @else border-gray-200 @enderror bg-gray-50 text-sm outline-none focus:border-[#C89B3C]">
                                <option value="">Sélectionner un enseignant d'abord</option>
                            </select>
                            @error('level')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Mode -->
                        <div>
                            <label for="mode" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
                                Mode <span class="text-red-500">*</span>
                            </label>
                            <select id="mode" name="mode" required
                                    class="w-full px-4 py-3 rounded-xl border-2 @error('mode') border-red-500 @else border-gray-200 @enderror bg-gray-50 text-sm outline-none focus:border-[#C89B3C]">
                                <option value="normal" {{ old('mode') == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="special" {{ old('mode') == 'special' ? 'selected' : '' }}>Spécial</option>
                                <option value="vip" {{ old('mode') == 'vip' ? 'selected' : '' }}>VIP</option>
                            </select>
                            @error('mode')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════ -->
                <!-- TARIF -->
                <!-- ═══════════════════════════════════════ -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-extrabold text-[#0B2A55] mb-5 flex items-center gap-2">
                        💰 Tarif
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Type de facturation -->
                        <div>
                            <label for="billing_type" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
                                Type de facturation <span class="text-red-500">*</span>
                            </label>
                            <select id="billing_type" name="billing_type" required
                                    class="w-full px-4 py-3 rounded-xl border-2 @error('billing_type') border-red-500 @else border-gray-200 @enderror bg-gray-50 text-sm outline-none focus:border-[#C89B3C]">
                                <option value="monthly" {{ old('billing_type') == 'monthly' ? 'selected' : '' }}>Mensuel</option>
                                <option value="per_session" {{ old('billing_type') == 'per_session' ? 'selected' : '' }}>Par séance</option>
                            </select>
                            @error('billing_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Prix étudiant -->
                        <div>
                            <label for="student_price" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
                                Prix étudiant (DA) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="student_price" name="student_price" value="{{ old('student_price') }}" step="100" min="0"
                                   class="w-full px-4 py-3 rounded-xl border-2 @error('student_price') border-red-500 @else border-gray-200 @enderror bg-gray-50 text-sm outline-none focus:border-[#C89B3C]"
                                   required>
                            @error('student_price')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Part enseignant -->
                        <div>
                            <label for="teacher_share" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
                                Part enseignant (DA) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="teacher_share" name="teacher_share" value="{{ old('teacher_share') }}" step="100" min="0"
                                   class="w-full px-4 py-3 rounded-xl border-2 @error('teacher_share') border-red-500 @else border-gray-200 @enderror bg-gray-50 text-sm outline-none focus:border-[#C89B3C]"
                                   required>
                            @error('teacher_share')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Part académie (auto-calculée) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
                                Part académie (DA)
                            </label>
                            <input type="hidden" name="academy_share" id="academy_share" value="0">
                            <div id="academy_share_display"
                                 class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-gray-100 text-sm font-bold text-[#0B2A55]">
                                0 DA
                            </div>
                            @error('academy_share')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date effective -->
                        <div>
                            <label for="effective_from" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
                                Date d'effet <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="effective_from" name="effective_from" value="{{ old('effective_from', date('Y-m-d')) }}"
                                   class="w-full px-4 py-3 rounded-xl border-2 @error('effective_from') border-red-500 @else border-gray-200 @enderror bg-gray-50 text-sm outline-none focus:border-[#C89B3C]"
                                   required>
                            @error('effective_from')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <p class="text-xs text-gray-400 mt-4">
                        Prix étudiant = Part enseignant + Part académie. L'application vérifie cette égalité.
                    </p>
                </div>

                <!-- ═══════════════════════════════════════ -->
                <!-- CRÉNEAUX -->
                <!-- ═══════════════════════════════════════ -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6"
                     x-data="scheduleManager()">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-extrabold text-[#0B2A55] flex items-center gap-2">
                            📅 Créneaux horaires
                        </h3>
                        <button type="button" x-on:click="addSchedule()"
                                class="inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-[#C89B3C] text-white text-xs font-bold hover:bg-[#b8912e] transition">
                            ＋ Ajouter
                        </button>
                    </div>

                    <template x-for="(schedule, index) in schedules" :key="index">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 p-4 rounded-xl bg-gray-50 border border-gray-200 relative">
                            <!-- Jour -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Jour</label>
                                <select x-model="schedule.day" :name="'schedules['+index+'][day]'" required
                                        class="w-full px-3 py-2 rounded-lg border-2 border-gray-200 bg-white text-sm outline-none focus:border-[#C89B3C]">
                                    <option value="Dimanche">Dimanche</option>
                                    <option value="Lundi">Lundi</option>
                                    <option value="Mardi">Mardi</option>
                                    <option value="Mercredi">Mercredi</option>
                                    <option value="Jeudi">Jeudi</option>
                                    <option value="Vendredi">Vendredi</option>
                                    <option value="Samedi">Samedi</option>
                                </select>
                            </div>
                            <!-- Début -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Début</label>
                                <input type="time" x-model="schedule.start_time" :name="'schedules['+index+'][start_time]'" required
                                       class="w-full px-3 py-2 rounded-lg border-2 border-gray-200 bg-white text-sm outline-none focus:border-[#C89B3C]">
                            </div>
                            <!-- Fin -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Fin</label>
                                <input type="time" x-model="schedule.end_time" :name="'schedules['+index+'][end_time]'" required
                                       class="w-full px-3 py-2 rounded-lg border-2 border-gray-200 bg-white text-sm outline-none focus:border-[#C89B3C]">
                            </div>
                            <!-- Salle -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Salle (optionnel)</label>
                                <input type="text" x-model="schedule.room" :name="'schedules['+index+'][room]'"
                                       class="w-full px-3 py-2 rounded-lg border-2 border-gray-200 bg-white text-sm outline-none focus:border-[#C89B3C]"
                                       placeholder="Ex : Salle 01">
                            </div>
                            <!-- Supprimer -->
                            <button type="button" x-on:click="removeSchedule(index)"
                                    class="absolute top-2 right-2 w-7 h-7 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs hover:bg-red-200 transition"
                                    title="Supprimer">
                                ✕
                            </button>
                        </div>
                    </template>

                    <p x-show="schedules.length === 0" class="text-gray-400 text-sm text-center py-4">
                        Aucun créneau. Cliquez sur "＋ Ajouter" pour commencer.
                    </p>
                </div>

                <!-- ═══════════════════════════════════════ -->
                <!-- BOUTONS -->
                <!-- ═══════════════════════════════════════ -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('groups.index') }}"
                       class="px-6 py-3 rounded-xl bg-gray-100 text-gray-700 font-bold text-sm hover:bg-gray-200 transition">
                        Annuler
                    </a>
                    <button type="submit"
                            class="px-8 py-3 rounded-xl bg-[#0B2A55] text-white font-bold text-sm shadow-md border-b-4 border-[#C89B3C] hover:bg-[#061A33] hover:-translate-y-0.5 transition">
                        Enregistrer le groupe
                    </button>
                </div>
            </form>

        </div>
    </div>

    @push('scripts')
    <script>
        function scheduleManager() {
            return {
                schedules: [{ day: 'Dimanche', start_time: '09:00', end_time: '10:30', room: '' }],
                addSchedule() {
                    this.schedules.push({ day: 'Dimanche', start_time: '09:00', end_time: '10:30', room: '' });
                },
                removeSchedule(index) {
                    this.schedules.splice(index, 1);
                }
            };
        }

        function loadSubjectsAndLevels() {
            const teacherId = document.getElementById('teacher_id').value;
            const subjectSelect = document.getElementById('subject_id');
            const levelSelect = document.getElementById('level');

            if (!teacherId) {
                subjectSelect.innerHTML = '<option value="">Sélectionner un enseignant d\'abord</option>';
                levelSelect.innerHTML = '<option value="">Sélectionner un enseignant d\'abord</option>';
                return;
            }

            fetch('/teachers/' + teacherId + '/subjects-json')
                .then(r => r.json())
                .then(data => {
                    subjectSelect.innerHTML = '<option value="">Choisir une matière</option>';
                    data.forEach(s => {
                        subjectSelect.innerHTML += '<option value="' + s.id + '">' + s.name + '</option>';
                    });
                });

            fetch('/teachers/' + teacherId + '/levels-json')
                .then(r => r.json())
                .then(data => {
                    levelSelect.innerHTML = '<option value="">Choisir un niveau</option>';
                    data.forEach(l => {
                        levelSelect.innerHTML += '<option value="' + l + '">' + l + '</option>';
                    });
                });
        }

        function updateAcademyShare() {
            const price = parseFloat(document.getElementById('student_price').value) || 0;
            const share = parseFloat(document.getElementById('teacher_share').value) || 0;
            const academy = price - share;
            document.getElementById('academy_share').value = academy;
            document.getElementById('academy_share_display').textContent = academy.toLocaleString('fr-FR') + ' DA';
            document.getElementById('academy_share_display').classList.toggle('text-red-600', academy < 0);
            document.getElementById('academy_share_display').classList.toggle('text-[#0B2A55]', academy >= 0);
        }

        document.getElementById('student_price').addEventListener('input', updateAcademyShare);
        document.getElementById('teacher_share').addEventListener('input', updateAcademyShare);
    </script>
    @endpush

</x-app-layout>
