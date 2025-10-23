<div class="flex flex-col gap-2">
    <label for="{{ $attributes->get('id') }}">{{ $attributes->get('label') }}</label>
    <input 
        type="text" 
        name="{{ $attributes->get('name') }}" 
        id="{{ $attributes->get('id') }}"
        placeholder="{{ $attributes->get('placeholder') }}"
        class="p-2 border-1 border-gray-600 rounded-md"
    >
</div>