<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(private UserService $userService)
    {
    }
    public function index()
    {
        return response()->json(User::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }


    public function resendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "phone" => ["required", "regex:/^\+998[0-9]{9}$/"],
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
        if ($user->is_verified) {
            return response()->json(['message' => 'User already verified'], 400);
        }
        $user->verification_code = mt_rand(100000, 999999);
        $user->save();
        try {
            $this->userService->sendVerificationCode($user);
            return response()->json(['message' => 'Verification code resent successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 429);
        }
    }

    public function verifyCode(Request $request)
    {
        $user = $request->user();

        if ($user->verification_code === $request->code) {
            $user->is_verified = true;
            $user->verification_code = null;
            $user->save();

            return response()->json(['message' => 'User verified successfully']);
        }

        return response()->json(['message' => 'Invalid verification code'], 400);
    }

}
