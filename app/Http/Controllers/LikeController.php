<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Models\Post;
use App\Models\Like;


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
                return response()->json(["messege" => "you already liked this post"], 400);
            } else {
                $like = Like::create([
                    'userid' => $userid,
                    'postid' => $postid
                ]);
            }

            if(!$like){
                return response()->json(['message' => 'Post is not existed'], 400);
            } else {
                $incrementlike = Post::where('postid', $postid)
                ->where('userid', $userid)
                ->increment('likes');

                $newlikeid = $like -> likeid;
    
                return response()->json([
                    "likeid" => $newlikeid, 
                    "messege" => "post has been liked"
                ],200);
            }
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
                return response()->json(['message' => 'Post is not existed or you have\'nt liked this post'], 400);
            } else {
                $decrementlike = Post::where('postid', $postid)
                ->where('userid', $userid)
                ->decrement('likes');

                return response()->json(["messege" => "post has been disliked"], 200);
            }
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

            return response()->json(["likedby" => $likedby], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
}
