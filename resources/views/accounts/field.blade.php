<label for="{{ $name }}">{{ $label }}</label>
<input id="{{ $name }}" name="{{ $name }}" type="{{ $type ?? 'text' }}" value="{{ $value ?? '' }}"
    autocomplete="{{ $autocomplete ?? 'off' }}" @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror required>
@error($name)
    <p class="error" id="{{ $name }}-error">{{ $message }}</p>
@enderror
