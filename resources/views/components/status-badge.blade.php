@props(['status'])

@php
    $icons = [
        'pending'    => '⏳',
        'confirmed'  => '✔',
        'ongoing'    => '🔄',
        'completed'  => '✅',
        'cancelled'  => '✖',
        'no_show'    => '⚪', // or '🚫'
    ];
    $colors = [
        'pending'    => 'badge bg-warning text-dark',
        'confirmed'  => 'badge bg-success',
        'ongoing'    => 'badge bg-info text-white',
        'completed'  => 'badge bg-primary',
        'cancelled'  => 'badge bg-danger',
        'no_show'    => 'badge bg-secondary',
    ];
    $statusKey = strtolower($status);
    $icon = $icons[$statusKey] ?? '';
    $colorClass = $colors[$statusKey] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="px-3 py-2 rounded-full text-xs font-semibold {{ $colorClass }}">
    {{ $icon }} {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>