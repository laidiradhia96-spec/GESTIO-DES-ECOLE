<x-app-layout>

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}

    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div>

                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Espace Élève
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Bienvenue dans votre espace personnel
                </p>

            </div>

        </div>

    </x-slot>


    {{-- ========================================================= --}}
    {{-- PAGE --}}
    {{-- ========================================================= --}}

    <div class="py-10 bg-gray-100 dark:bg-gray-900 min-h-screen">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- ================================================= --}}
            {{-- BIENVENUE --}}
            {{-- ================================================= --}}

            <div
                class="rounded-3xl p-8 mb-8 text-white shadow-lg"
                style="background: linear-gradient(135deg, #0B2A55, #163F73);"
            >

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">

                    <div>

                        <p class="text-blue-200 text-sm mb-2">
                            👋 Bienvenue
                        </p>

                        <h1 class="text-3xl md:text-4xl font-bold">
                            {{ $student->first_name }}
                            {{ $student->last_name }}
                        </h1>

                        <p class="mt-3 text-blue-100">
                            Niveau :
                            <span class="font-semibold">
                                {{ $student->level }}
                            </span>
                        </p>

                    </div>

                    <div class="text-6xl">
                        🎓
                    </div>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- STATISTIQUES --}}
            {{-- ================================================= --}}

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">


                {{-- MATIÈRES --}}

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm text-gray-500">
                                Mes matières
                            </p>

                            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">
                                {{ $subjectsCount }}
                            </p>

                        </div>

                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-xl"
                            style="background-color:#0B2A55;"
                        >
                            📚
                        </div>

                    </div>

                </div>


                {{-- PRÉSENCES --}}

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm text-gray-500">
                                Présences
                            </p>

                            <p class="text-3xl font-bold text-green-600 mt-2">
                                {{ $presentCount }}
                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center text-xl">
                            ✅
                        </div>

                    </div>

                </div>


                {{-- ABSENCES --}}

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm text-gray-500">
                                Absences
                            </p>

                            <p class="text-3xl font-bold text-red-600 mt-2">
                                {{ $absentCount }}
                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center text-xl">
                            ❌
                        </div>

                    </div>

                </div>


                {{-- STATUT --}}

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm text-gray-500">
                                Statut
                            </p>

                            <p class="text-lg font-bold text-green-600 mt-2">
                                Élève actif
                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-xl">
                            🎓
                        </div>

                    </div>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- CONTENU PRINCIPAL --}}
            {{-- ================================================= --}}

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">


                {{-- ================================================= --}}
                {{-- MES MATIÈRES --}}
                {{-- ================================================= --}}

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">

                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">

                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            📚 Mes matières
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            Vos matières, enseignants et statut de paiement
                        </p>

                    </div>


                    @if ($enrollments->count() > 0)

                        <div class="divide-y divide-gray-200 dark:divide-gray-700">

                            @foreach ($enrollments as $enrollment)

                                <div class="p-5 hover:bg-gray-50 dark:hover:bg-gray-700 transition">

                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">


                                        {{-- ================================= --}}
                                        {{-- MATIÈRE + ENSEIGNANT --}}
                                        {{-- ================================= --}}

                                        <div>

                                            <h4 class="font-semibold text-gray-800 dark:text-gray-100">

                                                {{ $enrollment->subject->name }}

                                            </h4>


                                            @if ($enrollment->teacher)

                                                <p class="text-sm text-gray-500 mt-1">

                                                    👨‍🏫

                                                    {{ $enrollment->teacher->first_name }}
                                                    {{ $enrollment->teacher->last_name }}

                                                </p>

                                            @endif

                                        </div>


                                        {{-- ================================= --}}
                                        {{-- STATUTS --}}
                                        {{-- ================================= --}}

                                        <div class="flex flex-col items-end gap-2">


                                            {{-- ============================== --}}
                                            {{-- STATUT INSCRIPTION --}}
                                            {{-- ============================== --}}

                                            @if ($enrollment->status === 'active')

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">

                                                    🟢 Actif

                                                </span>

                                            @else

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">

                                                    🔴 Inactif

                                                </span>

                                            @endif


                                            {{-- ============================== --}}
                                            {{-- TYPE ABONNEMENT --}}
                                            {{-- ============================== --}}

                                            @if ($enrollment->payment_type === 'vip')

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">

                                                    ⭐ VIP

                                                </span>

                                            @elseif ($enrollment->payment_type === 'monthly')

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">

                                                    📅 Mensuel

                                                </span>

                                            @endif


                                           {{-- ============================== --}}
{{-- STATUT PAIEMENT --}}
{{-- ============================== --}}

@if ($enrollment->payment_status === 'paid')

    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
        ✅ {{ $enrollment->payment_label ?? 'Payé' }}
    </span>

@elseif ($enrollment->payment_status === 'unpaid')

    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
        ⚠️ {{ $enrollment->payment_label ?? 'Non payé' }}
    </span>

@else

    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
        ℹ️ {{ $enrollment->payment_label ?? 'Paiement non défini' }}
    </span>

@endif


                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="p-8 text-center text-gray-500">

                            Aucune matière enregistrée.

                        </div>

                    @endif

                </div>



                {{-- ================================================= --}}
                {{-- SLIDER ANNONCES --}}
                {{-- ================================================= --}}

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
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden"
                >


                    {{-- HEADER ANNONCES --}}

                    <div
                        class="p-6 text-white"
                        style="background: linear-gradient(135deg, #0B2A55, #163F73);"
                    >

                        <div class="flex items-center justify-between">

                            <div>

                                <h3 class="text-lg font-bold">
                                    📢 Annonces
                                </h3>

                                <p class="text-sm text-blue-100 mt-1">
                                    Informations de l'académie
                                </p>

                            </div>

                            <div class="text-3xl">
                                🔔
                            </div>

                        </div>

                    </div>


                    {{-- CONTENU SLIDER --}}

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


                                        {{-- TYPE --}}

                                        <div class="flex items-center gap-2 mb-4">

                                            @if ($announcement->type === 'important')

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">

                                                    🔴 Important

                                                </span>

                                            @elseif ($announcement->type === 'warning')

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">

                                                    🟠 Attention

                                                </span>

                                            @else

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">

                                                    🔵 Information

                                                </span>

                                            @endif

                                        </div>


                                        {{-- TITRE --}}

                                        <h4 class="text-xl font-bold text-gray-800 dark:text-gray-100">

                                            {{ $announcement->title }}

                                        </h4>


                                        {{-- CONTENU --}}

                                        <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">

                                            {{ $announcement->content }}

                                        </p>


                                        {{-- DATE --}}

                                        @if ($announcement->published_at)

                                            <p class="mt-4 text-xs text-gray-400">

                                                📅 {{ $announcement->published_at->format('d/m/Y') }}

                                            </p>

                                        @endif


                                    </div>

                                @endforeach

                            </div>


                            {{-- NAVIGATION --}}

                            <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">


                                {{-- PREVIOUS --}}

                                <button
                                    type="button"

                                    @click="active = active > 0 ? active - 1 : {{ $announcements->count() - 1 }}"

                                    class="w-9 h-9 rounded-full flex items-center justify-center text-white transition hover:opacity-80"

                                    style="background-color:#0B2A55;"
                                >
                                    ←
                                </button>


                                {{-- DOTS --}}

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


                                {{-- NEXT --}}

                                <button
                                    type="button"

                                    @click="active = active < {{ $announcements->count() - 1 }}
                                        ? active + 1
                                        : 0"

                                    class="w-9 h-9 rounded-full flex items-center justify-center text-white transition hover:opacity-80"

                                    style="background-color:#0B2A55;"
                                >
                                    →
                                </button>


                            </div>

                        @else

                            {{-- AUCUNE ANNONCE --}}

                            <div class="min-h-[220px] flex flex-col items-center justify-center text-center">

                                <div class="text-5xl mb-4">
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



            {{-- ================================================= --}}
            {{-- DERNIÈRES PRÉSENCES --}}
            {{-- ================================================= --}}

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm mt-6 overflow-hidden">


                <div class="p-6 border-b border-gray-200 dark:border-gray-700">

                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">

                        📋 Mes dernières présences

                    </h3>

                </div>


                @if ($attendances->count() > 0)

                    <div class="overflow-x-auto">

                        <table class="w-full">


                            <thead style="background-color:#0B2A55;">

                                <tr class="text-white text-sm">


                                    <th class="px-6 py-4 text-left">
                                        Matière
                                    </th>


                                    <th class="px-6 py-4 text-left">
                                        Enseignant
                                    </th>


                                    <th class="px-6 py-4 text-center">
                                        Date
                                    </th>


                                    <th class="px-6 py-4 text-center">
                                        Statut
                                    </th>


                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">


                                @foreach ($attendances as $attendance)

                                    <tr>


                                        {{-- MATIÈRE --}}

                                        <td class="px-6 py-4">

                                            {{ $attendance->subject->name }}

                                        </td>


                                        {{-- ENSEIGNANT --}}

                                        <td class="px-6 py-4">

                                            @if ($attendance->teacher)

                                                {{ $attendance->teacher->first_name }}
                                                {{ $attendance->teacher->last_name }}

                                            @else

                                                —

                                            @endif

                                        </td>


                                        {{-- DATE --}}

                                        <td class="px-6 py-4 text-center">

                                            {{ $attendance->date->format('d/m/Y') }}

                                        </td>


                                        {{-- STATUT --}}

                                        <td class="px-6 py-4 text-center">


                                            @if ($attendance->status === 'present')

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">

                                                    🟢 Présent

                                                </span>

                                            @elseif ($attendance->status === 'absent')

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">

                                                    🔴 Absent

                                                </span>

                                            @elseif ($attendance->status === 'late')

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">

                                                    🟠 Retard

                                                </span>

                                            @else

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">

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

                    <div class="p-8 text-center text-gray-500">

                        Aucune présence enregistrée.

                    </div>

                @endif

            </div>


        </div>

    </div>

</x-app-layout>