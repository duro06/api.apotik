<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        //
    }
    public function login(Request $request)
    {
        //
        $loginUserData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required'
        ]);
        $user = User::where('email', $loginUserData['email'])->first();
        if (!$user || !Hash::check($loginUserData['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid Credentials'
            ], 401);
        }
        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;
        return new JsonResponse([
            'token' => $token,
        ]);
    }
    public function logout(Request $request)
    {
        //
        $user = Auth::user();
        $user->tokens()->delete();

        return new JsonResponse([
            'message' => 'Logout Successfully'
        ]);
    }
    public function profile(Request $request)
    {
        //
        $user = Auth::user();

        return new JsonResponse([
            'user' => $user
        ]);
    }
}
