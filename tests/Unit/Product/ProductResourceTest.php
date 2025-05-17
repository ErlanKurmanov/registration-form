<?php

namespace Tests\Unit\Product;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class ProductResourceTest extends TestCase
{
    public function testProductResourceTransformation()
    {
        // Create a product model instance
        $product = new Product([
            'id' => 1,
            'name' => 'Test Product',
            'category_id' => 5,
            'description' => 'Test description',
            'price' => 29.99,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Transform the model using the resource
        $resource = new ProductResource($product);
        $result = $resource->toArray(new Request());

        // Assert the structure and values
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Test Product', $result['name']);
        $this->assertEquals(5, $result['category_id']);
        $this->assertEquals(29.99, $result['price']);

        // Assert that the resource doesn't include fields that shouldn't be exposed
        $this->assertArrayNotHasKey('description', $result);
        $this->assertArrayNotHasKey('is_available', $result);
        $this->assertArrayNotHasKey('created_at', $result);
        $this->assertArrayNotHasKey('updated_at', $result);
    }

    public function testProductResourceCollection()
    {
        // Create product model instances
        $product1 = new Product([
            'id' => 1,
            'name' => 'Product 1',
            'category_id' => 5,
            'price' => 19.99
        ]);

        $product2 = new Product([
            'id' => 2,
            'name' => 'Product 2',
            'category_id' => 6,
            'price' => 29.99
        ]);

        // Create a collection of products
        $products = collect([$product1, $product2]);

        // Transform the collection using the resource collection
        $collection = ProductResource::collection($products);
        $result = $collection->toArray(new Request());

        // Assert the structure and values
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        // Check first product
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('Product 1', $result[0]['name']);
        $this->assertEquals(5, $result[0]['category_id']);
        $this->assertEquals(19.99, $result[0]['price']);

        // Check second product
        $this->assertEquals(2, $result[1]['id']);
        $this->assertEquals('Product 2', $result[1]['name']);
        $this->assertEquals(6, $result[1]['category_id']);
        $this->assertEquals(29.99, $result[1]['price']);
    }
}
