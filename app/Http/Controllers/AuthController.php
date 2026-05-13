<?php

namespace App\Http\Controllers;

use App\Services\CandidateService;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                return redirect()->intended($this->adminDashboardUrl());
            }

            return redirect()->intended($this->candidateDashboardUrl());
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
                return redirect()->intended($this->adminDashboardUrl());
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

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name'     => trim($validated['first_name'] . ' ' . $validated['last_name']),
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $this->candidateService->createFromUser($user, $validated);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect($this->candidateDashboardUrl())
            ->with('success', __('Account created successfully.'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function candidateDashboardUrl(): string
    {
        try {
            return route('filament.candidate.pages.dashboard');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException) {
            return '/candidate/dashboard';
        }
    }

    private function adminDashboardUrl(): string
    {
        try {
            return route('filament.admin.pages.dashboard');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException) {
            return '/admin';
        }
    }
}
