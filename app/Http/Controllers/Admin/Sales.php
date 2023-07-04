<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;

class Sales extends BaseController
{
    public function landingPage()
    {

    }
    //get all sales
    public function getSales($index=0)
    {
        $sales = Sale::offset($index*50)->limit(50)->get();
        $dataCo=[];
        foreach ($sales as $sale) {
            $data = $this->getSaleData($sale);
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    //get sale by id
    public function getSaleId($id)
    {
        $purchase = Sale::where('id',$id)->first();
        $data = $this->getSaleData($purchase);
        return $this->sendResponse($data, 'retrieved');
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
            'user' => $sale->name,
            'dateInitiated' => $sale->created_at

        ];
        return $data;
    }
}
