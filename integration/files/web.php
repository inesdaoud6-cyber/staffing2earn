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
    if (in_array($locale, ['fr', 'en', 'ar'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('lang.switch');

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

Route::prefix('candidate')->name('candidate.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn () => redirect(route('filament.candidate.pages.dashboard')))
        ->name('dashboard');
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
