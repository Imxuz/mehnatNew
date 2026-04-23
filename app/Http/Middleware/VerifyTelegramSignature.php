<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTelegramSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $secret = config('app.telegram_api_secret');
        $payload = $request->getContent();

        $expected = hash_hmac('sha256', $payload, $secret);
        $signature = $request->header('X-SIGNATURE');

        if (!$signature || !hash_equals($expected, $signature)) {
            return response()->json([
                'message' => 'System locked'
            ], 401);
        }

        return $next($request);
    }
}
