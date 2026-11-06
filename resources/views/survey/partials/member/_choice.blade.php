{{-- Selectable option cards. Params: $name, $type ('radio'|'checkbox'), $options, $required (bool), $cols (1|2) --}}
@php($selected = (array) old($name, []))
<div class="choice-grid {{ ($cols ?? 2) == 1 ? 'choice-grid-1' : '' }}" data-choice-group="{{ $name }}"
    {{ !empty($required) ? 'data-required' : '' }}>
    @foreach ($options as $option)
        <label class="choice-card {{ in_array($option, $selected) ? 'active' : '' }}">
            <input type="{{ $type }}" name="{{ $name }}[]" value="{{ $option }}"
                {{ in_array($option, $selected) ? 'checked' : '' }}>
            <span class="choice-indicator {{ $type === 'radio' ? 'is-radio' : '' }}"></span>
            <span class="choice-label">{{ ($labels ?? [])[$option] ?? $option }}</span>
        </label>
    @endforeach
</div>
<div class="invalid-feedback-custom group-feedback">Please select at least one option.</div>
