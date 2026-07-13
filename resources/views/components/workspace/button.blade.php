@props(['tone' => 'primary', 'href' => null, 'type' => 'button'])

@if($href)
    <a href="{{ $href }}" {{ $attributes->class(['button', 'button--'.$tone]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class(['button', 'button--'.$tone]) }}>{{ $slot }}</button>
@endif
