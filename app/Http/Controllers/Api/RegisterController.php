<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    
    public function register(Request $request){
        $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email|unique:users',
            'password' => ['required', 
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ]
        ]);

        $user = User::create(
            $request->only('email', 'name') + [
                'password' => bcrypt($request->password)
                ]
            );
        
        $token = $user->createToken(config('app.name'))->plainTextToken;

        return response()->json([
            'message' => 'Register Successfully',
            'result' => [
                'user'  => $user,
                'token' => $token
            ]
        ]);
        
    }

    public function login(Request $request){
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8']
        ]);

        if(!$user = User::where('email', $request->email)->first()){
            return response()->json([
                'message' => 'Invalid Credentials',
                'result' => []
            ],400);
        }
        
        if(!Hash::check($request->password,$user->password)){
            return response()->json([
                'message' => 'Invalid Credentials',
                'result' => []
            ],400);
        }
        $token = $user->createToken(config('app.name'))->plainTextToken;

        return response()->json([
            'message' => 'Login Successful',
            'result' => [
                'user'  => $user,
                'token' => $token
            ]
        ]);

    }

}
