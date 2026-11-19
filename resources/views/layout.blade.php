<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Employee management</title>
    @vite('resources/css/app.css')
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ url('/employees') }}">
                <span class="brand-mark" aria-hidden="true">E</span>
                <span>Employee<span class="brand-light"> / Management</span></span>
            </a>
            <nav aria-label="Main navigation">
                <a class="nav-link" href="{{ url('/employees') }}" aria-current="{{ request()->is('employees*') ? 'page' : 'false' }}">Employees</a>
            </nav>
        </div>
    </header>

    <main class="page-shell">
        @if (session('success'))
            <div class="notice notice-success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="notice notice-error" role="alert">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="site-footer">
        Employee management <span aria-hidden="true">&middot;</span> People first, always.
    </footer>
</body>
</html>