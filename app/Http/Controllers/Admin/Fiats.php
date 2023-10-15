<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Fiat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Fiats extends BaseController
{
    public function landingPage()
    {
        return view('settings.fiats');
    }

    public function createFiat()
    {
        return view('settings.create-fiat');
    }

    //get all fiat currencies
    public function getFiats()
    {
        $fiats = Fiat::get();
        if ($fiats->count()<1){
            return $this->sendError('fiat.error',['error'=>'Nothing found.']);
        }
        $dataCo = [];

        foreach ($fiats as $fiat) {
            $data=[
                'name'=>$fiat->name,'code'=>$fiat->code,
                'usdRate'=>$fiat->rateUsd,'ngnRate'=>$fiat->rateNGN,
                'buyRate'=>$fiat->buyRate,'sellRate'=>$fiat->sellRate,
                'symbol'=>$fiat->sign,'country'=>$fiat->country,
                'settlementPeriod'=>$fiat->settlementPeriod,
                'verifiedLimit'=>$fiat->verifiedLimit,'unverifiedLimit'=>$fiat->unverifiedLimit,
                'withdrawalFee'=>$fiat->withdrawalFee,'minAllowed'=>$fiat->minAllowed,
                'canWithdraw'=>($fiat->canWithdraw==1)?'yes':'no',
                'status'=>($fiat->status==1)?'active':'inactive'
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    //update fiat
    public function updateFiat(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'name'=>['required','string'],
            'code'=>['required','alpha'],
            'usdRate'=>['required','numeric'],
            'ngnRate'=>['required','numeric'],
            'buyRate'=>['required','numeric'],
            'sellRate'=>['required','numeric'],
            'symbol'=>['required','string'],
            'country'=>['required','alpha'],
            'settlementPeriod'=>['required','string'],
            'verifiedLimit'=>['required','numeric'],
            'unverifiedLimit'=>['required','numeric'],
            'withdrawalFee'=>['required','numeric'],
            'minAllowed'=>['required','numeric'],
            'canWithdraw'=>['required','numeric'],
            'status'=>['required','numeric'],
            'id'=>['required','integer'],
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }

        $input=$validator->validated();
        //check for the fiat
        $fiat = Fiat::where('id',$input['id'])->first();
        if (empty($fiat)){
            return $this->sendError('fiat.error',['error'=>'Invalid action'],422);
        }

        $dataFiat=[
            'name'=>$input['name'],'code'=>$input['code'],'rateUsd'=>$input['usdRate'],
            'rateNGN'=>$input['ngnRate'],'buyRate'=>$input['buyRate'],'sellRate'=>$input['sellRate'],
            'symbol'=>$input['sign'],'country'=>$input['country'],'settlementPeriod'=>$input['settlementPeriod'],
            'verifiedLimit'=>$input['verifiedLimit'],'unverifiedLimit'=>$input['unverifiedLimit'],
            'withdrawalFee'=>$input['withdrawalFee'],'minAllowed'=>$input['minAllowed'],
            'canWithdraw'=>$input['canWithdraw'],'feeType'=>1,'status'=>$input['status']
        ];

        if (Fiat::where('id',$fiat->id)->update($dataFiat)){
            $fiat=Fiat::find($fiat->id);

            $dataResponse=[
                'name'=>$fiat->name,'code'=>$fiat->code,
                'usdRate'=>$fiat->rateUsd,'ngnRate'=>$fiat->rateNGN,
                'buyRate'=>$fiat->buyRate,'sellRate'=>$fiat->sellRate,
                'symbol'=>$fiat->sign,'country'=>$fiat->country,
                'settlementPeriod'=>$fiat->settlementPeriod,
                'verifiedLimit'=>$fiat->verifiedLimit,'unverifiedLimit'=>$fiat->unverifiedLimit,
                'withdrawalFee'=>$fiat->withdrawalFee,'minAllowed'=>$fiat->minAllowed,
                'canWithdraw'=>($fiat->canWithdraw==1)?'yes':'no',
                'status'=>($fiat->status==1)?'active':'inactive'
            ];
            return $this->sendResponse($dataResponse,'successfully updated');
        }
        return $this->sendError('fiat.error',['error'=>'Something went wrong'],422);
    }
}
