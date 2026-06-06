{{-- Renders the "affected record" label for an activity log entry. Works even
     after the subject row is deleted by reading from the stored properties. --}}
@props(['activity'])

@php
    $props = $activity->properties;
    $label = match ($activity->log_name) {
        'thesis' => 'Thesis: '.($activity->subject?->title ?? data_get($props, 'attributes.title') ?? '—'),
        'account' => 'Account: '.($activity->subject?->name ?? data_get($props, 'department') ?? '—'),
        default => $activity->description,
    };
@endphp

<span {{ $attributes }}>{{ $label }}</span>
