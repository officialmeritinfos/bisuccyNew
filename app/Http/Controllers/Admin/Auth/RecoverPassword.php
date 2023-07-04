<?php

namespace App\Http\Controllers\Admin\Auth;

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

class RecoverPassword extends BaseController
{
    public function landingPage()
    {

    }

    public function processEmail(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email'=>['required','email','exists:users,email'],
        ])->stopOnFirstFailure();
        if($validator->fails()){
            return back()->with('errors',$validator->errors()->all());
        }
        $input=$validator->validated();

        $user = User::where('email',$input['email'])->first();

        Auth::login($user);//temporarily login user

        $user->notify(new PasswordResetMail($user));

        $success['name'] =  $user->name;
        $success['needAuth'] = true;
        $success['hasToken']=true;
        $success['redirect_to']=route('auth.verifyPasswordRecovery');
        return redirect()->route('auth.verifyPasswordRecovery')->with('success','Authentication needed');
    }

    public function enterCode()
    {
        return view('auth.verify-password-recovery');
    }

    public function processResetCode(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'code'=>['required','numeric'],
        ])->stopOnFirstFailure();
        if($validator->fails()){
            return back()->with('errors',$validator->errors()->all());
        }
        $input=$validator->validated();

        $codeExists = PasswordReset::where(['email'=>$user->email,'user'=>$user->id])
            ->orderBy('created_at','desc')->first();
        if (empty($codeExists)){
            return back()->with('errors','Invalid token');
        }
        if (sha1($input['code'])!=$codeExists->token){
            return back()->with('errors','Invalid code');
        }
        $success['name'] =  $user->name;
        $success['needAuth'] = false;
        $success['redirect_to']=route('auth.changePassword');
        return redirect()->route('auth.changePassword')->with('success','Authentication successful. proceed to change your password');
    }
    public function recoverPassword()
    {
        return view('auth.change-password');
    }

    public function doChange(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'password'=>['bail','required',
                Password::min(8)->letters()->mixedCase()->symbols()->uncompromised(2)
            ],
            'password_confirmation'=>['required','same:password']
        ])->stopOnFirstFailure();
        if($validator->fails()){
            return back()->with('errors',$validator->errors()->all());
        }
        $input=$validator->validated();

        $data=[
            'password'=>bcrypt($input['password'])
        ];

        if (User::where('id',$user->id)->update($data)){

            $success['name'] =  $user->name;
            $success['hasToken']=false;
            $success['redirect_to']=route('auth.login');

            PasswordReset::where('email',$user->email)->delete();

            $user->notify(new PasswordChangedMail($user));

            return redirect()->route('auth.login')->with('success','Password successfully changed');;

        }
        return back()->with('errors','Something went wrong');
    }
}
