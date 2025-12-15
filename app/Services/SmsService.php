<?php
namespace App\Services;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class SmsService
{
    public static function sendVerificationCode(string $phone, int $code): bool
    {
        $phone = Str::of($phone)
            ->remove(['+', '-', ' '])
            ->prepend('0');
        $query = 'http://172.16.0.195/playsms/index.php';
        $query .= '?app=ws&u=' . urlencode('orisp');
        $query .= '&p=' . urlencode('oror1414*');
        $query .= '&h=' . urlencode('b6b577eb826c4527f837a50abea577ea');
        $query .= '&op=' . urlencode('pv');
        $query .= '&unicode=1';
        $query .= '&to=' . urlencode($phone);

        $message = App::isLocale('ru')
            ? 'НГМК Код верификации: '.$code
            : 'NKMK tasdiqlash kodi: '.$code;

        $query .= '&msg=' . urlencode($message);
        try {
            file_get_contents($query);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
