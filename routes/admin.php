<?php
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'admin', 'verified'], 'prefix' => 'admin'], function () {
    Route::apiResource('users', AdminUserController::class);

});
