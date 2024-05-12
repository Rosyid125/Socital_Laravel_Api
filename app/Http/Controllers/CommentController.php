<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Models\Comment;
use App\Models\Post;

class CommentController extends Controller
{

    public function addComment(Request $request){
        try {
            $validatedData = $request->validate([
                'comment'=> 'required'
            ]);

            $postid = $request->route('postid');
            $datetime = date('Y-m-d H:i:s');
            $userid = Auth::user()->userid;
            $comment = $validatedData['comment'];

            $createcomment = Comment::create([
                'postid' => $postid,
                'userid' => $userid,
                'datetime' => $datetime,
                'comment' => $comment
            ]);


            if(!$createcomment){
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to add comment.'
                ], 400);
            }

            $incrementcomments = Post::where('postid', $postid)
            ->where('userid', $userid)
            ->increment('comments');

            $newcommentid = $createcomment->commentid;

            return response()->json([
                'status' => true,
                'messege' => 'You commented on the post.',
                'commentid' => $newcommentid
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function deleteComment(Request $request){
        try {
            $postid = $request->route('postid');
            $userid = Auth::user()->userid;
            $commentid = $request->route('commentid');

            $deletecomment = Comment::where([
                'userid' => $userid,
                'postid' => $postid,
                'commentid' => $commentid
            ])
            ->delete();

            if(!$deletecomment){
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to delete comment.'
                ], 400);
            }

            $decrementcomments = Post::where('postid', $postid)
            ->where('userid', $userid)
            ->decrement('comments');

            return response()->json([
                'status' => true,
                'message' => 'Comment has been deleted.'
            ], 200);
        }catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function getComments(Request $request){
        try {
            $postid = $request->route('postid');

            $comments = Comment::with(['user' => function ($query) {
                $query->select('userid','username', 'profilepicture');
            }])
            ->where('postid', $postid)
            ->select('commentid', 'userid', 'datetime', 'comment')
            ->get();

            return response()->json([
                'status' => true,
                'message' => 'Comments retrieved successfully.',
                'comments' => $comments
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
