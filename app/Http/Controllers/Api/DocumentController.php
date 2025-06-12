<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Document;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $documents = Document::with('client', 'documentType')->get();

            $documents->transform(function ($document) {
                $document->document = asset('storage/' . $document->document);
                return $document;
            });

            return response()->json([
                'data' => $documents
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
            // Retrieve the selected document type
            $documentType = DocumentType::findOrFail($request->input('type_id'));

            // Size in bytes (min/max from KB to bytes)
            $minSize = $documentType->min_size * 1024;
            $maxSize = $documentType->max_size * 1024;

            // Parse dimension (e.g., "800x600")
            if (!preg_match('/^\d+x\d+$/', $documentType->dimension)) {
                return response()->json(['error' => 'Invalid dimension format in document type.'], 422);
            }

            [$width, $height] = explode('x', $documentType->dimension);

            // Validation rules
            $rules = [
                'client_id' => 'required|exists:clients,id',
                'type_id' => 'required|exists:document_types,id',
                'document' => [
                    'required',
                    'file',
                    'mimes:' . strtolower($documentType->format),
                    'min:' . $minSize,
                    'max:' . $maxSize,
                    'dimensions:width=' . $width . ',height=' . $height,
                ],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Store the uploaded file
            $path = $request->file('document')->store('documents');

            // Create document record
            $doc = Document::create([
                'client_id' => $request->client_id,
                'type_id' => $request->type_id,
                'document' => $path,
            ]);

            return response()->json(['data' => $doc], 201);

        } catch (Exception $ex) {
            Log::error('Document store failed: ' . $ex->getMessage());

            return response()->json([
                'error' => 'Failed to upload document.',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $document = Document::with('client', 'documentType')->where('id', $id)->first();

            if (!$document) {
                return response()->json([
                    'error' => 'Document not found'
                ], 404);
            }

            $document->document = asset('storage/' . $document->document);

            return response()->json([
                'data' => $document
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
    public function update(Request $request, $id)
    {
        try {
            $document = Document::findOrFail($id);

            // Get document type (can also be changed during update)
            $documentType = DocumentType::findOrFail($request->input('type_id', $document->type_id));

            // Size in bytes
            $minSize = $documentType->min_size * 1024;
            $maxSize = $documentType->max_size * 1024;

            // Parse and validate dimension
            if (!preg_match('/^\d+x\d+$/', $documentType->dimension)) {
                return response()->json(['error' => 'Invalid dimension format in document type.'], 422);
            }

            [$width, $height] = explode('x', $documentType->dimension);

            $rules = [
                'client_id' => 'required|exists:clients,id',
                'type_id' => 'required|exists:document_types,id',
            ];

            if ($request->hasFile('document')) {
                $rules['document'] = [
                    'file',
                    'mimes:' . strtolower($documentType->format),
                    'min:' . $minSize,
                    'max:' . $maxSize,
                    'dimensions:width=' . $width . ',height=' . $height,
                ];
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Update file if a new one is uploaded
            if ($request->hasFile('document')) {
                // Delete old file if exists
                if ($document->document && Storage::exists($document->document)) {
                    Storage::delete($document->document);
                }

                // Store new file
                $path = $request->file('document')->store('documents');
                $document->document = $path;
            }

            // Update other fields
            $document->client_id = $request->client_id;
            $document->type_id = $request->type_id;
            $document->save();

            return response()->json(['data' => $document], 200);

        } catch (Exception $ex) {
            Log::error('Document update failed: ' . $ex->getMessage());

            return response()->json([
                'error' => 'Failed to update document.',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $document = Document::findOrFail($id);
            $document->delete();

            return response()->json([
                'data' => 'Document deleted successfully'
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
