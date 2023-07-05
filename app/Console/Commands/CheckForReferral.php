<?php

namespace App\Console\Commands;

use App\Models\FiatDeposit;
use App\Models\GeneralSetting;
use App\Models\ReferralEarning;
use App\Models\Sale;
use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Console\Command;

class CheckForReferral extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:referral';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks and credits referral bonus';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $web = GeneralSetting::find(1);

        $users = User::where('status',1)->get();
        if ($users->count()>0){
            foreach ($users as $user) {
                //check user's total fiat deposit
                $sumDeposit = FiatDeposit::where('user',$user->id)->where('status',1)->sum('usdPaid');
                //total crypto swaps
                $sumCrypto = Sale::where('user',$user->id)->where('status',1)->sum('amountCredit');

                $total = $sumCrypto+$sumDeposit;
                if ($total>=$web->refBonusCriteria){
                    //check if user wqs referred
                    if (!empty($user->refBy)){
                        $referral = User::where('userRef',$user->refBy)->first();
                        if (!empty($referral)){
                         //check if user has been credited before
                            $hasBonus = ReferralEarning::where(['user'=>$user->id,'referrer'=>$referral->id])->first();
                            if (empty($hasBonus)){

                                $referral->refBalance = $referral->refBalance+$web->refBonus;

                                $referral->save();

                                $earning = ReferralEarning::create([
                                    'user'=>$user->id,
                                    'referrer'=>$referral->id,
                                    'amount'=>$web->refBonus,
                                    'status'=>1
                                ]);
                                if (!empty($earning)){
                                    //send notification
                                    $message = "you have received $".$web->refBonus." as referral earning";
                                    $referral->notify(new UserNotification($referral,$message,'Referral Earning received.'));
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
