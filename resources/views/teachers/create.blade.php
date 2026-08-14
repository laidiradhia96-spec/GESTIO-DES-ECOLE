<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center gap-4">

            <div class="w-14 h-14 rounded-2xl
                        bg-[#0B2A55]
                        flex items-center justify-center
                        shadow-lg">

                <span class="text-3xl">👨‍🏫</span>

            </div>

            <div>

                <h2 class="text-2xl font-extrabold
                           text-[#0B2A55]
                           dark:text-white">

                    Ajouter un enseignant

                </h2>

                <p class="text-sm text-gray-500 mt-1">

                    Créer un nouveau profil enseignant

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


            <!-- ================= HERO ================= -->

            <div class="relative overflow-hidden
                        rounded-3xl
                        bg-gradient-to-r
                        from-[#061A33]
                        via-[#0B2A55]
                        to-[#163E73]
                        shadow-xl
                        p-7 md:p-9 mb-7">

                <div class="absolute -right-20 -top-24
                            w-64 h-64 rounded-full
                            bg-[#C89B3C]/10">
                </div>

                <div class="absolute -right-10 -bottom-32
                            w-72 h-72 rounded-full
                            border border-[#C89B3C]/20">
                </div>


                <div class="relative z-10
                            flex items-center gap-5">

                    <div class="w-20 h-20 rounded-3xl
                                bg-white/10
                                backdrop-blur
                                border border-white/10
                                flex items-center
                                justify-center
                                text-4xl
                                shadow-lg">

                        👨‍🏫

                    </div>

                    <div>

                        <p class="text-[#C89B3C]
                                  text-xs font-extrabold
                                  tracking-[0.2em]">

                            ACADÉMIE EL TAFAWOK

                        </p>

                        <h1 class="text-2xl md:text-3xl
                                   font-extrabold
                                   text-white mt-1">

                            Nouvel enseignant

                        </h1>

                        <p class="text-blue-100 text-sm mt-2">

                            Ajoutez les informations du membre
                            de votre équipe pédagogique.

                        </p>

                    </div>

                </div>

            </div>


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
                  action="{{ route('teachers.store') }}">

                @csrf


                <!-- ================= PERSONAL ================= -->

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

                            👤

                        </div>

                        <div>

                            <h2 class="text-xl font-extrabold
                                       text-[#0B2A55]
                                       dark:text-white">

                                Informations personnelles

                            </h2>

                            <p class="text-sm text-gray-500 mt-1">

                                Identité et informations professionnelles

                            </p>

                        </div>

                    </div>


                    <div class="grid grid-cols-1
                                md:grid-cols-2
                                gap-6">


                        <!-- Prénom -->

                        <div>

                            <label class="block
                                          text-sm font-bold
                                          text-[#0B2A55]
                                          dark:text-gray-200
                                          mb-2">

                                Prénom
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="text"
                                name="first_name"
                                value="{{ old('first_name') }}"
                                required
                                placeholder="Ex : Ahmed"

                                class="w-full rounded-xl
                                       border-2
                                       border-gray-200
                                       bg-gray-50
                                       px-4 py-3.5
                                       text-gray-800
                                       outline-none
                                       transition

                                       focus:border-[#C89B3C]
                                       focus:bg-white
                                       focus:ring-4
                                       focus:ring-[#C89B3C]/10"
                            >

                        </div>


                        <!-- Nom -->

                        <div>

                            <label class="block
                                          text-sm font-bold
                                          text-[#0B2A55]
                                          dark:text-gray-200
                                          mb-2">

                                Nom
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="text"
                                name="last_name"
                                value="{{ old('last_name') }}"
                                required
                                placeholder="Ex : Benali"

                                class="w-full rounded-xl
                                       border-2
                                       border-gray-200
                                       bg-gray-50
                                       px-4 py-3.5
                                       text-gray-800
                                       outline-none
                                       transition

                                       focus:border-[#C89B3C]
                                       focus:bg-white
                                       focus:ring-4
                                       focus:ring-[#C89B3C]/10"
                            >

                        </div>


                        <!-- Spécialité -->

                        <div>

                            <label class="block
                                          text-sm font-bold
                                          text-[#0B2A55]
                                          dark:text-gray-200
                                          mb-2">

                                Spécialité
                                <span class="text-red-500">*</span>

                            </label>

                            <div class="relative">

                                <span class="absolute left-4 top-1/2
                                             -translate-y-1/2
                                             text-lg">

                                    📚

                                </span>

                                <input
                                    type="text"
                                    name="speciality"
                                    value="{{ old('speciality') }}"
                                    required
                                    placeholder="Ex : Mathématiques"

                                    class="w-full rounded-xl
                                           border-2
                                           border-gray-200
                                           bg-gray-50
                                           pl-12 pr-4 py-3.5
                                           text-gray-800
                                           outline-none
                                           transition

                                           focus:border-[#C89B3C]
                                           focus:bg-white
                                           focus:ring-4
                                           focus:ring-[#C89B3C]/10"
                                >

                            </div>

                        </div>


                        <!-- Date recrutement -->

                        <div>

                            <label class="block
                                          text-sm font-bold
                                          text-[#0B2A55]
                                          dark:text-gray-200
                                          mb-2">

                                Date de recrutement

                            </label>

                            <input
                                type="date"
                                name="hire_date"
                                value="{{ old('hire_date') }}"

                                class="w-full rounded-xl
                                       border-2
                                       border-gray-200
                                       bg-gray-50
                                       px-4 py-3.5
                                       text-gray-800
                                       outline-none
                                       transition

                                       focus:border-[#C89B3C]
                                       focus:bg-white
                                       focus:ring-4
                                       focus:ring-[#C89B3C]/10"
                            >

                        </div>

                    </div>

                </div>

<!-- ================= NIVEAUX ================= -->

<div class="bg-white dark:bg-gray-800
            rounded-3xl
            shadow-sm
            border border-gray-100
            dark:border-gray-700
            p-6 md:p-8 mb-6">

    <div class="flex items-center gap-4 mb-7">

        <div class="w-12 h-12 rounded-xl
                    bg-blue-100
                    flex items-center
                    justify-center
                    text-xl">

            🎓

        </div>

        <div>

            <h2 class="text-xl font-extrabold
                       text-[#0B2A55]
                       dark:text-white">

                Niveaux d'enseignement

            </h2>

            <p class="text-sm text-gray-500 mt-1">

                Sélectionnez les niveaux dans lesquels cet enseignant peut enseigner.

            </p>

        </div>

    </div>


    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        @foreach($levels as $level)

            <label class="relative cursor-pointer">

                <input
                    type="checkbox"
                    name="levels[]"
                    value="{{ $level->id }}"
                    class="peer sr-only"
                    {{ in_array($level->id, old('levels', [])) ? 'checked' : '' }}
                >

                <div class="p-5 rounded-2xl
                            border-2 border-gray-200
                            bg-gray-50
                            peer-checked:border-[#0B2A55]
                            peer-checked:bg-blue-50
                            transition">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10 rounded-xl
                                    bg-white
                                    flex items-center justify-center
                                    shadow-sm">

                            🎓

                        </div>

                        <div>

                            <div class="font-extrabold
                                        text-[#0B2A55]">

                                {{ $level->name }}

                            </div>

                            <div class="text-xs text-gray-500 mt-1">

                                {{ $level->code }}

                            </div>

                        </div>

                    </div>

                    <div class="mt-3 text-xs
                                text-gray-500">

                        {{ $level->description }}

                    </div>

                </div>

            </label>

        @endforeach

    </div>


    @error('levels')
        <p class="mt-3 text-sm text-red-600 font-semibold">
            {{ $message }}
        </p>
    @enderror

</div>
                <!-- ================= CONTACT ================= -->

                <div class="bg-white dark:bg-gray-800
                            rounded-3xl
                            shadow-sm
                            border border-gray-100
                            dark:border-gray-700
                            p-6 md:p-8 mb-6">

                    <div class="flex items-center gap-4 mb-7">

                        <div class="w-12 h-12 rounded-xl
                                    bg-[#C89B3C]/10
                                    flex items-center
                                    justify-center
                                    text-xl">

                            📞

                        </div>

                        <div>

                            <h2 class="text-xl font-extrabold
                                       text-[#0B2A55]
                                       dark:text-white">

                                Informations de contact

                            </h2>

                            <p class="text-sm text-gray-500 mt-1">

                                Coordonnées de l'enseignant

                            </p>

                        </div>

                    </div>


                    <div class="grid grid-cols-1
                                md:grid-cols-2
                                gap-6">


                        <!-- Téléphone -->

                        <div>

                            <label class="block
                                          text-sm font-bold
                                          text-[#0B2A55]
                                          dark:text-gray-200
                                          mb-2">

                                Téléphone

                            </label>

                            <div class="relative">

                                <span class="absolute left-4 top-1/2
                                             -translate-y-1/2">

                                    📱

                                </span>

                                <input
                                    type="text"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    placeholder="0550 00 00 00"

                                    class="w-full rounded-xl
                                           border-2
                                           border-gray-200
                                           bg-gray-50
                                           pl-12 pr-4 py-3.5
                                           outline-none
                                           transition

                                           focus:border-[#C89B3C]
                                           focus:bg-white
                                           focus:ring-4
                                           focus:ring-[#C89B3C]/10"
                                >

                            </div>

                        </div>


                        <!-- Email -->

                        <div>

                            <label class="block
                                          text-sm font-bold
                                          text-[#0B2A55]
                                          dark:text-gray-200
                                          mb-2">

                                Email

                            </label>

                            <div class="relative">

                                <span class="absolute left-4 top-1/2
                                             -translate-y-1/2">

                                    ✉️

                                </span>

                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="enseignant@email.com"

                                    class="w-full rounded-xl
                                           border-2
                                           border-gray-200
                                           bg-gray-50
                                           pl-12 pr-4 py-3.5
                                           outline-none
                                           transition

                                           focus:border-[#C89B3C]
                                           focus:bg-white
                                           focus:ring-4
                                           focus:ring-[#C89B3C]/10"
                                >

                            </div>

                        </div>


                        <!-- Adresse -->

                        <div class="md:col-span-2">

                            <label class="block
                                          text-sm font-bold
                                          text-[#0B2A55]
                                          dark:text-gray-200
                                          mb-2">

                                Adresse

                            </label>

                            <textarea
                                name="address"
                                rows="4"
                                placeholder="Adresse de l'enseignant..."

                                class="w-full rounded-xl
                                       border-2
                                       border-gray-200
                                       bg-gray-50
                                       px-4 py-3.5
                                       text-gray-800
                                       outline-none
                                       resize-none
                                       transition

                                       focus:border-[#C89B3C]
                                       focus:bg-white
                                       focus:ring-4
                                       focus:ring-[#C89B3C]/10"
                            >{{ old('address') }}</textarea>

                        </div>

                    </div>

                </div>


                <!-- ================= STATUS ================= -->

                <div class="bg-white dark:bg-gray-800
                            rounded-3xl
                            shadow-sm
                            border border-gray-100
                            dark:border-gray-700
                            p-6 md:p-8 mb-6">

                    <div class="flex items-center
                                justify-between
                                gap-4">

                        <div class="flex items-center gap-4">

                            <div class="w-12 h-12 rounded-xl
                                        bg-green-100
                                        flex items-center
                                        justify-center
                                        text-xl">

                                ✓

                            </div>

                            <div>

                                <h2 class="text-xl font-extrabold
                                           text-[#0B2A55]
                                           dark:text-white">

                                    Statut

                                </h2>

                                <p class="text-sm text-gray-500 mt-1">

                                    Définissez le statut actuel de l'enseignant.

                                </p>

                            </div>

                        </div>


                        <label class="relative
                                      inline-flex items-center
                                      cursor-pointer">

                            <input
                                type="checkbox"
                                name="active"
                                value="1"
                                checked
                                class="sr-only peer"
                            >

                            <div class="w-14 h-7
                                        bg-gray-200
                                        rounded-full
                                        peer
                                        peer-checked:bg-[#0B2A55]
                                        transition">

                            </div>

                            <div class="absolute left-1 top-1
                                        w-5 h-5
                                        bg-white
                                        rounded-full
                                        shadow
                                        transition-transform
                                        peer-checked:translate-x-7">

                            </div>

                        </label>

                    </div>


                    <div class="mt-5
                                rounded-xl
                                bg-green-50
                                border border-green-100
                                px-4 py-3">

                        <p class="text-sm
                                  font-semibold
                                  text-green-700">

                            ✓ L'enseignant sera enregistré comme actif.

                        </p>

                    </div>

                </div>


                <!-- ================= ACTIONS ================= -->

                <div class="bg-white dark:bg-gray-800
                            rounded-3xl
                            shadow-sm
                            border border-gray-100
                            dark:border-gray-700
                            p-6">

                    <div class="flex flex-col-reverse
                                sm:flex-row
                                sm:items-center
                                sm:justify-between
                                gap-4">

                        <a
                            href="{{ route('teachers.index') }}"

                            class="inline-flex
                                   items-center
                                   justify-center
                                   gap-2
                                   rounded-xl
                                   bg-gray-100
                                   px-6 py-3.5
                                   text-sm font-bold
                                   text-gray-700
                                   hover:bg-gray-200
                                   transition">

                            ←

                            Annuler

                        </a>


                        <button
                            type="submit"

                            class="inline-flex
                                   items-center
                                   justify-center
                                   gap-3
                                   rounded-xl
                                   bg-[#0B2A55]
                                   px-8 py-3.5
                                   text-sm font-extrabold
                                   text-white
                                   shadow-lg
                                   border-b-4
                                   border-[#C89B3C]
                                   hover:bg-[#061A33]
                                   hover:-translate-y-0.5
                                   hover:shadow-xl
                                   transition-all duration-200">

                            💾

                            Enregistrer l'enseignant

                        </button>

                    </div>

                </div>

            </form>


            <!-- ================= FOOTER ================= -->

            <div class="mt-6 text-center">

                <p class="text-xs text-gray-400">

                    Académie El Tafawok • Gestion des enseignants

                </p>

            </div>




            
        </div>

    </div>

</x-app-layout>