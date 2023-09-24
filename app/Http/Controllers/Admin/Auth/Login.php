<?php

namespace App\Http\Controllers\Admin\Auth;

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
    public function landingPage()
    {
        return view('auth.login');
    }

    public function doLogin(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email'=>['required','email','exists:users,email'],
            'password'=>['required',Password::min(8)->uncompromised(2)]
        ])->stopOnFirstFailure();
        if($validator->fails()){
            return back()->with('errors',$validator->errors()->all());
        }
        $input=$validator->validated();
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user  = Auth::user();
            if ($user->isAdmin !=1){
                $response =[
                    'name'=>$user->name,
                    'hasToken'=>true,
                    'redirect_to'=>route('auth.login'),
                ];
                $message='You do not have the clearance to access this page';
                $dataUpdate = ['twoWayPassed'=>2];

                User::where('id',$user->id)->update($dataUpdate);
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->with('errors',$message);
            }

            switch ($user->twoFactor){
                case 1:
                    $dataUser = ['twoWayPassed' =>2,'isLoggedIn'=>2];
                    User::where('id',$user->id)->update($dataUser);
                    $user->notify(new TwoFactorMail($user));

                    $success['name'] =  $user->name;
                    $success['needAuth'] = true;
                    $success['hasToken']=true;
                    $success['loggedIn'] = false;
                    $success['redirect_to']=route('auth.twoFactor');
                    return redirect()->route('auth.twoFactor');
                    break;
                default:
                    $dataUser = ['twoWayPassed' =>1,'isLoggedIn'=>1];
                    User::where('id',$user->id)->update($dataUser);
                    $user->notify(new LoginMail($user->name,$request->ip()));

                    $success['name'] =  $user->name;
                    $success['needAuth'] = false;
                    $success['hasToken']=true;
                    $success['loggedIn'] = true;
                    $success['redirect_to']=route('admin.index');
                    return redirect()->route('admin.index');
            }
        }
        return back()->with('errors','Wrong Password');
    }

    public function logout(Request $request)
    {

        $user = Auth::user();

        $user->isLoggedIn = 2;

        $user->save();

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('auth.login')->with('info','Logout was successful');
    }
}
