<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('groups.index') }}"
                   class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition">
                    ←
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-extrabold text-[#0B2A55] dark:text-white">
                            {{ $group->name }}
                        </h2>
                        @if($group->is_active)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Actif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inactif
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $group->subject->name }} • {{ $group->teacher->first_name }} {{ $group->teacher->last_name }} • {{ $group->level }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('groups.edit', $group) }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-amber-500 text-white font-bold text-sm hover:bg-amber-600 transition">
                    ✏️ Modifier
                </a>
                <form action="{{ route('groups.toggle-status', $group) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl {{ $group->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} text-white font-bold text-sm transition">
                        {{ $group->is_active ? '🔴 Désactiver' : '🟢 Activer' }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen py-10 bg-gradient-to-br from-gray-50 via-white to-blue-50 dark:from-gray-900 dark:to-gray-800">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- ═══════════════════════════════════════ -->
            <!-- DÉTAILS -->
            <!-- ═══════════════════════════════════════ -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <!-- Enseignant -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 text-center">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mx-auto mb-3 text-2xl">👨‍🏫</div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Enseignant</p>
                    <p class="font-extrabold text-[#0B2A55]">{{ $group->teacher->first_name }} {{ $group->teacher->last_name }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $group->teacher->speciality }}</p>
                </div>

                <!-- Matière -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 text-center">
                    <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center mx-auto mb-3 text-2xl">📚</div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Matière</p>
                    <p class="font-extrabold text-[#0B2A55]">{{ $group->subject->name }}</p>
                </div>

                <!-- Niveau -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 text-center">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center mx-auto mb-3 text-2xl">🎓</div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Niveau</p>
                    <p class="font-extrabold text-[#0B2A55]">{{ $group->level }}</p>
                </div>

                <!-- Mode -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 text-center">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center mx-auto mb-3 text-2xl">
                        @if($group->mode === 'vip') 🌟 @elseif($group->mode === 'special') ⭐ @else 📘 @endif
                    </div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Mode</p>
                    <p class="font-extrabold text-[#0B2A55]">{{ ucfirst($group->mode) }}</p>
                </div>

            </div>

            <!-- ═══════════════════════════════════════ -->
            <!-- TARIF ACTUEL -->
            <!-- ═══════════════════════════════════════ -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-extrabold text-[#0B2A55] mb-5 flex items-center gap-2">
                    💰 Tarif actuel
                </h3>

                @if($group->currentTariff)
                    @php $t = $group->currentTariff; @endphp
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-center">
                            <p class="text-xs font-bold uppercase tracking-wider text-green-600 mb-1">Prix étudiant</p>
                            <p class="text-xl font-extrabold text-green-800">{{ number_format($t->student_price, 0, ',', ' ') }} DA</p>
                            <p class="text-xs text-green-600 mt-1">{{ $t->billing_type === 'monthly' ? '/mois' : '/séance' }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-center">
                            <p class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-1">Part enseignant</p>
                            <p class="text-xl font-extrabold text-blue-800">{{ number_format($t->teacher_share, 0, ',', ' ') }} DA</p>
                        </div>
                        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-center">
                            <p class="text-xs font-bold uppercase tracking-wider text-amber-600 mb-1">Part académie</p>
                            <p class="text-xl font-extrabold text-amber-800">{{ number_format($t->academy_share, 0, ',', ' ') }} DA</p>
                        </div>
                        <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-center">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Date d'effet</p>
                            <p class="text-sm font-bold text-gray-700">{{ \Carbon\Carbon::parse($t->effective_from)->format('d/m/Y') }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-gray-400 text-center py-4">Aucun tarif défini.</p>
                @endif
            </div>

            <!-- ═══════════════════════════════════════ -->
            <!-- CRÉNEAUX -->
            <!-- ═══════════════════════════════════════ -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-extrabold text-[#0B2A55] mb-5 flex items-center gap-2">
                    📅 Créneaux horaires
                </h3>

                @if($group->schedules->count())
                    <div class="space-y-3">
                        @foreach($group->schedules as $s)
                            <div class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 border border-gray-200">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                             {{ in_array($s->day, ['Dimanche','Mardi','Jeudi','Samedi']) ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $s->day }}
                                </span>
                                <span class="font-bold text-[#0B2A55]">
                                    {{ substr($s->start_time, 0, 5) }} → {{ substr($s->end_time, 0, 5) }}
                                </span>
                                @if($s->room)
                                    <span class="text-sm text-gray-500">
                                        📍 {{ $s->room }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400 text-center py-4">Aucun créneau défini.</p>
                @endif
            </div>

            <!-- ═══════════════════════════════════════ -->
            <!-- HISTORIQUE TARIFS -->
            <!-- ═══════════════════════════════════════ -->
            @if($group->tariffs->count() > 1)
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-extrabold text-[#0B2A55] mb-5 flex items-center gap-2">
                        📊 Historique des tarifs
                    </h3>
                    <div class="space-y-3">
                        @foreach($group->tariffs as $tariff)
                            <div class="flex items-center gap-4 p-4 rounded-xl {{ $tariff->is_active ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' }}">
                                <span class="font-bold {{ $tariff->is_active ? 'text-green-800' : 'text-gray-600' }}">
                                    {{ number_format($tariff->student_price, 0, ',', ' ') }} DA
                                </span>
                                <span class="text-xs {{ $tariff->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $tariff->billing_type === 'monthly' ? '/mois' : '/séance' }}
                                </span>
                                <span class="text-xs text-gray-400">
                                    du {{ \Carbon\Carbon::parse($tariff->effective_from)->format('d/m/Y') }}
                                    @if($tariff->effective_to)
                                        au {{ \Carbon\Carbon::parse($tariff->effective_to)->format('d/m/Y') }}
                                    @else
                                        — en cours
                                    @endif
                                </span>
                                @if($tariff->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-200 text-green-800">actuel</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- ═══════════════════════════════════════ -->
            <!-- FOOTER -->
            <!-- ═══════════════════════════════════════ -->
            <div class="text-center">
                <p class="text-xs text-gray-400">
                    Année scolaire : {{ $group->schoolYear->name }} •
                    Créé le {{ $group->created_at->format('d/m/Y à H:i') }}
                </p>
            </div>

        </div>
    </div>

</x-app-layout>
