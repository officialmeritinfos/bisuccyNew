<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\SignalPackage;
use App\Models\SignalPackageFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SignalPackages extends BaseController
{
    public function landingPage()
    {

    }

    public function getPackages()
    {
        $packages = SignalPackage::where('status',1)->get();
        if ($packages->count()<1){
            return $this->sendError('signal.error',['error'=>'No package found']);
        }
        $dataCo=[];

        foreach ($packages as $package) {
            $features = SignalPackageFeature::where('packageId',$package->id)->get();
            $dataPack=[];
            if ($features->count()>0){
                foreach ($features as $feature) {
                    $dataP=[
                        'feature'=>$feature->content
                    ];
                    $dataPack[]=$dataP;
                }
            }
            $data=[
                'id'=>$package->id,
                'name'=>$package->name,
                'amount'=>$package->amount,
                'duration'=>$package->duration,
                'interval'=>$package->interval,
                'status'=>($package->status==1)?'active':'inactive',
                'features'=>$dataPack
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo,'retrieved');
    }
    //edit
    public function editPackage(Request $request)
    {
        $admin = Auth::user();
        $web = GeneralSetting::find(1);

        $validator = Validator::make($request->all(),[
            'id'=>['numeric','required'],
            'pin'=>['required'],
            'name'=>['required','string'],
            'amount'=>['required','numeric'],
            'duration'=>['required','string'],
            'interval'=>['required','string'],
            'status'=>['required','integer'],
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input = $validator->validated();

        //check if the pin is accurate
        if(!Hash::check($input['pin'],$admin->transPin)){
            return $this->sendError('security.error',['error'=>'Invalid account Pin'],422);
        }

        $package = SignalPackage::where('id',$input['id'])->first();
        if (empty($package)){
            return $this->sendError('package.error',['error'=>'invalid package selected'],422);
        }

        $dataPackage=[
            'name'=>$input['name'],'amount'=>$input['amount'],'duration'=>$input['duration'],
            'interval'=>$input['interval'],'status'=>$input['status']
        ];
        if (SignalPackage::where('id',$input['id'])->update($dataPackage)){
            $dataResponse = $dataPackage;
            return $this->sendResponse($dataResponse,'updated successfully.');
        }
        return $this->sendError('package.error',['error'=>'Something Went wrong']);
    }
    //edit package features
    public function editPackageFeatures(Request $request)
    {
        $admin = Auth::user();
        $web = GeneralSetting::find(1);

        $validator = Validator::make($request->all(),[
            'id'=>['numeric','required'],
            'pin'=>['required'],
            'content'=>['required','string'],
            'package'=>['required','integer'],
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input = $validator->validated();

        //check if the pin is accurate
        if(!Hash::check($input['pin'],$admin->transPin)){
            return $this->sendError('security.error',['error'=>'Invalid account Pin'],422);
        }

        $data=[
            'content'=>$input['content'],
            'packageId'=>$input['package']
        ];
        if (SignalPackageFeature::where('id',$input['id'])->update($data)){
            $dataResponse = $data;
            return $this->sendResponse($dataResponse,'updated successfully.');
        }
        return $this->sendError('package.error',['error'=>'Something Went wrong']);
    }
    //add package
    public function addPackage(Request $request)
    {
        $admin = Auth::user();
        $web = GeneralSetting::find(1);

        $validator = Validator::make($request->all(),[
            'pin'=>['required'],
            'name'=>['required','string'],
            'amount'=>['required','numeric'],
            'duration'=>['required','string'],
            'interval'=>['required','string'],
            'status'=>['required','integer'],
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input = $validator->validated();

        //check if the pin is accurate
        if(!Hash::check($input['pin'],$admin->transPin)){
            return $this->sendError('security.error',['error'=>'Invalid account Pin'],422);
        }

        $package = SignalPackage::where('name',$input['name'])->first();
        if (!empty($package)){
            return $this->sendError('package.error',['error'=>'package already added'],422);
        }

        $dataPackage=[
            'name'=>$input['name'],'amount'=>$input['amount'],'duration'=>$input['duration'],
            'interval'=>$input['interval'],'status'=>$input['status']
        ];
        if (SignalPackage::create($dataPackage)){
            $dataResponse = $dataPackage;
            return $this->sendResponse($dataResponse,'added successfully.');
        }
        return $this->sendError('package.error',['error'=>'Something Went wrong']);
    }

    public function addPackageFeature(Request $request)
    {
        $admin = Auth::user();
        $web = GeneralSetting::find(1);

        $validator = Validator::make($request->all(),[
            'pin'=>['required'],
            'content'=>['required','string'],
            'package'=>['required','integer'],
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input = $validator->validated();

        //check if the pin is accurate
        if(!Hash::check($input['pin'],$admin->transPin)){
            return $this->sendError('security.error',['error'=>'Invalid account Pin'],422);
        }

        $package = SignalPackage::where('id',$input['package'])->first();
        if (empty($package)){
            return $this->sendError('package.error',['error'=>'invalid package selected'],422);
        }

        $data=[
            'content'=>$input['content'],
            'packageId'=>$input['package']
        ];

        if (SignalPackageFeature::create($data)){
            $dataResponse = $data;
            return $this->sendResponse($dataResponse,'added successfully.');
        }
        return $this->sendError('package.error',['error'=>'Something Went wrong']);
    }
}
