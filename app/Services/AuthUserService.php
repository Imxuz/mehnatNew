<?php
namespace App\Services;
use App\Jobs\SendVerificationSms;
use App\Models\PhoneAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthUserService
{
    public function __construct(private SmsService $smsService){}
    public function createUser(array $data): User
    {
        $user = User::where('phone', $data['phone'])->first();
        if ($user && $user->is_verified) {
            throw ValidationException::withMessages([
                'phone' => ['This phone number is already verified.'],
            ]);
        }

        $verificationData = [
            'agreement' => $data['agreement'],
            'password' => Hash::make($data['password']),
            'verification_code' => mt_rand(100000, 999999),
            'verification_code_expires_at' => now()->addMinutes(10),
            'is_verified' => false,
        ];
        if ($user) {
            $user->update($verificationData);
        } else {
            $verificationData['phone'] = $data['phone'];
            $user = User::create($verificationData);
        }
        $attempt = PhoneAttempt::updateOrCreate(['user_id' => $user->id], ['ip_address' => request()->ip()]);
        if ($attempt->last_sms_sent_at && !$attempt->last_sms_sent_at->isToday()) {
            $attempt->update(['sms_sent_today' => 0]);
        }
        if ($attempt->sms_sent_today >= 3) {
            throw new \Exception('Siz bugun 3 martadan ko‘p kod so‘ray olmaysiz.');
        }
        if ($attempt->last_sms_sent_at && $attempt->last_sms_sent_at->addSeconds(60)->isFuture()) {
            throw new \Exception('Kod yuborishdan oldin 60 soniya kuting.');
        }
//        SendVerificationSms::dispatch($user->phone, $user->verification_code);
        $this->smsService->sendVerificationCode($user->phone, $user->verification_code);
        $attempt->update([
            'sms_sent_today' => $attempt->sms_sent_today + 1,
            'last_sms_sent_at' => now(),
        ]);


        return $user;
    }


    public function sendVerificationCode(User $user): bool
    {
        $attempt = PhoneAttempt::firstOrCreate(['user_id' => $user->id]);
        if ($attempt->last_sms_sent_at && !$attempt->last_sms_sent_at->isToday()) {
            $attempt->update(['sms_sent_today' => 0]);
        }
        if ($attempt->sms_sent_today >= 3) {
            throw new \Exception('Siz bugun 3 martadan ko‘p kod so‘ray olmaysiz.');
        }
        if ($attempt->last_sms_sent_at && $attempt->last_sms_sent_at->addSeconds(60)->isFuture()) {
            throw new \Exception('Kod yuborishdan oldin 60 soniya kuting.');
        }
        $code = mt_rand(100000, 999999);

        $user->update([
            'verification_code' => $code ,
            'verification_code_expires_at' => now()->addMinutes(10),
            ]);
        $this->smsService->sendVerificationCode($user->phone, $code);
        $attempt->update([
            'sms_sent_today' => $attempt->sms_sent_today + 1,
            'last_sms_sent_at' => now(),
        ]);

        return true;
    }

    public function resendSms($phone){
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if ($user->is_verified) {
            return response()->json(['message' => 'User already verified'], 400);
        }
        $user->verification_code = mt_rand(100000, 999999);
        $user->save();
        try {
            $this->sendVerificationCode($user);
            return response()->json(['message' => 'Verification code resent successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 429);
        }
    }



}
