<x-layouts::auth title="Masuk">
    <div class="account-form">
        <span class="account-form-kicker">SELAMAT DATANG KEMBALI</span>
        <h2 class="account-form-title">Masuk ke akun</h2>
        <p class="account-form-description">Masukkan nama dan kode akun Anda untuk melanjutkan.</p>

        @if (session('status'))
            <div class="account-alert account-alert-success" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="account-alert account-alert-error" role="alert">
                Periksa kembali nama akun atau kode akun Anda.
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="account-fields">
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
                    placeholder="Masukkan kode akun"
                    autocomplete="current-password"
                    required
                >
                @error('password')
                    <span class="account-field-error">{{ $message }}</span>
                @enderror
            </div>

            <label class="account-remember">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <span>Ingat saya</span>
            </label>

            <button class="account-submit" type="submit" data-test="login-button">Masuk</button>
        </form>

        <p class="account-switch">Belum punya akun? <a href="{{ route('register') }}">Buat akun</a></p>
    </div>
</x-layouts::auth>
