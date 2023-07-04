<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\Notification;
use App\Models\SignalEnrollmentPayment;
use App\Models\SignalPackage;
use App\Models\User;
use App\Notifications\AdminMail;
use App\Notifications\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SignalEnrollmentPayments extends BaseController
{
    public function landingPage()
    {

    }
    //get signal payments
    public function getPayments($index=0)
    {
        $payments = SignalEnrollmentPayment::offset($index*50)->limit(50)->get();
        $dataCo=[];
        foreach ($payments as $payment) {
            $data = $this->getPaymentData($payment);
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    //get payments by id
    public function getPaymentId($id)
    {
        $payment = SignalEnrollmentPayment::where('id',$id)->first();
        $data = $this->getPaymentData($payment);
        return $this->sendResponse($data, 'retrieved');
    }
    /**
     * @param $payment
     * @return array
     */
    protected function getPaymentData($payment): array
    {
        $user = User::where('id', $payment->user)->first();
        $package = SignalPackage::where('id',$payment->package)->first();
        switch ($payment->status) {
            case 1:
                $status = 'completed';
                break;
            case 2:
                $status = 'pending approval';
                break;
            case 3:
                $status = 'cancelled';
                break;
            default:
                $status = 'pending';
                break;
        }
        $data = [
            'id' => $payment->id,
            'reference' => $payment->reference,
            'amount' => $payment->amount,
            'amountPaid' => $payment->amountPaid,
            'fiat' => $payment->fiat,
            'bank' => $payment->bank,
            'accountName' => $payment->accountName,
            'accountNumber' => $payment->accountNumber,
            'amountCredited' => $payment->amountCredit,
            'status' => $status,
            'user' => $user->name,
            'dateInitiated' => $payment->created_at,
            'authorizedBy'=>$payment->authorizedBy,
            'signalPackage'=>$package->name
        ];
        return $data;
    }
    //approve payment
    public function approveSignalPayment(Request $request)
    {
        $admin = Auth::user();
        $web = GeneralSetting::find(1);

        $validator = Validator::make($request->all(),[
            'id'=>['numeric','required'],
            'pin'=>['required']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input = $validator->validated();

        //check if the pin is accurate
        if(!Hash::check($input['pin'],$admin->transPin)){
            return $this->sendError('security.error',['error'=>'Invalid account Pin'],422);
        }

        $payment = SignalEnrollmentPayment::where('id',$input['id'])->first();
        if (empty($payment)){
            return $this->sendError('payment.error',['error'=>'payment not found'],422);
        }
        if ($payment->status==1){
            return $this->sendError('payment.error',['error'=>'Payment already approved.'],422);
        }

        $user = User::where('id',$payment->user)->first();
        $package = SignalPackage::where('id',$payment->packages);
        $dataPayment=[
            'status'=>1,
            'authorizedBy'=>$admin->name
        ];

        $dataUser=[
            'enrolledInSignal'=>1,'packageEnrolled'=>$package->id,
            'timeRenewPayment'=>strtotime($package->duration,time())
        ];

        $messageToSuperAdmin="
            An enrollment into a signal package ".$package->name." by ".$user->name." has been approved
            by ".$admin->name.". Payment Reference is ".$payment->reference.".
        ";
        $messageToUser="
            Your payment for Signal enrolment on ".env('APP_NAME')." has been approved. You now have been granted
            access to the signal room. If you face any challenges, please contact support.
        ";

        if (SignalEnrollmentPayment::where('id',$payment->id)->update($dataPayment)){
            User::where('id',$user->id)->update($dataUser);

            $dataNotify=[
                'title'=>'Signal Enrolment','content'=>'Your enrolment request has been approved.',
                'user'=>$user->id
            ];
            Notification::create($dataNotify);

            $dataNotifyAdmin=[
                'title'=>'Signal Room Enrollment Approval','content'=>'An enrolment into the signal room was approved.',
                'user'=>$admin->id,'showAdmin'=>1
            ];
            Notification::create($dataNotifyAdmin);
            $user->notify(new AdminMail($user,$messageToUser,'Signal Room Enrolment'));
            //send app notification
            $appMessage ="
                Your enrollment to the signal room has been approved.
            ";
            $user->notify(new UserNotification($user,$appMessage,'Signal subscription approved.'));
            //send mails to admin
            $superAdmin = User::where(['isAdmin'=>1,'role'=>1])->first();
            if (!empty($admin)){
                $superAdmin->notify(new AdminMail($superAdmin,$messageToSuperAdmin,
                    'Signal Room Enrolment on '.env('APP_NAME')));
            }
            $dataResponse = [
                'status'=>'approved'
            ];
            return $this->sendResponse($dataResponse,'payment approved');
        }
        return $this->sendError('signal.error',['error'=>'Something went wrong']);
    }
}
