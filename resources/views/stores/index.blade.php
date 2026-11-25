<x-layouts.app :title="__('visit.choose')">
    @if ($stores === [])
        <p>{{ __('stores.none') }}</p>
    @else
        <ul>
            @foreach ($stores as $store)
                <li>
                    <span>{{ $store->name->value }}</span>
                    <a href="{{ route('visit', $store->id->value) }}">{{ __('visit.actions.plan') }}</a>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.app>
