<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ProductPackage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
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

            $packages->transform(function ($package) {
                $package->image = asset('storage/' . $package->image);
                return $package;
            });

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
            $data = $request->validate([
                'product_id' => 'required|exists:products,id',
                'name' => 'required|string|max:255',
                'total' => 'nullable',
                'discount' => 'nullable',
                'description' => 'required|string',
                'image' => 'required|string'
            ]);

            if (isset($data['image'])) {
                $relativePath = $this->saveImage($data['image']);
                $data['image'] = $relativePath;
            }

            $package = ProductPackage::create([
                'product_id' => $request->input('product_id'),
                'name' => $request->input('name'),
                'total' => $request->input('total'),
                'discount' => $request->input('discount'),
                'description' => $request->input('description'),
                'image' => $data['image']
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

            if (!$package) {
                return response()->json([
                    'error' => 'Package not found'
                ], 404);
            }

            $package->image = asset('storage/' . $package->image);

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
                'image' => 'required|string'
            ]);

            $package = ProductPackage::findOrFail($id);

            // if (isset($validatedData['image'])) {
            //     $relativePath = $this->saveImage($validatedData['image']);
            //     $validatedData['image'] = $relativePath;

            //     if ($package->image) {
            //         $absolutePath = public_path($package->image);
            //         File::delete($absolutePath);
            //     }
            // }
            if (isset($validatedData['image']) && Str::startsWith($validatedData['image'], 'data:image')) {
                $relativePath = $this->saveImage($validatedData['image']);
                $validatedData['image'] = $relativePath;

                if ($package->image) {
                    $absolutePath = public_path('storage/' . $package->image);
                    File::delete($absolutePath);
                }
            } else {
                unset($validatedData['image']);
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
        $relativePath = 'packages/' . $fileName;
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
