<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthUserController;



// Oddiy GET so'rovi
Route::get('/ec', function () {
    return response()->json(["status" => "yest"]);
});
Route::post('logi', [AuthUserController::class, 'create']);
Route::post('user/resend-code', [AuthUserController::class, 'resendCode']);
Route::post('user/verify-code', [AuthUserController::class, 'verifyCode']);
Route::post('/login', [AuthUserController::class, 'login']);
Route::post('/refresh', [AuthUserController::class, 'refresh']);
Route::post('/logout', [AuthUserController::class, 'logout']);

Route::middleware('auth.jwt')->group(function () {
    Route::resource('user', UserController::class);
});
Route::prefix('admin')->middleware('auth.admin.jwt')->group(function () {

});


