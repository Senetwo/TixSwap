@props(['title' => 'Admin'])
@php
    // Work waiting for an admin, shown as counts in the sidebar.
    $counts = [
        'review' => \App\Models\Ticket::where('status', 'pending')->count(),
        'disputes' => \App\Models\Dispute::where('status', 'open')->count(),
        'payouts' => \App\Models\Payout::where('status', 'pending')->count()
            + \App\Models\Order::where('status', 'needs_refund')->count(),
    ];
    $items = [
        ['Overview', 'admin.dashboard', 'admin.dashboard', 'chart', null],
        ['Review queue', 'admin.tickets.pending', 'admin.tickets.*', 'clipboard-check', $counts['review']],
        ['Listings', 'admin.listings.index', 'admin.listings.*', 'ticket', null],
        ['Disputes', 'admin.disputes.index', 'admin.disputes.*', 'scale', $counts['disputes']],
        ['Payouts & refunds', 'admin.payouts.index', 'admin.payouts.*', 'banknotes', $counts['payouts']],
        ['Users', 'admin.users.index', 'admin.users.*', 'users', null],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <x-partials-head :title="$title.' · Admin'" />
</head>
<body class="h-full" x-data="{ sidebar: false }" @keydown.escape.window="sidebar = false">
    {{-- Mobile drawer backdrop --}}
    <div x-cloak x-show="sidebar" x-transition.opacity class="fixed inset-0 z-40 bg-gray-950/50 lg:hidden" @click="sidebar = false" aria-hidden="true"></div>

    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-gray-950 text-gray-300 transition-transform duration-200 lg:translate-x-0">
        <div class="flex h-16 items-center justify-between px-5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-bold text-white">
                <x-application-logo class="h-8 w-8 text-brand-500" />
                TixSwap <span class="rounded-md bg-white/10 px-1.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-gray-300">Admin</span>
            </a>
            <button type="button" class="rounded-lg p-1.5 text-gray-400 hover:bg-white/10 lg:hidden" @click="sidebar = false" aria-label="Close menu">
                <x-ui.icon name="x-mark" />
            </button>
        </div>
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Admin">
            @foreach ($items as [$label, $route, $active, $icon, $count])
                @php($isActive = request()->routeIs($active))
                <a href="{{ route($route) }}" @class([
                    'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                    'bg-white/10 text-white' => $isActive,
                    'hover:bg-white/5 hover:text-white' => ! $isActive,
                ]) @if ($isActive) aria-current="page" @endif>
                    <x-ui.icon :name="$icon" @class(['h-5 w-5', 'text-white' => $isActive, 'text-gray-500 group-hover:text-gray-300' => ! $isActive]) />
                    {{ $label }}
                    @if ($count)
                        <span class="ml-auto rounded-full bg-brand-600 px-2 py-0.5 text-xs font-semibold text-white">{{ $count }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
        <div class="border-t border-white/10 p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 font-semibold text-white">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg p-2 text-gray-400 hover:bg-white/10 hover:text-white" title="Log out" aria-label="Log out"><x-ui.icon name="logout" /></button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex min-h-full flex-col lg:pl-64">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-gray-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
            <button type="button" class="-ml-2 rounded-lg p-2 text-gray-600 hover:bg-gray-100 lg:hidden" @click="sidebar = true" aria-label="Open menu">
                <x-ui.icon name="menu" class="h-6 w-6" />
            </button>
            <h1 class="text-base font-semibold text-gray-900">{{ $title }}</h1>
            <a href="{{ route('profile.edit') }}" class="ml-auto text-sm font-medium text-gray-500 hover:text-gray-900">Account</a>
        </header>

        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-6xl">
                @if (session('success'))
                    <x-ui.alert tone="success" dismissible class="mb-6">{{ session('success') }}</x-ui.alert>
                @endif
                @if (session('error'))
                    <x-ui.alert tone="error" dismissible class="mb-6">{{ session('error') }}</x-ui.alert>
                @endif
                {{ $slot }}
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
