<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\PasswordChangedMail;
use App\Notifications\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ResetPassword extends BaseController
{
    /**
     * Send verification email to check that the email was correct
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email'=>['required','email','exists:users,email'],
        ])->stopOnFirstFailure();
        if($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],'422');
        }
        $input=$validator->validated();

        $user = User::where('email',$input['email'])->first();

        $token = $user->createToken(config('app.name'),['user:verifyReset'])->plainTextToken;
        $user->notify(new PasswordResetMail($user));

        $success['name'] =  $user->name;
        $success['needAuth'] = true;
        $success['token'] =  $token;
        $success['hasToken']=true;
        $success['redirect_to']='verify-password-reset';
        return $this->sendResponse($success, 'Authentication needed');
    }

    /**
     * Authenticate the password reset verification code
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticatePasswordResetCode(Request $request)
    {
        $user = Auth::user();

        if (!$user->tokenCan('user:verifyReset')) {
            return $this->sendError('validation.error',['error'=>'Access denied. Please start over again.'],422);
        }

        $validator = Validator::make($request->all(),[
            'code'=>['required','numeric'],
        ])->stopOnFirstFailure();
        if($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],'422');
        }
        $input=$validator->validated();

        $codeExists = PasswordReset::where(['email'=>$user->email,'user'=>$user->id])
            ->orderBy('created_at','desc')->first();
        if (empty($codeExists)){
            return $this->sendError('validation.error',['error'=>'Invalid token'],'422');
        }
        if (sha1($input['code'])!=$codeExists->token){
            return $this->sendError('validation.error',['error'=>'Invalid Code'],'422');
        }
        $success['name'] =  $user->name;
        $success['needAuth'] = false;
        $success['token'] =  $user->createToken(config('app.name'),['user:resetPassword'])->plainTextToken;
        $success['hasToken']=true;
        $success['redirect_to']='change-password';
        return $this->sendResponse($success, 'Authentication needed');
    }

    /**
     * Change password
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ResetPassword(Request $request)
    {
        $user = Auth::user();
        if (!$user->tokenCan('user:resetPassword')) {
            return $this->sendError('validation.error',['error'=>'Access denied. Please start over again.'],422);
        }
        $validator = Validator::make($request->all(),[
            'password'=>['bail','required',
                Password::min(8)->letters()->mixedCase()->symbols()->uncompromised(2)
            ],
            'password_confirmation'=>['required','same:password']
        ])->stopOnFirstFailure();
        if($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],'422');
        }
        $input=$validator->validated();

        $data=[
            'password'=>bcrypt($input['password'])
        ];

        if (User::where('id',$user->id)->update($data)){

            $success['name'] =  $user->name;
            $success['hasToken']=false;
            $success['redirect_to']='login';

            auth()->user()->tokens()->delete();
            PasswordReset::where('email',$user->email)->delete();

            $user->notify(new PasswordChangedMail($user));
            return $this->sendResponse($success, 'Password changed');
        }
        return $this->sendError('password.error',['error'=>'Something went wrong'],421);
    }

    public function resendPasswordReset(Request $request)
    {
        $user = Auth::user();
        if (!$user->tokenCan('user:verifyReset')) {
            return $this->sendError('validation.error',['error'=>'Access denied. Please start over again.'],422);
        }
        $user->notify(new PasswordResetMail($user));

        $success['name'] =  $user->name;
        $success['token'] =  $request->bearerToken();
        return $this->sendResponse($success, 'Password Reset Sent code resent.');
    }
}
