<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="shopsell">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ShopSell - Modern Retail Management</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans bg-base-100">
    <div class="min-h-screen flex flex-col">
        <!-- Navbar -->
        <div class="navbar bg-base-100 border-b border-base-200 px-4 sm:px-8">
            <div class="flex-1">
                <a class="btn btn-ghost normal-case text-xl font-bold text-primary">ShopSell</a>
            </div>
            <div class="flex-none gap-2">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-sm">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary btn-sm ml-2">Register</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>

        <!-- Hero Section -->
        <div class="hero flex-grow bg-base-200">
            <div class="hero-content text-center py-20">
                <div class="max-w-md">
                    <h1 class="text-5xl font-bold">Welcome to ShopSell</h1>
                    <p class="py-6 text-lg">The all-in-one solution for managing your retail business, tracking inventory, and understanding your customers.</p>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">Get Started</a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-primary">Start Your Free Trial</a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="py-20 bg-base-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-8 text-center">
                <h2 class="text-3xl font-bold mb-12">Powerful Features</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
                    <div class="card bg-base-200 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title text-primary">Inventory Management</h3>
                            <p>Real-time tracking of your products, stock levels, and automated alerts for low inventory.</p>
                        </div>
                    </div>
                    <div class="card bg-base-200 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title text-primary">Sales Analytics</h3>
                            <p>Deep insights into your transactions, revenue growth, and top-performing products.</p>
                        </div>
                    </div>
                    <div class="card bg-base-200 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title text-primary">Customer CRM</h3>
                            <p>Build lasting relationships with your customers by tracking their preferences and history.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer p-10 bg-neutral text-neutral-content">
            <div>
                <span class="footer-title">Services</span>
                <a class="link link-hover">Inventory Management</a>
                <a class="link link-hover">Point of Sale</a>
                <a class="link link-hover">Reporting</a>
            </div>
            <div>
                <span class="footer-title">Company</span>
                <a class="link link-hover">About us</a>
                <a class="link link-hover">Contact</a>
                <a class="link link-hover">Legal</a>
            </div>
            <div>
                <span class="footer-title">Legal</span>
                <a class="link link-hover">Terms of use</a>
                <a class="link link-hover">Privacy policy</a>
                <a class="link link-hover">Cookie policy</a>
            </div>
        </footer>
    </div>
</body>
</html>
