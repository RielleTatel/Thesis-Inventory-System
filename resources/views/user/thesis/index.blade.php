<x-department-layout title="My theses">
    <div x-data="{ confirm: null }">
        {{-- Page head --}}
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <x-page-heading title="My theses">
                {{ $department?->name }} — records owned by your department
            </x-page-heading>
            <x-btn href="{{ route('department.theses.create') }}" variant="accent">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Add Thesis
            </x-btn>
        </div>

        {{-- Stat cards --}}
        <div class="grid gap-4 sm:grid-cols-3 mb-6">
            <x-card>
                <p class="text-xs font-bold uppercase tracking-wide text-text/50">Total records</p>
                <p class="mt-1 text-3xl font-bold text-navy">{{ $stats['total'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs font-bold uppercase tracking-wide text-text/50">Latest year</p>
                <p class="mt-1 text-3xl font-bold text-gold">{{ $stats['latest_year'] ?? '—' }}</p>
            </x-card>
            <x-card>
                <p class="text-xs font-bold uppercase tracking-wide text-text/50">Last updated</p>
                <p class="mt-1 text-3xl font-bold text-green">
                    {{ $stats['last_updated'] ? \Illuminate\Support\Carbon::parse($stats['last_updated'])->format('Y-m-d') : '—' }}
                </p>
            </x-card>
        </div>

        {{-- Records --}}
        <x-card>
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h2 class="text-lg font-semibold text-navy">Thesis records</h2>
                <form method="GET" action="{{ route('department.theses.index') }}" class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-text/40">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                    </span>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search my theses…"
                           class="w-64 max-w-full rounded-md border-0 bg-input py-2 pl-9 pr-3 text-sm text-text placeholder:text-text/40 focus:ring-2 focus:ring-cyan">
                </form>
            </div>

            @if ($theses->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-bold uppercase tracking-wide text-text/50 border-b border-text/10">
                                <th class="py-3 pr-4 font-bold">Title</th>
                                <th class="py-3 pr-4 font-bold">Year</th>
                                <th class="py-3 pr-4 font-bold">Authors</th>
                                <th class="py-3 pr-4 font-bold">Last updated</th>
                                <th class="py-3 pl-4 font-bold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($theses as $thesis)
                                <tr class="border-b border-text/10 last:border-0 align-top">
                                    <td class="py-4 pr-4 max-w-md">
                                        <a href="{{ route('department.theses.edit', $thesis) }}"
                                           class="font-semibold text-navy hover:underline">{{ $thesis->title }}</a>
                                    </td>
                                    <td class="py-4 pr-4 text-text/70">{{ $thesis->year }}</td>
                                    <td class="py-4 pr-4 text-text/70 max-w-xs">{{ $thesis->authors->pluck('name')->join(', ') ?: '—' }}</td>
                                    <td class="py-4 pr-4 text-text/70 whitespace-nowrap">{{ $thesis->updated_at?->format('Y-m-d') }}</td>
                                    <td class="py-4 pl-4">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('department.theses.edit', $thesis) }}"
                                               class="grid place-items-center w-8 h-8 rounded-md border border-text/10 text-text/60 hover:text-navy hover:border-navy transition"
                                               aria-label="Edit">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>
                                                </svg>
                                            </a>
                                            <button type="button"
                                                    @click="confirm = { action: '{{ route('department.theses.destroy', $thesis) }}', title: @js($thesis->title) }"
                                                    class="grid place-items-center w-8 h-8 rounded-md border border-text/10 text-text/60 hover:text-danger hover:border-danger transition"
                                                    aria-label="Delete">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $theses->links() }}</div>
            @else
                <div class="py-12 text-center">
                    <p class="text-sm font-semibold text-navy">No thesis records yet</p>
                    <p class="mt-1 text-sm text-text/60">
                        {{ ($filters['q'] ?? '') !== '' ? 'No records match your search.' : 'Add your first thesis to get started.' }}
                    </p>
                    @if (($filters['q'] ?? '') === '')
                        <div class="mt-4">
                            <x-btn href="{{ route('department.theses.create') }}" variant="accent">Add Thesis</x-btn>
                        </div>
                    @endif
                </div>
            @endif
        </x-card>

        {{-- Delete confirmation --}}
        <div x-show="confirm" x-cloak @keydown.escape.window="confirm = null"
             class="fixed inset-0 z-30 grid place-items-center bg-text/40 p-4" style="display:none">
            <div class="bg-surface rounded-lg shadow-lg max-w-md w-full p-6" @click.outside="confirm = null">
                <h3 class="text-lg font-semibold text-navy">Delete thesis record</h3>
                <p class="mt-2 text-sm text-text/70 leading-relaxed">
                    Permanently delete “<span class="font-semibold text-text" x-text="confirm?.title"></span>”?
                    This removes it from the public archive and cannot be undone.
                </p>
                <form method="POST" :action="confirm?.action" class="mt-6 flex justify-end gap-3">
                    @csrf
                    @method('DELETE')
                    <x-btn type="button" variant="ghost" @click="confirm = null">Cancel</x-btn>
                    <x-btn type="submit" variant="danger">Delete record</x-btn>
                </form>
            </div>
        </div>
    </div>
</x-department-layout>
