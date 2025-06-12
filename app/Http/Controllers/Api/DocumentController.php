<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Document;
use Illuminate\Support\Str;
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
            $documentType = DocumentType::findOrFail($request->input('type_id'));

            $rules = [
                'client_id' => 'required|exists:clients,id',
                'type_id' => 'required|exists:document_types,id',
                'document' => 'required|string', // No longer expecting file, just base64 string
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Handle base64 file
            $base64String = $request->input('document');
            if (preg_match('/^data:(.*?);base64,(.*)$/', $base64String, $matches)) {
                $mimeType = $matches[1];
                $base64Data = base64_decode($matches[2]);
            } else {
                return response()->json(['error' => 'Invalid base64 format.'], 422);
            }

            $expectedMime = [
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
            ][$documentType->format] ?? null;

            if (!$expectedMime || $mimeType !== $expectedMime) {
                return response()->json(['error' => 'File format does not match expected type.'], 422);
            }

            $filename = Str::uuid() . '.' . $documentType->format;
            $path = 'documents/' . $filename;
            // Storage::put($path, $base64Data);
            Storage::disk('public')->put($path, $base64Data);

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

        $rules = [
            'client_id' => 'sometimes|required|exists:clients,id',
            'type_id' => 'sometimes|required|exists:document_types,id',
            'document' => 'sometimes|required|string', // base64 string
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If type_id is being updated, retrieve new DocumentType
        $documentType = $request->has('type_id')
            ? DocumentType::findOrFail($request->input('type_id'))
            : $document->documentType;

        if ($request->has('document')) {
            $base64String = $request->input('document');

            if (preg_match('/^data:(.*?);base64,(.*)$/', $base64String, $matches)) {
                $mimeType = $matches[1];
                $base64Data = base64_decode($matches[2]);
            } else {
                return response()->json(['error' => 'Invalid base64 format.'], 422);
            }

            $expectedMime = [
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
            ][$documentType->format] ?? null;

            if (!$expectedMime || $mimeType !== $expectedMime) {
                return response()->json(['error' => 'File format does not match expected type.'], 422);
            }

            // Delete old file if exists
            if ($document->document && Storage::disk('public')->exists($document->document)) {
                Storage::disk('public')->delete($document->document);
            }

            $filename = Str::uuid() . '.' . $documentType->format;
            $path = 'documents/' . $filename;
            Storage::disk('public')->put($path, $base64Data);

            $document->document = $path;
        }

        // Update other fields if provided
        if ($request->has('client_id')) {
            $document->client_id = $request->client_id;
        }
        if ($request->has('type_id')) {
            $document->type_id = $request->type_id;
        }

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
