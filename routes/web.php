<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Notes\Index as NotesIndex;

Route::redirect('/', '/login')->name('home');

Route::middleware('auth')->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('notes', NotesIndex::class)->name('notes.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/fortify-manual.php';
