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
use App\Models\User;

class CommentController extends Controller
{

    public function addComment(Request $request){
        try {
            $validatedData = $request->validate([
                'comment'=> 'required'
            ]);

            date_default_timezone_set('Asia/Jakarta');

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

            // update comments from table posts.
            $countcomments = Comment::where('postid', $postid)->count();

            $updatecomments = Post::where('postid', $postid)->update(['comments' => $countcomments]);
            // update comments from table posts.

            // Add new notification row to table notifications.
            $postmaker = Post::where('postid', $postid)
            ->value('userid');
            
            // If user comments on own post then wont make any notification.
            if($postmaker != $userid){
                $triggererusername = User::where('userid', $userid)->select('username')->first();

                $notification = Notification::create([
                    'userid' => $postmaker,
                    'trigerrerid' => $userid,
                    'notification' => $triggererusername->username . ' commented on your post. \''. $comment . '\'',
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

            // Check if comment dosent belong to the user.
            $comment = Comment::where([
                'userid' => $userid,
                'commentid' => $commentid
            ])->first();

            if(!$comment){
                return response()->json([
                    'status' => false,
                    'message' => 'You can only delete your comments.'
                ], 422);
            }
            // Check if comment dosent belong to the user.

            $deletecomment = Comment::where([
                'userid' => $userid,
                'postid' => $postid,
                'commentid' => $commentid
            ])
            ->delete();

            // update comments from table posts.
            $countcomments = Comment::where('postid', $postid)->count();

            $updatecomments = Post::where('postid', $postid)->update(['comments' => $countcomments]);
            // update comments from table posts.

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
