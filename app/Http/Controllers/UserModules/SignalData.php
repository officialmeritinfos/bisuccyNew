<?php

namespace App\Http\Controllers\UserModules;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\Signal;
use App\Models\SignalEnrollmentPayment;
use App\Models\SignalInput;
use App\Models\SignalPackage;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\AdminMail;
use App\Notifications\UserNotification;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SignalData extends BaseController
{
    use PubFunctions;
    //enroll into signal
    public function enrollInSignalPackage(Request $request)
    {
        $user = Auth::user();
        $web= GeneralSetting::find(1);
        $validator = Validator::make($request->all(), [
            'package' => ['required', 'numeric','integer'],
            'amount'=>['required','numeric'],
            'bank'=>['required','string'],
            'accountNumber'=>['required','string'],
            'accountName'=>['required','string'],
        ])->stopOnFirstFailure();
        if ($validator->fails()) {
            return $this->sendError('validation.error', ['error' => $validator->errors()->all()], 422);
        }
        $input = $validator->validated();

        $package = SignalPackage::where('id',$input['package'])->first();
        if(empty($package)){
            return $this->sendError('signal.error', ['error' => 'Invalid package'], 422);
        }
        $ref = $this->generateRef('signal_enrollment_payments','reference');
        $dataEnrollment =[
            'user'=>$user->id,
            'package'=>$package->id,
            'reference'=>$ref,
            'amount'=>$package->amount,
            'amountPaid'=>$input['amount'],
            'bank'=>$input['bank'],
            'accountName'=>$input['accountName'],
            'accountNumber'=>$input['accountNumber']
        ];

        $enroll = SignalEnrollmentPayment::create($dataEnrollment);
        if(!empty($enroll)){
            //send email to admin
            $admin = User::where('isAdmin',1)->first();
            if(!empty($admin)){
                $message = "
                    A new enrolment into the  ".$package->name." signal package has been received
                    on <b>".env('APP_NAME')."</b>. Find Transaction details submitted below:<br><br>
                    <b>Transaction Reference</b>:".$ref."<br><br>
                    <b>Amount Paid </b>:".number_format($input['amount'],2)."<br><br>
                    <b>Bank</b>:".$input['bank']."<br><br>
                    <b>Account Name</b>:".$input['accountName']."<br><br>
                    <b>Account Number</b>:".$input['accountNumber']." <br><br>
                ";
                $admin->notify(new AdminMail($admin,$message,'New '.$package->name.' Signal Enrolment'));
            }
            $dataResponse = [
                'name'=>$user->name,
                'token'=>$request->bearerToken(),
            ];
            return $this->sendResponse($dataResponse,'We are currently confirming your payment.');
        }
        return $this->sendError('signal.error',['error'=>'something went wrong']);
    }
    //get user signals
    public function getUserSignal()
    {
        $user = Auth::user();
        if ($user->enrolledInSignal!=1){
            return $this->sendError('signal.error',['error'=>'you do not have access to this page']);
        }
        $signals = Signal::where('package',$user->packageEnrolled)
            ->orWhere('package','all')->orderBy('created_at','desc')->get();
        if ($signals->count()<1){
            return $this->sendError('signal.error',['error'=>'no signals published yet']);
        }
        $dataCo=[];

        foreach ($signals as $signal) {
            $userPackage = SignalPackage::where('id',$user->packageEnrolled)->first();
            $signalInputs=SignalInput::where('signalRef',$signal->reference)->get();
            $dataInput=[];
            if ($signalInputs->count()>0){
                foreach ($signalInputs as $input) {
                    $dataP=[
                        'content'=>$input->content
                    ];
                    $dataInput[]=$dataP;
                }
            }
            $data=[
                'reference'=>$signal->reference,
                'title'=>$signal->title,
                'package'=>$userPackage->name,
                'photo'=>asset('user/photos/'.$signal->photo),
                'dateCreated'=>$signal->created_at,
                'contents'=>$dataInput
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo,'retrieved');
    }
    //pay signal using crypto
    public function paySignalUsingCrypto(Request $request)
    {
        $user = Auth::user();
        $web= GeneralSetting::find(1);
        $validator = Validator::make($request->all(), [
            'package' => ['required', 'numeric','integer'],
            'asset'=>['required','string'],
        ])->stopOnFirstFailure();
        if ($validator->fails()) {
            return $this->sendError('validation.error', ['error' => $validator->errors()->all()], 422);
        }
        $input = $validator->validated();

        $package = SignalPackage::where('id',$input['package'])->first();
        if(empty($package)){
            return $this->sendError('signal.error', ['error' => 'Invalid package'], 422);
        }

        //check if user's account balance is up to the amount
        $wallet = Wallet::where(['user'=>$user->id,'asset'=>$input['asset']])->first();
        if (empty($wallet)){
            return $this->sendError('wallet.error', ['error' => 'Invalid wallet selected'], 422);
        }
        //let's convert amount to the equivalent crypto amount
        $packageAmount = $package->amount;
        $rate = $this->getRateInstant($input['asset']);
        $cryptoAmount = $packageAmount/$rate;
        if ($wallet->availableBalance<$cryptoAmount){
            return $this->sendError('wallet.error', ['error' => 'Insufficient balance'], 422);
        }

        $ref = $this->generateRef('signal_enrollment_payments','reference');

        $dataEnrollment =[
            'user'=>$user->id,
            'package'=>$package->id,
            'reference'=>$ref,
            'amount'=>$package->amount,
            'amountPaid'=>$cryptoAmount,
            'bank'=>$input['asset'],
            'accountName'=>$user->name,
            'accountNumber'=>$wallet->address,
            'status'=>1
        ];

        $dataBalance = [
            'availableBalance'=>$wallet->availableBalance - $cryptoAmount
        ];

        $dataUser=[
            'enrolledInSignal'=>1,'packageEnrolled'=>$package->id,
            'timeRenewPayment'=>strtotime($package->duration,time())
        ];

        $enroll = SignalEnrollmentPayment::create($dataEnrollment);
        if(!empty($enroll)){

            Wallet::where('id',$wallet->id)->update($dataBalance);

            //update user
            User::where('id',$user->id)->update($dataUser);

            //send email to admin
            $admin = User::where('isAdmin',1)->first();
            if(!empty($admin)){
                $message = "
                    A new enrolment into the  ".$package->name." signal package has been received
                    on <b>".env('APP_NAME')."</b>. Find Transaction details below:<br><br>
                    <b>Transaction Reference</b>:".$ref."<br><br>
                    <b>Amount Paid </b>:".number_format($cryptoAmount,6)."<br><br>
                    <b>Asset</b>:".$input['asset']."<br><br>
                    <b>Address</b>:".$wallet->address."<br><br>
                    <p>This enrolment has been authenticated automatically and is instant.</p>
                ";
                $admin->notify(new AdminMail($admin,$message,'New '.$package->name.' Signal Enrolment'));
            }
            $messageToUser="
                Your payment for Signal enrolment on ".env('APP_NAME')." was successful.
                You now have been granted access to the signal room. If you face any challenges,
                please contact support.
            ";
            $user->notify(new AdminMail($user,$messageToUser,'Signal Room Enrolment'));
            //send app notification
            $appMessage ="
                Your enrollment to the signal room was successful.
            ";
            $user->notify(new UserNotification($user,$appMessage,'Signal subscription successful.'));

            $dataResponse = [
                'name'=>$user->name,
                'token'=>$request->bearerToken()
            ];
            return $this->sendResponse($dataResponse,'You have successfully enrolled in the '.$package->name.' signal room.');
        }
        return $this->sendError('signal.error',['error'=>'something went wrong']);
    }
}
