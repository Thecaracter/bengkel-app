<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex">
        <!-- Mobile menu button (top-right) -->
        <div class="md:hidden fixed top-4 right-4 z-30">
            <button @click="sidebarOpen = !sidebarOpen" 
                    class="p-2 rounded-md text-gray-800 hover:bg-gray-200">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>

        <!-- Sidebar with slide-from-right -->
        <div class="flex-shrink-0">
            <div x-show="sidebarOpen" 
                 class="fixed inset-0 bg-black bg-opacity-50 z-10 md:hidden"
                 @click="sidebarOpen = false">
            </div>
            
            <div x-cloak
                 :class="{'translate-x-0': sidebarOpen, 'translate-x-full': !sidebarOpen}"
                 class="fixed md:static inset-y-0 right-0 z-20 bg-gradient-to-b from-blue-900 to-indigo-900 w-64 flex-shrink-0 transition-transform duration-300 ease-in-out transform md:translate-x-0 h-full overflow-y-auto">
                @include('layouts.navigation')
            </div>
        </div>
        
        <!-- Main content -->
        <div class="flex-1 flex flex-col bg-gray-100 overflow-hidden">
            @if (session('success') || session('error'))
            <div class="fixed top-4 right-4 z-50">
                <div class="rounded-lg shadow-lg {{ session('success') ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500' }} p-4"
                     x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 3000)">
                    <div class="flex items-center">
                        <p class="text-sm font-medium {{ session('success') ? 'text-green-800' : 'text-red-800' }}">
                            {{ session('success') ?? session('error') }}
                        </p>
                        <button @click="show = false" class="ml-4 text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1 overflow-y-auto">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </div>

    @vite(['resources/js/fontawesome.js'])
    @stack('scripts')
</body>
</html>