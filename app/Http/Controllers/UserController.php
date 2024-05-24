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
            $allusers = User::select('userid', 'username', 'profilepicture', 'followers', 'followings')
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
            ->select('userid', 'username', 'email', 'profilepicture', 'bio', 'followers', 'followings')
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
    public function editUser(Request $request) {
        try {
            $userid = Auth::user()->userid;
            $username = $request->input('username');
            $email = $request->input('email');
            $prevpassword = $request->input('prevpassword');
            $newpassword = $request->input('newpassword');
            $bio = $request->input('bio');
    
            // Validate email if it exists
            if ($email) {
                $validatedData = $request->validate([
                    'email' => 'email',
                ]);
                $email = $validatedData['email'];
    
                // Check if the email is already in use
                $user = User::where('email', $email)->first();
                if ($user) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Email is used.'
                    ], 400);
                }
    
                // Update email
                User::where('userid', $userid)->update(['email' => $email]);
            }
    
            // Update password if both fields are provided
            if ($prevpassword || $newpassword) {
                if ($prevpassword && $newpassword) {
                    if (!Hash::check($prevpassword, Auth::user()->password)) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Previous password is not correct'
                        ], 400);
                    }
                    User::where('userid', $userid)->update(['password' => Hash::make($newpassword)]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Both password fields are required.'
                    ], 400);
                }
            }

            // Code untuk menangani unggahan file
            $profilepicture = $request->file('profilepicture');
            if($profilepicture) {
                $profilepicturename = time().'.'.$profilepicture->getClientOriginalExtension();
                $profilepicture->storeAs('profilepictures', $profilepicturename, 'public');
                User::where('userid', $userid)->update(['profilepicture' => 'http://localhost:8000/storage/profilepictures/' . $profilepicturename]);
            }
    
            // Update other fields
            $updateData = array_filter([
                'username' => $username,
                'bio' => $bio
            ], function($value) { return $value !== null; });
    
            if (!empty($updateData)) {
                User::where('userid', $userid)->update($updateData);
            }
    
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
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    
    public function searchUsers(Request $request){
        try{
            $search = $request->route('search');

            $users = User::where('username', 'like', '%' . $search . '%')
            ->select('userid', 'username', 'profilepicture', 'followers', 'followings')
            ->orderBy('followers', 'desc')
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
