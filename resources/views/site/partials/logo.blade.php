@php
    // "Strata Visual" → STRATA.VISUAL (titik beraksen). Nama satu kata tampil apa adanya.
    $parts = preg_split('/\s+/', trim(config('site.name')), 2);
@endphp
{{ mb_strtoupper($parts[0]) }}@if (isset($parts[1]))<span>.</span>{{ mb_strtoupper($parts[1]) }}@endif
