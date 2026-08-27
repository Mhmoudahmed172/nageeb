@props(['name'])

<svg {{ $attributes->class('size-5 shrink-0') }} viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
    @switch($name)
        @case('home') <path d="m3 11 9-8 9 8v9a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1v-9Z" stroke-width="1.7"/> @break
        @case('book') <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5v14Z" stroke-width="1.7"/> @break
        @case('layers') <path d="m12 3 9 5-9 5-9-5 9-5Z M3 12l9 5 9-5M3 16l9 5 9-5" stroke-width="1.7"/> @break
        @case('quiz') <circle cx="12" cy="12" r="9" stroke-width="1.7"/><path d="M9.8 9a2.3 2.3 0 1 1 3.1 2.15c-.9.35-.9 1.1-.9 1.85M12 17h.01" stroke-width="1.7"/> @break
        @case('assignment') <path d="M9 5h6M9 3h6v4H9V3Z M7 5H5v16h14V5h-2M8 12h8M8 16h6" stroke-width="1.7"/> @break
        @case('live') <rect x="3" y="5" width="18" height="14" rx="2" stroke-width="1.7"/><path d="m10 9 5 3-5 3V9Z" stroke-width="1.7"/> @break
        @case('video') <rect x="2" y="6" width="13" height="12" rx="2" stroke-width="1.7"/><path d="m15 11 6-3.5v9L15 13v-2Z" stroke-width="1.7"/> @break
        @case('text') <path d="M5 5h14M5 10h14M5 15h9M5 20h6" stroke-width="1.7"/> @break
        @case('file') <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z M14 3v5h5" stroke-width="1.7"/> @break
        @case('audio') <path d="M11 5 6 9H3v6h3l5 4V5Z M16 9a4 4 0 0 1 0 6M19 6a8 8 0 0 1 0 12" stroke-width="1.7"/> @break
        @case('link') <path d="M10 13a4 4 0 0 0 5.66 0l3-3A4 4 0 0 0 13 4.34l-1.5 1.5M14 11a4 4 0 0 0-5.66 0l-3 3A4 4 0 0 0 11 19.66l1.5-1.5" stroke-width="1.7"/> @break
        @case('users') <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 21v-2a4 4 0 0 0-3-3.87" stroke-width="1.7"/> @break
        @case('message') <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z" stroke-width="1.7"/> @break
        @case('subscription') <rect x="3" y="5" width="18" height="14" rx="2" stroke-width="1.7"/><path d="M3 10h18M7 15h3" stroke-width="1.7"/> @break
        @case('orders') <path d="M6 3h12v18H6zM9 8h6M9 12h6M9 16h4" stroke-width="1.7"/> @break
        @case('revenue') <path d="M12 2v20M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-width="1.7"/> @break
        @case('analytics') <path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke-width="1.7"/> @break
        @case('settings') <circle cx="12" cy="12" r="3" stroke-width="1.7"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.55V21h-4v-.08A1.7 1.7 0 0 0 8.94 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.55-1.03H3v-4h.08A1.7 1.7 0 0 0 4.6 8.94a1.7 1.7 0 0 0-.34-1.88L4.2 7l2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-1.55V3h4v.08A1.7 1.7 0 0 0 15.06 4.6a1.7 1.7 0 0 0 1.88-.34L17 4.2 19.83 7l-.06.06a1.7 1.7 0 0 0-.34 1.88A1.7 1.7 0 0 0 21 10h.08v4H21a1.7 1.7 0 0 0-1.6 1Z" stroke-width="1.3"/> @break
        @default <circle cx="12" cy="12" r="8" stroke-width="1.7"/>
    @endswitch
</svg>
