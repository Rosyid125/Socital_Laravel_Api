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
    public function userFollowStatus(Request $request){
        try {
            $following = $request->route('following');
            $followed = $request->route('followed');

            $follow = Follow::where([
                'following' => $following,
                'followed' => $followed
            ])->first();

            if($follow){
                return response()->json([
                    'status' => true,
                    'messege' => 'You already followed this user.',
                    'followid' => $follow->followid,
                    'followed' => true
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'messege' => 'You don\'t follow this user.',
                    'followid' => null,
                    'followed' => false
                ], 200);
            }
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
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
                ], 400);
            }
            //Check if already followed

            $follow = Follow::create([
                'following' => $following,
                'followed' => $followed,
            ]);

            // update followers and followings from table users.
            $countfollowers = Follow::where('followed', $followed)->count();

            $updatefollowers = User::where('userid', $followed)->update([
                'followers' => $countfollowers
            ]);

            $countfollowings = Follow::where('following', $following)->count();

            $updatefollowings = User::where('userid', $following)->update([
                'followings' => $countfollowings
            ]);
            // update followers and followings from table users.

            $newfollowid = $follow->followid;

            // Add new notification row to table notifications.
            $triggererusername = User::where('userid', $following)->select('username')->first();

            $notification = Notification::create([
                'userid' => $followed,
                'trigerrerid' => $following,
                'notification' => $triggererusername->username .' followed you',
                'datetime' => date('Y-m-d H:i:s'),
                'status' => 'unread',
            ]);
            // Add new notification row to table notifications.

            // For react logic reason i got to add some query to get the (justfollowed) user details.
            $followingdetails = Follow::with(['followed' => function ($query) {
                $query->select('userid','username', 'profilepicture');
            }])
            ->where('following', $following)
            ->select('followid', 'followed')
            ->get();

            return response()->json([
                'status' => true,
                'messege' => 'Follow succesfull.',
                'following' => $followingdetails
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

            // Check if follow dosent belong to the user.
            $follow = Follow::where([
                'following' => $following,
                'followid' => $followid
            ])->first();

            if(!$follow){
                return response()->json([
                    'status' => false,
                    'message' => 'You can only delete your follows.'
                ], 422);
            }
            // Check if follow dosent belong to the user.
            
            $unfollow = Follow::where([
                'following' => $following,
                'followid' => $followid
            ])->delete();

            
            // Get followed id since in this function only has the followid and followingid.
            $followed = Follow::where('followid', $followid)
            ->value('followed');

            // update followers and followings from table users.
            $countfollowers = Follow::where('followed', $followed)->count();

            $updatefollowers = User::where('userid', $followed)->update([
                'followers' => $countfollowers
            ]);

            $countfollowings = Follow::where('following', $following)->count();

            $updatefollowings = User::where('userid', $following)->update([
                'followings' => $countfollowings
            ]);
            // update followers and followings from table users.

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

            $followingdetails = Follow::with(['followed' => function ($query) {
                $query->select('userid','username', 'profilepicture');
            }])
            ->where('following', $userid)
            ->select('followid', 'followed')
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

            $followerdetails = Follow::with(['following' => function ($query) {
                $query->select('userid','username', 'profilepicture');
            }])
            ->where('followed', $userid)
            ->select('followid', 'following')
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
