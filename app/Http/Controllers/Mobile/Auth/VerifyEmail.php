<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Models\User;
use App\Notifications\WelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VerifyEmail extends BaseController
{
    public function authenticate(Request $request)
    {
        $user = Auth::user();
        if (!$user->tokenCan('user:emailVerify')) {
            return $this->sendError('validation.error',['error'=>'Access denied. Please start over again.'],422);
        }
        $validator = Validator::make($request->all(),[
            'code'=>['required','numeric']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input = $validator->validated();

        if ($user->emailVerified!=1){
            $exists = EmailVerification::where(['user'=>$user->id,'email'=>$user->email])
                ->orderBy('id','desc')
                ->first();
            if (empty($exists)){
                return $this->sendError('email.verification.error',['error'=>'invalid request'],422);
            }
            if (sha1($input['code']) !=$exists->token){
                return $this->sendError('email.verification.error',['error'=>'invalid code'],422);
            }

            $dataUser=[
                'emailVerified'=>1,'email_verified_at'=>$user->markEmailAsVerified()
            ];
            User::where('id',$user->id)->update($dataUser);



            $user->notify(new WelcomeMail($user->name));
            $user->tokens()->delete();

            $dataResponse = [
                'name'=>$user->name,
                'hasToken'=>false,
                'redirect_to'=>'login',
            ];

            EmailVerification::where('user',$user->id)->delete();
            return $this->sendResponse($dataResponse,'Email successfully verified.');
        }
        return $this->sendError('email.verification.error',['error'=>'email already verified'],422);
    }

    public function resendVerificationMail(Request $request)
    {
        $user = Auth::user();
        if (!$user->tokenCan('user:emailVerify')) {
            return $this->sendError('validation.error',['error'=>'Access denied. Please start over again.'],422);
        }
        if ($user->emailVerified!=1){
            $user->notify(new \App\Notifications\EmailVerification($user));
            //resend the email verification
            $dataResponse=[
                'name'=>$user->name,
                'hasToken'=>true,
                'needsEmailVerification'=>true,
                'token'=>$request->bearerToken()
            ];
            return $this->sendResponse($dataResponse,'Verification mail resent');
        }
        return $this->sendError('email.verification.error',['error'=>'email already verified'],422);
    }
}
