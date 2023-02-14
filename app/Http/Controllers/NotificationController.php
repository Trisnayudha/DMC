<?php

namespace App\Http\Controllers;

use App\Helpers\Notification;
use App\Models\Notification\NotificationModel;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $list = NotificationModel::orderBy('id', 'desc')->get();
        // dd($list);
        $data = [
            'list' => $list
        ];
        return view('admin.notification.index', $data);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->all_users == 'yes') {
            $users = User::get();
            foreach ($users as $users_id) {

                $data = NotificationModel::updateOrCreate(
                    [
                        'id' => $request->id
                    ],
                    [
                        'type' => $request->type,
                        'title' => $request->title,
                        'message' => $request->message,
                        'category' => $request->category,
                        'target_slug' => $request->target_id,
                        'all_users' => $request->all_users,
                        'users_id' => $users_id->id
                    ]
                );
                $notif = new Notification();
                $notif->message = $request->message;
                $notif->id = $users_id->id;
                $notif->NotifApp();
            }
        } else {
            foreach ($request->users_id as $users_id) {

                $data = NotificationModel::updateOrCreate(
                    [
                        'id' => $request->id
                    ],
                    [
                        'type' => $request->type,
                        'message' => $request->message,
                        'title' => $request->title,
                        'category' => $request->category,
                        'target_slug' => $request->target_id,
                        'all_users' => $request->all_users,
                        'users_id' => $users_id

                    ]
                );

                $notif = new Notification();
                $notif->message = $request->message;
                $notif->id = $users_id;
                $notif->NotifApp();
            }
        }

        return response()->json([
            'success' => true,
            'payload' => $request->all()
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $where = array('id' => $request->id);
        $data  = NotificationModel::where($where)->first();
        // activity()->log('Edit Data Kategori');
        return response()->json($data);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $data = NotificationModel::where('id', $request->id)->delete();
        // activity()->log('Menghapus Data Kategori');
        return response()->json(['success' => true]);
    }

    public function users()
    {
        $data = User::orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'payload' => $data
        ]);
    }
}
