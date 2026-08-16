<x-app-layout>

    {{-- HEADER --}}
    <x-slot name="header">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="text-2xl font-extrabold text-[#0B2A55]">
                    Modifier l'annonce
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Modifier les informations de l'annonce
                </p>

            </div>

            <a href="{{ route('announcements.index') }}"
               class="inline-flex items-center gap-2
                      px-4 py-2
                      rounded-xl
                      bg-gray-100
                      hover:bg-gray-200
                      text-gray-700
                      font-bold
                      transition">

                ← Retour

            </a>

        </div>

    </x-slot>


    {{-- MAIN --}}
    <div class="min-h-screen bg-gray-50 py-10">

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white
                        rounded-2xl
                        border border-gray-200
                        shadow-sm
                        overflow-hidden">


                {{-- CARD HEADER --}}
                <div class="px-6 py-6
                            border-b border-gray-100
                            bg-[#F8FAFC]">

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12
                                    rounded-xl
                                    bg-blue-50
                                    flex items-center justify-center
                                    text-2xl">

                            📢

                        </div>

                        <div>

                            <h1 class="text-xl font-extrabold text-[#0B2A55]">
                                Modifier l'annonce
                            </h1>

                            <p class="text-sm text-gray-500 mt-1">
                                Mettez à jour les informations ci-dessous.
                            </p>

                        </div>

                    </div>

                </div>


                {{-- FORM --}}
                <form method="POST"
                      action="{{ route('announcements.update', $announcement) }}">

                    @csrf
                    @method('PUT')


                    <div class="px-6 py-8 space-y-6">


                        {{-- TITLE --}}
                        <div>

                            <label for="title"
                                   class="block text-sm font-bold text-gray-700 mb-2">

                                Titre

                            </label>

                            <input type="text"
                                   id="title"
                                   name="title"
                                   value="{{ old('title', $announcement->title) }}"
                                   required
                                   maxlength="255"
                                   class="w-full
                                          rounded-xl
                                          border border-gray-200
                                          bg-gray-50
                                          px-4 py-3
                                          text-gray-700
                                          outline-none
                                          focus:bg-white
                                          focus:border-blue-400
                                          focus:ring-2
                                          focus:ring-blue-100">

                            @error('title')

                                <p class="text-sm text-red-600 mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- CONTENT --}}
                        <div>

                            <label for="content"
                                   class="block text-sm font-bold text-gray-700 mb-2">

                                Contenu

                            </label>

                            <textarea id="content"
                                      name="content"
                                      rows="7"
                                      required
                                      class="w-full
                                             rounded-xl
                                             border border-gray-200
                                             bg-gray-50
                                             px-4 py-3
                                             text-gray-700
                                             outline-none
                                             resize-y
                                             focus:bg-white
                                             focus:border-blue-400
                                             focus:ring-2
                                             focus:ring-blue-100">{{ old('content', $announcement->content ?? $announcement->description ?? '') }}</textarea>

                            @error('content')

                                <p class="text-sm text-red-600 mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- TYPE --}}
                        <div>

                            <label for="type"
                                   class="block text-sm font-bold text-gray-700 mb-2">

                                Type

                            </label>

                            <select id="type"
                                    name="type"
                                    required
                                    class="w-full
                                           rounded-xl
                                           border border-gray-200
                                           bg-gray-50
                                           px-4 py-3
                                           text-gray-700
                                           outline-none
                                           focus:bg-white
                                           focus:border-blue-400
                                           focus:ring-2
                                           focus:ring-blue-100">

                                <option value="info"
                                    {{ old('type', $announcement->type) === 'info' ? 'selected' : '' }}>
                                    🔵 Information
                                </option>

                                <option value="important"
                                    {{ old('type', $announcement->type) === 'important' ? 'selected' : '' }}>
                                    🔴 Important
                                </option>

                                <option value="warning"
                                    {{ old('type', $announcement->type) === 'warning' ? 'selected' : '' }}>
                                    🟠 Attention
                                </option>

                            </select>

                            @error('type')

                                <p class="text-sm text-red-600 mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- ACTIVE --}}
                        <div class="rounded-xl
                                    border border-gray-200
                                    bg-gray-50
                                    p-5">

                            <label class="flex items-center gap-3 cursor-pointer">

                                <input type="checkbox"
                                       name="is_active"
                                       value="1"
                                       {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}
                                       class="w-5 h-5
                                              rounded
                                              border-gray-300
                                              text-blue-600
                                              focus:ring-blue-500">

                                <div>

                                    <p class="font-bold text-gray-700">
                                        Annonce active
                                    </p>

                                    <p class="text-sm text-gray-500 mt-1">
                                        Cette annonce sera visible lorsqu'elle est active.
                                    </p>

                                </div>

                            </label>

                        </div>

                    </div>


                    {{-- ACTIONS --}}
                    <div class="px-6 py-5
                                border-t border-gray-100
                                flex items-center
                                justify-end
                                gap-3">

                        <a href="{{ route('announcements.index') }}"
                           class="px-5 py-3
                                  rounded-xl
                                  bg-gray-100
                                  hover:bg-gray-200
                                  text-gray-700
                                  font-bold
                                  transition">

                            Annuler

                        </a>

                        <button type="submit"
                                class="px-5 py-3
                                       rounded-xl
                                       bg-[#2563EB]
                                       hover:bg-[#1D4ED8]
                                       text-white
                                       font-bold
                                       shadow-sm
                                       transition">

                            💾 Enregistrer les modifications

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>