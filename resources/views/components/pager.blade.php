{{-- Navigasi halaman sederhana. Pakai: <x-pager :paginator="$tasks" /> --}}
@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="mt-6 flex items-center justify-between gap-3 text-sm" aria-label="Halaman">
        @if ($paginator->onFirstPage())
            <span class="btn cursor-not-allowed text-ink/30 ring-1 ring-line">
                <x-icon name="chevron-left" class="size-4" /> Sebelumnya
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-ghost ring-1 ring-line">
                <x-icon name="chevron-left" class="size-4" /> Sebelumnya
            </a>
        @endif

        <span class="text-ink/70">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-ghost ring-1 ring-line">
                Berikutnya <x-icon name="chevron-right" class="size-4" />
            </a>
        @else
            <span class="btn cursor-not-allowed text-ink/30 ring-1 ring-line">
                Berikutnya <x-icon name="chevron-right" class="size-4" />
            </span>
        @endif
    </nav>
@endif
