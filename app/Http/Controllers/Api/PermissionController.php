<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $permissions = Permission::where('guard_name', 'api')->get();

            return response()->json([
                'data' => $permissions
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
                'name' => 'required'
            ]);

            $permissionWeb = Permission::create([
                'name' => $request->name,
                'guard_name' => 'web'
            ]);

            $permissionApi = Permission::create([
                'name' => $request->name,
                'guard_name' => 'api'
            ]);

            return response()->json([
                'data' => $permissionApi
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
    public function show(string $id)
    {
        try {
            $permission = Permission::where('id', $id)->first();

            return response()->json([
                'data' => $permission
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

            try {
                $permission = Permission::findOrFail($id);

                Permission::where('name', $permission->name)->whereIn('guard_name', ['web', 'api'])->update([
                    'name' => $request->name
                ]);

                return response()->json([
                    'data' => Permission::where('name', $request->name)->where('guard_name', 'api')->first()
                ], 200);
            } catch (Exception $ex) {
                return response()->json([
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
            $permission = Permission::findOrFail($id);

            Permission::where('name', $permission->name)->whereIn('guard_name', ['web', 'api'])->delete();

            return response()->json([
                'deleted_permission' => $permission->name
            ]);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
