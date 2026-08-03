@props([
    'name',
    'size' => 20,
])

<svg
    {{ $attributes->merge(['class' => 'sv-icon']) }}
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true"
    focusable="false"
>
    @switch($name)
        @case('bolt')
            <path d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z" fill="currentColor"/>
            @break
        @case('home')
            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z"/>
            @break
        @case('chart')
            <path d="M4 20V10m5 10V4m5 16v-7m5 7V7"/>
            @break
        @case('rooms')
            <rect x="3" y="3" width="8" height="8" rx="2"/>
            <rect x="13" y="3" width="8" height="8" rx="2"/>
            <rect x="3" y="13" width="8" height="8" rx="2"/>
            <rect x="13" y="13" width="8" height="8" rx="2"/>
            @break
        @case('settings')
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1A1.7 1.7 0 0 0 4.6 15 1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1A1.7 1.7 0 0 0 9 4.6 1.7 1.7 0 0 0 10 3V2.8h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1Z"/>
            @break
        @case('tools')
            <path d="M14.7 6.3a4 4 0 0 0-5-5l2.2 2.2-2.4 2.4-2.2-2.2a4 4 0 0 0 5 5L20 16.4a2.5 2.5 0 1 1-3.6 3.6l-7.7-7.7"/>
            <path d="m5 14-3 3 5 5 3-3"/>
            @break
        @case('bell')
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
            <path d="M10 21h4"/>
            @break
        @case('user')
            <circle cx="12" cy="8" r="4"/>
            <path d="M4 21a8 8 0 0 1 16 0"/>
            @break
        @case('logout')
            <path d="M10 17l5-5-5-5"/>
            <path d="M15 12H3"/>
            <path d="M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/>
            @break
        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16"/>
            @break
        @case('close')
            <path d="m6 6 12 12M18 6 6 18"/>
            @break
        @case('wifi')
            <path d="M5 12.5a10 10 0 0 1 14 0"/>
            <path d="M8.5 16a5 5 0 0 1 7 0"/>
            <circle cx="12" cy="20" r="1"/>
            @break
        @case('wifi-off')
            <path d="m3 3 18 18"/>
            <path d="M8.5 16a5 5 0 0 1 4.5-1.4M5 12.5a10 10 0 0 1 4.4-2.4M14.8 10.4A10 10 0 0 1 19 12.5"/>
            @break
        @case('sensor')
            <rect x="4" y="4" width="16" height="16" rx="4"/>
            <path d="M8 8h8v8H8zM12 1v3M12 20v3M1 12h3M20 12h3"/>
            @break
        @case('mqtt')
            <path d="M4 19a15 15 0 0 1 15-15"/>
            <path d="M4 13a9 9 0 0 1 9-9"/>
            <path d="M4 7a3 3 0 0 1 3-3"/>
            <circle cx="5" cy="19" r="1.5" fill="currentColor" stroke="none"/>
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 7v5l3 2"/>
            @break
        @case('plug')
            <path d="M8 2v6M16 2v6M6 8h12v2a6 6 0 0 1-12 0V8ZM12 16v6"/>
            @break
        @case('energy')
            <path d="M3 12h4l2-6 4 12 2-6h6"/>
            @break
        @case('money')
            <rect x="3" y="5" width="18" height="14" rx="3"/>
            <path d="M7 9h.01M17 15h.01M12 9v6M10 11h3a1 1 0 0 1 0 2h-3"/>
            @break
        @case('devices')
            <rect x="3" y="4" width="8" height="16" rx="2"/>
            <rect x="13" y="4" width="8" height="16" rx="2"/>
            <path d="M7 8h.01M17 8h.01M7 16h.01M17 16h.01"/>
            @break
        @case('chevron-down')
            <path d="m6 9 6 6 6-6"/>
            @break
        @case('chevron-right')
            <path d="m9 6 6 6-6 6"/>
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14"/>
            @break
        @case('edit')
            <path d="M12 20h9"/>
            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z"/>
            @break
        @case('trash')
            <path d="M4 7h16M9 7V4h6v3M6 7l1 14h10l1-14M10 11v6M14 11v6"/>
            @break
        @case('check')
            <path d="m5 12 4 4L19 6"/>
            @break
        @case('warning')
            <path d="M12 3 2.5 20h19L12 3Z"/>
            <path d="M12 9v5M12 17h.01"/>
            @break
        @case('info')
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 11v5M12 8h.01"/>
            @break
        @case('eye')
            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
            <circle cx="12" cy="12" r="2.5"/>
            @break
        @case('eye-off')
            <path d="m4 4 16 16"/>
            <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
            @break
        @case('lock')
            <rect x="5" y="10" width="14" height="10" rx="3"/>
            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
            @break
        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="3"/>
            <path d="m5 8 7 5 7-5"/>
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="3"/>
            <path d="M8 3v4M16 3v4M3 10h18"/>
            @break
        @case('filter')
            <path d="M4 5h16M7 12h10M10 19h4"/>
            @break
        @case('download')
            <path d="M12 3v12m0 0 5-5m-5 5-5-5"/>
            <path d="M5 21h14"/>
            @break
        @case('search')
            <circle cx="11" cy="11" r="7"/>
            <path d="m20 20-4-4"/>
            @break
        @case('shield')
            <path d="M12 3 5 6v5c0 4.6 2.7 8 7 10 4.3-2 7-5.4 7-10V6l-7-3Z"/>
            <path d="m9 12 2 2 4-5"/>
            @break
        @case('database')
            <ellipse cx="12" cy="5" rx="8" ry="3"/>
            <path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>
            @break
        @case('server')
            <rect x="3" y="4" width="18" height="6" rx="2"/>
            <rect x="3" y="14" width="18" height="6" rx="2"/>
            <path d="M7 7h.01M7 17h.01M11 7h6M11 17h6"/>
            @break
        @case('power')
            <path d="M12 2v10"/>
            <path d="M6.3 5.7a8 8 0 1 0 11.4 0"/>
            @break
        @case('lightbulb')
            <path d="M9 18h6M10 22h4"/>
            <path d="M8.5 15.5A7 7 0 1 1 15.5 15.5c-.9.7-1.5 1.5-1.5 2.5h-4c0-1-.6-1.8-1.5-2.5Z"/>
            @break
        @case('fan')
            <circle cx="12" cy="12" r="2"/>
            <path d="M12 10c-1-6 3-8 5-5 2 3-1 6-5 7M14 12c6-1 8 3 5 5-3 2-6-1-7-5M12 14c1 6-3 8-5 5-2-3 1-6 5-7M10 12c-6 1-8-3-5-5 3-2 6 1 7 5"/>
            @break
        @case('refresh')
            <path d="M20 6v5h-5M4 18v-5h5"/>
            <path d="M18.5 9A7 7 0 0 0 6 6.5L4 9M5.5 15A7 7 0 0 0 18 17.5l2-2.5"/>
            @break
        @case('document')
            <path d="M6 3h8l4 4v14H6V3Z"/>
            <path d="M14 3v5h5M9 13h6M9 17h6"/>
            @break
        @case('activity')
            <path d="M3 12h4l2-6 4 12 2-6h6"/>
            @break
        @default
            <circle cx="12" cy="12" r="9"/>
    @endswitch
</svg>
