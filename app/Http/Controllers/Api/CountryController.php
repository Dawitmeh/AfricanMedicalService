<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
            $request->validate([
                'name' => 'required',
                'code' => 'required',
                'flag' => 'required|image|mimes:jpg,jpeg,png,svg|max:2048'
            ]);

            // Handle image upload
            if ($request->hasFile('flag')) {
                $flagPath = $request->file('flag')->store('flags', 'public'); // stores in storage/app/public/flags
            }

            // Save the data including image path
            $country = Country::create([
                'name' => $request->input('name'),
                'code' => $request->input('code'),
                'flag' => $flagPath ?? null,
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
                'flag' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            ]);

            // Find the country
            $country = Country::findOrFail($id);

            // Handle flag upload if present
            if ($request->hasFile('flag')) {
                // Delete the old image if it exists
                if ($country->flag && Storage::disk('public')->exists($country->flag)) {
                    Storage::disk('public')->delete($country->flag);
                }

                // Store the new image
                $flagPath = $request->file('flag')->store('flags', 'public');
                $validatedData['flag'] = $flagPath;
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
}
