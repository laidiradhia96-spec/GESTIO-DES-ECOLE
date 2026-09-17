
<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-8 rounded-full" style="background-color:#C89B3C;"></span>
                    <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100">
                        Espace Élève
                    </h2>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-4">
                    Bienvenue dans votre espace personnel
                </p>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HERO --}}
            <div
                class="relative overflow-hidden rounded-3xl mb-7 shadow-xl"
                style="background:linear-gradient(135deg,#0B2A55 0%,#163F73 65%,#1D4D86 100%);"
            >
                <div class="absolute -right-12 -top-12 w-56 h-56 rounded-full opacity-10 bg-white"></div>
                <div class="absolute right-24 -bottom-24 w-72 h-72 rounded-full opacity-10 bg-white"></div>

                <div class="relative p-7 sm:p-9 lg:p-10">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-7">

                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-blue-100 text-xs font-semibold mb-4">
                                <span>👋</span>
                                Bienvenue dans votre espace
                            </div>

                            <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                                {{ $student->first_name }}
                                {{ $student->last_name }}
                            </h1>

                            <div class="flex flex-wrap items-center gap-3 mt-4">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/10 text-white text-sm">
                                    🎓
                                    Niveau
                                    <strong>{{ $student->level }}</strong>
                                </span>

                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-green-400/15 text-green-100 text-sm">
                                    <span class="w-2 h-2 rounded-full bg-green-400"></span>
                                    Élève actif
                                </span>
                            </div>
                        </div>

                        <div class="hidden sm:flex w-24 h-24 lg:w-28 lg:h-28 rounded-3xl items-center justify-center bg-white/10 border border-white/10 text-6xl shadow-inner">
                            🎓
                        </div>

                    </div>
                </div>
            </div>

            {{-- STATISTICS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">

                <div class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                Mes matières
                            </p>
                            <p class="text-3xl font-extrabold text-gray-800 dark:text-white mt-2">
                                {{ $subjectsCount }}
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shadow-sm"
                             style="background-color:#0B2A55;color:white;">
                            📚
                        </div>
                    </div>
                    <div class="h-1 w-12 rounded-full mt-4" style="background-color:#C89B3C;"></div>
                </div>

                <div class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                Présences
                            </p>
                            <p class="text-3xl font-extrabold text-green-600 mt-2">
                                {{ $presentCount }}
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-xl">
                            ✅
                        </div>
                    </div>
                    <div class="h-1 w-12 rounded-full mt-4 bg-green-500"></div>
                </div>

                <div class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                Absences
                            </p>
                            <p class="text-3xl font-extrabold text-red-600 mt-2">
                                {{ $absentCount }}
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-xl">
                            ❌
                        </div>
                    </div>
                    <div class="h-1 w-12 rounded-full mt-4 bg-red-500"></div>
                </div>

                <div class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                Statut
                            </p>
                            <p class="text-lg font-extrabold text-green-600 mt-3">
                                Actif
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-xl">
                            🎓
                        </div>
                    </div>
                    <div class="h-1 w-12 rounded-full mt-4 bg-blue-600"></div>
                </div>

            </div>

            {{-- MAIN CONTENT --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- SUBJECTS --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">

                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-extrabold text-gray-800 dark:text-gray-100">
                                    📚 Mes matières
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    Vos matières, enseignants et statut de paiement
                                </p>
                            </div>

                            <span class="hidden sm:inline-flex px-3 py-1.5 rounded-xl text-xs font-bold"
                                  style="background-color:#F8F1E3;color:#8A671E;">
                                {{ $subjectsCount }} matière(s)
                            </span>
                        </div>
                    </div>

                    @if ($enrollments->count() > 0)

                        <div class="divide-y divide-gray-100 dark:divide-gray-700">

                            @foreach ($enrollments as $enrollment)

                                <div class="p-5 sm:p-6 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">

                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">

                                        <div class="min-w-0">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0"
                                                     style="background-color:#EEF3F9;">
                                                    📖
                                                </div>

                                                <div class="min-w-0">
                                                    <h4 class="font-bold text-gray-800 dark:text-gray-100 truncate">
                                                        {{ $enrollment->subject->name }}
                                                    </h4>

                                                    @if ($enrollment->teacher)
                                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                            👨‍🏫
                                                            {{ $enrollment->teacher->first_name }}
                                                            {{ $enrollment->teacher->last_name }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap sm:flex-col sm:items-end gap-2">

                                            @if ($enrollment->status === 'active')
                                                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                                    🟢 Actif
                                                </span>
                                            @else
                                                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                                    🔴 Inactif
                                                </span>
                                            @endif

                                            @if (in_array($enrollment->payment_type, ['vip', 'vip_monthly', 'vip_per_session']))
                                                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                                                    ⭐ VIP
                                                </span>
                                            @elseif (in_array($enrollment->payment_type, ['monthly', 'special_monthly']))
                                                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                                    📅 Mensuel
                                                </span>
                                            @endif

                                            @if ($enrollment->payment_status === 'paid')
                                                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                                    ✅ {{ $enrollment->payment_label ?? 'Payé' }}
                                                </span>
                                            @elseif ($enrollment->payment_status === 'unpaid')
                                                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                                    ⚠️ {{ $enrollment->payment_label ?? 'Non payé' }}
                                                </span>
                                            @else
                                                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                                    ℹ️ {{ $enrollment->payment_label ?? 'Paiement non défini' }}
                                                </span>
                                            @endif

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="p-10 text-center">
                            <div class="text-5xl mb-4">📚</div>
                            <p class="font-semibold text-gray-700 dark:text-gray-200">
                                Aucune matière enregistrée
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Vos matières apparaîtront ici.
                            </p>
                        </div>

                    @endif

                </div>

                {{-- ANNOUNCEMENTS --}}
                <div
                    x-data="{
                        active: 0,
                        timer: null,

                        start() {
                            this.timer = setInterval(() => {
                                if (this.active < {{ max($announcements->count() - 1, 0) }}) {
                                    this.active++;
                                } else {
                                    this.active = 0;
                                }
                            }, 5000);
                        }
                    }"
                    x-init="start()"
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden"
                >

                    <div class="p-6 text-white"
                         style="background:linear-gradient(135deg,#0B2A55,#163F73);">

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-blue-200">
                                    Académie El Tafawok
                                </p>
                                <h3 class="text-lg font-extrabold mt-1">
                                    📢 Annonces
                                </h3>
                                <p class="text-sm text-blue-100 mt-1">
                                    Informations de l'académie
                                </p>
                            </div>

                            <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-2xl">
                                🔔
                            </div>
                        </div>

                    </div>

                    <div class="p-6">

                        @if ($announcements->count() > 0)

                            <div class="relative min-h-[220px]">

                                @foreach ($announcements as $index => $announcement)

                                    <div
                                        x-show="active === {{ $index }}"
                                        x-transition:enter="transition ease-out duration-500"
                                        x-transition:enter-start="opacity-0 translate-x-4"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0"
                                        class="absolute inset-0"
                                    >

                                        <div class="flex items-center gap-2 mb-4">

                                            @if ($announcement->type === 'important')
                                                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                                    🔴 Important
                                                </span>
                                            @elseif ($announcement->type === 'warning')
                                                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                                                    🟠 Attention
                                                </span>
                                            @else
                                                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                                    🔵 Information
                                                </span>
                                            @endif

                                        </div>

                                        <h4 class="text-xl font-extrabold text-gray-800 dark:text-gray-100 leading-tight">
                                            {{ $announcement->title }}
                                        </h4>

                                        <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-300">
                                            {{ $announcement->content }}
                                        </p>

                                        @if ($announcement->published_at)
                                            <p class="mt-5 text-xs text-gray-400">
                                                📅 {{ $announcement->published_at->format('d/m/Y') }}
                                            </p>
                                        @endif

                                    </div>

                                @endforeach

                            </div>

                            <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">

                                <button
                                    type="button"
                                    @click="active = active > 0 ? active - 1 : {{ $announcements->count() - 1 }}"
                                    class="w-9 h-9 rounded-xl flex items-center justify-center text-white transition hover:scale-105"
                                    style="background-color:#0B2A55;"
                                >
                                    ←
                                </button>

                                <div class="flex items-center gap-2">
                                    @foreach ($announcements as $index => $announcement)
                                        <button
                                            type="button"
                                            @click="active = {{ $index }}"
                                            class="w-2.5 h-2.5 rounded-full transition"
                                            :class="active === {{ $index }}
                                                ? 'bg-blue-900 scale-125'
                                                : 'bg-gray-300'"
                                        ></button>
                                    @endforeach
                                </div>

                                <button
                                    type="button"
                                    @click="active = active < {{ $announcements->count() - 1 }}
                                        ? active + 1
                                        : 0"
                                    class="w-9 h-9 rounded-xl flex items-center justify-center text-white transition hover:scale-105"
                                    style="background-color:#0B2A55;"
                                >
                                    →
                                </button>

                            </div>

                        @else

                            <div class="min-h-[220px] flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-4xl mb-4"
                                     style="background-color:#EEF3F9;">
                                    📢
                                </div>

                                <h4 class="font-bold text-gray-700 dark:text-gray-200">
                                    Aucune annonce
                                </h4>

                                <p class="text-sm text-gray-500 mt-2">
                                    Les annonces de l'académie apparaîtront ici.
                                </p>
                            </div>

                        @endif

                    </div>

                </div>

            </div>

            {{-- ATTENDANCES --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm mt-6 overflow-hidden">

                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-extrabold text-gray-800 dark:text-gray-100">
                                📋 Mes dernières présences
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Consultez votre historique récent
                            </p>
                        </div>

                        <span class="hidden sm:inline-flex px-3 py-1.5 rounded-xl text-xs font-bold"
                              style="background-color:#EEF3F9;color:#0B2A55;">
                            Historique
                        </span>
                    </div>
                </div>

                @if ($attendances->count() > 0)

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead style="background-color:#0B2A55;">
                                <tr class="text-white text-xs uppercase tracking-wide">
                                    <th class="px-6 py-4 text-left">Matière</th>
                                    <th class="px-6 py-4 text-left">Enseignant</th>
                                    <th class="px-6 py-4 text-center">Date</th>
                                    <th class="px-6 py-4 text-center">Statut</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">

                                @foreach ($attendances as $attendance)

                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">

                                        <td class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-200">
                                            {{ $attendance->subject->name }}
                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            @if ($attendance->teacher)
                                                {{ $attendance->teacher->first_name }}
                                                {{ $attendance->teacher->last_name }}
                                            @else
                                                —
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            {{ $attendance->date->format('d/m/Y') }}
                                        </td>

                                        <td class="px-6 py-4 text-center">

                                            @if ($attendance->status === 'present')
                                                <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                                    🟢 Présent
                                                </span>
                                            @elseif ($attendance->status === 'absent')
                                                <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                                    🔴 Absent
                                                </span>
                                            @elseif ($attendance->status === 'late')
                                                <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                                                    🟠 Retard
                                                </span>
                                            @else
                                                <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                                    🔵 Justifié
                                                </span>
                                            @endif

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @else

                    <div class="p-10 text-center">
                        <div class="text-5xl mb-4">📋</div>
                        <p class="font-semibold text-gray-700 dark:text-gray-200">
                            Aucune présence enregistrée
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            Votre historique apparaîtra ici.
                        </p>
                    </div>

                @endif

            </div>

            {{-- LAST PAYMENT --}}
            <div class="mt-6 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm bg-white dark:bg-gray-800">

                <div class="p-6 text-white"
                     style="background:linear-gradient(135deg,#0B2A55,#163F73);">

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-blue-200 font-semibold">
                                Historique financier
                            </p>
                            <h3 class="text-lg font-extrabold mt-1">
                                💳 Dernier paiement
                            </h3>
                        </div>

                        <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-2xl">
                            💳
                        </div>
                    </div>

                </div>

                @if ($payments->count() > 0)

                    @php
                        $latestPayment = $payments->first();
                    @endphp

                    <div class="p-6">

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                            <div class="rounded-2xl bg-gray-50 dark:bg-gray-700/40 p-4">
                                <p class="text-xs uppercase tracking-wide font-semibold text-gray-400">
                                    Matière
                                </p>
                                <p class="font-bold text-gray-800 dark:text-gray-100 mt-2">
                                    {{ $latestPayment->subject->name ?? '—' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-gray-50 dark:bg-gray-700/40 p-4">
                                <p class="text-xs uppercase tracking-wide font-semibold text-gray-400">
                                    Période
                                </p>
                                <p class="font-bold text-gray-800 dark:text-gray-100 mt-2">
                                    {{ $latestPayment->period }}
                                </p>
                            </div>

                            <div class="rounded-2xl p-4"
                                 style="background-color:#EEF8F1;">
                                <p class="text-xs uppercase tracking-wide font-semibold text-gray-500">
                                    Montant payé
                                </p>
                                <p class="font-extrabold text-green-600 text-lg mt-2">
                                    {{ number_format($latestPayment->amount_paid, 2, ',', ' ') }}
                                    DA
                                </p>
                            </div>

                            <div class="rounded-2xl bg-gray-50 dark:bg-gray-700/40 p-4">
                                <p class="text-xs uppercase tracking-wide font-semibold text-gray-400">
                                    Date du paiement
                                </p>
                                <p class="font-bold text-gray-800 dark:text-gray-100 mt-2">
                                    {{ $latestPayment->payment_date?->format('d/m/Y') }}
                                </p>
                            </div>

                        </div>

                    </div>

                @else

                    <div class="p-10 text-center">
                        <div class="text-5xl mb-4">💳</div>
                        <p class="font-semibold text-gray-700 dark:text-gray-200">
                            Aucun paiement enregistré
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            Vos paiements apparaîtront ici.
                        </p>
                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>
