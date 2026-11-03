@props(['title' => null, 'description' => null, 'image' => null, 'navigation' => null, 'width' => 'max-w-7xl'])
@php
    $user = auth()->user();
    // Role-aware primary navigation: [label, route name, active pattern]
    $nav = match (true) {
        ! $user => [],
        $user->isAdmin() => [['Admin', 'admin.dashboard', 'admin.*']],
        $user->isSeller() => [
            ['My listings', 'seller.dashboard', ['seller.dashboard', 'seller.tickets.edit']],
        ],
        default => [
            ['Browse', 'home', ['home', 'events.show', 'tickets.show']],
            ['My tickets', 'cart.index', ['cart.index', 'tickets.purchased.show', 'disputes.*']],
        ],
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <x-partials-head :title="$title" :description="$description" :image="$image" />
</head>
<body class="flex min-h-full flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:shadow-lift">Skip to content</a>

    <header x-data="{ menu: false }" class="sticky top-0 z-40 border-b border-gray-200/80 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/75">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-6 px-4 sm:px-6 lg:px-8">
            <a href="{{ $user && ! $user->isBuyer() ? route('dashboard') : route('home') }}" class="flex items-center gap-2 text-lg font-bold tracking-tight text-gray-900">
                <x-application-logo class="h-8 w-8 text-brand-600" />
                TixSwap
            </a>

            <nav class="hidden items-center gap-1 md:flex" aria-label="Main">
                @foreach ($nav as [$label, $route, $active])
                    <a href="{{ route($route) }}" @class([
                        'rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                        'bg-gray-100 text-gray-900' => request()->routeIs($active),
                        'text-gray-600 hover:bg-gray-50 hover:text-gray-900' => ! request()->routeIs($active),
                    ]) @if (request()->routeIs($active)) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-2">
                @guest
                    <x-ui.button variant="ghost" size="sm" :href="route('login')">Log in</x-ui.button>
                    <x-ui.button size="sm" :href="route('register')">Sign up</x-ui.button>
                @else
                    @if ($user->isSeller() && ! request()->routeIs('seller.tickets.create', 'seller.dashboard'))
                        <x-ui.button size="sm" icon="plus" :href="route('seller.tickets.create')" class="hidden sm:inline-flex">Sell a ticket</x-ui.button>
                    @endif

                    <x-notification-bell />

                    {{-- Account menu (desktop) --}}
                    <div x-data="{ open: false }" class="relative hidden md:block" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="true"
                                class="flex items-center gap-2 rounded-full p-1 pr-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-sm font-semibold text-brand-700">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                            <span class="max-w-[10rem] truncate">{{ $user->name }}</span>
                            <x-ui.icon name="chevron-down" class="h-4 w-4 text-gray-400" />
                        </button>
                        <div x-cloak x-show="open" @click.outside="open = false" x-transition.origin.top.right
                             class="absolute right-0 mt-2 w-60 overflow-hidden rounded-xl bg-white shadow-lift ring-1 ring-gray-200" role="menu">
                            <div class="border-b border-gray-100 px-4 py-3">
                                <p class="truncate text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                                <p class="truncate text-sm text-gray-500">{{ $user->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" role="menuitem"><x-ui.icon name="user" class="h-4 w-4 text-gray-400" /> Account settings</a>
                                <a href="{{ route('support') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" role="menuitem"><x-ui.icon name="chat" class="h-4 w-4 text-gray-400" /> Help &amp; support</a>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100 py-1">
                                @csrf
                                <button class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50" role="menuitem"><x-ui.icon name="logout" class="h-4 w-4 text-gray-400" /> Log out</button>
                            </form>
                        </div>
                    </div>

                    {{-- Mobile menu button --}}
                    <button type="button" @click="menu = !menu" :aria-expanded="menu" aria-controls="mobile-menu"
                            class="-mr-2 rounded-lg p-2 text-gray-600 hover:bg-gray-100 md:hidden">
                        <span class="sr-only">Open menu</span>
                        <x-ui.icon name="menu" class="h-6 w-6" x-show="!menu" />
                        <x-ui.icon name="x-mark" class="h-6 w-6" x-cloak x-show="menu" />
                    </button>
                @endguest
            </div>
        </div>

        @auth
            <div id="mobile-menu" x-cloak x-show="menu" x-transition.opacity class="border-t border-gray-200 bg-white md:hidden">
                <nav class="space-y-1 px-4 py-3" aria-label="Mobile">
                    @if ($user->isSeller())
                        <x-ui.button icon="plus" :href="route('seller.tickets.create')" class="mb-2 w-full">Sell a ticket</x-ui.button>
                    @endif
                    @foreach ($nav as [$label, $route, $active])
                        <a href="{{ route($route) }}" @class([
                            'block rounded-lg px-3 py-2.5 text-base font-medium',
                            'bg-gray-100 text-gray-900' => request()->routeIs($active),
                            'text-gray-700 hover:bg-gray-50' => ! request()->routeIs($active),
                        ])>{{ $label }}</a>
                    @endforeach
                </nav>
                <div class="border-t border-gray-100 px-4 py-3">
                    <div class="flex items-center gap-3 px-3 pb-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-50 font-semibold text-brand-700">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-gray-900">{{ $user->name }}</p>
                            <p class="truncate text-sm text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="block rounded-lg px-3 py-2.5 text-base text-gray-700 hover:bg-gray-50">Account settings</a>
                    <a href="{{ route('support') }}" class="block rounded-lg px-3 py-2.5 text-base text-gray-700 hover:bg-gray-50">Help &amp; support</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="block w-full rounded-lg px-3 py-2.5 text-left text-base text-gray-700 hover:bg-gray-50">Log out</button>
                    </form>
                </div>
            </div>
        @endauth
    </header>

    <main id="main" class="flex-1">
        <div class="mx-auto {{ $width }} px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
            @if (session('success'))
                <x-ui.alert tone="success" dismissible class="mb-6">{{ session('success') }}</x-ui.alert>
            @endif
            @if (session('error'))
                <x-ui.alert tone="error" dismissible class="mb-6">{{ session('error') }}</x-ui.alert>
            @endif
            {{ $slot }}
        </div>
    </main>

    <footer class="border-t border-gray-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-8 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
            <div>
                <div class="flex items-center gap-2 font-semibold text-gray-900">
                    <x-application-logo class="h-6 w-6 text-brand-600" /> TixSwap
                </div>
                <p class="mt-2 flex items-center gap-1.5 text-sm text-gray-500">
                    <x-ui.icon name="shield-check" class="h-4 w-4 text-mpesa-600" />
                    M-Pesa payments · money held in escrow until after the event
                </p>
            </div>
            <nav class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500" aria-label="Footer">
                <a href="{{ route('about') }}" class="hover:text-gray-900">About</a>
                <a href="{{ route('support') }}" class="hover:text-gray-900">Support</a>
                <a href="{{ route('terms') }}" class="hover:text-gray-900">Terms</a>
                <a href="{{ route('privacy') }}" class="hover:text-gray-900">Privacy</a>
                <span class="text-gray-400">© {{ date('Y') }} TixSwap</span>
            </nav>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
