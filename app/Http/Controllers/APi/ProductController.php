<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function product(Request $req)
    {
        $user = 2; // Using Auth::id() for cleaner code

        $validatedData = $req->validate([
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|integer',
        ]);

        $validatedData['user_id'] = $user; // Store user ID

        // Create new product
        $data = Product::create($validatedData);

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 201); // 201 Created status code for successful creation
    }

    public function show_product()
    {
        $product = Product::all();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404); // 404 Not Found for missing product
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ], 200); // 200 OK for successful retrieval
    }
    public function edit_product($id) {
        $product = Product::find($id);

        if ($product) {
            return response()->json([
                'data' => $product,
            ]);
        }

        return response()->json([
            'message' => 'Product not found',
        ], 404); // Return 404 if the product is not found
    }

    public function update_product(Request $req, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404); // 404 Not Found for missing product
        }

        $validatedData = $req->validate([
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|integer',
        ]);

        // Update product
        $product->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
        ], 200); // 200 OK for successful update
    }

    public function delete_product($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404); // 404 Not Found for missing product
        }

        // Delete product
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ], 200); // 200 OK for successful deletion
    }
}

