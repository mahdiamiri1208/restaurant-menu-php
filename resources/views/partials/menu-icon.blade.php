@php($attributes = 'width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"')
<svg {!! $attributes !!}>
    @switch($name)
        @case('search')
            <circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 4.5 4.5"></path>
            @break
        @case('close')
            <path d="m18 6-12 12M6 6l12 12"></path>
            @break
        @case('flame')
            <path d="M12 22c4.2 0 7-2.9 7-6.7 0-3.1-1.6-5.2-4.1-7.8-.1 2.4-1.2 3.7-2.1 4.1C12.8 7.4 10.1 4.6 7 2c.4 4.7-2 7.3-2 12.2C5 18.4 7.9 22 12 22Z"></path>
            @break
        @case('leaf')
            <path d="M20.5 3.5C12 3.5 5 5.2 5 12.1c0 3 2 5.2 4.8 5.2 6.8 0 9.5-6.4 10.7-13.8Z"></path><path d="M3.5 21c3-5.3 6.6-8.5 11.6-11.4"></path>
            @break
        @case('bell')
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4"></path>
            @break
        @default
            <path d="M4 8.5 12 4l8 4.5v7L12 20l-8-4.5v-7Z"></path><path d="m4.5 8.8 7.5 4.3 7.5-4.3M12 13v7"></path>
    @endswitch
</svg>
