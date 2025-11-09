<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;



// Oddiy GET so'rovi
Route::get('/ec', function () {
    return response()->json(["status" => "yest"]);
});
Route::resource('user', UserController::class);
Route::post('user/resend-code', [UserController::class, 'resendCode']);
Route::middleware('auth:sanctum')->post('user/verify-code', [UserController::class, 'verifyCode']);
