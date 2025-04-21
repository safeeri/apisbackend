<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
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
    $validator = Validator::make($request->all(), [
        'title' => 'required|string',
        'description' => 'required|string',
        'price' => 'required|numeric',
        'image' => 'required|image|max:2048',
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

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
        $product = Product::find($id);
    
        if (!$product) {
            return response()->json([
                'message' => 'Product not found!',
            ], 404);
        }
    
        $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'image' => 'nullable|image|max:2048',
        ]);
    
        // Handle image upload
        $imagePath = $product->image; // Keep the existing image path
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }
    
        // Update the product record
        $product->update([
            'title' => $request->input('title', $product->title), // Use existing value if not provided
            'description' => $request->input('description', $product->description),
            'price' => $request->input('price', $product->price),
            'image' => $imagePath,
        ]);
    
        return response()->json([
            'message' => 'Product updated successfully!',
            'product' => $product
        ], 200);
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
