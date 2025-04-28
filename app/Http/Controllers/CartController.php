<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\SyncCartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Синхронизация гостевой корзины с сервером после авторизации
    public function sync(SyncCartRequest $request): JsonResponse
    {
        $user = Auth::user();
        $guestItems = $request->validated()['items'];

        $cart = Cart::getForUser($user);

        // Объединяем гостевые товары с существующей корзиной
        foreach ($guestItems as $guestItem) {
            $this->mergeCartItem($cart, $guestItem);
        }

        return response()->json([
            'cart' => $this->getCartResponse($cart)
        ]);
    }

    // Добавление/обновление товара в корзине
    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $cart = Cart::getForUser(Auth::user());

        $this->mergeCartItem($cart, $request->validated());

        return response()->json([
            'cart' => $this->getCartResponse($cart)
        ]);
    }

    // Удаление товара из корзины
    public function removeItem(Product $product): JsonResponse
    {
        $cart = Cart::getForUser(Auth::user());

        $cart->items()->where('product_id', $product->id)->delete();

        return response()->json([
            'cart' => $this->getCartResponse($cart)
        ]);
    }

    // Получение текущей корзины
    public function show(): JsonResponse
    {
        $cart = Cart::getForUser(Auth::user());

        return response()->json([
            'cart' => $this->getCartResponse($cart)
        ]);
    }

    protected function mergeCartItem(Cart $cart, array $itemData): void
    {
        $existingItem = $cart->items()
            ->where('product_id', $itemData['product_id'])
            ->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $itemData['quantity']
            ]);
        } else {
            $cart->items()->create($itemData);
        }
    }

    // Формирование ответа с корзиной
    protected function getCartResponse(Cart $cart): array
    {
        return [
            'id' => $cart->id,
            'items' => $cart->items->map(function (CartItem $item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'options' => $item->options,
                    'total' => $item->product->price * $item->quantity,
                ];
            }),
            'total' => $cart->items->sum(function (CartItem $item) {
                return $item->product->price * $item->quantity;
            }),
        ];
    }
}
