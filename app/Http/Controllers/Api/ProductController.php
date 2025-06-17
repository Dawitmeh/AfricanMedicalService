<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $products = Product::with('currency', 'package')->get();

            $products->transform(function ($product) {
                $product->image = asset('storage/' . $product->image);
                return $product;
            }); 

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
            $data = $request->validate([
                'currency_id' => 'required|exists:currencies,id',
                'name' => 'required|string',
                'cost' => 'nullable',
                'price' => 'nullable',
                'image' => 'required|string',
                'description' => 'required|string',
                'active' => 'nullable'
            ]);

            if (isset($data['image'])) {
                $relativePath = $this->saveImage($data['image']);
                $data['image'] = $relativePath;
            }

            $product = Product::create([
                'currency_id' => $request->input('currency_id'),
                'name' => $request->input('name'),
                'cost' => $request->input('cost'),
                'price' => $request->input('price'),
                'description' => $request->input('description'),
                'image' => $data['image'],
                'active' => $request->input('active')
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
            $product = Product::with('currency', 'package')->where('id', $id)->first();

            if (!$product) {
                return response()->json([
                    'error' => 'Product not found'
                ], 404);
            }

            $product->image = asset('storage/' . $product->image);

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
                'image' => 'nullable|string',
                'description' => 'required|string',
                'active' => 'nullable'
            ]);

            $product = Product::findOrFail($id);

            // if (isset($validatedData['image'])) {
            //     $relativePath = $this->saveImage($validatedData['image']);
            //     $validatedData['image'] = $relativePath;

            //     if ($product->image) {
            //         $absolutePath = public_path($product->image);
            //         File::delete($absolutePath);
            //     }
            // }
            if (isset($validatedData['image']) && Str::startsWith($validatedData['image'], 'data:image')) {
                $relativePath = $this->saveImage($validatedData['image']);
                $validatedData['image'] = $relativePath;

                if ($product->image) {
                    $absolutePath = public_path('storage/' . $product->image);
                    File::delete($absolutePath);
                }
            } else {
                unset($validatedData['image']);
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

    private function saveImage($image) 
    {
        // check if image is valid base64 string
        if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {

            $image = substr($image, strpos($image, ',') + 1);

            // get file extension
            $type = strtolower($type[1]); // jpg, png, gif

            // check if file is an image
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception(('invalid image type'));
            }
            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }
        
        // correct path: storage/app/public/hospitals
        $fileName = Str::random() . '.' . $type;
        $relativePath = 'products/' . $fileName;
        $storagePath = storage_path('app/public/' . $relativePath);

        // make sure the directory exists
        if (!File::exists(dirname($storagePath))) {
            File::makeDirectory(dirname($storagePath), 0755, true);
        }

        file_put_contents($storagePath, $image);

        // return 'storage/' . $relativePath;
        return $relativePath;
    }
}
