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
                {{-- Keyword multi-select state is shared between the picker (in the
                     controls row) and the active-keywords chip row below it, so the
                     x-data scope wraps both. --}}
                @php
                    $selectedKeywords = array_values(array_filter(
                        (array) ($filters['keyword'] ?? []),
                        fn ($name) => $name !== null && $name !== '',
                    ));
                @endphp
                <div x-data="{
                        selected: @js($selectedKeywords),
                        add(name) {
                            if (name && ! this.selected.includes(name)) {
                                this.selected.push(name);
                                this.$nextTick(() => this.$root.closest('form').requestSubmit());
                            }
                        },
                        remove(name) {
                            this.selected = this.selected.filter(k => k !== name);
                            this.$nextTick(() => this.$root.closest('form').requestSubmit());
                        },
                        toggle(name) {
                            this.selected.includes(name) ? this.remove(name) : this.add(name);
                        },
                     }">
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

                    {{-- Keywords (multi-select). The panel is rendered by <x-floating-dropdown>,
                         which teleports it to <body> and anchors it to the trigger so the
                         card's overflow-hidden can't clip it; it scrolls internally past
                         16rem and flips up only when there's no room below. Selected rows show
                         a brand-cyan tint AND a right-edge check (color + shape, never color
                         alone); labels stay left-aligned so nothing shifts. Hidden keyword[]
                         inputs keep the URL shareable. --}}
                    <div>
                        <label class="block text-xs font-semibold text-text/60 mb-1">Keywords</label>

                        <template x-for="name in selected" :key="name">
                            <input type="hidden" name="keyword[]" :value="name">
                        </template>

                        <x-floating-dropdown align="bottom-start" width="w-64"
                                             trigger-class="flex min-w-44 items-center justify-between gap-2 rounded-md bg-input px-3 py-2 text-sm text-text focus:outline-none focus:ring-2 focus:ring-cyan">
                            <x-slot:trigger>
                                <span class="text-text/70">Add keyword…</span>
                                <svg class="h-4 w-4 text-text/40 transition" :class="open && 'rotate-180'"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6"/>
                                </svg>
                            </x-slot:trigger>

                            @foreach ($keywords as $keyword)
                                {{-- Selected: cyan tint + full-strength navy text + right check.
                                     Unselected: neutral text + distinct gray hover. --}}
                                <button type="button" role="option" @click="toggle(@js($keyword))"
                                        :aria-selected="selected.includes(@js($keyword))"
                                        :class="selected.includes(@js($keyword))
                                            ? 'bg-cyan/15 text-navy font-semibold hover:bg-cyan/25'
                                            : 'text-text hover:bg-bg'"
                                        class="flex w-full items-center justify-between gap-3 px-3 py-1.5 text-left text-sm">
                                    <span>{{ $keyword }}</span>
                                    <svg x-show="selected.includes(@js($keyword))" style="display:none"
                                         class="h-4 w-4 shrink-0 text-navy" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                         stroke-linejoin="round" aria-hidden="true">
                                        <path d="M20 6 9 17l-5-5"/>
                                    </svg>
                                </button>
                            @endforeach
                        </x-floating-dropdown>
                    </div>

                    @if (array_filter($filters))
                        <x-btn href="{{ route('public.thesis.index') }}" variant="ghost" class="self-center">Clear</x-btn>
                    @endif

                    <div class="ml-auto flex items-center gap-3 self-center">
                        <span class="text-sm font-semibold text-text/70">
                            {{ $theses->total() }} {{ Str::plural('result', $theses->total()) }}
                        </span>

                        {{-- View toggle — persisted in localStorage --}}
                        <div x-data="{
                                view: localStorage.getItem('thesis-view') || 'card',
                                set(v) { this.view = v; localStorage.setItem('thesis-view', v); }
                             }"
                             x-init="$watch('view', v => $dispatch('view-changed', v))"
                             class="flex items-center rounded-md border border-text/15 overflow-hidden">
                            <button type="button" @click="set('card')"
                                    :class="view === 'card' ? 'bg-navy text-surface' : 'bg-surface text-text/50 hover:text-navy hover:bg-bg'"
                                    class="grid place-items-center w-8 h-8 transition"
                                    title="Card view" aria-label="Card view">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                                </svg>
                            </button>
                            <button type="button" @click="set('table')"
                                    :class="view === 'table' ? 'bg-navy text-surface' : 'bg-surface text-text/50 hover:text-navy hover:bg-bg'"
                                    class="grid place-items-center w-8 h-8 transition"
                                    title="Table view" aria-label="Table view">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>{{-- /controls row --}}

                {{-- Active keywords — their own full-width row below the controls. --}}
                <div class="mt-4 flex flex-wrap items-center gap-1.5" x-show="selected.length" style="display:none">
                    <span class="text-xs font-semibold text-text/50">Active keywords:</span>
                    <template x-for="name in selected" :key="name">
                        <span class="inline-flex items-center gap-1 rounded-full bg-cyan/15 py-1 pl-2.5 pr-1 text-xs font-semibold text-navy">
                            <span x-text="name"></span>
                            <button type="button" @click="remove(name)"
                                    class="grid h-4 w-4 place-items-center rounded-full hover:bg-cyan/30"
                                    :aria-label="'Remove ' + name">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M18 6 6 18M6 6l12 12"/>
                                </svg>
                            </button>
                        </span>
                    </template>
                </div>
                </div>{{-- /x-data wrapper --}}
            </x-card>

            {{-- Results --}}
            @if ($theses->isNotEmpty())
                <div x-data="{
                        view: localStorage.getItem('thesis-view') || 'card',
                     }"
                     @view-changed.window="view = $event.detail">

                    {{-- Card view --}}
                    <div x-show="view === 'card'" style="display:none">
                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($theses as $thesis)
                                <x-thesis-card :thesis="$thesis" />
                            @endforeach
                        </div>
                    </div>

                    {{-- Table view --}}
                    <div x-show="view === 'table'" style="display:none">
                        <x-card>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-xs font-bold uppercase tracking-wide text-text/50 border-b border-text/10">
                                            <th class="py-3 pr-4 font-bold">Title</th>
                                            <th class="py-3 pr-4 font-bold">Authors</th>
                                            <th class="py-3 pr-4 font-bold">Department</th>
                                            <th class="py-3 pr-4 font-bold">Program</th>
                                            <th class="py-3 font-bold">Year</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($theses as $thesis)
                                            <tr class="border-b border-text/10 last:border-0 cursor-pointer hover:bg-bg transition"
                                                @click="window.location = '{{ route('public.thesis.show', $thesis) }}'">
                                                <td class="py-3 pr-4 max-w-xs">
                                                    <span class="font-semibold text-navy leading-snug">{{ $thesis->title }}</span>
                                                </td>
                                                <td class="py-3 pr-4 text-text/70 max-w-[14rem]">
                                                    {{ $thesis->authors->pluck('name')->join('; ') ?: '—' }}
                                                </td>
                                                <td class="py-3 pr-4 text-text/70 whitespace-nowrap">
                                                    {{ $thesis->department->name }}
                                                </td>
                                                <td class="py-3 pr-4 text-text/70 whitespace-nowrap">
                                                    {{ $thesis->program }}
                                                </td>
                                                <td class="py-3 text-text/70">{{ $thesis->year }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </x-card>
                    </div>
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
