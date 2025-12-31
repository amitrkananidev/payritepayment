<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\EkoController;
use App\Http\Controllers\CyrusController;
use App\Http\Controllers\BillavenueController;
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

Route::prefix('v1')->group(function () {
      Route::post('login', [ApiController::class, 'token']);
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
          Route::get('bank', [BillavenueController::class, 'DmtBankList']);
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
      
      Route::prefix('callback')->group(function () {
          Route::post('phonepe', [ApiController::class, 'callbackPhonePe']);
          Route::get('cyrus', [CyrusController::class, 'callback']);
          
          Route::post('bulkpe', [ApiController::class, 'callbackBulkpe']);
          Route::post('bulkpe-credit', [ApiController::class, 'callbackBulkpeCredit']);
      });
});