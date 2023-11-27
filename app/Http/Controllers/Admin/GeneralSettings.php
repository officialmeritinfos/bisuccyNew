<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\AdminMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GeneralSettings extends BaseController
{
    public function landingPAge()
    {
        return view('settings.index');
    }

    public function getSettings()
    {
        $web=GeneralSetting::find(1);

        $dataResponse=[
            'name'=>$web->name,
            'email'=>$web->email,
            'phone'=>$web->phone,
            'maintenance'=>($web->maintenance==1)?'1':'0',
            'registration'=>($web->registration==1)?'1':'0',
            'emailVerification'=>($web->emailVerification==1)?'1':'0',
            'phoneVerification'=>($web->phoneVerification==1)?'1':'0',
            'twoFactor'=>($web->twoFactor==1)?'1':'0',
            'withdrawalCharge'=>$web->withdrawalCharge,
            'depositCharge'=>$web->depositCharge,
            'sellCharge'=>$web->sellCharge,
            'buyCharge'=>$web->buyCharge,
            'canSend'=>($web->canSend==1)?'1':'0',
            'canDeposit'=>($web->canDeposit)?'1':'0',
            'canSell'=>($web->canSell)?'1':'0',
            'canBuy'=>($web->canBuy)?'1':'0',
            'canSwap'=>($web->canSwap)?'1':'0',
            'mainCurrency'=>$web->mainCurrency,
            'refBonus'=>$web->refBonus
        ];

        return $this->sendResponse($dataResponse,'retrieved');
    }

    public function editSettings(Request $request)
    {
        $admin = Auth::user();
        $web= GeneralSetting::find(1);
        $validator = Validator::make($request->all(),[
            'name'=>['required','string'],
            'email'=>['required','email'],
            'phone'=>['required','string'],
            'maintenance'=>['required','integer'],
            'registration'=>['required','integer'],
            'emailVerification'=>['required','integer'],
            'phoneVerification'=>['required','integer'],
            'twoFactor'=>['required','integer'],
            'withdrawalCharge'=>['required','numeric'],
            'depositCharge'=>['required','numeric'],
            'sellCharge'=>['required','numeric'],
            'buyCharge'=>['required','numeric'],
            'canSend'=>['required','integer'],
            'canDeposit'=>['required','integer'],
            'canSell'=>['required','integer'],
            'canBuy'=>['required','integer'],
            'canSwap'=>['required','integer'],
            'mainCurrency'=>['required','alpha'],
            'refBonus'=>['required','numeric'],
        ])->stopOnFirstFailure();
        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input = $validator->validated();

        $data=[
            'name'=>$input['name'],
            'email'=>$input['name'],
            'phone'=>$input['name'],
            'maintenance'=>$input['name'],
            'registration'=>$input['name'],
            'emailVerification'=>$input['name'],
            'phoneVerification'=>$input['name'],
            'twoFactor'=>$input['name'],
            'withdrawalCharge'=>$input['name'],
            'depositCharge'=>$input['name'],
            'sellCharge'=>$input['name'],
            'buyCharge'=>$input['name'],
            'canSend'=>$input['name'],
            'canDeposit'=>$input['name'],
            'canSell'=>$input['name'],
            'canBuy'=>$input['name'],
            'canSwap'=>$input['name'],
            'mainCurrency'=>$input['name'],
            'refBonus'=>$input['refBonus'],
        ];
        if (GeneralSetting::where('id',$web->id)->update($data)){
            $superAdminMessage="
                Your platform settings was updated by ".$admin->name."
            ";
            $dataNotifyAdmin=[
                'title'=>'Settings Update','content'=>'Website settings were updated',
                'user'=>$admin->id,'showAdmin'=>1
            ];
            Notification::create($dataNotifyAdmin);
            //send mail to the user
            $superAdmin=User::where(['isAdmin'=>1,'role'=>1])->first();
            if (!empty($superAdmin)){
                $superAdmin->notify(new AdminMail($superAdmin,$superAdminMessage,'Website settings updated.'));
            }
            return $this->sendResponse($data,'Settings updated');
        }
        return $this->sendError('gettings.error',['error'=>'Something went wrong']);
    }
}
