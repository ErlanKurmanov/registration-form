<?php


use App\Http\Controllers\CartController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProductController;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;


Route::group(['prefix' => 'products'], function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::get('/category/{id}', [ProductController::class, 'byCategory']);

    // Избранное

    Route::post('/{product}/favorite', [FavoriteController::class, 'addFavorite']);
    Route::delete('/{product}/favorite', [FavoriteController::class, 'removeFavorite']);
    // Опционально: маршрут для получения списка избранных товаров текущего пользователя
    Route::get('/user/favorites', [FavoriteController::class, 'getUserFavorites']);

    //Add middleware for changing products
    Route::post('/', [ProductController::class, 'store']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
});


Route::get('/check', function(){
    dump(Cart::getForUser($user));
});

Route::middleware('auth:api')->group(function () {
//    Route::post('/cart/sync', function(){
//    return response()->json(['message' => 'success']);
//});
    Route::post('/cart/sync', [CartController::class, 'sync']);
    Route::post('/cart/add', [CartController::class, 'addItem']);
    Route::delete('/cart/remove/{product}', [CartController::class, 'removeItem']);
    Route::get('/cart', [CartController::class, 'show']);

    Route::get('cart/test', function (){
        $cart = Cart::getForUser(Auth::user());
        return (new \App\Http\Resources\CartResource($cart));
    });
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
