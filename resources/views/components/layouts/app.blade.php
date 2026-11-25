<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <style>
        body { margin: 0; font-family: system-ui, sans-serif; background: #f8fafc; color: #0f172a; }
        header, main { max-width: 40rem; margin: 0 auto; padding: 1rem; }
        header { display: flex; gap: 1rem; align-items: center; justify-content: flex-end; }
        a { color: #0369a1; }
        label { display: block; margin-top: .75rem; }
        input { display: block; width: 100%; max-width: 24rem; padding: .4rem; margin-top: .25rem; }
        input[type=checkbox] { display: inline; width: auto; }
        button { margin-top: .5rem; padding: .4rem .9rem; }
        ul { padding-left: 0; list-style: none; }
        li { display: flex; gap: 1rem; align-items: center; justify-content: space-between; padding: .25rem 0; }
        li form button { margin-top: 0; }
        .status { padding: .5rem; background: #dcfce7; color: #14532d; }
        .error { color: #b91c1c; font-size: .9rem; }
    </style>
</head>
<body>
    @auth
        <header>
            <a href="{{ route('shopping-list') }}">{{ __('shopping-list.title') }}</a>
            @can(\App\Providers\AccountServiceProvider::MANAGE_STORES)
                <a href="{{ route('admin.stores') }}">{{ __('stores.title') }}</a>
            @endcan
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">{{ __('accounts.actions.logout') }}</button>
            </form>
        </header>
    @endauth
    <main>
        <h1>{{ $title }}</h1>
        @if (session('status'))
            <p class="status" role="status">{{ session('status') }}</p>
        @endif
        {{ $slot }}
    </main>
</body>
</html>
