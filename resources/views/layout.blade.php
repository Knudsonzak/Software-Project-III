<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SmartHR | @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    @vite('resources/css/app.css')
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <aside class="col-md-3 col-lg-2 sidebar p-4">
                <a class="brand" href="{{ url('/dashboard') }}"><span aria-hidden="true">✦</span> SmartHR</a>
                @auth
                    <div class="sidebar-user">{{ auth()->user()->name }}</div>
                @endauth
                <nav class="sidebar-nav" aria-label="Main navigation">
                    <a class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}" @if (request()->is('dashboard')) aria-current="page" @endif><span aria-hidden="true">▦</span> Dashboard</a>
                    <a class="sidebar-link {{ request()->is('employees*') ? 'active' : '' }}" href="{{ url('/employees') }}" @if (request()->is('employees*')) aria-current="page" @endif><span aria-hidden="true">♙</span> Employees</a>
                    <span class="sidebar-link sidebar-link-muted"><span aria-hidden="true">◷</span> Attendance</span>
                    <span class="sidebar-link sidebar-link-muted"><span aria-hidden="true">▤</span> Payroll</span>
                    @if (auth()->user()?->role === 'admin')

                        <!-- request()->('url string') this returns a boolean if the URL path has admin/leaves{anything after} * means anything can go after like {/admin/leaves or /admin/leaves/123/approve or also /admin/leaves?status=pending } -->
                        <a class="sidebar-link {{ request()->is('admin/leaves*') ? 'active' : '' }}" href="{{ route('admin.leaves.index') }}" @if (request()->is('admin/leaves*')) aria-current="page" @endif><span aria-hidden="true">◫</span> Leave requests</a>
                    @else
                        <a class="sidebar-link {{ request()->is('leaves*') ? 'active' : '' }}" href="{{ route('leaves.index') }}" @if (request()->is('leaves*')) aria-current="page" @endif><span aria-hidden="true">◫</span> Leave requests</a>
                    @endif
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="sidebar-link sidebar-logout" type="submit"><span aria-hidden="true">↪</span> Log out</button>
                        </form>
                    @endauth
                </nav>
                <div class="workspace-label">{{ auth()->check() ? strtoupper(auth()->user()->role) : 'ADMIN' }} WORKSPACE</div>
            </aside>
            <main class="col-md-9 col-lg-10 main-content">
                <div class="page-shell">
                    @if (session('success'))
                        <div class="notice notice-success" role="status">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="notice notice-error" role="alert">{{ session('error') }}</div>
                    @endif
                    @yield('content')
                </div>
                <footer class="site-footer">SmartHR <span aria-hidden="true">&middot;</span> People first, always.</footer>
            </main>
        </div>
    </div>
    @yield('scripts')
</body>
</html>