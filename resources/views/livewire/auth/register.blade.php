<x-layouts::auth title="Buat akun">
    <div class="account-form">
        <span class="account-form-kicker">MULAI DI SINI</span>
        <h2 class="account-form-title">Buat akun baru</h2>
        <p class="account-form-description">Buat nama akun dan kode untuk mendaftar.</p>

        @if ($errors->any())
            <div class="account-alert account-alert-error" role="alert">
                Ada data yang perlu diperiksa. Silakan coba lagi.
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="account-fields">
            @csrf
            <div class="account-field">
                <label for="name">Nama akun</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    placeholder="Contoh: namasaya"
                    autocomplete="username"
                    required
                    autofocus
                >
                @error('name')
                    <span class="account-field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="account-field">
                <label for="password">Kode akun</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Minimal 8 karakter"
                    autocomplete="new-password"
                    required
                >
                @error('password')
                    <span class="account-field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="account-field">
                <label for="password_confirmation">Ulangi kode akun</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    placeholder="Masukkan ulang kode akun"
                    autocomplete="new-password"
                    required
                >
            </div>

            <button class="account-submit" type="submit" data-test="register-user-button">Buat akun</button>
        </form>

        <p class="account-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </div>
</x-layouts::auth>
