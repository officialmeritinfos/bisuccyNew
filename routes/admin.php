<?php

use App\Http\Controllers\Admin\Dashboard;
use App\Http\Controllers\Admin\Deposits;
use App\Http\Controllers\Admin\Faqs;
use App\Http\Controllers\Admin\FiatDeposits;
use App\Http\Controllers\Admin\Fiats;
use App\Http\Controllers\Admin\FiatWithdrawals;
use App\Http\Controllers\Admin\GeneralSettings;
use App\Http\Controllers\Admin\Messages;
use App\Http\Controllers\Admin\Notifications;
use App\Http\Controllers\Admin\Permissions;
use App\Http\Controllers\Admin\Purchases;
use App\Http\Controllers\Admin\Sales;
use App\Http\Controllers\Admin\SignalEnrollmentPayments;
use App\Http\Controllers\Admin\SignalPackages;
use App\Http\Controllers\Admin\Signals;
use App\Http\Controllers\Admin\Staff;
use App\Http\Controllers\Admin\Swaps;
use App\Http\Controllers\Admin\SystemAccounts;
use App\Http\Controllers\Admin\SystemFiatAccounts;
use App\Http\Controllers\Admin\UserBanks;
use App\Http\Controllers\Admin\Users;
use App\Http\Controllers\Admin\UserWallets;
use App\Http\Controllers\Admin\Withdrawals;
use Illuminate\Support\Facades\Route;



/*====================== ADMIN DASHBOARD ROUTES =====================*/
/**
 *  This route group uses the TwoFactor Middleware to ensure that the admin
 *  Two-factor authentication was passed before taking them to the
 *  admin dashboard. If the two-factor was not passed, it returns an error
 *  instructing them to fulfil the two-fatcor first.
 *  The route name is prefixed with admin. e.g admin.index will generate
 * the full route http://127.0.0.1:8000/sysadmin/dashboard
 * This applies to every route in the admin.php namespace.
 * This can be changed in the RouteServiceProvider
 */

/*=======================DASHBOARD CONTROLLER ROUTES==============*/
    Route::get('dashboard', [Dashboard::class, 'landingPage'])->name('index');//landing page
    Route::get('set-pin', [Dashboard::class, 'setPinlandingPage'])->name('setPinlandingPage');// set pin landing page
    Route::post('dashboard/set-pin', [Dashboard::class, 'setPin'])
        ->name('setPin');//set account pin needed for approving transactions
    Route::get('dashboard/profile', [Dashboard::class, 'profileLandingPage'])
        ->name('getAdminProfile');//fetch the details of the logged in admin
    Route::get('dashboard/admin-details', [Dashboard::class, 'getAdminDetails'])
        ->name('adminDetails');//fetch the details of the logged in admin
    Route::get('dashboard/change-password', [Dashboard::class, 'changePasswordLandingPage'])
        ->name('changePassword');//landing page for changing password
    Route::post('dashboard/do-change-password', [Dashboard::class, 'doChangePassword'])
        ->name('resetPassword');//changes the password
    Route::get('dashboard/get-transactions-data',[Dashboard::class,'latestTransactions'])
        ->name('latest_transactions');//five latest transactions
    Route::get('dashboard/get-dashboard-data',[Dashboard::class,'dashboardData'])
        ->name('dashboard_data');//populate admin dashboard with some data
    /*=======================DEPOSIT CONTROLLER ROUTES==============*/
    Route::get('deposits', [Deposits::class, 'landingPage'])->name('deposits');//landing page
    Route::get('deposits/all/{index?}', [Deposits::class, 'getDeposits'])->name('getDeposits');//fetch all deposits
    /*=======================FAQ CONTROLLER ROUTES==============*/
    // Dec 07 2022 - Not doing FAQs...
    Route::get('faqs', [Faqs::class, 'landingPage'])->name('faqs');//landing page
    Route::get('faqs/fetch', [Faqs::class, 'getFaqs'])->name('getFaqs');//fetch all faqs
    Route::get('faqs/fetch/{id}', [Faqs::class, 'getFaq'])->name('faqDetail');//fetch a single faq
    Route::post('faqs/create', [Faqs::class, 'createFaq'])->name('createFaq');//creates an Faq resource
    Route::post('faqs/update', [Faqs::class, 'updateFaq'])->name('updateFaq');//updates an faq resource
    Route::get('faqs/remove', [Faqs::class, 'remove'])->name('removeFaq');//deletes an faq resource
    /*=======================FIAT DEPOSITS CONTROLLER ROUTES==============*/
    Route::get('fiat-deposits', [FiatDeposits::class, 'landingPage'])->name('fiatDeposits');//landing page
    Route::get('fiat-deposits/all/{index?}', [FiatDeposits::class, 'getDeposits'])
        ->name('getFiatDeposits');//fetches all deposits
    Route::get('fiat-deposits/{id}', [FiatDeposits::class, 'getDepositId'])
        ->name('depositDetail');//fetch a single deposit
    Route::post('fiat-deposits/approve', [FiatDeposits::class, 'approveDeposit'])
        ->name('approveDeposit');//approves a deposit
    Route::post('fiat-deposits/cancel', [FiatDeposits::class, 'cancelDeposit'])
        ->name('cancelDeposit');//cancels a deposit
    /*=======================FIATS CONTROLLER ROUTES==============*/
    Route::get('fiats', [Fiats::class, 'landingPage'])->name('fiats');//landing page
    Route::get('fiats/all', [Fiats::class, 'getFiats'])->name('getFiats');//fetch all fiats
    Route::get('fiats/create', [Fiats::class, 'createFiat'])->name('createFiat');//update fiats
    Route::post('fiats/create', [Fiats::class, 'updateFiat'])->name('updateFiats');//update fiats
    /*=======================FIAT WITHDRAWALS CONTROLLER ROUTES==============*/
    Route::get('fiat-withdrawals', [FiatWithdrawals::class, 'landingPage'])->name('fiatWithdrawals');//landing page
    Route::get('fiat-withdrawals/all/{index?}', [FiatWithdrawals::class, 'getWithdrawals'])
        ->name('getFiatWithdrawals');//fetch all fiat withdrawals
    Route::get('fiat-withdrawals/{id}', [FiatWithdrawals::class, 'getWithdrawalId'])
        ->name('fiatWithdrawalDetail');//fetch a single withdrawal
    Route::post('fiat-withdrawals/approve', [FiatWithdrawals::class, 'approveWithdrawal'])
        ->name('approveFiatWithdrawal');//approves the fiat withdrawal request
    /*=======================GENERAL SETTINGS CONTROLLER ROUTES==============*/
    Route::get('settings', [GeneralSettings::class, 'landingPAge'])->name('settings');//landing page
    Route::post('settings/edit', [GeneralSettings::class, 'editSettings'])->name('editSettings');//edit settings
    Route::get('settings/get', [GeneralSettings::class, 'getSettings'])
        ->name('getSettings');//fetches the site configurations
    /*=======================MESSAGES CONTROLLER ROUTES==============*/
    Route::get('messages', [Messages::class, 'landingPage'])
        ->name('messages');//popup notifications for app users - landing page
    Route::get('messages/create', [Messages::class, 'createMessageLanding'])->name('createMessageLanding');
    Route::get('messages/all/{index?}', [Messages::class, 'getMessages'])
        ->name('getMessages');//fetch all messages
    Route::get('messages/{id}', [Messages::class, 'getMessageDetail'])->name('messageId');//fetch a singe message
    Route::post('messages/create', [Messages::class, 'doCreateMessage'])->name('createMessage');
    /*=======================NOTIFICATIONS CONTROLLER ROUTES==============*/
    Route::get('notifications', [Notifications::class, 'landingPage'])
        ->name('notifications');//landing page
    Route::get('notifications/create', [Notifications::class, 'createNotificationLanding'])
    ->name('createNotificationLanding');//landing page
    Route::get('notifications/all/{index?}', [Notifications::class, 'getNotifications'])
        ->name('getNotifications');//get all notifications
    Route::get('notifications/{id}', [Notifications::class, 'getNotificationDetail'])
        ->name('notificationDetails');//fetch a single notification;
    /*=======================PERMISSIONS/ROLE CONTROLLER ROUTES==============*/
    Route::get('permissions', [Permissions::class, 'landingPage'])->name('permissions');//landing page
    Route::get('permissions/roles', [Permissions::class, 'getRoles'])->name('getPermissions');//fetch all
    Route::get('permissions/create', [Permissions::class, 'createRoleLandingPage'])->name('createRoleLandingPage');//creates
    Route::post('/api/permissions/create', [Permissions::class, 'createRole'])->name('createPermission');//creates
    /*=======================PURCHASES CONTROLLER ROUTES==============*/
    Route::get('purchases', [Purchases::class, 'landingPage'])->name('purchases');//landing page
    Route::get('purchases/all/{index?}', [Purchases::class, 'getPurchases'])->name('getPurchases');//fetch all purchases
    Route::get('purchases/{id}', [Purchases::class, 'getPurchaseId'])->name('purchaseDetails');//fetch a single purchase
    /*=======================SALES CONTROLLER ROUTES==============*/
    Route::get('sales', [Sales::class, 'landingPage'])->name('sales');//landing page
    Route::get('sales/all/{index?}', [Sales::class, 'getSales'])->name('getSales');//fetch all sales
    Route::get('sales/{id}', [Sales::class, 'getSaleId'])->name('salesDetail');//fetch a single sales
    /*=======================SIGNAL ENROLLMENT PAYMENT CONTROLLER ROUTES==============*/
    Route::get('signal-enrollment-payments', [SignalEnrollmentPayments::class, 'landingPage'])
        ->name('signalPayments');//landing page
    Route::get('signal-payments/all/{index?}', [SignalEnrollmentPayments::class, 'getPayments'])
        ->name('getSignalPayments');//fetches all payments
    Route::get('signal-payments/{id}', [SignalEnrollmentPayments::class, 'getPaymentId'])
        ->name('getSignalPaymentId');//fetch a single payment
    Route::post('signal-payments/approve', [SignalEnrollmentPayments::class, 'approveSignalPayment'])
        ->name('approveSignalPayment');//approve signal payment
    /*=======================SIGNAL PACKAGES CONTROLLER ROUTES==============*/
    Route::get('signal-packages', [SignalPackages::class, 'landingPage'])
        ->name('signalPackages');//landing page
    Route::get('signal-packages/all', [SignalPackages::class, 'getPackages'])
        ->name('getSignalPackages');//fetch all packages
    Route::post('signal-packages/create', [SignalPackages::class, 'addPackage'])
        ->name('addPackage');//add package
    Route::post('signal-packages/add-features', [SignalPackages::class, 'addPackageFeature'])
        ->name('addPackageFeatures');//add package features
    Route::post('signal-packages/edit', [SignalPackages::class, 'editPackage'])
        ->name('editPackage');//edit package
    Route::post('signal-packages/edit-features', [SignalPackages::class, 'editPackageFeatures'])
        ->name('editPackageFeatures');//edit package features
    /*=======================SIGNALS CONTROLLER ROUTES==============*/
    Route::get('signals', [Signals::class, 'landingPage'])->name('signals');//landing page
    Route::get('signals/all/{index?}', [Signals::class, 'getSignals'])->name('getSignals');//fetch all signals
    Route::get('signals/create', [Signals::class, 'createSignalLandingPage'])->name('createSignalLandingPage');//create signal
    Route::post('signals/create', [Signals::class, 'addSignal'])->name('addSignal'); //add signal
    Route::get('signals/{id}', [Signals::class, 'getSignalById'])->name('getSignalDetail');//fetch single signal
    Route::get('signals/inputs/{signalId}', [Signals::class, 'getSignalInputs'])
        ->name('signalInputs');//fetch signal inputs
    /*=======================STAFF CONTROLLER ROUTES==============*/
    Route::get('staff', [Staff::class, 'landingPage'])->name('staff');//landing page
    Route::get('/staff/create', [Staff::class, 'addStaffLandingPage'])->name('addStaffLandingPage');//create staff resource
    Route::post('/api/staff/add', [Staff::class, 'addStaff'])->name('addStaff');//create staff resource
    Route::get('staff/all/{index?}', [Staff::class, 'getAllStaff'])->name('getStaff');//fetch all staff
    Route::get('staff/{id}', [Staff::class, 'getStaffDetail'])->name('getStaffDetail');//fetch single staff
    /*=======================SWAPS CONTROLLER ROUTES==============*/
    Route::get('swaps', [Swaps::class, 'landingPage'])->name('swaps');//landing page
    Route::get('swaps/all/{index?}', [Swaps::class, 'getSwaps'])->name('getSwaps');//fetch all swapping
    Route::get('swaps/{id}', [Swaps::class, 'getSwapId'])->name('getSwapId');//fetch a single swapping
    /*=======================SYSTEM ACCOUNTS CONTROLLER ROUTES==============*/
    Route::get('system-accounts', [SystemAccounts::class, 'landingPage'])->name('accounts');//landing page
    Route::get('system-accounts/all', [SystemAccounts::class, 'getAccounts'])
        ->name('fetchAccounts');//fetch all accounts
    Route::get('system-accounts/withdrawals', [SystemAccounts::class, 'withdrawalslandingPage'])
        ->name('systemWithdrawals');//system account withdrawal
    Route::get('system-accounts/withdrawals/create/{id}', [SystemAccounts::class, 'withdrawalslandingPage'])
        ->name('systemWithdrawals');//system account withdrawal
    Route::get('system-accounts/allWithdrawals', [SystemAccounts::class, 'systemWithdrawals'])
        ->name('systemWithdrawalList');//fetch all withdrawal from crypto reserve
    Route::post('system-accounts/withdraw', [SystemAccounts::class, 'doWithdrawal'])
        ->name('withdrawalSystemAccount');//withdraw from system account
    Route::post('system-accounts/approveWithdrawal', [SystemAccounts::class, 'approveWithdrawal'])
        ->name('approveSystemWithdrawal');//approve withdrawal from system account
    Route::get('system-accounts/{id}', [SystemAccounts::class, 'accountDetails'])
        ->name('fetchAccountId');//fetch account by id
    /*=======================SYSTEM FIAT ACCOUNTS CONTROLLER ROUTES==============*/
    Route::get('system-fiat-accounts', [SystemFiatAccounts::class, 'landingPage'])
        ->name('systemFiatAccount');//landing page
    Route::get('system-fiat-accounts/create', [SystemFiatAccounts::class, 'createLandingPage'])
        ->name('createSystemFiatAccount');//landing page
    Route::get('system-fiat-account/all', [SystemFiatAccounts::class, 'getAccounts'])
        ->name('getSystemFIatAccount');//fetch all fiat accounts
    Route::post('system-fiat-accounts/add', [SystemFiatAccounts::class, 'addAccount'])
        ->name('addFiatAccount');//create a system fiat account resource
    Route::post('system-fiat-accounts/delete', [SystemFiatAccounts::class, 'delete'])
        ->name('deleteFiatAccount');//create a system fiat account resource
    /*=======================USER BANKS CONTROLLER ROUTES==============*/
    Route::get('users-banks', [UserBanks::class, 'landingPage'])->name('userBanks');//landing page
    Route::get('user-banks/all/{index?}', [UserBanks::class, 'getBanks'])
        ->name('getUsersBanks');//fetch all user banks
    Route::get('user-banks/user/{user}/{index?}', [UserBanks::class, 'getBanksByUser'])
        ->name('getUserBankByUser');//fetch a user banks
    /*=======================USERS CONTROLLER ROUTES==============*/
    Route::get('users', [Users::class, 'landingPage'])
        ->name('users');//landing page
    Route::get('users/all/{index?}', [Users::class, 'getUsers'])
        ->name('getUsers');//get all users
    Route::get('users/{userId}', [Users::class, 'userDetailsLandingPage'])
        ->name('userDetailsLandingPage');//fetch a single user's details
    Route::get('/api/users/{userId}', [Users::class, 'getUserDetails'])
        ->name('getUserDetails');//fetch a single user's details
    Route::get('/api/users/withdrawals/{user}', [Users::class, 'userCryptoWithdrawalsLandingPage'])
        ->name('userCryptoWithdrawalsLandingPage');
    Route::get('/api/users/withdrawals/{user}/{index?}', [Users::class, 'getUserWithdrawals'])
        ->name('getUserCryptoWithdrawals'); //fetch all the user withdrawals
    Route::get('users/deposits/{user}', [Users::class, 'userDepositsLandingPage'])
        ->name('userDepositsLandingPage');//fetch all the user deposits
    Route::get('/api/users/deposits/{user}/{index?}', [Users::class, 'getUserDeposits'])
        ->name('getUserCryptoDeposits');//fetch all the user deposits
    Route::get('users/swaps/{user}', [Users::class, 'userSwapsLandingPage'])
        ->name('userSwapsLandingPage');//fetch all the user swappings
    Route::get('/api/users/swaps/{user}/{index?}', [Users::class, 'getUserSwaps'])
        ->name('getUserSwaps');//fetch all the user swappings
    Route::get('users/purchases/{user}}', [Users::class, 'userPurchasesLandingPage'])
        ->name('userPurchasesLandingPage');//fetch all the user purchases
    Route::get('/api/users/purchases/{user}/{index?}', [Users::class, 'getUserPurchases'])
        ->name('getUserPurchases');//fetch all the user purchases
    Route::get('users/sales/{user}', [Users::class, 'userSalesLandingPage'])
        ->name('userSalesLandingPage');//fetch all the user sales
    Route::get('/api/users/sales/{user}/{index?}', [Users::class, 'getUserSales'])
        ->name('getUserSales');//fetch all the user sales
    Route::get('users/signal-payments/{user}', [Users::class, 'userSignalPaymentsLandingPage'])
        ->name('userSignalPaymentsLandingPage');//fetch all the user signal payments
    Route::get('/api/users/signal-payments/{user}/{index?}', [Users::class, 'getUserSignalPayments'])
        ->name('getUserPayments');//fetch all the user signal payments
    Route::get('users/fiat-withdrawals/{user}', [Users::class, 'userFiatWithdrawalsLandingPage'])
        ->name('userFiatWithdrawalsLandingPage');//fetch all the user fiat withdrawals
    Route::get('/api/users/fiat-withdrawals/{user}/{index?}', [Users::class, 'getUserFiatWithdrawals'])
        ->name('getUserFiatWithdrawals');//fetch all the user fiat withdrawals
    Route::get('users/banks/{user}', [Users::class, 'userBanksLandingPage'])
        ->name('userBanksLandingPage');//fetch all the user banks
    Route::get('/api/users/banks/{user}/{index?}', [Users::class, 'getUserBanks'])
        ->name('getUserBanks');//fetch all the user banks
    Route::get('users/referrals/{user}', [Users::class, 'userReferralsLandingPage'])
        ->name('userReferralsLandingPage');//fetch all the user referrals
    Route::get('/api/users/referrals/{user}/{index?}', [Users::class, 'getUserReferrals'])
        ->name('getUserReferrals');//fetch all the user referrals
    Route::get('users/documents/{user}', [Users::class, 'userVerificationLandingPage'])
        ->name('userVerificationLandingPage');//fetch all the user verification documents
    Route::get('/api/users/documents/{user}', [Users::class, 'userVerificationDocument'])
        ->name('userVerificationDocuments');//fetch all the user verification documents
    Route::post('user/top-up-balance', [Users::class, 'topUpUserBalance'])
        ->name('topUpUserBalance');//top up user balance
    Route::post('user/subtract-balance', [Users::class, 'subtractUserBalance'])
        ->name('subtractUserBalance');//subtract user balance
    Route::post('user/update-settings', [Users::class, 'updateUserSettings'])
        ->name('updateUserSettings');//update user
    Route::post('user/approve-verification', [Users::class, 'verifyUser'])
        ->name('approveVerification');//approve user verification documents
    Route::post('user/reject-verification', [Users::class, 'rejectUserVerification'])
        ->name('rejectVerification');//reject user verification documents
    Route::get('user/activate-notification/{user}', [Users::class, 'activateNotification'])
        ->name('activateNotification');//activate user notification
    Route::get('user/deactivate-notification/{user}', [Users::class, 'deactivateNotification'])
        ->name('deactivateNotification');//deactivate user notification

    /*=======================USER WALLETS CONTROLLER ROUTES==============*/
    Route::get('users-wallets', [UserWallets::class, 'landingPage'])->name('userWallets');//landing page
    Route::get('user-wallets/all/{index?}', [UserWallets::class, 'getWallets'])
        ->name('getUsersWallets');//fetch all wallets
    Route::get('user-wallets/user/all/{user}/{index?}', [UserWallets::class, 'getUserWallets'])
        ->name('getUserWallets');//fetch all wallets associated to a user
    Route::get('user-wallets/{id}', [UserWallets::class, 'walletDetails'])
        ->name('getWalletId');//get a single wallet
    Route::get('user-wallets/wallet/deposits/{wallet}/{index?}', [UserWallets::class, 'walletDeposits'])
        ->name('getWalletDeposit');//get a wallet deposits
    Route::get('user-wallets/wallet/withdrawals/{wallet}/{index?}', [UserWallets::class, 'walletWithdrawals'])
        ->name('getWalletWithdrawals');//get a wallet withdrawals

    Route::post('get-gas-fee', [UserWallets::class, 'calculateGasFees'])
        ->name('getGasFee');//estimate fee for transferring from account deprecated but left for lrgacy purpose
    Route::post('user-wallets/topUp', [UserWallets::class, 'addFunds'])
        ->name('topUpWallet');//top up wallet
    Route::post('user-wallets/subtractFunds', [UserWallets::class, 'subtractFunds'])
        ->name('subtractWallet');//subtract from wallet
    /*=======================WITHDRAWALS CONTROLLER ROUTES==============*/
    Route::get('withdrawals', [Withdrawals::class, 'landingPage'])->name('withdrawals');//landing page
    Route::get('withdrawals/all/{index?}', [Withdrawals::class, 'getWithdrawals'])
        ->name('getWithdrawals');//fetch all withdrawals
    Route::get('withdrawals/user/all/{user}/{index?}', [Withdrawals::class, 'getWithdrawalByUser'])
        ->name('getWithdrawalsByUser');//fetch all withdrawals from a user
    Route::post('withdrawals/approve', [Withdrawals::class, 'approveWithdrawal'])
        ->name('approveCryptoWithdrawal');//approve a crypto withdrawal


    //logout admin
    Route::get('logout',[\App\Http\Controllers\Admin\Auth\Login::class,'logout'])->name('logout');
