@props(['status'])

@php
    $colors = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'confirmed' => 'bg-green-100 text-green-800',
        'completed' => 'bg-blue-100 text-blue-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
    $colorClass = $colors[strtolower($status)] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="px-2 py-1 rounded-full text-xs font-semibold {{ $colorClass }}">
    {{ ucfirst($status) }}
</span>