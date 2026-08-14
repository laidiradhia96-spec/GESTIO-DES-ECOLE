<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
            Modifier l'élève
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('students.update', $student) }}" method="POST">

                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Nom -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Nom
                            </label>

                            <input type="text"
                                   name="last_name"
                                   value="{{ old('last_name', $student->last_name) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">

                            @error('last_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Prénom -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Prénom
                            </label>

                            <input type="text"
                                   name="first_name"
                                   value="{{ old('first_name', $student->first_name) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">

                            @error('first_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date naissance -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Date de naissance
                            </label>

                            <input type="date"
                                   name="date_of_birth"
                                   value="{{ old('date_of_birth', $student->date_of_birth) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">
                        </div>

                        <!-- Niveau -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Niveau
                            </label>

                            <input type="text"
                                   name="level"
                                   value="{{ old('level', $student->level) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">

                            @error('level')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Téléphone -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Téléphone
                            </label>

                            <input type="text"
                                   name="phone"
                                   value="{{ old('phone', $student->phone) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">
                        </div>

                        <!-- Parent -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Nom du parent
                            </label>

                            <input type="text"
                                   name="parent_name"
                                   value="{{ old('parent_name', $student->parent_name) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">
                        </div>

                        <!-- Téléphone parent -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Téléphone du parent
                            </label>

                            <input type="text"
                                   name="parent_phone"
                                   value="{{ old('parent_phone', $student->parent_phone) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">
                        </div>

                        <!-- Adresse -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Adresse
                            </label>

                            <input type="text"
                                   name="address"
                                   value="{{ old('address', $student->address) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300">
                        </div>

                    </div>

                    <div class="mt-8 flex gap-3">

                        <button type="submit"
                                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                            Enregistrer les modifications
                        </button>

                        <a href="{{ route('students.index') }}"
                           class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">
                            Annuler
                        </a>

                    </div>

                </form>

            </div>

        </div>
    </div>

</x-app-layout>