<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function getProducts(): JsonResponse
    {
        return new JsonResponse(['data' => Product::all()], 200);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function getProduct(string $id): JsonResponse
    {
        $product = Product::find($id);
        
        if (!$product) {
            return new JsonResponse(['data' => ['error' => 'Not found product by id ' . $id]], 404);
        }
        
        return new JsonResponse(['data' => $product], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createProduct(ProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        
        return new JsonResponse(['data' => $product], 201);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function updateProduct(ProductRequest $request, string $id): JsonResponse
    {
        $product = Product::find($id);
        
        if (!$product) {
            return new JsonResponse(['data' => ['error' => 'Not found product by id ' . $id]], 404);
        }
        
        $product->update($request->validated());
        
        return new JsonResponse(['data' => $product->fresh()], 200);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function deleteProduct(string $id): JsonResponse
    {
        $product = Product::find($id);
        
        if (!$product) {
            return new JsonResponse(['data' => ['error' => 'Not found product by id ' . $id]], 404);
        }
        
        $product->delete();
        
        return new JsonResponse(null, 204);
    }
}