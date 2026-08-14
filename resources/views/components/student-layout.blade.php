<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        {{ config('app.name', 'Académie El Tafawok') }}
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-800">

    {{-- ================= NAVBAR ÉLÈVE ================= --}}

    <nav class="bg-white border-b border-gray-200 shadow-sm">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex justify-between items-center h-20">

                {{-- Logo / Nom académie --}}

                <div class="flex items-center gap-3">

                    <div
                        class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-xl"
                        style="background-color:#0B2A55;"
                    >
                        🎓
                    </div>

                    <div>
                        <div class="font-bold text-xl" style="color:#0B2A55;">
                            Académie
                        </div>

                        <div class="text-sm" style="color:#C89B3C;">
                            El Tafawok
                        </div>
                    </div>

                </div>


                {{-- Menu étudiant --}}

                <div class="hidden md:flex items-center gap-6">

                    <a
                        href="{{ route('student.dashboard') }}"
                        class="font-semibold text-sm"
                        style="color:#0B2A55;"
                    >
                        🏠 Mon espace
                    </a>

                    <span class="text-gray-300">|</span>

                    <span class="text-sm text-gray-600">
                        👤
                        {{ Auth::user()->name }}
                    </span>

                </div>


                {{-- Déconnexion --}}

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-white shadow-sm"
                        style="background-color:#0B2A55;"
                    >
                        Déconnexion
                    </button>
                </form>

            </div>

        </div>

    </nav>


    {{-- ================= HEADER ================= --}}

    @isset($header)

        <header class="bg-white border-b border-gray-200">

            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

                {{ $header }}

            </div>

        </header>

    @endisset


    {{-- ================= PAGE ================= --}}

    <main>

        {{ $slot }}

    </main>

</body>

</html>