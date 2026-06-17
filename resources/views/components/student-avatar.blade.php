@props(['student', 'size' => 32])

@php
    $size = (int) $size;
    // Shared circular frame so every branch has identical outer dimensions —
    // rows and headers don't shift when a picture is added or removed.
    $frame = 'width:' . $size . 'px;height:' . $size . 'px;border-radius:9999px;flex-shrink:0;';
    $center = 'display:inline-flex;align-items:center;justify-content:center;background:rgba(47,85,151,0.1);';

    $icons = config('profile_icons');
    $emoji = $student->profile_icon ? ($icons[$student->profile_icon] ?? null) : null;
@endphp

@if(!empty($student->profile_picture))
    <img src="{{ $student->profile_picture }}" alt="{{ $student->name }}"
         style="{{ $frame }}object-fit:cover;">
@elseif($emoji)
    <div style="{{ $frame }}{{ $center }}font-size:{{ round($size * 0.5625, 2) }}px;line-height:1;">
        {{ $emoji }}
    </div>
@else
    <div style="{{ $frame }}{{ $center }}">
        <span style="color:#2f5597;font-weight:700;font-size:{{ round($size * 0.375, 2) }}px;">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
    </div>
@endif
