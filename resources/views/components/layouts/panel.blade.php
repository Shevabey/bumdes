@props(['title' => 'Panel', 'header' => null])

@include('layouts.panel', [
    'title' => $title,
    'header' => $header,
    'slot' => $slot,
])
