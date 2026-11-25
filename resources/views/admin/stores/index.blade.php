<x-layouts.app :title="__('stores.title')">
    <p><a href="{{ route('admin.stores.create') }}">{{ __('stores.new') }}</a></p>
    @if ($stores === [])
        <p>{{ __('stores.none') }}</p>
    @else
        <ul>
            @foreach ($stores as $store)
                <li>
                    <span>{{ $store->name->value }}</span>
                    <span>{{ __('stores.sectors', ['count' => count($store->sectors())]) }}</span>
                    <a href="{{ route('admin.stores.show', $store->id->value) }}">{{ __('stores.actions.open') }}</a>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.app>
