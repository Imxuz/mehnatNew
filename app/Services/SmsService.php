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
        $cleanPhone = Str::of($phone)->remove(['+', '-', ' '])->toString();
        $formattedPhone = '+' . $cleanPhone;

        $message = "НГМК Код верификации: $code";

        try {
            $response = Http::withoutVerifying()
                ->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ],
                ])
                ->asForm()
                ->timeout(5)
                ->connectTimeout(3)
                ->post('https://172.16.0.195:7443/sendsms.php', [
                    'username' => 'orisp',
                    'password' => 'oror1414*',
                    'to' => $formattedPhone,
                    'text' => $message,
                    'from' => 'mehnatUz',
                    'smsc' => 'smsc18',
                    'coding' => '2',
                    'charset' => 'utf-8',
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
