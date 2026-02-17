<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthUserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{

    public function __construct(private AuthUserService $authUserService)
    {
    }
    /**
     * 1-HOLAT: Telefon bor, JSHR yo'q
     * 2-HOLAT: Telefon bor, JSHR bor va to'g'ri kiritilgan
     */
    public function requestReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'regex:/^998[0-9]{9}$/', 'digits:12'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Telefon raqam noto\'g\'ri formatda',
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Ushbu telefon raqam bilan ro\'yxatdan o\'tgan foydalanuvchi topilmadi',
                'case' => 'user_not_found'
            ], 404);
        }


        if ($user->pinfl&&!$request->deleteProfile) {
            return response()->json([
                'success' => true,
                'case' => 'jshr_required',
                'message' => 'Iltimos, JSHR ni kiriting',
                'phone' => $request->phone
            ]);
        }
        try {
            $message = $this->authUserService->resetCode($request->phone);

            return response()->json([
                'success' => true,
                'message' => $message,
                'case' => 'code_sent',
                'delete' => $request->deleteProfile,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }

    }

    /**
     * JSHR ni tekshirish (2-HOLAT va 3-HOLAT uchun)
     */
    public function verifyPinfl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'regex:/^998[0-9]{9}$/', 'digits:12'],
            'pinfl' => 'required|digits:14',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validatsiya xatosi',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Foydalanuvchi topilmadi',
                'case' => 'user_not_found'
            ], 404);
        }
        if ($user->pinfl !== $request->pinfl) {
            return response()->json([
                'success' => false,
                'message' => "JSHIR xato kiritildi qayta urinib ko'ring",
                'case' => 'jshr_incorrect'
            ], 422);
//            return $this->handleFailedJshrAttempt($user);
        }
        try {
            $message = $this->authUserService->resetCode($request->phone);

            return response()->json([
                'success' => true,
                'message' => $message,
                'case' => 'code_sent',
                'delete' => $request->deleteProfile,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }

    /**
     * Muvaffaqiyatsiz JSHR urinishlarini boshqarish
     */

    /**
     * 3-HOLAT: Accountni o'chirishni so'rash
     */
    public function requestDeletion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'regex:/^998[0-9]{9}$/', 'digits:12'],
            "code" => ["required", "digits:6"],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validatsiya xatosi',
                'errors' => $validator->errors()
            ], 422);
        }
        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Foydalanuvchi topilmadi'
            ], 422);
        }
        if ($user->verification_code !== $request->code) {
            return response()->json([
                'success' => false,
                'message' => 'SMS kod xato kiritildi. Iltimos qayta kiriting.'
            ], 422);
        }
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => "Sizning profilingiz o'chirildi. Qayta ro'yxatdan o'tish bo'limidan ro'yxatdan o'ting"
        ], 200);
    }


    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'regex:/^998[0-9]{9}$/', 'digits:12'],
            "code" => ["required", "digits:6"],
            "password" => ["required","confirmed",Password::min(8)->letters()->mixedCase()->numbers()->symbols()]
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if ($user->verification_code_expires_at &&
            Carbon::now()->greaterThan($user->verification_code_expires_at)) {
            return response()->json([
                'message' => 'Verification code expired'
            ], 400);
        }

        if ($user->verification_code === $request->code) {
            $user->is_verified = true;
            $user->password = Hash::make($request->password);
            $user->verification_code = null;
            $user->verification_code_expires_at = null;
            $user->save();
            return response()->json(['message' => 'User verified successfully']);
        }
        return response()->json(['message' => 'Invalid verification code'], 400);
    }


}
