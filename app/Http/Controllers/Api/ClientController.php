<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $clients = Client::with('user')->get();

            $clients->transform(function ($client) {
                $client->image = asset('storage/' . $client->image);
                return $client;
            });

            return response()->json([
                'data' => $clients
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
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:clients',
                'phone' => 'required|max:15|unique:clients',
                'password' => 'required|string|confirmed',
                'type_id' => 'nullable|exists:client_types,id',
                'age' => 'required',
                'image' => 'nullable|string',
                'country_code' => 'required',
            ], [
                'email.unique' => 'The email has already been taken',
                'phone.unique' => 'The phone has already been taken',
                'password.confirmed' => 'The password confirmation does not match'
            ]);

            $rawPhone = ltrim($request->phone, '0');
            $phone = $request->country_code . $rawPhone;

            // Create the user first
            $user = User::create([
                'name' => $request->first_name,
                'email' => $request->email,
                'phone' => $phone,
                'userType' => 'client',
                'password' => Hash::make($request->password)
            ]);

            if (isset($data['image'])) {
                $relativePath = $this->saveImage($data['image']);
                $data['image'] = $relativePath;
            }

            $client = Client::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $phone,
                'country_code' => $request->country_code,
                'age' => $request->age,
                'image' => $data['image'],
                'type_id' => $request->type_id,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'Message' => 'Registration successful',
                'data' => $client,
                'user' => $user
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
            $client = Client::with('user')->where('id', $id)->first();

            if (!$client) {
                return response()->json([
                    'error' => 'Client not found'
                ], 404);
            }

            $client->image = asset('storage/' . $client->image);

            return response()->json([
                'data' => $client
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
            $client = Client::findOrFail($id);
            $user = User::findOrFail($client->user_id);

            // Validation
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'required|max:15|unique:users,phone,' . $user->id,
                'password' => 'required|string|confirmed',
                'type_id' => 'nullable|exists:client_types,id',
                'age' => 'required',
                'image' => 'nullable|string',
                'country_code' => 'required',
            ], [
                'email.unique' => 'The email has already been taken',
                'phone.unique' => 'The phone has already been taken',
                'password.confirmed' => 'The password confirmation does not match'
            ]);

            $rawPhone = ltrim($request->phone, '0');
            $phone = $request->country_code . $rawPhone;

            $user->update([
                'name' => $request->first_name,
                'email' => $request->email,
                'phone' => $phone,
                'password' => $request->filled('password') ? Hash::make($request->password) : $user->password
            ]);

            // Handle image
            if (isset($validatedData['image'])) {
                $relativePath = $this->saveImage($validatedData['image']);
                $validatedData['image'] = $relativePath;

                if ($client->image) {
                    $absolutePath = public_path($client->image);
                    File::delete($absolutePath);
                }

                $client->update([
                    'first_name' => $request->first_name,
                    'user_id' => $user->id,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'country_code' => $request->country_code,
                    'age' => $request->age,
                    'image' => $data['image'] ?? $client->image,
                    'type_id' => $request->type_id,
                    'password' => $request->filled('password') ? Hash::make($request->password) : $client->password
                ]);

                return response()->json([
                    'data' => $client,
                    'user' => $user
                ], 200);
            }
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
            $client = Client::findOrFail($id);
            $client->delete();

            return response()->json([
                'message' => 'Client deleted successfully!'
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
        $relativePath = 'clients/' . $fileName;
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
