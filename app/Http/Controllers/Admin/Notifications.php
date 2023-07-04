<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Notifications extends BaseController
{
    public function landingPage()
    {

    }

    public function getNotifications($index=0)
    {
        $admin = Auth::user();

        if($admin->isAdmin ==1 && $admin->role==1)
        {
           $notifications =  Notification::where('user', $admin->id)->orWhere('showAdmin',1)->offset($index*50)->limit(50)->get();
        }else{
            $notifications =  Notification::where('user', $admin->id)->offset($index*50)->limit(50)->get();
        }

        if ($notifications->count()<1){
            return $this->sendError('notification.error',['error'=>'No data found']);
        }
        $dataCo=[];

        foreach ($notifications as $notification) {
            $user = User::where('id',$notification->user)->first();

            $data=[
                'id'=>$notification->id,
                'title'=>$notification->title,
                'content'=>$notification->content,
                'createdAt'=>strtotime($notification->created_at),
                'staff'=>$user->name,'userId'=>$user->id
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function getNotificationDetail($id)
    {
        $admin = Auth::user();

        if($admin->isAdmin ==1 && $admin->role==1)
        {
            $notification =  Notification::where(['user'=>$admin->id,'id'=>$id])
                ->orWhere(['showAdmin'=>1,'id'=>$id])->first;
        }else{
            $notification =  Notification::where(['user'=>$admin->id,'id'=>$id])->first;
        }

        if (empty($notification)){
            return $this->sendError('notification.error',['error'=>'No data found']);
        }

        $user = User::where('id',$notification->user)->first();

        $data=[
            'id'=>$notification->id,
            'title'=>$notification->title,
            'content'=>$notification->content,
            'createdAt'=>strtotime($notification->created_at),
            'staff'=>$user->name,'userId'=>$user->id
        ];
        return $this->sendResponse($data, 'retrieved');
    }
}
