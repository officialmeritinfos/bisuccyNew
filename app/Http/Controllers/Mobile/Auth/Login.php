<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\EmailVerification;
use App\Notifications\LoginMail;
use App\Notifications\TwoFactorMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class Login extends BaseController
{
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email'=>['required','email','exists:users,email'],
            'password'=>['required',Password::min(8)->letters()->mixedCase()->symbols()],
            'deviceId'=>['nullable','string']
        ])->stopOnFirstFailure();
        if($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],'422');
        }
        $input=$validator->validated();
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user  = Auth::user();
            if ($user->emailVerified !=1){
                $response =[
                    'name'=>$user->name,
                    'hasToken'=>true,
                    'redirect_to'=>'email-verification',
                    'needsEmailVerification'=>true,
                    'token'=>$user->createToken(config('app.name'),['user:emailVerify'])->plainTextToken
                ];
                $user->notify(new EmailVerification($user));
                $message='You need to activate your account first before proceeding.';
                return $this->sendResponse($response, $message);
            }


            switch ($user->twoFactor){
                case 1:
                    $token = $user->createToken(config('app.name'),['user:twoFactor'])->plainTextToken;
                    $dataUser = ['twoWayPassed' =>2,'isLoggedIn'=>2,'deviceId'=>$input['deviceId']];
                    User::where('id',$user->id)->update($dataUser);
                    $user->notify(new TwoFactorMail($user));

                    $success['name'] =  $user->name;
                    $success['needAuth'] = true;
                    $success['token'] =  $token;
                    $success['hasToken']=true;
                    $success['loggedIn'] = false;
                    $success['redirect_to']='twoFactor';
                    return $this->sendResponse($success, 'Authentication needed');
                    break;
                default:
                    $token = $user->createToken(config('app.name'),['user:account'])->plainTextToken;
                    $dataUser = ['twoWayPassed' =>1,'isLoggedIn'=>1,'deviceId'=>$input['deviceId']];
                    User::where('id',$user->id)->update($dataUser);
                    $user->notify(new LoginMail($user->name,$request->ip()));

                    $success['name'] =  $user->name;
                    $success['needAuth'] = false;
                    $success['token'] =  $token;
                    $success['hasToken']=true;
                    $success['loggedIn'] = true;
                    $success['addedPhone']=(empty($user->phone))?false:true;
                    $success['redirect_to']='dashboard';
                    return $this->sendResponse($success, 'Login successful');
            }
        }
        return $this->sendError('authorization.error.', ['error'=>'Wrong Password'],
            '403');
    }
}
