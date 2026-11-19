{{-- Interlocking serif "S" + "Y" monogram (same drawing as public/img/slayna-logo.svg).
     Inline so the "S" takes the logo's text colour (currentColor): black on light, white on dark.
     Pass a unique $id when it appears more than once on a page. --}}
@php($id = $id ?? 'sl-mark')
<svg class="sl-logo-mark" viewBox="92 68 248 284" aria-hidden="true" focusable="false">
    <defs>
        <text id="{{ $id }}-s" x="196" y="352" text-anchor="middle" font-family="'Times New Roman', Georgia, serif" font-size="330">S</text>
        <text id="{{ $id }}-y" x="216" y="300" text-anchor="middle" font-family="'Times New Roman', Georgia, serif" font-size="330">Y</text>
        <clipPath id="{{ $id }}-over"><rect x="120" y="100" width="100" height="90"/></clipPath>
        <mask id="{{ $id }}-cut-y" maskUnits="userSpaceOnUse" x="0" y="0" width="400" height="400">
            <rect width="400" height="400" fill="#fff"/>
            <use href="#{{ $id }}-s" fill="#000" stroke="#000" stroke-width="16" stroke-linejoin="round"/>
        </mask>
        <mask id="{{ $id }}-cut-s" maskUnits="userSpaceOnUse" x="0" y="0" width="400" height="400">
            <rect width="400" height="400" fill="#fff"/>
            <use href="#{{ $id }}-y" fill="#000" stroke="#000" stroke-width="16" stroke-linejoin="round" clip-path="url(#{{ $id }}-over)"/>
        </mask>
    </defs>
    <use href="#{{ $id }}-y" fill="#D7141A" mask="url(#{{ $id }}-cut-y)"/>
    <use href="#{{ $id }}-s" fill="currentColor" mask="url(#{{ $id }}-cut-s)"/>
    <use href="#{{ $id }}-y" fill="#D7141A" clip-path="url(#{{ $id }}-over)"/>
</svg>
