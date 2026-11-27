<?php

namespace Tests\Feature\Notes;

use App\Livewire\Notes\Index;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class NotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_notes(): void
    {
        $this->get(route('notes.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_create_edit_soft_delete_and_restore_notes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(Index::class)
            ->set('title', 'Ide awal')
            ->set('content', 'Konten catatan pertama')
            ->call('save')
            ->assertHasNoErrors();

        $note = Note::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Ide awal', $note->title);

        $component
            ->call('edit', $note->id)
            ->set('title', 'Ide diperbarui')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => 'Ide diperbarui',
        ]);

        $component->call('deleteNote', $note->id);
        $this->assertSoftDeleted('notes', ['id' => $note->id]);

        $component
            ->call('setTab', 'trash')
            ->assertSee('Ide diperbarui')
            ->call('restoreNote', $note->id);

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'deleted_at' => null,
        ]);
    }

    public function test_notes_page_caches_data_per_user(): void
    {
        $user = User::factory()->create();
        $user->notes()->create([
            'title' => 'Catatan dari database',
            'content' => 'Konten yang tampil dari cache.',
        ]);

        Cache::forget("notes.user.{$user->id}.active");

        $this->actingAs($user)
            ->get(route('notes.index'))
            ->assertOk()
            ->assertSee('Catatan dari database')
            ->assertSee('cache selama 5 menit');

        $this->assertTrue(Cache::has("notes.user.{$user->id}.active"));
    }

    public function test_users_only_see_their_own_notes(): void
    {
        $owner = User::factory()->create();
        $owner->notes()->create([
            'title' => 'Catatan pribadi',
            'content' => 'Hanya pemilik yang boleh melihat ini.',
        ]);

        $otherUser = User::factory()->create();

        Livewire::actingAs($otherUser)
            ->test(Index::class)
            ->assertDontSee('Catatan pribadi');
    }
}
