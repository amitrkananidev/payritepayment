<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\EkoController;
use App\Http\Controllers\CyrusController;
use App\Http\Controllers\BillavenueController;
use App\Http\Controllers\CredoPayController;
use App\Http\Controllers\ApiDistributorController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('myuse', [ApiController::class, 'myUseFunction']);
Route::get('rest-balance', [ApiController::class, 'balanceReset']);
Route::get('bbpsCategoriesInsert', [ApiController::class, 'bbpsCategoriesInsert']);
Route::get('bbpsBillerInsert', [ApiController::class, 'bbpsBillerInsert']);
Route::get('bbpsBillerIDInsert', [ApiController::class, 'bbpsBillerIDInsert']);

if (config('app.API_ENABLED', true)) {

Route::prefix('v1')->group(function () {
      Route::post('login', [ApiController::class, 'token']);
      Route::post('login-otp-verify', [ApiController::class, 'loginOtpVerify']);
      Route::post('login-otp-resent', [ApiController::class, 'otpResend']);
      Route::post('get-token', [ApiController::class, 'getToken']);
      Route::post('get-balance', [ApiController::class, 'getBalance']);
      
      Route::get('wallet-test', [ApiController::class, 'walletTest']);
      Route::get('bank-list', [ApiController::class, 'getBanks']);
      
      Route::get('fcm', [ApiController::class, 'fcmtesting']);
      
      Route::post('my-statment', [ApiController::class, 'myStatment']);
      
      Route::prefix('profile')->group(function () {
          Route::post('get', [ApiController::class, 'getProfile']);
          Route::post('update', [ApiController::class, 'updateProfile']);
          Route::post('update-profile-image', [ApiController::class, 'updateProfileImage']);
      });
      
      Route::prefix('kyc')->group(function () {
          Route::post('get', [ApiController::class, 'getKyc']);
          Route::post('update', [ApiController::class, 'updateKyc']);
      });
      
      Route::prefix('shop')->group(function () {
          Route::post('get', [ApiController::class, 'getShop']);
          Route::post('update', [ApiController::class, 'updateStore']);
      });
      
      Route::prefix('fund_request')->group(function () {
          Route::post('get-bank', [ApiController::class, 'getFundBanks']);
          Route::post('create', [ApiController::class, 'createMoneyRequest']);
          Route::post('list', [ApiController::class, 'listMoneyRequest']);
          Route::post('phonepe', [ApiController::class, 'phonepePGData']);
          Route::post('pg-list', [ApiController::class, 'pgListMoneyRequest']);
          Route::post('qr-list', [ApiController::class, 'qrPaymentReport']);
      });
      
      Route::prefix('qr-payment')->group(function () {
          Route::post('send-otp', [ApiController::class, 'qrOtpSend']);
          Route::post('verify-otp', [ApiController::class, 'qrOtpVerify']);
          Route::post('get-transaction-status', [ApiController::class, 'qrTransactionStatus']);
      });
      
      Route::prefix('dmt')->group(function () {
          Route::post('create-customer', [ApiController::class, 'createCustomer']);
          Route::post('otp-customer-verify', [ApiController::class, 'otpCustomerVerify']);
          Route::post('customer-login', [ApiController::class, 'customerLogin']);
          Route::post('get-beneficiaries', [ApiController::class, 'getBeneficiaries']);
          Route::post('add-beneficiary', [ApiController::class, 'addBeneficiary']);
          Route::post('verify-beneficiary', [ApiController::class, 'accountVerification']);
          Route::post('delete-beneficiary', [ApiController::class, 'deleteBeneficiary']);
          Route::post('do-transactions', [ApiController::class, 'doTransactions']);
          Route::post('report', [ApiController::class, 'dmtReport']);
          Route::post('get-transaction-status', [ApiController::class, 'dmtTransactionStatus']);
          
          Route::post('ekoOnboard', [EkoController::class, 'ekoOnboard']);
          Route::post('aepsActivateService', [EkoController::class, 'aepsActivateService']);
          Route::post('aepsUserServiceEnquiry', [EkoController::class, 'aepsUserServiceEnquiry']);
          Route::post('ekoUserServiceList', [EkoController::class, 'ekoUserServiceList']);
          
          Route::post('verify-pan', [ApiController::class, 'panVerification']);
      });
      
      Route::prefix('eko-dmt')->group(function () {
          Route::post('customer-login', [ApiController::class, 'ekoDmtCustomerLogin']);
          Route::post('get-beneficiaries', [ApiController::class, 'ekoDmtGetBenf']);
          Route::post('add-beneficiaries', [ApiController::class, 'ekoDmtAddBenf']);
          Route::post('do-transactions', [ApiController::class, 'ekoDmtDoTransactions']);
          Route::post('refund', [ApiController::class, 'ekoDmtRefund']);
          Route::post('callback_url', [EkoController::class, 'dmtCallbackUrl']);
      });
      
      Route::prefix('bill-dmt')->group(function () {
          Route::post('customer-login', [ApiController::class, 'billDmtCustomerLogin']);
          Route::post('create-customer', [ApiController::class, 'billDmtCreateCustomer']);
          Route::post('otp-customer-verify', [ApiController::class, 'billDmtCustomerVerify']);
          Route::post('get-beneficiaries', [ApiController::class, 'billDmtGetBenf']);
          Route::post('add-beneficiary', [ApiController::class, 'billDmtAddBenf']);
          Route::post('delete-beneficiary', [ApiController::class, 'billDmtDeleteBenf']);
          Route::post('do-transactions', [ApiController::class, 'billDmtDoTransactions']);
          Route::post('do-transactions-web', [ApiController::class, 'billDmtDoTransactionsWeb']);
          Route::post('do-transactions-otp-send', [ApiController::class, 'billDmtDoTransactionsOtpSend']);
          Route::post('do-transactions-otp-verify', [ApiController::class, 'billDmtDoTransactionsOtpVerify']);
          Route::get('bank', [BillavenueController::class, 'DmtBankList']);
          Route::get('check-status', [ApiController::class, 'billDmtCheckStatus']);
          Route::post('refund-request', [ApiController::class, 'billDmtRefundRequest']);
          Route::post('refund-otp', [ApiController::class, 'billDmtRefundOtpVerify']);
          Route::get('refund-by-me', [ApiController::class, 'billDmtRefundRequestManul']);
      });
      
      Route::prefix('acemoney')->group(function () {
          Route::post('customer-login', [ApiController::class, 'acemoneyCustomerLogin']);
          Route::post('ekyc-customer', [ApiController::class, 'acemoneyKycCustomer']);
          Route::post('create-customer', [ApiController::class, 'acemoneyCreateCustomer']);
          Route::post('create-customer-otp', [ApiController::class, 'acemoneyCreateCustomerOtp']);
          Route::post('get-beneficiaries', [ApiController::class, 'aceDmtGetBenf']);
          Route::post('add-beneficiary', [ApiController::class, 'aceDmtAddBenf']);
          Route::post('do-transactions', [ApiController::class, 'aceDmtDoTransactions']);
          Route::post('do-transactions-otp-send', [ApiController::class, 'aceDmtDoTransactionsOtpSend']);
          Route::post('do-transactions-otp-verify', [ApiController::class, 'aceDmtDoTransactionsOtpVerify']);
          Route::post('receipt', [ApiController::class, 'aceDmtReceipt']);
      });
      
      Route::prefix('digikhata')->group(function () {
          Route::post('customer-login', [ApiController::class, 'digikhataCustomerLogin']);
          Route::post('customer-otp-verify', [ApiController::class, 'digikhataCustomerOtpVerify']);
          Route::post('aadhar-otp', [ApiController::class, 'digikhataAadharOtp']);
          Route::post('aadhar-otp-resend', [ApiController::class, 'digikhataAadharOtpResend']);
          Route::post('aadhar-otp-verify', [ApiController::class, 'digikhataAadharOtpVerify']);
          Route::post('pancard-kyc', [ApiController::class, 'digikhataPancardKyc']);
          Route::post('get-beneficiaries', [ApiController::class, 'digikhatabeneficiaryList']);
          Route::post('add-beneficiary', [ApiController::class, 'digikhataAddbeneficiary']);
          Route::post('delete-beneficiary', [ApiController::class, 'digikhataDeletebeneficiary']);
          Route::post('delete-beneficiary-otp', [ApiController::class, 'digikhataDeletebeneficiaryOtp']);
          Route::post('do-transactions', [ApiController::class, 'digikhataDmtDoTransactions']);
          Route::post('do-transactions-otp-verify', [ApiController::class, 'digiDmtDoTransactionsOtpVerify']);
          
          Route::post('refund-request', [ApiController::class, 'digiDmtRefundRequest']);
          Route::post('refund-otp', [ApiController::class, 'digiDmtRefundOtpVerify']);
      });
      
      Route::prefix('bbps')->group(function () {
          Route::post('fetch', [ApiController::class, 'bbpsFetchBill']);
          Route::get('pay', [ApiController::class, 'bbpsPayBill']);
      });
      
      Route::prefix('upi')->group(function () {
          Route::post('do-transactions', [ApiController::class, 'doTransactionsUpi']);
          Route::post('report', [ApiController::class, 'upiReport']);
      });
      
      Route::prefix('aeps')->group(function () {
          Route::post('aeps_keys_data', [EkoController::class, 'aepsKeysData']);
          Route::post('aeps_callback_url', [EkoController::class, 'aepsCallbackUrl']);
          Route::post('report', [ApiController::class, 'aepsReport']);
          
          Route::post('get-state-cyrus', [ApiController::class, 'getStateOfCyrusState']);
          Route::post('get-bank-cyrus', [ApiController::class, 'getBankOfCyrus']);
          Route::post('registration-cyrus-aeps', [ApiController::class, 'registrationCyrusAeps']);
          Route::post('otp-verify-cyrus-aeps', [ApiController::class, 'otpVerifyCyrusAeps']);
          Route::post('get-cyrus-aeps', [ApiController::class, 'getCyrusAeps']);
          Route::post('daily-kyc-aeps', [ApiController::class, 'dailyKycCyrusAeps']);
          
          Route::get('ekyc-otp', [EkoController::class, 'aepsEkycOtp']);
          Route::get('ekyc-otp-verify', [EkoController::class, 'aepsEkycOtpVerify']);
      });
      
      Route::prefix('cp-aeps')->group(function () {
          Route::post('merchant-onboarding', [ApiController::class, 'credoAepsMerchantOnboarding']);
          Route::get('terminal-onboarding', [ApiController::class, 'credoAepsTerminalOnboarding']);
          Route::get('merchant-updating', [ApiController::class, 'credoAepsMerchantUpdate']);
          Route::get('terminal-updating', [ApiController::class, 'credoAepsTerminalUpdate']);
          Route::get('terminal-activation', [ApiController::class, 'credoAepsTerminalActivation']);
          Route::get('terminal-deactivation', [ApiController::class, 'credoAepsTerminalDeActivation']);
          
          Route::post('check-fa', [ApiController::class, 'credoAepsCheckFa']);
          Route::post('fa', [ApiController::class, 'credoAepsFa']);
          Route::post('banks', [ApiController::class, 'credoAepsBanks']);
          Route::post('do-transaction', [ApiController::class, 'credoAepsDoTransactions']);
      });
      
      Route::prefix('upi-collection')->group(function () {
          Route::get('create', [ApiController::class, 'bulkpeUPIcreation']);
      });
      
      Route::prefix('bulkpe')->group(function () {
          Route::get('upload-sender', [ApiController::class, 'bulkpeUploadSender']);
          Route::post('create-sender', [ApiController::class, 'bulkpeCreateSender']);
          Route::post('get-sender', [ApiController::class, 'bulkpegetSender']);
          Route::post('get-beneficiaries', [ApiController::class, 'bulkpegetBenf']);
          Route::post('add-beneficiary', [ApiController::class, 'bulkpeAddBenf']);
          Route::post('do-transactions', [ApiController::class, 'bulkpeDoTransactions']);
          Route::post('report', [ApiController::class, 'ccpayoutReport']);
      });
      
      Route::prefix('rkwallet')->group(function () {
          Route::get('op', [ApiController::class, 'rechargeOP']);
          Route::post('recharge', [ApiController::class, 'rkWalletRecharge']);
          Route::post('report', [ApiController::class, 'rechargeReport']);
      });
      
      Route::prefix('mplan')->group(function () {
          Route::get('recharge', [ApiController::class, 'mplanRecharge']);
          Route::get('dth', [ApiController::class, 'mplanDth']);
          Route::get('recharge-mobile', [ApiController::class, 'mplanRechargeMobile']);
          Route::get('recharge-dth', [ApiController::class, 'mplanRechargeDth']);
          Route::get('recharge-dth-info', [ApiController::class, 'mplanRechargeDthInfo']);
      });
      
      Route::prefix('callback')->group(function () {
          Route::post('phonepe', [ApiController::class, 'callbackPhonePe']);
          Route::post('airpay', [ApiController::class, 'callbackAirpay']);
          Route::get('cyrus', [CyrusController::class, 'callback']);
          Route::post('safexpay', [ApiController::class, 'callbackSafexpay']);
          Route::post('bulkpe', [ApiController::class, 'callbackBulkpe']);
          Route::post('bulkpe-credit', [ApiController::class, 'callbackBulkpeCredit']);
          Route::post('bulkpe-cc', [ApiController::class, 'callbackBulkpeCC']);
          Route::get('IPayDigital', [ApiController::class, 'callbackIPayDigital']);
          Route::prefix('credopay')->group(function () {
              Route::post('onboarding', [ApiController::class, 'postCredopayOnboardingCallback']);
              Route::post('terminal-onboarding', [ApiController::class, 'postCredopayTerminalOnboardingCallback']);
              Route::post('terminalstatus', [ApiController::class, 'postCredopayTerminalOnboardingCallback']);
              Route::post('transaction', [ApiController::class, 'postCredopayTransactionsCallback']);
              //transactions
          });
          Route::prefix('eko')->group(function () {
              Route::post('cms', [ApiController::class, 'callbackEkoCms']);
              Route::post('payout', [ApiController::class, 'callbackEkoPayout']);
          });
          Route::post('rkwallet', [ApiController::class, 'callbackRkwallet']);
          Route::post('acemoney', [ApiController::class, 'callbackAcemoney']);
          
      });
      
      Route::prefix('cron')->group(function () {
          Route::get('dmt', [ApiController::class, 'calculateDMT']);
          Route::get('dmt-dist', [ApiController::class, 'calculateDMTDist']);
          Route::get('upi', [ApiController::class, 'calculateUPI']);
        //   Route::get('upi-dist', [ApiController::class, 'calculateUPIDist']);
          Route::get('aeps', [ApiController::class, 'calculateAEPS']);
          Route::get('dmt-status', [ApiController::class, 'dmtStatusTxnFail']);
          Route::get('dmt-3-status', [ApiController::class, 'dmt3StatusTxnFail']);
          
          Route::get('test', [ApiController::class, 'testCron']);
      });
      
      Route::prefix('app')->group(function () {
          Route::get('version', [ApiController::class, 'appVersion']);
      });
    
    
    Route::prefix('dist')->group(function () {
        Route::post('login', [ApiController::class, 'distLogin']);
        Route::post('static', [ApiDistributorController::class, 'getStatics']);
        Route::post('get-retailers', [ApiDistributorController::class, 'getRetailer']);
        Route::post('retailer-statment', [ApiDistributorController::class, 'retailerStatment']);
        Route::post('business-summary', [ApiDistributorController::class, 'businessSummary']);
        Route::post('retailer-od', [ApiDistributorController::class, 'fundOD']);
        Route::post('my-statment', [ApiDistributorController::class, 'myStatment']);
        Route::post('retailer-business-summary', [ApiDistributorController::class, 'retailerBusinessSummary']);
    });
});
}else{
    Route::any('{any}', function () {
        return response()->json(['success' => false, 'message' => 'Unauthorized access']);
    })->where('any', '.*');
}