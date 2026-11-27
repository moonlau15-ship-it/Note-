<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['name' => 'demo'],
            [
                'email' => null,
                'password' => 'password123',
            ],
        );

        Note::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Selamat datang'],
            ['content' => 'Catatan ini dibuat oleh seeder. Anda dapat mengubah atau menghapusnya kapan saja.'],
        );

        Note::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Coba fitur CRUD'],
            ['content' => 'Buat catatan baru, edit isinya, lalu coba pulihkan dari tempat sampah.'],
        );
    }
}
