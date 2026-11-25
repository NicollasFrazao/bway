<x-layouts.app :title="__('visit.home.title')">
    <p>{{ __('visit.home.intro') }}</p>
    <ol>
        <li>{{ __('visit.home.step_list') }}</li>
        <li>{{ __('visit.home.step_store') }}</li>
        <li>{{ __('visit.home.step_walk') }}</li>
    </ol>
    @auth
        <p><a href="{{ route('shopping-list') }}">{{ __('shopping-list.title') }}</a></p>
    @else
        <p><a href="{{ route('register') }}">{{ __('accounts.title.register') }}</a> · <a href="{{ route('login') }}">{{ __('accounts.title.login') }}</a></p>
    @endauth
</x-layouts.app>
