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
use App\Models\Follow;

class PostController extends Controller
{
    public function getAllPosts(Request $request)
    {
        try {
            $userid = $request->route('userid');
            
            $followed = Follow::select('followed')
            ->where('following', $userid)
            ->get();

            $posts = Post::with(['user' => function ($query) {
                $query->select('userid','username', 'profilepicture');
            }])
            ->where(function ($query) use ($followed, $userid) {
                $query->whereIn('userid', $followed)
                      ->orWhere('userid', $userid);
            })
            ->select('postid', 'userid', 'datetime', 'post', 'postpic', 'likes', 'comments')
            ->get();

            return response()->json(["posts" => $posts], 200);
        } catch (\Exception $e) {

            dd($e);
            
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    public function getAllUserPosts(Request $request)
    {
        try {
            $userid = $request->route('userid');

            $posts = Post::where('userid', $userid)
            ->select('postid', 'userid', 'datetime', 'post', 'postpic', 'likes', 'comments')
            ->get();

            return response()->json(["posts" => $posts], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    public function createPost(Request $request)
    {
        try {
            $userid = Auth::user()->userid;
            $post = $request->input('post');
            $postpic = $request->input('postpic');

            if (!$post && !$postpic) {
                return response()->json(['messsage' => 'Post and Post Picture can\'t be empty'], 400);
            }

            $create = Post::create([
                'userid' => $userid,
                'datetime' => date("Y-m-d H:i:s"),
                'post' => $post,
                'postpic'=> $postpic,
                'likes' => 0,
                'comments' => 0
            ]);

            $newpostid = $create->postid;

            if (!$create) {
                return response()->json(['messsage' => 'Can\'t create post'], 400);
            } else {
                return response()->json([
                    'messsage' => 'Post has been created',
                    'postid' => $newpostid
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function postDetails(Request $request)
    {
        try {
            $postid = $request->route('postid');

            $details = Post::with(['user' => function ($query) {
                $query->select('userid','username', 'profilepicture');
            }])
            ->where('postid', $postid)
            ->select('postid', 'userid', 'datetime', 'post', 'postpic', 'likes', 'comments')
            ->get();

            return response()->json(["details" => $details], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function editPost(Request $request)
    {
        try {
            $postid = $request->route('postid');
            $userid = Auth::user()->userid;
            $post = $request->input('post');
            $postpic = $request->input('postpic');

            if (!$post && !$postpic) {
                return response()->json(['messsage' => 'Post and Post Picture can\'t be empty'], 400);
            }

            $update = Post::where('postid', $postid)
            ->where('userid', $userid)
            ->update([
                'datetime' => date("Y-m-d H:i:s"),
                'post' => $post,
                'postpic' => $postpic,
            ]);

            if (!$update) {
                return response()->json(['messsage' => 'Post is not existed or this is not your post'], 400);
            } else {
                return response()->json(['messsage' => 'Post has been updated'], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function deletePost(Request $request)
    {
        try {
            $postid = $request->route('postid');
            $userid = Auth::user()->userid;

            $delete = Post::where([
                'postid' => $postid,
                'userid' => $userid
            ])->delete();

            if(!$delete){
                return response()->json(['messsage' => 'Post is not existed or this is not your post'], 400);
            } else {
                return response()->json(["messege" => "your post has been deleted"], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
}