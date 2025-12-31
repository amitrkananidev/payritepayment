<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\RetailerController;
use App\Http\Controllers\PhonePeController;
use App\Http\Controllers\AirPayController;

use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\icons\Boxicons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\tables\Basic as TablesBasic;

// authentication
Route::get('/', function () {
    return view('auth.login');
});
// Route::get('/auth/login', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::post('/auth/login', [LoginBasic::class, 'login'])->name('auth-login-post');
Route::get('logout', [LoginBasic::class, 'logout'])->name('logout');

Route::get('/auth/register', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::get('/auth/forgot-password', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');
Route::get('404', function () {
    return view('404');
});

Route::get('profile', function () {
    return view('new_pages.profile2');
});

Auth::routes();
Route::get('/', function () {
    if(Auth::check()) { 
        
        if(Auth::user()->user_type == 1){
            return redirect('/dashboard');
        }elseif(Auth::user()->user_type == 3){
            return redirect('/distributor/dashboard');
        }elseif(Auth::user()->user_type == 2){
            return redirect('/retailer/dashboard');
        }else{
            return redirect('logout');
        }
        
    } 
    else {return view('auth.login');}
})->name('main-page');

// Main Page Route


Route::get('change-password', function () {
    return view('new_pages.profile');
})->name('change_password');

Route::get('aeps-device', function () {
    return view('new_pages.retailer.services.aeps.cyrus');
})->name('aeps_device');

Route::post('change-password', [AdminController::class, 'postChangePassword'])->name('post_change_password');

Route::get('get-city', [AdminController::class, 'getCity'])->name('get_state_city');
Route::get('get-treansaction-id', [DistributorController::class, 'getTreansactionId'])->name('get_treansaction_id');

Route::post('send-password', [AdminController::class, 'postSendPassword'])->name('post_send_password');

Route::group(['middleware' => 'admin'], function () {
    // Define routes within the group
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard_admin');
    
    Route::get('/test-pdf', [AdminController::class, 'createPdf'])->name('create_Pdf');
    Route::prefix('retailer')->group(function () {
        Route::get('create', [AdminController::class, 'createRetailer'])->name('create_retailer');
        Route::post('create', [AdminController::class, 'postCreateRetailer'])->name('post_create_retailer');
        Route::get('view', [AdminController::class, 'viewRetailer'])->name('view_retailer');
        Route::get('view-data', [AdminController::class, 'viewRetailerData'])->name('view_retailer_data');
        Route::get('view-data-export', [AdminController::class, 'viewRetailerDataExport'])->name('view_retailer_data_export'); // export
        Route::get('services', [AdminController::class, 'servicesRetailer'])->name('services_retailer');
        Route::get('services-data', [AdminController::class, 'servicesRetailerData'])->name('services_retailer_data');
        Route::post('service-active', [AdminController::class, 'serviceActive'])->name('service_active');
        Route::post('od-transfer', [AdminController::class, 'fundOD'])->name('fund_od');
        
    });
    Route::prefix('distributor')->group(function () {
        Route::get('create', [AdminController::class, 'createDistributor'])->name('create_distributor');
        Route::post('create', [AdminController::class, 'postCreateDistributor'])->name('post_create_distributor');
        Route::post('otp', [AdminController::class, 'postCreateDistributorOtp'])->name('post_create_distributor_otp');
        Route::get('view', [AdminController::class, 'viewDistributor'])->name('view_distributor');
        Route::get('view-data', [AdminController::class, 'viewDistributorData'])->name('view_distributor_data');
        Route::post('create-retailer', [AdminController::class, 'distToRetailer'])->name('create_dist_to_retailer');
        
    });
    Route::prefix('fund-request')->group(function () {
        Route::get('fund-request', [AdminController::class, 'fundRequest'])->name('fund_request');
        Route::get('fund-request-data', [AdminController::class, 'fundRequestData'])->name('fund_request_data');
        Route::post('fund-request-approve', [AdminController::class, 'fundRequestApprove'])->name('fund_request_approve');
        Route::post('fund-request-reject', [AdminController::class, 'fundRequestReject'])->name('fund_request_reject');
        Route::get('add-bank', [DistributorController::class, 'addFundBank'])->name('add_bank_fund');
        Route::post('add-bank', [DistributorController::class, 'postAddFundBank'])->name('post_add_bank_fund');
        Route::post('bank-rec', [AdminController::class, 'postBankRec'])->name('post_bank_rec');
    });
    
    Route::prefix('report')->group(function () {
        Route::get('user-statment/{id}', [AdminController::class, 'userStatment'])->name('usser_statment');
        Route::get('user-statment-data/{id}', [AdminController::class, 'usserStatmentData'])->name('usser_statment_data');
        Route::get('user-statment-data-export/{id}', [AdminController::class, 'usserStatmentDataExport'])->name('usser_statment_data_export'); //export
        Route::get('dmt', [AdminController::class, 'dmtReport'])->name('dmt_report');
        Route::get('dmt-data', [AdminController::class, 'dmtReportData'])->name('dmt_report_data');
        Route::get('dmt-data-export', [AdminController::class, 'dmtReportDataExport'])->name('dmt_report_data_export'); //export
        Route::post('dmt-txn-failed', [AdminController::class, 'dmtTxnFailed'])->name('dmt_txn_failed');
        Route::post('dmt-txn-success', [AdminController::class, 'dmtTxnSuccess'])->name('dmt_txn_success');
        Route::get('gst', [AdminController::class, 'getGst'])->name('get_gst');
        Route::get('gst-data', [AdminController::class, 'gstData'])->name('gst_data');
        Route::get('tds', [AdminController::class, 'getTds'])->name('get_tds');
        Route::get('tds-data', [AdminController::class, 'tdsData'])->name('tds_data');
        Route::get('tds-data-export', [AdminController::class, 'tdsDataExport'])->name('tds_data_export'); //export
        Route::get('fund-od-report', [AdminController::class, 'fundODReport'])->name('fund_od_report');
        Route::get('fund-od-report-data', [AdminController::class, 'fundODReportData'])->name('fund_od_report_data');
        Route::get('fund-request', [AdminController::class, 'fundRequestReport'])->name('fund_request_report');
        Route::get('fund-request-data', [AdminController::class, 'fundRequestReportData'])->name('fund_request_report_data');
        Route::get('aeps', [AdminController::class, 'aepsReport'])->name('aeps_report');
        Route::get('aeps-data', [AdminController::class, 'aepsReportData'])->name('aeps_report_data');
        
        Route::get('online-fund', [AdminController::class, 'onlineFund'])->name('online_fund');
        Route::get('online-fund-data', [AdminController::class, 'onlineFundData'])->name('online_fund_data');
        
        Route::get('qr-fund', [AdminController::class, 'qrFund'])->name('qr_fund');
        Route::get('qr-fund-data', [AdminController::class, 'qrFundData'])->name('qr_fund_data');
        
        Route::get('commission-fee', [AdminController::class, 'commissionFee'])->name('commission_fee');
        Route::get('commission-fee-data', [AdminController::class, 'commissionFeeData'])->name('commission_fee_data');
        Route::get('account-verify', [AdminController::class, 'accountVeirfy'])->name('account_verify');
        Route::get('account-verify-data', [AdminController::class, 'accountVeirfyData'])->name('account_verify_data');
        
        Route::get('users-wallet', [AdminController::class, 'allUsersWallet'])->name('all_users_wallet');
        Route::get('users-wallet-Data', [AdminController::class, 'allUsersWalletData'])->name('all_users_wallet_data');
        
        Route::get('buisness-report', [AdminController::class, 'businessReport'])->name('business_report');
        Route::post('buisness-report-export', [AdminController::class, 'businessReportData'])->name('business_report_export');
        
        Route::get('refund-otp/{id}', [AdminController::class, 'billDmtRefundRequest'])->name('bill_dmt_refund_otp');
        Route::post('refund-otp-verify', [AdminController::class, 'billDmtRefundOtpVeirfy'])->name('bill_post_dmt_refund_otp_verify');
        
        Route::get('total-tds-data', [AdminController::class, 'gettotalTdsDataExport'])->name('total_tds_data');
        Route::post('total-tds-data-export', [AdminController::class, 'totalTdsDataExport'])->name('total_tds_data_export'); //export
        
        Route::get('consolidated-data', [AdminController::class, 'getExportConsolidatedAccountStatement'])->name('consolidated_data');
        Route::post('consolidated-data-export', [AdminController::class, 'exportConsolidatedAccountStatement'])->name('consolidated_data_export'); //export
        
        Route::get('recharge', [AdminController::class, 'rechargeReport'])->name('recharge_report');
        Route::get('recharge-data', [AdminController::class, 'rechargeReportData'])->name('recharge_report_data');
        
        Route::get('ccpayout', [AdminController::class, 'ccpayoutReport'])->name('ccpayout_report');
        Route::get('ccpayout-data', [AdminController::class, 'ccpayoutReportData'])->name('ccpayout_report_data');
        
    });
    
    Route::prefix('recharge')->group(function () {
        Route::get('create-op', [AdminController::class, 'getRechargeCreateOp'])->name('get_recharge_create_op');
        Route::post('create-op', [AdminController::class, 'postRechargeCreateOp'])->name('post_recharge_create_op');
        Route::get('create-slab', [AdminController::class, 'getRechargeSlab'])->name('get_recharge_create_slab');
        Route::post('create-slab', [AdminController::class, 'postRechargeSlab'])->name('post_recharge_create_slab');
        
        Route::get('slab-commission', [AdminController::class, 'getRechargeSlabCommission'])->name('get_recharge_slab_commission');
        Route::post('slab-commission', [AdminController::class, 'postRechargeSlabCommission'])->name('post_recharge_slab_commission');
    });
    
    Route::prefix('receipt')->group(function () {
        Route::get('dmt/{id}', [RetailerController::class, 'dmtReceipt'])->name('dmt_receipt');
        Route::get('bbps', [RetailerController::class, 'bbpsReceipt'])->name('bbps_receipt');
    });
    
});


Route::group(['middleware' => 'distributor'], function () {
    
    Route::get('/distributor/dashboard', [DistributorController::class, 'index'])->name('dashboard_distributor');
    Route::prefix('distributor-retailer')->group(function () {
        Route::get('create', [DistributorController::class, 'createRetailer'])->name('create_retailer_distributor');
        Route::post('create', [DistributorController::class, 'postCreateRetailer'])->name('post_create_retailer_distributor');
        Route::post('otp', [DistributorController::class, 'postCreateRetailerOtp'])->name('post_create_retailer_otp_distributor');
        Route::get('view', [DistributorController::class, 'viewRetailer'])->name('view_retailer_distributor');
        Route::get('view-data', [DistributorController::class, 'viewRetailerData'])->name('view_retailer_data_distributor');
        
        Route::post('get-User-Details', [DistributorController::class, 'getUserDetails'])->name('get_user_detail_distributor');
        Route::get('re-kyc', [DistributorController::class, 'rekycRetailer'])->name('rekyc_distributor');
        Route::post('re-kyc', [DistributorController::class, 'postRekycRetailer'])->name('post_rekyc_distributor');
        Route::post('kyc-document', [DistributorController::class, 'postKycDocument'])->name('post_kyc_document_distributor');
        Route::post('od-transfer', [DistributorController::class, 'fundOD'])->name('fund_od_distributor');
        
    });
    Route::prefix('distributor-fund-request')->group(function () {
        Route::get('create-fund-request', [DistributorController::class, 'createFundRequest'])->name('create_fund_request_distributor');
        Route::post('create-fund-request', [DistributorController::class, 'postCreateFundRequest'])->name('post_create_fund_request_distributor');
        
        Route::get('fund-request', [DistributorController::class, 'fundRequest'])->name('fund_request_distributor');
        Route::get('fund-request-data', [DistributorController::class, 'fundRequestData'])->name('fund_request_data_distributor');
        Route::post('fund-request-approve', [DistributorController::class, 'fundRequestApprove'])->name('fund_request_approve_distributor');
        Route::post('fund-request-reject', [DistributorController::class, 'fundRequestReject'])->name('fund_request_reject_distributor');
        Route::get('add-bank', [DistributorController::class, 'addFundBank'])->name('add_bank_fund_distributor');
        Route::post('add-bank', [DistributorController::class, 'postAddFundBank'])->name('post_add_bank_fund_distributor');
        Route::get('fund-od-report', [DistributorController::class, 'fundODReport'])->name('fund_od_report_distributor');
        Route::get('fund-od-report-data', [DistributorController::class, 'fundODReportData'])->name('fund_od_report_data_distributor');
    });
    
    Route::prefix('distributor-fund-load')->group(function () {
        Route::post('/phonepe/initiate', [PhonePeController::class, 'initiatePayment'])->name('phonepe_initiate_distributor');
        Route::get('/phonepe/callback', [PhonePeController::class, 'paymentCallback'])->name('phonepe_callback_distributor');
        
    });
    
    Route::prefix('distributor-report')->group(function () {
        Route::get('my-statment', [DistributorController::class, 'myStatment'])->name('my_statment_distributor');
        Route::get('my-statment-data', [DistributorController::class, 'myStatmenttData'])->name('my_statment_data_distributor');
        Route::get('dmt', [DistributorController::class, 'dmtReport'])->name('dmt_report_distributor');
        Route::get('dmt-data', [DistributorController::class, 'dmtReportData'])->name('dmt_report_data_distributor');
        Route::get('scannpay', [DistributorController::class, 'scanandpayReport'])->name('scannpay_report_distributor');
        Route::get('scannpay-data', [DistributorController::class, 'scanandpayReportData'])->name('scannpay_report_data_distributor');
        Route::get('retailer-fund-request', [DistributorController::class, 'retailerFundRequest'])->name('retailer_fund_request_distributor');
        Route::get('retailer-fund-request-data', [DistributorController::class, 'retailerFundRequestData'])->name('retailer_fund_request_data_distributor');
        Route::get('online-fund', [DistributorController::class, 'onlineFund'])->name('online_fund_distributor');
        Route::get('online-fund-data', [DistributorController::class, 'onlineFundData'])->name('online_fund_data_distributor');
    });
    
    
});

Route::group(['middleware' => 'retailer'], function () {
    Route::any('/airpay/response', [AirPayController::class, 'responseAirpay'])->name('response_airpay');
    
    Route::get('/retailer/dashboard', [RetailerController::class, 'index'])->name('dashboard_retailer');
    Route::prefix('retailer-fund-request')->group(function () {
        Route::get('create-fund-request', [RetailerController::class, 'createFundRequest'])->name('create_fund_request_retailer');
        Route::post('create-fund-request', [RetailerController::class, 'postCreateFundRequest'])->name('post_create_fund_request_retailer');
        
        Route::get('online-fund', [RetailerController::class, 'onlineFund'])->name('online_fund_retailer');
        Route::get('online-fund-data', [RetailerController::class, 'onlineFundData'])->name('online_fund_data_retailer');
        
        Route::get('qr-fund', [RetailerController::class, 'qrFund'])->name('qr_fund_retailer');
        Route::get('qr-fund-data', [RetailerController::class, 'qrFundData'])->name('qr_fund_data_retailer');
        
        Route::get('fund-request', [RetailerController::class, 'fundRequest'])->name('fund_request_retailer');
        Route::get('fund-request-data', [RetailerController::class, 'fundRequestData'])->name('fund_request_data_retailer');
    });
    
    Route::post('airpay/payment', [RetailerController::class, 'airpayPg'])->name('airpay_payment_retailer');
    
    Route::prefix('qr')->group(function () {
        Route::post('qr-otp-send', [RetailerController::class, 'qrOtpSend'])->name('qr_otp_send_retailer');
        Route::post('qr-otp-veirfy', [RetailerController::class, 'qrOtpVerify'])->name('qr_otp_verify_retailer');
    });
    
    Route::prefix('retailer-services')->group(function () {
        Route::get('aeps', [RetailerController::class, 'getAeps'])->name('aeps_retailer');
        Route::post('aeps-otp', [RetailerController::class, 'AepsOtp'])->name('aeps_otp_retailer');
        
        Route::get('aeps2', [RetailerController::class, 'cyrusAeps'])->name('aeps_2_retailer');
        Route::post('aeps2-registration', [RetailerController::class, 'cyrusAepsRegistration'])->name('aeps_registration_retailer');
        Route::post('aeps2-otp', [RetailerController::class, 'cyrusAepsOtpVerify'])->name('aeps_otp_verify_retailer');
        
        Route::get('dmt-login', [RetailerController::class, 'dmtLogin'])->name('dmt_login_retailer');
        Route::get('get-city-based-on-pin', [RetailerController::class, 'pincodeLocation'])->name('get_city_based_on_pin_retailer');
        Route::post('dmt-registration', [RetailerController::class, 'dmtRegistration'])->name('dmt_registration_retailer');
        Route::post('dmt-otp', [RetailerController::class, 'dmtOtp'])->name('dmt_otp_retailer');
        Route::post('dmt', [RetailerController::class, 'postDmtLogin'])->name('post_dmt_login_retailer');
        Route::post('dmt-benf-add', [RetailerController::class, 'postDmtBenfAdd'])->name('post_dmt_benf_add_retailer');
        Route::post('dmt-benf-delete', [RetailerController::class, 'postDmtBenfDelete'])->name('post_dmt_benf_delete_retailer');
        Route::post('dmt-benf-verify', [RetailerController::class, 'postDmtBenfVerify'])->name('post_dmt_benf_verify_retailer');
        Route::post('dmt-transaction', [RetailerController::class, 'postDmtTransaction'])->name('post_dmt_transaction_retailer');
        
        Route::get('upi-test', [RetailerController::class, 'upiTransferTest'])->name('upi_transfer_test_retailer');
        
        Route::get('upi', [RetailerController::class, 'upiTransfer'])->name('upi_transfer_retailer');
        Route::post('upi', [RetailerController::class, 'postUpiTransfer'])->name('post_upi_transfer_retailer');
        
        Route::get('recharge', [RetailerController::class, 'getRecharge'])->name('get_recharge_retailer');
        Route::get('recharge-dth', [RetailerController::class, 'getDthRecharge'])->name('get_dth_recharge_retailer');
        Route::post('recharge', [RetailerController::class, 'postRecharge'])->name('post_recharge_retailer')->middleware('auth', 'custom.throttle:1,0.5,user');;
        
        Route::get('payout', [RetailerController::class, 'getpayout'])->name('payout_retailer');
        // Route::get('payout', function () {
        //     return view('new_pages.retailer.services.payout.payout');
        // })->name('payout_retailer');
        
        Route::prefix('eko-dmt')->group(function () {
            Route::get('login', [RetailerController::class, 'ekoDmtLogin'])->name('eko_dmt_login_retailer');
            Route::post('login', [RetailerController::class, 'ekoPostDmtLogin'])->name('eko_post_dmt_login_retailer');
            Route::post('transaction', [RetailerController::class, 'ekoPostDmtTransaction'])->name('eko_post_dmt_transaction_retailer');
            Route::post('benf-add', [RetailerController::class, 'ekoPostDmtBenfAdd'])->name('eko_post_dmt_benf_add_retailer');
        });
        
        Route::prefix('cp-aeps')->group(function () {
            Route::get('login', [RetailerController::class, 'credopayAeps'])->name('credo_aeps_retailer');
            Route::post('registration', [RetailerController::class, 'credopayAepsRegistration'])->name('credo_aeps_registration_retailer');
            Route::post('2fa', [RetailerController::class, 'credopayAepsFa'])->name('credo_aeps_fa_retailer');
            Route::post('transaction', [RetailerController::class, 'credopayAepsTransaction'])->name('credo_aeps_transaction_retailer');
        });
        
        Route::prefix('bill-dmt')->group(function () {
            Route::get('login', [RetailerController::class, 'billDmtLogin'])->name('bill_dmt_login_retailer');
            Route::post('login', [RetailerController::class, 'billPostDmtLogin'])->name('bill_post_dmt_login_retailer');
            Route::post('create', [RetailerController::class, 'billPostDmtCreateCustomer'])->name('bill_post_dmt_create_customer_retailer');
            Route::post('create-artl', [RetailerController::class, 'billPostDmtCreateCustomerArtl'])->name('bill_post_dmt_create_customer_artl_retailer');//ARTL
            Route::post('verify', [RetailerController::class, 'billPostDmtCreateCustomerVerify'])->name('bill_post_dmt_create_customer_verify_retailer');
            Route::post('verify-artl', [RetailerController::class, 'billPostDmtCreateCustomerVerifyArtl'])->name('bill_post_dmt_create_customer_verify_artl_retailer');//ARTL
            Route::post('transaction', [RetailerController::class, 'billPostDmtTransaction'])->name('bill_post_dmt_transaction_retailer');
            
            Route::post('transaction-opt-send', [RetailerController::class, 'billPostDmtTransactionOTPSend'])->name('bill_post_dmt_transaction_otp_send_retailer');
            
            Route::post('transaction-otp', [RetailerController::class, 'billPostDmtTransactionOtp'])->name('bill_post_dmt_transaction_otp_retailer');
            Route::post('benf-add', [RetailerController::class, 'billPostDmtBenfAdd'])->name('bill_post_dmt_benf_add_retailer');
            Route::post('dmt-benf-delete', [RetailerController::class, 'billDmtBenfDelete'])->name('bill_post_dmt_benf_delete_retailer');
            Route::get('refund-otp/{id}', [RetailerController::class, 'billDmtRefundRequest'])->name('bill_dmt_refund_otp_retailer');
            Route::post('refund-otp-verify', [RetailerController::class, 'billDmtRefundOtpVeirfy'])->name('bill_post_dmt_refund_otp_verify_retailer');
        });
        
        Route::prefix('dmt3')->group(function () {
            Route::get('login', [RetailerController::class, 'aceDmtLogin'])->name('ace_dmt_login_retailer');
            Route::post('login', [RetailerController::class, 'acePostDmtLogin'])->name('ace_post_dmt_login_retailer');
            Route::post('create', [RetailerController::class, 'acePostDmtCreateCustomer'])->name('ace_post_dmt_create_customer_retailer');
            Route::post('verify-customer', [RetailerController::class, 'acePostDmtCreateCustomerVerify'])->name('ace_post_dmt_create_customer_verify_retailer');
            Route::post('benf-add', [RetailerController::class, 'acePostDmtBenfAdd'])->name('ace_post_dmt_benf_add_retailer');
            Route::post('transaction', [RetailerController::class, 'acePostDmtTransaction'])->name('ace_post_dmt_transaction_retailer');
            Route::post('transaction-opt-send', [RetailerController::class, 'acePostDmtTransactionOTPSend'])->name('ace_post_dmt_transaction_otp_send_retailer');
            Route::post('transaction-otp', [RetailerController::class, 'acePostDmtTransactionOtp'])->name('ace_post_dmt_transaction_otp_retailer');
        });
        
        Route::prefix('dmt4')->group(function () {
            Route::get('login', [RetailerController::class, 'digiDmtLogin'])->name('digi_dmt_login_retailer');
            Route::post('login', [RetailerController::class, 'digiPostDmtLogin'])->name('digi_post_dmt_login_retailer');
            Route::post('login-otp', [RetailerController::class, 'digiPostDmtLoginOtp'])->name('digi_post_dmt_login_otp_retailer');
            Route::get('create/{id}', [RetailerController::class, 'digiGetDmtCreateCustomer'])->name('digi_get_dmt_create_customer_retailer');
            Route::post('create', [RetailerController::class, 'digiPostDmtCreateCustomer'])->name('digi_post_dmt_create_customer_retailer');
            Route::post('aadhar-verify', [RetailerController::class, 'digiPostDmtCreateCustomerVerify'])->name('digi_post_dmt_create_customer_verify_retailer');
            Route::post('benf-add', [RetailerController::class, 'digiPostDmtBenfAdd'])->name('digi_post_dmt_benf_add_retailer');
            Route::post('benf-delete', [RetailerController::class, 'digiPostDmtBenfDelete'])->name('digi_post_dmt_benf_delete_retailer');
            Route::post('benf-delete-otp', [RetailerController::class, 'digiPostDmtBenfDeleteOtp'])->name('digi_post_dmt_benf_delete_otp_retailer');
            
            Route::post('transaction', [RetailerController::class, 'digiPostDmtTransaction'])->name('digi_post_dmt_transaction_retailer');
            Route::post('transaction-otp', [RetailerController::class, 'digiPostDmtTransactionOtp'])->name('digi_post_dmt_transaction_otp_retailer');
            
            Route::get('refund-otp/{id}', [RetailerController::class, 'digiDmtRefundRequest'])->name('digi_dmt_refund_otp_retailer');
            Route::post('refund-otp-verify', [RetailerController::class, 'digiDmtRefundOtpVeirfy'])->name('digi_post_dmt_refund_otp_verify_retailer');
        });
        
        Route::prefix('bbps')->group(function () {
            Route::get('index', [RetailerController::class, 'getBbps'])->name('get_index_bbps_retailer');
            Route::get('category/{id}', [RetailerController::class, 'getBbpsCategory'])->name('get_category_bbps_retailer');
            Route::post('biller', [RetailerController::class, 'postBbpsBiller'])->name('post_biller_bbps_retailer');
            Route::post('param', [RetailerController::class, 'postBbpsBillerParam'])->name('post_biller_param_bbps_retailer');
            Route::post('fetch', [RetailerController::class, 'postBbpsBillFetch'])->name('post_bill_fetch_bbps_retailer');
            Route::post('detail', [RetailerController::class, 'postBbpsBillerDetail'])->name('post_bill_detail_bbps_retailer');
        });
        
        Route::prefix('ccpayout')->group(function () {
            Route::get('sender', [RetailerController::class, 'getCcPayoutSender'])->name('get_sender_retailer');
            Route::post('sender', [RetailerController::class, 'postCcPayoutSender'])->name('post_sender_retailer');
            Route::get('create-sender', [RetailerController::class, 'getCreateSender'])->name('get_create_sender_retailer');
            Route::post('create-sender', [RetailerController::class, 'postCreateSender'])->name('post_create_sender_retailer');
            Route::get('beneficiaries/{id}', [RetailerController::class, 'getCcPayoutBenf'])->name('get_benf_retailer');
            Route::post('benf-add', [RetailerController::class, 'postAddBenf'])->name('post_ccpayout_dmt_benf_add_retailer');
            Route::post('transaction', [RetailerController::class, 'postDoTransactions'])->name('post_ccpayout_transaction_retailer');
        });
    });
    
    Route::prefix('retailer-report')->group(function () {
        Route::get('dmt', [RetailerController::class, 'dmtReport'])->name('dmt_report_retailer');
        Route::get('dmt-data', [RetailerController::class, 'dmtReportData'])->name('dmt_report_data_retailer');
        Route::get('scannpay', [RetailerController::class, 'scanandpayReport'])->name('scannpay_report_retailer');
        Route::get('scannpay-data', [RetailerController::class, 'scanandpayReportData'])->name('scannpay_report_data_retailer');
        
        Route::get('my-statment', [RetailerController::class, 'myStatment'])->name('my_statment_retailer');
        Route::get('my-statment-data', [RetailerController::class, 'myStatmenttData'])->name('my_statment_data_retailer');
        
        Route::get('fund-od-report', [RetailerController::class, 'fundODReport'])->name('fund_od_report_retailer');
        Route::get('fund-od-report-data', [RetailerController::class, 'fundODReportData'])->name('fund_od_report_data_retailer');
        
        Route::get('aeps', [RetailerController::class, 'aepsReport'])->name('aeps_report_retailer');
        Route::get('aeps-data', [RetailerController::class, 'aepsReportData'])->name('aeps_report_data_retailer');
        
        Route::get('buisness-report', [RetailerController::class, 'businessReport'])->name('business_report_retailer');
        Route::post('buisness-report-export', [RetailerController::class, 'businessReportData'])->name('business_report_export_retailer');
        
        Route::get('recharge', [RetailerController::class, 'rechargeReport'])->name('recharge_report_retailer');
        Route::get('recharge-data', [RetailerController::class, 'rechargeReportData'])->name('recharge_report_data_retailer');
        Route::get('recharge-data-last', [RetailerController::class, 'rechargeReportDataLast'])->name('recharge_report_data_last_retailer');
        Route::get('recharge-dth-data-last', [RetailerController::class, 'rechargeDthReportDataLast'])->name('recharge_dth_report_data_last_retailer');
        
        Route::get('bbps', [RetailerController::class, 'postBbpsReport'])->name('bbps_report_retailer');
        
        Route::get('ccpayout', [RetailerController::class, 'ccpayoutReport'])->name('ccpayout_report_retailer');
        Route::get('ccpayout-data', [RetailerController::class, 'postCcpayoutReportData'])->name('ccpayout_report_data_retailer');
    });
    
    Route::prefix('retailer-receipt')->group(function () {
        Route::get('dmt/{id}', [RetailerController::class, 'dmtReceipt'])->name('dmt_receipt_retailer');
        Route::get('dmt-uat/{id}', [RetailerController::class, 'dmtReceiptUat'])->name('dmt_receipt_uat_retailer');
    });
    
});

// layout
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// pages
Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');

// cards
Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');

// User Interface
Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

// extended ui
Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');

// icons
Route::get('/icons/boxicons', [Boxicons::class, 'index'])->name('icons-boxicons');

// form elements
Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

// form layouts
Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');

// tables
Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');
