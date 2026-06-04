<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? config('app.name', 'Thesis Inventory System') }}</title>

        {{-- Source Sans 3 is loaded via resources/css/app.css (@theme --font-sans). --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-text antialiased">
        <div class="min-h-screen flex flex-col bg-bg">
            {{-- Public viewer header — no authentication required (SRS viewer class). --}}
            <header class="bg-navy text-surface">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <a href="{{ url('/') }}" class="text-xl font-bold">
                        {{ config('app.name', 'Thesis Inventory System') }}
                    </a>
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-gold hover:underline">
                        Staff Login
                    </a>
                </div>
            </header>

            <main class="flex-1">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {{ $slot }}
                </div>
            </main>

            <footer class="bg-sidebar text-surface/70">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-sm">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Thesis Inventory System') }}
                </div>
            </footer>
        </div>
    </body>
</html>
