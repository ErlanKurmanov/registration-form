<?php

namespace Tests\Unit\Product;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProductRequestsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testCreateProductRequestValidation()
    {
        // Create a category for testing
        $category = Category::factory()->create();

        // Create a new instance of the form request
        $request = new CreateProductRequest();

        // Valid data
        $validData = [
            'category_id' => $category->id,
            'name' => 'Test Product',
            'description' => 'Test description',
            'price' => 19.99,
            'is_available' => true
        ];

        // Test valid data
        $validator = Validator::make($validData, $request->rules());
        $this->assertTrue($validator->passes());

        // Test required fields
        $invalidData = [
            // Missing category_id
            'name' => 'Test Product',
            'price' => 19.99
        ];
        $validator = Validator::make($invalidData, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());

        // Test invalid category_id
        $invalidData = [
            'category_id' => 999, // Non-existent ID
            'name' => 'Test Product',
            'description' => 'Test description',
            'price' => 19.99
        ];
        $validator = Validator::make($invalidData, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());

        // Test invalid price format
        $invalidData = [
            'category_id' => $category->id,
            'name' => 'Test Product',
            'price' => 'not-a-number'
        ];
        $validator = Validator::make($invalidData, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('price', $validator->errors()->toArray());

        // Test negative price
        $invalidData = [
            'category_id' => $category->id,
            'name' => 'Test Product',
            'price' => -10.00
        ];
        $validator = Validator::make($invalidData, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('price', $validator->errors()->toArray());

        // Test name too long
        $invalidData = [
            'category_id' => $category->id,
            'name' => str_repeat('a', 300), // Creates a string longer than 255 chars
            'price' => 19.99
        ];
        $validator = Validator::make($invalidData, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function testUpdateProductRequestValidation()
    {
        // Create a category for testing
        $category = Category::factory()->create();

        // Create a new instance of the form request
        $request = new UpdateProductRequest();

        // Test valid data (all fields are optional in update)
        $validData = [
            'category_id' => $category->id,
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 29.99,
            'is_available' => false
        ];
        $validator = Validator::make($validData, $request->rules());
        $this->assertTrue($validator->passes());

        // Test valid partial data
        $validPartialData = [
            'name' => 'Updated Product'
        ];
        $validator = Validator::make($validPartialData, $request->rules());
        $this->assertTrue($validator->passes());

        // Test invalid category_id
        $invalidData = [
            'category_id' => 999 // Non-existent ID
        ];
        $validator = Validator::make($invalidData, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());

        // Test invalid price
        $invalidData = [
            'price' => 'not-a-number'
        ];
        $validator = Validator::make($invalidData, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('price', $validator->errors()->toArray());

        // Test negative price
        $invalidData = [
            'price' => -10.00
        ];
        $validator = Validator::make($invalidData, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('price', $validator->errors()->toArray());

        // Test name too long
        $invalidData = [
            'name' => str_repeat('a', 300) // Creates a string longer than 255 chars
        ];
        $validator = Validator::make($invalidData, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function testCreateProductRequestAuthorization()
    {
        $request = new CreateProductRequest();
        $this->assertTrue($request->authorize());
    }

    public function testUpdateProductRequestAuthorization()
    {
        $request = new UpdateProductRequest();
        $this->assertTrue($request->authorize());
    }

    public function testCreateProductRequestMessages()
    {
        $request = new CreateProductRequest();
        $messages = $request->messages();

        // Assert that custom messages exist for key validation rules
        $this->assertArrayHasKey('category_id.required', $messages);
        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('price.required', $messages);
        $this->assertArrayHasKey('price.min', $messages);
    }
}
