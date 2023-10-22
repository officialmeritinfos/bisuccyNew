<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBank;
use Illuminate\Http\Request;

class UserBanks extends BaseController
{
    public function landingPage()
    {
        return view('users.banks');
    }

    public function getBanks($index=0)
    {
        $banks = UserBank::offset($index*50)->limit(50)->get();
        if ($banks->count()<1){
            return $this->sendError('account.error',['error'=>'No data found']);
        }

        $dataCo=[];
        foreach ($banks as $bank) {
            $user = User::where('id',$bank->user)->first();
            $data = [
                'id'=>$bank->id,'bank'=>$bank->bank,
                'accountName'=>$bank->accountName,
                'accountNumber'=>$bank->accountNumber,
                'status'=>($bank->status==1)?'active':'inactive',
                'dateCreated'=>strtotime($bank->created_at),
                'user'=>$user->name,'userId'=>$user->id,'userRef'=>$user->userRef
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function getBanksByUser($user,$index=0)
    {
        $banks = UserBank::where('user',$user)->offset($index*50)->limit(50)->get();
        if ($banks->count()<1){
            return $this->sendError('account.error',['error'=>'No data found']);
        }

        $dataCo=[];
        foreach ($banks as $bank) {
            $user = User::where('id',$bank->user)->first();
            $data = [
                'id'=>$bank->id,'bank'=>$bank->bank,
                'accountName'=>$bank->accountName,
                'accountNumber'=>$bank->accountNumber,
                'status'=>($bank->status==1)?'active':'inactive',
                'dateCreated'=>strtotime($bank->created_at),
                'user'=>$user->name,'userId'=>$user->id,'userRef'=>$user->userRef
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
}
