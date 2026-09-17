<x-app-layout>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                Informations de l'élève
            </h2>

            <a href="{{ route('students.index') }}"
               class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">

        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        {{ $student->first_name }} {{ $student->last_name }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <p class="text-sm text-gray-500">
                                Nom
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->last_name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Prénom
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->first_name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Date de naissance
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->date_of_birth ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Niveau
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->level }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Téléphone
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->phone ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Nom du parent
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->parent_name ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Téléphone du parent
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->parent_phone ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Adresse
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->address ?? '-' }}
                            </p>
                        </div>

                    </div>

                    {{-- ================================================= --}}
                    {{-- GROUPES D'INSCRIPTION --}}
                    {{-- ================================================= --}}

                    @php
                        $activeGroups = $student->enrollments->where('status', 'active')->map(fn ($e) => $e->group)->filter()->unique('id');
                        $inactiveGroups = $student->enrollments->where('status', '!=', 'active')->map(fn ($e) => $e->group)->filter()->unique('id');
                    @endphp

                    <div class="mt-8">
                        <h4 class="text-lg font-extrabold text-[#0B2A55] dark:text-white flex items-center gap-2 mb-4">
                            👥 Groupes d'inscription
                        </h4>

                        @if($activeGroups->count())
                            <div class="space-y-3">
                                @foreach($activeGroups as $group)
                                    <div class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 border border-gray-200">
                                        <span class="text-2xl">
                                            @if($group->mode === 'vip') 🌟
                                            @elseif($group->mode === 'special') ⭐
                                            @else 📘
                                            @endif
                                        </span>
                                        <div class="flex-1">
                                            <div class="font-extrabold text-[#0B2A55]">
                                                {{ $group->subject->name ?? '—' }}
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                {{ $group->teacher->first_name ?? '' }} {{ $group->teacher->last_name ?? '' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $group->level }} — {{ $group->name }}
                                                @php
                                                    $modeColor = match($group->mode) {
                                                        'vip' => 'bg-purple-100 text-purple-800',
                                                        'special' => 'bg-amber-100 text-amber-800',
                                                        default => 'bg-green-100 text-green-800',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $modeColor }} ml-2">
                                                    {{ ucfirst($group->mode) }}
                                                </span>
                                            </div>
                                            @if($group->currentTariff)
                                                <div class="text-sm font-bold text-[#0B2A55] mt-1">
                                                    {{ number_format($group->currentTariff->student_price, 0, ',', ' ') }} DA
                                                    {{ $group->currentTariff->billing_type === 'monthly' ? '/mois' : '/séance' }}
                                                </div>
                                            @endif
                                            @if($group->schedules->count())
                                                <div class="flex flex-wrap gap-2 mt-2">
                                                    @foreach($group->schedules as $s)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                                            {{ $s->day }} {{ substr($s->start_time, 0, 5) }}–{{ substr($s->end_time, 0, 5) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            Actif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-400 italic">Aucun groupe actif.</p>
                        @endif

                        @if($inactiveGroups->count())
                            <details class="mt-4">
                                <summary class="text-sm text-gray-400 cursor-pointer hover:text-gray-600">
                                    Groupes désactivés ({{ $inactiveGroups->count() }})
                                </summary>
                                <div class="space-y-2 mt-2">
                                    @foreach($inactiveGroups as $group)
                                        <div class="flex items-center gap-4 p-3 rounded-xl bg-gray-50 border border-gray-200 opacity-60">
                                            <div class="flex-1">
                                                <span class="font-bold text-gray-600">
                                                    {{ $group->subject->name ?? '—' }}
                                                </span>
                                                <span class="text-sm text-gray-500">
                                                    — {{ $group->name }}
                                                </span>
                                            </div>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                Inactif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    </div>

                    @if($errors->any())

                    <div class="mt-8 w-full
                                bg-red-50
                                border border-red-200
                                text-red-700
                                px-5 py-4
                                rounded-2xl">

                        @foreach($errors->all() as $error)

                            <p class="{{ !$loop->first ? 'mt-1' : '' }}">

                                ⚠️ {{ $error }}

                            </p>

                        @endforeach

                    </div>

                @endif


                <div class="mt-8 flex gap-3">

                        <a href="{{ route('attendances.archive', ['student_id' => $student->id]) }}"
                           class="bg-[#0B2A55] text-white px-5 py-2 rounded-lg hover:bg-[#061A33]">
                            📋 Historique des présences
                        </a>

                        <a href="{{ route('students.edit', $student) }}"
                           class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700">
                            Modifier
                        </a>

                        <form action="{{ route('students.destroy', $student) }}"
                              method="POST">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    onclick="return confirm('Voulez-vous supprimer cet élève ?')"
                                    class="bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700">
                                Supprimer
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>