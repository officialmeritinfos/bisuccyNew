<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\LoginMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TwoFactor extends BaseController
{
    public function landingPage()
    {
        return view('auth.two-factor');
    }

    public function authenticate(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(),[
            'code'=>['required','numeric']
        ])->stopOnFirstFailure();
        if($validator->fails()){
            return back()->with('errors',$validator->errors()->all());
        }
        $input = $validator->validated();

        $twoWay = \App\Models\TwoFactor::where('user',$user->id)->orderBy('id','desc')->first();
        if (empty($twoWay)){
            return back()->with('errors','Unauthorized user');
        }
        if (sha1($input['code']) !=$twoWay->token){
            return back()->with('errors','Invalid Code');
        }
        $dataUser = ['twoWayPassed' =>1];
        $updated = User::where('id',$user->id)->update($dataUser);
        if (empty($updated)){
            return back()->with('errors','unable to verify data');
        }
        \App\Models\TwoFactor::where('user',$user->id)->delete();
        $user->notify(new LoginMail($user->name,$request->ip()));
        auth()->user()->tokens()->delete();

        $token = $user->createToken(config('app.name'),['user:account'])->plainTextToken;

        $success['name'] =  $user->name;
        $success['loggedIn'] = true;
        $success['token'] = $token;
        $success['redirect_to']=route('admin.index');
        return redirect()->route('admin.index')->with('success','Login was successful');;
    }
}
