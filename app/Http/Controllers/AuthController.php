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
            $userid = Auth::user()->userid;

            return response()->json([
                'status' => true,
                'message' => 'You are indeed logged in.',
                'userid' => $userid
            ], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
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
                'confpassword' => 'required'
            ]);
            
            $username = $validatedData['username'];
            $email = $validatedData['email'];
            $password = $validatedData['password'];
            $confpassword = $validatedData['confpassword'];

            if($password != $confpassword) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password not match'
                ], 400);
            }

            $user = User::where('email', $email)
            ->first();

            if($user){
                return response()->json([
                    'status' => false,
                    'message' => 'Email is used.'
                ], 400);
            }

            User::create([
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data saved succesully'
            ], 200);
        }catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->errors()
            ], 422);
        }catch (\Exception $e) {
            dd($e);
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

            $user = User::where('email', $email)
            ->first();

            if(!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 404);
            }

            $authattempt = Auth::attempt(['email' => $email, 'password' => $password]);

            if(!$authattempt) {
                return response()->json([
                    'status' => false,
                    'message' => 'Incorrect password.'
                ], 401);
            }

            // Its basically the same as "Auth::user()->createToken('AuthToken')->plainTextToken;" i just wanna try something different
            $token = auth('sanctum')->user()->createToken('AuthToken')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Authentication successful.',
                'userid' => $user->userid,
                'token' => $token
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false, 
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Its basically the same as "Auth::user()->tokens()->delete();" i just wanna try something different
            $delete = auth('sanctum')->user()->tokens()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logged out successfully.'
            ], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Logout failed.'
            ], 500);
        }
    }
}