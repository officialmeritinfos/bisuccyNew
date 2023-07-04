<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Permission;
use App\Models\User;
use App\Notifications\AdminMail;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Staff extends BaseController
{
    use PubFunctions;
    public function landingPage()
    {

    }

    //create staff
    public function addStaff(Request $request)
    {
        $admin = Auth::user();
        $validator = Validator::make($request->all(),[
            'name'=>['required','string'],
            'email'=>['required','email','unique:users,email'],
            'password'=>['required'],
            'role'=>['required','integer'],
            'pin'=>['required']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()]);
        }
        $input = $validator->validated();
        //check if the admin is capable
        $hashed = Hash::check($input['pin'],$admin->transPin);
        if (!$hashed){
            return $this->sendResponse('account.error',['error'=>'Invalid Pin']);
        }
        if ($admin->role!=1){
            return $this->sendResponse('staff.error',['error'=>'Unauthorized action: you do not have the permission for this.']);
        }
        //check the role assigned
        if ($input['role']!=1){
            $role = Permission::where('id',$input['role'])->first();
        }

        $userRef = $this->generateRef('users','userRef');
        $dataUser = [
            'name'=>$input['name'],
            'email'=>$input['email'],
            'userRef'=>$userRef,
            'isAdmin'=>1,
            'role'=>$input['role'],
            'emailVerified'=>1,
            'password'=>bcrypt($input['password'])
        ];

        $user = User::create($dataUser);
        if (!empty($user)){
            //notify the user
            $message = "
                Welcome to the ".env('APP_NAME')." staff management role. We are excited to have you here.<br>
                This is a welcome mail and contains your unique password, which we suggest you change once you log in
                to your portal.<br>
                <p>Your unique password is <b>".$input['password']."</b></p>. Use this to log in to your staff account here:
                <br>
                 <a href='".route('admin.login')."'>".route('admin.login')."</a>
            ";
            $user->notify(new AdminMail($user,$message,'Welcome to '.env('APP_NAME').' Staff Login'));

            $dataResponse = [
                'name'=>$user->name,
                'email'=>$user->email,
                'reference'=>$userRef,
                'role'=>($input['role']==1)?'superAdmin':$role->name
            ];
            return  $this->sendResponse($dataResponse,'staff added.');
        }
        return  $this->sendError('account.error',['error'=>'Something went wrong'],421);
    }

    public function getAllStaff($index=0)
    {
        $users = User::where('isAdmin',1)->where('role','!=',1)->offset($index*50)->limit(50)->get();
        if ($users->count()<1){
            return $this->sendError('account.error',['error'=>'No data found']);
        }

        $dataCo=[];
        foreach ($users as $user) {
            $data = [
                'id'=>$user->id,
                'reference'=>$user->userRef,
                'email'=>$user->email,
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    //get a particular staff
    public function getStaffDetail($id)
    {
        $user = User::where(['isAdmin'=>1,'id'=>$id])->where('role','!=',1)->first();
        if (empty($user)) {
            return $this->sendError('account.error', ['error' => 'No data found']);
        }
        $data = [
            'id'=>$user->id,
            'reference'=>$user->userRef,
            'email'=>$user->email,
            'registrationIp'=>$user->regIp,
            'country'=>$user->country,
            'phoneCode'=>$user->phoneCode,
            'phone'=>$user->phone,
            'state'=>$user->state,
            'city'=>$user->city,
            'address'=>$user->address,
            'accountCurrency'=>$user->mainCurrency,
            'twoFactor'=>($user->twoFactor==1)?'on':'off',
            'emailVerified'=>($user->emailVerified==1)?'yes':'no',
        ];
        return $this->sendResponse($data, 'retrieved');
    }
}
