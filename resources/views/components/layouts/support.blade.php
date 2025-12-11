<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="lofi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/robsontenorio/mary@0.44.2/libs/currency/currency.js"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-white">
    @auth
        @if(auth()->user()->can('support.access'))
            {{-- MAIN --}}
            <x-main full-width>
                <x-slot:content>
                    {{-- Simple Topbar for Support Users --}}
                    <x-nav sticky class="rounded-md border shadow-sm mb-4">
                        <x-slot:brand>
                            <div>
                                <img src="/img/logo.jpg" class="lg:h-15 lg:w-24"/>
                            </div>
                        </x-slot:brand>
                        <x-slot:actions>
                            <x-dropdown>
                                <x-slot:trigger>
                                    <x-button class="btn-ghost btn-sm" icon="o-user-circle" label="{{ auth()->user()->name }}" />
                                </x-slot:trigger>
                                <x-menu-item 
                                    title="Sign Out" 
                                    icon="o-arrow-right-on-rectangle" 
                                    link="{{ route('logout') }}"
                                    class="text-red-500 hover:text-red-600" />
                            </x-dropdown>
                        </x-slot:actions>
                    </x-nav>
                    
                    {{ $slot }}
                </x-slot:content>
            </x-main>

            {{-- TOAST area --}}
            <x-toast />
        @else
            <div class="min-h-screen flex items-center justify-center bg-gray-50">
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Access Denied</h1>
                    <p class="text-gray-600 mb-4">You do not have permission to access this area.</p>
                    <x-button label="Go to Home" link="{{ route('admin.home') }}" class="btn-primary" />
                </div>
            </div>
        @endif
    @else
        <div class="min-h-screen flex items-center justify-center bg-gray-50">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Authentication Required</h1>
                <p class="text-gray-600 mb-4">Please log in to continue.</p>
                <x-button label="Login" link="{{ route('login') }}" class="btn-primary" />
            </div>
        </div>
    @endauth
</body>
</html>