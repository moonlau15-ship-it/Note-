<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Akun' }} | Ruang Akun</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="account-page">
        <main class="account-shell">
            <section class="account-intro" aria-label="Informasi aplikasi">
                <a class="account-brand" href="{{ route('home') }}">
                    <span class="account-brand-mark" aria-hidden="true">A</span>
                    <span>Ruang Akun</span>
                </a>

                <div class="account-intro-copy">
                    <span class="account-eyebrow">RUANG UNTUK ANDA</span>
                    <h1>Akun Anda,<br>akses yang lebih mudah.</h1>
                    <p>Masuk untuk melanjutkan, atau buat akun baru dalam beberapa langkah sederhana.</p>
                </div>

                <div class="account-orbit account-orbit-one" aria-hidden="true"></div>
                <div class="account-orbit account-orbit-two" aria-hidden="true"></div>
                <span class="account-intro-note">Sederhana. Aman. Milik Anda.</span>
            </section>

            <section class="account-content">
                <div class="account-form-wrap">
                    {{ $slot }}
                </div>
                <p class="account-footer">Gunakan akun Anda untuk melanjutkan dengan aman.</p>
            </section>
        </main>
    </body>
</html>
