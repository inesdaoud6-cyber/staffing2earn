<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
   public function handle(Request $request, Closure $next): Response
{
    if (! auth()->check()) {
        return redirect()->route('filament.admin.auth.login');
    }

    if (auth()->user()->hasRole('admin')) {
        return $next($request);
    }

    return redirect('/candidate/dashboard')
        ->with('error', 'Accès réservé aux administrateurs.');
}
}