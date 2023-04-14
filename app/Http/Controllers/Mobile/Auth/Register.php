<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Models\UserReferral;
use App\Notifications\EmailVerification;
use App\Notifications\WelcomeMail;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class Register extends BaseController
{
    use PubFunctions;

    public function authenticate(Request $request)
    {
        $web = GeneralSetting::find(1);
        $validator = Validator::make($request->all(),[
            'name'=>['bail','required','string'],
            'email'=>['bail','email','required','unique:users,email'],
            'password'=>[
                'bail',
                'required',
                Password::min(8)->letters()->mixedCase()->symbols()
            ],
            'referral'=>['bail','nullable','alpha_num','exists:users,userRef'],
            'deviceId'=>['nullable','string']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input = $validator->validated();
        //create some resources
        $reference = $this->generateRef('users','userRef',8);
        if ($request->filled('referral')){
            $referrer =User::where('userRef',$input['referral'])->first();
            $hasRef = 1;
        }else{
            $hasRef =2;
        }
        //collate the user data
        $dataUser = [
            'name'=>$input['name'],
            'email'=>$input['email'],
            'password'=>bcrypt($input['password']),
            'refBy'=>($hasRef==1)?$input['referral']:'',
            'regIp'=>$request->ip(),
            'userRef'=>$reference,
            'deviceId'=>$input['deviceId']
        ];

        $user = User::create($dataUser);
        if (!empty($user)){
            if ($hasRef==1){
                $dataRef=[
                    'user'=>$user->id,'referrer'=>$referrer->id
                ];
                UserReferral::create($dataRef);
            }
            //send welcome mail to user
            switch ($web->EmailVerification){
                case 1:
                    $response =[
                        'name'=>$user->name,
                        'hasToken'=>false,
                        'redirect_to'=>'login',
                        'needsEmailVerification'=>false
                    ];
                    $user->notify(new WelcomeMail($user->name));
                    break;
                default:
                    $token = $user->createToken(config('app.name'),['user:emailVerify'])->plainTextToken;
                    $response =[
                        'name'=>$user->name,
                        'hasToken'=>true,
                        'redirect_to'=>'email-verification',
                        'needsEmailVerification'=>true,
                        'token'=>$token
                    ];
                    $user->notify(new EmailVerification($user));
                    break;
            }
            return $this->sendResponse($response,'account created',201);
        }
        return $this->sendError('user.error',['error'=>'Unable to create account'],400);
    }
}
