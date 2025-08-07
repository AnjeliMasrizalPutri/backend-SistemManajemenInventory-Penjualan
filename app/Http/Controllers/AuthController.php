<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Jika gagal login
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => false,
                'message' => 'Email atau password salah',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user()->load('role');

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
            ]
        ]);
    }

    public function logout(Request $request)
{
    $user = $request->user();

    if ($user) {
        $user->tokens()->delete(); // hapus token
    }

    return response()->json([
        'status' => true,
        'message' => 'Logout berhasil'
    ], Response::HTTP_OK);
}



}
