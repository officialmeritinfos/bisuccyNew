<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Notification;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Swap;
use App\Models\User;
use App\Models\Withdrawal;
use App\Notifications\AdminMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Dashboard extends BaseController
{
    public function landingPage()
    {
        return view('dashboard.index');
    }
    public function profileLandingPage()
    {
        return view('dashboard.profile');
    }
    //set account pin for admin
    public function setPin(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'pin'=>['required'],
            'confirm_pin'=>['required','same:pin'],
            'password'=>['required']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }

        $input = $validator->validated();

        //check if the admin has set the transaction pin
        if ($user->setPin==1){
            return $this->sendError('security.error',['error'=>'Account Pin has already been set']);
        }
        //does the password match
        if (!Hash::check($input['password'],$user->password)){
            return $this->sendError('security.error',['error'=>'Wrong password']);
        }

        $dataUser = [
            'setPin'=>1,'transPin'=>bcrypt($input['pin'])
        ];

        if (User::where('id',$user->id)->update($dataUser)){
            $dataNotify=[
                'user'=>$user->id,'title'=>'You are protected',
                'content'=>'Your account Pin has been set'
            ];
            Notification::create($dataNotify);
            $message="
                Your account pin on ".env('APP_NAME')." has been set.
                Endeavour to keep this safe from reach. You will need it to approve
                transactions.
            ";

            $user->notify(new AdminMail($user,$message,'Account pin setup'));

            $dataResponse=[
                'name'=>$user->name,
                'email'=>$user->email,
            ];
            return $this->sendResponse($dataResponse,'account pin set');
        }
        return $this->sendError('security.error',['error'=>'Something went wrong']);
    }
    //get admin details
    public function getAdminDetails()
    {
        $user = Auth::user();
        //only an admin has this privilege
        if ($user->isAdmin!=1){
            return $this->sendError('account.error',['error'=>'Unauthorized access']);
        }
        $dataResponse=[
            'name'=>$user->id,'email'=>$user->email,'phone'=>$user->phone,
            'reference'=>$user->userRef,'twoFactor'=>($user->twoFactor==1)?'on':'off',
            'setPin'=>($user->setPin==1)?'yes':'no',
        ];
        return $this->sendResponse($dataResponse,'retrieved');
    }
    //change account password
    public function changePasswordLandingPage()
    {

    }

    public function doChangePassword(Request $request)
    {
        $admin = Auth::user();
        $validator = Validator::make($request->all(),[
            'pin'=>['required'],
            'old_password'=>['required'],
            'new_password'=>['required'],
            'confirm_password'=>['required','same:new_password'],
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }

        $input = $validator->validated();

        //check if the pin is accurate
        if(!Hash::check($input['pin'],$admin->transPin)){
            return $this->sendError('security.error',['error'=>'Invalid account Pin'],422);
        }
        //does the password match
        if (!Hash::check($input['old_password'],$admin->password)){
            return $this->sendError('security.error',['error'=>'Old passwordis wrong']);
        }

        $dataUser = [
           'password'=>bcrypt($input['new_password'])
        ];

        if (User::where('id',$admin->id)->update($dataUser)){
            $dataNotify=[
                'user'=>$admin->id,'title'=>'Account Password Change',
                'content'=>'Your account password was changed.'
            ];
            Notification::create($dataNotify);
            $message="
                Your account password on ".env('APP_NAME')." was currently changed from your
                dashboard. Please review this.
            ";

            $admin->notify(new AdminMail($admin,$message,'Account Password changed'));

            $dataResponse=[
                'name'=>$admin->name,
                'email'=>$admin->email,
            ];
            return $this->sendResponse($dataResponse,'account password changed');
        }
        return $this->sendError('security.error',['error'=>'Something went wrong']);
    }
    //last five transactions
    public function latestTransactions()
    {
        //fetch the transactions
        $dataResponse=[
            'purchases'=>$this->getPurchases(),
            'sales'=>$this->getSales(),
        ];
        return $this->sendResponse($dataResponse,'fetch successful');

    }
    public function getPurchases()
    {
        $purchases = Purchase::limit(5)->orderBy('id','desc')->get();
        $dataCo=[];
        foreach ($purchases as $purchase) {
            $data = $this->getPurchaseData($purchase);
            $dataCo[]=$data;
        }
        return $dataCo;
    }
    /**
     * @param $purchase
     * @return array
     */
    protected function getPurchaseData($purchase): array
    {
        $user = User::where('id', $purchase->user)->first();
        switch ($purchase->status) {
            case 1:
                $status = 'completed';
                break;
            case 2:
                $status = 'pending';
                break;
            case 3:
                $status = 'cancelled';
                break;
            default:
                $status = 'pending approval';
                break;
        }
        $data = [
            'id' => $purchase->id,
            'reference' => $purchase->reference,
            'cryptoAmount' => $purchase->amount,
            'asset' => $purchase->asset,
            'fiatAmount' => $purchase->fiatAmount,
            'fiat' => $purchase->fiat,
            'rateGiven' => $purchase->rate,
            'ngnRate' => $purchase->rateNGN,
            'charge' => $purchase->charge,
            'amountCredited' => $purchase->amountCredit,
            'status' => $status,
            'user' => $user->name,
            'dateInitiated' => $purchase->created_at

        ];
        return $data;
    }
    public function getSales()
    {
        $sales = Sale::limit(5)->orderBy('id','desc')->get();
        $dataCo=[];
        foreach ($sales as $sale) {
            $data = $this->getSaleData($sale);
            $dataCo[]=$data;
        }
        return $dataCo;
    }
    /**
     * @param $sale
     * @return array
     */
    protected function getSaleData($sale): array
    {
        $user = User::where('id', $sale->user)->first();
        switch ($sale->status) {
            case 1:
                $status = 'completed';
                break;
            case 2:
                $status = 'pending';
                break;
            case 3:
                $status = 'cancelled';
                break;
            default:
                $status = 'pending approval';
                break;
        }
        $data = [
            'id' => $sale->id,
            'reference' => $sale->reference,
            'cryptoAmount' => $sale->amount,
            'asset' => $sale->asset,
            'fiatAmount' => $sale->fiatAmount,
            'fiat' => $sale->fiat,
            'rateGiven' => $sale->rate,
            'ngnRate' => $sale->rateNGN,
            'charge' => $sale->charge,
            'amountCredited' => $sale->amountCredit,
            'status' => $status,
            'user' => $user->name,
            'dateInitiated' => $sale->created_at

        ];
        return $data;
    }
    //dashboard data - users, transactions etc
    public function dashboardData()
    {
        //fetch the data
        $users = User::where('isAdmin','!=',1)->get();
        $sales = Sale::get();
        $purchases = Purchase::get();
        $withdrawals = Withdrawal::get();
        $deposits = Deposit::get();
        $swap = Swap::get();

        //return their counts
        $dataResponse=[
            'users'=>$users->count(),
            'sales'=>$sales->count(),
            'purchases'=>$purchases->count(),
            'withdrawals'=>$withdrawals->count(),
            'deposits'=>$deposits->count(),
            'swaps'=>$swap->count(),
        ];
        return $this->sendResponse($dataResponse,'fetch successful');
    }
}
