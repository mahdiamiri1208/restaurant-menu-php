@props([
    'size' => 24,
    'color' => 'currentColor',
    'strokeWidth' => 1.2,
])

<svg
    {{ $attributes->merge([
        'data-icon' => 'hookah',
        'width' => $size,
        'height' => $size,
        'viewBox' => '0 0 24 24',
        'fill' => 'none',
        'stroke' => $color,
        'stroke-width' => $strokeWidth,
        'stroke-linecap' => 'round',
        'stroke-linejoin' => 'round',
        'aria-hidden' => 'true',
    ]) }}
>
    {!! \App\Support\MenuCategoryIcon::paths('hookah') !!}
</svg>
