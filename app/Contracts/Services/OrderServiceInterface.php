<?php

namespace App\Contracts\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;

// Для передачи данных корзины

interface OrderServiceInterface
{
    public function createOrderFromCart(User $user, Cart $cart, array $orderData): Order;

}
