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
            $validatedData = $request->validate([
                "userid"=> "required"
            ]);

            $postid = $request->postid;
            $userid = $validatedData['userid'];

            $alreadyliked = Like::where('userid', $userid)
            ->where('postid', $postid)
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
    
                return response()->json(["messege" => "post has been liked"], 200);
            }
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 400);
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
            $validatedData = $request->validate([
                "userid"=> "required"
            ]);

            $postid = $request->postid;
            $userid = $validatedData['userid'];

            $alreadyliked = Like::where('userid', $userid)
            ->where('postid', $postid)
            ->first();

            if($alreadyliked){
                $like = Post::where('postid', $postid)
                ->select('likes')
                ->first();
            } else {
                return response()->json(["messege" => "you're not liked this post yet"], 400);
            }

            if($like->likes > 0){
                
                $dislike = Like::where([
                    'userid' => $userid,
                    'postid' => $postid
                    ])->delete();

                if(!$dislike){
                    return response()->json(['message' => 'Post is not existed'], 400);
                } else {
                    $decrementlike = Post::where('postid', $postid)
                    ->where('userid', $userid)
                    ->decrement('likes');
    
                    return response()->json(["messege" => "post has been disliked"], 200);
                }
            } else {
                return response()->json(["messege" => "you can't dislike this post"], 400);
            }
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 400);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function likedBy(Request $request){
        try {

            $postid = $request->postid;

            $likedby = Like::with(['user' => function ($query) {
                $query->select('userid','username', 'profilepicture');
            }])
            ->where('postid', $postid)
            ->select('userid')
            ->get();

            return response()->json(["likedby" => $likedby], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 400);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
}
