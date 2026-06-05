@php
    // Pre-fill from old() input (after a validation error) or the existing record.
    $vTitle = old('title', $thesis?->title);
    $vYear = old('year', $thesis?->year);
    $vProgram = old('program', $thesis?->program);
    $vAbstract = old('abstract', $thesis?->abstract);
    $vRecommendations = old('recommendations', $thesis?->recommendations);

    $vAuthors = old('authors', $thesis?->authors->pluck('name')->all() ?? []);
    $vAdvisers = old('advisers', $thesis?->advisers->pluck('name')->all() ?? []);
    $vPanelists = old('panelists', $thesis?->panelists->pluck('name')->all() ?? []);
    $vKeywords = old('keywords', $thesis?->keywords->pluck('name')->all() ?? []);

    $inputClass = 'w-full rounded-md border-0 bg-input text-sm text-text placeholder:text-text/40 focus:ring-2 focus:ring-cyan';
    $labelClass = 'block text-sm font-semibold text-text mb-1';
    $maxYear = (int) date('Y');
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <x-card>
        <div class="space-y-6">
            {{-- Title --}}
            <div>
                <label for="title" class="{{ $labelClass }}">Title <span class="text-danger">*</span></label>
                <textarea id="title" name="title" rows="2" placeholder="Full thesis title"
                          class="{{ $inputClass }}">{{ $vTitle }}</textarea>
                @error('title')<p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>@enderror
            </div>

            {{-- Year + Program --}}
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="year" class="{{ $labelClass }}">Year <span class="text-danger">*</span></label>
                    <input type="number" id="year" name="year" value="{{ $vYear }}" min="1900" max="{{ $maxYear }}" step="1"
                           inputmode="numeric" placeholder="e.g. 2025" class="{{ $inputClass }}">
                    @error('year')
                        <p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>
                    @else
                        <p class="mt-1 text-xs text-text/50">Between 1900 and {{ $maxYear }}.</p>
                    @enderror
                </div>
                <div>
                    <label for="program" class="{{ $labelClass }}">Program <span class="text-danger">*</span></label>
                    <input type="text" id="program" name="program" value="{{ $vProgram }}"
                           placeholder="e.g. BS Computer Science" class="{{ $inputClass }}">
                    @error('program')<p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Authors --}}
            <x-repeatable-list name="authors" label="Authors" hint="Listed in order of authorship."
                               placeholder="Author full name" :values="$vAuthors" />

            {{-- Abstract (with OCR placeholder) --}}
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <label for="abstract" class="{{ $labelClass }} mb-0">Abstract <span class="text-danger">*</span></label>
                    <x-scan-button />
                </div>
                <textarea id="abstract" name="abstract" rows="5" placeholder="Summarize the study's aims, method, and key findings…"
                          class="{{ $inputClass }}">{{ $vAbstract }}</textarea>
                @error('abstract')<p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>@enderror
            </div>

            {{-- Recommendations (with OCR placeholder) --}}
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <label for="recommendations" class="{{ $labelClass }} mb-0">Recommendations</label>
                    <x-scan-button />
                </div>
                <textarea id="recommendations" name="recommendations" rows="4" placeholder="Recommendations arising from the study…"
                          class="{{ $inputClass }}">{{ $vRecommendations }}</textarea>
                @error('recommendations')<p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>@enderror
            </div>

            <hr class="border-text/10">

            {{-- Adviser / Panelists / Keywords --}}
            <x-repeatable-list name="advisers" label="Adviser" placeholder="Adviser full name" :values="$vAdvisers" />
            <x-repeatable-list name="panelists" label="Panelists" placeholder="Panelist full name" :values="$vPanelists" />
            <x-repeatable-list name="keywords" label="Keywords" hint="Used for search and filtering."
                               placeholder="Keyword or topic" :values="$vKeywords" :numbered="false" />
        </div>
    </x-card>

    <div class="mt-5 flex justify-end gap-3">
        <x-btn href="{{ route('department.theses.index') }}" variant="ghost">Cancel</x-btn>
        <x-btn type="submit" variant="accent">{{ $thesis ? 'Save changes' : 'Save thesis' }}</x-btn>
    </div>
</form>
