<?php

namespace Tests\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication (if needed)
        $this->user = User::factory()->create();

        // You might need to adjust this based on your authentication setup
        // $this->actingAs($this->user);
    }

    public function testGetAllProducts()
    {
        // Create test products
        $products = Product::factory()->count(3)->create();

        // Make the API request
        $response = $this->getJson('/api/products');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'category_id', 'price']
                ]
            ]);
    }

    public function testGetProductById()
    {
        // Create a test product
        $product = Product::factory()->create();

        // Make the API request
        $response = $this->getJson("/api/products/{$product->id}");

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category_id' => $product->category_id,
                    'price' => $product->price
                ]
            ]);
    }

    public function testGetProductByIdNotFound()
    {
        // Make the API request with non-existent ID
        $response = $this->getJson('/api/products/999');

        // Assert response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found.'
            ]);
    }

    public function testGetProductsByCategory()
    {
        // Create a test category
        $category = Category::factory()->create();

        // Create products in that category
        $products = Product::factory()->count(2)->create([
            'category_id' => $category->id
        ]);

        // Create a product in another category
        Product::factory()->create();

        // Make the API request
        $response = $this->getJson("/api/categories/{$category->id}/products");

        // Assert response
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'category_id', 'price']
                ]
            ]);
    }

    public function testGetProductsByCategoryNotFound()
    {
        // Make the API request with non-existent category ID
        $response = $this->getJson('/api/categories/999/products');

        // Assert response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Category not found.'
            ]);
    }

    public function testCreateProduct()
    {
        // Create a test category
        $category = Category::factory()->create();

        // Prepare product data
        $productData = [
            'name' => 'New Test Product',
            'category_id' => $category->id,
            'description' => 'Test product description',
            'price' => 29.99,
            'is_available' => true
        ];

        // Make the API request
        $response = $this->postJson('/api/products', $productData);

        // Assert response
        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'New Test Product',
                    'category_id' => $category->id,
                    'price' => 29.99
                ]
            ]);

        // Assert that the product was created in the database
        $this->assertDatabaseHas('products', [
            'name' => 'New Test Product',
            'price' => 29.99
        ]);
    }

    public function testCreateProductValidationErrors()
    {
        // Make the API request with invalid data
        $response = $this->postJson('/api/products', [
            // Missing required fields
            'description' => 'Test description'
        ]);

        // Assert response
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id', 'name', 'price']);
    }

    public function testUpdateProduct()
    {
        // Create a test product
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price' => 19.99
        ]);

        // Prepare update data
        $updateData = [
            'name' => 'Updated Name',
            'price' => 29.99
        ];

        // Make the API request
        $response = $this->putJson("/api/products/{$product->id}", $updateData);

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'name' => 'Updated Name',
                    'price' => 29.99
                ]
            ]);

        // Assert that the product was updated in the database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 29.99
        ]);
    }

    public function testUpdateProductNotFound()
    {
        // Make the API request with non-existent product ID
        $response = $this->putJson('/api/products/999', [
            'name' => 'Updated Name'
        ]);

        // Assert response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found.'
            ]);
    }

    public function testUpdateProductValidationErrors()
    {
        // Create a test product
        $product = Product::factory()->create();

        // Make the API request with invalid data
        $response = $this->putJson("/api/products/{$product->id}", [
            'price' => 'not-a-number'
        ]);

        // Assert response
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function testDeleteProduct()
    {
        // Create a test product
        $product = Product::factory()->create();

        // Make the API request
        $response = $this->deleteJson("/api/products/{$product->id}");

        // Assert response
        $response->assertStatus(204);

        // Assert that the product was deleted from the database
        $this->assertDatabaseMissing('products', [
            'id' => $product->id
        ]);
    }

    public function testDeleteProductNotFound()
    {
        // Make the API request with non-existent product ID
        $response = $this->deleteJson('/api/products/999');

        // Assert response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found.'
            ]);
    }
}
