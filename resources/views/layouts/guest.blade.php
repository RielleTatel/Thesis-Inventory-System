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
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-8 bg-navy">

            {{-- Logo icon --}}
            <div class="mb-4">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-gold">
                    <svg class="w-8 h-8 text-navy" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
            </div>

            {{-- Title --}}
            <h1 class="text-3xl sm:text-4xl text-surface mb-1">
                <span class="font-bold">AdZU</span> Thesis Archives
            </h1>

            {{-- Subtitle --}}
            <p class="text-surface/60 text-sm mb-8">Department &amp; Administrator sign-in</p>

            {{-- Card --}}
            <div class="w-full max-w-md bg-surface rounded-2xl shadow-xl px-8 py-8 sm:px-10">
                {{ $slot }}
            </div>

            {{-- Visitor link --}}
            <a href="{{ route('public.thesis.index') }}" class="mt-8 text-sm text-surface hover:text-gold transition inline-flex items-center gap-1">
                &larr; Browse theses as a visitor
            </a>
        </div>
    </body>
</html>
