<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Exception;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $types = DocumentType::with('document')->get();

            return response()->json([
                'data' => $types
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
                'name' => 'required|string',
                'format' => 'required|string',
                'min_size' => 'nullable',
                'max_size' => 'nullable',
                'document_prefix' => 'nullable|string',
                'dimension' => 'required',
                'description' => 'required'
            ]);

            $type = DocumentType::create($data);

            return response()->json([
                'data' => $type
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
            $type = DocumentType::with('document')->where('id', $id)->first();

            return response()->json([
                'data' => $type
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
            $request->validate([
                'name' => 'required|string',
                'format' => 'required|string',
                'min_size' => 'nullable',
                'max_size' => 'nullable',
                'document_prefix' => 'nullable|string',
                'dimension' => 'required',
                'description' => 'required'
            ]);

            $type = DocumentType::findOrFail($id);
            $type->update($request->all());

            return response()->json([
                'data' => $type
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
            $type = DocumentType::findOrFail($id);
            $type->delete();

            return response()->json([
                'message' => 'Document type deleted!' 
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
