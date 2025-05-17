<?php

namespace Tests\Unit\Product;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = new ProductService();
    }

    public function testGetAllProducts()
    {
        // Create test products
        Product::factory()->count(3)->create();

        // Call the service method
        $products = $this->productService->getAllProducts();

        // Assert that we got all products
        $this->assertCount(3, $products);
        $this->assertInstanceOf(Product::class, $products->first());
    }

    public function testGetProductById()
    {
        // Create a test product
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 9.99
        ]);

        // Call the service method
        $foundProduct = $this->productService->getProductById($product->id);

        // Assert that we found the correct product
        $this->assertEquals($product->id, $foundProduct->id);
        $this->assertEquals('Test Product', $foundProduct->name);
        $this->assertEquals(9.99, $foundProduct->price);
    }

    public function testGetProductByIdNotFound()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->productService->getProductById(999);
    }

    public function testGetProductsByCategory()
    {
        // Create a test category
        $category = Category::factory()->create();

        // Create products in that category
        Product::factory()->count(2)->create([
            'category_id' => $category->id
        ]);

        // Create a product in a different category
        Product::factory()->create();

        // Call the service method
        $products = $this->productService->getProductsByCategory($category->id);

        // Assert that we got only products from the correct category
        $this->assertCount(2, $products);
        $this->assertEquals($category->id, $products->first()->category_id);
    }

    public function testGetProductsByCategoryNotFound()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->productService->getProductsByCategory(999);
    }

    public function testCreateProduct()
    {
        // Create a test category
        $category = Category::factory()->create();

        // Prepare data for a new product
        $productData = [
            'name' => 'New Test Product',
            'category_id' => $category->id,
            'description' => 'Product description',
            'price' => 19.99,
            'is_available' => true
        ];

        // Call the service method
        $product = $this->productService->createProduct($productData);

        // Assert that the product was created with correct data
        $this->assertEquals('New Test Product', $product->name);
        $this->assertEquals($category->id, $product->category_id);
        $this->assertEquals(19.99, $product->price);
        $this->assertEquals('Product description', $product->description);
        $this->assertTrue($product->is_available);

        // Assert that the product exists in the database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Test Product'
        ]);
    }

    public function testUpdateProduct()
    {
        // Create a test product
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price' => 9.99
        ]);

        // Prepare update data
        $updateData = [
            'name' => 'Updated Name',
            'price' => 19.99
        ];

        // Call the service method
        $updatedProduct = $this->productService->updateProduct($product->id, $updateData);

        // Assert that the product was updated with correct data
        $this->assertEquals($product->id, $updatedProduct->id);
        $this->assertEquals('Updated Name', $updatedProduct->name);
        $this->assertEquals(19.99, $updatedProduct->price);

        // Assert that the product was updated in the database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 19.99
        ]);
    }

    public function testUpdateProductNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        $updateData = [
            'name' => 'Updated Name',
            'price' => 19.99
        ];

        $this->productService->updateProduct(999, $updateData);
    }

    public function testDeleteProduct()
    {
        // Create a test product
        $product = Product::factory()->create();

        // Verify product exists in database
        $this->assertDatabaseHas('products', ['id' => $product->id]);

        // Call the service method
        $result = $this->productService->deleteProduct($product->id);

        // Assert that the method returned true
        $this->assertTrue($result);

        // Assert that the product was deleted from the database
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function testDeleteProductNotFound()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->productService->deleteProduct(999);
    }
}
