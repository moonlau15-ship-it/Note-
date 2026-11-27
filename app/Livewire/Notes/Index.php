<?php

namespace App\Livewire\Notes;

use App\Models\Note;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.workspace')]
#[Title('Catatan')]
class Index extends Component
{
    public string $title = '';

    public string $content = '';

    public ?int $editingId = null;

    public string $activeTab = 'active';

    public bool $showForm = false;

    public function render(): View
    {
        $userId = (int) Auth::id();
        $tab = $this->activeTab === 'trash' ? 'trash' : 'active';
        $notes = Cache::remember(
            $this->cacheKey($userId, $tab),
            now()->addMinutes(5),
            function () use ($userId, $tab) {
                $query = Note::query()->where('user_id', $userId);

                if ($tab === 'trash') {
                    $query->onlyTrashed();
                }

                return $query->latest()->get()->map(fn (Note $note): array => [
                    'id' => $note->id,
                    'title' => $note->title,
                    'content' => $note->content,
                    'date' => ($note->deleted_at ?? $note->updated_at)?->format('d M Y') ?? '',
                ])->all();
            },
        );

        return view('livewire.notes.index', [
            'notes' => $notes,
            'usingCache' => true,
        ]);
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['active', 'trash'], true)) {
            throw ValidationException::withMessages([
                'activeTab' => 'Pilihan tampilan tidak valid.',
            ]);
        }

        $this->activeTab = $tab;
        $this->resetForm();
    }

    public function startCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $note = $this->findOwnedNote($id);

        $this->editingId = $note->id;
        $this->title = $note->title;
        $this->content = $note->content;
        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string', 'max:5000'],
        ]);
        $wasEditing = $this->editingId !== null;

        if ($wasEditing) {
            $note = $this->findOwnedNote($this->editingId);
            $note->update($validated);
        } else {
            Auth::user()->notes()->create($validated);
        }

        $this->forgetNotesCache();
        $this->resetForm();

        Flux::toast(
            variant: 'success',
            text: $wasEditing ? 'Catatan berhasil diperbarui.' : 'Catatan berhasil disimpan.',
        );
    }

    public function deleteNote(int $id): void
    {
        $this->findOwnedNote($id)->delete();
        $this->forgetNotesCache();
        $this->resetForm();

        Flux::toast(variant: 'success', text: 'Catatan dipindahkan ke tempat sampah.');
    }

    public function restoreNote(int $id): void
    {
        $this->findOwnedNote($id, withTrashed: true)->restore();
        $this->forgetNotesCache();

        Flux::toast(variant: 'success', text: 'Catatan berhasil dipulihkan.');
    }

    public function permanentlyDeleteNote(int $id): void
    {
        $this->findOwnedNote($id, withTrashed: true)->forceDelete();
        $this->forgetNotesCache();

        Flux::toast(variant: 'success', text: 'Catatan dihapus permanen.');
    }

    private function findOwnedNote(int $id, bool $withTrashed = false): Note
    {
        $query = Note::query()->where('user_id', Auth::id());

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    private function forgetNotesCache(): void
    {
        $userId = (int) Auth::id();

        Cache::forget($this->cacheKey($userId, 'active'));
        Cache::forget($this->cacheKey($userId, 'trash'));
    }

    private function cacheKey(int $userId, string $tab): string
    {
        return "notes.user.{$userId}.{$tab}";
    }

    private function resetForm(): void
    {
        $this->reset(['title', 'content', 'editingId']);
        $this->resetValidation();
        $this->showForm = false;
    }
}
