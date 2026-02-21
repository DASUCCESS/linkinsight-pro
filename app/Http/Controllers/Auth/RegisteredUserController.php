<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Public user registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Toggle from settings: auth.require_email_verification
        $settingValue = Setting::getValue('auth', 'require_email_verification', true);
        $requireVerification = ! in_array($settingValue, [false, 0, '0', 'no', 'off'], true);

        if ($requireVerification) {
            event(new Registered($user));
        } else {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * Admin registration form (uses admin auth view).
     * Protected by auth+admin in routes.
     */
    public function createAdmin(): View
    {
        return view('admin.auth.register');
    }

    /**
     * Handle admin registration.
     */
    public function storeAdmin(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => User::ROLE_ADMIN,
            'status'   => 'active',
            'is_admin' => true,
        ]);

        $settingValue = Setting::getValue('auth', 'require_email_verification', true);
        $requireVerification = ! in_array($settingValue, [false, 0, '0', 'no', 'off'], true);

        if ($requireVerification) {
            event(new Registered($user));
        } else {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user);

        return redirect()->route('admin.dashboard');
    }
}
