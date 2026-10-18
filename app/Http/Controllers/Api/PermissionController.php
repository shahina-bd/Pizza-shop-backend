<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PermissionController extends Controller
{
    /**
     * Display a listing of all permissions.
     */
    public function index()
    {
        try {
            $permissions = Permission::all();
            return response()->json([
                'success' => true,
                'message' => 'Permissions retrieved successfully',
                'data' => $permissions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a specific permission.
     */
    public function show($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Permission retrieved successfully',
                'data' => $permission
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permission not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:permissions,name|max:255',
                'guard_name' => 'nullable|string|max:255',
            ]);

            $permission = Permission::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'data' => $permission
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, $id)
    {
        try {
            $permission = Permission::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|unique:permissions,name,' . $id . '|max:255',
                'guard_name' => 'nullable|string|max:255',
            ]);

            $permission->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Permission updated successfully',
                'data' => $permission
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permission not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the specified permission.
     */
    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $permission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permission deleted successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permission not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get permissions grouped by category.
     */
    public function grouped()
    {
        try {
            $permissions = Permission::all();
            
            $grouped = [];
            foreach ($permissions as $permission) {
                $parts = explode(' ', $permission->name);
                $action = end($parts);
                $resource = implode(' ', array_slice($parts, 0, -1));
                
                if (!isset($grouped[$resource])) {
                    $grouped[$resource] = [];
                }
                $grouped[$resource][] = $permission;
            }

            return response()->json([
                'success' => true,
                'message' => 'Grouped permissions retrieved successfully',
                'data' => $grouped
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve grouped permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk create permissions.
     */
    public function bulkStore(Request $request)
    {
        try {
            $validated = $request->validate([
                'permissions' => 'required|array',
                'permissions.*.name' => 'required|string|unique:permissions,name|max:255',
                'permissions.*.guard_name' => 'nullable|string|max:255',
            ]);

            $created = [];
            foreach ($validated['permissions'] as $permissionData) {
                $permission = Permission::create($permissionData);
                $created[] = $permission;
            }

            return response()->json([
                'success' => true,
                'message' => count($created) . ' permissions created successfully',
                'data' => $created
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
