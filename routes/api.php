<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\ClickController;
use App\Http\Controllers\UserVacancyController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\DirDemandController;
use App\Http\Controllers\OccupationController;
use \App\Http\Controllers\FaqsController;
use \App\Http\Controllers\AdderDemandController;
use \App\Http\Controllers\SpecialOccupationController;
use \App\Http\Controllers\PasswordResetController;



Route::resource('region', RegionController::class);
Route::resource('dir_demands', DirDemandController::class);
Route::resource('adder_demands', AdderDemandController::class);
Route::resource('occupation', OccupationController::class);
Route::resource('specials', SpecialOccupationController::class);
Route::get('/auth/user', [AuthUserController::class, 'userAdmin']);
Route::prefix('auth')->group(function () {
    Route::post('password/request-reset', [PasswordResetController::class, 'requestReset'])->middleware('throttle:10,1');
    Route::post('password/verify-pinfl', [PasswordResetController::class, 'verifyPinfl'])->middleware('throttle:10,1');
    Route::post('password/request-deletion', [PasswordResetController::class, 'requestDeletion'])->middleware('throttle:10,1');
});

Route::post('user/create', [AuthUserController::class, 'create']);
Route::post('userUpdate', [AuthUserController::class, 'userUpdate']);
Route::post('resend-code', [AuthUserController::class, 'resendCode'])->middleware('throttle:2,1');;
Route::post('verify-code', [AuthUserController::class, 'verifyCode'])->middleware('throttle:3,1');
Route::post('/login', [AuthUserController::class, 'login'])->middleware('throttle:5,1');
Route::post('/refresh', [AuthUserController::class, 'refresh']);
Route::post('/logout', [AuthUserController::class, 'logout']);
Route::post('/vacancy-count', [VacancyController::class, 'viewCount'])->middleware('throttle:10,1');
Route::get('vacancy', [UserVacancyController::class,"index"]);
Route::resource('faqs', FaqsController::class);




Route::middleware('auth.jwt')->group(function () {
    Route::resource('user', UserController::class);
    Route::resource('click', ClickController::class);
    Route::get('docs/{filepath}', [UserController::class,'show'])->where('filepath', '.*');
    Route::resource('userVacancy', UserVacancyController::class);
    Route::post('click/vacancyInfo',[ UserVacancyController::class, 'userVacancy']);
    Route::post('/get-passport-data',[ UserController::class, 'getPassportData']);
});



Route::post('admin/login', [AdminController::class,'login']);
Route::prefix('admin')->middleware('auth.admin.jwt')->group(function () {
    Route::resource('data', AdminController::class);

    Route::post('delayWorker', [AdminController::class,'delayWorker']);
    Route::post('check', [AdminController::class,'checkDoc']);
    Route::get('search/occupation', [OccupationController::class,'searchOccupation']);
    Route::get('user/clicks', [ClickController::class,'adminUserClicks']);
    Route::post('response/click', [ClickController::class,'responseClick']);
    Route::get('user/clicks/export', [ClickController::class,'exportUsers']);
    Route::post('publication', [VacancyController::class,'publication'])->middleware('admin.permission:vacancy-publication');
    Route::resource('vacancy', VacancyController::class);
    Route::get('docs/{filepath}', [UserController::class,'show'])->where('filepath', '.*');
    Route::get('users/infos', [UserController::class,'tableUsers']);
    Route::get('divisions', [RegionController::class, 'adminDivision']);
});


