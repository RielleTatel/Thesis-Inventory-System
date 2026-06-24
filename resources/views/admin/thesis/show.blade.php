<x-admin-layout :title="$thesis->title">
    <div class="max-w-4xl">
        <a href="{{ route('admin.theses.index') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-text/70 hover:text-navy transition mb-5">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Back to thesis records
        </a>

        @if (! $thesis->isPublished())
            <div class="mb-5 flex items-center gap-3 rounded-lg border border-text/20 bg-text/5 px-4 py-3 text-sm">
                <x-badge tone="gray">Draft</x-badge>
                <span class="text-text/70">This record is a draft — not visible to the public viewer.</span>
            </div>
        @endif

        <article class="bg-surface rounded-lg shadow-sm overflow-hidden border border-text/10">
            <div class="h-1.5 bg-cyan"></div>

            <div class="p-6 sm:p-9">
                {{-- Meta row --}}
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <x-badge tone="cyan">{{ $thesis->year }}</x-badge>
                    <span class="text-sm font-semibold text-text/70">{{ $thesis->program }}</span>
                    <span class="text-text/30">•</span>
                    <span class="text-sm text-text/60">{{ $thesis->department->name }}</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold leading-tight text-pretty text-navy">
                    {{ $thesis->title }}
                </h1>

                {{-- Authors --}}
                @if ($thesis->authors->isNotEmpty())
                    <div class="mt-5">
                        <x-section-label>Authors</x-section-label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($thesis->authors as $author)
                                <x-chip kind="person">{{ $author->name }}</x-chip>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Abstract --}}
                <div class="mt-8">
                    <x-section-label :underline="true">Abstract</x-section-label>
                    <x-long-text class="mt-3" :text="$thesis->abstract" placeholder="No abstract provided." />
                </div>

                {{-- Recommendations --}}
                <div class="mt-8">
                    <x-section-label :underline="true">Recommendations</x-section-label>
                    <x-long-text class="mt-3" :text="$thesis->recommendations" placeholder="No recommendations provided." />
                </div>

                {{-- Field rows --}}
                <dl class="mt-8">
                    <x-detail-row label="Adviser">
                        @forelse ($thesis->advisers as $adviser)
                            <x-chip kind="person">{{ $adviser->name }}</x-chip>
                        @empty
                            <span class="text-sm text-text/50">—</span>
                        @endforelse
                    </x-detail-row>

                    <x-detail-row label="Panelists">
                        @forelse ($thesis->panelists as $panelist)
                            <x-chip kind="person">{{ $panelist->name }}</x-chip>
                        @empty
                            <span class="text-sm text-text/50">—</span>
                        @endforelse
                    </x-detail-row>

                    {{-- Optional: an empty proofreaders relationship displays as "N/A"
                         (display concern only — never stored as a literal value). --}}
                    <x-detail-row label="Proofreader">
                        @forelse ($thesis->proofreaders as $proofreader)
                            <x-chip kind="person">{{ $proofreader->name }}</x-chip>
                        @empty
                            <span class="text-sm text-text/50">N/A</span>
                        @endforelse
                    </x-detail-row>

                    {{-- Approval/signature page backs up the Adviser/Panelists above. --}}
                    <x-approval-page-viewer :thesis="$thesis" />

                    <x-detail-row label="Keywords">
                        @forelse ($thesis->keywords as $keyword)
                            <x-chip kind="keyword">{{ $keyword->name }}</x-chip>
                        @empty
                            <span class="text-sm text-text/50">—</span>
                        @endforelse
                    </x-detail-row>

                    <x-detail-row label="Program" :divider="false">
                        <span class="text-sm text-text">{{ $thesis->program }} — {{ $thesis->department->name }}</span>
                    </x-detail-row>
                </dl>
            </div>
        </article>
    </div>
</x-admin-layout>
