@props([
    'name',
    'tone' => 'blue',
    'size' => 20,
    'variant' => 'soft',
    'label' => null,
])

<span
    {{ $attributes->class([
        'sv-feature-icon',
        'sv-feature-icon--' . $tone,
        'sv-feature-icon--' . $variant,
    ]) }}
    @if($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif
>
    <x-icon :name="$name" :size="$size" />
</span>
