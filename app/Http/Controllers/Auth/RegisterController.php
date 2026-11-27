<?php

namespace App\Http\Controllers\Auth;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisterController extends Controller
{
    use PasswordValidationRules, ProfileValidationRules;

    public function create(): View
    {
        return view('livewire.auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $input = $request->all();

        if (is_string($input['name'] ?? null)) {
            $input['name'] = Str::lower($input['name']);
        }

        Validator::make($input, [
            'name' => $this->nameRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        User::create([
            'name' => $input['name'],
            'password' => Hash::make($input['password']),
        ]);

        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()
            ->route('login')
            ->with('status', 'Akun berhasil dibuat. Silakan masuk.');
    }
}
