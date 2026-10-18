<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index()
    {
        try {
            // Get all users with their roles (using Spatie's HasRoles)
            $users = User::with('roles')->get(['id', 'name', 'email', 'mobile', 'created_at']);
            $count = $users->count();
            \Log::info("UserController@index: Returning {$count} users");
            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            \Log::error('UserController@index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a specific user with roles.
     */
    public function show($id)
    {
        try {
            $user = User::with('roles')->findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => $user
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'role' => 'required|string|exists:roles,name',
            ]);

            $user = User::findOrFail($id);
            $user->assignRole($validated['role']);

            return response()->json([
                'success' => true,
                'message' => 'Role assigned successfully',
                'data' => $user->load('roles')
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
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a role from a user.
     */
    public function removeRole(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'role' => 'required|string|exists:roles,name',
            ]);

            $user = User::findOrFail($id);
            $user->removeRole($validated['role']);

            return response()->json([
                'success' => true,
                'message' => 'Role removed successfully',
                'data' => $user->load('roles')
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
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync all roles for a user (replace existing).
     */
    public function syncRoles(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'roles' => 'required|array',
                'roles.*' => 'string|exists:roles,name',
            ]);

            $user = User::findOrFail($id);
            $user->syncRoles($validated['roles']);

            return response()->json([
                'success' => true,
                'message' => 'Roles synced successfully',
                'data' => $user->load('roles')
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
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
