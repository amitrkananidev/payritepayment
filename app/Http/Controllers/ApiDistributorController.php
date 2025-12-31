<?php

namespace App\Http\Controllers;
use App\Classes\UserAuth;
use App\Classes\ApiCalls;
use App\Http\Controllers\BillavenueController;
use App\Http\Controllers\CredoPayController;
use App\Http\Controllers\EkoController;
use App\Http\Controllers\BulkpeController;
use App\Http\Controllers\CyrusController;
use App\Classes\WalletCalculation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Simple\Manager;
use Log;
use Dipesh79\LaravelPhonePe\LaravelPhonePe;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

use App\Models\User;
use App\Models\user_devices;
use App\Models\user_levels;
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
use App\Models\dmt_upi_beneficiaries;
use App\Models\fund_ods;
use App\Models\fund_banks;
use App\Models\fund_requests;
use App\Models\fund_onlines;
use App\Models\user_commissions;
use App\Models\user_login_tokens;

class ApiDistributorController extends Controller
{
    public function __construct(UserAuth $Auth, 
                                WalletCalculation $WalletCalculation, 
                                ApiCalls $ApiCalls, 
                                BillavenueController $BillavenueController, 
                                EkoController $EkoController, 
                                BulkpeController $BulkpeController,
                                CredoPayController $CredoPayController,
                                CyrusController $CyrusController){
        $this->UserAuth = $Auth;
        $this->WalletCalculation = $WalletCalculation;
        $this->BillavenueController = $BillavenueController;
        $this->EkoController = $EkoController;
        $this->BulkpeController = $BulkpeController;
        $this->CyrusController = $CyrusController;
        $this->ApiCalls = $ApiCalls;
        $this->CredoPayController = $CredoPayController;
    }
    
    public function getStatics(Request $request){
        Log::info("APPgetStatics");
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        if($check_token){
            
        }else{
            return response()->json(['success' => false, 'message' => 'Unauthorized access']);
        }
    }
    
    public function getRetailer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        if($check_token){
            $user = User::where('mobile',$request->user_mobile)->first();
            
            // $data = User::with('wallet')->select('users.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"),'shop_details.shop_name')
            //     ->join('shop_details','shop_details.user_id','users.id')
            //     ->join('user_levels','user_levels.user_id','users.id')
            //     ->where('user_levels.toplevel_id',$user->id)
            //     ->where('users.user_type','2')->get();
            $data = User::with([
                            'wallet',
                            'transactions' => function ($query) {
                                $query->where('meta', 'like', '%FUND OD%');
                            }
                        ])
                    ->select([
                        'users.*',
                        DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"),
                        'shop_details.shop_name'
                    ])
                    ->join('shop_details', 'shop_details.user_id', 'users.id')
                    ->join('user_levels', 'user_levels.user_id', 'users.id')
                    ->where('user_levels.toplevel_id', $user->id)
                    ->where('users.user_type', '2')
                    ->get()
                    ->map(function ($user) {
                        $fundOdBalance_c = $user->transactions()->sum('amount');
                        
                        $user->fund_od_balance = $fundOdBalance_c;
                        return $user;
                    });
        
            return response()->json(['success' => true, 'message' => '.', 'data'=>$data]);
            
        }else{
            return response()->json(['success' => false, 'message' => 'Unauthorized access']);
        }
        
    }
    
    public function retailerStatment(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "retailer_mobile" => "required|exists:users,mobile",
            "start_date" => "required",
            "end_date" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $retailer_mobile = $request->retailer_mobile;
        $user = User::where('mobile',$retailer_mobile)->first();
        // $retailer_user = User::where('mobile',$retailer_mobile)->first();

        if($user){
            $from = $this->UserAuth->fetchFromDate($request->start_date);
            $to = $this->UserAuth->fetchToDate($request->end_date);
            $user_id = $user->id;
            $data = Transaction::select('transactions.*',DB::raw('ROUND(transactions.amount / 100, 2) as amount'),DB::raw('ROUND(transactions.balance / 100, 2) as balance'))
            ->leftJoin('fund_ods as fo', 'transactions.uuid', '=', 'fo.wallets_uuid')
            ->leftJoin('fund_onlines as fon', 'transactions.uuid', '=', 'fon.wallets_uuid')
            ->leftJoin('fund_requests as fr', 'transactions.uuid', '=', 'fr.wallets_uuid')
            ->leftJoin('transactions_aeps as ta', 'transactions.uuid', '=', 'ta.wallets_uuid')
            ->leftJoin('transactions_dmts as td', 'transactions.uuid', '=', 'td.wallets_uuid')
            ->where('transactions.payable_id',$user_id)
            ->where('payable_type', User::class)
            ->whereBetween('transactions.created_at', array($from, $to))
            ->orderBy('transactions.id', 'desc')
            ->get();
            
            return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found', 'data'=>""]);
        }
    }
    
    public function businessSummary(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "start_date" => "required",
            "end_date" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        if($user){
            $start_date = $this->UserAuth->fetchFromDate($request->start_date);
            $end_date = $this->UserAuth->fetchToDate($request->end_date);
            $user_id = $user->id;
            $users = user_levels::where('toplevel_id', $user_id)
                        ->pluck('user_id');
        
            // DMT Statistics
            $dmtStats = transactions_dmt::whereIn('user_id', $users)
                        ->whereBetween('created_at', [$start_date, $end_date])
                        ->selectRaw('
                            COUNT(CASE WHEN event = "DMT" THEN 1 END) as dmt_transaction_count,
                            COALESCE(SUM(CASE WHEN event = "dmt" THEN amount END), 0) as dmt_total_amount,
                            COUNT(CASE WHEN event = "SCANNPAY" THEN 1 END) as upi_transaction_count,
                            COALESCE(SUM(CASE WHEN event = "SCANNPAY" THEN amount END), 0) as upi_total_amount
                        ')
                        ->where('status',1)
                        ->first();
        
            // AEPS Statistics
            $aepsStats = transactions_aeps::whereIn('user_id', $users)
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->selectRaw('
                                COUNT(CASE WHEN transfer_type = "cash_withdrawal" THEN 1 END) as cash_withdrawal_count,
                                COALESCE(SUM(CASE WHEN transfer_type = "cash_withdrawal" THEN amount END), 0) as cash_withdrawal_amount
                            ')
                            ->where('status',1)
                            ->first();
            
            $user_commission = user_commissions::where("user_id",$user_id)->whereBetween('created_at', [$start_date, $end_date])->get()->sum('amount');
        
            $data = [
                'earning' => round($user_commission),
                'transactions' => $dmtStats->dmt_transaction_count + $dmtStats->upi_transaction_count + $aepsStats->cash_withdrawal_count,
                'your_business' => $dmtStats->dmt_total_amount + $dmtStats->upi_total_amount + $aepsStats->cash_withdrawal_amount,
                'dmt_stats' => [
                    'transaction_count' => $dmtStats->dmt_transaction_count ?? 0,
                    'total_amount' => $dmtStats->dmt_total_amount ?? 0,
                ],
                'upi_stats' => [
                    'transaction_count' => $dmtStats->upi_transaction_count ?? 0,
                    'total_amount' => $dmtStats->upi_total_amount ?? 0,
                ],
                'cash_withdrawal_stats' => [
                    'transaction_count' => $aepsStats->cash_withdrawal_count ?? 0,
                    'total_amount' => $aepsStats->cash_withdrawal_amount ?? 0,
                ]
            ];
            
            return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found', 'data'=>""]);
        }
    }
    
    public function retailerBusinessSummary(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "start_date" => "required",
            "end_date" => "required",
            "retailer_mobile" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        if($user){
            $retailer_mobile = $request->retailer_mobile;
            $user_ret = User::with('wallet')->where('mobile',$retailer_mobile)->first();
            
            $start_date = $this->UserAuth->fetchFromDate($request->start_date);
            $end_date = $this->UserAuth->fetchToDate($request->end_date);
            $user_id = $user_ret->id;
        
            // DMT Statistics
            $dmtStats = transactions_dmt::where('user_id', $user_id)
                        ->whereBetween('created_at', [$start_date, $end_date])
                        ->selectRaw('
                            COUNT(CASE WHEN event = "DMT" THEN 1 END) as dmt_transaction_count,
                            COALESCE(SUM(CASE WHEN event = "dmt" THEN amount END), 0) as dmt_total_amount,
                            COUNT(CASE WHEN event = "SCANNPAY" THEN 1 END) as upi_transaction_count,
                            COALESCE(SUM(CASE WHEN event = "SCANNPAY" THEN amount END), 0) as upi_total_amount
                        ')
                        ->where('status',1)
                        ->first();
        
            // AEPS Statistics
            $aepsStats = transactions_aeps::where('user_id', $user_id)
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->selectRaw('
                                COUNT(CASE WHEN transfer_type = "cash_withdrawal" THEN 1 END) as cash_withdrawal_count,
                                COALESCE(SUM(CASE WHEN transfer_type = "cash_withdrawal" THEN amount END), 0) as cash_withdrawal_amount
                            ')
                            ->where('status',1)
                            ->first();
            
            $user_commission = user_commissions::where('user_id', $user_id)->whereBetween('created_at', [$start_date, $end_date])->get()->sum('amount');
        
            $data = [
                'earning' => round($user_commission),
                'transactions' => $dmtStats->dmt_transaction_count + $dmtStats->upi_transaction_count + $aepsStats->cash_withdrawal_count,
                'your_business' => $dmtStats->dmt_total_amount + $dmtStats->upi_total_amount + $aepsStats->cash_withdrawal_amount,
                'dmt_stats' => [
                    'transaction_count' => $dmtStats->dmt_transaction_count ?? 0,
                    'total_amount' => $dmtStats->dmt_total_amount ?? 0,
                ],
                'upi_stats' => [
                    'transaction_count' => $dmtStats->upi_transaction_count ?? 0,
                    'total_amount' => $dmtStats->upi_total_amount ?? 0,
                ],
                'cash_withdrawal_stats' => [
                    'transaction_count' => $aepsStats->cash_withdrawal_count ?? 0,
                    'total_amount' => $aepsStats->cash_withdrawal_amount ?? 0,
                ]
            ];
            
            return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found', 'data'=>""]);
        }
    }
    
    public function fundOD(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "retailer_mobile" => "required",
            "amount" => "required",
            "type" => "required"
        ]);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user_dist = User::where('mobile',$request->user_mobile)->first();
        
        if($request->type == 'Credit'){
            $retailer_mobile = $request->retailer_mobile;
            $user = User::with('wallet')->where('mobile',$retailer_mobile)->first();
            $user_id = $user_dist->id;
            $amount = $request->amount;
            $user_balance = $user->wallet->balanceFloat;
            
            if($user_balance < $amount){
                // return response()->json(['success' => false, 'message' => 'Please Load Your Balance']);
                
                return response()->json(['success' => false, 'message' => 'Please Load Your Balance.']);
            }
            
            
            $retailer_id = $user->id;
            
            $i_credit = 1;
            distodcredit:
            $userwallet = User::find($user->id);
            $wallet = $userwallet->wallet;
            
            $txnid = $this->UserAuth->txnId('OD');
            
            $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
            if($balance_check){
                $balance_check = $balance_check->balance;
            }else{
                $balance_check = 0;
            }
            if($balance_check != $userwallet->wallet->balance){
                    Log::channel("balancemissmatch")->info("======== RETAILER OD CREDIT");
                    Log::channel("balancemissmatch")->info($wallet->id);
                    Log::channel("balancemissmatch")->info($balance_check->balance);
                    Log::channel("balancemissmatch")->info($userwallet->wallet->balance);
                    if($i_credit >= 25){
                        return response()->json(['success' => false, 'message' => 'Try after 5 Min.']);
                    }else{
                        $i_credit++;
                        goto distodcredit;
                    }
                    
            }
            // Deposit funds into the user's wallet
            $txn_wl = $wallet->depositFloat($amount,[
                    'meta' => [
                        'Title' => 'Fund OD',
                        'detail' => 'Credit_Retailer_'.$user->mobile.'_Fund_OD_Transfer_By_'.$user_dist->name,
                        'transaction_id' => $txnid,
                    ]
                ]);
            $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                
            $i_credit_dist = 1;
            distodcreditdist:
            // withdraw funds into the user's wallet
            $user_dist = User::find($user_id); 
            $wallet_dist = $user_dist->wallet;
            
            $balance_check_dist = Transaction::where('wallet_id', $wallet_dist->id)->orderBy('id','desc')->first();
            if($balance_check_dist->balance != $user_dist->wallet->balance){
                    Log::channel("balancemissmatch")->info("======== DIST OD DEBIT");
                    Log::channel("balancemissmatch")->info($wallet_dist->id);
                    Log::channel("balancemissmatch")->info($balance_check_dist->balance);
                    Log::channel("balancemissmatch")->info($user_dist->wallet->balance);
                    if($i_credit_dist >= 25){
                        return response()->json(['success' => false, 'message' => 'Try after 5 Min.']);
                    }else{
                        $i_credit_dist++;
                        goto distodcreditdist;
                    }
                    
            }
            $txn_wl_dist = $wallet_dist->withdrawFloat($amount,[
                    'meta' => [
                        'Title' => 'Fund OD',
                        'detail' => 'Debit_Distributor_'.$user->mobile.'_Fund_OD_Transfer_By_'.$user_dist->name,
                        'transaction_id' => $txnid,
                    ]
                ]);
            $balance_update = Transaction::where('uuid', $txn_wl_dist->uuid)->update(['balance' => $wallet_dist->balance]);
            
            $fund_req = new fund_ods();
            $fund_req->user_id = $retailer_id;
            $fund_req->transaction_id = $txnid;
            $fund_req->amount = $amount;
            $fund_req->wallets_uuid = $txn_wl->uuid;
            $fund_req->status = 1;
            $fund_req->save();
            
            // try {
            //     $msg = 'Amount ₹'.$amount.' Credited.';
            //     $api_response = $this->ApiCalls->sendfcmNotification($fund_req->user_id,"Fund Deposit",$msg);
            // }
            // catch(Exception $e) {
              
            // }
            
            
            
            
            return response()->json(['success' => true, 'message' => 'OD amount ₹'.$amount.' Transfered.']);
        }
        
        if($request->type == 'Debit'){
            $retailer_mobile = $request->retailer_mobile;
            $user_id = $user_dist->id;
            $amount = $request->amount;
            
            $i_debit = 1;
            distoddebit:
            $user = User::with('wallet')->where('mobile',$retailer_mobile)->first();
            
            $user_balance = $user->wallet->balanceFloat;
            $retailer_id = $user->id;
            if($user_balance < $amount){
                // return response()->json(['success' => false, 'message' => 'Please Load Your Balance']);
                
                return response()->json(['success' => false, 'message' => 'Retailer balance low.']);
            }
            $wallet = $user->wallet;
            $txnid = $this->UserAuth->txnId('OD');
            // Deposit funds into the user's wallet
            $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
            if($balance_check->balance != $user->wallet->balance){
                    Log::channel("balancemissmatch")->info("======== RETAILER OD DEBIT");
                    Log::channel("balancemissmatch")->info($wallet->id);
                    Log::channel("balancemissmatch")->info($balance_check->balance);
                    Log::channel("balancemissmatch")->info($user->wallet->balance);
                    if($i_debit >= 25){
                        return response()->json(['success' => false, 'message' => 'Try after 5 Min.']);
                    }else{
                        $i_debit++;
                        goto distoddebit;
                    }
                    
            }
            $txn_wl = $wallet->withdrawFloat($amount,[
                    'meta' => [
                        'Title' => 'Fund OD Reverse',
                        'detail' => 'Debit_Retailer_'.$user->mobile.'_Fund_OD_Reverse_Transfer_By_'.$user_dist->name,
                        'transaction_id' => $txnid,
                    ]
                ]);
            $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                
            $i_debit_dist = 1;
            distoddebitdist:
            // withdraw funds into the user's wallet
            $user_dist = User::find($user_id); 
            $wallet_dist = $user_dist->wallet;
            
            $balance_check_dist = Transaction::where('wallet_id', $wallet_dist->id)->orderBy('id','desc')->first();
            if($balance_check_dist->balance != $user_dist->wallet->balance){
                    Log::channel("balancemissmatch")->info("======== DIST OD CREDIT");
                    Log::channel("balancemissmatch")->info($wallet_dist->id);
                    Log::channel("balancemissmatch")->info($balance_check->balance);
                    Log::channel("balancemissmatch")->info($user_dist->wallet->balance);
                    if($i_debit_dist >= 25){
                        return response()->json(['success' => false, 'message' => 'Try after 5 Min.']);
                    }else{
                        $i_debit_dist++;
                        goto distoddebitdist;
                    }
                    
            }
            $txn_wl_dist = $wallet_dist->depositFloat($amount,[
                    'meta' => [
                        'Title' => 'Fund OD',
                        'detail' => 'Credit_Distributor_'.$user->mobile.'_Fund_OD_Reverse_Transfer_By_'.$user_dist->name,
                        'transaction_id' => $txnid,
                    ]
                ]);
            $balance_update = Transaction::where('uuid', $txn_wl_dist->uuid)->update(['balance' => $wallet_dist->balance]);
            
            $fund_req = new fund_ods();
            $fund_req->user_id = $retailer_id;
            $fund_req->transaction_id = $txnid;
            $fund_req->amount = $amount;
            $fund_req->wallets_uuid = $txn_wl->uuid;
            $fund_req->status = 1;
            $fund_req->save();
            
            // try {
            //     $msg = 'Amount ₹'.$amount.' Credited.';
            //     $api_response = $this->ApiCalls->sendfcmNotification($fund_req->user_id,"Fund Deposit",$msg);
            // }
            // catch(Exception $e) {
              
            // }
            
            return response()->json(['success' => true, 'message' => 'OD amount ₹'.$amount.' Transfered.']);
        }
    }
    
    public function myStatment(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "start_date" => "required",
            "end_date" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        if($user){
            $from = $this->UserAuth->fetchFromDate($request->start_date);
            $to = $this->UserAuth->fetchToDate($request->end_date);
            $user_id = $user->id;
            $data = Transaction::select('transactions.*',DB::raw('ROUND(transactions.amount / 100, 2) as amount'),DB::raw('ROUND(transactions.balance / 100, 2) as balance'))
            ->leftJoin('fund_ods as fo', 'transactions.uuid', '=', 'fo.wallets_uuid')
            ->leftJoin('fund_onlines as fon', 'transactions.uuid', '=', 'fon.wallets_uuid')
            ->leftJoin('fund_requests as fr', 'transactions.uuid', '=', 'fr.wallets_uuid')
            ->leftJoin('transactions_aeps as ta', 'transactions.uuid', '=', 'ta.wallets_uuid')
            ->leftJoin('transactions_dmts as td', 'transactions.uuid', '=', 'td.wallets_uuid')
            ->where('transactions.payable_id',$user_id)
            ->where('payable_type', User::class)
            ->whereBetween('transactions.created_at', array($from, $to))
            ->orderBy('transactions.id', 'desc')
            ->get();
            
            return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    
}
