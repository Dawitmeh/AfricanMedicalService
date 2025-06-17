<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Hospital;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class HospitalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $hospitals = Hospital::with('country')->orderByDesc('created_at')->get();

            $hospitals->transform(function ($hospital) {
                $hospital->image = asset('storage/' . $hospital->image);
                return $hospital;
            });

            return response()->json([
                'data' => $hospitals
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
                'country_id' => 'required|exists:countries,id',
                'name' => 'required|string',
                'location' => 'required',
                'description' => 'required',
                'image' => 'required|string'
            ]);

            // if ($request->hasFile('image')) {
            //     $imagePath = $request->file('image')->store('hospitals', 'public');
            // }
            // check if image given and save on local file system
            if (isset($data['image'])) {
                $relativePath = $this->saveImage($data['image']);
                $data['image'] = $relativePath;
            }

            $hospital = Hospital::create([
                'country_id' => $data['country_id'],
                'name' => $data['name'],
                'location' => $request->input('location'),
                'description' => $request->input('description'),
                'image' => $data['image']
            ]);

            return response()->json([
                'data' => $hospital
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
            $hospital = Hospital::with('country')->where('id', $id)->first();

            if (!$hospital) {
                return response()->json([
                    'error' => 'Hospital not found'
                ], 404);
            }

            $hospital->image = asset('storage/' . $hospital->image);

            return response()->json([
                'data' => $hospital
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
                'country_id' => 'required|exists:countries,id',
                'name' => 'required|string',
                'location' => 'required',
                'description' => 'required',
                'image' => 'nullable|string'
            ]);

            $hospital = Hospital::findOrFail($id);

          
            if (isset($validatedData['image']) && Str::startsWith($validatedData['image'], 'data:image')) {
                $relativePath = $this->saveImage($validatedData['image']);
                $validatedData['image'] = $relativePath;

                if ($hospital->image) {
                    $absolutePath = public_path('storage/' . $hospital->image);
                    File::delete($absolutePath);
                }
            }  else {
                unset($validatedData['image']);
            }

            $hospital->update($validatedData);

            return response()->json([
                'data' => $hospital
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $hospital = Hospital::findOrFail($id);
            $hospital->delete();

            return response()->json([
                'message' => 'Hospital Deleted'
            ]);

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
        $relativePath = 'hospitals/' . $fileName;
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
