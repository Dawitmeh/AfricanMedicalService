<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fleet;
use Exception;
use Illuminate\Http\Request;
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

            $request->validate([
                'country_id' => 'required|exists:countries,id',
                'name' => 'required|string|max:255',
                'capacity' => 'required|numeric',
                'classification' => 'required',
                'icon' => 'required|image|mimes:jpg,jpeg,png,svg|max:2048',
                'description' => 'required|string'
            ]);

            if ($request->hasFile('icon')) {
                $iconPath = $request->file('icon')->store('fleets', 'public');
            }

            $fleet = Fleet::create([
                'country_id' => $request->input('country_id'),
                'name' => $request->input('name'),
                'capacity' => $request->input('capacity'),
                'classification' => $request->input('classification'),
                'icon' => $iconPath ?? null,
                'description' => $request->input('description')
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
                'icon' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
                'description' => 'required|string'
            ]);

            $fleet = Fleet::findOrFail($id);

            if ($request->hasFile('icon')) {

                if ($fleet->icon && Storage::disk('public')->exists($fleet->icon)) {
                    Storage::disk('public')->delete($fleet->icon);
                }

                $iconPath = $request->file('icon')->store('fleets', 'public');
                $validatedData['icon'] = $iconPath;
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
}
