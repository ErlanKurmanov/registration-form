<?php

namespace App\Repositories;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Models\User;

class CartRepository implements CartRepositoryInterface
{
    public function getForUser(User $user)
    {
        return $user->cart()->firstOrCreate();
    }
}
