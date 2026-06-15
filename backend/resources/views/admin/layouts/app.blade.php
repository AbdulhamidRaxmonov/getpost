<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'YesPOS') - YesPOS Admin</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link { @apply flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-300 hover:bg-white/10 hover:text-white transition-all duration-200; }
        .sidebar-link.active { @apply bg-blue-600 text-white; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans" x-data="{ sidebarOpen: true }">

<!-- Sidebar -->
<div class="fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300"
     :class="sidebarOpen ? 'w-64' : 'w-16'"
     style="background: linear-gradient(180deg, #1e3a5f 0%, #0f2444 100%);">

    <!-- Logo -->
    <div class="flex items-center gap-3 h-16 px-4 border-b border-white/10">
        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-cash-register text-white text-sm"></i>
        </div>
        <span class="text-white font-bold text-xl" x-show="sidebarOpen" x-cloak>YesPOS</span>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto py-4 px-2">
        @if(auth()->user()->isSuperAdmin())
        <!-- Super Admin Menu -->
        <div class="mb-2" x-show="sidebarOpen" x-cloak>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-2 mb-1">Super Admin</p>
        </div>
        <a href="{{ route('super.dashboard') }}"
           class="sidebar-link {{ request()->routeIs('super.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt w-5 text-center"></i>
            <span x-show="sidebarOpen" x-cloak>Dashboard</span>
        </a>
        <a href="{{ route('super.organizations.index') }}"
           class="sidebar-link {{ request()->routeIs('super.organizations*') ? 'active' : '' }}">
            <i class="fas fa-building w-5 text-center"></i>
            <span x-show="sidebarOpen" x-cloak>Tashkilotlar</span>
        </a>
        <a href="{{ route('super.users.index') }}"
           class="sidebar-link {{ request()->routeIs('super.users*') ? 'active' : '' }}">
            <i class="fas fa-users w-5 text-center"></i>
            <span x-show="sidebarOpen" x-cloak>Foydalanuvchilar</span>
        </a>
        <a href="{{ route('super.reports.index') }}"
           class="sidebar-link {{ request()->routeIs('super.reports*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar w-5 text-center"></i>
            <span x-show="sidebarOpen" x-cloak>Hisobotlar</span>
        </a>
        <hr class="border-white/10 my-3">
        @endif

        <!-- Merchant Menu -->
        @if(auth()->user()->isOrgAdmin() || auth()->user()->isSuperAdmin())
        <div class="mb-2" x-show="sidebarOpen" x-cloak>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-2 mb-1">
                {{ auth()->user()->isSuperAdmin() ? 'Merchant Panel' : 'Boshqaruv' }}
            </p>
        </div>
        <a href="{{ route('merchant.dashboard') }}"
           class="sidebar-link {{ request()->routeIs('merchant.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home w-5 text-center"></i>
            <span x-show="sidebarOpen" x-cloak>Dashboard</span>
        </a>
        <a href="{{ route('merchant.products.index') }}"
           class="sidebar-link {{ request()->routeIs('merchant.products*') ? 'active' : '' }}">
            <i class="fas fa-box w-5 text-center"></i>
            <span x-show="sidebarOpen" x-cloak>Mahsulotlar</span>
        </a>
        <a href="{{ route('merchant.categories.index') }}"
           class="sidebar-link {{ request()->routeIs('merchant.categories*') ? 'active' : '' }}">
            <i class="fas fa-tags w-5 text-center"></i>
            <span x-show="sidebarOpen" x-cloak>Kategoriyalar</span>
        </a>
        <a href="{{ route('merchant.stocks.index') }}"
           class="sidebar-link {{ request()->routeIs('merchant.stocks*') ? 'active' : '' }}">
            <i class="fas fa-warehouse w-5 text-center"></i>
            <span x-show="sidebarOpen" x-cloak>Ombor</span>
        </a>
        <a href="{{ route('merchant.supply-orders.index') }}"
           class="sidebar-link {{ request()->routeIs('merchant.supply-orders*') ? 'active' : '' }}">
            <i class="fas fa-truck w-5 text-center"></i>
            <span x-show="sidebarOpen" x-cloak>Kirim</span>
        </a>
        <a href="{{ route('merchant.orders.index') }}"
           class="sidebar-link {{ request()->routeIs('merchant.orders*') ? 'active' : '' }}">
            <i class="fas fa-receipt w-5 text-center"></i>
            <span x-show="sidebarOpen" x-cloak>Savdolar</span>
        </a>
        <a href="{{ route('merchant.customers.index') }}"
           class="sidebar-link {{ request()->routeIs('merchant.customers*') ? 'active' : '' }}">
            <i class="fas fa-user-friends w-5 text-center"></i>
            <span x-show="sidebarOpen" x-cloak>Mijozlar</span>
        </a>
        <a href="{{ route('merchant.users.index') }}"
           class="sidebar-link {{ request()->routeIs('merchant.users*') ? 'active' : '' }}">
            <i class="fas fa-user-cog w-5 text-center"></i>
            <span x-show="sidebarOpen" x-cloak>Hodimlar</span>
        </a>
        <a href="{{ route('merchant.branches.index') }}"
           class="sidebar-link {{ request()->routeIs('merchant.branches*') ? 'active' : '' }}">
            <i class="fas fa-store w-5 text-center"></i>
            <span x-show="sidebarOpen" x-cloak>Filiallar</span>
        </a>
        <a href="{{ route('merchant.terminals.index') }}"
           class="sidebar-link {{ request()->routeIs('merchant.terminals*') ? 'active' : '' }}">
            <i class="fas fa-desktop w-5 text-center"></i>
            <span x-show="sidebarOpen" x-cloak>Terminallar</span>
        </a>

        <!-- Reports submenu -->
        <div x-data="{ open: {{ request()->routeIs('merchant.reports*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="sidebar-link w-full {{ request()->routeIs('merchant.reports*') ? 'active' : '' }}">
                <i class="fas fa-chart-pie w-5 text-center"></i>
                <span x-show="sidebarOpen" x-cloak class="flex-1 text-left">Hisobotlar</span>
                <i class="fas fa-chevron-down text-xs" x-show="sidebarOpen" x-cloak :class="open ? 'rotate-180' : ''" style="transition: transform 0.2s"></i>
            </button>
            <div x-show="open" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="{{ route('merchant.reports.sales') }}" class="sidebar-link text-sm {{ request()->routeIs('merchant.reports.sales') ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-5 text-center"></i>
                    <span x-show="sidebarOpen" x-cloak>Sotuv</span>
                </a>
                <a href="{{ route('merchant.reports.products') }}" class="sidebar-link text-sm {{ request()->routeIs('merchant.reports.products') ? 'active' : '' }}">
                    <i class="fas fa-box-open w-5 text-center"></i>
                    <span x-show="sidebarOpen" x-cloak>Mahsulotlar</span>
                </a>
                <a href="{{ route('merchant.reports.shifts') }}" class="sidebar-link text-sm {{ request()->routeIs('merchant.reports.shifts') ? 'active' : '' }}">
                    <i class="fas fa-clock w-5 text-center"></i>
                    <span x-show="sidebarOpen" x-cloak>Smenalar</span>
                </a>
                <a href="{{ route('merchant.reports.cashiers') }}" class="sidebar-link text-sm {{ request()->routeIs('merchant.reports.cashiers') ? 'active' : '' }}">
                    <i class="fas fa-user-tie w-5 text-center"></i>
                    <span x-show="sidebarOpen" x-cloak>Kassirlar</span>
                </a>
            </div>
        </div>
        @endif
    </nav>

    <!-- Bottom -->
    <div class="p-2 border-t border-white/10">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="sidebar-link w-full text-red-400 hover:text-red-300 hover:bg-red-500/20">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span x-show="sidebarOpen" x-cloak>Chiqish</span>
            </button>
        </form>
    </div>
</div>

<!-- Main Content -->
<div class="transition-all duration-300" :class="sidebarOpen ? 'ml-64' : 'ml-16'">
    <!-- Top Bar -->
    <header class="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm">
        <div class="flex items-center justify-between h-16 px-6">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen"
                        class="text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="text-sm text-gray-500">
                    <span>@yield('breadcrumb', 'YesPOS Admin')</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <!-- Organization name -->
                @if(auth()->user()->organization)
                <span class="text-sm text-gray-600 bg-blue-50 px-3 py-1 rounded-full">
                    <i class="fas fa-building text-blue-500 mr-1"></i>
                    {{ auth()->user()->organization->name }}
                </span>
                @endif

                <!-- User -->
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="hidden md:block">
                        <p class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ auth()->user()->role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="p-6">
        <!-- Flash Messages -->
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="mb-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle text-green-500"></i>
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="ml-auto text-green-500 hover:text-green-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif
        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <i class="fas fa-exclamation-circle text-red-500"></i>
            <span>{{ session('error') }}</span>
            <button @click="show = false" class="ml-auto text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif
        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                <li class="text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
