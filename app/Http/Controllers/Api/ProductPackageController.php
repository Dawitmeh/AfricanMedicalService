<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductPackage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $packages = ProductPackage::with('product')->get();

            return response()->json([
                'data' => $packages
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
                'product_id' => 'required|exists:products,id',
                'name' => 'required|string|max:255',
                'total' => 'nullable',
                'discount' => 'nullable',
                'description' => 'required|string',
                'image' => 'required|image|mimes:jpg,jpeg,png,svg|max:2048'
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('packages', 'public');
            }

            $package = ProductPackage::create([
                'product_id' => $request->input('product_id'),
                'name' => $request->input('name'),
                'total' => $request->input('total'),
                'discount' => $request->input('discount'),
                'description' => $request->input('description'),
                'image' => $imagePath
            ]);

            return response()->json([
                'data' => $package
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
            $package = ProductPackage::with('product')->where('id', $id)->first();

            return response()->json([
                'data' => $package
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
                'product_id' => 'required|exists:products,id',
                'name' => 'required|string|max:255',
                'total' => 'nullable',
                'discount' => 'nullable',
                'description' => 'required|string',
                'image' => 'required|image|mimes:jpg,jpeg,png,svg|max:2048'
            ]);

            $package = ProductPackage::findOrFail($id);

            if ($request->hasFile('image')) {
                if ($package->image && Storage::disk('public')->exists($package->image)) {
                    Storage::disk('public')->delete($package->image);
                }

                $imagePath = $request->file('image')->store('packages', 'public');
                $validatedData['image'] = $imagePath;
            }

            $package->update($validatedData);

            return response()->json([
                'data' => $package
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
            $package = ProductPackage::findOrFail($id);
            $package->delete();

            return response()->json([
                'message' => 'Product Package Deleted!'
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
