{{-- Shared authenticated shell: navy navbar + dark sidebar + centered content.
     Used by both the department and admin layouts (coding standard #8 — one shell).
     Props:
       area  — sidebar section label (e.g. "Department" / "Administration")
       role  — navbar role caption (e.g. "Department" / "Administrator")
       home  — brand/home URL
     Slots: `nav` (sidebar links), default (page content). --}}
@props(['area' => '', 'role' => '', 'home' => '/', 'title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'AdZU Thesis Archives') }}</title>

        {{-- Source Sans 3 is loaded via resources/css/app.css (@theme --font-sans). --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-text antialiased">
        @php
            [$brandLead, $brandRest] = array_pad(explode(' ', config('app.name', 'AdZU Thesis Archives'), 2), 2, '');
            $user = auth()->user();
            $displayName = $user?->department?->name ?? $user?->name;
            $initials = $user?->department?->code
                ?? \Illuminate\Support\Str::of($user?->name ?? '')->explode(' ')->take(2)->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->implode('');
            $initials = \Illuminate\Support\Str::upper($initials ?: '?');
        @endphp

        <div x-data="{ sidebar: false }" class="min-h-screen bg-bg">
            {{-- Top navbar --}}
            <header class="relative z-20 bg-navy text-surface border-b border-surface/10 shadow-md">
                <div class="px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="sidebar = !sidebar"
                                class="lg:hidden grid place-items-center w-9 h-9 rounded-md hover:bg-surface/10 transition"
                                aria-label="Toggle sidebar">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <a href="{{ $home }}" class="flex items-center gap-3">
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
                    </div>

                    <div class="flex items-center gap-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold text-surface/80 hover:text-surface transition">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                                </svg>
                                Sign out
                            </button>
                        </form>

                        @if ($user)
                            <div class="flex items-center gap-2 pl-4 border-l border-surface/10">
                                <span class="grid place-items-center w-9 h-9 rounded-full bg-cyan/20 text-surface text-xs font-bold">{{ $initials }}</span>
                                <span class="hidden sm:block leading-tight">
                                    <span class="block text-sm font-semibold max-w-40 truncate">{{ $displayName }}</span>
                                    <span class="block text-xs text-surface/60">{{ $role }}</span>
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </header>

            <div class="flex">
                {{-- Sidebar --}}
                <aside class="fixed lg:static inset-y-0 left-0 z-10 w-64 shrink-0 bg-sidebar text-surface/80 pt-16 lg:pt-0
                              flex flex-col lg:min-h-[calc(100vh-4rem)] transition-transform lg:translate-x-0"
                       :class="sidebar ? 'translate-x-0' : '-translate-x-full'">
                    <nav class="p-4 space-y-1">
                        <p class="px-3 pt-2 pb-3 text-xs font-bold uppercase tracking-wider text-surface/40">{{ $area }}</p>
                        {{ $nav }}
                    </nav>

                    @if ($user)
                        <div class="mt-auto p-4 border-t border-surface/10">
                            <p class="text-xs text-surface/40">Signed in as</p>
                            <p class="text-sm font-bold text-surface truncate">{{ $displayName }}</p>
                        </div>
                    @endif
                </aside>

                {{-- Backdrop for mobile sidebar --}}
                <div x-show="sidebar" x-cloak @click="sidebar = false"
                     class="fixed inset-0 z-0 bg-text/40 lg:hidden" style="display:none"></div>

                {{-- Main content --}}
                <main class="flex-1 min-w-0 px-4 sm:px-6 lg:px-8 py-8">
                    {{-- Centered content cap (design .content-wrap ≈ 1160px). --}}
                    <div class="max-w-6xl mx-auto">
                        @if (session('status'))
                            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                                 class="mb-6 rounded-lg bg-green/10 border border-green/20 px-4 py-3 text-sm font-semibold text-green">
                                {{ session('status') }}
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
