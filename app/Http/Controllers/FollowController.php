<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Models\Follow;
use App\Models\User;
use App\Models\Notification;

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

            if(!$follow){
                return response()->json([
                    'status' => false,
                    'messege' => 'Follow failed.'
                ]);
            }

            $newfollowid = $follow->followid;

            // Increment followers and followings from table users.
            $incrementfollowers = User::where('userid', $followed)
            ->increment('followers');
            $incrementfollowings = User::where('userid', $following)
            ->increment('following');

            // Add new notification row to table notifications.
            $notification = Notification::create([
                'userid' => $followed,
                'trigerrerid' => $following,
                'notification' => 'followed you',
                'datetime' => date('Y-m-d H:i:s'),
                'status' => 'unread',
            ]);

            return response()->json([
                'status' => true,
                'messege' => 'Follow succesfull.',
                'followid' => $newfollowid
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

            $unfollow = Follow::where([
                'following' => $following,
                'followid' => $followid
            ])->delete();

            if(!$unfollow){
                return response()->json([
                    'status' => false,
                    'messege' => 'Unfollow failed.'
                ]);
            }

            $followed = Follow::select('followed')
            ->where('followid', $followid)
            ->get();

            $decrementfollowers = User::where('userid', $followed)
            ->decrement('followers');
            $decrementfollowings = User::where('userid', $following)
            ->decrement('followers');

            return response()->json([
                'status' => true,
                'messege' => 'Unfollow successfull.'
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
                'status' => true,
                'messege' => 'Get following successfull.',
                'following' => $followingdetails
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
                'status' => true,
                'messege' => 'Get followers successfull.',
                'followers' => $followerdetails
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
