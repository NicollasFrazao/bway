<x-layouts.app :title="__('stores.title')">
    @if ($stores === [])
        <p>{{ __('stores.none') }}</p>
    @else
        <ul>
            @foreach ($stores as $store)
                <li>
                    <span>{{ $store->name->value }}</span>
                    <span>{{ __('stores.sectors', ['count' => count($store->sectors())]) }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.app>
