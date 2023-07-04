<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Signal;
use App\Models\SignalInput;
use App\Models\SignalNotification;
use App\Models\SignalPackage;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class Signals extends BaseController
{
    use PubFunctions;
    public function landingPage()
    {

    }

    public function addSignal(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title'=>['required','string'],
            'package'=>['required','string'],
            'photo'=>['nullable','mimes:jpg,bmp,png,jpeg,gif'],
            'inputs[]'=>['required']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input=$validator->validated();
        //check if the signal package exists
        if (strtolower($input['package']) !='all'){
            $packageExists = SignalPackage::where('id',$input['package'])->first();
            if (empty($packageExists)){
                return $this->sendError('package.error',['error'=>'Package selected not found.'],422);
            }
        }
        //check if file was uploaded
        if (!empty($input['photo'])){
            $image = time() . '_' . $request->file('photo')->hashName();
            $request->file('photo')->move(public_path('signals/'), $image);
            $moveImage = File::exists(public_path('signals/'.$image));
            if ($moveImage !=true){
                return $this->sendError('file.error', ['error' => 'Unable to upload image'], '421');
            }
        }else{
            $image = '';
        }
        $reference = $this->generateRef('signals','reference',25);
        $dataSignal = [
            'title'=>$input['title'],
            'reference'=>$reference,
            'package'=>$input['package'],
            'photo'=>$image,
        ];

        $signal = Signal::create($dataSignal);
        if (!empty($signal)){
            for ($i=0;$i<count($input['inputs']);$i++){
                $dataInputs = [
                    'signalRef'=>$signal->reference,
                    'content'=>$input['inputs'][$i]
                ];

                SignalInput::create($dataInputs);
            }
            $dataNotification =[
                'package'=>$input['package'],
                'message'=>'New signal has been published to the room',
                'subject'=>'New signal published',
                'timeToBroadcast'=>time(),
            ];
            SignalNotification::create($dataNotification);
            $dataResponse=[
                'id'=>$signal->id,'reference'=>$signal->reference,
                'title'=>$signal->title,
                'createdAt'=>strtotime($signal->created_at)
            ];
            return $this->sendResponse($dataResponse,'Signal added successfully');
        }
        return $this->sendError('signal.error',['error'=>'Something went wrong'],421);
    }
    //get signals
    public function getSignals($index = 0)
    {
        $signals = Signal::offset($index*50)->limit(50)->get();
        if ($signals->count()<1){
            return $this->sendError('signal.error',['error'=>'No data found']);
        }
        $dataCo=[];
        foreach ($signals as $signal) {
            $data=[
                'id'=>$signal->id,
                'title'=>$signal->title,
                'photo'=>asset('signals/'.$signal->photo),
                'createdAt'=>strtotime($signal->created_at),
                'status'=>($signal->status==1)?'active':'inactive'
            ];

            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    //get signal by id
    public function getSignalById($id)
    {
        $signal = Signal::where('id',$id)->first();
        if (empty($signal)){
            return $this->sendError('signal.error',['error'=>'No data found']);
        }
        //get the signal inputs
        $inputs = SignalInput::where('signalRef',$signal->reference)->get();
        $dataInput=[];
        foreach ($inputs as $input) {
            $dataIn=[
                'content'=>$input->content
            ];
            $dataInput[]=$dataIn;
        }
        //collate for response
        $data=[
            'id'=>$signal->id,
            'title'=>$signal->title,
            'photo'=>asset('signals/'.$signal->photo),
            'createdAt'=>strtotime($signal->created_at),
            'status'=>($signal->status==1)?'active':'inactive',
            'inputs'=>$dataInput
        ];
        return $this->sendResponse($data, 'retrieved');
    }
    //get signal inputs
    public function getSignalInputs($id)
    {
        $signal = Signal::where('id',$id)->first();
        if (empty($signal)){
            return $this->sendError('signal.error',['error'=>'No data found']);
        }
        //get the signal inputs
        $inputs = SignalInput::where('signalRef',$signal->reference)->get();
        $dataInput=[];
        foreach ($inputs as $input) {
            $dataIn=[
                'content'=>$input->content
            ];
            $dataInput[]=$dataIn;
        }
        return $this->sendResponse($dataInput, 'retrieved');
    }
}
