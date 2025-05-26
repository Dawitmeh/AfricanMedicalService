<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Exception;
use Illuminate\Http\Request;
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
            $request->validate([
                'country_id' => 'required|exists:countries,id',
                'name' => 'required|string',
                'location' => 'required',
                'description' => 'required',
                'image' => 'required|image|mimes:jpg,jpeg,png,svg|max:2048'
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('hospitals', 'public');
            }

            $hospital = Hospital::create([
                'country_id' => $request->input('country_id'),
                'name' => $request->input('name'),
                'location' => $request->input('location'),
                'description' => $request->input('description'),
                'image' => $imagePath ?? null,
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
                'image' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048'
            ]);

            $hospital = Hospital::findOrFail($id);

            // Handle image
            if ($request->hasFile('image')) {
                // Remove the old image
                if ($hospital->image && Storage::disk('public')->exists($hospital->image)) {
                    Storage::disk('public')->delete($hospital->image);
                }

                // Store the new image
                $imagePath = $request->file('image')->store('hospitals', 'public');
                $validatedData['image'] = $imagePath;
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
}
