<?php

use App\Http\Controllers\Mobile\Auth\Login;
use App\Http\Controllers\Mobile\Auth\Register;
use App\Http\Controllers\Mobile\Auth\ResetPassword;
use App\Http\Controllers\Mobile\Auth\TwoFactor;
use App\Http\Controllers\Mobile\Auth\VerifyEmail;
use App\Http\Controllers\Utilities\Countries;
use App\Http\Controllers\Utilities\Users;
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

    Route::post('user/set_phone',[Users::class,'setPhone'])
        ->middleware('abilities:user:account');
    Route::post('user/enter_phone_pin',[Users::class,'verifyPhone'])
        ->middleware('abilities:user:account');
    Route::post('user/resend_phone_verify',[Users::class,'resendPhoneVerify'])
        ->middleware('abilities:user:account');
    Route::post('user/set_address',[Users::class,'setAddress'])
        ->middleware('abilities:user:account');
    Route::post('user/set_bvn',[Users::class,'setBVN'])
        ->middleware('abilities:user:account');
    Route::post('user/set_id',[Users::class,'setIDVerification'])
        ->middleware('abilities:user:account');
    Route::post('user/set_photo',[Users::class,'submitPhoto'])
        ->middleware('abilities:user:account');
    Route::post('user/set_password',[Users::class,'setPassword'])
        ->middleware('abilities:user:account');
    Route::post('user/set_profile',[Users::class,'setProfile'])
        ->middleware('abilities:user:account');
    Route::post('user/set_currency',[Users::class,'setCurrency'])
        ->middleware('abilities:user:account');
    Route::post('user/set_payment_method',[Users::class,'setBank'])
        ->middleware('abilities:user:account');
    Route::post('user/get_payment_methods',[Users::class,'getUserBank'])
        ->middleware('abilities:user:account');
//    Route::post('user/get_faqs',[Users::class,''])
//        ->middleware('abilities:user:account');
    Route::get('user/get_user_wallets',[Users::class,'getUserWallets'])
        ->middleware('abilities:user:account');
    Route::get('user/get_user_wallets/{asset}',[Users::class,'getSpecificUserWallets'])
        ->middleware('abilities:user:account');
    Route::post('user/get_user_deposits/{page?}',[Users::class,'getDeposits'])
        ->middleware('abilities:user:account');
    Route::post('user/get_user_deposits_asset/{asset}/{page?}',[Users::class,'getDepositByAsset'])
        ->middleware('abilities:user:account');
    Route::post('user/get_crypto_balance/{asset}',[Countries::class,'getUserCryptoBalance'])
        ->middleware('abilities:user:account');
    Route::post('user/get_fiat_balance/{fiat}',[Countries::class,'getUserFiatBalance'])
        ->middleware('abilities:user:account');
    //buy
    Route::post('user/buy',[Users::class,'buyCrypto'])
        ->middleware('abilities:user:account');
    //sell
    Route::post('user/sell',[Users::class,'sellCrypto'])
        ->middleware('abilities:user:account');
    //get sales
    Route::post('user/get_user_sales/{asset}/{page?}',[Users::class,'getUserSalesByAsset'])
        ->middleware('abilities:user:account');
    Route::post('user/get_user_sales/{page?}',[Users::class,'getUserSales'])
        ->middleware('abilities:user:account');
    //get purchases
    Route::post('user/get_user_purchases/{asset}/{page?}',[Users::class,'getUserPurchasesAsset'])
        ->middleware('abilities:user:account');
    Route::post('user/get_user_purchases/{page?}',[Users::class,'getUserPurchases'])
        ->middleware('abilities:user:account');
    //fetch system bank account
    Route::get('get_system_bank_account',[Users::class,'getSystemFiatAccount'])
        ->middleware('abilities:user:account');
    //initiate deposit
    Route::post('user/initiate_deposit',[Users::class,'fundFiat'])
        ->middleware('abilities:user:account');
    //confirm deposit
    Route::post('user/confirm_fiat_deposit',[Users::class,'confirmFiatFunding'])
        ->middleware('abilities:user:account');
    //get deposits
    Route::get('user/get_user_fiat_deposits',[Users::class,'getUserFiatDeposits'])
        ->middleware('abilities:user:account');
    //initiate fiat withdrawal
    Route::post('user/initiate_withdrawal',[Users::class,'withdrawFiatFunds'])
        ->middleware('abilities:user:account');
    Route::get('user/get_user_fiat_withdrawals',[Users::class,'getUserFiatWithdrawals'])
        ->middleware('abilities:user:account');
    //initiate swap
    Route::post('user/initiate_swap',[Users::class,'processSwap'])
        ->middleware('abilities:user:account');
    Route::get('user/get_swaps',[Users::class,'getUserSwapList'])
        ->middleware('abilities:user:account');
    //enroll on signal
    Route::post('user/enroll_signal',[Users::class,'enrollInSignalPackage'])
        ->middleware('abilities:user:account');
    Route::get('user/get_signal_room',[Users::class,'getUserSignal'])
        ->middleware('abilities:user:account');
    Route::post('user/enroll_signal/crypto',[Users::class,'paySignalUsingCrypto'])
        ->middleware('abilities:user:account');
    //send coin out to other users
    Route::get('user/get_recipient_details_phone/{phone}',[Users::class,'getWithdrawalRecipientDetailByPhone'])
        ->middleware('abilities:user:account');
    Route::get('user/get_recipient_details_email/{email}',[Users::class,'getWithdrawalRecipientDetailByEmail'])
        ->middleware('abilities:user:account');
    Route::post('user/send_crypto_to_user',[Users::class,'sendCryptoUser'])
        ->middleware('abilities:user:account');
    Route::post('user/withdraw_crypto',[Users::class,'sendCryptoToExternal'])
        ->middleware('abilities:user:account');
    //get user details
    Route::post('user/user_details',[Users::class,'getLoggedInUserDetails']);

    Route::get('user/fiat_transactions',[Users::class,'fetchUserFiatTransactions']);
    Route::get('user/crypto_transactions/{asset}',[Users::class,'fetchUserCryptoTransactions']);
    //Transaction Otps
    Route::post('user/otp/{purpose}',[Users::class,'sendRequestForOtp'])
        ->middleware('abilities:user:account');
});
Route::get('countries',[Countries::class,'fetchCountries']);
Route::get('get_currencies',[Countries::class,'getCurrencies']);
Route::get('get_help',[Countries::class,'getContact']);
Route::get('get_faq',[Countries::class,'getFaq']);
Route::get('get_tokens/{fiat?}',[Countries::class,'getSupportedTokens']);
Route::get('get_network_fee/{asset}',[Countries::class,'getSendingFee']);
Route::get('get_recipient_detail/{email}',[Countries::class,'getRecipientDetails']);
Route::get('get_recipient_detail_phone/{phone}',[Countries::class,'getRecipientDetailsPhone']);
Route::get('get_exchange_rate/{asset}/{fiat?}',[Countries::class,'getRateCryptoNow']);
Route::get('get_crypto_exchange_rate/{asset}/{assetTo}/{amount?}',
    [Countries::class,'getCryptoToCryptoRate']);
Route::get('get_signal_packages',[Countries::class,'fetchSignalPackages']);
Route::post('get_coin_rate',[Countries::class,'getUsdToCryptoRate']);
Route::post('get_coin_rate_ngn',[Countries::class,'getNGNToCrypto']);
Route::post('get_crypto_to_usd_rate',[Countries::class,'convertFromCryptoToUsd']);
Route::post('get_crypto_to_ngn_rate',[Countries::class,'convertFromCryptoToNgn']);

Route::post('get_usd_to_ngn/{amount?}',[Countries::class,'convertUsdToNgn']);

Route::get('testing',[Users::class,'testEndpointsNew']);
