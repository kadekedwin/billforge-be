<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $user
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'image_size_bytes' => 'nullable|integer|min:0'
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $user->fresh()
        ]);
    }
}
