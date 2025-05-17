<?php

namespace App\Http\Controllers;

use App\Contracts\Services\CartServiceInterface;
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\SyncCartRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class CartController extends Controller
{
    protected CartServiceInterface $cartService;

    public function __construct(CartServiceInterface $cartService)
    {
        $this->cartService = $cartService;
    }

    // Синхронизация гостевой корзины с сервером после авторизации
    public function sync(SyncCartRequest $request): JsonResponse
    {
        $guestItems = $request->validated()['items'];

        $cart = $this->cartService->sync($guestItems);

        return (new CartResource($cart))->response();

    }

    // Добавление/обновление товара в корзине
    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $itemData = $request->validated();
        $cart = $this->cartService->addItem($itemData);
        return (new CartResource($cart))->response();
    }

    // Удаление товара из корзины
    public function removeItem(Product $product): JsonResponse
    {
        $cart = $this->cartService->removeItem($product);

        return (new CartResource($cart))->response();
    }

    // Получение текущей корзины
    public function show(): JsonResponse
    {
        $cart = Cart::getForUser(Auth::user());

        return (new CartResource($cart))->response();
    }

}
