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
use \App\Http\Controllers\AiController;
use \App\Http\Controllers\UserTelegramController;
use \App\Http\Controllers\CaptchaController;
use \App\Http\Controllers\CheckTestProcessController;
use App\Http\Controllers\Telegram\TelegramWebhookController;



Route::resource('region', RegionController::class)->only(['index', 'show']);
Route::resource('dir_demands', DirDemandController::class)->only(['index', 'show']);
Route::resource('adder_demands', AdderDemandController::class)->only(['index', 'show']);
Route::resource('occupation', OccupationController::class)->only(['index', 'show']);
Route::resource('specials', SpecialOccupationController::class)->only(['index', 'show']);
Route::resource('faqs', FaqsController::class)->only(['index', 'show']);

Route::get('/auth/user', [AuthUserController::class, 'userAdmin']);
Route::prefix('auth')->group(function () {
    Route::post('telegram/session', [\App\Http\Controllers\Telegram\TelegramAuthController::class, 'createSession']);
    Route::get('telegram/check-session', [\App\Http\Controllers\Telegram\TelegramAuthController::class, 'checkSession']);
    Route::post('password/request-reset', [PasswordResetController::class, 'requestReset'])->middleware('throttle:password_reset');
    Route::post('password/request-deletion', [PasswordResetController::class, 'requestDeletion'])->middleware('throttle:password_reset');
    Route::post('password/verify-pinfl', [PasswordResetController::class, 'verifyPinfl'])->middleware('throttle:password_reset');
    Route::post('password/verify-code', [PasswordResetController::class, 'verifyCode'])->middleware('throttle:password_reset');
});

Route::post('user/create', [AuthUserController::class, 'create'])->middleware('throttle:user_create');
Route::post('resend-code', [AuthUserController::class, 'resendCode'])->middleware('throttle:resend_code');
Route::post('verify-code', [AuthUserController::class, 'verifyCode'])->middleware('throttle:verify_code');
Route::post('/login', [AuthUserController::class, 'login'])->middleware('throttle:login');
Route::post('/refresh', [AuthUserController::class, 'refresh']);
Route::post('/logout', [AuthUserController::class, 'logout']);
Route::post('/vacancy-count', [VacancyController::class, 'viewCount']);
Route::get('vacancy', [UserVacancyController::class, "index"]);
Route::get('vacancy/archive', [UserVacancyController::class, "archive"]);
Route::get('/get-captcha', [CaptchaController::class, 'getCaptcha']);

Route::middleware('auth.jwt')->group(function () {
    Route::resource('user', UserController::class);
    Route::post('userUpdate', [AuthUserController::class, 'userUpdate']);
    Route::post('photo-user', [AuthUserController::class, 'photoUser']);
    Route::resource('click', ClickController::class);
    Route::get('docs/{filepath}', [UserController::class, 'show'])->where('filepath', '.*');
    Route::resource('userVacancy', UserVacancyController::class);
    Route::post('click/vacancyInfo', [UserVacancyController::class, 'userVacancy']);
    Route::post('click/userDoc', [UserVacancyController::class, 'clickVacancyUserDoc']);
    Route::post('/get-passport-data', [UserController::class, 'getPassportData']);
    Route::post('/telegram/login', [UserTelegramController::class, 'genCodeTelegram']);
});

// Admin katalog boshqaruvi (Frontend admin so'rovlari uchun)
Route::middleware('auth.admin.jwt')->group(function () {
    Route::resource('dir_demands', DirDemandController::class)->except(['index', 'show']);
    Route::resource('adder_demands', AdderDemandController::class)->except(['index', 'show']);
    Route::resource('occupation', OccupationController::class)->except(['index', 'show']);
    Route::resource('specials', SpecialOccupationController::class)->except(['index', 'show']);
    Route::resource('faqs', FaqsController::class)->except(['index', 'show']);
});

Route::post('admin/login', [AdminController::class, 'login'])->middleware('throttle:5,1');
Route::prefix('admin')->middleware('auth.admin.jwt')->group(function () {
    Route::resource('data', AdminController::class);

    Route::post('delayWorker', [AdminController::class, 'delayWorker']);
    Route::post('check', [AdminController::class, 'checkDoc']);
    Route::get('search/occupation', [OccupationController::class, 'searchOccupation']);
    Route::get('user/clicks', [ClickController::class, 'adminUserClicks']);
    Route::post('user/clicks/manual', [ClickController::class, 'storeManualClick'])->middleware('admin.permission:manual-click-create');
    Route::post('response/click', [ClickController::class, 'responseClick']);
    Route::get('user/clicks/export', [ClickController::class, 'exportUsers']);
    Route::post('publication', [VacancyController::class, 'publication'])->middleware('admin.permission:vacancy-publication');
    Route::resource('vacancy', VacancyController::class);
    Route::get('docs/{filepath}', [UserController::class, 'show'])->where('filepath', '.*');
    Route::get('users/infos', [UserController::class, 'tableUsers']);
    Route::get('divisions', [RegionController::class, 'adminDivision']);
    Route::resource('check/test/process', CheckTestProcessController::class);
    Route::post('ai/response', [AiController::class, 'getAiResponse']);
});

Route::post('telegram/webhook', [TelegramWebhookController::class, 'handle'])->middleware('verify.telegram.signature');


