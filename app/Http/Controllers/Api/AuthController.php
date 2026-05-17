<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = AdminUser::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные'],
            ]);
        }

        $token = base64_encode(json_encode([
            'user_id' => $user->id,
            'exp' => time() + 86400 * 7,
        ]));

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function me(): JsonResponse
    {
        $token = request()->header('X-Admin-Token');

        if (! $token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode(base64_decode($token), true);

        if (! $data || ($data['exp'] ?? 0) < time()) {
            return response()->json(['error' => 'Token expired'], 401);
        }

        $user = AdminUser::find($data['user_id']);

        return response()->json($user);
    }
}
