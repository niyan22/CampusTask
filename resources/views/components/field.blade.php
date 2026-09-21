{{-- Input teks + label + pesan error. Pakai: <x-field name="email" label="Email" type="email" :value="$user->email" /> --}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-ink">
        {{ $label }}
        @if ($hint)
            <span class="font-normal text-ink/60">{{ $hint }}</span>
        @endif
    </label>

    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}"
           @error($name) aria-invalid="true" @enderror
           {{ $attributes->merge(['class' => 'input']) }}>

    @error($name)
        <p class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
    @enderror
</div>
