<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add security-related response headers for Best Practices / hardening.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-site');
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), interest-cohort=()'
        );

        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        if (! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy($request));
        }

        return $response;
    }

    private function contentSecurityPolicy(Request $request): string
    {
        $connectSrc = ["'self'", 'https://www.google.com', 'https://www.gstatic.com'];

        if (app()->environment('local')) {
            // Vite HMR / local asset server
            $connectSrc = array_merge($connectSrc, [
                'http://localhost:5173',
                'http://127.0.0.1:5173',
                'ws://localhost:5173',
                'ws://127.0.0.1:5173',
                'https://localhost:5173',
                'wss://localhost:5173',
            ]);
        }

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com https://www.gstatic.com",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "font-src 'self' https://fonts.bunny.net data:",
            "img-src 'self' data: blob: https:",
            'connect-src '.implode(' ', $connectSrc),
            'frame-src https://www.google.com https://www.gstatic.com https://recaptcha.google.com',
        ];

        // Only upgrade insecure subresources when the page is already HTTPS.
        if ($request->isSecure()) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }
}
