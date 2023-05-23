<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\Deposit;
use App\Models\SystemAccount;
use App\Models\SystemIncoming;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Notifications\AdminMail;
use App\Notifications\DepositMail;
use App\Notifications\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function receiverWebhook(Request $request)
    {
        try {
            $eventType = $request->input('event');
            switch ($eventType){
                case 'WALLET_DEPOSIT_COMPLETED':
                    $this->processCryptoDeposit($request);
                    break;
                default:
                    return 1;
                    break;
            }
        }catch (\Exception $exception){
            Log::info('Webhook error: '.$exception);
        }
    }
    //process crypto deposit
    public function processCryptoDeposit($request)
    {
        $depositId =  $request->input('data.depositId');
        $currency =  $request->input('data.currency');
        $network =  $request->input('data.network');
        $usdValue =  $request->input('data.usdValue');
        $addressType =  $request->input('data.addressType');
        $txId =  $request->input('data.txid');
        $mainAmount =  $request->input('data.amount');
        $from_address =  $request->input('data.from_address');
        $to_address =  $request->input('data.to_address');
        //convert the amount to main currency amount
        switch ($currency){
            case 'BTC':
                $amount = $mainAmount/10000000;
                break;
            case 'ETH':
                $amount = $mainAmount/100000000000000000;
                break;
            default:
                $amount = $mainAmount;
                break;
        }
        if ($network=='BTC'){
            $network = 'bitcoin';
        }else{
            $network = $network;
        }
        $dataDeposit = [
            'amount'=>$amount,
            'address'=>$to_address,
            'sender'=>$from_address,
            'hash'=>$txId,
            'network'=>$network,
            'id'=>$depositId,
            'currency'=>$currency
        ];
        switch ($addressType){
            case 'main':
                $this->processMainWalletDeposit($dataDeposit);
                break;
            default:
                $this->processSubWalletDeposit($dataDeposit);
                break;
        }
    }
    //process deposit to admin
    public function processMainWalletDeposit($data)
    {
        $coin = Coin::where('asset',$data['currency'])->first();

        $systemAccount = SystemAccount::where([
            'asset'=>$data['currency'],'network'=>strtolower($data['network'])
        ])->first();
        //start processing
        if (!empty($systemAccount)){
            //ensure that the deposit does not exist to avoid double entry
            $depositExists = SystemIncoming::where([
                'transHash' => $data['hash'], 'asset' => $data['currency']
            ])->first();
            if (empty($depositExists)){

                $dataDeposit = [
                    'amount' => $data['amount'],
                    'asset' => $data['currency'],
                    'addressFrom' => $data['sender'],
                    'addressTo' => $data['address'],
                    'depositId' => $data['id'],
                    'transHash' => $data['hash'],
                    'network' => $data['network'],
                ];
                $deposit = SystemIncoming::create($dataDeposit);
                if (!empty($deposit)){

                    $newBalance = $systemAccount->availableBalance+$data['amount'];
                    $systemAccount->availableBalance = $newBalance;
                    $systemAccount->save();

                    //send notification to admin
                    $admin = User::where(['isAdmin' => 1, 'role' => 1])->first();
                    if (!empty($admin)) {

                        $dataMail = [
                            'amount' => $data['amount']
                        ];

                        $admin->notify(new DepositMail($admin, $coin, $dataMail));
                    }
                }
            }
        }
    }
    //process subwallet deposit
    public function processSubWalletDeposit($data)
    {
        $coin = Coin::where('asset',$data['currency'])->first();
        $wallet = Wallet::where([
            'asset'=>$data['currency'],'network'=>strtolower($data['network'])
        ])->first();
        //start processing
        if (!empty($wallet)){
            $user = User::where('id',$wallet->user)->first();
            $userWallet = UserWallet::where(['asset'=>$wallet->asset,'user'=>$wallet->user])->first();
            //ensure that the deposit does not exist to avoid double entry
            $depositExists = Deposit::where([
                'transHash' => $data['hash'], 'asset' => $data['currency']
            ])->first();
            if (empty($depositExists)){

                $dataDeposit = [
                    'amount' => $data['amount'],
                    'asset' => $data['currency'],
                    'addressFrom' => $data['sender'],
                    'addressTo' => $data['address'],
                    'depositId' => $data['id'],
                    'transHash' => $data['hash'],
                    'network' => $data['network'],
                    'user'=>$user->id
                ];
                $deposit = Deposit::create($dataDeposit);
                if (!empty($deposit)){

                    $newBalance = $userWallet->availableBalance+$data['amount'];

                    $dataUserWallet = [
                        'availableBalance'=>$userWallet->availableBalance+$newBalance
                    ];

                    $dataWallet = [
                        'availableBalance' => $wallet->availableBalance + $data['amount'],
                        'mainBalance' => $wallet->mainBalance + $data['amount']
                    ];

                    UserWallet::where('id',$userWallet->id)->update($dataUserWallet);
                    Wallet::where('id',$wallet->id)->update($dataWallet);

                    $this->notifyUser($user,$deposit,$coin);
                    $this->notifyAdmin($user,$deposit);

                }
            }
        }
    }
    //send notification to user
    public function notifyUser($user,$deposit,$coin)
    {
        $dataMail=[
            'amount'=>$deposit->amount
        ];
        //send mail to user
        $user->notify(new DepositMail($user,$coin,$dataMail));
        //send app notification
        $appMessage =number_format($deposit->amount,6)." ".$coin->name." deposit received.";
        $user->notify(new UserNotification($user,$appMessage,'New deposit'));
    }
    //send notification to admin
    public function notifyAdmin($user,$deposit)
    {
        $coin = Coin::where('asset',$deposit->asset)->first();
        //fetch super admin
        $admin = User::where(['isAdmin'=>1,'role'=>1])->first();
        if (!empty($admin)){
            //send notification
            $subject = 'New '.$deposit->amount.' '.$coin->name.' deposit confirmed on '.env('APP_NAME');
            $message="
                A new deposit of ".number_format($deposit->amount,$coin->precision)." ".$coin->name." has been
                confirmed on ".env('APP_NAME').". Deposit was received from the user <b>".$user->name."</b>
                and transaction hash: <b>".$deposit->transHash."</b>.
            ";
            $admin->notify(new AdminMail($admin,$message,$subject));
            //send app notification
            $appMessage =number_format($deposit->amount,$coin->precision)." ".$coin->name." deposit
            received from ".$user->nam;
            $user->notify(new UserNotification($user,$appMessage,'New deposit received'));
        }
    }
}
