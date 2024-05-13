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

            // Check if users trying to follow themselves.
            if($followed == $following){
                return response()->json([
                    'status' => false,
                    'messege' => 'You cannot follow yourself.'
                ]);
            }

            // Check if already followed.
            $already = Follow::where([
                'following' => $following,
                'followed' => $followed
            ])->first();

            if($already){
                return response()->json([
                    'status' => false,
                    'messege' => 'Already followed.',
                ]);
            }
            //Check if already followed

            $follow = Follow::create([
                'following' => $following,
                'followed' => $followed
            ]);

            // Increment followers and followings from table users.
            $incrementfollowers = User::where('userid', $followed)
            ->increment('followers');

            $incrementfollowings = User::where('userid', $following)
            ->increment('followings');
            // Increment followers and followings from table users.

            $newfollowid = $follow->followid;

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
            
            // Get followed id since in this function only has the followid and followingid.
            $followed = Follow::where('followid', $followid)
            ->value('followed');

            // Decrement followers and followings from table users.
            // Checking if there is no followers or followings.
            $followers = User::where('userid', $followed)
            ->value('followers');

            if($followers > 0){
                $decrementfollowers = User::where('userid', $followed)
                ->decrement('followers');      
            }

            // Checking if there is no followers or followings.
            $followings = User::where('userid', $following)
            ->value('followings');

            if($followings > 0){
                $decrementfollowings = User::where('userid', $following)
                ->decrement('followings');
            }
            // Decrement followers and folowings from table users.

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
            
            // Get following ids
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
            
            // Get follower ids
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
