<nav x-data="{ open: false }"
    class="bg-white border-b border-gray-200 shadow-sm">

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between h-20">

            <!-- Left Side -->
            <div class="flex items-center">

                <!-- Logo -->
                <div class="flex items-center">

                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-3">

                        <img
                            src="{{ asset('images/logo.jpeg') }}"
                            alt="Académie El Tafawok"
                            class="h-14 w-14 object-contain"
                        >

                        <div class="leading-tight">

                            <div class="text-xl font-bold text-[#0B2A55]">
                                Académie
                            </div>

                            <div class="text-sm font-medium text-[#C89B3C]">
                                El Tafawok
                            </div>

                        </div>

                    </a>

                </div>


                <!-- Navigation -->
                <div class="hidden sm:flex sm:items-center sm:ms-12 space-x-8">

                    <!-- Dashboard -->
                    <x-nav-link
                        :href="route('dashboard')"
                        :active="request()->routeIs('dashboard')"
                    >
                    🏠 Dashboard
                      <!--  {{ __('Dashboard') }}-->
                    </x-nav-link>


                    <!-- Élèves -->
                    <x-nav-link
                        :href="route('students.index')"
                        :active="request()->routeIs('students.*')"
                    >
                        👨‍🎓 Élèves
                    </x-nav-link>


                    <!-- Enseignants -->
                    <x-nav-link
                        :href="route('teachers.index')"
                        :active="request()->routeIs('teachers.*')"
                    >
                       👨‍🏫 Enseignants
                    </x-nav-link>


                    <!-- Matières -->
                    <x-nav-link
                        :href="route('subjects.index')"
                        :active="request()->routeIs('subjects.*')"
                    >
                        📚 Matières
                    </x-nav-link>

                    <!-- Présences -->
<x-nav-link
    :href="route('attendances.index')"
    :active="request()->routeIs('attendances.*')"
>
    📋 Présences
</x-nav-link>
<!-- Paiements -->


<x-nav-link
    :href="route('payments.index')"
    :active="request()->routeIs('payments.*')"
>
    💳 Paiements
</x-nav-link>


<!-- Signalements -->
<x-nav-link
    :href="route('payment-signalements.index')"
    :active="request()->routeIs('payment-signalements.*')"
>
    🚨 Impayés
</x-nav-link>
                </div>

            </div>


            <!-- User Dropdown -->
            <div class="hidden sm:flex sm:items-center">

                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">

                        <button
                            class="inline-flex items-center gap-2 px-4 py-2
                                   rounded-lg
                                   text-sm font-medium
                                   text-[#0B2A55]
                                   bg-gray-50
                                   hover:bg-[#0B2A55]
                                   hover:text-white
                                   transition duration-200"
                        >

                            <span>
                                {{ Auth::user()->name }}
                            </span>

                            <svg
                                class="w-4 h-4 fill-current"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                            >

                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />

                            </svg>

                        </button>

                    </x-slot>


                    <x-slot name="content">

                        <!-- Profile -->
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>


                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}">

                            @csrf

                            <x-dropdown-link
                                :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                            >
                                {{ __('Log Out') }}
                            </x-dropdown-link>

                        </form>

                    </x-slot>

                </x-dropdown>

            </div>


            <!-- Mobile Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">

                <button
                    @click="open = ! open"
                    class="inline-flex items-center justify-center p-2
                           rounded-md
                           text-[#0B2A55]
                           hover:bg-gray-100
                           focus:outline-none"
                >

                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                    >

                        <path
                            :class="{
                                'hidden': open,
                                'inline-flex': ! open
                            }"
                            class="inline-flex"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />

                        <path
                            :class="{
                                'hidden': ! open,
                                'inline-flex': open
                            }"
                            class="hidden"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />

                    </svg>

                </button>

            </div>

        </div>

    </div>


    <!-- Mobile Navigation -->
    <div
        :class="{
            'block': open,
            'hidden': ! open
        }"
        class="hidden sm:hidden border-t border-gray-100"
    >

        <div class="pt-2 pb-3 space-y-1">

            <!-- Dashboard -->
            <x-responsive-nav-link
                :href="route('dashboard')"
                :active="request()->routeIs('dashboard')"
            >
                Dashboard
            </x-responsive-nav-link>


            <!-- Élèves -->
            <x-responsive-nav-link
                :href="route('students.index')"
                :active="request()->routeIs('students.*')"
            >
                Élèves
            </x-responsive-nav-link>


            <!-- Enseignants -->
            <x-responsive-nav-link
                :href="route('teachers.index')"
                :active="request()->routeIs('teachers.*')"
            >
                Enseignants
            </x-responsive-nav-link>


            <!-- Matières -->
            <x-responsive-nav-link
                :href="route('subjects.index')"
                :active="request()->routeIs('subjects.*')"
            >
                Matières
            </x-responsive-nav-link>


<!-- Paiements -->
<x-responsive-nav-link
    :href="route('payments.index')"
    :active="request()->routeIs('payments.*')"
>
    💳 Paiements
</x-responsive-nav-link>


<!-- Signalements -->
<x-responsive-nav-link
    :href="route('payment-signalements.index')"
    :active="request()->routeIs('payment-signalements.*')"
>
    🚨 Impayés
</x-responsive-nav-link>
        </div>
        <!-- Présences -->
<x-responsive-nav-link
    :href="route('attendances.index')"
    :active="request()->routeIs('attendances.*')"
>
    📋 Présences
</x-responsive-nav-link>


        <!-- Mobile User -->
        <div class="pt-4 pb-1 border-t border-gray-200">

            <div class="px-4">

                <div class="font-medium text-base text-[#0B2A55]">
                    {{ Auth::user()->name }}
                </div>

                <div class="font-medium text-sm text-gray-500">
                    {{ Auth::user()->email }}
                </div>

            </div>


            <div class="mt-3 space-y-1">

                <!-- Profile -->
                <x-responsive-nav-link
                    :href="route('profile.edit')"
                >
                    Profile
                </x-responsive-nav-link>


                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}">

                    @csrf

                    <x-responsive-nav-link
                        :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                    >
                        Log Out
                    </x-responsive-nav-link>


                

                </form>

            </div>

        </div>

    </div>

</nav>