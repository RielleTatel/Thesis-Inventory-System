@php
    // Pre-fill from old() input (after a validation error) or the existing record.
    $vTitle = old('title', $thesis?->title);
    $vYear = old('year', $thesis?->year);
    $vProgram = old('program', $thesis?->program);
    $vAbstract = old('abstract', $thesis?->abstract);
    $vRecommendations = old('recommendations', $thesis?->recommendations);
    $vStatus = old('status', $thesis?->status ?? 'draft');

    $vAuthors = old('authors', $thesis?->authors->pluck('name')->all() ?? []);
    $vAdvisers = old('advisers', $thesis?->advisers->pluck('name')->all() ?? []);
    $vPanelists = old('panelists', $thesis?->panelists->pluck('name')->all() ?? []);
    $vKeywords = old('keywords', $thesis?->keywords->pluck('name')->all() ?? []);

    $inputClass = 'w-full rounded-md border-0 bg-input text-sm text-text placeholder:text-text/40 focus:ring-2 focus:ring-cyan';
    $labelClass = 'block text-sm font-semibold text-text mb-1';
    $maxYear = (int) date('Y');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <x-card>
        <div class="space-y-6">
            {{-- Approval-page OCR: read one signature page and auto-fill several
                 fields at once. Parsed values are populated below for review. --}}
            <div class="flex flex-col gap-3 rounded-md border border-cyan/30 bg-cyan/5 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-text">Have the approval / signature page?</p>
                    <p class="text-xs text-text/60">
                        Scan it to auto-fill Title, Authors, Program, Adviser and Panelists — then review before saving.
                    </p>
                </div>
                <x-ocr-scanner mode="approval" field="approval page" />
            </div>

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
                    <x-ocr-scanner target="abstract" field="Abstract" />
                </div>
                <textarea id="abstract" name="abstract" rows="5" placeholder="Summarize the study's aims, method, and key findings…"
                          class="{{ $inputClass }}">{{ $vAbstract }}</textarea>
                @error('abstract')<p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>@enderror
            </div>

            {{-- Recommendations (with OCR placeholder) --}}
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <label for="recommendations" class="{{ $labelClass }} mb-0">Recommendations</label>
                    <x-ocr-scanner target="recommendations" field="Recommendations" />
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

            <hr class="border-text/10">

            {{-- Approval / signature page image — the one allowed attachment
                 (FR-4.4 exception). New upload replaces the current one; the
                 remove toggle deletes it on save. --}}
            <div x-data="{ remove: false }">
                <label for="approval_page" class="{{ $labelClass }}">Approval page image</label>

                @if ($thesis?->hasApprovalPage())
                    <div x-show="!remove" class="mb-2 flex items-center gap-3 rounded-md bg-input px-3 py-2">
                        <a href="{{ $thesis->approvalPageUrl() }}" target="_blank" rel="noopener"
                           class="text-sm font-semibold text-cyan hover:underline">View current approval page</a>
                        <button type="button" @click="remove = true"
                                class="ml-auto text-xs font-semibold text-danger hover:underline">Remove</button>
                    </div>
                    <div x-show="remove" x-cloak class="mb-2 flex items-center gap-3 rounded-md bg-danger/10 px-3 py-2">
                        <span class="text-xs font-semibold text-danger">Current approval page will be removed when you save.</span>
                        <button type="button" @click="remove = false"
                                class="ml-auto text-xs font-semibold text-text/70 hover:underline">Undo</button>
                    </div>
                    <input type="hidden" name="remove_approval_page" :value="remove ? 1 : 0">
                @endif

                <input type="file" id="approval_page" name="approval_page" accept="image/*"
                       class="block w-full text-sm text-text/70 file:mr-3 file:rounded-md file:border-0 file:bg-navy file:px-3 file:py-2 file:text-sm file:font-semibold file:text-surface hover:file:bg-navy/90">
                <p class="mt-1 text-xs text-text/50">JPG, PNG or WEBP, up to 5&nbsp;MB. A new image replaces the current one.</p>
                @error('approval_page')<p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>@enderror
            </div>
        </div>
    </x-card>

    <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-sm text-text/60">
            @if ($vStatus === 'published')
                <x-badge tone="green">Published</x-badge>
                <span>Visible to the public viewer.</span>
            @else
                <x-badge tone="gray">Draft</x-badge>
                <span>Not visible to the public yet.</span>
            @endif
        </div>
        <div class="flex gap-3">
            <x-btn href="{{ route('department.theses.index') }}" variant="ghost">Cancel</x-btn>
            {{-- Each button submits name="status" with its own value — browser sends the clicked one. --}}
            <x-btn type="submit" name="status" value="draft" variant="ghost">Save as Draft</x-btn>
            <x-btn type="submit" name="status" value="published" variant="accent">Publish</x-btn>
        </div>
    </div>
</form>
