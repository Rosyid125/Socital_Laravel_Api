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
use App\Models\Friend;

class PostController extends Controller
{
    public function getallposts(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'userid' => 'required',
            ]);
    
            $userid = $validatedData['userid'];
            $userid12 = Friend::select('userid1', 'userid2')
            ->where('userid1', $userid)
            ->orWhere('userid2', $userid)
            ->get();
            
            $posts = Post::where(function ($query) use ($userid12) {
                $query->whereIn('userid', $userid12->pluck('userid1'))
                      ->orWhereIn('userid', $userid12->pluck('userid2'));
            })->get();
            return response()->json(["posts" => $posts], 200);

        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function getalluserposts(Request $request)
    {
        try {
            $userid = $request->userid;

            $posts = Post::where('userid', $userid)->get();

            return response()->json(["posts" => $posts], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function addapost(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'userid' => 'required',
            ]);

            do {
                $postid = Str::random(10);
            } while (Post::where('postid', $postid)->exists()); //ini saya buat random supaya bisa diambil, nah kalau userid tidak perlu ini karena ada data yang unique, yaitu email, jadi bisa diambil dari situ ketika query, sedangkan ini tidak bisa (perulangan untuk menghindari duplikasi).
    
            $userid = $validatedData['userid'];
            $post = $request->input('post');
            $postpic = $request->input('postpic');            

            $create = Post::create([
                'userid' => $userid,
                'post' => $post,
                'postpic'=> $postpic,
                'postid' => $postid,
            ]);

            if (!$create) {
                return response()->json(['messsage' => 'Can\'t create post'], 400);
            } else {
                return response()->json([
                    'messsage' => 'Post has been created',
                    'postid' => $postid
                ], 200);
            }

        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 422);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function editapost(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'userid' => 'required',
            ]);
    
 
            $postid = $request->postid;
            $userid = $validatedData['userid'];
            $post = $request->input('post');
            $postpic = $request->input('postpic');    

            $update = Post::where('postid', $postid)
            ->where('userid', $userid)
            ->update([
                'post' => $post,
                'postpic' => $postpic,
            ]);

            if (!$update) {
                return response()->json(['messsage' => 'Post is not existed or this is not your post'], 400);
            } else {
                return response()->json(['messsage' => 'Post has been updated'], 200);
            }

        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function deleteapost(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'userid' => 'required',
            ]);
    
            $postid = $request->postid;
            $userid = $validatedData['userid'];

            $delete = Post::where('userid', $userid)->where('postid', $postid)->delete();

            if(!$delete){
                return response()->json(['messsage' => 'Post is not existed or this is not your post'], 400);
            } else {
                return response()->json(["messege" => "your post has been deleted"], 200);
            }

        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
}