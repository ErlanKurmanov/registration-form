<?php


use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;


Route::group(['prefix' => 'products'], function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::get('/category/{id}', [ProductController::class, 'byCategory']);

    Route::post('/', [ProductController::class, 'store']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);

});



Route::middleware('auth:api')->group(function () {
//    Route::post('/cart/sync', function(){
//    return response()->json(['message' => 'success']);
//});
    Route::post('/cart/sync', [CartController::class, 'sync']);
    Route::post('/cart/add', [CartController::class, 'addItem']);
    Route::delete('/cart/remove/{product}', [CartController::class, 'removeItem']);
    Route::get('/cart', [CartController::class, 'show']);
});


Route::middleware('auth:api')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

// Опционально: fallback маршрут для ненайденных API эндпоинтов
Route::fallback(function(){
    return response()->json(['message' => 'Маршрут не найден.'], 404);
});
