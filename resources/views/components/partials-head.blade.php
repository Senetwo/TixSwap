@props(['title' => null])
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#dc2626">
<meta name="description" content="Buy and sell verified event tickets in Kenya. Pay with M-Pesa; your money is held safely until after the event.">
<title>{{ $title ? $title.' · TixSwap' : 'TixSwap: buy and sell event tickets safely' }}</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
