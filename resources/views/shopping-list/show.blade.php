<x-layouts.app :title="__('shopping-list.title')">
    @if ($items === [])
        <p>{{ __('shopping-list.empty') }}</p>
    @else
        <ul>
            @foreach ($items as $item)
                <li>
                    <span>{{ $item->productName->value }}</span>
                    <form method="POST" action="{{ route('shopping-list.items.destroy', ['product' => $item->productId->value]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" aria-label="{{ __('shopping-list.actions.remove') }} {{ $item->productName->value }}">{{ __('shopping-list.actions.remove') }}</button>
                    </form>
                </li>
            @endforeach
        </ul>
        <form method="POST" action="{{ route('shopping-list.clear') }}">
            @csrf
            @method('DELETE')
            <button type="submit">{{ __('shopping-list.actions.clear') }}</button>
        </form>
    @endif

    <form method="GET" action="{{ route('shopping-list') }}" role="search">
        <label for="search">{{ __('shopping-list.search') }}</label>
        <input id="search" name="search" type="search" value="{{ $search }}">
        <button type="submit">{{ __('shopping-list.actions.search') }}</button>
    </form>
    @error('product_id')
        <p class="error" role="alert">{{ $message }}</p>
    @enderror
    @if ($search !== '' && $products === [])
        <p>{{ __('shopping-list.no_match', ['term' => $search]) }}</p>
    @endif
    <ul>
        @foreach ($products as $product)
            <li>
                <span>{{ $product->name->value }}</span>
                <form method="POST" action="{{ route('shopping-list.items.store') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id->value }}">
                    <button type="submit" aria-label="{{ __('shopping-list.actions.add') }} {{ $product->name->value }}">{{ __('shopping-list.actions.add') }}</button>
                </form>
            </li>
        @endforeach
    </ul>
</x-layouts.app>
