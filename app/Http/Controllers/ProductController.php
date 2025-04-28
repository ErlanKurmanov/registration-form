<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Contracts\ProductServiceInterface;

use Illuminate\Http\JsonResponse;


class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }


    public function index(): JsonResponse
    {
        $products = $this->productService->getAllProducts();

        return ProductResource::collection($products)
            ->response()
            ->setStatusCode(200);
    }

    public function byCategory(int $id): JsonResponse
    {
        $products = $this->productService->getProductsByCategory($id);

        return ProductResource::collection($products)
            ->response()
            ->setStatusCode(200);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->productService->getProductById($id);

            return (new ProductResource($product))
                ->response()
                ->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }
    }


    public function store(CreateProductRequest $request): JsonResponse
    {

        $data = $request->validated();
        $product = $this->productService->createProduct($data);

        return (new ProductResource($product))->response()->setStatusCode(201);

    }


    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        try {
            $product = $this->productService->updateProduct($id, $data);

            return (new ProductResource($product))
                ->response()
                ->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }
    }



    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->productService->deleteProduct($id);

            if ($deleted) {
                // 204 No Content is a common RESTful response for successful deletion
                return response()->json([
                    'message' => 'Product deleted successfully.',
                ], 204);
            }

            return response()->json([
                'message' => 'Failed to delete product.',
            ], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }
    }
}
