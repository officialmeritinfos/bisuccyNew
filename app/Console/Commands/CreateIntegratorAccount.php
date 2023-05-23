<?php

namespace App\Console\Commands;

use App\Models\Coin;
use App\Models\GeneralSetting;
use App\Models\SystemAccount;
use App\Models\User;
use App\Models\Wallet;
use App\Regular\OneLiquidity;
use App\Traits\PubFunctions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateIntegratorAccount extends Command
{
    use PubFunctions;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:integrator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Integrator account';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function __construct() {
        parent::__construct();
    }

    public function handle()
    {
//        $this->createIntegrator();
//        $this->createFloatLedger();
//        $this->generateToken();
//        $this->createFloatLedger();
//        $this->initiateDeposit();
//        $this->fetchDeposits();
//        $this->generateMainWallet();
    }
    //create integrator
    public function createIntegrator()
    {
        try {
            $web = GeneralSetting::find(1);

            $data = [
                'firstName'=>'Michael',
                'lastName'=>'Erastus',
                'country'=>'NGA',
                'businessName'=>'Meritinfos Company Limited',
                'email'=>'michaelerastus9@gmail.com',
                'webhookUrl'=>'https://webhook.site/d51e80bf-b12e-43d0-a824-f7e7e1168924',
                'contactNumber'=>'+2348147298815',
                'floatCurrencies'=>[
                    'USD','NGN','EUR','GBP'
                ]
            ];
            $oneLiquidity = new OneLiquidity();
            $response = $oneLiquidity->registerIntegrator($data);
            if ($response->successful()){
                Log::info($response);
            }else{
                Log::info('Error: '.$response);
            }
        }catch (\Exception $exception){
            Log::info($exception);
        }
    }
    //generate authentication token
    public function generateToken()
    {
        $data = ['email'=>'michaelerastus9@gmail.com'];
        $oneLiquidity = new OneLiquidity();
        $response = $oneLiquidity->generateToken($data);
        if ($response->successful()){
            Log::info($response);
        }else{
            Log::info('Error: '.$response);
        }
    }
    //create float ledger
    public function createFloatLedger()
    {
        $oneLiquidity = new OneLiquidity();
        $currencies = ['USD','NGN','EUR'];

        foreach ($currencies as $currency) {
            $data=[
                'currency'=>$currency
            ];

            $response = $oneLiquidity->createFloatLedger($data);

            if ($response->successful()){
                Log::info($response);
            }else{
                Log::info('Error: '.$response);
            }
        }
    }
    //initiate a deposit
    public function initiateDeposit()
    {
        $oneLiquidity = new OneLiquidity();

        $currencies = [
            'USD',
            'NGN',
            'EUR'
        ];

        foreach ($currencies as $currency) {
            $data=[
                'currency'=>$currency,
                'amount'=>100000
            ];

            $response = $oneLiquidity->initiateDeposit($data);

            if ($response->successful()){
                Log::info($response);
            }else{
                Log::info('Error: '.$response);
            }
        }
    }
    //fetch deposit
    public function fetchDeposits()
    {
        $oneLiquidity = new OneLiquidity();

        $deposits = [
            '71705451-91a7-4cf0-9a2a-0776785e7b05',
            'e5b57f1a-613e-4635-a744-80e98a93a45b',
        ];

        foreach ($deposits as $deposit) {
            $this->info('Starting the lookup for Deposit ID: '.$deposit);

            $response = $oneLiquidity->fetchDeposit($deposit);

            if ($response->successful()){

                $this->info('A successful response was gotten:'.$response);
                Log::info($response);
            }else{
                $response = $response->json();
                $this->error('An error response was received:'.$response['message']);
                Log::info('Error: '.$response['message']);
            }
        }
    }
    //generate Main wallets
    public function generateMainWallet()
    {
        $oneLiquidity = new OneLiquidity();
        $coins = Coin::where(['status'=>1,'hasTest'=>1])->get();
        if ($coins->count()>0){
            foreach ($coins as $coin) {
                //get system account
                $systemAccount = SystemAccount::where('asset',$coin->asset)->first();
                if (empty($systemAccount)){
                    $data =[
                        'currency'=>$coin->asset
                    ];
                    $response = $oneLiquidity->generateMainWallet($data);
                    if ($response->successful()){
                        $data = json_decode($response,1);
                        $walletId = $data['data']['walletId'];
                        $addresses = $data['data']['addresses'];
                        foreach ($addresses as $address) {
                            SystemAccount::create([
                                'asset'=>$coin->asset,'address'=>$address['address'],
                                'network'=>$address['network'],'walletId'=>$walletId,
                            ]);
                        }
                        Log::info($response);
                    }else{
                        Log::info('Error: '.$response);
                    }
                }
            }
        }
    }

}
