@php($store = $view->store)
<x-layouts.app :title="__('stores.sections.assortment').' · '.$store->name->value">
    @error('sectors')<p class="error" role="alert">{{ $message }}</p>@enderror
    <form method="POST" action="{{ route('admin.stores.assortment', $store->id->value) }}">
        @csrf
        @method('PUT')
        <ul>
            @foreach ($view->products as $product)
                @php($selected = $view->assortment->sectorSelling($product->id)?->value)
                <li>
                    <label for="product-{{ $product->id->value }}">{{ $product->name->value }}</label>
                    <select id="product-{{ $product->id->value }}" name="sectors[{{ $product->id->value }}]">
                        <option value="">{{ __('stores.not_sold') }}</option>
                        @foreach ($store->sectors() as $sector)
                            @unless ($sector->isEntrance())
                                <option value="{{ $sector->id->value }}" @selected($selected === $sector->id->value)>{{ $sector->name->value }}</option>
                            @endunless
                        @endforeach
                    </select>
                </li>
            @endforeach
        </ul>
        <button type="submit">{{ __('stores.actions.save_assortment') }}</button>
    </form>
    <p><a href="{{ route('admin.stores.show', $store->id->value) }}">{{ $store->name->value }}</a></p>
</x-layouts.app>
