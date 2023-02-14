<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification\NotificationModel;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function notification(Request $request)
    {
        $limit = $request->limit;
        $id =  auth('sanctum')->user()->id;
        $list = NotificationModel::where('users_id', $id)->where('category', 'notification')->orderby('id', 'desc')->paginate($limit);
        $response['status'] = 200;
        $response['message'] = 'List Notification';
        $response['payload'] = $list;

        return response()->json($response);
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
