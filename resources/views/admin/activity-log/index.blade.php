<x-admin-layout title="Activity log">
    <x-page-heading title="Activity log">
        Audit trail of account and thesis actions across the system.
    </x-page-heading>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.activity-log.index') }}" class="mt-6">
        <x-card class="mb-6">
            <div class="flex flex-wrap items-end gap-x-5 gap-y-4">
                <div class="flex-1 min-w-56">
                    <label class="block text-xs font-semibold text-text/60 mb-1">Search</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-text/40">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                        </span>
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search actor or affected record…"
                               class="w-full rounded-md border-0 bg-input py-2 pl-9 pr-3 text-sm text-text placeholder:text-text/40 focus:ring-2 focus:ring-cyan">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-text/60 mb-1">Action type</label>
                    <select name="action" onchange="this.form.requestSubmit()"
                            class="min-w-40 rounded-md border-0 bg-input text-sm text-text focus:ring-2 focus:ring-cyan">
                        <option value="">All actions</option>
                        @foreach ($actionTypes as $type)
                            <option value="{{ $type }}" @selected(($filters['action'] ?? '') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-text/60 mb-1">From</label>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" onchange="this.form.requestSubmit()"
                           class="rounded-md border-0 bg-input text-sm text-text focus:ring-2 focus:ring-cyan">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-text/60 mb-1">To</label>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" onchange="this.form.requestSubmit()"
                           class="rounded-md border-0 bg-input text-sm text-text focus:ring-2 focus:ring-cyan">
                </div>

                @if (array_filter($filters))
                    <x-btn href="{{ route('admin.activity-log.index') }}" variant="ghost" class="self-center">Clear</x-btn>
                @endif
            </div>
        </x-card>
    </form>

    {{-- Feed --}}
    <x-card>
        @if ($activities->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold uppercase tracking-wide text-text/50 border-b border-text/10">
                            <th class="py-3 pr-4">Actor</th>
                            <th class="py-3 pr-4">Action</th>
                            <th class="py-3 pr-4">Affected record</th>
                            <th class="py-3 pr-4">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activities as $activity)
                            @php
                                $tone = match ($activity->event) {
                                    'created' => 'green',
                                    'updated' => 'cyan',
                                    'deleted' => 'red',
                                    default => 'gray',
                                };
                                $mode = data_get($activity->properties, 'records_mode');
                            @endphp
                            <tr class="border-b border-text/10 last:border-0 align-top">
                                <td class="py-4 pr-4">
                                    <div class="font-semibold text-navy">{{ $activity->causer?->name ?? 'System' }}</div>
                                    <div class="text-xs text-text/50">{{ Str::title($activity->causer?->getRoleNames()->first() ?? '—') }}</div>
                                </td>
                                <td class="py-4 pr-4">
                                    <x-badge :tone="$tone">{{ ucfirst($activity->event ?? $activity->description) }}</x-badge>
                                    @if ($activity->log_name === 'account' && $mode)
                                        <span class="ml-1 text-xs text-text/50">records {{ $mode === 'delete' ? 'deleted' : 'kept' }}</span>
                                    @endif
                                </td>
                                <td class="py-4 pr-4 text-text/70">
                                    <x-activity-subject :activity="$activity" />
                                </td>
                                <td class="py-4 pr-4 text-text/60 whitespace-nowrap">{{ $activity->created_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $activities->links() }}</div>
        @else
            <div class="py-12 text-center">
                <div class="mx-auto grid place-items-center w-14 h-14 rounded-full bg-input text-navy/50">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-navy">No matching activity</h3>
                <p class="mt-1 text-sm text-text/60 max-w-md mx-auto">
                    {{ array_filter($filters) ? 'Adjust the filters or search term to see logged actions.' : 'Account and thesis actions will appear here as they happen.' }}
                </p>
            </div>
        @endif
    </x-card>
</x-admin-layout>
