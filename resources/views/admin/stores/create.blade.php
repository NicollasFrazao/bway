<x-layouts.app :title="__('stores.new')">
    <p class="muted">{{ __('stores.create_intro') }}</p>
    <form method="POST" action="{{ route('admin.stores.store') }}" enctype="multipart/form-data">
        @csrf
        <label for="name">{{ __('stores.fields.name') }}</label>
        <input id="name" name="name" value="{{ old('name') }}" required>
        @error('name')<p class="error">{{ $message }}</p>@enderror
        <label for="floor_plan">{{ __('stores.fields.floor_plan') }}</label>
        <input id="floor_plan" name="floor_plan" type="file" accept="image/jpeg,image/png,image/gif,image/webp" required>
        @error('floor_plan')<p class="error">{{ $message }}</p>@enderror
        <fieldset>
            <legend>{{ __('stores.fields.entrance') }}</legend>
            @include('admin.stores.area', ['values' => []])
            @error('area')<p class="error">{{ $message }}</p>@enderror
        </fieldset>
        <button type="submit">{{ __('stores.actions.create') }}</button>
    </form>
</x-layouts.app>
