<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:clients',
                'phone' => 'required|max:15|unique:clients',
                'password' => 'required|string|confirmed',
                'type_id' => 'nullable|exists:client_types,id',
                'age' => 'required',
                'country_code' => 'required',

            ], [
                'email.unique' => 'The email has already been taken',
                'phone.unique' => 'The phone has already been taken',
                'password.confirmed' => 'The password confirmation does not match'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $rawPhone = ltrim($request->phone, '0');
            $phone = $request->country_code . $rawPhone;

            // Create the user in 'users'
            $user = User::create([
                'name' => $request->first_name,
                'email' => $request->email,
                'phone' => $phone,
                'userType' => 'client',
                'password' => Hash::make($request->password)
            ]);

            // Create the client in client table
            $client = Client::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $phone,
                'country_code' => $request->country_code,
                'age' => $request->age,
                'type_id' => $request->type_id,
                'password' => Hash::make($request->password)
            ]);

            $token = $user->createToken('main')->plainTextToken;

            return response()->json([
                'Message'=> 'Registration successful',
                'client' => $client,
                'user' => $user,
                'token' => $token
            ], 201);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ], [
                'email.required' => 'Email is required',
                'password.required' => 'Password is required'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Attempt to find the user
            $user = User::with('client')->where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid Credentials'
                ], 401);
            }

            // Generate a new token 
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }


    public function adminLogin(Request $request) 
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string'
            ], [
                'email.required' => 'Email is required',
                'password.required' => 'Password is required'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token'=> $token
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
