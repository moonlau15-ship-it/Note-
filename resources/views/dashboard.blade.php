<x-layouts::workspace title="Beranda">
    <section class="dashboard-welcome">
        <span class="workspace-eyebrow">RUANG PRIBADI</span>
        <h1>Halo, {{ auth()->user()->name }}.</h1>
        <p>Semua ide dan hal penting Anda, tersusun rapi di satu tempat.</p>
        <a class="dashboard-cta" href="{{ route('notes.index') }}" wire:navigate>
            <span class="dashboard-cta-icon" aria-hidden="true">✎</span>
            <span>
                <strong>Buka catatan</strong>
                <small>Lihat, buat, dan kelola catatan Anda</small>
            </span>
            <span class="dashboard-cta-arrow" aria-hidden="true">→</span>
        </a>
    </section>
</x-layouts::workspace>
