<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupabaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $users = $this->supabase->get('admin_users', [
            'email' => 'eq.' . $request->email,
            'limit' => 1,
        ]);

        $user = $users[0] ?? null;

        if (!$user || !Hash::check($request->password, $user['password'])) {
            return response()->json([
                'errors' => [
                    'email' => ['Неверные учетные данные'],
                ],
            ], 422);
        }

        $token = base64_encode(json_encode([
            'user_id' => $user['id'],
            'exp' => time() + 86400 * 7,
        ]));

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
            ],
        ]);
    }

    public function me(): JsonResponse
    {
        $token = request()->header('X-Admin-Token');

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode(base64_decode($token), true);

        if (!$data || ($data['exp'] ?? 0) < time()) {
            return response()->json(['error' => 'Token expired'], 401);
        }

        $user = $this->supabase->find('admin_users', $data['user_id']);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 401);
        }

        return response()->json([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ]);
    }
}