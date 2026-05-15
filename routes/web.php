<?php

use App\Http\Controllers\AuthController;
use App\Http\Middleware\AdminMiddleware;
use Barryvdh\TranslationManager\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

Route::get('/auth/login',  [AuthController::class, 'showLogin'])->name('auth.login');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login.post');

Route::get('/admin-login',  [AuthController::class, 'showAdminLogin'])->name('auth.admin.login');
Route::post('/admin-login', [AuthController::class, 'adminLogin'])->name('auth.admin.login.post');

Route::get('/auth/register',  [AuthController::class, 'showRegister'])->name('auth.register');
Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register.post');

Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::get('/login', fn () => redirect()->route('auth.login'))->name('login');

Route::post('/candidate/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->name('filament.candidate.auth.logout');

Route::get('/lang/{locale}', function (string $locale) {
    $referer = request()->headers->get('referer', url('/'));
    $response = redirect($referer);

    if (in_array($locale, ['fr', 'en', 'ar'])) {
        // Use a cookie — works across all middleware stacks (web + Filament panels)
        $response->withCookie(cookie()->forever('locale', $locale, '/', null, false, false, false, 'lax'));
    }

    return $response;
})->name('lang.switch');
Route::get('/debug-locale', function () {
    return response()->json([
        'cookie_locale'   => request()->cookie('locale', 'NOT SET'),
        'session_locale'  => session('locale', 'NOT SET'),
        'app_locale'      => app()->getLocale(),
        'config_locale'   => config('app.locale'),
        'all_cookies'     => array_keys(request()->cookies->all()),
        'test_trans'      => __('nav.workspace_management'),
    ]);
});



Route::middleware('auth')->group(function () {
    Route::get('/email/verify', fn () => view('auth.verify-email'))
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect(route('filament.candidate.pages.dashboard'))
            ->with('success', __('Email verified successfully!'));
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', __('Verification link sent!'));
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::group([
    'prefix'     => 'translations',
    'middleware' => ['web', 'auth', AdminMiddleware::class],
], function () {
    Route::get('/',                   [Controller::class, 'getIndex']);
    Route::get('/view/{groupKey?}',   [Controller::class, 'getView']);
    Route::post('/add/{group}',       [Controller::class, 'postAdd']);
    Route::post('/edit/{group}/{key}',[Controller::class, 'postEdit']);
    Route::post('/delete/{group}/{key}', [Controller::class, 'postDelete']);
    Route::post('/import',            [Controller::class, 'postImport']);
    Route::post('/find',              [Controller::class, 'postFind']);
    Route::post('/publish',           [Controller::class, 'postPublish']);
});