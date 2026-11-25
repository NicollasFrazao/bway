<x-layouts.app :title="__('accounts.title.login')">
    <form method="POST" action="{{ route('login') }}">
        @csrf
        @include('accounts.field', ['name' => 'email', 'label' => __('accounts.fields.email'), 'value' => old('email'), 'type' => 'email', 'autocomplete' => 'email'])
        @include('accounts.field', ['name' => 'password', 'label' => __('accounts.fields.password'), 'type' => 'password', 'autocomplete' => 'current-password'])
        <label><input type="checkbox" name="remember" value="1"> {{ __('accounts.fields.remember') }}</label>
        <button type="submit">{{ __('accounts.actions.login') }}</button>
    </form>
    <p><a href="{{ route('password.request') }}">{{ __('accounts.links.forgot') }}</a></p>
    <p><a href="{{ route('register') }}">{{ __('accounts.links.no_account') }}</a></p>
</x-layouts.app>
