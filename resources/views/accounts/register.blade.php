<x-layouts.app :title="__('accounts.title.register')">
    <form method="POST" action="{{ route('register') }}">
        @csrf
        @include('accounts.field', ['name' => 'name', 'label' => __('accounts.fields.name'), 'value' => old('name'), 'autocomplete' => 'name'])
        @include('accounts.field', ['name' => 'email', 'label' => __('accounts.fields.email'), 'value' => old('email'), 'type' => 'email', 'autocomplete' => 'email'])
        @include('accounts.field', ['name' => 'password', 'label' => __('accounts.fields.password'), 'type' => 'password', 'autocomplete' => 'new-password'])
        @include('accounts.field', ['name' => 'password_confirmation', 'label' => __('accounts.fields.password_confirmation'), 'type' => 'password', 'autocomplete' => 'new-password'])
        <button type="submit">{{ __('accounts.actions.register') }}</button>
    </form>
    <p><a href="{{ route('login') }}">{{ __('accounts.links.has_account') }}</a></p>
</x-layouts.app>
