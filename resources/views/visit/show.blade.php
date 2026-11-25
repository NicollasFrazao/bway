<x-layouts.app :title="__('visit.title')">
    @if ($plan->stops === [])
        <p>{{ __('visit.nothing') }}</p>
    @else
        <p class="muted">{{ __('visit.summary', ['stops' => count($plan->stops), 'steps' => $plan->length, 'start' => $plan->start->name->value]) }}</p>
        <p><a href="{{ route('visit.map', $storeId) }}">{{ __('visit.actions.map') }}</a></p>
        <ol class="stops">
            @foreach ($plan->stops as $number => $stop)
                <li class="stop">
                    <h2>{{ $number + 1 }}. {{ $stop->sector->name->value }}</h2>
                    <ul style="width: 100%">
                        @foreach ($stop->items as $item)
                            <li>
                                <span>{{ $item->productName->value }}</span>
                                <form method="POST" action="{{ route('visit.acquire', [$storeId, $item->productId->value]) }}">
                                    @csrf
                                    <button type="submit" aria-label="{{ __('visit.actions.acquire_named', ['product' => $item->productName->value]) }}">{{ __('visit.actions.acquire') }}</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ol>
    @endif
    @if ($plan->unsold !== [])
        <h2>{{ __('visit.unsold') }}</h2>
        <ul>
            @foreach ($plan->unsold as $item)
                <li>{{ $item->productName->value }}</li>
            @endforeach
        </ul>
    @endif
    <p><a href="{{ route('shopping-list') }}">{{ __('shopping-list.title') }}</a> · <a href="{{ route('stores') }}">{{ __('visit.choose') }}</a></p>
</x-layouts.app>
