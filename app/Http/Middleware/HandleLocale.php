<?php

namespace App\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleLocale
{
    /**
     * Apply the authenticated user's locale preference for this request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->effectiveLocale();

        if (Locales::isSupported($locale)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
