<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        // Return available roles
        return response()->json([
            'roles' => ['admin', 'user']
        ]);
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role' => 'required|in:admin,user',
        ]);

        $user->update(['role' => $data['role']]);

        return response()->json([
            'message' => 'Role assigned successfully.',
            'user' => $user->only('id', 'name', 'email', 'role'),
        ]);
    }

    public function getUserRole(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user->only('id', 'name', 'email', 'role'),
        ]);
    }
}