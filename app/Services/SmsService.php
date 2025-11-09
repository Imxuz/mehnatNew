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
        $query = 'http://173.45.45.45/playsms/index.php';
        $query .= '?app=ws&u=' . urlencode('orisp');
        $query .= '&p=' . urlencode('rtrtrt1212**');
        $query .= '&h=' . urlencode('dfgfdgfdgfdewrewrew');
        $query .= '&op=' . urlencode('pv');
        $query .= '&unicode=1';
        $query .= '&to=' . urlencode($phone);

        $message = App::isLocale('ru')
            ? 'Код верификации: ' . $code
            : 'Tasdiqlash kodi: ' . $code;

        $query .= '&msg=' . urlencode($message);
        try {
            file_get_contents($query);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
