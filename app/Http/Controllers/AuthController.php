<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function register(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'token' => $token
        ]);
    }

    /**
     * Login
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function login(Request $request)
    {
        $this->validate($request,[
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = Auth::user()->createToken('auth')->plainTextToken;

        return response()->json([
            'token' => $token
        ]);
    }
}
