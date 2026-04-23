<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\User;
use App\Models\UserTelegram;
use App\Models\Vacancy;
use Illuminate\Http\Request;

class UserTelegramController extends Controller
{
    public function activeVacancy(){
        $vacancies = Vacancy::with('demands.dir_demand','occupation','region','oClick')->orderBy('created_at','desc')
            ->where('publication','!=', null)->where('close_at',">",now())->get();
        return response()->json($vacancies);
    }
    public function genCodeTelegram()
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['message' => 'Foydalanuvchi topilmadi'], 401);
        }
        $userTelegram = UserTelegram::firstOrCreate(
            ['user_id' => $user->id],
            ['attempt' => 0, 'attempt_time' => now()]
        );
        if ($userTelegram->expired_at > now()) {
            return response()->json([
                'remnant_sec' => round(now()->diffInSeconds($userTelegram->expired_at)),
                'message' => "SMS yuborilganiga 10 daqiqa bo'lmadi"
            ], 429);
        }
        if ($userTelegram->attempt_time && $userTelegram->attempt_time->addDay()->isPast()) {
            $userTelegram->update([
                'attempt' => 0,
                'attempt_time' => now()
            ]);
        }
        if ($userTelegram->attempt >= 3) {
            return response()->json([
                'status' => false,
                'message' => "Bugungi 3 ta urinish tugagan. Ertaga qayta urinib ko'ring."
            ], 429);
        }
        $code = str()->random(10);
        $userTelegram->update([
            'sms_code' => $code,
            'expired_at' => now()->addMinutes(10),
            'attempt' => $userTelegram->attempt + 1,
            'attempt_time' => now()
        ]);
        return response()->json([
            'status' => true,
            'code' => $code,
            'message' => 'Kod muvaffaqiyatli yaratildi'
        ]);
    }


    public function checkUser(Request $request)
    {
        $telegramId = $request->input('telegram_id');
        $rawPhone = $request->input('phone');
        $inputCode = $request->input('secret_code');

        $formattedPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        $user = User::where('phone', $formattedPhone)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => "Ushbu raqamli foydalanuvchi topilmadi. Iltimos, raqamni to'g'ri formatda yuboring (+998XXXXXXXXX)."
            ]);
        }
        $userTelegram = UserTelegram::where('user_id', $user->id)->first();

        if (!$userTelegram) {
            return response()->json([
                'status' => 'error',
                'message' => "Sizga tizimga kirish uchun ruxsat berilmagan (Telegram ma'lumotlari topilmadi)."
            ]);
        }

        if ($userTelegram->expired_at<now()){
            return response()->json([
                'status' => 'error',
                'message' => "Siz mehnat.ngmk.uz portalidan parolni qayta oling.\n❗️Parol hali o'rnatilmagan yoki eskirgan bo'lishi mumkin"
            ]);
        }

        if ($userTelegram->sms_code != $inputCode) {
            return response()->json([
                'status' => 'error',
                'message' => "Maxfiy kod xato. Iltimos, qaytadan urinib ko'ring."
            ]);
        }

        $userTelegram->update([
            'telegram_id' => $telegramId,
            'sms_code' => null,
            'expired_at'=>null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Muvaffaqiyatli kirdingiz! Profilingiz Telegram bilan bog'landi."
        ]);
    }

    public function exitUser(Request $request)
    {
        $user = UserTelegram::where('telegram_id', $request->input('telegram_id'))->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => "Foydalanuvchi topilmadi."
            ], 404);
        }
        $user->update([
            'telegram_id' => null
        ]);
        return response()->json([
            'status' => 'success',
            'message' => "Foydalanuvchi muvaffaqiyatli chiqdi."
        ]);
    }

    public function checkAuth(Request $request)
    {
        $telegramId = $request->input('telegram_id');
        $exists = UserTelegram::where('telegram_id', $telegramId)
            ->whereNotNull('telegram_id')
            ->exists();

        return response()->json([
            'is_logged_in' => $exists
        ]);
    }

    public function myVacancies(Request $request){
        $telegramId = $request->input('telegram_id');
        $user = UserTelegram::where('telegram_id',$telegramId)->first();
        $userId = $user->user_id;
        $clickVacancies = Click:: where('user_id', $userId)->pluck('vacancy_id');
        $userVacancies = Vacancy::with([
            'demands.dir_demand',
            'occupation',
            'region',
            'telegClick' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }
        ])
            ->whereIn('id',$clickVacancies)
            ->orderBy('created_at','asc')->limit(5)->get();
        return response()->json([
            'status' => 'success',
            'data' => $userVacancies
        ]);
    }

    public function usersList(){
        $users = UserTelegram::select('telegram_id as id')->get();
        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }
    public function publicationId(Request $request) {
        $vacancy_id = $request->vacancy_id;
        $vacancy = Vacancy::with([
            'demands.dir_demand',
            'occupation',
            'region',
        ])->find($vacancy_id);
        if (!$vacancy) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vakansiya topilmadi'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $vacancy
        ]);
    }


}
