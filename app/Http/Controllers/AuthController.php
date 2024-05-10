<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class AuthController extends Controller
{
    public function me(Request $request) {
        try {
            if (auth('sanctum')->user()) {
                Auth::user()->currentAccessToken();
                return response()->json([
                    'status' => true,
                    'message' => "You are indeed logged in."
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required',
                'password' => 'required',
            ]);
    
            $email = $validatedData['email'];
            $password = $validatedData['password'];

            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 404);
            }

            if (Auth::attempt(['email' => $email, 'password' => $password])) {
                $userid = Auth::id();
                $token = auth('sanctum')->user()->createToken('AuthToken')->plainTextToken;
                return response()->json([
                    'status' => true,
                    'message' => 'Authentication successful.',
                    'userid' => $userid,
                    'token' => $token
                ], 200);
            
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Incorrect password.'
                ], 401);
            }

        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            if (auth('sanctum')->user()) {
                auth('sanctum')->user()->tokens()->delete();
                return response()->json([
                    'message' => 'Logged out successfully.'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated.'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Logout failed.'
            ], 500);
        }
    }


    public function register(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'username' => 'required',
                'email' => 'required|email|',
                'password' => 'required',
            ]);
            
            $username = $validatedData['username'];
            $email = $validatedData['email'];
            $password = $validatedData['password'];

            $user = User::where('email', $email)->first();

            if($user){
                return response()->json(['message' => 'Email telah digunakan'], 400);
            }

            User::create([
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($password)
            ]);

            return response()->json(['message' => 'Data berhasil disimpan'], 200);
        }catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 422);
        }catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
}