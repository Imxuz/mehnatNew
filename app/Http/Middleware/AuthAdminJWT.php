<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Illuminate\Http\JsonResponse;

class AuthAdminJWT extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $admin = auth('apiAdmin')->authenticate();
            if (!$admin) {
                return response()->json(['error' => 'Admin not found'], 404);
            }
        } catch (Exception $e) {
            return match (true) {
                $e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException =>
                response()->json(['error' => 'Token is Invalid'], 401),
                $e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException =>
                $this->handleExpiredToken($request),
                default =>
                response()->json(['error' => 'Authorization Token not found'], 401),
            };
        }

        return $next($request);
    }

    protected function handleExpiredToken($request): JsonResponse
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json([
                'error' => 'Token expired',
                'refresh_token' => $newToken,
            ], 401);
        } catch (Exception $e) {
            return response()->json(['error' => 'Token expired and cannot be refreshed'], 401);
        }
    }
}
