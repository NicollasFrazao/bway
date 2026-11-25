<x-layouts.app :title="__('accounts.title.reset')">
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        @include('accounts.field', ['name' => 'email', 'label' => __('accounts.fields.email'), 'value' => old('email', $email), 'type' => 'email', 'autocomplete' => 'email'])
        @include('accounts.field', ['name' => 'password', 'label' => __('accounts.fields.password'), 'type' => 'password', 'autocomplete' => 'new-password'])
        @include('accounts.field', ['name' => 'password_confirmation', 'label' => __('accounts.fields.password_confirmation'), 'type' => 'password', 'autocomplete' => 'new-password'])
        <button type="submit">{{ __('accounts.actions.reset') }}</button>
    </form>
</x-layouts.app>
