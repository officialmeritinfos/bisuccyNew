<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Messages extends BaseController
{
    public function landingPage()
    {
        return view('messages.index');
    }

    public function createMessageLanding()
    {
        return view('messages.create');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * title -> the title of the message to broadcast
     * content -> content of the broadcast
     * timeToBroadcast -> time to broadcast
     * type -> if it is mobile notification(1) or an email notification(2)
     */
    public function doCreateMessage(Request $request)
    {
        $admin = Auth::user();

        $validator = Validator::make($request->all(),[
            'title'=>['nullable','string'],
            'content'=>['required'],
            'timeToBroadcast'=>['nullable','date'],
            'type'=>['nullable','integer']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()]);
        }
        $input = $validator->validated();

        if (empty($input['timeToBroadcast'])){
            $time = time();
        }else{
            $time = strtotime($input['timeToBroadcast']);
        }

        if (empty($input['type'])){
            $type = 1;
        }else{
            $type = $input['type'];
        }

        $data = [
            'user'=>$admin->id,
            'type'=>$type,
            'content'=>$input['content'],
            'title'=>$input['title'],
            'timeToBroadcast'=>$time
        ];
        $message = Message::create($data);
        if (!empty($message)){

            $dataResponse = [
                'user'=>$admin->id,
                'content'=>$input['content'],
                'title'=>$input['title'],
                'timeToBroadcast'=>date('d-M-Y h:i:s',$time),
                'status'=>($message->broadCasted==1)?'completed':'pending'
            ];
            return $this->sendResponse($dataResponse, 'Notification created.');
        }
        return $this->sendError('message.error',['error'=>'Something went wrong']);
    }

    public function getMessages($index=0)
    {
        $admin = Auth::user();
        $notifications =  Message::offset($index*50)->limit(50)->get();

        $dataCo=[];

        foreach ($notifications as $notification) {
            $user = User::where('id',$notification->user)->first();

            $data=[
                'id'=>$notification->id,
                'title'=>$notification->title,
                'content'=>$notification->content,
                'createdAt'=>strtotime($notification->created_at),
                'staff'=>$user->name??'N/A','userId'=>$user->id??'N/A',
                'timeToBroadcast'=>$notification->timeToBroadcast,
                'status'=>($notification->broadCasted==1)?'completed':'pending'
            ];

            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function getMessageDetail($id)
    {
        $admin = Auth::user();
        $notification =  Message::where('id',$id)->first();
        $user = User::where('id',$notification->user)->first();

        $data=[
            'id'=>$notification->id,
            'title'=>$notification->title,
            'content'=>$notification->content,
            'createdAt'=>strtotime($notification->created_at),
            'staff'=>$user->name??'N/A','userId'=>$user->id??'N/A',
            'timeToBroadcast'=>$notification->timeToBroadcast,
            'status'=>($notification->broadCasted==1)?'completed':'pending'
        ];

        return $this->sendResponse($data, 'retrieved');
    }
}
