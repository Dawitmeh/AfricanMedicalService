<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            return response()->json([
                'data' => Role::where('guard_name', 'api')->get()
            ]);

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
                'name' => 'required'
            ]);

            $roleWeb = Role::create([
                'name' => $request->name,
                'guard_name' => 'web'
            ]);

            $roleApi = Role::create([
                'name' => $request->name,
                'guard_name' => 'api'
            ]);

            return response()->json([
                'message' => 'Role created successfully',
                'data' => $roleApi
            ]);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $role = Role::where('id', $id)->first();
            return response()->json([
                'data' => $role
            ]);

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
                'name' => 'required'
            ]);

            try {

                $role = Role::findOrFail($id);

                Role::where('name', $role->name)->whereIn('guard_name', ['web', 'api'])->update([
                    'name' => $request->name
                ]);

                return response()->json([
                    'message' => 'Role updated successfully',
                    'data' => $role
                ]);

            } catch (Exception $ex) {
                 return response()->json([
                    'message' => 'Error updating role',
                    'error' => $ex->getMessage()
                ], 400);
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
            $role = Role::findOrFail($id);

            Role::where('name', $role->name)->whereIn('guard_name', ['web', 'api'])->delete();

            return response()->json([
                'message' => 'Role deleted',
                'deleted_role' => $role->name
            ]);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function addPermissionToRole($roleId) 
    {
        try {
            $permissions = Permission::where('guard_name', 'api')->get();
            $role = Role::findOrFail($roleId);

            $rolePermissions = DB::table('role_has_permissions')
                                ->where('role_has_permissions.role_id', $role->id)
                                ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
                                ->all();
            
            return response()->json([
                'permissions' => $permissions,
                'role' => $role,
                'rolePermissions' => $rolePermissions
            ]);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function updatePermissionToRole(Request $request, $roleId) 
    {
        try {
            $request->validate([
                'permission' => 'required'
            ]);
    
            $role = Role::findOrFail($roleId);
            $role->syncPermissions($request->permission);
    
            return response()->json([
                'data' => $role,
                'message' => 'Permissions added to role'
            ]);

        } catch (Exception $ex) {
            return response()->json([
                'code' => 400,
                'error' => $ex->getMessage()
            ]);
        }
        

    }
}
