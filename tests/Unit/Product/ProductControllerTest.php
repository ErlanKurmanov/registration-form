<?php

namespace Tests\Unit\Product;

use App\Contracts\Services\ProductServiceInterface;
use App\Http\Controllers\ProductController;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    protected $productService;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the product service
        $this->productService = Mockery::mock(ProductServiceInterface::class);
        $this->controller = new ProductController($this->productService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testIndex()
    {
        // Create test data
        $products = Collection::make([
            new Product(['id' => 1, 'name' => 'Test Product 1', 'category_id' => 1, 'price' => 10.99]),
            new Product(['id' => 2, 'name' => 'Test Product 2', 'category_id' => 2, 'price' => 20.99])
        ]);

        // Set up expectations for the mock
        $this->productService->shouldReceive('getAllProducts')
            ->once()
            ->andReturn($products);

        // Call the controller method
        $response = $this->controller->index();

        // Assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        // Get the response data
        $responseData = json_decode($response->getContent(), true);

        // Assert the response structure
        $this->assertArrayHasKey('data', $responseData);
        $this->assertCount(2, $responseData['data']);
        $this->assertEquals(1, $responseData['data'][0]['id']);
        $this->assertEquals('Test Product 1', $responseData['data'][0]['name']);
        $this->assertEquals(10.99, $responseData['data'][0]['price']);
    }

    public function testShow()
    {
        // Create test data
        $product = new Product([
            'id' => 1,
            'name' => 'Test Product',
            'category_id' => 1,
            'price' => 10.99
        ]);

        // Set up expectations for the mock
        $this->productService->shouldReceive('getProductById')
            ->once()
            ->with(1)
            ->andReturn($product);

        // Call the controller method
        $response = $this->controller->show(1);

        // Assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        // Get the response data
        $responseData = json_decode($response->getContent(), true);

        // Assert the response structure
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals(1, $responseData['data']['id']);
        $this->assertEquals('Test Product', $responseData['data']['name']);
    }

    public function testShowNotFound()
    {
        // Set up expectations for the mock
        $this->productService->shouldReceive('getProductById')
            ->once()
            ->with(999)
            ->andThrow(new ModelNotFoundException());

        // Call the controller method
        $response = $this->controller->show(999);

        // Assert response status code
        $this->assertEquals(404, $response->getStatusCode());

        // Get the response data
        $responseData = json_decode($response->getContent(), true);

        // Assert the response message
        $this->assertEquals('Product not found.', $responseData['message']);
    }

    public function testByCategory()
    {
        // Create test data
        $products = Collection::make([
            new Product(['id' => 1, 'name' => 'Test Product 1', 'category_id' => 1, 'price' => 10.99]),
            new Product(['id' => 2, 'name' => 'Test Product 2', 'category_id' => 1, 'price' => 20.99])
        ]);

        // Set up expectations for the mock
        $this->productService->shouldReceive('getProductsByCategory')
            ->once()
            ->with(1)
            ->andReturn($products);

        // Call the controller method
        $response = $this->controller->byCategory(1);

        // Assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        // Get the response data
        $responseData = json_decode($response->getContent(), true);

        // Assert the response structure
        $this->assertArrayHasKey('data', $responseData);
        $this->assertCount(2, $responseData['data']);
        $this->assertEquals(1, $responseData['data'][0]['id']);
        $this->assertEquals(1, $responseData['data'][0]['category_id']);
    }

    public function testByCategoryNotFound()
    {
        // Set up expectations for the mock
        $this->productService->shouldReceive('getProductsByCategory')
            ->once()
            ->with(999)
            ->andThrow(new ModelNotFoundException());

        // Call the controller method
        $response = $this->controller->byCategory(999);

        // Assert response status code
        $this->assertEquals(404, $response->getStatusCode());

        // Get the response data
        $responseData = json_decode($response->getContent(), true);

        // Assert the response message
        $this->assertEquals('Category not found.', $responseData['message']);
    }

    public function testStore()
    {
        // Create test data
        $productData = [
            'name' => 'New Product',
            'category_id' => 1,
            'description' => 'Product description',
            'price' => 15.99,
            'is_available' => true
        ];

        $product = new Product(array_merge(['id' => 1], $productData));

        // Create request mock
        $request = Mockery::mock(CreateProductRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn($productData);

        // Set up expectations for the product service
        $this->productService->shouldReceive('createProduct')
            ->once()
            ->with($productData)
            ->andReturn($product);

        // Call the controller method
        $response = $this->controller->store($request);

        // Assert response status code
        $this->assertEquals(201, $response->getStatusCode());

        // Get the response data
        $responseData = json_decode($response->getContent(), true);

        // Assert the response structure
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals(1, $responseData['data']['id']);
        $this->assertEquals('New Product', $responseData['data']['name']);
    }

    public function testUpdate()
    {
        // Create test data
        $updateData = [
            'name' => 'Updated Product',
            'price' => 25.99
        ];

        $product = new Product([
            'id' => 1,
            'name' => 'Updated Product',
            'category_id' => 1,
            'price' => 25.99
        ]);

        // Create request mock
        $request = Mockery::mock(UpdateProductRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn($updateData);

        // Set up expectations for the product service
        $this->productService->shouldReceive('updateProduct')
            ->once()
            ->with(1, $updateData)
            ->andReturn($product);

        // Call the controller method
        $response = $this->controller->update($request, 1);

        // Assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        // Get the response data
        $responseData = json_decode($response->getContent(), true);

        // Assert the response structure
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals(1, $responseData['data']['id']);
        $this->assertEquals('Updated Product', $responseData['data']['name']);
        $this->assertEquals(25.99, $responseData['data']['price']);
    }

    public function testUpdateNotFound()
    {
        // Create test data
        $updateData = [
            'name' => 'Updated Product',
            'price' => 25.99
        ];

        // Create request mock
        $request = Mockery::mock(UpdateProductRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn($updateData);

        // Set up expectations for the product service
        $this->productService->shouldReceive('updateProduct')
            ->once()
            ->with(999, $updateData)
            ->andThrow(new ModelNotFoundException());

        // Call the controller method
        $response = $this->controller->update($request, 999);

        // Assert response status code
        $this->assertEquals(404, $response->getStatusCode());

        // Get the response data
        $responseData = json_decode($response->getContent(), true);

        // Assert the response message
        $this->assertEquals('Product not found.', $responseData['message']);
    }

    public function testDestroy()
    {
        // Set up expectations for the product service
        $this->productService->shouldReceive('deleteProduct')
            ->once()
            ->with(1)
            ->andReturn(true);

        // Call the controller method
        $response = $this->controller->destroy(1);

        // Assert response status code
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testDestroyNotFound()
    {
        // Set up expectations for the product service
        $this->productService->shouldReceive('deleteProduct')
            ->once()
            ->with(999)
            ->andThrow(new ModelNotFoundException());

        // Call the controller method
        $response = $this->controller->destroy(999);

        // Assert response status code
        $this->assertEquals(404, $response->getStatusCode());

        // Get the response data
        $responseData = json_decode($response->getContent(), true);

        // Assert the response message
        $this->assertEquals('Product not found.', $responseData['message']);
    }

    public function testDestroyFailed()
    {
        // Set up expectations for the product service
        $this->productService->shouldReceive('deleteProduct')
            ->once()
            ->with(1)
            ->andReturn(false);

        // Call the controller method
        $response = $this->controller->destroy(1);

        // Assert response status code
        $this->assertEquals(500, $response->getStatusCode());

        // Get the response data
        $responseData = json_decode($response->getContent(), true);

        // Assert the response message
        $this->assertEquals('Failed to delete product.', $responseData['message']);
    }
}
