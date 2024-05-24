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
    public function editUser(Request $request){
        try{
            $userid = Auth::user()->userid;
            $username = $request->input('username');
            $email = $request->input('email');
            if($email){
                $validatedData = $request->validate([
                    'email' => 'email',
                ]);
                $email = $validatedData['email'];
            }
            $prevpassword = $request->input('prevpassword');
            $newpassword = $request->input('newpassword');
            $profilepicture = $request->input('profilepicture');
            $bio = $request->input('bio');

            if ($email) {
                $user = User::where('email', $email)
                ->first();
    
                if($user){
                    return response()->json([
                        'status' => false,
                        'message' => 'Email is used.'
                    ], 400);
                }

                $updateemail  = User::where('userid', $userid)->update([
                    'email' => $email
                ]);
            }
            if ($prevpassword || $newpassword) {
                if ($prevpassword && $newpassword) {
                    $matchpassword = Hash::check($prevpassword, Auth::user()->password);
                
                    if (!$matchpassword) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Previous password is not correct'
                        ], 400);
                    }

                    $updatepassword = User::where('userid', $userid)->update([
                        'password' => Hash::make($newpassword)
                    ]);
                }
                return response()->json([
                    'status' => false,
                    'message' => 'Both of password fields required.'
                ], 400);
            }

            $updatetherest  = User::where('userid', $userid)->update([
                'username' => $username,
                'profilepicture' => $profilepicture,
                'bio' => $bio,
                'email' => $email,
                'password' => Hash::make($newpassword),
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
