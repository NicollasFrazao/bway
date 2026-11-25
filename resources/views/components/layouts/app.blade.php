<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <style>
        :root { --ink: #0f172a; --muted: #475569; --link: #0369a1; --accent: #c2410c; --paper: #f8fafc; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, sans-serif; font-size: 1rem; line-height: 1.5; background: var(--paper); color: var(--ink); }
        header, main { max-width: 42rem; margin: 0 auto; padding: 1rem; }
        header { display: flex; flex-wrap: wrap; gap: .75rem 1rem; align-items: center; }
        header .brand { font-weight: 700; margin-right: auto; color: var(--ink); text-decoration: none; }
        header form { margin: 0; }
        a { color: var(--link); }
        a:focus-visible, button:focus-visible, input:focus-visible { outline: 3px solid var(--accent); outline-offset: 2px; }
        label { display: block; margin-top: .75rem; }
        input { display: block; width: 100%; max-width: 24rem; padding: .5rem; margin-top: .25rem; font-size: 1rem; }
        input[type=checkbox] { display: inline; width: auto; }
        button { margin-top: .5rem; padding: .5rem 1rem; font-size: 1rem; min-height: 2.75rem; cursor: pointer; }
        ul { padding-left: 0; list-style: none; }
        ol.stops { padding-left: 0; list-style: none; }
        li { display: flex; flex-wrap: wrap; gap: .5rem 1rem; align-items: center; justify-content: space-between; padding: .35rem 0; }
        li form button { margin-top: 0; }
        .status { padding: .5rem; background: #dcfce7; color: #14532d; }
        .error { color: #b91c1c; font-size: .9rem; }
        .muted { color: var(--muted); }
        .stop { border-top: 1px solid #cbd5e1; padding-top: .75rem; }
        .stop h2 { font-size: 1.1rem; margin: 0; }
        .map svg { width: 100%; height: auto; background: #fff; border: 1px solid #cbd5e1; }
    </style>
</head>
<body>
    <header>
        <a class="brand" href="{{ route('home') }}">{{ config('app.name') }}</a>
        @auth
            <a href="{{ route('shopping-list') }}">{{ __('shopping-list.title') }}</a>
            <a href="{{ route('stores') }}">{{ __('visit.choose') }}</a>
            @can(\App\Providers\AccountServiceProvider::MANAGE_STORES)
                <a href="{{ route('admin.stores') }}">{{ __('stores.title') }}</a>
            @endcan
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">{{ __('accounts.actions.logout') }}</button>
            </form>
        @else
            <a href="{{ route('login') }}">{{ __('accounts.title.login') }}</a>
            <a href="{{ route('register') }}">{{ __('accounts.title.register') }}</a>
        @endauth
    </header>
    <main>
        <h1>{{ $title }}</h1>
        @if (session('status'))
            <p class="status" role="status">{{ session('status') }}</p>
        @endif
        {{ $slot }}
    </main>
</body>
</html>
