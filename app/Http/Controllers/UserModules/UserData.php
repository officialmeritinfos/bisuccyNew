<?php

namespace App\Http\Controllers\UserModules;

use App\Http\Controllers\Controller;
use App\Models\ReferralEarning;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Models\Coin;
use App\Models\Country;
use App\Models\Deposit;
use App\Models\Fiat;
use App\Models\FiatDeposit;
use App\Models\FiatWithdrawal;
use App\Models\GeneralSetting;
use App\Models\Otp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Signal;
use App\Models\SignalEnrollmentPayment;
use App\Models\SignalInput;
use App\Models\SignalPackage;
use App\Models\Swap;
use App\Models\SystemAccount;
use App\Models\SystemFiatAccount;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\UserBank;
use App\Models\UserVerification;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Notifications\AdminMail;
use App\Notifications\PasswordChangedMail;
use App\Notifications\UserNotification;
use App\Notifications\WithdrawalMailLater;
use App\Traits\PubFunctions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserData extends BaseController
{
    use PubFunctions;

    public $regular;

    public function __construct()
    {
        $this->regular = new \App\Regular\Wallet();
    }
    //get the details of a loggedin user
    public function getLoggedInUserDetails()
    {
        $user = Auth::user();
        $package = SignalPackage::where('id',$user->packageEnrolled)->first();
        //let's check if the user has a pending signal subscription
        $pendingSubscription = SignalEnrollmentPayment::where(['user'=>$user->id,'status'=>2])->first();
        $tier =1;
        //check which tier user is on
        if ($user->emailVerified==1 && $user->phoneVerified==1){
            $tier = 1;
        }
        if (!empty($user->proofOfAddress)){
            $tier=2;
        }
        if ($user->accountVerified==1){
            $tier=3;
        }

        $data = [
            'id'=>$user->id,
            'name'=>$user->name,
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
            'phoneVerified'=>($user->phoneVerified==1)?'yes':'no',
            'accountVerified'=>($user->accountVerified==1)?'verified':'unverified',
            'photo'=>asset('user/photo/'.$user->photo),
            'canSendCrypto'=>($user->canSend==1)?'on':'off',
            'canDeposit'=>($user->canDeposit==1)?'on':'off',
            'canSell'=>($user->canSell==1)?'on':'off',
            'canBuy'=>($user->canBuy==1)?'on':'off',
            'canSwap'=>($user->canSwap==1)?'on':'off',
            'refBy'=>$user->refBy,
            'accountBalance'=>$user->balance,
            'enrolledInSignal'=>$user->enrolledInSignal==1,
            'hasPendingSignalSubscription'=> !empty($pendingSubscription),
            'packageEnrolled'=>(!empty($package))?$package->name:'none',
            'notification'=>($user->notification==1)?'active':'inactive',
            'timeRenewPayment'=>($user->enrolledInSignal==1)?date('d-m-Y',$user->timeRenewPayment):'none',
            'addressProof'=>asset('user/photo/'.$user->proofOfAddress),
            'submitedId'=>($user->accountVerified==4||$user->accountVerified==1)?true:false,
            'submitedPhoto'=>(empty($user->photo))?false:true,
            'referralBalance'=>$user->refBalance,
            'submitedAddress'=>empty($user->proofOfAddress)?false:true,
            'tier'=>$tier
        ];
        return $this->sendResponse($data, 'retrieved');
    }
    //set phone
    public function setPhone(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'phoneCode'=>['required','string'],
            'phone'=>['required','numeric']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()]);
        }

        $input = $validator->validated();

        $phone = ltrim($input['phone'],'0');
        $phoneCode = ltrim($input['phoneCode'],'+');
        $phones = $phoneCode.$phone;
        $data=[
            'phone'=> $phoneCode.$phone,
            'phoneCode'=>$input['phoneCode']
        ];
        $sendVerification = $this->regular->sendToken($phones);
        if ($sendVerification->ok()){
            $token = $sendVerification->json();

            if (User::where('id',$user->id)->update($data)) {
                $dataResponse = [
                    'token' => $request->bearerToken(),
                    'name' => $user->name,
                    'pinId'=>$token['pinId'],
                    'redirect_to'=>'enter_phone_pin'
                ];
                return $this->sendResponse($dataResponse, 'Phone number added.');
            }
        }
        //Log::info($sendVerification->json());
        return $this->sendError('error',['error'=>'Something went wrong']);

    }
    //verify phone
    public function verifyPhone(Request $request): JsonResponse
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'pinId'=>['required','string'],
            'code'=>['required','numeric']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()]);
        }

        $input = $validator->validated();
        $verifyToken = $this->regular->verifyPhoneToken($input['pinId'],$input['code']);

        $response = $verifyToken->json();
        if ($verifyToken->successful()){


            if (!$response['verified']){
                return $this->sendError('error',['error'=>'Unable to verify phone number']);
            }

            User::where('id',$user->id)->update([
                'phoneVerified'=>1
            ]);

            return $this->sendResponse([
                'token' => $request->bearerToken(),
                'name' => $user->name,
                'phoneVerified'=>'yes',
                'redirect_to'=>'dashboard'
            ],'phone verified');
        }
        return $this->sendError('error',['error'=>$response['verified']]);
    }
    //resend verification code
    public function resendPhoneVerify(Request $request): JsonResponse
    {
        $user = Auth::user();
        $phones = $user->phone;
        $sendVerification = $this->regular->sendToken($phones);
        if ($sendVerification->ok()){
            $token = $sendVerification->json();
            $dataResponse = [
                'token' => $request->bearerToken(),
                'name' => $user->name,
                'pinId'=>$token['pinId'],
                'redirect_to'=>'enter_phone_pin'
            ];
            return $this->sendResponse($dataResponse, 'Verification Code Sent', 201);
        }
        return $this->sendError('error',['error'=>'Something went wrong']);
    }
    public function setAddress(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'address'=>['required','string'],
            'addressImage'=>['required','mimes:jpeg,jpg,png,pdf','max:6000']
        ])->stopOnFirstFailure();
        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()]);
        }

        $input = $validator->validated();

        $image = time() . '_' . $request->file('addressImage')->hashName();
        $request->file('addressImage')->move(public_path('user/photo/'), $image);
        $moveImage = File::exists(public_path('user/photo/'.$image));
        if ($moveImage !=true){
            return $this->sendError('file.error', ['error' => 'Unable to upload proof of address'], '421');
        }

        $data=[
            'address'=>$input['address'],
            'proofOfAddress'=>$image
        ];

        User::where('id',$user->id)->update($data);

        $dataResponse=[
            'token'=>$request->bearerToken(),
            'name'=>$user->name,
        ];
        return $this->sendResponse($dataResponse,'Address submitted: Under review.',201);
    }

    public function setBVN(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'bvn'=>['required','string','digits:10'],
        ])->stopOnFirstFailure();
        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()]);
        }

        $input = $validator->validated();

        $data=[
            'secretId'=>encrypt($input['bvn'])
        ];

        User::where('id',$user->id)->update($data);

        $dataResponse=[
            'token'=>$request->bearerToken(),
            'name'=>$user->name,
        ];
        return $this->sendResponse($dataResponse,'BVN successfully submitted. Verification is underway');
    }
    public function setIDVerification(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'idType'=>['required','string'],
            'idNumber'=>['required','string'],
            'dateCreated'=>['nullable','date'],
            'expiryDate'=>['nullable','date'],
            'photo'=>['required','mimes:jpeg,jpg,png','max:6000']
        ])->stopOnFirstFailure();
        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()]);
        }
        $input = $validator->validated();

        $image = time() . '_' . $request->file('photo')->hashName();
        $request->file('photo')->move(public_path('user/ids/'), $image);
        $moveImage = File::exists(public_path('user/ids/'.$image));
        if ($moveImage !=true){
            return $this->sendError('file.error', ['error' => 'Unable to upload image'], '421');
        }

        $data=[
            'user'=>$user->id,'idType'=>$input['idType'],'idNumber'=>$input['idNumber'],
            'image'=>$image,'dateCreated'=>$input['dateCreated'],'expiryDate'=>($request->filled('expiryDate'))?$input['expiryDate']:'',
            'status'=>4
        ];

        $dataUser =[
            'accountVerified'=>4
        ];

        $doc = UserVerification::create($data);
        if (!empty($doc)){
            User::where('id',$user->id)->update($dataUser);

            $dataResponse=[
                'token'=>$request->bearerToken(),
                'name'=>$user->name,
                'submitted'=>true
            ];

            return $this->sendResponse($dataResponse,'KYC document submitted. Verification is underway.');
        }
        return $this->sendError('verification.error', ['error' => 'Something went wrong. Please try again'], '421');
    }

    public function submitPhoto(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'photo'=>['required','mimes:jpeg,jpg,png','max:6000']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input = $validator->validated();
        $image = time() . '_' . $request->file('photo')->hashName();
        $request->file('photo')->move(public_path('user/photo/'), $image);
        $moveImage = File::exists(public_path('user/photo/'.$image));
        if ($moveImage !=true){
            return $this->sendError('file.error', ['error' => 'Unable to upload pro'], '421');
        }
        $dataUser =[
            'photo'=>$image
        ];
        if (User::where('id',$user->id)->update($dataUser)){

            $dataResponse=[
                'token'=>$request->bearerToken(),
                'name'=>$user->name,
                'submitted'=>true,
                'photo'=>asset('user/photo/'.$image)
            ];
            return $this->sendResponse($dataResponse,'Profile Image updated');
        }
        return $this->sendError('verification.error', ['error' => 'Something went wrong. Please try again'],
            '421');
    }

    public function setPassword(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'currentPassword'=>['bail','required','current_password:api'],
            'password'=>['bail','required',
                Password::min(8)->letters()->mixedCase()->symbols()->uncompromised(2)
            ],
            'password_confirmation'=>['required','same:password']
        ])->stopOnFirstFailure();
        if($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],'422');
        }
        $input=$validator->validated();

        $data=[
            'password'=>bcrypt($input['password'])
        ];

        if (User::where('id',$user->id)->update($data)){

            $success['name'] =  $user->name;
            $success['hasToken']=true;
            $success['token']=$request->bearerToken();

            $user->notify(new PasswordChangedMail($user));
            return $this->sendResponse($success, 'Password successfully changed');
        }
        return $this->sendError('password.error',['error'=>'Something went wrong'],421);
    }

    public function setProfile(Request $request)
    {
        $user = Auth::user();
        $web = GeneralSetting::find(1);

        $validator = Validator::make($request->all(),[
            'password'=>['bail','required','current_password:api'],
            'name'=>['bail','required'],
            'email'=>['required','email']
        ])->stopOnFirstFailure();
        if($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],'422');
        }
        $input=$validator->validated();
        $emailVerify =($input['email'] ==$user->email) ? 1:$web->emailVerification;

        $userData = [
            'email'=>$input['email'],'name'=>$input['name'],'emailVerified'=>$emailVerify
        ];
        if (User::where('id',$user->id)->update($userData)){
            $emailVerify = ($emailVerify==1)? true:false;

            $dataResponse = [
                'name'=>$input['name'],'email'=>$input['email'],
                'needVerification'=>$emailVerify,'instruction'=>'You can log user out to verify their email',
                'token'=>$request->bearerToken()
            ];

            return $this->sendResponse($dataResponse,'Profile successfully updated',202);
        }
        return $this->sendError('profile.error',['error'=>'Something went wrong.'],'422');
    }

    public function setCurrency(Request $request)
    {
        $user = Auth::user();
        $web = GeneralSetting::find(1);

        $validator = Validator::make($request->all(),[
            'currency'=>['required','alpha','exists:fiats,code'],
        ])->stopOnFirstFailure();
        if($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],'422');
        }
        $input=$validator->validated();

        $dataUser = [
            'mainCurrency'=>$input['currency']
        ];
        if (User::where('id',$user->id)->update($dataUser)){
            $dataResponse = [
                'currency'=>$input['currency'],
                'token'=>$request->bearerToken()
            ];
            return $this->sendResponse($dataResponse,'Profile successfully updated',202);
        }
        return $this->sendError('profile.error',['error'=>'Something went wrong.'],'422');
    }
    public function getUserBank()
    {
        $user = Auth::user();
        $banks = UserBank::where('user',$user->id)->orderBy('status','asc')->get();
        $dataCo = [];
        foreach ($banks as $bank) {
            $data=[
                'bank'=>$bank->bank,
                'accountName'=>$bank->accountName,'accountNumber'=>$bank->accountNumber,
                'status'=> ($bank->status==1)? 'active':'inactive'
            ];
            $dataCo[] = $data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function setBank(Request $request)
    {
        $user = Auth::user();
        $web = GeneralSetting::find(1);

        $validator = Validator::make($request->all(),[
            'bank'=>['required','string'],
            'accountName'=>['required','string'],
            'accountNumber'=>['required','string','digits:10']
        ])->stopOnFirstFailure();
        if($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],'422');
        }
        $input=$validator->validated();

        $dataUser =[
            'bank'=>$input['bank'],'accountName'=>$input['accountName'],
            'user'=>$user->id,'accountNumber'=>$input['accountNumber'],
            'status'=>1
        ];
        $bank = UserBank::create($dataUser);
        if (!empty($bank)){
            $dataResponse=[
                'name'=>$user->name,'token'=>$request->bearerToken(),
                'bank'=>$bank->bank,
                'accountName'=>$bank->accountName,'accountNumber'=>$bank->accountNumber,
                'status'=> ($bank->status==1)? 'active':'inactive'
            ];
            return $this->sendResponse($dataResponse,'Payment method Successfully added.');
        }
        return $this->sendError('bank.error',['error'=>'Something went wrong'],421);
    }

    public function testEndpointsNew()
    {
        $response = $this->regular->testEndpointTat();
        Log::info($response);
    }
    //verify otp sent
    public function verifyOtpSent($user,$code,$purpose)
    {
        //check otp
        $otp = Otp::where(['user'=>$user->id,'purpose'=>$purpose])->first();
        if (empty($otp)){
            $error = "Something is wrong with your request. Try again";
            $stat = false;
        }elseif ($otp->codeExpires < time()){
            //check if the otp has expired
            $error = "Your OTP has timed out.";
            $stat = false;
        }else{
            $hashedOtp = Hash::check($code, $otp->token);
            if (!$hashedOtp) {

                //check if the otp is correct
                $error = "Invalid OTP token.";
                $stat = false;
            } else {
                Otp::where('user', $user->id)->delete();

                $error = "Successful";
                $stat = true;
            }
        }

        return [
            'status'=>$stat,
            'error'=>$error
        ];
    }
    //referral earning
    public function userReferralEarnings()
    {
        $user = Auth::user();

        $earnings = ReferralEarning::where('referrer',$user->id)->get();

        if ($earnings->count()<1){
            return $this->sendError('referral.error',['error'=>'No data'],400);
        }

        $dataRef=[];

        foreach ($earnings as $earning) {
            $user = User::where('id',$earning->user)->first();

            $refData=[
                'downline'=>$user->name,
                'amount'=>$earning->amount
            ];

            $dataRef[]=$refData;
        }
        return $this->sendResponse($dataRef,'retrieved');
    }
}
