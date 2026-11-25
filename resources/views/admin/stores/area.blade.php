{{-- The four numbers of a rectangle on the grid, for a form. --}}
@foreach (['x', 'y', 'width', 'height'] as $field)
    <label>{{ __('stores.fields.'.$field) }}
        <input type="number" name="{{ $field }}" min="0" value="{{ $values[$field] ?? old($field) }}" required>
    </label>
@endforeach
