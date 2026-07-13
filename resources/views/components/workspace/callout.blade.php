@props(['tone' => 'info', 'title'])
<section {{ $attributes->class(['callout','callout--'.$tone]) }} role="{{ $tone === 'danger' ? 'alert' : 'note' }}"><h2>{{ $title }}</h2>{{ $slot }}</section>
