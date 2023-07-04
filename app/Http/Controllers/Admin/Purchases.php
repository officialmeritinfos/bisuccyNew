<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;

class Purchases extends BaseController
{
    public function landingPage()
    {

    }
    //get all purchases
    public function getPurchases($index=0)
    {
        $purchases = Purchase::offset($index*50)->limit(50)->get();
        $dataCo=[];
        foreach ($purchases as $purchase) {
            $data = $this->getPurchaseData($purchase);
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    //get purchase by id
    public function getPurchaseId($id)
    {
        $purchase = Purchase::where('id',$id)->first();
        $data = $this->getPurchaseData($purchase);
        return $this->sendResponse($data, 'retrieved');
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
}
