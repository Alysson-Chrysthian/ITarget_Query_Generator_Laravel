<div class="flex flex-col gap-2">
    <label for="{{ $attributes->get('id') }}">{{ $attributes->get('label') }}</label>
    <select
        {{ $attributes->merge([
            'name' => $attributes->get('name'),
            'id' => $attributes->get('id'),
            'class' => 'border-1 border-gray-600 p-2 rounded-md'
        ]) }}
    >
        {{ $slot }}
    </select>
</div>