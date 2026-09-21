{{-- Dipasang di <head> supaya tema (terang/gelap) langsung benar sebelum halaman tampil. --}}
<script>
    try {
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    } catch (e) {}
</script>
