<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center gap-4">

            <div class="w-14 h-14 rounded-2xl
                        bg-[#0B2A55]
                        flex items-center justify-center
                        shadow-lg">

                <span class="text-3xl">🗓️</span>

            </div>

            <div>

                <h2 class="text-2xl font-extrabold
                           text-[#0B2A55]
                           dark:text-white">

                    Modifier l'année scolaire {{ $schoolYear->name }}

                </h2>

                <p class="text-sm text-gray-500 mt-1">

                    Mettre à jour la période de l'année scolaire

                </p>

            </div>

        </div>

    </x-slot>


    <div class="min-h-screen py-10
                bg-gradient-to-br
                from-gray-50 via-white to-blue-50
                dark:from-gray-900 dark:to-gray-800">

        <div class="max-w-5xl mx-auto
                    px-4 sm:px-6 lg:px-8">


            <!-- ================= ERRORS ================= -->

            @if ($errors->any())

                <div class="mb-7
                            rounded-2xl
                            border border-red-200
                            bg-red-50
                            p-5
                            shadow-sm">

                    <div class="flex items-start gap-4">

                        <div class="w-10 h-10 rounded-xl
                                    bg-red-100
                                    flex items-center
                                    justify-center
                                    text-lg">

                            ⚠️

                        </div>

                        <div>

                            <h3 class="font-extrabold
                                       text-red-800 mb-2">

                                Veuillez corriger les erreurs

                            </h3>

                            <ul class="space-y-1
                                       text-sm
                                       text-red-700">

                                @foreach ($errors->all() as $error)

                                    <li class="flex gap-2">

                                        <span>•</span>

                                        <span>{{ $error }}</span>

                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    </div>

                </div>

            @endif


            <!-- ================= FORM ================= -->

            <form method="POST"
                  action="{{ route('school-years.update', $schoolYear) }}">

                @csrf
                @method('PUT')


                <div class="bg-white dark:bg-gray-800
                            rounded-3xl
                            shadow-sm
                            border border-gray-100
                            dark:border-gray-700
                            p-6 md:p-8 mb-6">

                    <div class="flex items-center gap-4 mb-7">

                        <div class="w-12 h-12 rounded-xl
                                    bg-[#0B2A55]/10
                                    flex items-center
                                    justify-center
                                    text-xl">

                            🗓️

                        </div>

                        <div>

                            <h2 class="text-xl font-extrabold
                                       text-[#0B2A55]
                                       dark:text-white">

                                Informations de l'année

                            </h2>

                            <p class="text-sm text-gray-500 mt-1">

                                Nom et période de l'année scolaire

                            </p>

                        </div>

                    </div>


                    <div class="grid grid-cols-1
                                md:grid-cols-3
                                gap-6">


                        <!-- Nom -->

                        <div class="md:col-span-3">

                            <label class="block
                                          text-sm font-bold
                                          text-[#0B2A55]
                                          dark:text-gray-200
                                          mb-2">

                                Nom de l'année
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $schoolYear->name) }}"
                                required
                                placeholder="Ex : 2025-2026"

                                class="w-full rounded-xl
                                       border-2
                                       border-gray-200
                                       bg-gray-50
                                       px-4 py-3.5
                                       text-gray-800
                                       font-bold
                                       focus:border-[#0B2A55]
                                       focus:ring-[#0B2A55]
                                       focus:bg-white
                                       transition">

                            <p class="text-xs text-gray-400 mt-2">

                                Format : <span class="font-bold">AAAA-AAAA</span> (ex : 2028-2029)

                            </p>

                        </div>


                        <!-- Date de début -->

                        <div>

                            <label class="block
                                          text-sm font-bold
                                          text-[#0B2A55]
                                          dark:text-gray-200
                                          mb-2">

                                Date de début
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="date"
                                name="start_date"
                                value="{{ old('start_date', $schoolYear->start_date->format('Y-m-d')) }}"
                                required

                                class="w-full rounded-xl
                                       border-2
                                       border-gray-200
                                       bg-gray-50
                                       px-4 py-3.5
                                       text-gray-800
                                       focus:border-[#0B2A55]
                                       focus:ring-[#0B2A55]
                                       focus:bg-white
                                       transition">

                        </div>


                        <!-- Date de fin -->

                        <div>

                            <label class="block
                                          text-sm font-bold
                                          text-[#0B2A55]
                                          dark:text-gray-200
                                          mb-2">

                                Date de fin
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="date"
                                name="end_date"
                                value="{{ old('end_date', $schoolYear->end_date->format('Y-m-d')) }}"
                                required

                                class="w-full rounded-xl
                                       border-2
                                       border-gray-200
                                       bg-gray-50
                                       px-4 py-3.5
                                       text-gray-800
                                       focus:border-[#0B2A55]
                                       focus:ring-[#0B2A55]
                                       focus:bg-white
                                       transition">

                        </div>


                        <!-- Info -->

                        <div class="md:col-span-3
                                    rounded-xl
                                    bg-blue-50
                                    border border-blue-100
                                    p-4
                                    flex items-start gap-3">

                            <span class="text-xl">💡</span>

                            <p class="text-sm text-blue-800">

                                Le changement d'année courante se fait depuis la
                                <a href="{{ route('school-years.index') }}"
                                   class="font-bold underline">
                                    liste des années
                                </a>
                                avec le bouton « Définir comme courante ».

                            </p>

                        </div>

                    </div>

                </div>


                <!-- ================= ACTIONS ================= -->

                <div class="flex items-center gap-3">

                    <button type="submit"
                            class="px-8 py-3.5
                                   rounded-xl
                                   bg-[#0B2A55]
                                   text-white
                                   font-bold
                                   shadow-md
                                   border-b-4 border-[#C89B3C]
                                   hover:bg-[#061A33]
                                   transition">

                        Enregistrer

                    </button>

                    <a href="{{ route('school-years.show', $schoolYear) }}"
                       class="px-6 py-3.5
                              rounded-xl
                              bg-gray-100
                              text-gray-700
                              font-bold
                              hover:bg-gray-200
                              transition">

                        Annuler

                    </a>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>
