<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Follow;

class FollowController extends Controller
{
    public function follow(Request $request){
        try{
            $followed = $request->route('userid');
            $following = Auth::user()->userid;

            $follow = Follow::create([
                'following' => $following,
                'followed' => $followed
            ]);

            $newfollowid = $follow->followid;

            return response()->json([
                "status" => true,
                "messege" => "follow successfully",
                "followid" => $newfollowid
            ],200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function unfollow(Request $request){
        try{
            $followid = $request->route('followid');
            $following = Auth::user()->userid;

            $follow = Follow::where([
                'following' => $following,
                'followid' => $followid
            ])->delete();

            return response()->json([
                "status" => true,
                "messege" => "unfollow successfully"
            ],200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function getFollowing(Request $request){
        try {
            $userid = $request->route('userid');
            
            $following = Follow::select('followed')
            ->where('following', $userid)
            ->get();

            $followingdetails = User::whereIn('userid', $following)
            ->select('userid', 'username', 'profilepicture')
            ->get();

            return response()->json([
                "status" => true,
                "messege" => "get following successfully",
                "following" => $followingdetails
            ], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function getFollowers(Request $request){
        try {
            $userid = $request->route('userid');
            
            $followers = Follow::select('following')
            ->where('followed', $userid)
            ->get();

            $followerdetails = User::whereIn('userid', $followers)
            ->select('userid', 'username', 'profilepicture')
            ->get();

            return response()->json([
                "status" => true,
                "messege" => "get followers successfully",
                "followers" => $followerdetails
            ], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
}
