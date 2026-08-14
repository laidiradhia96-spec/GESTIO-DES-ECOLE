<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Créer un compte élève
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Créer les identifiants de connexion de l'élève
                </p>
            </div>

            <a
                href="{{ route('students.index') }}"
                class="inline-flex items-center justify-center px-4 py-2
                       bg-gray-100 dark:bg-gray-700
                       border border-gray-300 dark:border-gray-600
                       rounded-xl
                       font-semibold text-sm
                       text-gray-700 dark:text-gray-200
                       hover:bg-gray-200 dark:hover:bg-gray-600
                       transition"
            >
                ← Retour
            </a>

        </div>
    </x-slot>


    <div class="py-10 bg-gray-100 dark:bg-gray-900 min-h-screen">

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Carte principale --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">

                {{-- En-tête --}}
                <div class="px-6 py-6 border-b border-gray-200 dark:border-gray-700">

                    <div class="flex items-center gap-4">

                        <div class="w-14 h-14 rounded-2xl
                                    bg-indigo-100 dark:bg-indigo-900/40
                                    flex items-center justify-center">

                            <span class="text-2xl">
                                👤
                            </span>

                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                                Compte de l'élève
                            </h3>

                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </p>
                        </div>

                    </div>

                </div>


                {{-- Informations élève --}}
                <div class="px-6 py-6">

                    <div class="bg-gray-50 dark:bg-gray-700/40
                                rounded-xl p-5 mb-6">

                        <h4 class="font-semibold text-gray-800 dark:text-gray-100 mb-4">
                            Informations de l'élève
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Prénom
                                </p>

                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ $student->first_name }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Nom
                                </p>

                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ $student->last_name }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Niveau
                                </p>

                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ $student->level }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Téléphone
                                </p>

                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ $student->phone ?? 'Non renseigné' }}
                                </p>
                            </div>

                        </div>

                    </div>


                    {{-- Formulaire --}}
                    <form
                        method="POST"
                        action="{{ route('students.account.store', $student) }}"
                        class="space-y-6"
                    >

                        @csrf


                        {{-- Email --}}
                        <div>

                            <label
                                for="email"
                                class="block text-sm font-semibold
                                       text-gray-700 dark:text-gray-200 mb-2"
                            >
                                Adresse e-mail
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                placeholder="exemple@email.com"
                                class="w-full rounded-xl
                                       border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-white
                                       focus:border-indigo-500
                                       focus:ring-indigo-500"
                            >

                            @error('email')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Mot de passe --}}
                        <div>

                            <label
                                for="password"
                                class="block text-sm font-semibold
                                       text-gray-700 dark:text-gray-200 mb-2"
                            >
                                Mot de passe
                            </label>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                placeholder="Minimum 8 caractères"
                                class="w-full rounded-xl
                                       border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-white
                                       focus:border-indigo-500
                                       focus:ring-indigo-500"
                            >

                            @error('password')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Confirmation --}}
                        <div>

                            <label
                                for="password_confirmation"
                                class="block text-sm font-semibold
                                       text-gray-700 dark:text-gray-200 mb-2"
                            >
                                Confirmer le mot de passe
                            </label>

                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                placeholder="Retaper le mot de passe"
                                class="w-full rounded-xl
                                       border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-white
                                       focus:border-indigo-500
                                       focus:ring-indigo-500"
                            >

                        </div>


                        {{-- Information --}}
                        <div class="rounded-xl
                                    bg-indigo-50 dark:bg-indigo-900/20
                                    border border-indigo-100 dark:border-indigo-800
                                    p-4">

                            <div class="flex gap-3">

                                <div class="text-xl">
                                    💡
                                </div>

                                <div>

                                    <p class="font-semibold text-indigo-800 dark:text-indigo-200">
                                        Espace personnel
                                    </p>

                                    <p class="text-sm text-indigo-700 dark:text-indigo-300 mt-1">
                                        Après la création du compte, l'élève pourra
                                        utiliser son adresse e-mail et son mot de passe
                                        pour accéder à son espace personnel.
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- Boutons --}}
                        <div class="flex flex-col sm:flex-row gap-3 pt-2">

                            <a
                                href="{{ route('students.index') }}"
                                class="w-full sm:w-auto
                                       inline-flex items-center justify-center
                                       px-6 py-3
                                       rounded-xl
                                       bg-gray-100 dark:bg-gray-700
                                       text-gray-700 dark:text-gray-200
                                       font-semibold
                                       hover:bg-gray-200 dark:hover:bg-gray-600
                                       transition"
                            >
                                Annuler
                            </a>


                            <button
                                type="submit"
                                class="w-full sm:flex-1
                                       inline-flex items-center justify-center
                                       px-6 py-3
                                       rounded-xl
                                       bg-indigo-600
                                       text-white
                                       font-semibold
                                       hover:bg-indigo-700
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-indigo-500
                                       focus:ring-offset-2
                                       transition"
                            >
                                👤 Créer le compte
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>