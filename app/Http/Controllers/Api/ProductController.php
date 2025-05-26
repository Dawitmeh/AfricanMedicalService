<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $products = Product::with('currency')->get();

            return response()->json([
                'data' => $products
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'currency_id' => 'required|exists:currencies,id',
                'name' => 'required|string',
                'cost' => 'nullable',
                'price' => 'nullable',
                'image' => 'required|image|mimes:jpg,jpeg,png,svg|max:2048',
                'description' => 'required|string'
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $product = Product::create([
                'currency_id' => $request->input('currency_id'),
                'name' => $request->input('currency'),
                'cost' => $request->input('cost'),
                'price' => $request->input('price'),
                'description' => $request->input('description'),
                'image' => $imagePath ?? null,
            ]);

            return response()->json([
                'data' => $product
            ], 201);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $product = Product::with('currency')->where('id', $id)->first();

            return response()->json([
                'data' => $product
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $validatedData = $request->validate([
                'currency_id' => 'required|exists:currencies,id',
                'name' => 'required|string',
                'cost' => 'nullable',
                'price' => 'nullable',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
                'description' => 'required|string'
            ]);

            $product = Product::findOrFail($id);

            if ($request->hasFile('image')) {

                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                $imagePath = $request->file('image')->store('products', 'public');
                $validatedData['image'] = $imagePath;
            }

            $product->update($validatedData);

            return response()->json([
                'data' => $product
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json([
                'message' => 'Product Deleted!'
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
