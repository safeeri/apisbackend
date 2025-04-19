<?php

namespace App\Http\Controllers;
use App\Models\Product;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all products
        return response()->json(Product::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming data
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
        ]);
    
        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }
    
        // Create the product record
        $product = Product::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
        ]);
    
        return response()->json([
            'message' => 'Product added successfully!',
            'product' => $product
        ], 201);
    }
    
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    // Find the product by ID
    $product = Product::findOrFail($id);

    // Validate incoming data
    $request->validate([
        'title' => 'sometimes|required|string',
        'description' => 'sometimes|required|string',
        'price' => 'sometimes|required|numeric',
        'image' => 'nullable|image|max:2048',
    ]);

    // Handle image upload if new image is provided
    if ($request->hasFile('image')) {
        // Delete the old image if it exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        // Store the new image
        $product->image = $request->file('image')->store('products', 'public');
    }

    // Update product details with the provided fields
    $product->update($request->only('title', 'description', 'price'));

    // Return response with the updated product details
    return response()->json([
        'message' => 'Product updated successfully!',
        'product' => $product
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        // Delete the image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        // Delete product record
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
