<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Services\Telegram\TelegramUpdateRouter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, TelegramUpdateRouter $router): JsonResponse
    {
        $update = $request->all();

        // Updateni router orqali to'g'ridan-to'g'ri qayta ishlash
        try {
            $router->route($update);
        } catch (\Exception $e) {
            Log::error("Telegram webhook handling error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
