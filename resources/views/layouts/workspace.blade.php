<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Ruang Catatan' }} | Ruang Catatan</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="workspace-page">
        <div class="workspace-shell">
            <header class="workspace-header">
                <a class="workspace-brand" href="{{ route('dashboard') }}" wire:navigate>
                    <span class="workspace-brand-icon">R</span>
                    <span>Ruang Catatan</span>
                </a>

                <nav class="workspace-nav" aria-label="Navigasi utama">
                    <a href="{{ route('dashboard') }}" wire:navigate @class(['workspace-nav-link', 'is-active' => request()->routeIs('dashboard')])>Beranda</a>
                    <a href="{{ route('notes.index') }}" wire:navigate @class(['workspace-nav-link', 'is-active' => request()->routeIs('notes.*')])>Catatan</a>
                </nav>

                <div class="workspace-user">
                    <span class="workspace-user-name">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="workspace-logout" type="submit">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="workspace-main">
                {{ $slot }}
            </main>

            <footer class="workspace-footer">Catatan Anda tersimpan dengan aman di akun ini.</footer>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
