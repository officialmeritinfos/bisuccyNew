<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Faqs extends BaseController
{
    public function landingPage()
    {

    }
    //get all faqs
    public function getFaqs()
    {
        $faqs = Faq::where('status',1)->get();
        if ($faqs->count()<1){
            return $this->sendError('faq.error',['error'=>'Nothing found.']);
        }
        $dataCo = [];

        foreach ($faqs as $faq) {
            $data=[
                'question'=>$faq->question,'answer'=>$faq->answer,
                'id'=>$faq->id,'status'=>($faq->status==1)?'active':'inactive'
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    //get a single faq
    public function getFaq($id)
    {
        $faq = Faq::where('id',$id)->first();
        if (empty($faq)){
            return $this->sendError('faq.error',['error'=>'Nothing found.']);
        }
        $dataResponse=[
            'question'=>$faq->question,'answer'=>$faq->answer,
            'id'=>$faq->id,'status'=>($faq->status==1)?'active':'inactive'
        ];
        return $this->sendResponse($dataResponse, 'retrieved');
    }
    public function createFaq(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(),[
            'question'=>['required','string'],
            'answer'=>['required','string']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }

        $input = $validator->validated();
        //check if faq exists
        $faqExists = Faq::where('question',$input['question'])->first();
        if (!empty($faqExists)){
            return $this->sendError('faq.error',['error'=>'Faq already added.'],422);
        }

        $dataFaq=[
            'question'=>$input['question'],'answer'=>$input['answer'],'status'=>1
        ];
        $faq=Faq::create($dataFaq);
        if (!empty($faq)){

            $dataResponse = [
                'admin'=>$user->name,
                'question'=>$faq->question,'answer'=>$faq->answer,
                'id'=>$faq->id,'status'=>($faq->status==1)?'active':'inactive'
            ];
            return $this->sendResponse($dataResponse,'added');
        }
        return $this->sendError('faq.error',['error'=>'Something went wrong'],422);
    }
    //update FAQ
    public function updateFaq(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(),[
            'question'=>['required','string'],
            'answer'=>['required','string'],
            'id'=>['required','numeric'],
            'status'=>['required','numeric']
        ])->stopOnFirstFailure();
        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }

        $input = $validator->validated();
        //check if faq exists
        $faqExists = Faq::where('question',$input['question'])->where('id','!=',$input['id'])->first();
        if (!empty($faqExists)){
            return $this->sendError('faq.error',['error'=>'Faq already added.'],422);
        }

        $dataFaq=[
            'question'=>$input['question'],'answer'=>$input['answer'],'status'=>$input['status']
        ];
        if (Faq::where('id',$input['id'])->update($dataFaq)){
            $faq = Faq::where('id',$input['id'])->first();
            $dataResponse = [
                'admin'=>$user->name,
                'question'=>$faq->question,'answer'=>$faq->answer,
                'id'=>$faq->id,'status'=>($faq->status==1)?'active':'inactive'
            ];
            return $this->sendResponse($dataResponse,'Updated');
        }
        return $this->sendError('faq.error',['error'=>'Something went wrong'],422);
    }

    public function remove($id)
    {
        $user = Auth::user();

        $faqExists = Faq::where('id',$id)->first();
        if (empty($faqExists)){
            return $this->sendError('faq.error',['error'=>'Nothing found'],422);
        }
        if (Faq::where('id',$id)->delete()){
            $dataResponse=[
                'faq'=>'removed'
            ];
            return $this->sendResponse($dataResponse,'deleted');
        }
        return $this->sendError('faq.error',['error'=>'Something went wrong'],422);
    }
}
