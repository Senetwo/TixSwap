@props([
    'label' => null,
    'name',
    'type' => 'text',   // any input type, or "textarea" / "select"
    'id' => null,
    'value' => null,
    'hint' => null,
    'prefix' => null,    // e.g. "KES"
    'options' => [],     // for selects: [value => label]
    'optional' => false,
])
@php
    $id ??= $name;
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
    $hasError = $errors->has($errorKey);
    $current = old($errorKey, $value);
    $control = 'form-control '.($hasError ? 'form-control-error ' : '').($prefix ? 'pl-14 ' : '');
    $describedBy = trim(($hint ? $id.'-hint ' : '').($hasError ? $id.'-error' : ''));
@endphp
<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-800">
            {{ $label }} @if ($optional) <span class="font-normal text-gray-400">(optional)</span> @endif
        </label>
    @endif
    <div @class(['relative', 'mt-1.5' => $label])>
        @if ($prefix)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-medium text-gray-500">{{ $prefix }}</span>
        @endif
        @if ($type === 'textarea')
            <textarea id="{{ $id }}" name="{{ $name }}" @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($hasError) aria-invalid="true" @endif
                {{ $attributes->except('class')->merge(['class' => $control, 'rows' => 4]) }}>{{ $current }}</textarea>
        @elseif ($type === 'select')
            <select id="{{ $id }}" name="{{ $name }}" @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                {{ $attributes->except('class')->merge(['class' => $control]) }}>
                @foreach ($options as $optValue => $optLabel)
                    <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>{{ $optLabel }}</option>
                @endforeach
            </select>
        @else
            <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" @if ($type !== 'file' && $type !== 'password') value="{{ $current }}" @endif
                @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($hasError) aria-invalid="true" @endif
                {{ $attributes->except('class')->merge(['class' => $control]) }}>
        @endif
    </div>
    @if ($hint && ! $hasError)
        <p id="{{ $id }}-hint" class="mt-1.5 text-sm text-gray-500">{{ $hint }}</p>
    @endif
    @if ($hasError)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-red-600">{{ $errors->first($errorKey) }}</p>
    @endif
</div>
