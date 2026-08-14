<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    📢 Gestion des annonces
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Publier et gérer les informations destinées aux élèves
                </p>
            </div>

            <a href="{{ route('announcements.create') }}"
               class="inline-flex items-center justify-center px-5 py-3 rounded-xl text-white font-semibold shadow-sm transition hover:opacity-90"
               style="background-color:#0B2A55;">

                ➕ Nouvelle annonce

            </a>

        </div>

    </x-slot>


    <div class="py-10 bg-gray-100 dark:bg-gray-900 min-h-screen">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- Message succès --}}

            @if (session('success'))

                <div class="mb-6 rounded-xl bg-green-100 border border-green-200 text-green-800 px-5 py-4">

                    ✅ {{ session('success') }}

                </div>

            @endif


            {{-- Carte principale --}}

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">


                {{-- Header --}}

                <div class="p-6 border-b border-gray-200 dark:border-gray-700">

                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        Toutes les annonces
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Consultez les annonces publiées par l'administration.
                    </p>

                </div>


                @if ($announcements->count() > 0)

                    <div class="divide-y divide-gray-200 dark:divide-gray-700">

                        @foreach ($announcements as $announcement)

                            <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition">


                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">


                                    {{-- Contenu --}}

                                    <div class="flex-1">

                                        <div class="flex flex-wrap items-center gap-3 mb-2">

                                            <h4 class="text-lg font-bold text-gray-800 dark:text-gray-100">

                                                {{ $announcement->title }}

                                            </h4>


                                            {{-- Type --}}

                                            @if ($announcement->type === 'important')

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">

                                                    🔴 Important

                                                </span>

                                            @elseif ($announcement->type === 'warning')

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">

                                                    🟠 Attention

                                                </span>

                                            @else

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">

                                                    🔵 Information

                                                </span>

                                            @endif


                                            {{-- Statut --}}

                                            @if ($announcement->is_active)

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">

                                                    🟢 Publiée

                                                </span>

                                            @else

                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">

                                                    ⚪ Désactivée

                                                </span>

                                            @endif

                                        </div>


                                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">

                                            {{ $announcement->content }}

                                        </p>


                                        <div class="mt-3 text-sm text-gray-500">

                                            📅

                                            @if ($announcement->published_at)

                                                {{ $announcement->published_at->format('d/m/Y à H:i') }}

                                            @else

                                                Non publiée

                                            @endif

                                        </div>

                                    </div>


                                    {{-- Actions --}}

                                    <div class="flex flex-wrap items-center gap-2">


                                        <a href="{{ route('announcements.show', $announcement) }}"
                                           class="px-4 py-2 rounded-lg bg-blue-100 text-blue-700 text-sm font-semibold hover:bg-blue-200">

                                            👁️ Voir

                                        </a>


                                        <a href="{{ route('announcements.edit', $announcement) }}"
                                           class="px-4 py-2 rounded-lg bg-yellow-100 text-yellow-700 text-sm font-semibold hover:bg-yellow-200">

                                            ✏️ Modifier

                                        </a>


                                        <form method="POST"
                                              action="{{ route('announcements.destroy', $announcement) }}"
                                              onsubmit="return confirm('Voulez-vous vraiment supprimer cette annonce ?');">

                                            @csrf

                                            @method('DELETE')

                                            <button type="submit"
                                                    class="px-4 py-2 rounded-lg bg-red-100 text-red-700 text-sm font-semibold hover:bg-red-200">

                                                🗑️ Supprimer

                                            </button>

                                        </form>


                                    </div>


                                </div>

                            </div>

                        @endforeach

                    </div>


                    {{-- Pagination --}}

                    <div class="p-6 border-t border-gray-200 dark:border-gray-700">

                        {{ $announcements->links() }}

                    </div>


                @else

                    {{-- Aucun résultat --}}

                    <div class="p-12 text-center">

                        <div class="text-6xl mb-4">
                            📢
                        </div>

                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            Aucune annonce
                        </h3>

                        <p class="text-gray-500 mt-2">
                            Vous n'avez pas encore créé d'annonce.
                        </p>


                        <a href="{{ route('announcements.create') }}"
                           class="inline-flex items-center mt-6 px-5 py-3 rounded-xl text-white font-semibold"
                           style="background-color:#0B2A55;">

                            ➕ Créer la première annonce

                        </a>

                    </div>

                @endif


            </div>

        </div>

    </div>

</x-app-layout>