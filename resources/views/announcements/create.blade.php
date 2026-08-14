<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center gap-4">

            <div class="w-12 h-12 rounded-2xl bg-[#0B2A55]
                        flex items-center justify-center shadow-md">

                <span class="text-2xl">📢</span>

            </div>

            <div>

                <h2 class="font-extrabold text-2xl text-[#0B2A55]">
                    Ajouter une annonce
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Publier une information pour les élèves
                </p>

            </div>

        </div>

    </x-slot>


    <div class="py-10 bg-gray-50 min-h-screen">

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white rounded-3xl shadow-sm
                        border border-gray-100 overflow-hidden">

                {{-- HEADER --}}

                <div class="p-6 bg-gradient-to-r
                            from-[#0B2A55] to-[#173E73]">

                    <div class="flex items-center gap-4">

                        <div class="w-14 h-14 rounded-2xl
                                    bg-white/10
                                    flex items-center justify-center">

                            <span class="text-3xl">📢</span>

                        </div>

                        <div>

                            <h3 class="text-xl font-extrabold text-white">
                                Nouvelle annonce
                            </h3>

                            <p class="text-blue-100 text-sm mt-1">
                                Cette annonce sera visible dans l'espace élève.
                            </p>

                        </div>

                    </div>

                </div>


                {{-- FORM --}}

                <form method="POST"
                      action="{{ route('announcements.store') }}"
                      class="p-6 md:p-8">

                    @csrf


                    {{-- TITRE --}}

                    <div class="mb-6">

                        <label for="title"
                               class="block text-sm font-bold
                                      text-[#0B2A55] mb-2">

                            Titre de l'annonce

                        </label>

                        <input
                            id="title"
                            type="text"
                            name="title"
                            value="{{ old('title') }}"
                            required
                            placeholder="Ex : Nouveau programme des cours"

                            class="w-full px-4 py-4
                                   rounded-xl
                                   border-2 border-gray-200
                                   bg-gray-50
                                   outline-none
                                   transition
                                   focus:border-[#C89B3C]
                                   focus:ring-4
                                   focus:ring-[#C89B3C]/10"
                        >

                        @error('title')

                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- CONTENU --}}

                    <div class="mb-6">

                        <label for="content"
                               class="block text-sm font-bold
                                      text-[#0B2A55] mb-2">

                            Contenu de l'annonce

                        </label>

                        <textarea
                            id="content"
                            name="content"
                            rows="6"
                            required
                            placeholder="Écrivez ici le contenu de l'annonce..."

                            class="w-full px-4 py-4
                                   rounded-xl
                                   border-2 border-gray-200
                                   bg-gray-50
                                   outline-none
                                   transition
                                   resize-none
                                   focus:border-[#C89B3C]
                                   focus:ring-4
                                   focus:ring-[#C89B3C]/10"
                        >{{ old('content') }}</textarea>

                        @error('content')

                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- BUTTONS --}}

                    <div class="flex flex-col sm:flex-row
                                gap-3 pt-4">

                        <a href="{{ route('announcements.index') }}"
                           class="flex-1
                                  text-center
                                  px-6 py-4
                                  rounded-xl
                                  bg-gray-100
                                  text-gray-700
                                  font-bold
                                  hover:bg-gray-200
                                  transition">

                            Annuler

                        </a>


                        <button
                            type="submit"

                            class="flex-1
                                   px-6 py-4
                                   rounded-xl
                                   bg-[#0B2A55]
                                   hover:bg-[#061A33]
                                   text-white
                                   font-bold
                                   shadow-lg
                                   border-b-4
                                   border-[#C89B3C]
                                   transition">

                            📢 Publier l'annonce

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>