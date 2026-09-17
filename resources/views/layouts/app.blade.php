<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Meals on Wheels')</title>
    <style>
        :root { --ink:#1f2328; --muted:#656d76; --line:#d8dee4; --bg:#f6f8fa; --accent:#f97316; }
        * { box-sizing: border-box; }
        body { margin:0; font:15px/1.55 system-ui, -apple-system, "Segoe UI", sans-serif; color:var(--ink); background:var(--bg); }
        header { background:#fff; border-bottom:1px solid var(--line); }
        .bar { max-width:1040px; margin:0 auto; padding:14px 20px; display:flex; align-items:center; gap:18px; }
        .brand { font-weight:700; text-decoration:none; color:var(--ink); margin-right:auto; }
        .bar a { color:var(--muted); text-decoration:none; font-size:14px; }
        .bar a:hover { color:var(--ink); }
        main { max-width:1040px; margin:0 auto; padding:28px 20px; }
        .card { background:#fff; border:1px solid var(--line); border-radius:8px; padding:24px; }
        .narrow { max-width:400px; margin:36px auto; }
        h1 { font-size:20px; margin:0 0 4px; }
        .sub { color:var(--muted); font-size:14px; margin:0 0 20px; }
        label { display:block; font-size:13px; font-weight:600; margin:14px 0 5px; }
        input { width:100%; padding:8px 11px; border:1px solid var(--line); border-radius:6px; font-size:14px; font-family:inherit; }
        input:focus { outline:2px solid var(--accent); outline-offset:-1px; border-color:transparent; }
        button { margin-top:20px; width:100%; padding:9px; border:0; border-radius:6px; background:var(--accent); color:#fff; font-size:14px; font-weight:600; cursor:pointer; font-family:inherit; }
        button:hover { filter:brightness(.94); }
        .alert { padding:11px 14px; border-radius:6px; font-size:14px; margin-bottom:18px; }
        .alert-error { background:#fff0f0; border:1px solid #f5c2c0; color:#8b1a17; }
        .alert-ok { background:#eefbf2; border:1px solid #b7e4c7; color:#14633a; }
        .field-error { color:#b42318; font-size:12.5px; margin-top:5px; }
        .foot { text-align:center; font-size:13.5px; color:var(--muted); margin-top:18px; }
        .row { display:flex; gap:12px; }
        .row > div { flex:1; }
        table { width:100%; border-collapse:collapse; font-size:14px; }
        th, td { text-align:left; padding:9px 10px; border-bottom:1px solid var(--line); }
        th { font-size:12px; text-transform:uppercase; letter-spacing:.04em; color:var(--muted); }
    </style>
</head>
<body>
<header>
    <div class="bar">
        <a href="{{ url('/') }}" class="brand">Meals on Wheels</a>
        @auth
            <span style="font-size:14px;color:var(--muted)">
                {{ auth()->user()->full_name }} &middot; {{ auth()->user()->role->label() }}
            </span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit" style="width:auto;margin:0;padding:6px 12px;background:transparent;color:var(--muted);font-weight:400">
                    Sign out
                </button>
            </form>
        @else
            <a href="{{ route('login') }}">Sign in</a>
            <a href="{{ route('register') }}">Create account</a>
        @endauth
    </div>
</header>

<main>
    @if (session('status'))
        <div class="alert alert-ok">{{ session('status') }}</div>
    @endif

    @yield('content')
</main>
</body>
</html>
