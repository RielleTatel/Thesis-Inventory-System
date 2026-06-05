{{-- A single thesis in the browse results grid. Links to the detail page. --}}
@props(['thesis'])

<a href="{{ route('public.thesis.show', $thesis) }}"
   class="group flex flex-col gap-3 p-5 rounded-lg bg-surface border border-text/10 shadow-sm
          hover:shadow-md hover:border-cyan transition focus:outline-none focus:ring-2 focus:ring-cyan">
    <div class="flex items-center gap-2 flex-wrap">
        <x-badge tone="cyan">{{ $thesis->year }}</x-badge>
        <span class="text-xs font-semibold text-text/60">{{ $thesis->program }}</span>
    </div>

    <h3 class="text-lg font-semibold text-navy leading-snug text-pretty group-hover:underline">
        {{ $thesis->title }}
    </h3>

    @if ($thesis->authors->isNotEmpty())
        <p class="text-sm font-semibold text-text/70">{{ $thesis->authors->pluck('name')->join(', ') }}</p>
    @endif

    <p class="text-sm text-text/70 leading-relaxed line-clamp-3">{{ $thesis->abstract }}</p>

    <div class="mt-auto pt-1 flex flex-wrap gap-1.5">
        @foreach ($thesis->keywords->take(3) as $keyword)
            <x-chip kind="keyword">{{ $keyword->name }}</x-chip>
        @endforeach
        @if ($thesis->keywords->count() > 3)
            <span class="self-center text-xs font-medium text-text/50">+{{ $thesis->keywords->count() - 3 }}</span>
        @endif
    </div>
</a>
