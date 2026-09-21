{{-- Tombol ganti tema terang/gelap. Pilihan disimpan di browser (localStorage). --}}
<button type="button" aria-label="Ganti tema terang/gelap"
        onclick="document.documentElement.classList.toggle('dark'); try { localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light'; } catch (e) {}"
        {{ $attributes->merge(['class' => 'rounded-lg p-2 transition']) }}>
    <x-icon name="moon" class="size-5 dark:hidden" />
    <x-icon name="sun" class="hidden size-5 dark:block" />
</button>
