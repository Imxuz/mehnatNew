<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserAuthRequest;
use App\Models\User;
use App\Services\AuthUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthUserController extends Controller
{
    public function __construct(private AuthUserService $authUserService)
    {
    }


    public function create(StoreUserAuthRequest $request)
    {
        $user = $this->authUserService->createUser($request->validated());

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }
    public function resendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'regex:/^998[0-9]{9}$/', 'digits:12'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        return $this->authUserService->resendSms($request->phone);

    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'regex:/^998[0-9]{9}$/', 'digits:12'],
            "code" => ["required", "digits:6"],
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
        if ($user->verification_code === $request->code) {
            $user->is_verified = true;
            $user->verification_code = null;
            $user->save();
            return response()->json(['message' => 'User verified successfully']);
        }
        return response()->json(['message' => 'Invalid verification code'], 400);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'regex:/^998[0-9]{9}$/', 'digits:12'],
            "password" => ["required", "string:100"],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $credentials = $request->only('phone', 'password');
        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Phone or password incorrect'], 401);
        }

        $user = auth('api')->user();
        if (!$user->is_verified) {
            return response()->json(['message' => 'User not verified'], 403);
        }
        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        try {
            $newToken = auth('api')->refresh();
            return $this->respondWithToken($newToken);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Could not refresh token. Token is either missing or invalid.',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user'         => Auth::user(),
        ]);
    }
}
