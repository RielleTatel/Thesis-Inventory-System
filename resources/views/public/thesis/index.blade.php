<x-public-layout title="Browse theses">
    {{-- One GET form spans the hero search box AND the filter card so they
         submit together. Selects auto-submit on change for instant filtering. --}}
    <form method="GET" action="{{ route('public.thesis.index') }}">
        {{-- Hero search. Mirrors the design's structure: the navbar is flat
             bg-navy and the hero is a navy→navy-deep vertical gradient whose
             TOP equals the navbar's navy, so the two join seamlessly with no
             edge (no border, no shade mismatch). --}}
        <section class="bg-linear-to-b from-navy to-navy-deep text-surface">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
                <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">Search the thesis archive</h1>
                <p class="mt-3 text-surface/70 text-base sm:text-lg">
                    Browse catalogued theses across every department — free and open to the public.
                </p>

                <div class="relative mt-6">
                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-text/40">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                    </span>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}"
                           placeholder="Search by title, author, abstract, or keyword…"
                           class="w-full rounded-lg border-0 bg-surface py-4 pl-12 pr-4 text-base text-text shadow-md
                                  placeholder:text-text/40 focus:ring-2 focus:ring-cyan">
                    <button type="submit" class="sr-only">Search</button>
                </div>
            </div>
        </section>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{-- Filters --}}
            <x-card class="mb-6">
                <div class="flex flex-wrap items-end gap-x-5 gap-y-4">
                    <div class="flex items-center gap-2 self-center font-bold text-sm text-text/70">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>
                        </svg>
                        Filters
                    </div>

                    {{-- Year range --}}
                    <div>
                        <label class="block text-xs font-semibold text-text/60 mb-1">Year range</label>
                        <div class="flex items-center gap-2">
                            <select name="year_from" onchange="this.form.requestSubmit()" aria-label="From year"
                                    class="rounded-md border-0 bg-input text-sm text-text focus:ring-2 focus:ring-cyan">
                                <option value="">From</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" @selected(($filters['year_from'] ?? '') == $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                            <span class="text-text/40 font-bold">–</span>
                            <select name="year_to" onchange="this.form.requestSubmit()" aria-label="To year"
                                    class="rounded-md border-0 bg-input text-sm text-text focus:ring-2 focus:ring-cyan">
                                <option value="">To</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" @selected(($filters['year_to'] ?? '') == $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Program --}}
                    <div>
                        <label class="block text-xs font-semibold text-text/60 mb-1">Program</label>
                        <select name="program" onchange="this.form.requestSubmit()"
                                class="min-w-56 rounded-md border-0 bg-input text-sm text-text focus:ring-2 focus:ring-cyan">
                            <option value="">All programs</option>
                            @foreach ($programs as $program)
                                <option value="{{ $program }}" @selected(($filters['program'] ?? '') === $program)>{{ $program }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Keyword --}}
                    <div>
                        <label class="block text-xs font-semibold text-text/60 mb-1">Keyword</label>
                        <select name="keyword" onchange="this.form.requestSubmit()"
                                class="min-w-44 rounded-md border-0 bg-input text-sm text-text focus:ring-2 focus:ring-cyan">
                            <option value="">All keywords</option>
                            @foreach ($keywords as $keyword)
                                <option value="{{ $keyword }}" @selected(($filters['keyword'] ?? '') === $keyword)>{{ $keyword }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if (array_filter($filters))
                        <x-btn href="{{ route('public.thesis.index') }}" variant="ghost" class="self-center">Clear</x-btn>
                    @endif

                    <div class="ml-auto self-center text-sm font-semibold text-text/70">
                        {{ $theses->total() }} {{ Str::plural('result', $theses->total()) }}
                    </div>
                </div>
            </x-card>

            {{-- Results --}}
            @if ($theses->isNotEmpty())
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($theses as $thesis)
                        <x-thesis-card :thesis="$thesis" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $theses->links() }}
                </div>
            @else
                <x-card>
                    <div class="py-16 text-center">
                        <div class="mx-auto grid place-items-center w-14 h-14 rounded-full bg-input text-navy/50">
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-navy">No theses match your search</h3>
                        <p class="mt-1 text-sm text-text/60 max-w-md mx-auto">
                            Try removing a filter or searching a broader term. The archive may not yet
                            contain a record for this topic.
                        </p>
                        @if (array_filter($filters))
                            <div class="mt-5">
                                <x-btn href="{{ route('public.thesis.index') }}" variant="ghost">Clear all filters</x-btn>
                            </div>
                        @endif
                    </div>
                </x-card>
            @endif
        </div>
    </form>
</x-public-layout>
