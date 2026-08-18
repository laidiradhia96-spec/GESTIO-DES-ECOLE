<x-app-layout>

    {{-- HEADER --}}
    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div class="flex items-center gap-3">

                <div class="w-11 h-11 rounded-xl
                            bg-[#0B2A55]
                            flex items-center justify-center
                            text-white text-xl shadow-sm">

                    🗓️

                </div>

                <div>

                    <h2 class="text-2xl font-extrabold text-[#0B2A55]">
                        Année scolaire {{ $schoolYear->name }}
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Détails et données liées à l'année
                    </p>

                </div>

            </div>


            <div class="flex items-center gap-2">

                <a href="{{ route('school-years.index') }}"
                   class="inline-flex items-center gap-2
                          px-5 py-3
                          rounded-xl
                          bg-gray-100
                          text-gray-700
                          font-bold
                          hover:bg-gray-200
                          transition">

                    ← Retour

                </a>

                <a href="{{ route('school-years.edit', $schoolYear) }}"
                   class="inline-flex items-center gap-2
                          px-5 py-3
                          rounded-xl
                          bg-[#2563EB]
                          hover:bg-[#1D4ED8]
                          text-white
                          font-bold
                          shadow-sm
                          transition">

                    ✏️ Modifier

                </a>

            </div>

        </div>

    </x-slot>


    {{-- MAIN --}}
    <div class="min-h-screen bg-gray-50 py-8">

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- SUCCESS --}}
            @if(session('success'))

                <div class="mb-6 flex items-center gap-3
                            rounded-xl
                            border border-green-200
                            bg-green-50
                            px-5 py-4
                            text-green-700">

                    <span class="text-xl">✓</span>

                    <span class="font-semibold">
                        {{ session('success') }}
                    </span>

                </div>

            @endif


            {{-- ERROR --}}
            @if(session('error'))

                <div class="mb-6 flex items-center gap-3
                            rounded-xl
                            border border-red-200
                            bg-red-50
                            px-5 py-4
                            text-red-700">

                    <span class="text-xl">⚠</span>

                    <span class="font-semibold">
                        {{ session('error') }}
                    </span>

                </div>

            @endif


            {{-- INFO CARD --}}
            <div class="bg-white rounded-2xl
                        border border-gray-200
                        shadow-sm
                        overflow-hidden
                        mb-8">

                <div class="px-6 py-5 border-b border-gray-100
                            flex items-center justify-between">

                    <h3 class="text-xl font-extrabold text-[#0B2A55]">
                        Informations
                    </h3>

                    @if($schoolYear->is_current)

                        <span class="inline-flex items-center gap-1.5
                                     px-3 py-1.5
                                     rounded-full
                                     bg-green-50
                                     text-green-700
                                     text-xs
                                     font-extrabold">

                            <span class="w-2 h-2 rounded-full bg-green-500"></span>

                            Année courante

                        </span>

                    @else

                        <form method="POST"
                              action="{{ route('school-years.set-current', $schoolYear) }}">

                            @csrf

                            <button type="submit"
                                    class="inline-flex items-center gap-2
                                           px-4 py-2
                                           rounded-xl
                                           bg-green-50
                                           hover:bg-green-100
                                           text-green-700
                                           font-bold
                                           text-sm
                                           transition">

                                ✅ Définir comme courante

                            </button>

                        </form>

                    @endif

                </div>

                <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-3 gap-6">

                    <div>

                        <p class="text-xs font-extrabold uppercase tracking-wider text-gray-400">
                            Nom
                        </p>

                        <p class="mt-1 font-extrabold text-[#0B2A55]">
                            {{ $schoolYear->name }}
                        </p>

                    </div>

                    <div>

                        <p class="text-xs font-extrabold uppercase tracking-wider text-gray-400">
                            Date de début
                        </p>

                        <p class="mt-1 font-bold text-gray-700">
                            {{ $schoolYear->start_date->format('d/m/Y') }}
                        </p>

                    </div>

                    <div>

                        <p class="text-xs font-extrabold uppercase tracking-wider text-gray-400">
                            Date de fin
                        </p>

                        <p class="mt-1 font-bold text-gray-700">
                            {{ $schoolYear->end_date->format('d/m/Y') }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- COUNTS CARD --}}
            <div class="bg-white rounded-2xl
                        border border-gray-200
                        shadow-sm
                        overflow-hidden">

                <div class="px-6 py-5 border-b border-gray-100">

                    <h3 class="text-xl font-extrabold text-[#0B2A55]">
                        Données liées
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Une année contenant des données ne peut pas être supprimée.
                    </p>

                </div>

                <div class="px-6 py-5">

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                        @foreach([
                            'attendances' => ['📋', 'Présences', $counts['attendances']],
                            'enrollments' => ['📝', 'Inscriptions', $counts['enrollments']],
                            'payments' => ['💳', 'Paiements', $counts['payments']],
                            'class_sessions' => ['🗓️', 'Séances', $counts['class_sessions']],
                            'payment_signalements' => ['🚨', 'Signalements', $counts['payment_signalements']],
                            'unpaid_signalements' => ['⚠️', 'Signalements d\'impayés', $counts['unpaid_signalements']],
                            'payment_schedules' => ['📅', 'Échéanciers', $counts['payment_schedules']],
                        ] as $item)

                            <div class="flex items-center gap-3
                                        rounded-xl
                                        border border-gray-100
                                        bg-gray-50
                                        p-4">

                                <div class="w-11 h-11 rounded-xl
                                            bg-white
                                            border border-gray-100
                                            flex items-center
                                            justify-center
                                            text-xl">

                                    {{ $item[0] }}

                                </div>

                                <div>

                                    <p class="text-xs font-semibold text-gray-500">
                                        {{ $item[1] }}
                                    </p>

                                    <p class="text-xl font-extrabold text-[#0B2A55]">
                                        {{ $item[2] }}
                                    </p>

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>
