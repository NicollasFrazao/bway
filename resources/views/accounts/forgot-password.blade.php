<x-layouts.app :title="__('accounts.title.forgot')">
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        @include('accounts.field', ['name' => 'email', 'label' => __('accounts.fields.email'), 'value' => old('email'), 'type' => 'email', 'autocomplete' => 'email'])
        <button type="submit">{{ __('accounts.actions.send_link') }}</button>
    </form>
    <p><a href="{{ route('login') }}">{{ __('accounts.links.has_account') }}</a></p>
</x-layouts.app>
