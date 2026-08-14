<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Connexion - Académie El Tafawok</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* =========================
           ANIMATIONS
        ========================= */

        @keyframes slideLeft {
            from {
                opacity: 0;
                transform: translateX(-70px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideRight {
            from {
                opacity: 0;
                transform: translateX(70px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes goldLine {
            from {
                transform: scaleY(0);
            }
            to {
                transform: scaleY(1);
            }
        }

        .logo-section {
            animation: slideLeft 0.9s ease-out forwards;
        }

        .login-section {
            animation: slideRight 0.9s ease-out forwards;
        }

        .welcome-text {
            animation: fadeUp 1s ease-out 0.4s both;
        }

        .gold-line {
            animation: goldLine 1s ease-out 0.5s both;
            transform-origin: center;
        }

        .logo-image {
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .logo-image:hover {
            transform: scale(1.05) rotate(1deg);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        }

        .login-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.20);
        }

        .login-button {
            transition: all 0.25s ease;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(11, 42, 85, 0.35);
        }

        /* Mobile */

        @media (max-width: 768px) {

            .gold-line {
                display: none;
            }

            .logo-section,
            .login-section {
                animation: fadeUp 0.8s ease-out forwards;
            }

            .login-card:hover {
                transform: none;
            }
        }
    </style>
</head>


<body class="min-h-screen bg-gradient-to-br from-[#061A33] via-[#0B2A55] to-[#163E73]">


    <div class="min-h-screen flex items-center justify-center px-5 py-10">


        <div class="w-full max-w-6xl">


            <!-- =========================
                 MAIN CONTENT
            ========================= -->

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-16 items-center">


                <!-- =========================
                     LEFT : LOGO
                ========================= -->

                <div class="logo-section text-center md:text-left">

                    <div class="flex flex-col items-center md:items-start">


                        <!-- Logo -->

                        <div class="relative mb-7">

                            <!-- Glow -->

                            <div class="absolute inset-0
                                        bg-[#C89B3C]/20
                                        blur-3xl
                                        rounded-full
                                        scale-110">
                            </div>


                            <div class="relative
                                        bg-white
                                        rounded-[2rem]
                                        p-5
                                        border-4
                                        border-[#C89B3C]
                                        shadow-2xl">

                                <img
                                    src="{{ asset('images/logo.jpeg') }}"
                                    alt="Académie El Tafawok"
                                    class="logo-image
                                           w-44 h-44
                                           md:w-52 md:h-52
                                           object-contain
                                           rounded-2xl"
                                >

                            </div>

                        </div>


                        <!-- School Name -->

                        <div class="welcome-text">

                            <h1 class="text-4xl md:text-5xl
                                       font-extrabold
                                       text-white
                                       tracking-wide">

                                Académie

                            </h1>


                            <h2 class="text-2xl md:text-3xl
                                       font-bold
                                       text-[#C89B3C]
                                       tracking-[0.25em]
                                       mt-2">

                                EL TAFAWOK

                            </h2>


                            <div class="flex items-center
                                        justify-center
                                        md:justify-start
                                        gap-3 mt-5">

                                <div class="h-px w-12 bg-[#C89B3C]"></div>

                                <span class="text-blue-100/80 text-sm">
                                    Excellence • Éducation • Réussite
                                </span>

                                <div class="h-px w-12 bg-[#C89B3C]"></div>

                            </div>

                        </div>


                        <!-- Description -->

                        <p class="text-blue-100/70
                                  mt-7
                                  max-w-md
                                  text-sm md:text-base
                                  leading-relaxed
                                  text-center md:text-left">

                            Bienvenue dans votre espace
                            d'administration.
                            Gérez votre académie simplement,
                            rapidement et efficacement.

                        </p>


                    </div>

                </div>


                <!-- =========================
                     GOLD SEPARATOR
                ========================= -->

                <div class="hidden md:flex
                            absolute
                            left-1/2
                            -translate-x-1/2
                            h-64
                            items-center">

                    <div class="gold-line
                                w-px
                                h-full
                                bg-gradient-to-b
                                from-transparent
                                via-[#C89B3C]
                                to-transparent">
                    </div>

                </div>


                <!-- =========================
                     RIGHT : LOGIN
                ========================= -->

                <div class="login-section">


                    <div class="login-card
                                bg-white
                                rounded-[2rem]
                                shadow-2xl
                                overflow-hidden
                                border border-white/30">


                        <!-- Card Header -->

                        <div class="px-8 md:px-10
                                    pt-9 pb-7
                                    text-center">


                            <div class="inline-flex
                                        items-center
                                        justify-center
                                        w-14 h-14
                                        rounded-2xl
                                        bg-[#0B2A55]
                                        shadow-lg
                                        mb-4">

                                <span class="text-2xl">
                                    🔐
                                </span>

                            </div>


                            <h2 class="text-3xl
                                       font-extrabold
                                       text-[#0B2A55]">

                                Connexion

                            </h2>


                            <p class="text-gray-500 mt-2">

                                Connectez-vous à votre
                                compte administrateur.

                            </p>

                        </div>


                        <!-- Gold line -->

                        <div class="h-1
                                    bg-gradient-to-r
                                    from-transparent
                                    via-[#C89B3C]
                                    to-transparent">
                        </div>


                        <!-- Form -->

                        <div class="p-8 md:p-10">


                            @if (session('status'))

                                <div class="mb-6 p-4 rounded-xl
                                            bg-green-50
                                            border border-green-200
                                            text-green-700 text-sm">

                                    {{ session('status') }}

                                </div>

                            @endif


                            <form method="POST"
                                  action="{{ route('login') }}">

                                @csrf


                                <!-- EMAIL -->

                                <div class="mb-6">

                                    <label
                                        for="email"
                                        class="block text-sm
                                               font-bold
                                               text-[#0B2A55]
                                               mb-2">

                                        Adresse e-mail

                                    </label>


                                    <div class="relative">

                                        <span class="absolute
                                                     left-4
                                                     top-1/2
                                                     -translate-y-1/2
                                                     text-[#C89B3C]
                                                     text-xl">

                                            ✉

                                        </span>


                                        <input
                                            id="email"
                                            type="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            required
                                            autofocus
                                            autocomplete="username"
                                            placeholder="exemple@email.com"

                                            class="w-full
                                                   pl-12 pr-4 py-4
                                                   rounded-xl
                                                   border-2
                                                   border-gray-200
                                                   bg-gray-50
                                                   outline-none
                                                   transition

                                                   focus:border-[#C89B3C]
                                                   focus:ring-4
                                                   focus:ring-[#C89B3C]/10"
                                        >

                                    </div>


                                    @error('email')

                                        <p class="mt-2 text-sm
                                                  text-red-600">

                                            {{ $message }}

                                        </p>

                                    @enderror

                                </div>


                                <!-- PASSWORD -->

                                <div class="mb-6">

                                    <div class="flex
                                                justify-between
                                                items-center
                                                mb-2">

                                        <label
                                            for="password"
                                            class="text-sm
                                                   font-bold
                                                   text-[#0B2A55]">

                                            Mot de passe

                                        </label>


                                        @if (Route::has('password.request'))

                                            <a
                                                href="{{ route('password.request') }}"
                                                class="text-sm
                                                       font-semibold
                                                       text-[#C89B3C]
                                                       hover:text-[#0B2A55]
                                                       transition">

                                                Mot de passe oublié ?

                                            </a>

                                        @endif

                                    </div>


                                    <div class="relative">


                                        <span class="absolute
                                                     left-4
                                                     top-1/2
                                                     -translate-y-1/2
                                                     text-[#C89B3C]
                                                     text-xl">

                                            🔒

                                        </span>


                                        <input
                                            id="password"
                                            type="password"
                                            name="password"
                                            required
                                            autocomplete="current-password"
                                            placeholder="••••••••"

                                            class="w-full
                                                   pl-12 pr-14 py-4
                                                   rounded-xl
                                                   border-2
                                                   border-gray-200
                                                   bg-gray-50
                                                   outline-none
                                                   transition

                                                   focus:border-[#C89B3C]
                                                   focus:ring-4
                                                   focus:ring-[#C89B3C]/10"
                                        >


                                        <button
                                            type="button"
                                            onclick="togglePassword()"

                                            class="absolute
                                                   right-4
                                                   top-1/2
                                                   -translate-y-1/2
                                                   text-gray-400
                                                   hover:text-[#C89B3C]
                                                   text-xl
                                                   transition">

                                            👁️

                                        </button>

                                    </div>


                                    @error('password')

                                        <p class="mt-2 text-sm
                                                  text-red-600">

                                            {{ $message }}

                                        </p>

                                    @enderror

                                </div>


                                <!-- REMEMBER -->

                                <div class="flex items-center mb-7">

                                    <input
                                        id="remember_me"
                                        type="checkbox"
                                        name="remember"

                                        class="w-4 h-4
                                               rounded
                                               border-gray-300
                                               text-[#0B2A55]
                                               focus:ring-[#C89B3C]"
                                    >


                                    <label
                                        for="remember_me"
                                        class="ml-3
                                               text-sm
                                               text-gray-600">

                                        Se souvenir de moi

                                    </label>

                                </div>


                                <!-- BUTTON -->

                                <button
                                    type="submit"

                                    class="login-button
                                           w-full
                                           py-4
                                           rounded-xl

                                           bg-[#0B2A55]
                                           hover:bg-[#061A33]

                                           text-white
                                           font-bold
                                           text-lg

                                           shadow-lg

                                           border-b-4
                                           border-[#C89B3C]">

                                    Se connecter

                                </button>


                            </form>

                        </div>

                    </div>


                    <!-- Footer -->

                    <p class="text-center
                              text-sm
                              text-blue-100/60
                              mt-6">

                        © {{ date('Y') }}

                        <span class="text-[#C89B3C]
                                     font-semibold">

                            Académie El Tafawok

                        </span>

                        — Tous droits réservés.

                    </p>


                </div>

            </div>

        </div>

    </div>


    <!-- =========================
         PASSWORD SCRIPT
    ========================= -->

    <script>

        function togglePassword() {

            const password =
                document.getElementById('password');

            if (password.type === 'password') {

                password.type = 'text';

            } else {

                password.type = 'password';

            }

        }

    </script>

</body>

</html>