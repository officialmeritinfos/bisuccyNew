<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\LoginMail;
use App\Notifications\TwoFactorMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TwoFactor extends BaseController
{
    public function authenticate(Request $request)
    {
        $user = Auth::user();
        if (!$user->tokenCan('user:twoFactor')) {
            return $this->sendError('validation.error',['error'=>'Access denied. Please start over again.'],422);
        }
        $validator = Validator::make($request->all(),[
            'code'=>['required','numeric']
        ])->stopOnFirstFailure();
        if($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],'422');
        }
        $input = $validator->validated();

        $twoWay = \App\Models\TwoFactor::where('user',$user->id)->orderBy('id','desc')->first();
        if (empty($twoWay)){
            return $this->sendError('verification.error',['error'=>'Invalid Code sent'],'422');
        }
        if (sha1($input['code']) !=$twoWay->token){
            return $this->sendError('authentication.error',['error'=>'Invalid Code sent'],'403');
        }
        $dataUser = ['twoWayPassed' =>1];
        $updated = User::where('id',$user->id)->update($dataUser);
        if (empty($updated)){
            return $this->sendError('verification.error',['error'=>'unable to verify data'],'403');
        }
        \App\Models\TwoFactor::where('user',$user->id)->delete();
        $user->notify(new LoginMail($user->name,$request->ip()));
        auth()->user()->tokens()->delete();

        $token = $user->createToken(config('app.name'),['user:account'])->plainTextToken;

        $success['name'] =  $user->name;
        $success['loggedIn'] = true;
        $success['token'] = $token;
        $success['redirect_to']='dashboard';
        $success['addedPhone']=(empty($user->phone))?false:true;
        return $this->sendResponse($success, 'Login was successful');
    }

    public function resendTwoFactor(Request $request)
    {
        $user = Auth::user();
        if (!$user->tokenCan('user:twoFactor')) {
            return $this->sendError('validation.error',['error'=>'Access denied. Please start over again.'],422);
        }
        if ($user->twoWayPassed!=1){
            $user->notify(new TwoFactorMail($user));

            $success['name'] =  $user->name;
            $success['token'] =  $request->bearerToken();
            return $this->sendResponse($success, 'Authentication code resent.');
        }
    }
}
