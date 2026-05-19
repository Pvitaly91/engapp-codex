@props(['name'])

{{--
    Background icon library for the public fixed background.
    All icons share viewBox 0 0 24 24 and use currentColor so the
    parent .app-bg-icon--* tone wrapper can tint them. Icons that
    embed literal letters (Aa / ABC / Hi / EN / ?) read clearly even
    at thumbnail sizes, which is what makes the pattern feel like an
    English-learning hub instead of a generic shape backdrop.
--}}
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
    @switch($name)
        {{-- ===== TEXTUAL ICONS — instantly readable ===== --}}

        @case('letter-aa')
            {{-- Big A + small a in a rounded square — alphabet learning. --}}
            <rect x="3" y="3" width="18" height="18" rx="4" />
            <path d="M7 17 L9 9 L11 17 M7.6 14.2 H10.4" stroke-width="1.8" />
            <path d="M17.2 17 V12.6 A2.2 2.2 0 0 0 15 10.4 A2.2 2.2 0 0 0 12.8 12.6 A2.2 2.2 0 0 0 15 14.8 A2.2 2.2 0 0 0 17.2 13" stroke-width="1.8" />
            @break

        @case('letter-abc')
            {{-- "ABC" text inside a card. --}}
            <rect x="3" y="6" width="18" height="12" rx="2.5" />
            <text x="12" y="15.6" text-anchor="middle" font-family="ui-sans-serif, system-ui, sans-serif" font-weight="800" font-size="7.2" fill="currentColor" stroke="none">ABC</text>
            @break

        @case('question-bubble')
            {{-- Speech bubble with a question mark. --}}
            <path d="M5 6.5C5 5.12 6.12 4 7.5 4H16.5C17.88 4 19 5.12 19 6.5V13C19 14.38 17.88 15.5 16.5 15.5H11.5L8 19V15.5H7.5C6.12 15.5 5 14.38 5 13Z" />
            <path d="M10.4 8.4 A1.6 1.6 0 0 1 12 7 A1.7 1.7 0 0 1 13.6 8.8 C13.6 10 12 10.4 12 11.6" stroke-width="1.7" />
            <circle cx="12" cy="13" r="0.65" fill="currentColor" stroke="none" />
            @break

        @case('hi-bubble')
            {{-- Speech bubble with literal "Hi". --}}
            <path d="M4.5 6.5C4.5 5.12 5.62 4 7 4H17C18.38 4 19.5 5.12 19.5 6.5V13.2C19.5 14.58 18.38 15.7 17 15.7H10.5L7 19V15.7H7C5.62 15.7 4.5 14.58 4.5 13.2Z" />
            <text x="12" y="13" text-anchor="middle" font-family="ui-sans-serif, system-ui, sans-serif" font-weight="800" font-size="6.6" fill="currentColor" stroke="none">Hi</text>
            @break

        @case('en-globe')
            {{-- Globe + "EN" label. --}}
            <circle cx="12" cy="12" r="8" />
            <path d="M4 12h16" />
            <path d="M12 4c2.5 2.4 3.6 5 3.6 8s-1.1 5.6-3.6 8" />
            <path d="M12 4c-2.5 2.4-3.6 5-3.6 8s1.1 5.6 3.6 8" />
            <text x="12" y="14.6" text-anchor="middle" font-family="ui-sans-serif, system-ui, sans-serif" font-weight="800" font-size="5.4" fill="currentColor" stroke="none">EN</text>
            @break

        @case('translate-arrows')
            {{-- EN ⇄ UA translation. --}}
            <rect x="3" y="4.5" width="8.5" height="6.5" rx="1.6" />
            <text x="7.25" y="9.4" text-anchor="middle" font-family="ui-sans-serif, system-ui, sans-serif" font-weight="800" font-size="4.4" fill="currentColor" stroke="none">EN</text>
            <rect x="12.5" y="13" width="8.5" height="6.5" rx="1.6" />
            <text x="16.75" y="17.9" text-anchor="middle" font-family="ui-sans-serif, system-ui, sans-serif" font-weight="800" font-size="4.4" fill="currentColor" stroke="none">UA</text>
            <path d="M11.5 11 L13 12.5 L14.5 11" />
            <path d="M12.5 14.5 L11 13 L9.5 14.5" />
            @break

        @case('plus-letter')
            {{-- "A" letter in a small chip — used as filler / accent. --}}
            <rect x="6" y="6" width="12" height="12" rx="2" />
            <text x="12" y="15.2" text-anchor="middle" font-family="ui-sans-serif, system-ui, sans-serif" font-weight="800" font-size="8.4" fill="currentColor" stroke="none">A</text>
            @break

        {{-- ===== OBJECT ICONS — clearly recognisable line art ===== --}}

        @case('book-open')
            <path d="M3 5.5C3 5.22 3.22 5 3.5 5H10.5C11.33 5 12 5.67 12 6.5V19.5C12 19.78 11.78 20 11.5 20H4.5C3.67 20 3 19.33 3 18.5Z" />
            <path d="M21 5.5C21 5.22 20.78 5 20.5 5H13.5C12.67 5 12 5.67 12 6.5V19.5C12 19.78 12.22 20 12.5 20H19.5C20.33 20 21 19.33 21 18.5Z" />
            <path d="M5.5 8.5H9.5" />
            <path d="M5.5 11H9.5" />
            <path d="M14.5 8.5H18.5" />
            <path d="M14.5 11H18.5" />
            @break

        @case('pencil')
            <path d="M16.5 4.5L19.5 7.5L8 19H5V16Z" />
            <path d="M14.5 6.5L17.5 9.5" />
            <path d="M6.5 17.5L8 19" />
            @break

        @case('notebook')
            <rect x="5" y="3" width="14" height="18" rx="2" />
            <path d="M5 7H19" />
            <path d="M5 11H19" />
            <path d="M5 15H19" />
            <path d="M5 19H19" />
            <path d="M8 3V21" />
            @break

        @case('headphones')
            <path d="M4 13V12C4 7.58 7.58 4 12 4C16.42 4 20 7.58 20 12V13" />
            <path d="M4 13H7V18C7 19.1 6.1 20 5 20C4.45 20 4 19.55 4 19Z" />
            <path d="M20 13H17V18C17 19.1 17.9 20 19 20C19.55 20 20 19.55 20 19Z" />
            <path d="M3 14.5L2 15" />
            <path d="M21 14.5L22 15" />
            @break

        @case('microphone')
            <rect x="9" y="3" width="6" height="11" rx="3" />
            <path d="M6 11V12C6 15.31 8.69 18 12 18C15.31 18 18 15.31 18 12V11" />
            <path d="M12 18V21" />
            <path d="M9 21H15" />
            @break

        @case('lightbulb')
            <path d="M9 17H15" />
            <path d="M10 20H14" />
            <path d="M7.5 11A4.5 4.5 0 0 1 12 6.5A4.5 4.5 0 0 1 16.5 11C16.5 13 15 14 14.5 16H9.5C9 14 7.5 13 7.5 11Z" />
            <path d="M12 3V4" />
            <path d="M3.5 11H4.5" />
            <path d="M19.5 11H20.5" />
            <path d="M5.5 5.5L6.2 6.2" />
            <path d="M17.8 6.2L18.5 5.5" />
            @break

        @case('flashcards')
            <rect x="4" y="5" width="10.5" height="7.5" rx="2" transform="rotate(-6 9.25 8.75)" />
            <rect x="9" y="11" width="11" height="8" rx="2" transform="rotate(4 14.5 15)" />
            <path d="M7 8.5H11" />
            <path d="M13 16H17.5" />
            <path d="M13 17.8H17" />
            @break

        @case('star')
            <path d="M12 4L14.2 9L19.5 9.7L15.6 13.5L16.6 18.7L12 16L7.4 18.7L8.4 13.5L4.5 9.7L9.8 9Z" />
            @break

        @case('checkmark')
            <circle cx="12" cy="12" r="8.5" />
            <path d="M8 12.4L11 15L16 9.5" stroke-width="1.8" />
            @break

        @case('quote-marks')
            <path d="M5 7C5 5.9 5.9 5 7 5H9.5V8.5C9.5 11.5 7.5 13 5 13.5V11C6.5 10.5 7.5 9.5 7.5 8.5H5Z" />
            <path d="M14.5 7C14.5 5.9 15.4 5 16.5 5H19V8.5C19 11.5 17 13 14.5 13.5V11C16 10.5 17 9.5 17 8.5H14.5Z" />
            @break

        @case('graduation-cap')
            <path d="M12 3L22 8L12 13L2 8Z" />
            <path d="M6 10V15C6 16 8.5 17.5 12 17.5C15.5 17.5 18 16 18 15V10" />
            <path d="M22 8V13" />
            @break

        @case('spiral')
            {{-- Tiny accent — coiled spiral. --}}
            <path d="M12 8.5A3.5 3.5 0 1 0 15.5 12A2.5 2.5 0 1 0 13 14.5A1.6 1.6 0 1 0 14.6 12.9" />
            @break

        @case('dot')
            <circle cx="12" cy="12" r="2.4" fill="currentColor" stroke="none" />
            @break

        @default
            <circle cx="12" cy="12" r="6" />
    @endswitch
</svg>
