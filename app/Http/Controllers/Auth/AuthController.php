<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    use ApiResponse;

    public function login(Request $request)
    {
        $credentials = $request->validate(
            [
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string']
            ]
        );

        if (!Auth::attempt($credentials)) {
            return response()->json([
                "error" => "Bad Credentials"
            ], 401);
        }

        $user = Auth::user();

        return response()->json([
            "token" => $user->createToken('authtoken')->plainTextToken,
            "user" => $user->only(['first_name', 'last_name', 'email'])
        ]);

        return response()->noContent();
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
}
