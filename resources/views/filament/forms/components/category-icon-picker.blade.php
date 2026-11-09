@php
    $icons = \App\Support\MenuCategoryIcon::names();
    $labels = \App\Support\MenuCategoryIcon::labels();
    $statePath = $getStatePath();
    $selectedIcon = $getState();
@endphp

<div
    class="category-icon-picker"
    x-data="{
        selected: $wire.entangle('{{ $statePath }}').live,
    }"
>
    <div class="category-icon-grid" role="radiogroup" aria-label="آیکون دسته‌بندی">
        @foreach ($icons as $icon)
            <button
                type="button"
                @class([
                    'category-icon-option',
                    'is-selected' => $selectedIcon === $icon,
                ])
                role="radio"
                aria-checked="{{ $selectedIcon === $icon ? 'true' : 'false' }}"
                :aria-checked="selected === '{{ $icon }}'"
                :class="{ 'is-selected': selected === '{{ $icon }}' }"
                @click="selected = '{{ $icon }}'"
            >
                <span class="category-icon-art">{{ \App\Support\MenuCategoryIcon::render($icon, 28) }}</span>
                <span class="category-icon-name">{{ $icon }}</span>
                <span class="category-icon-label">{{ $labels[$icon] }}</span>
            </button>
        @endforeach
    </div>
</div>

<style>
    .category-icon-picker {
        display: grid;
        gap: 1rem;
    }

    .category-icon-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(112px, 1fr));
        gap: .625rem;
    }

    .category-icon-option {
        position: relative;
        display: grid;
        min-height: 6rem;
        justify-items: center;
        align-content: center;
        gap: .375rem;
        border: 1px solid var(--fi-color-gray-300);
        border-radius: .625rem;
        background: var(--fi-color-white);
        padding: .625rem .375rem;
        color: var(--fi-color-gray-700);
        cursor: pointer;
        text-align: center;
        transition: border-color .15s, background-color .15s, color .15s, box-shadow .15s;
    }

    .dark .category-icon-option {
        border-color: var(--fi-color-gray-700);
        background: var(--fi-color-gray-950);
        color: var(--fi-color-gray-200);
    }

    .category-icon-option:hover {
        border-color: var(--fi-color-primary-400);
    }

    .category-icon-option.is-selected {
        border-color: var(--fi-color-primary-600);
        background: color-mix(in srgb, var(--fi-color-primary-500) 9%, var(--fi-color-white));
        box-shadow: 0 0 0 1px color-mix(in srgb, var(--fi-color-primary-500) 24%, transparent);
        color: var(--fi-color-primary-700);
    }

    .dark .category-icon-option.is-selected {
        border-color: var(--fi-color-primary-400);
        background: color-mix(in srgb, var(--fi-color-primary-500) 14%, var(--fi-color-gray-950));
        color: var(--fi-color-primary-300);
    }

    .category-icon-option.is-selected::after {
        position: absolute;
        inset-block-start: .55rem;
        inset-inline-end: .55rem;
        width: .35rem;
        height: .65rem;
        border-right: 2px solid currentColor;
        border-bottom: 2px solid currentColor;
        content: "";
        opacity: .9;
        transform: rotate(45deg);
    }

    .category-icon-option:focus-visible {
        outline: 2px solid var(--fi-color-primary-500);
        outline-offset: 2px;
    }

    .category-icon-art {
        display: grid;
        place-items: center;
        color: inherit;
    }

    .category-icon-option.is-selected .category-icon-art {
        color: var(--fi-color-primary-700);
    }

    .dark .category-icon-option.is-selected .category-icon-art {
        color: var(--fi-color-primary-300);
    }

    .category-icon-art svg {
        width: 1.75rem;
        height: 1.75rem;
    }

    .category-icon-name {
        direction: ltr;
        font-size: .75rem;
        font-weight: 600;
    }

    .category-icon-label {
        font-size: .7rem;
    }
</style>
