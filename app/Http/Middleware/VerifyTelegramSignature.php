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
        $secret = (string) config('app.telegram_api_secret');

        // Agar secret kalit belgilanmagan bo'lsa
        if (empty($secret)) {
            return $next($request);
        }

        // 1. Rasmiy Telegram Bot API secret_token tekshiruvi
        $tgHeader = $request->header('X-Telegram-Bot-Api-Secret-Token');
        if ($tgHeader && hash_equals($secret, (string) $tgHeader)) {
            return $next($request);
        }

        // 2. Ichki mikroxizmatlar uchun X-SIGNATURE HMAC-SHA256 tekshiruvi
        $payload = $request->getContent();
        $expected = hash_hmac('sha256', $payload, $secret);
        $signature = $request->header('X-SIGNATURE');

        if ($signature && hash_equals($expected, (string) $signature)) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Unauthorized webhook signature'
        ], 401);
    }
}
