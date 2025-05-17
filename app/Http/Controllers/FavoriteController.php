<?php

namespace App\Http\Controllers;

use App\Contracts\Services\FavoriteServiceInterface;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function __construct(private FavoriteServiceInterface $favoriteService)
    {
    }

    public function addFavorite(Product $product): JsonResponse
    {
        try {
            $this->favoriteService->add($product, Auth::user());
            return response()->json(['message' => 'Added to favorites']);
        } catch (\Exception $e) {
            logger()->error('Failed to add favorite: ' . $e->getMessage());
            throw $e;
        }
    }

    public function removeFavorite(Product $product)
    {
        $this->favoriteService->removeFavorite($product, Auth::user());
        return response()->json([
            'message' => 'Этот товар уже удален из избранного'
        ]);
    }

    public function getUserFavorites()
    {
        $favoriteItems = Auth::user()->favoriteItems()->get();
        return response()->json($favoriteItems);
    }

}
