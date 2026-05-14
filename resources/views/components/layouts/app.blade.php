<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Espace Candidat' }}</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-slate-50 text-slate-900 font-sans antialiased">
        
        <!-- Topbar Navigation -->
        <nav class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="/" class="font-bold text-2xl text-indigo-700">
                                2Earn
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="/candidate/dashboard" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('candidate/dashboard') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                                     {{ __('Tableau de bord') }} 
                                
                            </a>
                            <a href="/candidate/choix-candidature" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('candidate/choix-candidature') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                                 {{ __('Offres') }} 
                            </a>
                            <a href="/candidate/my-profile" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('candidate/my-profile') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                                {{ __('Mon Profil') }} 
                            </a>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <!-- Settings Dropdown / Logout -->
                        <div class="ml-3 relative flex items-center gap-4">
                            <span class="text-sm font-medium text-gray-500">{{ auth()->user()->name ?? 'Candidat' }}</span>
                            <form method="POST" action="/candidate/logout">
                                @csrf
                                <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium cursor-pointer">
                                    {{ __('Déconnexion') }} 
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>
