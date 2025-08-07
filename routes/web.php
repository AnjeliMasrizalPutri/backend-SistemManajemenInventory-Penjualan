<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});


// Route::get('/sanctum/csrf-cookie', function () {
//     return response()->json(['csrf_cookie' => 'set']);
// });

// Route::middleware('web')->post('/login', function (Request $request) {
//     $credentials = $request->validate([
//         'email' => ['required', 'email'],
//         'password' => ['required'],
//     ]);

//     if (!Auth::attempt($credentials)) {
//         return response()->json(['message' => 'Login gagal'], 401);
//     }

//     $request->session()->regenerate();

//     return response()->json([
//         'message' => 'Login berhasil',
//         'user' => Auth::user()
//     ]);
// });

// Route::middleware('auth')->post('/logout', function (Request $request) {
//     Auth::logout();
//     $request->session()->invalidate();
//     $request->session()->regenerateToken();
//     return response()->json(['message' => 'Logout berhasil']);
// });
