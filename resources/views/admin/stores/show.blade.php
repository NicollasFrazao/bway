@php
    $store = $layout->store;
    $id = $store->id->value;
    $label = $layout->size === null ? 1 : max($layout->size->width, $layout->size->height) / 50;
@endphp
<x-layouts.app :title="$store->name->value">
    @error('area')<p class="error" role="alert">{{ $message }}</p>@enderror
    @error('floor_plan')<p class="error" role="alert">{{ $message }}</p>@enderror

    <section>
        <h2>{{ __('stores.sections.plan') }}</h2>
        @if ($layout->size === null)
            <p>{{ __('stores.not_mapped') }}</p>
        @else
            <p class="muted">{{ __('stores.grid_size', ['width' => $layout->size->width, 'height' => $layout->size->height]) }}</p>
            <figure class="map">
                <svg viewBox="0 0 {{ $layout->size->width }} {{ $layout->size->height }}" role="img" aria-label="{{ __('stores.plan_alt', ['store' => $store->name->value]) }}">
                    <image href="{{ route('admin.stores.grid', $id) }}" width="{{ $layout->size->width }}" height="{{ $layout->size->height }}" style="image-rendering: pixelated"/>
                    @foreach ($store->partitions() as $partition)
                        <rect x="{{ $partition->area->origin->x }}" y="{{ $partition->area->origin->y }}" width="{{ $partition->area->width }}" height="{{ $partition->area->height }}" fill="#16a34a" fill-opacity="0.5"/>
                    @endforeach
                    @foreach ($store->sectors() as $sector)
                        <rect x="{{ $sector->area->origin->x }}" y="{{ $sector->area->origin->y }}" width="{{ $sector->area->width }}" height="{{ $sector->area->height }}" fill="{{ $sector->isEntrance() ? '#22c55e' : '#38bdf8' }}" fill-opacity="0.4" stroke="#0f172a" stroke-width="1" vector-effect="non-scaling-stroke"/>
                        <text x="{{ $sector->area->origin->x + 1 }}" y="{{ $sector->area->origin->y + $label }}" font-size="{{ $label }}">{{ $sector->name->value }}</text>
                    @endforeach
                </svg>
            </figure>
        @endif
        <form method="POST" action="{{ route('admin.stores.floor-plan', $id) }}" enctype="multipart/form-data">
            @csrf
            <label for="floor_plan">{{ __('stores.sections.floor_plan') }}</label>
            <input id="floor_plan" name="floor_plan" type="file" accept="image/jpeg,image/png,image/gif,image/webp" required>
            <button type="submit">{{ __('stores.actions.map') }}</button>
        </form>
    </section>

    <section>
        <h2>{{ __('stores.sections.sectors') }}</h2>
        @foreach ($store->sectors() as $sector)
            <form method="POST" action="{{ route('admin.stores.sectors.update', [$id, $sector->id->value]) }}" class="stop">
                @csrf
                @method('PUT')
                <label>{{ __('stores.fields.name') }} @if ($sector->isEntrance()) ({{ __('stores.entrance') }}) @endif
                    <input name="name" value="{{ $sector->name->value }}" required>
                </label>
                @include('admin.stores.area', ['values' => ['x' => $sector->area->origin->x, 'y' => $sector->area->origin->y, 'width' => $sector->area->width, 'height' => $sector->area->height]])
                <button type="submit">{{ __('stores.actions.save') }}</button>
            </form>
            @unless ($sector->isEntrance())
                <form method="POST" action="{{ route('admin.stores.sectors.destroy', [$id, $sector->id->value]) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" aria-label="{{ __('stores.actions.remove') }} {{ $sector->name->value }}">{{ __('stores.actions.remove') }}</button>
                </form>
            @endunless
        @endforeach
        <form method="POST" action="{{ route('admin.stores.sectors.store', $id) }}" class="stop">
            @csrf
            <h3>{{ __('stores.sections.new_sector') }}</h3>
            <label>{{ __('stores.fields.name') }} <input name="name" required></label>
            @include('admin.stores.area', ['values' => []])
            <button type="submit">{{ __('stores.actions.add') }}</button>
        </form>
    </section>

    <section>
        <h2>{{ __('stores.sections.partitions') }}</h2>
        <ul>
            @foreach ($store->partitions() as $partition)
                <li>
                    <span>({{ $partition->area->origin->x }}, {{ $partition->area->origin->y }}) {{ $partition->area->width }} × {{ $partition->area->height }}</span>
                    <form method="POST" action="{{ route('admin.stores.partitions.destroy', [$id, $partition->id->value]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit">{{ __('stores.actions.remove') }}</button>
                    </form>
                </li>
            @endforeach
        </ul>
        <form method="POST" action="{{ route('admin.stores.partitions.store', $id) }}" class="stop">
            @csrf
            <h3>{{ __('stores.sections.new_partition') }}</h3>
            @include('admin.stores.area', ['values' => []])
            <button type="submit">{{ __('stores.actions.add') }}</button>
        </form>
    </section>

    <section>
        <h2>{{ __('stores.sections.assortment') }}</h2>
        <p><a href="{{ route('admin.stores.assortment', $id) }}">{{ __('stores.actions.assortment') }}</a></p>
    </section>

    <section>
        <h2>{{ __('stores.sections.routes') }}</h2>
        <p role="status">{{ __('stores.routes_progress', ['found' => $layout->routes->found, 'total' => $layout->routes->total]) }}
            @if ($layout->routes->isComplete()) {{ __('stores.routes_complete') }} @endif
            <a href="{{ route('admin.stores.show', $id) }}">{{ __('stores.refresh') }}</a>
        </p>
        @if ($layout->routes->unreachable !== [])
            <p class="error">{{ __('stores.routes_unreachable') }}</p>
            <ul>
                @foreach ($layout->routes->unreachable as [$from, $to])
                    <li>{{ $from->name->value }} → {{ $to->name->value }}</li>
                @endforeach
            </ul>
        @endif
        <form method="POST" action="{{ route('admin.stores.routes', $id) }}">
            @csrf
            <button type="submit">{{ __('stores.actions.generate') }}</button>
        </form>
    </section>
</x-layouts.app>
