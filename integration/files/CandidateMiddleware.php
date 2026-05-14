<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CandidateMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('filament.candidate.auth.login');
        }

        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('candidate')) {
            return $next($request);
        }

        $user->assignRole('candidate');

        return $next($request);
    }
}
