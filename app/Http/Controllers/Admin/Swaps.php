<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Swap;
use App\Models\SystemFiatAccount;
use App\Models\User;
use Illuminate\Http\Request;

class Swaps extends BaseController
{
    public function landingPage()
    {
        return view('swaps.index');
    }
    //get all Swaps
    public function getSwaps($index=0)
    {
        $swaps = Swap::offset($index*50)->limit(50)->get();
        $dataCo=[];
        foreach ($swaps as $swap) {
            $data = $this->getSwapData($swap);
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    //get a deposits
    public function getSwapId($id)
    {
        $swap = Swap::where('id',$id)->first();

        $data = $this->getSwapData($swap);
        return $this->sendResponse($data, 'retrieved');
    }

    /**
     * @param $swap
     * @return array
     */
    protected function getSwapData($swap): array
    {
        $user = User::where('id', $swap->user)->first();
        switch ($swap->status) {
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
            'id'=>$swap->id,
            'amountCredit' => $swap->amountCredit,
            'user' => $user->name,
            'from'=>$swap->assetFrom,
            'to'=>$swap->assetTo,
            'amountFrom'=>$swap->amountFrom,
            'amountTo'=>$swap->amountTo,
            'charge'=>$swap->charge,
            'date' => strtotime($swap->created_at),
            'status' => $status,
        ];
        return $data;
    }
}
