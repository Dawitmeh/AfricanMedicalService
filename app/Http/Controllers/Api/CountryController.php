<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Country;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $countries = Country::all();

            // Append full URL for flag
            $countries->transform(function ($country) {
                $country->flag = asset('storage/' . $country->flag);
                return $country;
            });

            return response()->json([
                'data' => $countries
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
                'name' => 'required',
                'code' => 'required',
                'flag' => 'required|string'
            ]);

            // Handle image upload
            if ($data['flag']) {
                $relativePath = $this->saveImage($data['flag']);
                $data['flag'] = $relativePath;
            }

            // Save the data including image path
            $country = Country::create([
                'name' => $request->input('name'),
                'code' => $request->input('code'),
                'flag' => $data['flag'] ?? null,
            ]);

            return response()->json([
                'data' => $country
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
            $country = Country::where('id', $id)->first();

            if (!$country) {
                return response()->json([
                    'error' => 'Country not found'
                ], 404);
            }

            // Add full asset 
            $country->flag = asset('storage/' . $country->flag);

            return response()->json([
                'data' => $country
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
            // Validate input
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:10',
                'flag' => 'nullable|string',
            ]);

            // Find the country
            $country = Country::findOrFail($id);

            // Handle flag upload if present
            if (isset($validatedData['flag'])) {
                $relativePath = $this->saveImage($validatedData['flag']);
                $validatedData['flag'] = $relativePath;

                // if there is an old image, delete it
                if ($country->flag) {
                    $absolutePath = public_path($country->flag);
                    File::delete($absolutePath);
                }
            }
            // Update the country with validated data
            $country->update($validatedData);

            return response()->json([
                'data' => $country
            ], 200);

        } catch (\Exception $ex) {
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
            $country = Country::findOrFail($id);

            $country->delete();

            return response()->json([
                'message' => 'Country deleted'
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
        $relativePath = 'countries/' . $fileName;
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
