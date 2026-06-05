<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AdZU Thesis Archives') }}</title>

        {{-- Source Sans 3 is loaded via resources/css/app.css (@theme --font-sans). --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-text antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-navy">
            <div>
                <a href="/" class="text-2xl font-bold text-surface">
                    {{ config('app.name', 'AdZU Thesis Archives') }}
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-surface shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
