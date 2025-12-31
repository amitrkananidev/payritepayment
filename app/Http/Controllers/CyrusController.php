<?php

namespace App\Http\Controllers;
use App\Classes\UserAuth;
use App\Classes\ApiCalls;
use App\Classes\WalletCalculation;

use App\Http\Controllers\EkoController;
use App\Http\Controllers\BulkpeController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Simple\Manager;
use Log;

use App\Models\User;
use App\Models\Addresses;
use App\Models\eko_services;
use App\Models\kyc_docs;
use App\Models\cities;
use App\Models\states;
use App\Models\banks;
use App\Models\dmt_customers;
use App\Models\dmt_beneficiaries;
use App\Models\shop_details;
use App\Models\transactions_dmt;
use App\Models\transactions_aeps;
use App\Models\fund_banks;
use App\Models\fund_requests;

class CyrusController extends Controller
{
    public function __construct(UserAuth $Auth, WalletCalculation $WalletCalculation, ApiCalls $ApiCalls, EkoController $EkoController, BulkpeController $BulkpeController){
        $this->UserAuth = $Auth;
        $this->WalletCalculation = $WalletCalculation;
        $this->EkoController = $EkoController;
        $this->BulkpeController = $BulkpeController;
        $this->ApiCalls = $ApiCalls;
    }
    
    public function getState($data)
    {
        $api_url = "https://cyrusrecharge.in/api/AepsBank.aspx";//"https://cyrusrecharge.in/api/PayoutAPI.aspx";
        Log::channel('cyrusapi')->info("STATE-URL");
        Log::channel('cyrusapi')->info($api_url);
        Log::channel('cyrusapi')->info("STATE-REQUEST");
        Log::channel('cyrusapi')->info($data);
        $api_response = $this->ApiCalls->cyrusPostCall($api_url,$data);
        Log::channel('cyrusapi')->info("STATE-RESPONSE");
        Log::channel('cyrusapi')->info($api_response);
        return $api_response;
    }
    
    public function getBank($data)
    {
        $api_url = "https://cyrusrecharge.in/api/AepsBank.aspx";//"https://cyrusrecharge.in/api/PayoutAPI.aspx";
        Log::channel('cyrusapi')->info("STATE-URL");
        Log::channel('cyrusapi')->info($api_url);
        Log::channel('cyrusapi')->info("STATE-REQUEST");
        Log::channel('cyrusapi')->info($data);
        $api_response = $this->ApiCalls->cyrusPostCall($api_url,$data);
        Log::channel('cyrusapi')->info("STATE-RESPONSE");
        Log::channel('cyrusapi')->info($api_response);
        return $api_response;
    }
    
    public function registrationAeps($data)
    {
        $api_url = "https://cyrusrecharge.in/api/aeps3_onboardf.aspx";//"https://cyrusrecharge.in/api/PayoutAPI.aspx";
        Log::channel('cyrusapi')->info("STATE-URL");
        Log::channel('cyrusapi')->info($api_url);
        Log::channel('cyrusapi')->info("STATE-REQUEST");
        Log::channel('cyrusapi')->info($data);
        $api_response = $this->ApiCalls->cyrusPostCall($api_url,$data);
        Log::channel('cyrusapi')->info("STATE-RESPONSE");
        Log::channel('cyrusapi')->info($api_response);
        return $api_response;
    }
    
    public function dailyKYC($data)
    {
        $api_url = "https://cyrusrecharge.in/api/aeps3_onboardf.aspx";//"https://cyrusrecharge.in/api/PayoutAPI.aspx";
        Log::channel('cyrusapi')->info("STATE-URL");
        Log::channel('cyrusapi')->info($api_url);
        Log::channel('cyrusapi')->info("STATE-REQUEST");
        Log::channel('cyrusapi')->info($data);
        $api_response = $this->ApiCalls->cyrusPostCall($api_url,$data);
        Log::channel('cyrusapi')->info("STATE-RESPONSE");
        Log::channel('cyrusapi')->info($api_response);
        return $api_response;
    }
    
    public function payoutTransaction($data)
    {
        $api_url = "https://cyrusrecharge.in/services_cyapi/payout_cyapi.aspx";//"https://cyrusrecharge.in/api/PayoutAPI.aspx";
        Log::channel('cyrusapi')->info("PAYOUT-URL");
        Log::channel('cyrusapi')->info($api_url);
        Log::channel('cyrusapi')->info("PAYOUT-REQUEST");
        Log::channel('cyrusapi')->info($data);
        $api_response = $this->ApiCalls->cyrusPostCall($api_url,$data);
        Log::channel('cyrusapi')->info("PAYOUT-RESPONSE");
        Log::channel('cyrusapi')->info($api_response);
        return $api_response;
    }
    
    public function aepsRegistration($data)
    {
        $api_url = "https://cyrusrecharge.in/api/AepsBank.aspx";
        Log::channel('cyrusapi')->info("aepsRegistration-REQUEST");
        Log::channel('cyrusapi')->info($data);
        $api_response = $this->ApiCalls->cyrusPostCall($api_url,$data);
        Log::channel('cyrusapi')->info("aepsRegistration-RESPONSE");
        Log::channel('cyrusapi')->info($api_response);
        return $api_response;
    }
    
    public function callback(Request $request)
    {
        Log::channel('cyrusapi')->info("Callback");
        Log::channel('cyrusapi')->info($request);
        $status = $request->statuscode;
        $data = json_decode($request->data);
        
        if($status == "DE_001") {
            $txn_id = $data->orderId;
            $utr = $data->rrn;
            
            $transaction = transactions_dmt::where('transaction_id',$txn_id)->where('status',0)->first();
            
            if($transaction){
                $amount = $transaction->amount;
                $transaction->status = 1;
                $transaction->utr = $utr;
                $transaction->save();
                $this->WalletCalculation->distributorUpi($txn_id);
                $this->WalletCalculation->retailorUpi($txn_id);
                // $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txn_id on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                //     $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->mobile,$text,1207172205377529606);
            }
            
            $msg = $txn_id." successfuly transfer.";
            $api_response = $this->ApiCalls->sendfcmNotification($transaction->user_id,"Scan And Pay",$msg);
            return response()->json(['success' => true, 'message' => 'success']);
        }
        
        if($status == "DE_602" || $status == "DE_405") {
            $txn_id = $data->orderId;
            $utr = $data->rrn;
            if(isset($data->Remark)) 
                $response_reason = $data->Remark;
            else
                $response_reason = '';
            
            $transaction = transactions_dmt::where('transaction_id',$txn_id)->where('status',0)->first();
            
            if($transaction){
                $userwallet = User::find($transaction->user_id);
                $wallet = $userwallet->wallet;
                $transfer_type = $transaction->transfer_type;
                $amount = $transaction->amount;
                $fee = $transaction->fee;
                $upi_id = $transaction->ben_ac_number;
                
                $txn_wl_fee = $wallet->depositFloat($fee,[
                    'meta' => [
                        'Title' => 'Scan And Pay Fee',
                        'detail' => 'Refund_Retailer_'.$userwallet->mobile.'_Scan_and_pay_'.$transfer_type.'_CustomerFee_'.$upi_id,
                        'transaction_id' => $txn_id,
                    ]
                ]);
                $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                $txn_wl = $wallet->depositFloat($amount,[
                    'meta' => [
                        'Title' => 'Scan And Pay',
                        'detail' => 'Refund_Retailer_'.$userwallet->mobile.'_Scan_and_pay_'.$transfer_type.'_Amount_'.$upi_id,
                        'transaction_id' => $txn_id,
                    ]
                ]);
                $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                $transaction->status = 2;
                $transaction->utr = $utr;
                $transaction->response_reason = $response_reason;
                $transaction->wallets_uuid = $wallet->uuid;
                $transaction->save();
                
                
                $msg = $txn_id." Failed.";
            $api_response = $this->ApiCalls->sendfcmNotification($transaction->user_id,"Scan And Pay",$msg);
            return response()->json(['success' => true, 'message' => 'success']);
            }
        }
    }
}
