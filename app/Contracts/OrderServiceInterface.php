<?php

namespace App\Contracts;

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection; // Для передачи данных корзины

interface OrderServiceInterface
{
    public function createOrderFromCart(User $user, Cart $cart, array $orderData): Order;

}
