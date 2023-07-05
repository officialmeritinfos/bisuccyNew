<?php

namespace App\Http\Controllers\UserModules\TransactionModule;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\FiatDeposit;
use App\Models\SystemFiatAccount;
use App\Models\User;
use App\Notifications\AdminMail;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FiatDepositData extends BaseController
{
    use PubFunctions;
    //get the system account for fiat deposit
    public function getSystemFiatAccount()
    {
        $systemBanks = SystemFiatAccount::where('status',1)->get();
        if($systemBanks->count()<1){
            return $this->sendError('system.error',['error'=>'No data found']);
        }
        $dataCo = [];
        foreach ($systemBanks as $bank) {
            $data=[
                'bank'=>$bank->bank,'reference'=>$bank->reference,
                'accountName'=>$bank->accountName,'accountNumber'=>$bank->accountNumber
            ];
            $dataCo[] = $data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    //fund fiat
    public function fundFiat(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'amount'=>['required','numeric'],
            'bank'=>['required','alphanum']
        ])->stopOnFirstFailure();
        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }

        $input = $validator->validated();

        $systemBank = SystemFiatAccount::where('reference',$input['bank'])->first();
        if (empty($systemBank)){
            return $this->sendError('validation.error',['error'=>'Invalid account'],422);
        }
        $ref = $this->generateRef('fiat_deposits','reference');
        //get the usd rate and convert to USD
        $rate = $this->fetchNgnToUsdRate();

        $usdAmount = $input['amount']/$rate;

        $dataDeposit = [
            'reference'=>$ref,'amount'=>$input['amount'],
            'systemAccount'=>$input['bank'],'user'=>$user->id,
            'usdRate'=>$rate,'usdAmount'=>$usdAmount
        ];

        $deposit = FiatDeposit::create($dataDeposit);
        if (!empty($deposit)){
            $dataResponse = [
                'amount'=>$deposit->amount,'reference'=>$deposit->reference,
                'systemBank'=>$systemBank->reference,'token'=>$request->bearerToken(),
                'name'=>$user->name
            ];
            return $this->sendResponse($dataResponse,'Deposit initiated, pending payment.');
        }
        return $this->sendError('deposit.error',['error'=>'Something went wrong'],422);
    }
    //confirm fiat funding
    public function confirmFiatFunding(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'depositReference' => ['required', 'alphanum'],
            'amountPaid' => ['required', 'numeric'],
            'bankPaidFrom' => ['required', 'string'],
            'accountNumber' => ['required', 'string'],
            'accountName' => ['required', 'string'],
        ])->stopOnFirstFailure();
        if ($validator->fails()) {
            return $this->sendError('validation.error', ['error' => $validator->errors()->all()], 422);
        }
        $input = $validator->validated();

        $deposit=FiatDeposit::where(['user'=>$user->id,'reference'=>$input['depositReference']])->first();
        if (empty($deposit)){
            return $this->sendError('deposit.error', ['error' => 'invalid deposit reference']);
        }

        $usdAmount = $input['amountPaid']/$deposit->usdRate;


        $depositData = [
            'bank'=>$input['bankPaidFrom'],'accountName'=>$input['accountName'],
            'accountNumber'=>$input['accountNumber'],'amountPaid'=>$input['amountPaid'],
            'status'=>4,'usdPaid'=>$usdAmount
        ];
        if (FiatDeposit::where('id',$deposit->id)->update($depositData)){
            $admin = User::where('isAdmin',1)->first();
            if (!empty($admin)){
                $message = "
                    A new fiat deposit has been made on <b>".env('APP_NAME')."</b>. Find Transaction
                    details below:<br><br>
                    <b>Deposit Reference</b>:".$deposit->reference."<br>
                    <b>Amount Paid</b>: NGN".number_format($input['amountPaid'])."<br>
                    <b>Bank Sent From</b>:".$input['bankPaidFrom']."<br>
                    <b>Account Number</b>:".$input['accountNumber']."<br>
                    <b>Account Name</b>:".$input['accountName']."<br>
                    <b>User</b>:".$user->name.".<br>
                    <p> Please confirm this and approve.</p>
                ";

                $admin->notify(new AdminMail($admin,$message,'New Fiat Deposit'));
            }
            $dataResponse=[
                'name'=>$user->name,'token'=>$request->bearerToken(),
                'message'=>'Deposit received; please wait while we confirm this.'
            ];
            return $this->sendResponse($dataResponse,'confirmation received.');
        }
        return $this->sendError('deposit.error', ['error' => 'invalid deposit reference'], 421);
    }
    //get user fiat deposits
    public function getUserFiatDeposits()
    {
        $user = Auth::user();

        $deposits = FiatDeposit::where('user',$user->id)->get();
        if ($deposits->count()<1){
            return $this->sendError('deposit.error',['error'=>'no data found']);
        }
        $dataCo = [];

        foreach ($deposits as $deposit) {
            $systemAccount = SystemFiatAccount::where('reference',$deposit->systemAccount)->first();
            switch ($deposit->status){
                case 1:
                    $status='approved';
                    break;
                case 2:
                    $status='pending';
                    break;
                case 4:
                    $status='awaiting-confirmation';
                    break;
                case 3:
                    $status='cancelled';
                    break;
            }
            $data=[
                'amount'=>$deposit->amount,'amountPaid'=>$deposit->amountPaid,
                'bankFrom'=>$deposit->bank,'accountName'=>$deposit->accountName,
                'accountNumber'=>$deposit->accountNumber,'reference'=>$deposit->reference,
                'systemBank'=>$systemAccount->bank,'systemAccountName'=>$systemAccount->accountName,
                'systemAccountNumber'=>$systemAccount->accountNumber,'status'=>$status,
                'usdAmount'=>$deposit->usdAmount,'usdPaid'=>$deposit->usdPaid,'rate'=>$deposit->usdRate
            ];
            $dataCo[]=$data;
        }

        return $this->sendResponse($dataCo,'retrieved');
    }
}
