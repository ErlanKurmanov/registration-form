<?php

namespace App\Services;

use App\Contracts\Services\FavoriteServiceInterface;
use App\Models\Product;
use App\Models\User;

class FavoriteService implements FavoriteServiceInterface
{

    public function add(Product $product, User $user): bool
    {

            if (!$user->favoriteItems()->where('product_id', $product->id)->exists()) {
                $user->favoriteItems()->attach($product->id);
                return true;
            }
            return false;
    }

    public function removeFavorite(Product $product, User $user): bool
    {
        try {
            if($user->favoriteItems()->where('product_id', $product->id)->exists()) {
                $user->favoriteItems()->detach($product->id);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            logger()->error('Failed to remove favorite: ' . $e->getMessage());
            throw $e;
        }

    }

    public function getUserFavorites()
    {
        // TODO: Implement getUserFavorites() method.
    }
}
