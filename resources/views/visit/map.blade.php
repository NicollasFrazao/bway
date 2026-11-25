@php
    $size = max($map->size->width, $map->size->height);
    $label = $size / 40;
    $centre = static fn ($area): array => [$area->origin->x + $area->width / 2, $area->origin->y + $area->height / 2];
    $points = static fn ($leg): string => implode(' ', array_map(static fn ($cell): string => ($cell->x + 0.5).','.($cell->y + 0.5), $leg->corners));
    $highlighted = $map->legs[$current - 1] ?? null;
@endphp
<x-layouts.app :title="__('visit.map.title')">
    @if ($highlighted !== null)
        <p role="status">{{ __('visit.map.leg', ['number' => $current, 'total' => count($map->legs), 'from' => $highlighted->from->name->value, 'to' => $highlighted->to->name->value, 'steps' => $highlighted->length]) }}</p>
        <p>
            @if ($current > 1)
                <a href="{{ route('visit.map', [$storeId, 'leg' => $current - 1]) }}">{{ __('visit.map.previous') }}</a>
            @endif
            @if ($current < count($map->legs))
                <a href="{{ route('visit.map', [$storeId, 'leg' => $current + 1]) }}">{{ __('visit.map.next') }}</a>
            @endif
        </p>
    @else
        <p>{{ __('visit.nothing') }}</p>
    @endif
    <figure class="map">
        <svg viewBox="0 0 {{ $map->size->width }} {{ $map->size->height }}" role="img" aria-labelledby="map-title map-desc">
            <title id="map-title">{{ __('visit.map.alt', ['store' => $map->store->name->value]) }}</title>
            <desc id="map-desc">{{ implode(', ', array_map(static fn ($leg) => $leg->to->name->value, $map->legs)) }}</desc>
            @foreach ($map->store->partitions() as $partition)
                <rect x="{{ $partition->area->origin->x }}" y="{{ $partition->area->origin->y }}" width="{{ $partition->area->width }}" height="{{ $partition->area->height }}" fill="#94a3b8"/>
            @endforeach
            @foreach ($map->store->sectors() as $sector)
                <rect x="{{ $sector->area->origin->x }}" y="{{ $sector->area->origin->y }}" width="{{ $sector->area->width }}" height="{{ $sector->area->height }}" fill="{{ $sector->isEntrance() ? '#bbf7d0' : '#e0f2fe' }}" stroke="#0369a1" stroke-width="1" vector-effect="non-scaling-stroke"/>
            @endforeach
            @foreach ($map->legs as $index => $leg)
                <polyline points="{{ $points($leg) }}" fill="none" stroke="{{ $index + 1 === $current ? '#c2410c' : '#fdba74' }}" stroke-width="{{ $index + 1 === $current ? 4 : 2 }}" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
            @endforeach
            @foreach ($map->legs as $index => $leg)
                @php([$x, $y] = $centre($leg->to->area))
                <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $label * 0.7 }}" fill="#0f172a"/>
                <text x="{{ $x }}" y="{{ $y }}" font-size="{{ $label }}" fill="#fff" text-anchor="middle" dominant-baseline="central">{{ $index + 1 }}</text>
            @endforeach
        </svg>
        <figcaption class="muted">{{ __('visit.map.caption') }}</figcaption>
    </figure>
    <p><a href="{{ route('visit', $storeId) }}">{{ __('visit.title') }}</a></p>
</x-layouts.app>
