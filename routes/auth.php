<?php
// routes/api.php
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerifyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);

        // Запрос и подтверждение удаления аккаунта пользователем
        Route::post('request-deletion', [AuthController::class, 'requestAccountDeletion']);
    });

});

// Маршрут подтверждения удаления (не требует auth:api, но требует signed)
Route::get('confirm-deletion/{user}', [AuthController::class, 'confirmAccountDeletion'])
    ->middleware('signed')
    ->name('api.auth.confirm-deletion');




// --- Верификация Email  ---
// Обработка клика по ссылке верификации из письма
Route::get('/email/verify/{id}/{hash}', [EmailVerifyController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');

// Уведомление о необходимости верификации (если пользователь пытается получить доступ к 'verified' маршруту без верификации)
Route::get('/email/verify', function () {
    return response()->json(['message' => 'Требуется подтверждение адреса электронной почты.'], 403);
})->middleware('auth:api')->name('verification.notice');

// Повторная отправка письма верификации
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Ссылка для подтверждения отправлена повторно.']);
})->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');

