<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ConfirmablePasswordController extends Controller
{
    /**
     * Confirm the user's password (for sensitive actions).
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Password tidak sesuai.'],
            ]);
        }

        // Simpan waktu konfirmasi jika diperlukan
        session(['auth.password_confirmed_at' => time()]);

        return response()->json(['message' => 'Password berhasil dikonfirmasi.']);
    }
}
