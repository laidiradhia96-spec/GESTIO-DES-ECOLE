<x-app-layout>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                Informations de l'élève
            </h2>

            <a href="{{ route('students.index') }}"
               class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">

        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        {{ $student->first_name }} {{ $student->last_name }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <p class="text-sm text-gray-500">
                                Nom
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->last_name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Prénom
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->first_name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Date de naissance
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->date_of_birth ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Niveau
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->level }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Téléphone
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->phone ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Nom du parent
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->parent_name ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Téléphone du parent
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->parent_phone ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Adresse
                            </p>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $student->address ?? '-' }}
                            </p>
                        </div>

                    </div>

                    @if($errors->any())

                    <div class="mt-8 w-full
                                bg-red-50
                                border border-red-200
                                text-red-700
                                px-5 py-4
                                rounded-2xl">

                        @foreach($errors->all() as $error)

                            <p class="{{ !$loop->first ? 'mt-1' : '' }}">

                                ⚠️ {{ $error }}

                            </p>

                        @endforeach

                    </div>

                @endif


                <div class="mt-8 flex gap-3">

                        <a href="{{ route('students.edit', $student) }}"
                           class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700">
                            Modifier
                        </a>

                        <form action="{{ route('students.destroy', $student) }}"
                              method="POST">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    onclick="return confirm('Voulez-vous supprimer cet élève ?')"
                                    class="bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700">
                                Supprimer
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>