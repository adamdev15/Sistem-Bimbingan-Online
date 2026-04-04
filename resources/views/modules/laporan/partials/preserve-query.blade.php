@php
    $except = $except ?? [];
@endphp
@foreach (request()->query() as $key => $val)
    @if (is_array($val))
        @continue
    @endif
    @if (! in_array($key, $except, true))
        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
    @endif
@endforeach
