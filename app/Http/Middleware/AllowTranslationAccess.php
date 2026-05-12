<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowTranslationAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            return $next($request);
        }

        abort(403);
    }
}