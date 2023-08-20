<?php

namespace App\Http\Controllers\UserModules\TransactionModule;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\FiatDeposit;
use App\Models\FiatWithdrawal;
use App\Models\Otp;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Swap;
use App\Models\SystemFiatAccount;
use App\Models\Withdrawal;
use App\Notifications\AdminMail;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TransactionData extends BaseController
{
    use PubFunctions;
    //fetch fiat transactions by a user
    public function fetchUserFiatTransactions()
    {
        $user = Auth::user();

        $deposits = FiatDeposit::where('user',$user->id)->get();
        $dataCo = [];

        $withdrawals = FiatWithdrawal::where('user',$user->id)->get();
        $dataCos = [];

        //deposits
        if ($deposits->count()>0){
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
                    'date'=>(date('Y-m-d',strtotime($deposit->created_at)).'('.date('h:ia',strtotime($deposit->created_at)).')')
                ];
                $dataCo[]=$data;
            }
        }
        //withdrawals
        if ($withdrawals->count()>0){
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
                $dataCos[]=$data;
            }
        }
        $transactions = [
            'deposits'=>$dataCo,
            'withdrawals'=>$dataCos
        ];

        return $this->sendResponse($transactions,'retrieved');
    }
    //User Crypto transactions
    public function fetchUserCryptoTransactions($crypto)
    {
        $user = Auth::user();

        $dataPur=[];
        $dataSale=[];
        $dataDeposit=[];
        $dataWithdrawal=[];
        $dataSwaps=[];
        //get all purchases
        $purchases = Purchase::where(['user'=>$user->id,'asset'=>$crypto])->get();
        $sales = Sale::where(['user'=>$user->id,'asset'=>$crypto])->get();
        //$swaps = Swap::where(['user'=>$user->id,'assetFrom'=>$crypto])->where()->get();
        $swaps = Swap::where(['user'=>$user->id])
            ->where(function ($query) use ($crypto){
                $query->where('assetFrom',$crypto)->orWhere('assetTo',$crypto);
            })->get();
        $deposits = Deposit::where(['user'=>$user->id,'asset'=>$crypto])->get();
        $withdrawals = Withdrawal::where(['user'=>$user->id,'asset'=>$crypto])->where('isSystem','!=',1)->get();

        //collate purchases
        if ($purchases->count()>0){
            foreach ($purchases as $purchase) {

                $network = $this->fetchCoinNetwork($crypto);

                $data=[
                    'amount'=>$purchase->amount,
                    'fiatAmount'=>$purchase->fiatAmount,
                    'asset'=>$purchase->asset,
                    'reference'=>$purchase->reference,
                    'charge'=>$purchase->charge,
                    'network'=>$network,
                    'fiat'=>$purchase->fiat,
                    'rate'=>$purchase->rate,
                    'rateNGN'=>$purchase->rateNGN,
                    'cryptoCredited'=>$purchase->amountCredit,
                    'date'=>(date('Y-m-d',strtotime($purchase->created_at)).'('.date('h:ia',strtotime($purchase->created_at)).')')
                ];
                $dataPur[]=$data;
            }
        }
        //collate sales
        if ($sales->count()>0){
            foreach ($sales as $sale) {

                $network = $this->fetchCoinNetwork($crypto);

                $data=[
                    'amount'=>$sale->amount,
                    'fiatAmount'=>$sale->fiatAmount,
                    'asset'=>$sale->asset,
                    'reference'=>$sale->reference,
                    'charge'=>$sale->charge,
                    'network'=>$network,
                    'fiat'=>$sale->fiat,
                    'rate'=>$sale->rate,
                    'rateNGN'=>$sale->rateNGN,
                    'date'=>(date('Y-m-d',strtotime($sale->created_at)).'('.date('h:ia',strtotime($sale->created_at)).')')
                ];
                $dataSale[]=$data;
            }

        }
        //collate swaps
        if ($swaps->count()>0){
            foreach ($swaps as $swap) {

                //$network = $this->fetchCoinNetwork($crypto);

//                $data=[
//                    'amountFrom'=>$swap->amountFrom,
//                    'amountTo'=>$swap->amountTo,
//                    'amountCredited'=>$swap->amountCredit,
//                    'assetFrom'=>$swap->assetFrom,
//                    'assetTo'=>$swap->assetTo,
//                    'reference'=>$swap->reference,
//                    'charge'=>$swap->charge,
//                    'network'=>$network,
//                    'date'=>(date('Y-m-d',strtotime($swap->created_at)).'('.date('h:ia',strtotime($swap->created_at)).')')
//                ];
                if ($swap->assetFrom==$crypto){
                    $network = $this->fetchCoinNetwork($crypto);
                    $data=[
                        'amount'=>'-'.$swap->amountFrom,
                        'asset'=>$swap->assetFrom,
                        'network'=>$network,
                        'reference'=>$swap->reference,
                        'date'=>(date('Y-m-d',strtotime($swap->created_at)).'('.date('h:ia',strtotime($swap->created_at)).')')
                    ];
                }else{
                    $network = $this->fetchCoinNetwork($crypto);
                    $data=[
                        'amount'=>'+'.$swap->amountCredit,
                        'asset'=>$swap->assetTo,
                        'network'=>$network,
                        'reference'=>$swap->reference,
                        'date'=>(date('Y-m-d',strtotime($swap->created_at)).'('.date('h:ia',strtotime($swap->created_at)).')')
                    ];
                }
                $dataSwaps[]=$data;
            }

        }
        //collate deposits
        if ($deposits->count()>0){
            foreach ($deposits as $deposit) {

                $network = $this->fetchCoinNetwork($crypto);

                $data=[
                    'wallet'=>$deposit->addressTo,
                    'amount'=>$deposit->amount,
                    'asset'=>$deposit->asset,
                    'reference'=>$deposit->transHash,
                    'transHash'=>$deposit->transHash,
                    'network'=>$network,
                    'date'=>(date('Y-m-d',strtotime($deposit->created_at)).'('.date('h:ia',strtotime($deposit->created_at)).')')
                ];
                $dataDeposit[]=$data;
            }

        }
        //collate withdrawals
        if ($withdrawals->count()>0){
            foreach ($withdrawals as $withdrawal) {
                switch ($withdrawal->status){
                    case 1:
                        $status='completed';
                        break;
                    case 3:
                        $status='cancelled';
                        break;
                    default:
                        $status = 'pending';
                        break;
                }

                $network = $this->fetchCoinNetwork($crypto);
                switch ($withdrawal->destination){
                    case 'external':
                        $destination = $withdrawal->addressTo;
                        break;
                    default:
                        $destination = $withdrawal->destination;
                        break;
                }
                $data=[
                    'wallet'=>$destination,
                    'amount'=>$withdrawal->amount,
                    'asset'=>$withdrawal->asset,
                    'reference'=>$withdrawal->reference,
                    'status'=>$status,
                    'fiatAmount'=>$withdrawal->fiatAmount,
                    'charge'=>$withdrawal->charge,
                    'network'=>$network,
                    'date'=>(date('Y-m-d',strtotime($withdrawal->created_at)).'('.date('h:ia',strtotime($withdrawal->created_at)).')')
                ];
                $dataWithdrawal[]=$data;
            }

        }

        $transactions = [
            'purchases'=>$dataPur,
            'deposits'=>$dataDeposit,
            'sales'=>$dataSale,
            'withdrawals'=>$dataWithdrawal,
            'swaps'=>$dataSwaps
        ];
        return $this->sendResponse($transactions,'retrieved');
    }

    /**
     * @param $crypto
     * @return string
     */
    private function fetchCoinNetwork($crypto): string
    {
        return match ($crypto) {
            'ETH', 'USDT' => 'ERC20',
            'USDT_TRON' => 'TRC20',
            'BUSD_BSC' => 'BEP20',
            'BTC' => 'BTC',
            'BNB' => 'BEP2',
            'BCH' => 'BCH',
            default => 'LTC',
        };
    }
    //request for Otp
    public function sendRequestForOtp(Request $request,$purpose)
    {
        $user = Auth::user();

        //create random code
        $token = $this->generateToken('otps','token');
        $codeExpires = '20 Minutes';

        $otp = Otp::create([
            'user'=>$user->id,
            'purpose'=>$purpose,
            'token'=>bcrypt($token),
            'codeExpires'=>strtotime($codeExpires,time())
        ]);
        if (!empty($otp)){
            $message = "
                There is a pending transaction on your ".env('APP_NAME')." account. Use the Code below to
                authorize this action.<br/>
                <p><b>".$token."</b></p>
            ";
            //send mail with Otp to user
            $user->notify(new AdminMail($user,$message,'Transaction Authentication.'));

            $dataResponse = [
                'user'=>$user->name,
                'token'=>$request->bearerToken(),
                'email'=>$user->email,
                'purpose'=>$purpose
            ];

            return $this->sendResponse($dataResponse,'OTP successfully sent');
        }
        return $this->sendError('otp.error',['error'=>'Something went wrong']);
    }

}
