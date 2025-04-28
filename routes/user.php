<?php


use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserProfileController;

Route::group(['middleware' => 'auth:api', 'prefix' => 'profiles'], function () {
    Route::middleware('verified')->group(function() {
        Route::get('/', [UserProfileController::class, 'show']);
        Route::put('/', [UserProfileController::class, 'update']);
    });
});


