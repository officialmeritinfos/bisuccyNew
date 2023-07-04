<?php

use App\Http\Controllers\Admin\Auth\Login;
use App\Http\Controllers\Admin\Auth\RecoverPassword;
use App\Http\Controllers\Admin\Auth\TwoFactor;
use Illuminate\Support\Facades\Route;
/** ======================AUTHENTICATION FOR ADMIN =====================
 * The route name is prefixed with admin. e.g auth.login will generate
 * the full route http://127.0.0.1:8000/sysadmin/auth/login
 * This applies to every route in the admin.php namespace.
 * This can be changed in the RouteServiceProvider
 */
Route::get('login',[Login::class,'landingPage'])
    ->name('login');
Route::post('auth/process-login',[Login::class,'doLogin'])
    ->name('processLogin');
// Two-factor authentication
Route::get('two-factor',[TwoFactor::class,'landingPage'])
    ->name('twoFactor');
//processes the code entered
Route::post('auth/process-twoFactor',[TwoFactor::class,'authenticate'])
    ->name('processTwoFactor');
//Recover Password
Route::get('forgot-password',[RecoverPassword::class,'landingPage'])
    ->name('forgotPassword');
//processes if the email is accurate and send code
Route::post('auth/authenticate-forgot-password',[RecoverPassword::class,'processEmail'])
    ->name('authenticatePassword');
//input both code
Route::get('verify-password-recovery',[RecoverPassword::class,'enterCode'])
    ->name('verifyPasswordRecovery');
//process the reset code
Route::post('verify-password-recovery',[RecoverPassword::class,'processResetCode'])
    ->name('processResetCode');
//enter the password here
Route::get('change-password',[RecoverPassword::class,'recoverPassword'])
    ->name('changePassword');
//changes the password
Route::post('auth/recoverPassword',[RecoverPassword::class,'doChange'])
    ->name('recoverPassword');
