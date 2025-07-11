<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Formating\FormatingHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        //
        $validateData = $request->validate([
            'nama' => 'required|string|between:2,100',
            'username' => 'required|string|between:2,100|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'password_confirmation' => 'required',
            'hp' => 'nullable',
            'alamat' => 'nullable',
            'kode_jabatan' => 'nullable',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'nama.between' => 'Nama harus antara :min sampai :max karakter.',

            'username.required' => 'Username wajib diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.between' => 'Username harus antara :min sampai :max karakter.',
            'username.unique' => 'Username sudah digunakan.',

            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal :max karakter.',
            'email.unique' => 'Email sudah digunakan.',

            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'password.min' => 'Password minimal :min karakter.',

            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
        ]);



        $user = User::create(array_merge($validateData, ['password' => bcrypt($request->password), 'kode' => '']));

        if (!$user) {
            return new JsonResponse(['message' => 'registrasi gagal'], 401);
        }
        $kode = FormatingHelper::genKodeDinLength($user->id, 5, 'USR');
        $user->update([
            'kode' => $kode
        ]);
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user,
            // 'valid' => array_merge($validator->validated(), ['password' => bcrypt($request->password)])
        ], 201);
    }
    public function login(Request $request)
    {
        //
        // $loginUserData = $request->validate([
        //     'email' => 'required|string|email',
        //     'password' => 'required'
        // ]);
        // $user = User::where('email', $loginUserData['email'])->first();

        $loginUserData = $request->validate([
            'login' => 'required|string',  // bisa username atau email
            'password' => 'required'
        ]);

        $login = $loginUserData['login'];

        // Cek apakah input berupa email
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Cari user berdasarkan email atau username
        $user = User::where($fieldType, $login)->first();

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
