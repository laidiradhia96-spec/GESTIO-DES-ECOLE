<nav class="bg-white border-b border-gray-200">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between h-20">

            {{-- Logo / Academy --}}

            <div class="flex items-center">

                <a href="{{ route('student.dashboard') }}"
                   class="flex items-center gap-3">

                    <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                         style="background-color:#0B2A55;">

                        <span class="text-2xl">🎓</span>

                    </div>

                    <div>

                        <div class="font-bold text-xl"
                             style="color:#0B2A55;">

                            Académie

                        </div>

                        <div class="text-sm"
                             style="color:#C89B3C;">

                            El Tafawok

                        </div>

                    </div>

                </a>

            </div>


            {{-- Navigation étudiant --}}

            <div class="hidden sm:flex items-center gap-8">

                <a href="{{ route('student.dashboard') }}"
                   class="text-sm font-semibold text-gray-700 hover:text-blue-900">

                    🏠 Accueil

                </a>

                <a href="{{ route('student.subjects') }}"
                   class="text-sm font-semibold text-gray-700 hover:text-blue-900">

                    📚 Mes matières

                </a>

                <a href="{{ route('student.attendances') }}"
                   class="text-sm font-semibold text-gray-700 hover:text-blue-900">

                    📋 Mes présences

                </a>

            </div>


            {{-- Compte étudiant --}}

            <div class="flex items-center">

                <div class="flex items-center gap-4">

                    <div class="hidden sm:block text-right">

                        <div class="text-sm font-semibold text-gray-800">

                            {{ Auth::user()->name }}

                        </div>

                        <div class="text-xs text-gray-500">

                            Élève

                        </div>

                    </div>


                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold"
                         style="background-color:#0B2A55;">

                        🎓

                    </div>


                    {{-- Logout --}}

                    <form method="POST" action="{{ route('logout') }}">

                        @csrf

                        <button type="submit"
                                class="text-sm font-semibold text-red-600 hover:text-red-800">

                            Déconnexion

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</nav>