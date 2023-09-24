@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-default-color font-semibold text-xs mb-2']) }}>
    {{ $value ?? $slot }}
</label>
