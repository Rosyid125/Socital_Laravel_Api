<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\User;


class UserController extends Controller
{
    public function getAllUsers(Request $request){
        try{
            $allusers = User::select('userid', 'username', 'profilepicture', 'followers')
            ->get();

            return response()->json([
                'status' => true,
                'message' => 'Get all users successfull.',
                'allusers' => $allusers
            ], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function userDetails(Request $request){
        try{
            $userid = $request->route('userid');
            $user = User::where('userid', $userid)
            ->select('userid', 'username', 'email', 'profilepicture', 'bio', 'followers')
            ->get();

            return response()->json([
                'status' => true,
                'message' => 'Get user successfull.',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function editUser(Request $request){
        try{
            $validatedData = $request->validate([
                'username' => 'required',
                'email' => 'required|email|',
                'prevpassword' => 'required',
                'password' => 'required',
            ]);

            $userid = Auth::user()->userid;
            $username = $validatedData['username'];
            $email = $validatedData['email'];
            $prevpassword = $validatedData['prevpassword'];
            $password = $validatedData['password'];
            $profilepicture = $request->input('profilepicture');
            $bio = $request->input('bio');

            $matchpassword = Hash::check($prevpassword, Auth::user()->password);

            if (!$matchpassword) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password not match'
                ], 400);
            }

            $updateuser = User::where('userid', $userid)->update([
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($password),
                'profilepicture' => $profilepicture,
                'bio' => $bio
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Update user success'
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
    public function searchUsers(Request $request){
        try{
            $search = $request->input('search');

            $users = User::where('username', 'like', '%' . $search . '%')
            ->select('userid', 'username', 'profilepicture')
            ->get();

            return response()->json([
                'status' => true,
                'message' => 'Search users successfull.',
                'users' => $users
            ]);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
}
