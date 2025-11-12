<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\ClickController;



// Oddiy GET so'rovi
Route::get('/ec', function () {
    return response()->json(["status" => "yest"]);
});
Route::post('user/create', [AuthUserController::class, 'create']);
Route::post('user/resend-code', [AuthUserController::class, 'resendCode']);
Route::post('user/verify-code', [AuthUserController::class, 'verifyCode']);
Route::post('/login', [AuthUserController::class, 'login']);
Route::post('/refresh', [AuthUserController::class, 'refresh']);
Route::post('/logout', [AuthUserController::class, 'logout']);

Route::middleware('auth.jwt')->group(function () {
    Route::resource('user', UserController::class);
    Route::resource('click', ClickController::class);
});



Route::post('admin/login', [AdminController::class,'login']);
Route::prefix('admin')->middleware('auth.admin.jwt')->group(function () {
    Route::resource('data', AdminController::class);
    Route::resource('vacancy', VacancyController::class);
});


