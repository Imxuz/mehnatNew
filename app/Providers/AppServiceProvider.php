<?php

namespace App\Providers;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Foydalanuvchi kirishi (Login) - Brute-force ga qarshi:
        // Telefon raqami + Qurilma barmoq izi (yoki IP) bo'yicha daqiqasiga 5 marta
        RateLimiter::for('login', function (Request $request) {
            $device = $request->header('X-Device-Fingerprint') ?: $request->ip();
            $phone = (string) $request->input('phone', '');
            return Limit::perMinute(5)->by($phone . '|' . $device)->response(function () {
                return response()->json([
                    'message' => 'Juda ko‘p urinish amalga oshirildi. Iltimos, 1 daqiqadan so‘ng qayta urinib ko‘ring.'
                ], 429);
            });
        });

        // 2. SMS kodni tasdiqlash (Verify Code):
        // Telefon raqami + Qurilma barmoq izi bo'yicha daqiqasiga 5 marta
        RateLimiter::for('verify_code', function (Request $request) {
            $device = $request->header('X-Device-Fingerprint') ?: $request->ip();
            $phone = (string) $request->input('phone', '');
            return Limit::perMinute(5)->by($phone . '|' . $device)->response(function () {
                return response()->json([
                    'message' => 'Tasdiqlash kodini tekshirish uchun juda ko‘p urinish qilindi. 1 daqiqa kuting.'
                ], 429);
            });
        });

        // 3. SMS qayta yuborish (Resend Code):
        // Telefon raqami + Qurilma barmoq izi bo'yicha daqiqasiga 3 marta
        // (Asosiy kunlik limit va 60s cooldown AuthUserService da tekshiriladi)
        RateLimiter::for('resend_code', function (Request $request) {
            $device = $request->header('X-Device-Fingerprint') ?: $request->ip();
            $phone = (string) $request->input('phone', '');
            return Limit::perMinute(3)->by($phone . '|' . $device)->response(function () {
                return response()->json([
                    'message' => 'SMS yuborish bo‘yicha ko‘p so‘rov yuborildi. Iltimos, biroz kuting.'
                ], 429);
            });
        });

        // 4. Ro'yxatdan o'tish (User Create):
        // Qurilma barmoq izi bo'yicha daqiqasiga 10 marta
        RateLimiter::for('user_create', function (Request $request) {
            $device = $request->header('X-Device-Fingerprint') ?: $request->ip();
            return Limit::perMinute(10)->by($device)->response(function () {
                return response()->json([
                    'message' => 'Ushbu qurilmadan juda ko‘p ro‘yxatdan o‘tish so‘rovi yuborildi. Biroz kuting.'
                ], 429);
            });
        });

        // 5. Parolni tiklash va so'rovlar (Password Reset / Deletion / Pinfl):
        // Telefon / JSHSHIR + Qurilma barmoq izi bo'yicha daqiqasiga 5 marta
        RateLimiter::for('password_reset', function (Request $request) {
            $device = $request->header('X-Device-Fingerprint') ?: $request->ip();
            $key = (string) ($request->input('phone') ?: $request->input('pinfl', ''));
            return Limit::perMinute(5)->by($key . '|' . $device)->response(function () {
                return response()->json([
                    'message' => 'Parolni tiklash bo‘yicha juda ko‘p so‘rov yuborildi. 1 daqiqa kuting.'
                ], 429);
            });
        });

        // 6. Telegram orqali vakansiyalarni tekshirish:
        RateLimiter::for('telegram_vacancies', function (Request $request) {
            return Limit::perMinute(5)->by($request->telegram_id ?: $request->ip());
        });

        // 7. Arizani bekor qilish (Click destroy):
        // User ID + Vakansiya ID bo'yicha har 120 daqiqada 1 marta
        RateLimiter::for('click_destroy', function (Request $request) {
            $userId = auth('api')->id();
            $vacancyId = $request->input('vacancy_id', '');
            $key = $userId ? ($userId . '|' . $vacancyId) : ($request->header('X-Device-Fingerprint') ?: $request->ip());
            return Limit::perMinutes(120, 1)->by($key)->response(function () {
                return response()->json([
                    'message' => 'Ushbu vakansiyaga berilgan arizani bekor qilish uchun juda ko‘p urinish qilindi. Iltimos, keyinroq urinib ko‘ring.'
                ], 429);
            });
        });
    }
}
