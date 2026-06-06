<x-admin-layout title="Thesis records">
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <x-page-heading title="Thesis records">
            All theses across every department — drafts and published. Read-only.
        </x-page-heading>
    </div>

    <x-card>
        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.theses.index') }}"
              class="flex flex-wrap items-end gap-x-4 gap-y-3 mb-5 pb-5 border-b border-text/10">

            {{-- Search --}}
            <div class="flex-1 min-w-52">
                <label class="block text-xs font-semibold text-text/60 mb-1">Search</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-text/40">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                    </span>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}"
                           placeholder="Title, author, abstract…"
                           class="w-full rounded-md border-0 bg-input py-2 pl-9 pr-3 text-sm text-text placeholder:text-text/40 focus:ring-2 focus:ring-cyan">
                </div>
            </div>

            {{-- Department --}}
            <div>
                <label class="block text-xs font-semibold text-text/60 mb-1">Department</label>
                <select name="department_id" onchange="this.form.requestSubmit()"
                        class="rounded-md border-0 bg-input text-sm text-text focus:ring-2 focus:ring-cyan">
                    <option value="">All departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}"
                                {{ (string) ($filters['department_id'] ?? '') === (string) $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-semibold text-text/60 mb-1">Status</label>
                <select name="status" onchange="this.form.requestSubmit()"
                        class="rounded-md border-0 bg-input text-sm text-text focus:ring-2 focus:ring-cyan">
                    <option value="">All statuses</option>
                    <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft"     {{ ($filters['status'] ?? '') === 'draft'     ? 'selected' : '' }}>Draft</option>
                </select>
            </div>

            {{-- Year range --}}
            <div>
                <label class="block text-xs font-semibold text-text/60 mb-1">Year range</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="year_from" value="{{ $filters['year_from'] ?? '' }}"
                           placeholder="From" min="1900" max="2200"
                           class="w-24 rounded-md border-0 bg-input text-sm text-text focus:ring-2 focus:ring-cyan">
                    <span class="text-text/40 font-bold">–</span>
                    <input type="number" name="year_to" value="{{ $filters['year_to'] ?? '' }}"
                           placeholder="To" min="1900" max="2200"
                           class="w-24 rounded-md border-0 bg-input text-sm text-text focus:ring-2 focus:ring-cyan">
                </div>
            </div>

            <div class="flex gap-2">
                <x-btn type="submit" variant="primary">Filter</x-btn>
                @if (array_filter($filters))
                    <x-btn href="{{ route('admin.theses.index') }}" variant="ghost">Clear</x-btn>
                @endif
            </div>
        </form>

        {{-- Result count --}}
        <p class="mb-4 text-sm font-semibold text-text/60">
            {{ $theses->total() }} {{ Str::plural('record', $theses->total()) }}
        </p>

        @if ($theses->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold uppercase tracking-wide text-text/50 border-b border-text/10">
                            <th class="py-3 pr-4 font-bold">Title</th>
                            <th class="py-3 pr-4 font-bold">Department</th>
                            <th class="py-3 pr-4 font-bold">Status</th>
                            <th class="py-3 pr-4 font-bold">Year</th>
                            <th class="py-3 pr-4 font-bold">Authors</th>
                            <th class="py-3 pl-4 font-bold text-right">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($theses as $thesis)
                            <tr class="border-b border-text/10 last:border-0 align-top">
                                <td class="py-4 pr-4 max-w-sm">
                                    <a href="{{ route('admin.theses.show', $thesis) }}"
                                       class="font-semibold text-navy hover:underline leading-snug">
                                        {{ $thesis->title }}
                                    </a>
                                </td>
                                <td class="py-4 pr-4 text-text/70 whitespace-nowrap">
                                    {{ $thesis->department->name }}
                                </td>
                                <td class="py-4 pr-4 whitespace-nowrap">
                                    @if ($thesis->isPublished())
                                        <x-badge tone="green">Published</x-badge>
                                    @else
                                        <x-badge tone="gray">Draft</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 pr-4 text-text/70">{{ $thesis->year }}</td>
                                <td class="py-4 pr-4 text-text/70 max-w-xs">
                                    {{ $thesis->authors->pluck('name')->join(', ') ?: '—' }}
                                </td>
                                <td class="py-4 pl-4 text-right">
                                    <a href="{{ route('admin.theses.show', $thesis) }}"
                                       class="rounded-md border border-text/15 bg-surface px-3 py-1.5 text-xs font-semibold text-text hover:bg-bg hover:border-text/30 transition">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $theses->links() }}</div>
        @else
            <div class="py-12 text-center">
                <p class="text-sm font-semibold text-navy">No thesis records found</p>
                <p class="mt-1 text-sm text-text/60">
                    {{ array_filter($filters) ? 'No records match your filters.' : 'No theses have been catalogued yet.' }}
                </p>
            </div>
        @endif
    </x-card>
</x-admin-layout>
