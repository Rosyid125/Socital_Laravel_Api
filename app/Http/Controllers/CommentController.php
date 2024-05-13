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
use App\Models\Notification;

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

            // Increment comments from table posts.
            $incrementcomments = Post::where('postid', $postid)
            ->increment('comments');

            // Add new notification row to table notifications.
            $postmaker = Post::where('postid', $postid)
            ->value('userid');
            
            // If user comments on own post then wont make any notification.
            if($postmaker != $userid){
                $notification = Notification::create([
                    'userid' => $postmaker,
                    'trigerrerid' => $userid,
                    'notification' => 'commented on your post \'{$comment}\'.',
                    'datetime' => date('Y-m-d H:i:s'),
                    'status' => 'unread',
                ]);
            }
            // Add new notification row to table notifications.

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

            // Decrement comments from table posts.
            // Checking if theres no comments on the post.
            $comments = Post::where('postid', $postid)
            ->value('comments');

            if($comments > 0){
                $decrementcomments = Post::where('postid', $postid)
                ->decrement('comments');
            }
            // Decrement comments from table posts.

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
            ->orderBy('datetime', 'desc')
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
