<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and attach standard cybersecurity headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Clickjacking himoyasi
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // MIME-type sniffing himoyasi
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Brauzer XSS filtri
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer ma'lumotlari sizib chiqishini cheklash
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Brauzer qurilma ruxsatlari siyosati
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');

        return $response;
    }
}
