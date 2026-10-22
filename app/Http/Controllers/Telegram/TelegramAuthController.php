<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Services\Telegram\TelegramAuthSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramAuthController extends Controller
{
    public function __construct(
        protected TelegramAuthSessionService $sessionService
    ) {}

    /**
     * Yangi Telegram orqali kirish sessiyasini yaratish
     * POST /api/auth/telegram/session
     */
    public function createSession(Request $request): JsonResponse
    {
        $mode = (string) $request->input('mode', 'login');
        $session = $this->sessionService->createSession($mode);

        return response()->json([
            'status' => true,
            'token' => $session['token'],
            'mode' => $session['mode'] ?? 'login',
            'bot_username' => $session['bot_username'],
            'auth_url' => $session['auth_url'],
            'expires_in' => $session['expires_in'],
        ]);
    }

    /**
     * Sessiya holatini tekshirish (Frontend polling uchun)
     * GET /api/auth/telegram/check-session?token=auth_...
     */
    public function checkSession(Request $request): JsonResponse
    {
        $token = (string) $request->query('token');

        if (empty($token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token ko‘rsatilmadi.',
            ], 422);
        }

        $result = $this->sessionService->checkAndConsumeSession($token);

        return response()->json($result);
    }
}
