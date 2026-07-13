@props(['status'])
@php($label = str($status ?: 'unknown')->replace('_', ' ')->title())
<span {{ $attributes->class(['status-badge', 'status-badge--'.str($status)->replace('_','-')]) }}><span class="status-badge__marker" aria-hidden="true"></span>{{ $label }}</span>
