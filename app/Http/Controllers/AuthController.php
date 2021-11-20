<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|max:8'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $token = $user->createToken('apptoken')->plainTextToken;

        $cookie = cookie('jwt', $token, 60 * 20);

        return response([
            'message' => 'account created successfull',
            'token' => $token
        ], 201)->withCookie($cookie);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Invalid Credentials'
            ], 401);
        }

        $token = $user->createToken('apptoken')->plainTextToken;

        $cookie = cookie('jwt', $token, 60 * 20);

        return response([
            'message' => 'successfully loggedIn',
            'token' => $token
        ])->withCookie($cookie);
    }

    public function logout() 
    {
        auth()->user()->tokens()->delete();

        $cookie = Cookie::forget('jwt');

        return response([
            'message' => 'successfully logged out'
        ])->withCookie($cookie);
    }
}
