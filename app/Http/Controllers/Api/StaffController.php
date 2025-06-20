<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $staffs = User::where('userType', 'admin')->with('roles')->get();

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
                'country_code' => 'required',
                'role' => 'required|array'
            ],  [
                'email.unique' => 'The email has already been taken',
                'phone.unique' => 'The phone has already been taken',
                'password.confirmed' => 'The password confirmation does not match'
            ]);

            $roles = $request->input('role');
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

             if ($staff) {
                // Assign each role separately for both guards
                foreach ($roles as $roleName) {
                    // First, ensure the roles exist for each guard
                    // $roleForWeb = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                    $roleForApi = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);

                    // $staff->assignRole($roleForWeb);
                    $staff->assignRole($roleForApi);
                }
            }

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
            $staff = User::where('userType', 'admin')->with('roles')->where('id', $id)->first();

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
                'email' => [
                    'required',
                    'string',
                    'email',
                    Rule::unique('users')->ignore($id),
                ],
                'phone' => [
                    'required',
                    'string',
                    Rule::unique('users')->ignore($id),
                ],
                'password' => 'nullable|string',
                'country_code' => 'required',
                'role' => 'nullable|array'
            ], [
                'email.unique' => 'The email has already been taken',
                'phone.unique' => 'The phone has already been taken',
                'password.confirmed' => 'The password confirmation does not match'
            ]);

            $staff = User::findOrFail($id);

            // Format phone number
            $rawPhone = ltrim($request->phone, '0');
            $data['phone'] = $request->country_code . $rawPhone;

            // Handle optional password update
            if (!empty($request->password)) {
                $data['password'] = Hash::make($request->password);
            } else {
                unset($data['password']);
            }

            $staff->update($data);

            // Update roles if provided
            if ($request->has('role')) {
                // Remove all existing roles and assign new ones with the correct guard
                $staff->syncRoles([]); // Clear existing roles

                foreach ($request->role as $roleName) {
                    $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);
                    $staff->assignRole($role);
                }
            }


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
