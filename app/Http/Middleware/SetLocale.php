<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Read locale from cookie (works across all middleware stacks including Filament)
        $locale = $request->cookie('locale', config('app.locale', 'fr'));

        if (in_array($locale, ['fr', 'en', 'ar'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}