<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Redirect HTTP requests to HTTPS when enabled.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldForceHttps() && ! $request->secure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }

    private function shouldForceHttps(): bool
    {
        if (filter_var(config('app.force_https'), FILTER_VALIDATE_BOOL)) {
            return true;
        }

        $appUrl = (string) config('app.url');

        return str_starts_with($appUrl, 'https://');
    }
}
