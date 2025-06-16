<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $staffs = User::where('userType', 'admin')->get();

            return response()->json([
                'data' => $staffs
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
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'phone' => 'required|string|unique:users',
                'password' => 'required|string|confirmed',
                'country_code' => 'required'
            ],  [
                'email.unique' => 'The email has already been taken',
                'phone.unique' => 'The phone has already been taken',
                'password.confirmed' => 'The password confirmation does not match'
            ]);

            $rawPhone = ltrim($request->phone, '0');
            $phone = $request->country_code . $rawPhone;

            // Create the staff
            $staff = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $phone,
                'userType' => 'admin',
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'Message' => 'Client registered successfully', 
                'data' => $staff
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
            $staff = User::where('userType', 'admin')->where('id', $id)->first();

            return response()->json([
                'data' => $staff
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
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'phone' => 'required|string|unique:users',
                'password' => 'required|string',
                'country_code' => 'required'
            ], [
                'email.unique' => 'The email has already been taken',
                'phone.unique' => 'The phone has already been taken',
                'password.confirmed' => 'The password confirmation does not match'
            ]);

            $staff = User::findOrFail($id);

            $staff->update($data);

            return response()->json([
                'data' => $staff
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
            $staff = User::findOrFail($id);
            $staff->delete();


            return response()->json([
                'message' => 'Staff deleted'
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
