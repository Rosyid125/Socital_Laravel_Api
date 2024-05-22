<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Models\Like;
use App\Models\Post;
use App\Models\Notification;
use App\Models\User;


class LikeController extends Controller
{
    public function userLikeStatus(Request $request){
        try {
            $userid = $request->route('userid');
            $postid = $request->route('postid');

            $like = Like::where([
                'userid' => $userid,
                'postid' => $postid
            ])->first();

            if($like){
                return response()->json([
                    'status' => true,
                    'messege' => 'Post has been liked.',
                    'liked' => true,
                    'likeid' => $like->likeid
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'messege' => 'Post has not been liked.',
                    'liked' => false
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
    public function likePost(Request $request){
        try {
            $postid = $request->route('postid');
            $userid = Auth::user()->userid;

            // Checking if user already liked the post.
            $already = Like::where(['userid' => $userid,
            'postid' => $postid])
            ->first();
            
            if($already){
                return response()->json([
                    'status' => false,
                    'messege' => 'You already liked this post.'
                ], 400);
            }
            // Checking if user already liked the post.

            $like = Like::create([
                'userid' => $userid,
                'postid' => $postid
            ]);

            // update likes from table posts.
            $countlikes = Like::where('postid', $postid)->count();

            $updatelikes = Post::where('postid', $postid)->update(['likes' => $countlikes]);
            // update likes from table posts.

            // Add new notification row to table notifications.
            $postmaker = Post::where('postid', $postid)
            ->value('userid');

            // If user likes on own post then wont make any notification.
            if($postmaker != $userid){
                $postcontent = Post::where('postid', $postid)->select('content')->first();

                $triggererusername = User::where('userid', $userid)->select('username')->first();

                $notification = Notification::create([
                    'userid' => $postmaker,
                    'trigerrerid' => $userid,
                    'notification' => $triggererusername -> username .' liked your post \''. $postcontent->content .'\'',
                    'datetime' => date('Y-m-d H:i:s'),
                    'status' => 'unread',
                ]);
         }
            // Add new notification row to table notifications.

            $newlikeid = $like -> likeid;

            return response()->json([
                'status' => true,
                'messege' => 'Post has been liked.',
                'likeid' => $newlikeid
            ], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function dislikePost(Request $request){
        try {
            $userid = Auth::user()->userid;
            $postid = $request->route('postid');
            $likeid = $request->route('likeid');

            // Check if like dosent belong to the user.
            $like = Like::where([
                'userid' => $userid,
                'likeid' => $likeid
            ])->first();

            if(!$like){
                return response()->json([
                    'status' => false,
                    'message' => 'You can only delete your like.'
                ], 422);
            }
            // Check if like dosent belong to the user.

            $dislike = Like::where([
                'userid' => $userid,
                'postid' => $postid,
                'likeid' => $likeid
            ])->delete();

            // update likes from table posts.
            $countlikes = Like::where('postid', $postid)->count();

            $updatelikes = Post::where('postid', $postid)->update(['likes' => $countlikes]);
            // update likes from table posts.

            return response()->json([
                'status' => true,
                'messege' => 'Post has been disliked.'
            ], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function getLikes(Request $request){
        try {
            $postid = $request->route('postid');

            $likedby = Like::with(['user' => function ($query) {
                $query->select('userid','username', 'profilepicture');
            }])
            ->where('postid', $postid)
            ->select('userid')
            ->get();

            return response()->json([
                'status' => true,
                'message' => 'Get likes success',
                'likedby' => $likedby
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
