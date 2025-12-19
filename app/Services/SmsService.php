<?php
namespace App\Services;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SmsService
{
    public static function sendVerificationCode(string $phone, int $code): bool
    {
        $phone = Str::of($phone)->remove(['+', '-', ' '])->prepend('+');
        $message = App::isLocale('ru')
            ? "НГМК Код верификации: $code"
            : "NKMK tasdiqlash kodi: $code";
        try {
            $response = Http::withoutVerifying()
            ->timeout(5)
            ->connectTimeout(3)
            ->get('https://12.16.0.15:113/cgi-bin/sendsms', [
                'smsc'     => 'smsc18',
                'username' => 'orisp',
                'password' => 'oror1414*',
                'from'     => '1234',
                'to'       => $phone,
                'text'     => $message,
                'coding'   => '2',
                'charset'=>'utf-8',
                'dlr-mask' => '3',
            ]);

            if ($response->successful()) {
                return true;
            }
            Log::error("SMS yuborishda xatolik (Status: {$response->status()}): " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('SMS tizimiga ulanishda xatolik: ' . $e->getMessage());
            return false;
        }
    }
}
