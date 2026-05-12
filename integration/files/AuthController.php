<?php

namespace App\Http\Controllers;

use App\Services\CandidateService;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected CandidateService $candidateService
    ) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (Auth::user()->hasRole('admin')) {
                return redirect()->intended(route('filament.admin.pages.dashboard'));
            }

            return redirect()->intended(route('filament.candidate.pages.dashboard'));
        }

        return back()
            ->withErrors(['email' => __('The provided credentials do not match our records.')])
            ->onlyInput('email');
    }

    public function showAdminLogin(): View
    {
        return view('auth.admin-login');
    }

    public function adminLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (Auth::user()->hasRole('admin')) {
                return redirect()->intended(route('filament.admin.pages.dashboard'));
            }

            Auth::logout();

            return back()->withErrors([
                'email' => __('This portal is for administrators only.'),
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => __('The provided credentials do not match our records.'),
        ])->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique' => __('This email is already registered. Please login instead.'),
        ]);

        $user = User::create([
            'name'     => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $this->candidateService->createFromUser($user, $validated);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('filament.candidate.pages.dashboard'))
            ->with('success', __('Account created successfully.'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
