<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? config('app.name', 'AdZU Thesis Archives') }}</title>

        {{-- Source Sans 3 is loaded via resources/css/app.css (@theme --font-sans). --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-text antialiased">
        <div class="min-h-screen flex flex-col bg-bg">
            {{-- Public viewer header — no authentication required (SRS viewer class). --}}
            @php
                // Two-tone wordmark: first word emphasized, remainder muted (design reference).
                [$brandLead, $brandRest] = array_pad(explode(' ', config('app.name', 'AdZU Thesis Archives'), 2), 2, '');
            @endphp
            {{-- Distinct header bar: navy with a thin light border + soft shadow so it
                 reads as its own bar above the hero (a deliberate change from the design). --}}
            <header class="relative z-10 bg-navy text-surface border-b border-surface/10 shadow-md">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">
                    <a href="{{ route('public.thesis.index') }}" class="flex items-center gap-3">
                        <span class="grid place-items-center w-9 h-9 rounded-lg bg-gold text-navy shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2z"/><path d="M9 7h6M9 10h6"/>
                            </svg>
                        </span>
                        <span class="text-xl leading-tight">
                            <span class="font-bold text-surface">{{ $brandLead }}</span>@if ($brandRest !== '')<span class="font-normal text-surface/70"> {{ $brandRest }}</span>@endif
                        </span>
                    </a>

                    <nav class="flex items-center gap-6">
                        <a href="{{ route('public.thesis.index') }}" class="text-sm font-semibold hover:text-gold transition">
                            Browse
                        </a>
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2 rounded-md bg-gold px-3 py-1.5 text-sm font-semibold text-navy hover:bg-gold/90 transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Sign in
                        </a>
                    </nav>
                </div>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="bg-sidebar text-surface/70">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-sm">
                    &copy; {{ date('Y') }} {{ config('app.name', 'AdZU Thesis Archives') }}
                </div>
            </footer>
        </div>
    </body>
</html>
