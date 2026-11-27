<div class="notes-page" x-data="{ search: '', confirmDelete: null }">
    <section class="notes-heading">
        <div>
            <span class="workspace-eyebrow">RUANG PRIBADI</span>
            <h1>{{ $activeTab === 'trash' ? 'Tempat sampah' : 'Catatan saya' }}</h1>
            <p>{{ $activeTab === 'trash' ? 'Catatan di sini dapat dipulihkan atau dihapus permanen.' : 'Simpan ide, daftar, dan hal penting Anda di satu tempat.' }}</p>
        </div>

        @if ($activeTab === 'active')
            <flux:button variant="primary" icon="plus" wire:click="startCreate">Catatan baru</flux:button>
        @endif
    </section>

    <section class="notes-toolbar">
        <div class="notes-tabs" role="tablist" aria-label="Jenis catatan">
            <button type="button" role="tab" aria-selected="{{ $activeTab === 'active' ? 'true' : 'false' }}" @class(['notes-tab', 'is-selected' => $activeTab === 'active']) wire:click="setTab('active')">Semua catatan</button>
            <button type="button" role="tab" aria-selected="{{ $activeTab === 'trash' ? 'true' : 'false' }}" @class(['notes-tab', 'is-selected' => $activeTab === 'trash']) wire:click="setTab('trash')">Tempat sampah</button>
        </div>

        @if (count($notes) > 0)
            <label class="notes-search">
                <flux:icon.magnifying-glass class="size-4" />
                <input type="search" x-model="search" placeholder="Cari catatan..." aria-label="Cari catatan">
                <span class="sr-only">Pencarian berjalan di browser dengan Alpine.js.</span>
            </label>
        @endif
    </section>

    @if ($usingCache)
        <p class="notes-cache-note"><span></span> Daftar dimuat menggunakan cache selama 5 menit</p>
    @endif

    @if ($showForm)
        <section class="note-editor" aria-labelledby="note-editor-heading">
            <div class="note-editor-heading">
                <div>
                    <span class="workspace-eyebrow">{{ $editingId ? 'PERBARUI CATATAN' : 'CATATAN BARU' }}</span>
                    <h2 id="note-editor-heading">{{ $editingId ? 'Edit catatan' : 'Tulis catatan' }}</h2>
                </div>
                <flux:button variant="ghost" icon="x-mark" aria-label="Tutup formulir" wire:click="$set('showForm', false)" />
            </div>

            <form wire:submit="save" class="note-form">
                <flux:input wire:model="title" label="Judul" placeholder="Contoh: Ide untuk akhir pekan" maxlength="120" required />
                <flux:textarea wire:model="content" label="Isi catatan" placeholder="Tuliskan catatan Anda di sini..." rows="5" maxlength="5000" required />
                <div class="note-form-actions">
                    <flux:button type="button" variant="ghost" wire:click="$set('showForm', false)">Batal</flux:button>
                    <flux:button type="submit" variant="primary">{{ $editingId ? 'Simpan perubahan' : 'Simpan catatan' }}</flux:button>
                </div>
            </form>
        </section>
    @endif

    @if (count($notes) === 0)
        <section class="notes-empty">
            <span class="notes-empty-icon" aria-hidden="true">{{ $activeTab === 'trash' ? '↺' : '✎' }}</span>
            <h2>{{ $activeTab === 'trash' ? 'Tempat sampah kosong' : 'Belum ada catatan' }}</h2>
            <p>{{ $activeTab === 'trash' ? 'Catatan yang Anda hapus akan muncul di sini.' : 'Mulai dengan menulis catatan pertama Anda.' }}</p>
            @if ($activeTab === 'active')
                <flux:button variant="primary" icon="plus" wire:click="startCreate">Buat catatan</flux:button>
            @endif
        </section>
    @else
        <section class="notes-grid" aria-label="Daftar catatan">
            @foreach ($notes as $note)
                <article
                    wire:key="note-{{ $note['id'] }}-{{ $activeTab }}"
                    class="note-card"
                    data-search="{{ Illuminate\Support\Str::lower($note['title'].' '.$note['content']) }}"
                    x-show="!search || $el.dataset.search.includes(search.toLowerCase())"
                >
                    <div class="note-card-top">
                        <span class="note-card-date">{{ $note['date'] }}</span>
                        @if ($activeTab === 'active')
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" aria-label="Aksi catatan {{ $note['title'] }}" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil-square" wire:click="edit({{ $note['id'] }})">Edit</flux:menu.item>
                                    <flux:menu.item icon="trash" variant="danger" x-on:click="confirmDelete = {{ $note['id'] }}">Pindahkan ke sampah</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        @endif
                    </div>

                    <h2>{{ $note['title'] }}</h2>
                    <p class="note-card-content">{{ $note['content'] }}</p>

                    @if ($activeTab === 'trash')
                        <div class="note-trash-actions">
                            <flux:button size="sm" variant="filled" icon="arrow-uturn-left" wire:click="restoreNote({{ $note['id'] }})">Pulihkan</flux:button>
                            <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="permanentlyDeleteNote({{ $note['id'] }})">Hapus permanen</flux:button>
                        </div>
                    @endif
                </article>
            @endforeach
        </section>

        <p
            class="notes-search-empty"
            x-cloak
            x-show="search && Array.from($el.previousElementSibling.querySelectorAll('.note-card')).every(card => getComputedStyle(card).display === 'none')"
            aria-live="polite"
        >
            Tidak ada catatan yang cocok dengan pencarian.
        </p>
    @endif

    <div
        class="note-confirm-backdrop"
        x-cloak
        x-show="confirmDelete !== null"
        x-transition.opacity
        x-on:keydown.escape.window="confirmDelete = null"
        x-on:click.self="confirmDelete = null"
        role="dialog"
        aria-modal="true"
        aria-labelledby="delete-dialog-title"
    >
        <section class="note-confirm-dialog">
            <span class="note-confirm-icon" aria-hidden="true">!</span>
            <h2 id="delete-dialog-title">Pindahkan catatan?</h2>
            <p>Catatan bisa dipulihkan kembali dari tempat sampah.</p>
            <div class="note-form-actions">
                <flux:button variant="ghost" x-on:click="confirmDelete = null">Batal</flux:button>
                <flux:button variant="danger" x-on:click="$wire.deleteNote(confirmDelete); confirmDelete = null">Pindahkan</flux:button>
            </div>
        </section>
    </div>
</div>
