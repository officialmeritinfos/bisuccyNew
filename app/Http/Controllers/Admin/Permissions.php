<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Permissions extends BaseController
{
    public function landingPage()
    {
        return view('staff.roles.index');
    }

    public function createRoleLandingPage()
    {
        return view('staff.roles.create');
    }


    //add role
    public function createRole(Request $request)
    {
        $admin = Auth::user();
        $validator = Validator::make($request->all(),[
            'name'=>['required','string'],
            'users'=>['required','integer'],
            'staff'=>['required','integer'],
            'account'=>['required','integer'],
            'fundUser'=>['required','integer'],
            'signal'=>['required','integer'],
            'pin'=>['required']
        ],[],[
            'users'=>'can control user accounts',
            'staff'=>'has access to staff',
            'account'=>'accounting role',
            'fundUser'=>'can fund and debit user',
            'signal'=>'signal room permissions'
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
        $data = [
            'name'=>$input['name'],
            'users'=>$input['users'],
            'staff'=>$input['staff'],
            'account'=>$input['account'],
            'fundUser'=>$input['fundUser'],
            'signal'=>$input['signal']
        ];
        $role = Permission::create($data);
        if (!empty($role)){

            $dataResponse = $this->roleData($role);
            return $this->sendResponse($dataResponse,'role successfully added');
        }
        return $this->sendError('permission.error',['error'=>'Something went wrong'],421);
    }

    public function getRoles()
    {
        $roles = Permission::get();
        if ($roles->count()<1){
            return $this->sendError('account.error',['error'=>'No data found']);
        }

        $dataCo=[];
        foreach ($roles as $role) {
            $data = $this->roleData($role);
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    /**
     * @param $role
     * @return array
     */
    private function roleData($role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'users' => ($role->users == 1) ? 'yes' : 'no',
            'staff' => ($role->staff == 1) ? 'yes' : 'no',
            'account' => ($role->account == 1) ? 'yes' : 'no',
            'fundUser' => ($role->fundUser == 1) ? 'yes' : 'no',
            'signal' => ($role->signal == 1) ? 'yes' : 'no',
        ];
    }
}
