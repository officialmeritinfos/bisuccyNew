<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\SystemFiatAccount;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SystemFiatAccounts extends BaseController
{
    use PubFunctions;
    //landing page
    public function landingPage()
    {

    }

    public function addAccount(Request $request)
    {
        $admin = Auth::user();

        $validator = Validator::make($request->all(),[
            'bank'=>['required','string'],
            'accountNumber'=>['required','numeric'],
            'accountName'=>['required','string'],
            'pin'=>['required','string']
        ])->stopOnfirstFailure();
        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input=$validator->validated();

        //check pin
        $hashed = Hash::check($input['pin'],$admin->transPin);
        if (!$hashed){
            return $this->sendError('security.error',['error'=>'Invalid account pin'],422);
        }

        $ref = $this->generateRef('system_fiat_accounts','reference',12);

        $data=[
            'reference'=>$ref,'bank'=>$input['bank'],
            'accountNumber'=>$input['accountNumber'],
            'accountName'=>$input['accountName'],
            'status'=>1
        ];

        $account = SystemFiatAccount::create($data);
        if (!empty($account)){
            $dataResponse=[
                'status'=>'added',
                'accountName'=>$account->accountName,
                'id'=>$account->id,
                'accountNumber'=>$account->accountNumber,
                'bank'=>$account->bank,
                'reference'=>$account->reference,
                'dateCreated'=>strtotime($account->created_at)
            ];
            return $this->sendResponse($dataResponse,'account added successfully.');
        }
        return $this->sendError('account.error',['error'=>'something went wrong']);
    }

    public function delete(Request $request)
    {
        $admin = Auth::user();

        $validator = Validator::make($request->all(),[
            'id'=>['required','integer'],
            'pin'=>['required','string']
        ])->stopOnfirstFailure();
        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input=$validator->validated();

        //check pin
        $hashed = Hash::check($input['pin'],$admin->transPin);
        if (!$hashed){
            return $this->sendError('security.error',['error'=>'Invalid account pin'],422);
        }
        if (SystemFiatAccount::where('id',$input['id'])->delete()){
            $dataResponse=[
                'status'=>'deleted',
                'id'=>$input['id']
            ];
            return $this->sendResponse($dataResponse,'account deleted successfully.');
        }
        return $this->sendError('account.error',['error'=>'something went wrong']);
    }

    public function getAccounts()
    {
        $accounts = SystemFiatAccount::get();
        if ($accounts->count()<1){
            return $this->sendError('account.error',['error'=>'No data found']);
        }

        $dataCo=[];
        foreach ($accounts as $account) {
            $data = [
                'id'=>$account->id,'bank'=>$account->bank,
                'accountName'=>$account->accountName,
                'accountNumber'=>$account->accountNumber,
                'status'=>($account->status==1)?'active':'inactive',
                'reference'=>$account->reference,
                'dateCreated'=>strtotime($account->created_at)
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
}
