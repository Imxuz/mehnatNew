<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $admin = auth('apiAdmin')->user();

        if (!$admin) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        if (!$admin->hasPermission($permission)) {
            return response()->json([
                'message' => 'Sizda ushbu amal uchun ruxsat yo‘q'
            ], 403);
        }

        return $next($request);
    }
}
