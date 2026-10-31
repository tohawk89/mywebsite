<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * While coming soon mode is on (Site settings > Maintenance), visitors get the coming soon page.
 * Logged-in admins still see the site. 503 tells search engines it's temporary.
 */
class ShowComingSoon
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('site.maintenance.enabled') && ! $request->user()?->is_admin) {
            return response()->view('coming-soon', [], 503)->header('Retry-After', '3600');
        }

        return $next($request);
    }
}
