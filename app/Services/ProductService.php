<?php

namespace App\Services;

use App\Contracts\Services\ProductServiceInterface;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductService implements ProductServiceInterface
{
    public function getAllProducts(): Collection
    {
        return Product::all();
    }

    public function getProductsByCategory(int $id): Collection
    {
        $category = Category::findOrFail($id);
        return $category->products;
    }

    public function getProductById(int $id): Product
    {
        return Product::findOrFail($id);
    }

    public function createProduct(array $data): Product
    {
        return Product::create($data);
    }

    public function updateProduct(int $id, array $data): Product
    {

        $product = Product::findOrFail($id);
        $product->update($data);
        return $product;
    }

    public function deleteProduct(int $id): bool
    {
        return Product::findOrFail($id)->delete();
    }
}
