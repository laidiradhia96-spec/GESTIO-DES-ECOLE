<nav x-data="{ open: false }"
    class="bg-white border-b border-gray-200 shadow-sm">

    <!-- Primary Navigation Menu -->
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between items-center h-16">

            <!-- Left Side: Logo + Nav -->
            <div class="flex items-center flex-nowrap whitespace-nowrap min-w-0">

                <!-- Logo -->
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-2 shrink-0">

                    <img
                        src="{{ asset('images/logo.jpeg') }}"
                        alt="Académie El Tafawok"
                        class="h-10 w-10 object-contain"
                    >

                    <div class="leading-tight hidden lg:block">
                        <div class="text-sm font-bold text-[#0B2A55]">
                            Académie
                        </div>
                        <div class="text-xs font-medium text-[#C89B3C]">
                            El Tafawok
                        </div>
                    </div>

                </a>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex lg:items-center lg:ml-6 flex-nowrap whitespace-nowrap gap-1">

                    <x-nav-link
                        :href="route('dashboard')"
                        :active="request()->routeIs('dashboard')"
                    >
                        🏠 Dashboard
                    </x-nav-link>

                    <x-nav-link
                        :href="route('students.index')"
                        :active="request()->routeIs('students.*')"
                    >
                        👨‍🎓 Élèves
                    </x-nav-link>

                    <x-nav-link
                        :href="route('teachers.index')"
                        :active="request()->routeIs('teachers.*')"
                    >
                        👨‍🏫 Enseignants
                    </x-nav-link>

                    <x-nav-link
                        :href="route('subjects.index')"
                        :active="request()->routeIs('subjects.*')"
                    >
                        📚 Matières
                    </x-nav-link>

                    <x-nav-link
                        :href="route('groups.index')"
                        :active="request()->routeIs('groups.*')"
                    >
                        👥 Groupes
                    </x-nav-link>

                    <x-nav-link
                        :href="route('attendances.index')"
                        :active="request()->routeIs('attendances.*')"
                    >
                        📋 Présences
                    </x-nav-link>

                    <x-nav-link
                        :href="route('payments.index')"
                        :active="request()->routeIs('payments.*')"
                    >
                        💳 Paiements
                    </x-nav-link>

                    <x-nav-link
                        :href="route('payment-signalements.index')"
                        :active="request()->routeIs('payment-signalements.*')"
                    >
                        🚨 Impayés
                    </x-nav-link>

                    <x-nav-link
                        :href="route('school-years.index')"
                        :active="request()->routeIs('school-years.*')"
                    >
                        🗓️ Années scolaires
                    </x-nav-link>

                    <x-nav-link
                        :href="route('revenus.index')"
                        :active="request()->routeIs('revenus.*')"
                    >
                        💰 Revenus
                    </x-nav-link>

                </div>

            </div>

            <!-- Right Side: User Dropdown -->
            <div class="hidden lg:flex lg:items-center shrink-0 ml-4">

                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">

                        <button
                            class="inline-flex items-center gap-2 px-3 py-1.5
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

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

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
            <div class="-me-2 flex items-center lg:hidden">

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
        class="hidden lg:hidden border-t border-gray-100"
    >

        <div class="pt-2 pb-3 space-y-1">

            <x-responsive-nav-link
                :href="route('dashboard')"
                :active="request()->routeIs('dashboard')"
            >
                Dashboard
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('students.index')"
                :active="request()->routeIs('students.*')"
            >
                Élèves
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('teachers.index')"
                :active="request()->routeIs('teachers.*')"
            >
                Enseignants
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('subjects.index')"
                :active="request()->routeIs('subjects.*')"
            >
                Matières
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('groups.index')"
                :active="request()->routeIs('groups.*')"
            >
                Groupes
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('attendances.index')"
                :active="request()->routeIs('attendances.*')"
            >
                📋 Présences
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('payments.index')"
                :active="request()->routeIs('payments.*')"
            >
                💳 Paiements
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('payment-signalements.index')"
                :active="request()->routeIs('payment-signalements.*')"
            >
                🚨 Impayés
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('school-years.index')"
                :active="request()->routeIs('school-years.*')"
            >
                🗓️ Années scolaires
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('revenus.index')"
                :active="request()->routeIs('revenus.*')"
            >
                💰 Revenus
            </x-responsive-nav-link>

        </div>

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

                <x-responsive-nav-link
                    :href="route('profile.edit')"
                >
                    Profile
                </x-responsive-nav-link>

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
