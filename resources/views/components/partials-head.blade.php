@props(['title' => null, 'description' => null, 'image' => null])
@php
    $fullTitle = $title ? $title.' · TixSwap' : 'TixSwap: buy and sell event tickets safely';
    $description = $description ?: 'Buy and sell verified event tickets in Kenya. Pay with M-Pesa; your money is held safely until after the event.';
    $image = $image ?: asset('favicon.svg');
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#dc2626">
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ url()->current() }}">
{{-- Link previews (WhatsApp, X, Facebook) --}}
<meta property="og:site_name" content="TixSwap">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ $image }}">
<meta name="twitter:card" content="summary_large_image">
<title>{{ $fullTitle }}</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('head')
