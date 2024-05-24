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
            
            // Get followed ids.
            $followed = Follow::select('followed')
            ->where('following', $userid)
            ->get();

            // Get all posts including user and the other users that are labeled as "followings".
            $posts = Post::with(['user' => function ($query) {
                $query->select('userid','username', 'profilepicture');
            }])
            ->where(function ($query) use ($followed, $userid) {
                $query->whereIn('userid', $followed)
                      ->orWhere('userid', $userid);
            })
            ->select('postid', 'userid', 'datetime', 'content', 'postpicture', 'likes', 'comments')
            ->orderBy('datetime', 'desc')
            ->get();

            return response()->json([
                'status' => true,
                'message' => 'All posts.',
                'posts' => $posts
            ], 200);
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

            $posts = Post::with(['user' => function ($query) {
                $query->select('userid','username', 'profilepicture');
            }])->where('userid', $userid)
            ->select('postid', 'userid', 'datetime', 'content', 'postpicture', 'likes', 'comments')
            ->orderBy('datetime', 'desc')
            ->get();

            return response()->json([
                'status' => true,
                'message' => 'All user posts.',
                'posts' => $posts], 200);
        } catch (\Exception $e) {
            dd($e);
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
            $content = $request->input('content');
            $postpicture = $request->file('postpicture');

            date_default_timezone_set('Asia/Jakarta');

            // Checking if content and postpicture are empty.
            if (!$content && !$postpicture) {
                return response()->json([
                    'status' => false,
                    'messsage' => 'Post and/or Post Picture can\'t be empty.'], 400);
            }

            $create = Post::create([
                'userid' => $userid,
                'datetime' => date('Y-m-d H:i:s'),
                'content' => $content,
                'postpicture' => null,
                'likes' => 0,
                'comments' => 0
            ]);

            $newpostid = $create->postid;

            if($postpicture) {
                $postpicturename = time().'.'.$postpicture->getClientOriginalExtension();
                $postpicture->storeAs('postpictures', $postpicturename, 'public');
                Post::where('postid', $newpostid)->update(['postpicture' => 'http://localhost:8000/storage/postpictures/' . $postpicturename]);
            }

            return response()->json([
                'status' => true,
                'messsage' => 'Post has been created',
                'postid' => $newpostid
            ], 200);
        } catch (\Exception $e) {
            dd($e);
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
            ->select('postid', 'userid', 'datetime', 'content', 'postpicture', 'likes', 'comments')
            ->get();

            return response()->json([
                'status' => true,
                'message' => 'Post details.',
                'details' => $details
            ], 200);
        } catch (\Exception $e) {
            dd($e);
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
            $content = $request->input('content');
            $postpicture = $request->file('postpicture');

            date_default_timezone_set('Asia/Jakarta');

            // Checking if content and postpicture are empty.
            if (!$content && !$postpicture) {
                return response()->json([
                    'status' => false,
                    'messsage' => 'Post and/or Post Picture can\'t be empty.'], 400);
            }
            
            $update = Post::where('postid', $postid)
            ->where('userid', $userid)
            ->update([
                'datetime' => date('Y-m-d H:i:s'),
                'content' => $content,
            ]);

            if($postpicture) {
                $postpicturename = time().'.'.$postpicture->getClientOriginalExtension();
                $postpicture->storeAs('postpictures', $postpicturename, 'public');
                Post::where('postid', $postid)->update(['postpicture' => 'http://localhost:8000/storage/postpictures/' . $postpicturename]);
            }

            return response()->json([
                'status' => true,
                'messsage' => 'Post has been updated.'
            ], 200);
        } catch (\Exception $e) {
            dd($e);
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

            // Check if post dosent belong to the user.
            $post = Post::where([
                'userid' => $userid,
                'postid' => $postid
            ])->first();

            if(!$post){
                return response()->json([
                    'status' => false,
                    'message' => 'You can only delete your posts.'
                ], 422);
            }
            // Check if post dosent belong to the user.

            $delete = Post::where([
                'postid' => $postid,
                'userid' => $userid
            ])->delete();

            return response()->json([
                'status' => true,
                'messege' => 'your post has been deleted'
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