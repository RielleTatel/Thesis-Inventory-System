{{--
    Renders a long free-text field (abstract, recommendations) while preserving
    the paragraph/line breaks stored in the value. `whitespace-pre-line` keeps
    newlines and blank lines visible, where HTML would otherwise collapse them
    into a single paragraph. Tokens only — no hardcoded hex (coding standard #8).

    Usage: <x-long-text :text="$thesis->abstract" placeholder="No abstract provided." />
--}}
@props([
    'text' => null,
    'placeholder' => '',
])

@php
    $value = trim((string) $text);
@endphp

<p {{ $attributes->merge(['class' => 'whitespace-pre-line text-base leading-relaxed text-text text-pretty']) }}>{{ $value !== '' ? $value : $placeholder }}</p>
