<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Fleet;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FleetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $fleets = Fleet::with('country')->get();

            $fleets->transform(function ($fleet) {
                $fleet->icon = asset('storage/' . $fleet->icon);
                return $fleet;
            });

            return response()->json([
                'data' => $fleets
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
                'name' => 'required|string|max:255',
                'capacity' => 'required|numeric',
                'classification' => 'required',
                'icon' => 'required|string',
                'description' => 'required|string'
            ]);

            if (isset($data['icon'])) {
                $relativePath = $this->saveImage($data['icon']);
                $data['icon'] = $relativePath;
            }

            $fleet = Fleet::create([
                'country_id' => $request->input('country_id'),
                'name' => $request->input('name'),
                'capacity' => $request->input('capacity'),
                'classification' => $request->input('classification'),
                'icon' => $data['icon'],
                'description' => $request->input('description'),
                'Available' => $request->input('Available'),
                'Active' => $request->input('Active')
            ]);

            return response()->json([
                'data' => $fleet
            ], 200);

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
            $fleet = Fleet::with('country')->where('id', $id)->first();

            if (!$fleet) {
                return response()->json([
                    'error' => 'Fleet not found'
                ], 404);
            }

            $fleet->icon = asset('storage/' . $fleet->icon);

            return response()->json([
                'data' => $fleet
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
                'name' => 'required|string|max:255',
                'capacity' => 'required',
                'classification' => 'required',
                'icon' => 'nullable|string',
                'description' => 'required|string',
                'Active' => 'nullable',
                'Available' => 'nullable'
            ]);

            $fleet = Fleet::findOrFail($id);

            if (isset($validatedData['icon'])) {
                $relativePath = $this->saveImage($validatedData['icon']);
                $validatedData['icon'] = $relativePath;

                if ($fleet->icon) {
                    $absolutePath = public_path($fleet->icon);
                    File::delete($absolutePath);
                }
            }

            $fleet->update($validatedData);

            return response()->json([
                'data' => $fleet
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
            $fleet = Fleet::findOrFail($id);
            $fleet->delete();

            return response()->json([
                'message' => 'Fleet deleted !'
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
        $relativePath = 'fleets/' . $fileName;
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
