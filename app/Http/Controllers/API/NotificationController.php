<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification\NotificationModel;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function notification(Request $request)
    {
        $limit = $request->limit ?? 10;
        $userId = auth('sanctum')->user()->id ?? 0;
        // Mengambil list notifikasi untuk user tertentu berdasarkan category 'notification'
        $list = NotificationModel::where('users_id', $userId)
            ->where('category', 'notification')
            ->orderBy('id', 'desc')
            ->paginate($limit);

        $response = [
            'status'  => 200,
            'message' => 'List Notification',
            'payload' => $list,
        ];

        return response()->json($response);
    }

    /**
     * Mark all notifications for the authenticated user as read.
     */
    public function readNotif(Request $request)
    {
        $userId = auth('sanctum')->user()->id ?? 0;

        // Jika ada parameter id, update notifikasi tersebut
        if ($request->has('id') && !empty($request->id)) {
            $notificationId = $request->id;
            $affected = NotificationModel::where('id', $notificationId)
                ->where('users_id', $userId)
                ->where('isRead', 0)
                ->update(['isRead' => 1]);

            if ($affected) {
                return response()->json([
                    'status'  => 200,
                    'message' => 'Notification marked as read'
                ]);
            } else {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Notification not found or already read'
                ]);
            }
        } else {
            // Jika tidak ada parameter id, update semua notifikasi yang belum dibaca
            $affected = NotificationModel::where('users_id', $userId)
                ->where('isRead', 0)
                ->update(['isRead' => 1]);

            return response()->json([
                'status'  => 200,
                'message' => 'All notifications marked as read',
                'affected_count' => $affected // Jumlah baris yang diupdate
            ]);
        }
    }

    public function highlight(Request $request)
    {
        $limit = $request->limit;
        $id =  auth('sanctum')->user()->id;
        $list = NotificationModel::where('users_id', $id)->where('category', 'highlight')->orderby('id', 'desc')->paginate($limit);
        $response['status'] = 200;
        $response['message'] = 'List Notification';
        $response['payload'] = $list;

        return response()->json($response);
    }
}
