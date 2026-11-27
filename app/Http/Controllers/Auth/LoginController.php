<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\TwoFactorAuthenticatable;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('livewire.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $credentials = $request->only('name', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::guard('web')->validate($credentials)) {
            RateLimiter::hit($this->throttleKey($request));

            event(new Failed(config('fortify.guard', 'web'), null, [
                Fortify::username() => $request->input('name'),
                'password' => $request->input('password'),
            ]));

            throw ValidationException::withMessages([
                Fortify::username() => [trans('auth.failed')],
            ]);
        }

        /** @var User|null $user */
        $user = Auth::guard('web')->getProvider()->retrieveByCredentials($credentials);

        if (! $user) {
            throw ValidationException::withMessages([
                Fortify::username() => [trans('auth.failed')],
            ]);
        }

        if (config('hashing.rehash_on_login', true)) {
            $provider = Auth::guard('web')->getProvider();
            if (method_exists($provider, 'rehashPasswordIfRequired')) {
                $provider->rehashPasswordIfRequired($user, $credentials);
            }
        }

        if ($this->shouldChallengeTwoFactor($user)) {
            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $remember,
            ]);

            RateLimiter::clear($this->throttleKey($request));
            TwoFactorAuthenticationChallenged::dispatch($user);

            return redirect()->route('two-factor.login');
        }

        Auth::guard('web')->login($user, $remember);
        $request->session()->regenerate();
        RateLimiter::clear($this->throttleKey($request));

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function shouldChallengeTwoFactor(User $user): bool
    {
        if (! in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user), true)) {
            return false;
        }

        if (Fortify::confirmsTwoFactorAuthentication()) {
            return ! empty($user->two_factor_secret) && ! is_null($user->two_factor_confirmed_at);
        }

        return ! empty($user->two_factor_secret);
    }

    private function throttleKey(Request $request): string
    {
        return \Illuminate\Support\Str::transliterate(\Illuminate\Support\Str::lower((string) $request->input(Fortify::username())).'|'.$request->ip());
    }
}
