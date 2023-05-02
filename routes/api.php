<?php

use App\Http\Controllers\Mobile\Auth\Login;
use App\Http\Controllers\Mobile\Auth\Register;
use App\Http\Controllers\Mobile\Auth\ResetPassword;
use App\Http\Controllers\Mobile\Auth\TwoFactor;
use App\Http\Controllers\Mobile\Auth\VerifyEmail;
use App\Http\Controllers\UserModules\BalanceData;
use App\Http\Controllers\UserModules\SignalData;
use App\Http\Controllers\UserModules\TransactionModule\BuyData;
use App\Http\Controllers\UserModules\TransactionModule\FiatDepositData;
use App\Http\Controllers\UserModules\TransactionModule\FiatWithdrawalData;
use App\Http\Controllers\UserModules\TransactionModule\SellData;
use App\Http\Controllers\UserModules\TransactionModule\SwapData;
use App\Http\Controllers\UserModules\TransactionModule\TransactionData;
use App\Http\Controllers\UserModules\TransactionModule\WithdrawalData;
use App\Http\Controllers\UserModules\UserData;
use App\Http\Controllers\UserModules\WalletData;
use App\Http\Controllers\Utilities\Utilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* ==========================================================================*
 *                                                                           *
 *                                                                           *
 *                      AUTHENTICATIONS ROUTE                                *
 *                                                                           *
 *                                                                           *
 * ==========================================================================*/
Route::post('register',[Register::class,'authenticate'])->name('auth.register');
Route::post('login',[Login::class,'authenticate'])->name('auth.login');
Route::post('recover-password',[ResetPassword::class,'authenticate'])->name('auth.recover-password');
//Token based verification. Must be having the Bearer Token
Route::middleware('auth:sanctum')->group(function (){
    Route::post('verify-email',[VerifyEmail::class,'authenticate'])
        ->name('auth.verify-email');//verify code
    Route::post('resend-email-verification',[VerifyEmail::class,'resendVerificationMail'])
        ->name('auth.resend-email-verify');//resend verification mail
    Route::post('two-factor',[TwoFactor::class,'authenticate'])
        ->name('auth.two-factor');//verify code
    Route::post('resend-two-factor',[TwoFactor::class,'resendTwoFactor'])
        ->name('auth.resend-two-factor');//resend two factor code
    Route::post('verify-reset-password',[ResetPassword::class,'authenticatePasswordResetCode'])
        ->name('auth.authenticate-reset-password');
    Route::post('reset-password',[ResetPassword::class,'ResetPassword'])->name('auth.reset-password');
    Route::post('resend-password-reset',[ResetPassword::class,'resendPasswordReset'])
        ->name('auth.resend-password-reset');//resend two factor code

    //Data in the UserData Module
    Route::post('user/user_details',[UserData::class,'getLoggedInUserDetails']);
    Route::post('user/set_phone',[UserData::class,'setPhone'])
        ->middleware('abilities:user:account');
    Route::post('user/enter_phone_pin',[UserData::class,'verifyPhone'])
        ->middleware('abilities:user:account');
    Route::post('user/resend_phone_verify',[UserData::class,'resendPhoneVerify'])
        ->middleware('abilities:user:account');
    Route::post('user/set_address',[UserData::class,'setAddress'])
        ->middleware('abilities:user:account');
    Route::post('user/set_bvn',[UserData::class,'setBVN'])
        ->middleware('abilities:user:account');
    Route::post('user/set_id',[UserData::class,'setIDVerification'])
        ->middleware('abilities:user:account');
    Route::post('user/set_photo',[UserData::class,'submitPhoto'])
        ->middleware('abilities:user:account');
    Route::post('user/set_password',[UserData::class,'setPassword'])
        ->middleware('abilities:user:account');
    Route::post('user/set_profile',[UserData::class,'setProfile'])
        ->middleware('abilities:user:account');
    Route::post('user/set_currency',[UserData::class,'setCurrency'])
        ->middleware('abilities:user:account');
    Route::post('user/set_payment_method',[UserData::class,'setBank'])
        ->middleware('abilities:user:account');
    Route::post('user/get_payment_methods',[UserData::class,'getUserBank'])
        ->middleware('abilities:user:account');
//    Route::post('user/get_faqs',[UserData::class,''])
//        ->middleware('abilities:user:account');

    // Routes for WalletData
    Route::get('user/get_user_wallets',[WalletData::class,'getUserWallets'])
        ->middleware('abilities:user:account');
    Route::get('user/get_user_wallets/{asset}',[WalletData::class,'getSpecificUserWallets'])
        ->middleware('abilities:user:account');
    //Routes for Balance Module
    Route::post('user/get_crypto_balance/{asset}',[BalanceData::class,'getUserCryptoBalance'])
        ->middleware('abilities:user:account');
    Route::post('user/get_fiat_balance/{fiat}',[BalanceData::class,'getUserFiatBalance'])
        ->middleware('abilities:user:account');

    //Route for Deposit Module
    Route::post('user/get_user_deposits/{page?}',[UserData::class,'getDeposits'])
        ->middleware('abilities:user:account');
    Route::post('user/get_user_deposits_asset/{asset}/{page?}',[UserData::class,'getDepositByAsset'])
        ->middleware('abilities:user:account');
    //Buy Module
    Route::post('user/buy',[BuyData::class,'buyCrypto'])
        ->middleware('abilities:user:account');
    Route::post('user/get_user_purchases/{asset}/{page?}',[BuyData::class,'getUserPurchasesAsset'])
        ->middleware('abilities:user:account');
    Route::post('user/get_user_purchases/{page?}',[BuyData::class,'getUserPurchases'])
        ->middleware('abilities:user:account');
    //sell Module
    Route::post('user/sell',[SellData::class,'sellCrypto'])
        ->middleware('abilities:user:account');
    Route::post('user/get_user_sales/{asset}/{page?}',[SellData::class,'getUserSalesByAsset'])
        ->middleware('abilities:user:account');
    Route::post('user/get_user_sales/{page?}',[SellData::class,'getUserSales'])
        ->middleware('abilities:user:account');

    //Fiat Deposit module
    Route::get('get_system_bank_account',[FiatDepositData::class,'getSystemFiatAccount'])
        ->middleware('abilities:user:account');
    Route::post('user/initiate_deposit',[FiatDepositData::class,'fundFiat'])
        ->middleware('abilities:user:account');
    Route::post('user/confirm_fiat_deposit',[FiatDepositData::class,'confirmFiatFunding'])
        ->middleware('abilities:user:account');
    Route::get('user/get_user_fiat_deposits',[UserData::class,'getUserFiatDeposits'])
        ->middleware('abilities:user:account');

    //Fiat Withdrawal Module
    Route::post('user/initiate_withdrawal',[FiatWithdrawalData::class,'withdrawFiatFunds'])
        ->middleware('abilities:user:account');
    Route::get('user/get_user_fiat_withdrawals',[FiatWithdrawalData::class,'getUserFiatWithdrawals'])
        ->middleware('abilities:user:account');

    //Swap Module
    Route::post('user/initiate_swap',[SwapData::class,'processSwap'])
        ->middleware('abilities:user:account');
    Route::get('user/get_swaps',[SwapData::class,'getUserSwapList'])
        ->middleware('abilities:user:account');

    //Signal Module Route
    Route::post('user/enroll_signal',[SignalData::class,'enrollInSignalPackage'])
        ->middleware('abilities:user:account');
    Route::get('user/get_signal_room',[SignalData::class,'getUserSignal'])
        ->middleware('abilities:user:account');
    Route::post('user/enroll_signal/crypto',[SignalData::class,'paySignalUsingCrypto'])
        ->middleware('abilities:user:account');

    //Crypto withdrawal Module
    Route::get('user/get_recipient_details_phone/{phone}',[WithdrawalData::class,'getWithdrawalRecipientDetailByPhone'])
        ->middleware('abilities:user:account');
    Route::get('user/get_recipient_details_email/{email}',[WithdrawalData::class,'getWithdrawalRecipientDetailByEmail'])
        ->middleware('abilities:user:account');
    Route::post('user/send_crypto_to_user',[WithdrawalData::class,'sendCryptoUser'])
        ->middleware('abilities:user:account');
    Route::post('user/withdraw_crypto',[WithdrawalData::class,'sendCryptoToExternal'])
        ->middleware('abilities:user:account');

    //Transaction data Module
    Route::get('user/fiat_transactions',[TransactionData::class,'fetchUserFiatTransactions']);
    Route::get('user/crypto_transactions/{asset}',[TransactionData::class,'fetchUserCryptoTransactions']);
    Route::post('user/otp/{purpose}',[TransactionData::class,'sendRequestForOtp'])
        ->middleware('abilities:user:account');
});
Route::get('countries',[Utilities::class,'fetchCountries']);
Route::get('get_currencies',[Utilities::class,'getCurrencies']);
Route::get('get_help',[Utilities::class,'getContact']);
Route::get('get_faq',[Utilities::class,'getFaq']);
Route::get('get_tokens/{fiat?}',[Utilities::class,'getSupportedTokens']);
Route::get('get_network_fee/{asset}',[Utilities::class,'getSendingFee']);
Route::get('get_recipient_detail/{email}',[Utilities::class,'getRecipientDetails']);
Route::get('get_recipient_detail_phone/{phone}',[Utilities::class,'getRecipientDetailsPhone']);
Route::get('get_exchange_rate/{asset}/{fiat?}',[Utilities::class,'getRateCryptoNow']);
Route::get('get_crypto_exchange_rate/{asset}/{assetTo}/{amount?}',
    [Utilities::class,'getCryptoToCryptoRate']);
Route::get('get_signal_packages',[Utilities::class,'fetchSignalPackages']);
Route::post('get_coin_rate',[Utilities::class,'getUsdToCryptoRate']);
Route::post('get_coin_rate_ngn',[Utilities::class,'getNGNToCrypto']);
Route::post('get_crypto_to_usd_rate',[Utilities::class,'convertFromCryptoToUsd']);
Route::post('get_crypto_to_ngn_rate',[Utilities::class,'convertFromCryptoToNgn']);

Route::post('get_usd_to_ngn/{amount?}',[Utilities::class,'convertUsdToNgn']);

Route::get('testing',[UserData::class,'testEndpointsNew']);
