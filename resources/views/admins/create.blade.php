<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-extrabold text-[#0B2A55]">
                Ajouter un administrateur
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Créer un nouveau compte administrateur
            </p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gray-50 py-10">

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">

                <form method="POST" action="{{ route('admins.store') }}">
                    @csrf

                    {{-- NOM --}}
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Nom complet
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Nom et prénom"
                        >

                        @error('name')
                            <p class="text-red-600 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- EMAIL --}}
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Adresse Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="admin@ecole.com"
                        >

                        @error('email')
                            <p class="text-red-600 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- PASSWORD --}}
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Mot de passe
                        </label>

                        <input
                            type="password"
                            name="password"
                            required
                            class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Minimum 8 caractères"
                        >

                        @error('password')
                            <p class="text-red-600 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- CONFIRMATION --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Confirmer le mot de passe
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            required
                            class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Confirmer le mot de passe"
                        >
                    </div>

                    {{-- ACTIONS --}}
                    <div class="flex justify-end gap-3">

                        <a
                            href="{{ route('admins.index') }}"
                            class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold transition"
                        >
                            Annuler
                        </a>

                        <button
                            type="submit"
                            class="px-5 py-2.5 rounded-xl bg-[#0B2A55] hover:bg-[#123B70] text-white font-bold transition"
                        >
                            + Créer l'administrateur
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>