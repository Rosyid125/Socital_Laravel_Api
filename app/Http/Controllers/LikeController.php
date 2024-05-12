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


class LikeController extends Controller
{

    public function likePost(Request $request){
        try {
            $postid = $request->route('postid');
            $userid = Auth::user()->userid;

            $alreadyliked = Like::where(['userid' => $userid,
            'postid' => $postid])
            ->first();
            
            if($alreadyliked){
                return response()->json([
                    'status' => false,
                    'messege' => 'You already liked this post.'
                ], 400);
            }

            $like = Like::create([
                'userid' => $userid,
                'postid' => $postid
            ]);

            if(!$like){
                return response()->json([
                    'status' => false,
                    'message' => 'Post is not existed.'
                ], 400);
            }

            // Increment likes from table posts.
            $incrementlikes = Post::where('postid', $postid)
            ->increment('likes');

            // Add new notification row to table notifications.
            $postmaker = Post::where('postid', $postid)
            ->select('userid')
            ->get();

            $notification = Notification::create([
                'userid' => $postmaker,
                'trigerrerid' => $userid,
                'notification' => 'liked your post',
                'datetime' => date('Y-m-d H:i:s'),
                'status' => 'unread',
            ]);
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

            $dislike = Like::where([
                'userid' => $userid,
                'postid' => $postid,
                'likeid' => $likeid
            ])->delete();

            if(!$dislike){
                return response()->json([
                    'status' => false,
                    'message' => 'Post is not existed or you have\'nt liked this post.'
                ], 400);
            } 

            // Decrement likes from table posts.
            $decrementlikes = Post::where('postid', $postid)
            ->decrement('likes');

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
