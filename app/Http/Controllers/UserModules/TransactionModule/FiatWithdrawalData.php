<?php

namespace App\Http\Controllers\UserModules\TransactionModule;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Fiat;
use App\Models\FiatWithdrawal;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Models\UserBank;
use App\Notifications\AdminMail;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FiatWithdrawalData extends BaseController
{
    use PubFunctions;
    public function withdrawFiatFunds(Request $request)
    {
        $user = Auth::user();
        $web= GeneralSetting::find(1);
        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric'],
            'password'=>['required','current_password:api'],
            'currency' => ['required', 'alpha'],
        ])->stopOnFirstFailure();
        if ($validator->fails()) {
            return $this->sendError('validation.error', ['error' => $validator->errors()->all()], 422);
        }
        $input = $validator->validated();

        $paymentMethod = UserBank::where('status',1)->first();

        if (empty($paymentMethod)){
            return $this->sendError('payment-method.error', ['error' => 'Please add a payment method first'],
                422);
        }

//        $currency = Fiat::where('code',$input['currency'])->first();
//        $rateNGN = $currency->rateNGN;

        $rateNGN = $this->fetchUsdToNgnRate();



        $ngnAmount = $rateNGN*$input['amount'];

        $amount = $input['amount'];

        if ($user->balance <$input['amount']){
            return $this->sendError('validation.error', ['error' => 'Insufficient funds'], 422);
        }

        $dataUser = [
            'balance'=>$user->balance-$amount
        ];
        $charge=$web->withdrawalCharge;
        $amountCredit = $ngnAmount-$charge;
        $ref = $this->generateRef('fiat_withdrawals','reference');
        $dataWithdrawal = [
            'user'=>$user->id,'reference'=>$ref,
            'amount'=>$ngnAmount,'fiatAmount'=>$input['amount'],
            'charge'=>$charge,
            'amountCredit'=>$amountCredit,'bank'=>$paymentMethod->bank,
            'accountName'=>$paymentMethod->accountName,'accountNumber'=>$paymentMethod->accountNumber,
            'status'=>2,'rate'=>$rateNGN
        ];

        $withdrawal = FiatWithdrawal::create($dataWithdrawal);
        if (!empty($withdrawal)){
            User::where('id',$user->id)->update($dataUser);

            $admin = User::where('isAdmin',1)->first();
            if (!empty($admin)){
                $message = "
                    A new fiat withdrawal request has been placed on <b>".env('APP_NAME')."</b>.
                    Find Transaction details below:<br><br>
                    <b>Withdrawal Reference</b>:".$withdrawal->reference."<br>
                    <b>Amount Requested</b>: NGN".number_format($ngnAmount)."<br>
                    <b>Amount To Credit</b>: NGN".number_format($amountCredit)."<br>
                    <b>Amount To Credit</b>: USD".number_format($input['amount'])."<br>
                    <b>Rate</b>: ".number_format($rateNGN)."<br>
                    <b>Charge</b>: NGN".number_format($charge)."<br>
                    <b>Bank </b>:".$paymentMethod->bank."<br>
                    <b>Account Number</b>:".$paymentMethod->accountNumber."<br>
                    <b>Account Name</b>:".$paymentMethod->accountName."<br>
                    <b>User</b>:".$user->name.".<br>
                    <p> Please confirm this and approve.</p>
                ";

                $admin->notify(new AdminMail($admin,$message,'New Fiat Withdrawal'));
            }
            $dataResponse=[
                'name'=>$user->name,'token'=>$request->bearerToken(),
                'message'=>'Withdrawal request received.'
            ];
            return $this->sendResponse($dataResponse,'withdrawal successful.');
        }
        return $this->sendError('withdrawal.error', ['error' => 'Something went wrong'], 422);
    }

    public function getUserFiatWithdrawals()
    {
        $user = Auth::user();

        $withdrawals = FiatWithdrawal::where('user',$user->id)->get();
        if ($withdrawals->count()<1){
            return $this->sendError('withdrawal.error',['error'=>'no data found']);
        }
        $dataCo = [];

        foreach ($withdrawals as $withdrawal) {
            switch ($withdrawal->status){
                case 1:
                    $status='approved';
                    break;
                case 2:
                    $status='pending';
                    break;
                case 3:
                    $status='cancelled';
                    break;
            }
            $data=[
                'amount'=>$withdrawal->amount,
                'amountCredited'=>$withdrawal->amountCredit,
                'bank'=>$withdrawal->bank,
                'accountName'=>$withdrawal->accountName,
                'accountNumber'=>$withdrawal->accountNumber,
                'reference'=>$withdrawal->reference,
                'usdAmount'=>$withdrawal->fiatAmount,
                'status'=>$status,'charge'=>$withdrawal->charge,
                'date'=>(date('Y-m-d',strtotime($withdrawal->created_at)).'('.date('h:ia',strtotime($withdrawal->created_at)).')')
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo,'retrieved');
    }
}
