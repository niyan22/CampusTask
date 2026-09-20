{{-- Input teks + label + pesan error. Pakai: <x-field name="email" label="Email" type="email" :value="$user->email" /> --}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-ultramarine">
        {{ $label }}
        @if ($hint)
            <span class="font-normal text-ultramarine/60">{{ $hint }}</span>
        @endif
    </label>

    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}"
           @error($name) aria-invalid="true" @enderror
           {{ $attributes->merge(['class' => 'w-full rounded-xl border border-periwinkle bg-white px-4 py-2.5 text-sm text-ultramarine placeholder:text-ultramarine/40 focus:border-amethyst focus:outline-2 focus:outline-amethyst/30 aria-invalid:border-rose-400']) }}>

    @error($name)
        <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
    @enderror
</div>
