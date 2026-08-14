<x-app-layout>

    {{-- HEADER --}}
    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div>
                <h2 class="text-2xl font-extrabold text-[#0B2A55] dark:text-white">
                    Gestion des administrateurs
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Gérer les comptes administrateurs de l'académie
                </p>
            </div>

            <a href="{{ route('admins.create') }}"
               class="inline-flex items-center justify-center gap-2
                      rounded-xl
                      bg-[#0B2A55]
                      px-5 py-3
                      text-sm font-extrabold
                      text-white
                      shadow-lg
                      border-b-4 border-[#C89B3C]
                      hover:bg-[#061A33]
                      hover:-translate-y-0.5
                      transition">

                ➕

                Ajouter un administrateur

            </a>

        </div>

    </x-slot>


    {{-- MAIN --}}
    <div class="min-h-screen bg-gradient-to-br
                from-gray-50 via-white to-blue-50
                py-10">

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- SUCCESS --}}
            @if(session('success'))

                <div class="mb-6 rounded-2xl
                            border border-green-200
                            bg-green-50
                            px-5 py-4
                            text-green-700
                            font-semibold">

                    ✓ {{ session('success') }}

                </div>

            @endif


            {{-- ERROR --}}
            @if(session('error'))

                <div class="mb-6 rounded-2xl
                            border border-red-200
                            bg-red-50
                            px-5 py-4
                            text-red-700
                            font-semibold">

                    ⚠️ {{ session('error') }}

                </div>

            @endif


            {{-- CARD --}}
            <div class="bg-white
                        rounded-3xl
                        shadow-sm
                        border border-gray-100
                        overflow-hidden">

                {{-- TOP --}}
                <div class="px-6 py-6
                            border-b border-gray-100
                            bg-[#F8FAFC]">

                    <div class="flex items-center gap-4">

                        <div class="w-14 h-14
                                    rounded-2xl
                                    bg-[#0B2A55]/10
                                    flex items-center
                                    justify-center
                                    text-2xl">

                            👥

                        </div>

                        <div>

                            <h3 class="text-xl font-extrabold text-[#0B2A55]">

                                Administrateurs

                            </h3>

                            <p class="text-sm text-gray-500 mt-1">

                                {{ $admins->total() }} administrateur(s)

                            </p>

                        </div>

                    </div>

                </div>


                {{-- TABLE --}}
                <div class="overflow-x-auto">

                    <table class="min-w-full">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="px-6 py-4 text-left
                                           text-xs font-extrabold
                                           uppercase tracking-wider
                                           text-gray-500">

                                    Administrateur

                                </th>

                                <th class="px-6 py-4 text-left
                                           text-xs font-extrabold
                                           uppercase tracking-wider
                                           text-gray-500">

                                    Email

                                </th>

                                <th class="px-6 py-4 text-left
                                           text-xs font-extrabold
                                           uppercase tracking-wider
                                           text-gray-500">

                                    Rôle

                                </th>

                                <th class="px-6 py-4 text-right
                                           text-xs font-extrabold
                                           uppercase tracking-wider
                                           text-gray-500">

                                    Actions

                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100">

                            @forelse($admins as $admin)

                                <tr class="hover:bg-gray-50 transition">

                                    {{-- NAME --}}
                                    <td class="px-6 py-5">

                                        <div class="flex items-center gap-3">

                                            <div class="w-11 h-11
                                                        rounded-xl
                                                        bg-[#0B2A55]
                                                        flex items-center
                                                        justify-center
                                                        text-white
                                                        font-extrabold">

                                                {{ strtoupper(substr($admin->name, 0, 1)) }}

                                            </div>

                                            <div>

                                                <p class="font-bold text-gray-800">

                                                    {{ $admin->name }}

                                                </p>

                                                @if($admin->id === auth()->id())

                                                    <span class="text-xs
                                                                 text-[#C89B3C]
                                                                 font-bold">

                                                        Votre compte

                                                    </span>

                                                @endif

                                            </div>

                                        </div>

                                    </td>


                                    {{-- EMAIL --}}
                                    <td class="px-6 py-5">

                                        <span class="text-sm text-gray-600">

                                            {{ $admin->email }}

                                        </span>

                                    </td>


                                    {{-- ROLE --}}
                                    <td class="px-6 py-5">

                                        <span class="inline-flex
                                                     items-center
                                                     gap-1.5
                                                     rounded-full
                                                     bg-blue-50
                                                     px-3 py-1.5
                                                     text-xs
                                                     font-extrabold
                                                     text-[#0B2A55]">

                                            🛡️ Admin

                                        </span>

                                    </td>


                                    {{-- ACTIONS --}}
                                    <td class="px-6 py-5">

                                        <div class="flex justify-end">

                                            @if($admin->id !== auth()->id())

                                                <form method="POST"
                                                      action="{{ route('admins.destroy', $admin) }}"
                                                      onsubmit="return confirm('Voulez-vous vraiment supprimer cet administrateur ?');">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="inline-flex
                                                                   items-center
                                                                   gap-2
                                                                   rounded-lg
                                                                   bg-red-50
                                                                   px-3 py-2
                                                                   text-sm
                                                                   font-bold
                                                                   text-red-600
                                                                   hover:bg-red-100
                                                                   transition">

                                                        🗑️ Supprimer

                                                    </button>

                                                </form>

                                            @else

                                                <span class="text-xs
                                                             text-gray-400
                                                             font-semibold">

                                                    Compte actuel

                                                </span>

                                            @endif

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="4"
                                        class="px-6 py-12
                                               text-center
                                               text-gray-500">

                                        Aucun administrateur trouvé.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- PAGINATION --}}
                @if($admins->hasPages())

                    <div class="px-6 py-5
                                border-t border-gray-100">

                        {{ $admins->links() }}

                    </div>

                @endif

            </div>


            {{-- FOOTER --}}
            <div class="mt-6 text-center">

                <p class="text-xs text-gray-400">

                    Académie El Tafawok • Gestion des administrateurs

                </p>

            </div>

        </div>

    </div>

</x-app-layout>