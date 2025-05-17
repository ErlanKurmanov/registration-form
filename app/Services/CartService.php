<?php

namespace App\Services;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Services\CartServiceInterface;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartService implements CartServiceInterface
{
    protected CartRepositoryInterface $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function sync(array $guestItems): Cart
    {
        $user = Auth::user();
        $cart = $this->cartRepository->getForUser($user);

        foreach ($guestItems as $guestItem) {
            $this->mergeCartItem($cart, $guestItem);
        }
        return $cart;
    }

    public function addItem($itemData)
    {
        $user = Auth::user();
        $cart = $this->cartRepository->getForUser($user);
        $this->mergeCartItem($cart, $itemData);
        return $cart;
    }

    public function removeItem(Product $product): Cart
    {
        $user = Auth::user();
        $cart = $this->cartRepository->getForUser($user);
        $cart->items()->where('product_id', $product->id)->delete();
        return $cart;
    }
    protected function mergeCartItem(Cart $cart, array $itemData): void
    {
        $existingItem = $cart->items()
            ->where('product_id', $itemData['product_id'])
            ->first();

        if ($existingItem) {
            $existingItem->update(
                [
                    'quantity' => $existingItem->quantity + $itemData['quantity']
                ]
            );
        } else {
            $cart->items()->create($itemData);
        }
    }
}
