<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function getNotifications(Request $request){
        try{
            $userid = $request->route('userid');
            
            $notifications = Notification::where('userid', $userid)
            ->with(['user' => function ($query) {
                $query->select('userid','username', 'profilepicture');
            }])
            ->select('notificationid', 'notification', 'datetime', 'status')
            ->orderBy('notificationid', 'desc')
            ->get();

            return response()->json([
                'status' => true,
                'message' => 'Notifications retrieved successfully.',
                'notifications' => $notifications
            ]);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    public function markAsRead(Request $request){
        try{
            $userid = Auth::user()->userid;
            $notificationid = $request->route('notificationid');

            $notification = Notification::where('userid', $userid)
            ->where('notificationid', $notificationid)
            ->update([
                'status' => 'read'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Notification marked as read.'
            ]);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }
}
