<?php

namespace App\Contracts\Services;

use App\Models\Cart;
use App\Models\Product;

interface CartServiceInterface
{
    public function sync(array $guestItems);
    public function addItem($itemData);
    public function removeItem(Product $product);
}
