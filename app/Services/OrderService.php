<?php

namespace App\Services;

use App\Contracts\OrderServiceInterface;
use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService implements OrderServiceInterface
{
    /**
     * Создать заказ из корзины пользователя
     */
    public function createOrderFromCart(User $user, Cart $cart, array $orderData): Order
    {
        return DB::transaction(function () use ($user, $cart, $orderData) {
            // Создаем заказ
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $this->calculateCartTotal($cart),
                'status' => OrderStatus::Pending->value,
                'shipping_address' => $orderData['shipping_address'] ?? null,
//                'billing_address' => $orderData['billing_address'] ?? null,
//                'payment_method' => $orderData['payment_method'] ?? 'credit_card',
                'comment' => $orderData['comment'] ?? null,
            ]);

            // Переносим товары из корзины в заказ
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                    'options' => $cartItem->options,
                ]);
            }

            return $order;
        });
    }


    protected function calculateCartTotal(Cart $cart): float
    {
        return $cart->items->reduce(function ($total, $item) {
            return $total + ($item->product->price * $item->quantity);
        }, 0);
    }
}
