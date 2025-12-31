<?php

namespace App\Http\Controllers;
use App\Classes\UserAuth;
use App\Classes\ApiCalls;
use App\Http\Controllers\AceMoneyController;
use App\Http\Controllers\BillavenueController;
use App\Http\Controllers\CredoPayController;
use App\Http\Controllers\EkoController;
use App\Http\Controllers\BulkpeController;
use App\Http\Controllers\CyrusController;
use App\Http\Controllers\SafexPayController;
use App\Http\Controllers\PayPointController;
use App\Http\Controllers\PlutosController;
use App\Classes\WalletCalculation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use CURLFile;

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
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use App\Models\app_versions;
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
use App\Models\beneficiaries;
use App\Models\dmt_beneficiaries;
use App\Models\shop_details;
use App\Models\transactions_dmt;
use App\Models\transactions_aeps;
use App\Models\dmt_upi_beneficiaries;
use App\Models\fund_banks;
use App\Models\fund_requests;
use App\Models\fund_onlines;
use App\Models\user_commissions;
use App\Models\user_login_tokens;

use App\Models\recharge_commissions;
use App\Models\recharge_slabs;
use App\Models\ace_operators;
use App\Models\transactions_recharges;

use App\Models\bbps_categories;
use App\Models\bbps_billers;
use App\Models\bbps_biller_additional_info;
use App\Models\bbps_biller_customer_params;
use App\Models\bbps_biller_payment_channels;
use App\Models\bbps_biller_payment_modes;
use App\Models\bbps_biller_response_params;
use App\Models\PanDetail;

use App\Models\CcSenders;
use App\Models\CcBeneficiaries;
use App\Models\transactions_cc;

class ApiController extends Controller
{
    public function __construct(UserAuth $Auth, 
                                WalletCalculation $WalletCalculation, 
                                ApiCalls $ApiCalls, 
                                BillavenueController $BillavenueController, 
                                EkoController $EkoController, 
                                BulkpeController $BulkpeController,
                                CredoPayController $CredoPayController,
                                CyrusController $CyrusController,
                                SafexPayController $SafexPayController,
                                PayPointController $PayPointController,
                                AceMoneyController $AceMoneyController,
                                PlutosController $PlutosController){
        $this->UserAuth = $Auth;
        $this->WalletCalculation = $WalletCalculation;
        $this->BillavenueController = $BillavenueController;
        $this->EkoController = $EkoController;
        $this->BulkpeController = $BulkpeController;
        $this->CyrusController = $CyrusController;
        $this->SafexPayController = $SafexPayController;
        $this->ApiCalls = $ApiCalls;
        $this->CredoPayController = $CredoPayController;
        $this->PayPointController = $PayPointController;
        $this->AceMoneyController = $AceMoneyController;
        $this->PlutosController = $PlutosController;
    }
    
    public function appVersion(Request $request)
    {
        if($request->get('version')){
          $ins = new app_versions();
          $ins->version = $request->get('version');
          $ins->save();
        }
        $data = app_versions::orderBy('id','DESC')->first();
        return response()->json(['success' => true, 'message' => '','version'=>$data->version]);
    }
    
    public function myUseFunction(Request $request)
    {
        
        return $this->PlutosController->getRegions('a');
        // $user_debit = $this->WalletCalculation->walletDepositFloat(65,50000,"Fund OD","Credit_Retailer_9825615077_Fund_OD_Transfer_By_CHETAN",'WEDOD250507155600838844');
        exit;
        $from = $this->UserAuth->fetchFromDate("2025-01-08");
        $to = $this->UserAuth->fetchToDate("2025-01-08");
        $user_comm = user_commissions::select("user_commissions.*")
                    ->join('transactions', 'transactions.uuid', 'user_commissions.wallets_uuid')
                    ->where('transactions.meta','LIKE','%_DMT_Remittance_Commission%')
                    ->whereBetween('user_commissions.created_at', array($from, $to))
                    ->get();
        $user_id = array();
        foreach($user_comm as $r){
            $userwallet = User::where('id',$r->user_id)->where('user_type',2)->first();
            if($userwallet){
                
                //Retailer Revers
                if(in_array($r->user_id,$user_id)){
                    
                }else{
                    
                    $balance = $userwallet->wallet->balanceFloat;
                    if($balance < $r->total_amount){
                        echo $r->user_id."#".$userwallet->mobile."#".$r->amount."Balance not debited<br>";
                    }else{
                        // $txnid = $r->transaction_id;
                        // $this->WalletCalculation->walletWithdrawFloat($r->user_id,$r->amount,"Commission Reverse","Debit_Duplicat_Commission_Reverse_$txnid",$txnid);
                    }
                    echo $userwallet->mobile."#".$r->user_id."#".$r->amount."<br>";
                    $user_id[] = $r->user_id;
                }
                
                //Dist Revers
                // if(!isset($user_id[$userwallet->id])) {
                //     $user_id[$userwallet->id] = 0;  // Initialize if doesn't exist
                //     $user_id[$userwallet->id."rev"] = 0;
                    
                // }
                // $fromR = $this->UserAuth->fetchFromDate("2025-01-08");
                // $toR = $this->UserAuth->fetchToDate("2025-01-08");
                // $reves = Transaction::where('meta','LIKE','%Debit_Duplicat_Commission_Reverse_%')->where('payable_id',$r->user_id)->whereBetween('created_at', array($fromR, $toR))->first();
                // if($reves){
                //     $reversd_amount = $reves->amount / 100;
                // }else{
                //     $reversd_amount = 0;
                // }
                
                // $revers_amount= $r->amount / 2;
                
                // $user_id[$userwallet->id] += $revers_amount;
                // $user_id[$userwallet->id."rev"] = $reversd_amount;
                
            }
            
        }
        
        // foreach($user_id as $key => $value) {
        //     if(!str_ends_with($key, 'rev')) {  // If it's not a rev key
        //         $number = $key;
        //         $mainValue = $value;
        //         $revValue = $user_id[$key . 'rev'] ?? 0;
                
        //         $final_amount = $mainValue + $revValue;
        //         if($final_amount > 0){
        //             echo $number." = ".$final_amount."<br>";
        //             // $this->WalletCalculation->walletWithdrawFloat($number,$final_amount,"Commission Reverse","Debit_Duplicat_Commission_Reverse_",0);
        //         }
                
        //     }
        // }
        
        
        
        
    }
    
    public function getToken(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            "token_pw" => "required"
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
        $plainPassword = $request->token_pw;
        $hashedPassword = '$2a$12$i6H2ti17jyVSU6qgUcP1meA1.KVQrbWfSGFJ2PJfFYo6xPAyOXRhC';
        if ($hashedPassword === $plainPassword) {
            $currentDate = app('currentDate');
            $newpassword = 'P@yr!t'.$currentDate->format('Y-m-d');
            return response()->json(['success' => true, 'message' => 'login successfully','new'=>$newpassword]);
        } else {
            
            return response()->json(['success' => false, 'message' => 'Unauthorized access']);
        }
        
    }
    
    public function token(Request $request)
    {
        #PASSWORD#mobile#DDMMYYHHMM
        #Pyr!$@#9876543210#010120241212
        $validator = Validator::make($request->all(), [
            
            "user_mobile" => "required|exists:users,mobile",
            "user_password" => "required",
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
        
        // $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $check_token = 1;
        if($check_token == 1){
            $plainPassword = $request->user_password;
            $user = User::select('id','user_id','name','surname','email','mobile','dob','first_login','password')->where('status',1)->where('user_type',2)->where('mobile',$request->user_mobile)->first();
            $hashedPassword = $user->password;
            if (Hash::check($plainPassword, $hashedPassword)) {
                // Passwords match
                $check_device = user_devices::where('user_id',$user->id)->where('device',$request->device_token)->first();
                if(!$check_device){
                    $ins_device = new user_devices();
                    $ins_device->user_id = $user->id;
                    $ins_device->device = $request->device_token;
                    $ins_device->save();
                }
                
                $check_token = user_login_tokens::where('user_id',$user->id)->count();
                if($check_token > 2){
                    $check_token = user_login_tokens::where('user_id',$user->id)->orderBy('id','ASC')->delete();
                }
                
                $token = $this->UserAuth->createToken();
                $otp = $this->UserAuth->getOTP();
                $ins_token = new user_login_tokens();
                $ins_token->user_id = $user->id;
                $ins_token->token = $token;
                $ins_token->otp = $otp;
                $ins_token->save();
                
                $wallet = User::find($user->id);
                
                $text = urlencode("Dear User, Use this OTP $otp to log in to your PAYRITE account. This OTP will be valid for the next 5 mins. https://payritepayment.in/");
                $api_response = $this->ApiCalls->smsgatewayhubGetCall($user->mobile,$text,1207170972278643017);
                
                return response()->json(['success' => true, 'message' => 'OTP Sent successfully','token'=>'','data'=>'','wallet'=>'0']);
            } else {
                // Passwords do not match;
                return response()->json(['success' => false, 'message' => 'Password Mismatch']);
            }
        }else{
            return response()->json(['success' => false, 'message' => 'Unauthorized access']);
        }
    }
    
    public function otpResend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "user_mobile" => "required|exists:users,mobile",
            "user_password" => "required",
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
        $plainPassword = $request->user_password;
        $user = User::select('id','password')->where('status',1)->where('user_type',2)->where('mobile',$request->user_mobile)->first();
        $hashedPassword = $user->password;
        if (Hash::check($plainPassword, $hashedPassword)) {
            $otp = $this->UserAuth->getOTP();
            $token = $this->UserAuth->createToken();
            $ins_token = new user_login_tokens();
            $ins_token->user_id = $user->id;
            $ins_token->token = $token;
            $ins_token->otp = $otp;
            $ins_token->save();
            
            $wallet = User::find($user->id);
            return response()->json(['success' => true, 'message' => 'OTP ReSent successfully','token'=>'','data'=>'','wallet'=>0]);
        }else{
            return response()->json(['success' => false, 'message' => 'Password Mismatch']);
        }
        
    }
    
    public function loginOtpVerify(Request $request)
    {
        #PASSWORD#mobile#DDMMYYHHMM
        #Pyr!$@#9876543210#010120241212
        $validator = Validator::make($request->all(), [
            
            "user_mobile" => "required|exists:users,mobile",
            "otp" => "required|digits:6",
            "device_token" => "required",
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
        
        // $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $check_token = 1;
        if($check_token == 1){
            
            $otp = $request->otp;
            $user = User::select('id','user_id','name','surname','email','mobile','dob','first_login','password')->where('status',1)->where('user_type',2)->where('mobile',$request->user_mobile)->first();
            $otp_check = user_login_tokens::where('user_id',$user->id)->where('otp',$otp)->first();
            if ($otp_check) {
                // Passwords match
                $check_device = user_devices::where('user_id',$user->id)->where('device',$request->device_token)->first();
                if(!$check_device){
                    $ins_device = new user_devices();
                    $ins_device->user_id = $user->id;
                    $ins_device->device = $request->device_token;
                    $ins_device->save();
                }
                
                $check_token = user_login_tokens::where('user_id',$user->id)->count();
                if($check_token > 2){
                    $check_token = user_login_tokens::where('user_id',$user->id)->orderBy('id','ASC')->delete();
                }
                
                $token = $this->UserAuth->createToken();
                
                $ins_token = new user_login_tokens();
                $ins_token->user_id = $user->id;
                $ins_token->token = $token;
                $ins_token->save();
                
                $wallet = User::find($user->id);
                return response()->json(['success' => true, 'message' => 'login successfully','token'=>$token,'data'=>$user,'wallet'=>$user->wallet->balanceFloat]);
            } else {
                // Passwords do not match;
                return response()->json(['success' => false, 'message' => 'Wrong OTP!']);
            }
        }else{
            return response()->json(['success' => false, 'message' => 'Unauthorized access']);
        }
    }
    
    public function distLogin(Request $request)
    {
        #PASSWORD#mobile#DDMMYYHHMM
        #Pyr!$@#9876543210#010120241212
        
        $validator = Validator::make($request->all(), [
            
            "user_mobile" => "required|exists:users,mobile",
            "user_password" => "required",
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
        
        // $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        Log::info($request->user_mobile."//".$request->user_password);
        $check_token = 1;
        if($check_token == 1){
            $plainPassword = $request->user_password;
            $user = User::select('id','user_id','name','surname','email','mobile','dob','first_login','password')->where('status',1)->where('user_type',3)->where('mobile',$request->user_mobile)->first();
            $hashedPassword = $user->password;
            if (Hash::check($plainPassword, $hashedPassword)) {
                // Passwords match
                $check_device = user_devices::where('user_id',$user->id)->where('device',$request->device_token)->first();
                if(!$check_device){
                    $ins_device = new user_devices();
                    $ins_device->user_id = $user->id;
                    $ins_device->device = $request->device_token;
                    $ins_device->save();
                }
                
                $check_token = user_login_tokens::where('user_id',$user->id)->count();
                if($check_token > 2){
                    $check_token = user_login_tokens::where('user_id',$user->id)->orderBy('id','ASC')->delete();
                }
                
                $token = $this->UserAuth->createToken();
                
                $ins_token = new user_login_tokens();
                $ins_token->user_id = $user->id;
                $ins_token->token = $token;
                $ins_token->save();
                
                $wallet = User::find($user->id);
                Log::info('login successfully');
                return response()->json(['success' => true, 'message' => 'login successfully','token'=>$token,'data'=>$user,'wallet'=>$user->wallet->balanceFloat]);
            } else {
                // Passwords do not match;
                Log::info('Passwords do not match');
                return response()->json(['success' => false, 'message' => 'Password Mismatch']);
            }
        }else{
            return response()->json(['success' => false, 'message' => 'Unauthorized access']);
        }
    }
    
    public function getBalance(Request $request){
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
        
        if($check_token == 0){
            return response()->json(['success' => false, 'message' => 'Unauthorized access']);
        }
        
        $user = User::where('mobile',$request->user_mobile)->first();
        $wallet = User::find($user->id);
        $kyc = kyc_docs::where('user_id',$user->id)->first();
        
        $eko_services = eko_services::where('user_id',$user->id)->first();
        if($kyc){
            $kyc_status = $kyc->status;
        }else{
            $kyc_status = 3;
        }
        
        if($eko_services){
            $cyrus_aeps = $eko_services->cyrus_aeps;
            $cyrus_aeps_daily = $eko_services->cyrus_aeps_daily;
        }else{
            $cyrus_aeps = 0;
            $cyrus_aeps_daily = 0;
        }
        
        return response()->json(['success' => true, 'message' => 'successfully','kyc_status'=>$kyc_status,'wallet'=>$wallet->wallet->balanceFloat,'cyrus_aeps'=>$cyrus_aeps,'aeps_daily'=>$cyrus_aeps_daily]);
        
    }
    
    public function walletTest(Request $request)
    {
        $n = 10;
        for($i=0;$i <= $n;$i++){
            sleep(1);
        testingwallet:
        $user = User::find(3);

        // Create a wallet for the user
        $wallet = Wallet::findOrNew($user);
        
        // Check if the user has a wallet
        if (!$wallet) {
            // If the user doesn't have a wallet, create one
            $wallet = Wallet::create(['user_id' => $userId]);
        } else {
            // If the user already has a wallet, fetch it
            $wallet = $user->wallet;
        }
        // echo $wallet->id;
        // echo "<br>";
        $wallet->refreshBalance();
        $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
        // echo $balance_check->balance;echo "<br>";
        // echo $wallet->balance;echo "<br>";
        if($balance_check->balance != $wallet->balance){
            goto testingwallet;
        }
        // exit;
        // echo $wallet->balanceFloat;exit;
        // Deposit funds into the user's wallet
        // $wallet->depositFloat(1000.50);
        
        
        $txn_wl_d = $wallet->depositFloat(10,[
                        'meta' => [
                            'Title' => 'Admin Crdit',
                            'detail' => 'TESTING WALLRY',
                            'transaction_id' => 11111,
                        ]
                    ]);
                    
        $balance_update = Transaction::where('uuid', $txn_wl_d->uuid)->update(['balance' => $wallet->balance]);
        
        $txn_wl = $wallet->withdrawFloat(10,[
                        'meta' => [
                            'Title' => 'Admin Debit',
                            'detail' => 'TESTING WALLRY',
                            'transaction_id' => 11111,
                        ]
                    ]);
        
        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
        
        // Deduct funds from the user's wallet
        // $wallet->withdrawFloat(5.4);
        
        // Retrieve the current balance of the user's wallet
        $balance = $wallet->balanceFloat;
        echo "<br>";
        echo $balance;
        }
    }
    
    public function getProfile(Request $request){
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
        
        $baseurl2 = env('APP_URL').'/uploads/profile_pics/';
        $profile_pic = DB::raw("CONCAT('$baseurl2',users.profile_image) AS profile_pic");
        $user = User::select('name','surname','email','mobile','dob',$profile_pic)->where('mobile',$request->user_mobile)->first();
        
        return response()->json(['success' => true, 'message' => 'successfully','data'=>$user]);
    }
    
    public function getKyc(Request $request){
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
        
        $baseurl2 = env('APP_URL').'/uploads/kycdocs/';
        $pan = DB::raw("CONCAT('$baseurl2',kyc_docs.pan_image) AS pan_image");
        $aadhaar_front_image = DB::raw("CONCAT('$baseurl2',kyc_docs.aadhaar_front_image) AS aadhaar_front_image");
        $aadhaar_back_image = DB::raw("CONCAT('$baseurl2',kyc_docs.aadhaar_back_image) AS aadhaar_back_image");
        
        $user = User::where('mobile',$request->user_mobile)->first();
        $doc = kyc_docs::select($pan,$aadhaar_front_image,$aadhaar_back_image,'pan_number','aadhaar_number','status')->where('user_id',$user->id)->first();
        
        if($doc){
            $upload_status = $doc->status;
        }else{
            $doc = "";
            $upload_status = 3;
        }
        
        return response()->json(['success' => true, 'message' => 'successfully','data'=>$doc,'upload_status'=>$upload_status]);
    }
    
    public function getShop(Request $request){
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
        
        $baseurl2 = env('APP_URL').'/uploads/shop/';
        $shop_img = DB::raw("CONCAT('$baseurl2',shop_details.shop_img) AS shop_img");
        $baseurl3 = env('APP_URL').'/uploads/selfie/';
        $selfie = DB::raw("CONCAT('$baseurl3',shop_details.selfie) AS selfie");
        
        $user = User::where('mobile',$request->user_mobile)->first();
        $doc = shop_details::select($selfie,$shop_img,'latitude','longitude','shop_name','shop_address','contact_number','status')->where('user_id',$user->id)->first();
        
        if($doc){
            $upload_status = $doc->status;
        }else{
            $doc = "";
            $upload_status = 3;
        }
        
        return response()->json(['success' => true, 'message' => 'successfully','data'=>$doc,'upload_status'=>$upload_status]);
    }
    
    public function updateKyc(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "pan_image" => "required",
            "aadhaar_front_image" => "required",
            "aadhaar_back_image" => "required",
            "pan_number" => "required",
            "aadhaar_number" => "required",
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
        $doc = kyc_docs::where('user_id',$user->id)->where('status',3)->first();
        if($doc){
            
                
                if ($request->input('pan_image')) {
                    $image = $request->input('pan_image');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $panimagename = 'PAN' . $user->mobile . time() . '.jpg';
                    $filePath = $panimagename;
                    Storage::disk('public_img_kyc')->put($filePath, base64_decode($image));
                }
                
                if ($request->input('aadhaar_front_image')) {
                    $image = $request->input('aadhaar_front_image');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $aadharfimagename = 'ADHARF' . $user->mobile . time() . '.jpg';
                    $filePath = $aadharfimagename;
                    Storage::disk('public_img_kyc')->put($filePath, base64_decode($image));
                }
                
                if ($request->input('aadhaar_back_image')) {
                    $image = $request->input('aadhaar_front_image');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $aadharbimagename = 'ADHARB' . $user->mobile . time() . '.jpg';
                    $filePath = $aadharbimagename;
                    Storage::disk('public_img_kyc')->put($filePath, base64_decode($image));
                }
                
                $doc->pan_image = $panimagename;
                $doc->aadhaar_front_image = $aadharfimagename;
                $doc->aadhaar_back_image = $aadharbimagename;
                $doc->pan_number = $request->get("pan_number");
                $doc->aadhaar_number = $request->get("aadhaar_number");
                $doc->status = 0;
                $doc->save();
                
                return response()->json(['success' => true, 'message' => 'KYC Update successfully']);
        }else{
            
                if ($request->input('pan_image')) {
                    $image = $request->input('pan_image');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $panimagename = 'PAN' . $user->mobile . time() . '.jpg';
                    $filePath = $panimagename;
                    Storage::disk('public_img_kyc')->put($filePath, base64_decode($image));
                }
                
                if ($request->input('aadhaar_front_image')) {
                    $image = $request->input('aadhaar_front_image');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $aadharfimagename = 'ADHARF' . $user->mobile . time() . '.jpg';
                    $filePath = $aadharfimagename;
                    Storage::disk('public_img_kyc')->put($filePath, base64_decode($image));
                }
                
                if ($request->input('aadhaar_back_image')) {
                    $image = $request->input('aadhaar_front_image');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $aadharbimagename = 'ADHARB' . $user->mobile . time() . '.jpg';
                    $filePath = $aadharbimagename;
                    Storage::disk('public_img_kyc')->put($filePath, base64_decode($image));
                }
                $doc = new kyc_docs();
                $doc->user_id = $user->id;
                $doc->pan_image = $panimagename;
                $doc->aadhaar_front_image = $aadharfimagename;
                $doc->aadhaar_back_image = $aadharbimagename;
                $doc->pan_number = $request->get("pan_number");
                $doc->aadhaar_number = $request->get("aadhaar_number");
                $doc->status = 0;
                $doc->save();
                
                return response()->json(['success' => true, 'message' => 'KYC Update successfully']);
        }
            
    }
    
    public function updateStore(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "shop_name" => "required",
            "shop_address" => "required",
            "shop_img" => "required",
            "selfie" => "required",
            "latitude" => "required",
            "longitude" => "required",
            "contact_number" => "required",
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
        $doc = shop_details::where('user_id',$user->id)->where('status',3)->first();
        if($doc){
            
                
                if ($request->input('shop_img')) {
                    $image = $request->input('shop_img');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $shopimagename = 'SHOP' . $user->mobile . time() . '.jpg';
                    $filePath = $shopimagename;
                    Storage::disk('public_img_shop')->put($filePath, base64_decode($image));
                }
                
                if ($request->input('selfie')) {
                    $image = $request->input('selfie');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $selfieimagename = 'SELFIE' . $user->mobile . time() . '.jpg';
                    $filePath = $selfieimagename;
                    Storage::disk('public_img_selfie')->put($filePath, base64_decode($image));
                }
                
                $doc->shop_name = $request->get("shop_name");
                $doc->shop_address = $request->get("shop_address");
                $doc->shop_img = $shopimagename;
                $doc->selfie = $selfieimagename;
                $doc->latitude = $request->get("latitude");
                $doc->longitude = $request->get("longitude");
                $doc->contact_number = $request->get("contact_number");
                $doc->status = 0;
                $doc->save();
                
                return response()->json(['success' => true, 'message' => 'Shop Detail Update successfully']);
        }else{
            
                if ($request->input('shop_img')) {
                    $image = $request->input('shop_img');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $shopimagename = 'SHOP' . $user->mobile . time() . '.jpg';
                    $filePath = $shopimagename;
                    Storage::disk('public_img_shop')->put($filePath, base64_decode($image));
                }
                
                if ($request->input('selfie')) {
                    $image = $request->input('selfie');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $selfieimagename = 'SELFIE' . $user->mobile . time() . '.jpg';
                    $filePath = $selfieimagename;
                    Storage::disk('public_img_selfie')->put($filePath, base64_decode($image));
                }
                
                $doc = new shop_details();
                $doc->user_id = $user->id;
                $doc->shop_name = $request->get("shop_name");
                $doc->shop_address = $request->get("shop_address");
                $doc->shop_img = $shopimagename;
                $doc->selfie = $selfieimagename;
                $doc->latitude = $request->get("latitude");
                $doc->longitude = $request->get("longitude");
                $doc->contact_number = $request->get("contact_number");
                $doc->status = 0;
                $doc->save();
                
                return response()->json(['success' => true, 'message' => 'Shop Detail Update successfully']);
        }
            
    }
    
    public function updateProfileImage(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "image" => "required",
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
            
                
                if ($request->input('image')) {
                    
                    
                    $image = $request->input('image');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $imagename = 'IMG' . $user->mobile . time() . '.jpg';
                    $filePath = $imagename;
                    Storage::disk('public_img_profile')->put($filePath, base64_decode($image));
                }
                
                $user->profile_image = $imagename;
                $user->save();
                
                return response()->json(['success' => true, 'message' => 'Profile Image Update successfully']);
        }else{
            return response()->json(['success' => false, 'message' => "User Not Found"]);
        }
            
    }
    
    public function updateProfile(Request $request){
        
        if($request->get('c_password')){
            $validator = Validator::make($request->all(), [
                "token" => "required",
                "user_mobile" => "required|exists:users,mobile",
                "c_password" => "required",
                "new_password" => "required|string|min:8",
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
            
            $user = User::where('status',1)->where('user_type',2)->where('mobile',$request->user_mobile)->first();
            $hashedPassword = $user->password;
            $plainPassword = $request->get('c_password');
            $newPassword = $request->get('new_password');
            if(Hash::check($plainPassword, $hashedPassword)) {
                $user->password = bcrypt($newPassword);
                $user->save();
                
                return response()->json(['success' => true, 'message' => "Password Update!"]);
            }else{
                return response()->json(['success' => false, 'message' => "Check Your Current Passowrd!"]);
            }
        }else{
            
        
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "name" => "required",
            "surname" => "required",
            "dob" => "required",
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
            
            
                $user->name = $request->get("name");
                $user->surname = $request->get("surname");
                $user->dob = $request->get("dob");
                $user->save();
                
                return response()->json(['success' => true, 'message' => 'Profile Update successfully']);
        }else{
            return response()->json(['success' => false, 'message' => "User Not Found"]);
        }
        }
            
    }
    
    public function createCustomer(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "mobile" => "required",
            "first_name" => "required",
            "address" => "required",
            "city" => "required",
            "state" => "required",
            "pincode" => "required",
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
            
            
            $check_customer = dmt_customers::where('mobile',$request->mobile)->where('status','1')->first();
            if(!$check_customer){
                $otp = rand(100000, 999999);
                $ins = dmt_customers::where('mobile',$request->mobile)->where('status','0')->first();
                if($ins){
                    $ins->user_id = $user->id;
                    $ins->mobile = $request->mobile;
                    $ins->first_name = $request->first_name;
                    $ins->last_name = $request->last_name;
                    $ins->address = $request->address;
                    $ins->city = $request->city;
                    $ins->state = $request->state;
                    $ins->pincode = $request->pincode;
                    $ins->status = 0;
                    $ins->otp = $otp;
                }else{
                    $ins = new dmt_customers();
                    $ins->user_id = $user->id;
                    $ins->mobile = $request->mobile;
                    $ins->first_name = $request->first_name;
                    $ins->last_name = $request->last_name;
                    $ins->address = $request->address;
                    $ins->city = $request->city;
                    $ins->state = $request->state;
                    $ins->pincode = $request->pincode;
                    $ins->status = 0;
                    $ins->otp = $otp;
                }
                
                $ins->save();
                
                $text = urlencode("Dear User, Use this OTP $otp to log in to your PAYRITE account. This OTP will be valid for the next 5 mins. https://payritepayment.in/");
                $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->mobile,$text,1207170972278643017);
                
                return response()->json(['success' => true, 'message' => 'Otp Send','is_reg'=>0]);
            }else{
                return response()->json(['success' => true, 'message' => 'Customer Already available','data'=>$check_customer,'is_reg'=>1]);
            }
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
        
    }
    
    public function otpCustomerVerify(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "mobile" => "required",
            "otp" => "required",
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
        
        $check_customer = dmt_customers::where('user_id',$user->id)->where('mobile',$request->mobile)->where('otp',$request->otp)->where('status','0')->first();
        if($check_customer){
            $check_customer->status = 1;
            $check_customer->save();
            $customer = dmt_customers::where('user_id',$user->id)->where('mobile',$request->mobile)->first();
            $dmt_limit = env('DMT_TXN_LIMIT');
            $dmt_use = $this->UserAuth->getCustomerUse($request->mobile);
            $available = $dmt_limit - $dmt_use;
            return response()->json(['success' => true, 'message' => 'OTP Verify','data'=>$customer,'is_reg'=>1,'available'=>$available,'used'=>$dmt_use,'limit'=>$dmt_limit]);
        }else{
            return response()->json(['success' => true, 'message' => 'OTP Not Match','is_reg'=>0]);
        }
        
    }
    
    public function customerLogin(Request $request){
       // return response()->json(['success' => false, 'message' => 'Service Is Dowm.']);
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "mobile" => "required",
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
        
        $check_customer = dmt_customers::where('mobile',$request->mobile)->where('status','1')->first();
        if($user){
            if($check_customer){
                $dmt_limit = env('DMT_TXN_LIMIT');
                $dmt_use = $this->UserAuth->getCustomerUse($request->mobile);
                $available = $dmt_limit - $dmt_use;
                return response()->json(['success' => true, 'message' => 'Customer Login','data'=>$check_customer,'is_reg'=>1,'available'=>$available,'used'=>$dmt_use,'limit'=>$dmt_limit]);
            }else{
                return response()->json(['success' => true, 'message' => 'Customer Not Found','is_reg'=>0]);
            }
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
    }
    
    public function getBeneficiaries(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required|exists:dmt_customers,mobile",
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
        
        $get_beneficiaries = dmt_beneficiaries::where('customer_mobile',$request->customer_mobile)->where('status','1')->orderBy('id','DESC')->get();
        
        if($user){
            return response()->json(['success' => true, 'message' => 'Beneficiaries','data'=>$get_beneficiaries]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    public function addBeneficiary(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required|exists:dmt_customers,mobile",
            "name" => "required",
            "account" => "required",
            "ifsc" => "required",
            "is_verify" => "required",
            "bank_name" => "required",
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
            
            
                $ins = new dmt_beneficiaries();
                $ins->user_id = $user->id;
                $ins->customer_mobile = $request->customer_mobile;
                $ins->bank_name = $request->bank_name;
                $ins->account_holder_name = $request->name;
                $ins->account_number = $request->account;
                $ins->ifsc = $request->ifsc;
                $ins->is_verify = $request->is_verify;
                $ins->save();
                
                return response()->json(['success' => true, 'message' => 'Beneficiary Add']);
            
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
        
    }
    
    public function deleteBeneficiary(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required|exists:dmt_customers,mobile",
            "beneficiary_id" => "required|exists:dmt_beneficiaries,id",
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
            $get_benf = dmt_beneficiaries::where('id',$request->beneficiary_id)->where('customer_mobile',$request->customer_mobile)->first();
            $get_benf->status = 2;
            $get_benf->save();
            
            return response()->json(['success' => true, 'message' => 'Beneficiary Removed.']);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
    }
    
    public function doTransactions(Request $request){
        // return response()->json(['success' => false, 'message' => 'Failed']);
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required|exists:dmt_customers,mobile",
            "beneficiary_id" => "required|exists:dmt_beneficiaries,id",
            "amount" => [
                            "required",
                            "numeric",
                            function($attribute, $value, $fail) {
                                // Check if the amount is greater than 100
                                if ($value < 100) {
                                    $fail($attribute . ' must be greater than 100.');
                                }
                            },
                        ],
            "transfer_type" => "required",
            "latitude" => "required",
            "longitude" => "required",
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
        
        $shop = shop_details::where('user_id',$user->id)->first();
            if($shop->latitude == null || $shop->longitude == null){
                return response()->json(['success' => false, 'message' => "Please Insert Shop Detail."]);
            }
            
            $distance = $this->UserAuth->geteDistance($shop->latitude, $shop->longitude, $request->latitude, $request->longitude);
            log::channel("location")->info("Retailer");
            log::channel("location")->info($request->latitude."/".$request->longitude);
            log::channel("location")->info("SHOP");
            log::channel("location")->info($shop->latitude."/".$shop->longitude);
            log::channel("location")->info("KM");
            log::channel("location")->info($distance);
            $distance_mnd = 25;
            if(isset($request->accuracy)){
                $distance_mnd = $distance_mnd + $request->accuracy;
                if($request->accuracy >= 100){
                    $distance_mnd = 250;
                }
            }
            
            if($distance > $distance_mnd){
                return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
            }
        
        $amount = $request->amount;
        $transfer_type = $request->transfer_type;
        
        $dmt_limit = env('DMT_TXN_LIMIT');
        $dmt_use = $this->UserAuth->getCustomerUse($request->customer_mobile);
        $check_limit = $dmt_use + $amount;
        log::info("DMT LIMIT");
        log::info($check_limit."/".$request->customer_mobile);
        if($dmt_limit <= $check_limit){
            return response()->json(['success' => false, 'message' => "Your Monthly Limit Is $dmt_limit."]);
        }
        
        if($amount >= 99 && $amount <= 1001){
            $fee = 10;
        }else{
            $fee = $amount * 1/100;
        }
        
        
        $totalamount = $amount + $fee;
        $gst = $fee - ($fee / 1.18);
        
        sleep(rand(1,6));
        if($user){
            $userwallet = User::find($user->id);
            $balance = $userwallet->wallet->balanceFloat;
            // print_r($balance);exit;
            if($balance < $totalamount){
                return response()->json(['success' => false, 'message' => 'Insufficient balance']);
            }
            // $wallet = Wallet::findOrNew($user);
            $wallet = $userwallet->wallet;
            $txnid = $this->UserAuth->txnId('DMT');
            
            $get_benf = dmt_beneficiaries::find($request->beneficiary_id);
            
            
            $recentTime = Carbon::now()->subMinutes(10);
            
            $duplicate = transactions_dmt::where('user_id', $user->id)
                            ->where('mobile', $request->customer_mobile)
                            ->where('ben_ac_number', $get_benf->account_number)
                            ->where('ben_ac_ifsc', $get_benf->ifsc)
                            ->where('amount', $amount)
                            ->where('created_at', '>=', $recentTime)
                            ->exists();
            if ($duplicate) {
                log::info('Duplicate');
                log::info($request->all());
                return response()->json(['success' => false, 'message' => 'Duplicate! By Mistake Double Click or Duplicat Transaction. Please Check you report.']);
            }
            
            $txn_wl_fee = $this->WalletCalculation->walletWithdrawFloat($user->id,$fee,"Money Transfer Fee",'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnid);
            // $txn_wl_fee = $wallet->withdrawFloat($fee,[
            //     'meta' => [
            //         'Title' => 'Money Transfer Fee',
            //         'detail' => 'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
            //         'transaction_id' => $txnid,
            //     ]
            // ]);
            // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
            
            $txn_wl = $this->WalletCalculation->walletWithdrawFloat($user->id,$amount,"Money Transfer",'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnid);
            // $txn_wl = $wallet->withdrawFloat($amount,[
            //     'meta' => [
            //         'Title' => 'Money Transfer',
            //         'detail' => 'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
            //         'transaction_id' => $txnid,
            //     ]
            // ]);
            // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
            
            
            
            $ins = new transactions_dmt();
            $ins->user_id = $user->id;
            $ins->event = 'DMT';
            $ins->transaction_id = $txnid;
            $ins->amount = $amount;
            $ins->mobile = $request->customer_mobile;
            $ins->transfer_type = $transfer_type;
            $ins->fee = $fee;
            $ins->tds = 0;
            $ins->gst = $gst;
            $ins->closing_balance = $wallet->balanceFloat;
            $ins->ben_name = $get_benf->account_holder_name;
            $ins->ben_ac_number = $get_benf->account_number;
            $ins->ben_ac_ifsc = $get_benf->ifsc;
            $ins->utr = 0;
            $ins->status = 0;
            $ins->wallets_uuid = $txn_wl;
            $ins->dmt_beneficiary_id = $get_benf->id;
            $ins->save();
            
            if ($duplicate) {
                return response()->json(['message' => 'Duplicate entry detected'], 409); // HTTP 409 Conflict
            }
            
            if($user->id == 12){
                $api = "SAFEXPAY";
            }else{
                // return response()->json(['success' => false, 'message' => 'Failed']);
                $api = "SAFEXPAY";
            }         
            
            
            if($api == 'CYRUS'){
                
            }elseif($api == 'SAFEXPAY'){
                Log::info($api);
            // $api_params = '{
            //             "amount": '.$amount.',
            //             "payment_mode": "'.$transfer_type.'",
            //             "reference_id": "'.$txnid.'",
            //             "transcation_note": "",
            //             "beneficiaryName": "'.$get_benf->account_holder_name.'",
            //             "upi": "",
            //             "account_number": "'.$get_benf->account_number.'",
            //             "ifsc": "'.$get_benf->ifsc.'"
            //         }';
            $api_params = '{
                    "header": {
                    "operatingSystem": "WEB",
                    "sessionId": "AGEN3580024871",
                    "version": "1.0.0"
                    },
                    "userInfo": {},
                    "transaction": {
                    "requestType": "WTW",
                    "requestSubType": "PWTB",
                    "tranCode": 0,
                    "txnAmt": '.$amount.',
                    "id": "AGEN3580024871",
                    "surChargeAmount": 0.0,
                    "txnCode": 0,
                    "userType": 0
                    },
                    "payOutBean": {
                    "mobileNo": "'.$request->customer_mobile.'",
                    "txnAmount": "'.$amount.'",
                    "accountNo": "'.$get_benf->account_number.'",
                    "ifscCode": "'.$get_benf->ifsc.'",
                    "bankName": "'.$get_benf->bank_name.'",
                    "accountHolderName": "'.$get_benf->account_holder_name.'",
                    "txnType": "'.$transfer_type.'",
                    "accountType": "Saving",
                    "emailId": "payrite.developer@gmail.com",
                    "orderRefNo": "'.$txnid.'",
                    "count": 0
                    }
                    }';
            
            // $result = $this->BulkpeController->payoutTransaction($api_params);
            $result = $this->SafexPayController->transactions($api_params);
            
            
            $json = json_decode($result);
            if(isset($json->response)){
                if($json->response->code == "0000"){
                    //success
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 1;
                    $update->response_reason = $json->response->description;
                    $update->utr = $json->payOutBean->bankRefNo;
                    $update->save();
                    // $this->WalletCalculation->distributorDmt($txnid);
                    // $this->WalletCalculation->retailorDmt($txnid);
                    $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txnid on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                    $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->customer_mobile,$text,1207172205377529606);
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    return response()->json(['success' => true, 'message' => 'Transaction Success.', 'data'=>$data]);
                    
                }elseif($json->response->code == "0001"){
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                }else{
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnid);
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnid);
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->utr = 0;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.2', 'data'=>$data]);
                }
                
            }else{
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnid);
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnid);
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->utr = 0;
                    $update->save();
                    
                    
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            
            }elseif($api == 'BULKPE'){
            $api_params = '{
                        "amount": '.$amount.',
                        "payment_mode": "'.$transfer_type.'",
                        "reference_id": "'.$txnid.'",
                        "transcation_note": "",
                        "beneficiaryName": "'.$get_benf->account_holder_name.'",
                        "upi": "",
                        "account_number": "'.$get_benf->account_number.'",
                        "ifsc": "'.$get_benf->ifsc.'"
                    }';
            
            $result = $this->BulkpeController->payoutTransaction($api_params);
            
            
            $json = json_decode($result);
            if(!isset($json->error)){
                if($json->data->status == "SUCCESS"){
                    //success
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 1;
                    $update->response_reason = $json->data->payment_remark;
                    $update->utr = $json->data->utr;
                    $update->save();
                    // $this->WalletCalculation->distributorDmt($txnid);
                    // $this->WalletCalculation->retailorDmt($txnid);
                    $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txnid on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                    $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->customer_mobile,$text,1207172205377529606);
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    return response()->json(['success' => true, 'message' => 'Transaction Success.', 'data'=>$data]);
                    
                }elseif($json->data->status == "PENDING"){
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                }else{
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnid);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnid);
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->utr = 0;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                }
                
            }else{
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnid);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnid);
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->utr = 0;
                    $update->save();
                    
                    
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            
            }elseif($api == 'EKO'){
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            if($transfer_type == 'IMPS'){
                $payment_mode = 5;
            }else{
                $payment_mode = 4;
            }
            
            $holder_name = str_replace('  ', ' ', $get_benf->account_holder_name);
            $post = [
                "initiator_id" => $initiator_id,
                "amount" => $amount,
                "payment_mode" => $payment_mode,
                "client_ref_id" => $txnid,
                "recipient_name" => $holder_name,
                "ifsc" => $get_benf->ifsc,
                "account" => $get_benf->account_number,
                "service_code" => "45",
                "sender_name" => "Testing",
                "source" => "NEWCONNECT",
                "tag" => "retailer",
                "beneficiary_account_type" => "1"
            ];
            $api_params = http_build_query($post, '', '&');
            $result = $this->EkoController->payoutTransaction($api_params,'35607001');
            
            
            $json = json_decode($result);
            if($json->status == 0){
                if($json->data->tx_status == 0){
                    //success
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 1;
                    $update->eko_status = $json->data->tx_status;
                    $update->utr = $json->data->tid;
                    $update->save();
                    // $this->WalletCalculation->distributorDmt($txnid);
                    // $this->WalletCalculation->retailorDmt($txnid);
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txnid on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                    $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->customer_mobile,$text,1207172205377529606);
                    return response()->json(['success' => true, 'message' => 'Transaction Completed.', 'data'=>$data]);
                    
                }elseif($json->data->tx_status == 2){
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->eko_status = $json->data->tx_status;
                    $update->utr = $json->data->tid;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction Under Process.', 'data'=>$data]);
                }elseif($json->data->tx_status == 5 || $json->data->tx_status == 3){
                    $update = transactions_dmt::find($ins->id);
                    $update->eko_status = $json->data->tx_status;
                    $update->utr = $json->data->tid;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction Under Process.', 'data'=>$data]);
                }else{
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnid);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnid);
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->utr = $json->data->utr;
                    $update->save();
                    
                    
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                }
                
            }else{
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnid);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnid);
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->utr = 0;
                    $update->save();
                    
                    
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            
            }
            
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
    }
    
    public function dmtReport(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "start_date" => "required",
            "end_date" => "required",
            "dmt_api" => "required",
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
            $api_id = $request->dmt_api;
            // $data = transactions_dmt::select('transactions_dmts.*',
            // DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
            // ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
            // ->join('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
            // ->where('transactions_dmts.user_id',$user->id)
            // ->where('dmt_customers.status','1')
            // ->whereColumn('dmt_beneficiaries.user_id','transactions_dmts.user_id')
            // ->whereColumn('dmt_customers.user_id','transactions_dmts.user_id')
            // ->whereBetween('transactions_dmts.created_at', array($from, $to))
            // ->orderBy('transactions_dmts.id','DESC')->get();
            
            $query = transactions_dmt::select(
                        'transactions_dmts.*',
                        DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"),
                        'users.mobile as retailer_mobile',
                        'shop_details.shop_name'
                    )
                    ->join('users', 'users.id', 'transactions_dmts.user_id')
                    ->join('shop_details', 'shop_details.user_id', 'transactions_dmts.user_id')
                    ->where('transactions_dmts.user_id', $user->id)
                    ->where('transactions_dmts.event', 'DMT')
                    ->whereBetween('transactions_dmts.created_at', [$from, $to]);
            
            // Add joins only if transactions_dmts.api_id is not 2
            if ($api_id == 0) {
                $query->addSelect(
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),
                    'dmt_customers.mobile as customer_mobile',
                    'dmt_beneficiaries.bank_name as bank_name'
                )
                ->join('dmt_customers', 'dmt_customers.mobile', 'transactions_dmts.mobile')
                ->leftJoin('dmt_beneficiaries', 'dmt_beneficiaries.id', 'transactions_dmts.dmt_beneficiary_id')
                ->where('dmt_customers.status', 1)->where('transactions_dmts.api_id', 0);
            }else{
                $query->addSelect(
                    'transactions_dmts.sender_name as customer_name',
                    'transactions_dmts.mobile as customer_mobile',
                    'transactions_dmts.bank_name as bank_name'
                );
                $query->where('transactions_dmts.api_id', $api_id);
            }
            
            $data = $query->orderBy('transactions_dmts.id', 'DESC')->get();
            
            return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    public function upiReport(Request $request){
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
            $data = transactions_dmt::select('transactions_dmts.*',
            DB::raw("CONCAT(users.name, ' ', users.surname) as customer_name"),'users.mobile as customer_mobile','dmt_beneficiaries.bank_name')
            ->join('users','users.id','transactions_dmts.user_id')
            ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
            ->where('transactions_dmts.user_id',$user->id)
            ->where('transactions_dmts.event','SCANNPAY')
            ->whereBetween('transactions_dmts.created_at', array($from, $to))
            ->orderBy('transactions_dmts.id','DESC')->get();
            
            return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
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
    
    public function aepsReport(Request $request){
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
            $data = transactions_aeps::select('transactions_aeps.*','banks.name as bank_name')
            ->join('banks','banks.credopay_id','transactions_aeps.bank_iin')
            ->where('transactions_aeps.user_id',$user->id)
            ->whereBetween('transactions_aeps.created_at', array($from, $to))
            ->orderBy('transactions_aeps.id','DESC')->get();
            
            return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    public function accountVerification(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "account" => "required",
            "ifsc" => "required",
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
            $userwallet = User::find($user->id);
            $balance = $userwallet->wallet->balanceFloat;
            $amount = 4;
            // print_r($balance);exit;
            if($balance < $amount){
                return response()->json(['success' => false, 'message' => 'Insufficient balance']);
            }
            
            $check_benf = beneficiaries::where('account_number',$request->account)->where('ifsc',$request->ifsc)->first();
            if($check_benf){
                $txnid = $this->UserAuth->txnId('BCV');
                $userwallet = User::find($user->id);
                $wallet = $userwallet->wallet;
                $totalamount = 4;
                $txn_wl = $this->WalletCalculation->walletWithdrawFloat($user->id,$totalamount,'Account Verification','Debit_Retailer_'.$user->mobile.'_Account_Verification_Fee_'.$request->account,$txnid);
                
                $update = new transactions_dmt();
                $update->user_id = $user->id;
                $update->event = 'ACCOUNTVERIFY';
                $update->transaction_id = $txnid;
                $update->amount = $totalamount;
                $update->fee = 0;
                $update->tds = 0;
                $update->gst = 0;
                $update->ben_name = $check_benf->account_holder_name;
                $update->ben_ac_number = $request->account;
                $update->ben_ac_ifsc = $request->ifsc;
                $update->closing_balance = $wallet->balanceFloat;
                $update->utr = 0;
                $update->status = 0;
                $update->status = 1;
                $update->utr = 0;
                $update->wallets_uuid = $txn_wl;
                $update->save();
                        
                        
                return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$check_benf->account_holder_name]);
            }else{
            
                $txnid = $this->UserAuth->txnId('BCV');
                $post = '{
                    "account_number": "'.$request->account.'",
                    "ifsc": "'.$request->ifsc.'",
                    "reference_id": "'.$txnid.'"
                }';
                $data = $this->BulkpeController->bankVerification($post);
                $json = json_decode($data);
                
                if($json->statusCode == 200 && $json->data->status == 'SUCCESS'){
                    // sleep(rand(1,7));
                    
                    // accountverify:
                    $userwallet = User::find($user->id);
                    $wallet = $userwallet->wallet;
                    $totalamount = 4;
                    // $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
                    // if($balance_check->balance != $userwallet->wallet->balance){
                    //     Log::channel("balancemissmatch")->info("========");
                    //     Log::channel("balancemissmatch")->info($wallet->id);
                    //     Log::channel("balancemissmatch")->info($balance_check->balance);
                    //     Log::channel("balancemissmatch")->info($userwallet->wallet->balance);
                    //     goto accountverify;
                    // }
                    
                    $txn_wl = $this->WalletCalculation->walletWithdrawFloat($user->id,$totalamount,'Account Verification','Debit_Retailer_'.$user->mobile.'_Account_Verification_Fee_'.$request->account,$txnid);
                    // $txn_wl = $wallet->withdrawFloat($totalamount,[
                    //     'meta' => [
                    //         'Title' => 'Account Verification',
                    //         'detail' => 'Debit_Retailer_'.$user->mobile.'_Account_Verification_Fee_'.$request->account,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = new transactions_dmt();
                    $update->user_id = $user->id;
                    $update->event = 'ACCOUNTVERIFY';
                    $update->transaction_id = $txnid;
                    $update->amount = $totalamount;
                    $update->fee = 0;
                    $update->tds = 0;
                    $update->gst = 0;
                    $update->ben_name = $json->data->account_holder_name;
                    $update->ben_ac_number = $request->account;
                    $update->ben_ac_ifsc = $request->ifsc;
                    $update->closing_balance = $wallet->balanceFloat;
                    $update->utr = 0;
                    $update->status = 0;
                    $update->status = 1;
                    $update->utr = 0;
                    $update->wallets_uuid = $txn_wl;
                    $update->save();
                    
                    $add_benf = new beneficiaries();
                    $add_benf->account_holder_name = $json->data->account_holder_name;
                    $add_benf->account_number = $request->account;
                    $add_benf->ifsc = $request->ifsc;
                    $add_benf->is_verify = 1;
                    $add_benf->save();
                        
                        
                    return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$json->data->account_holder_name]);
                }else{
                    return response()->json(['success' => false, 'message' => 'Try again']);
                }
            }
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    public function panVerification(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "pan" => "required",
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
            $userwallet = User::find($user->id);
            $balance = $userwallet->wallet->balanceFloat;
            $amount = 6;
            // print_r($balance);exit;
            if($balance < $amount){
                return response()->json(['success' => false, 'message' => 'Insufficient balance']);
            }
            
            $check_benf = PanDetail::where('pan',$request->account)->where('ifsc',$request->ifsc)->first();
            if($check_benf){
                $txnid = $this->UserAuth->txnId('BCV');
                $userwallet = User::find($user->id);
                $wallet = $userwallet->wallet;
                $totalamount = 6;
                $txn_wl = $this->WalletCalculation->walletWithdrawFloat($user->id,$totalamount,'Account Verification','Debit_Retailer_'.$user->mobile.'_Account_Verification_Fee_'.$request->account,$txnid);
                
                $update = new transactions_dmt();
                $update->user_id = $user->id;
                $update->event = 'PANVERIFY';
                $update->transaction_id = $txnid;
                $update->amount = $totalamount;
                $update->fee = 0;
                $update->tds = 0;
                $update->gst = 0;
                $update->ben_name = $check_benf->account_holder_name;
                $update->ben_ac_number = $request->account;
                $update->ben_ac_ifsc = $request->ifsc;
                $update->closing_balance = $wallet->balanceFloat;
                $update->utr = 0;
                $update->status = 0;
                $update->status = 1;
                $update->utr = 0;
                $update->wallets_uuid = $txn_wl;
                $update->save();
                        
                return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$check_benf->full_name]);
            }else{
            
                $txnid = $this->UserAuth->txnId('BCV');
                $post = '{
                    "account_number": "'.$request->account.'",
                    "ifsc": "'.$request->ifsc.'",
                    "reference_id": "'.$txnid.'"
                }';
                $data = $this->BulkpeController->bankVerification($post);
                $json = json_decode($data);
                
                if($json->statusCode == 200 && $json->data->status == 'SUCCESS'){
                    // sleep(rand(1,7));
                    
                    // accountverify:
                    $userwallet = User::find($user->id);
                    $wallet = $userwallet->wallet;
                    $totalamount = 6;
                    
                    $txn_wl = $this->WalletCalculation->walletWithdrawFloat($user->id,$totalamount,'Account Verification','Debit_Retailer_'.$user->mobile.'_Account_Verification_Fee_'.$request->account,$txnid);
                    
                    $update = new transactions_dmt();
                    $update->user_id = $user->id;
                    $update->event = 'ACCOUNTVERIFY';
                    $update->transaction_id = $txnid;
                    $update->amount = $totalamount;
                    $update->fee = 0;
                    $update->tds = 0;
                    $update->gst = 0;
                    $update->ben_name = $json->data->name;
                    $update->ben_ac_number = $request->pan;
                    $update->closing_balance = $wallet->balanceFloat;
                    $update->utr = 0;
                    $update->status = 0;
                    $update->status = 1;
                    $update->utr = 0;
                    $update->wallets_uuid = $txn_wl;
                    $update->save();
                    
                    $profile = new PanDetail();
                    $profile->first_name = $json->data->firstName;
                    $profile->middle_name = $json->data->middleName ?? '';
                    $profile->last_name = $json->data->lastName;
                    $profile->full_name = $json->data->name;
                    $profile->gender = $json->data->gender;
                    $profile->dob = (int)$json->data->dob;
                    $profile->email = $json->data->email ?? '';
                    $profile->phone = $json->data->phone ?? '';
                    $profile->building_name = $json->data->buildingName ?? '';
                    $profile->street_name = $json->data->streetName ?? '';
                    $profile->locality = $json->data->locality ?? '';
                    $profile->city = $json->data->city;
                    $profile->state = $json->data->state;
                    $profile->pin_code = $json->data->pinCode;
                    $profile->masked_aadhaar = $json->data->maskedAadhaar;
                    $profile->aadhaar_linked = $json->data->aadhaarLinked ?? false;
                    $profile->pan = $json->data->pan;
                    $profile->pan_type = $json->data->panType ?? 'Person';
                    $profile->is_pan_valid = $json->data->isPanValid ?? false;
                    $profile->save();
                        
                        
                    return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$json->data->name]);
                }else{
                    return response()->json(['success' => false, 'message' => 'Try again']);
                }
            }
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    public function getBanks(Request $request){
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
        
        $data = banks::orderBy('name','ASC')->get();
        
        return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
    }
    
    public function doTransactionsUpi(Request $request){
        return response()->json(['success' => false, 'message' => 'Failed']);
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "upi_id" => "required",
            "beneficiary_name" => "required|max:100",
            "amount" => "required",
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
        
        $shop = shop_details::where('user_id',$user->id)->first();
        if($shop->latitude == null || $shop->longitude == null){
            return response()->json(['success' => false, 'message' => "Please Insert Shop Detail."]);
        }
        
        $distance = $this->UserAuth->geteDistance($shop->latitude, $shop->longitude, $request->latitude, $request->longitude);
        log::channel("location")->info("Retailer");
        log::channel("location")->info($request->latitude."/".$request->longitude);
        log::channel("location")->info("SHOP");
        log::channel("location")->info($shop->latitude."/".$shop->longitude);
        log::channel("location")->info("KM");
        log::channel("location")->info($distance);
        if($distance > 25){
            return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
        }
        
        $amount = $request->amount;
        $transfer_type = 'UPI';
        
        if($amount >= 1 && $amount <= 1000){
            $fee = 10;
        }else{
            $fee = $amount * 1/100;
        }
        
        $totalamount = $amount + $fee;
        $gst = $fee - ($fee / 1.18);
        sleep(rand(1,6));
        
        if($user){
            $userwallet = User::find($user->id);
            $balance = $userwallet->wallet->balanceFloat;
            // print_r($balance);exit;
            if($balance < $totalamount){
                return response()->json(['success' => false, 'message' => 'Insufficient balance']);
            }
            
            $recentTime = Carbon::now()->subMinutes(10);
            
            $duplicate = transactions_dmt::where('user_id', $user->id)
                            ->where('ben_ac_number', $request->upi_id)
                            ->where('amount', $amount)
                            ->where('created_at', '>=', $recentTime)
                            ->exists();
            if ($duplicate) {
                log::info('Duplicate');
                log::info($request->all());
                return response()->json(['success' => false, 'message' => 'Duplicate! By Mistake Double Click or Duplicat Transaction. Please Check you report.']);
            }
            
            // $wallet = Wallet::findOrNew($user);
            $wallet = $userwallet->wallet;
            $txnid = $this->UserAuth->txnId('UPI');
            
            $txn_wl_fee = $this->WalletCalculation->walletWithdrawFloat($user->id,$fee,'Scan And Pay Fee','Debit_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_CustomerFee_'.$request->upi_id,$txnid);
            // $txn_wl_fee = $wallet->withdrawFloat($fee,[
            //     'meta' => [
            //         'Title' => 'Scan And Pay Fee',
            //         'detail' => 'Debit_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_CustomerFee_'.$request->upi_id,
            //         'transaction_id' => $txnid,
            //     ]
            // ]);
            // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
            
            $txn_wl = $this->WalletCalculation->walletWithdrawFloat($user->id,$amount,'Scan And Pay','Debit_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_Amount_'.$request->upi_id,$txnid);
            // $txn_wl = $wallet->withdrawFloat($amount,[
            //     'meta' => [
            //         'Title' => 'Scan And Pay',
            //         'detail' => 'Debit_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_Amount_'.$request->upi_id,
            //         'transaction_id' => $txnid,
            //     ]
            // ]);
            // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
            
            
            $ins = new transactions_dmt();
            $ins->user_id = $user->id;
            $ins->event = 'SCANNPAY';
            $ins->transaction_id = $txnid;
            $ins->amount = $amount;
            $ins->mobile = $request->user_mobile;
            $ins->transfer_type = $transfer_type;
            $ins->fee = $fee;
            $ins->tds = 0;
            $ins->gst = $gst;
            $ins->closing_balance = $wallet->balanceFloat;
            $ins->ben_name = $request->beneficiary_name;
            $ins->ben_ac_number = $request->upi_id;
            $ins->ben_ac_ifsc = 0;
            $ins->utr = 0;
            $ins->status = 0;
            $ins->wallets_uuid = $txn_wl;
            $ins->dmt_beneficiary_id = 0;
            $ins->save();
            
            $check_upi_in_bene = dmt_upi_beneficiaries::where('user_id',$user->id)->where('account_number',$request->upi_id)->first();
            if(!$check_upi_in_bene){
                $ins_ben = new dmt_upi_beneficiaries();
                $ins_ben->user_id = $user->id;
                $ins_ben->account_number = $request->upi_id;
                $ins_ben->account_holder_name = $request->beneficiary_name;
                $ins_ben->save();
            }
            
            $api = 'CYRUS';
            if($api == 'CYRUS'){
                $api_params = array('MerchantID' => 'AP487545',
                                'MerchantKey' => 'cIs8S3~Vg2Mtmn6',//rRm8!GGoOC7Zp87
                                'MethodName' => 'paytransfer',
                                'orderId' => $txnid,
                                'vpa' => $request->upi_id,
                                'Name' => $request->beneficiary_name,
                                'amount' => $amount,
                                'MobileNo' => $user->mobile,
                                'TransferType' => 'UPI');
            
                $result = $this->CyrusController->payoutTransaction($api_params);
                
                
                $json = json_decode($result);
                if($json->statuscode == "DE_001" || $json->statuscode == "DE_101"){
                    if($json->statuscode == "DE_001"){
                        //success
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 1;
                        $update->response_reason = $json->status;
                        $update->utr = $json->data->rrn;
                        $update->save();
                        // $this->WalletCalculation->distributorUpi($txnid);
                        // $this->WalletCalculation->retailorUpi($txnid);
                        // $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txnid on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                        // $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->user_mobile,$text,1207172205377529606);
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        return response()->json(['success' => true, 'message' => 'Transaction Done.', 'data'=>$data]);
                        
                    }elseif($json->statuscode == "DE_101"){
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    }
                    }else{
                        //Refund Proccess
                        $txnid_ref = $this->UserAuth->txnId('REF');
                        
                        $txn_wl_fee = $wallet->depositFloat($fee,[
                            'meta' => [
                                'Title' => 'Scan And Pay Fee',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_CustomerFee_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        $txn_wl = $wallet->depositFloat($amount,[
                            'meta' => [
                                'Title' => 'Scan And Pay',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_Amount_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->utr = 0;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                    
                    }
            }elseif($api == 'BULKPE'){
            $api_params = '{
                        "amount": "'.$amount.'",
                        "payment_mode": "UPI",
                        "reference_id": "'.$txnid.'",
                        "transcation_note": "",
                        "beneficiaryName": "'.$request->beneficiary_name.'",
                        "upi": "'.$request->upi_id.'",
                        "account_number": "",
                        "ifsc": ""
                    }';
            
            $result = $this->BulkpeController->payoutTransaction($api_params);
            
            
            $json = json_decode($result);
            if(!isset($json->error)){
                if($json->data->status == "SUCCESS"){
                    //success
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 1;
                    $update->response_reason = $json->data->payment_remark;
                    $update->utr = $json->data->utr;
                    $update->save();
                    // $this->WalletCalculation->distributorUpi($txnid);
                    // $this->WalletCalculation->retailorUpi($txnid);
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    
                }elseif($json->data->status == "PENDING"){
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                }else{
                    //Refund Proccess
                        
                        $txn_wl_fee = $wallet->depositFloat($fee,[
                            'meta' => [
                                'Title' => 'Scan And Pay Fee',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_CustomerFee_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        $txn_wl = $wallet->depositFloat($amount,[
                            'meta' => [
                                'Title' => 'Scan And Pay',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_Amount_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->utr = 0;
                        $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                }
                
            }else{
                    //Refund Proccess
                        
                        $txn_wl_fee = $wallet->depositFloat($fee,[
                            'meta' => [
                                'Title' => 'Scan And Pay Fee',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_CustomerFee_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        $txn_wl = $wallet->depositFloat($amount,[
                            'meta' => [
                                'Title' => 'Scan And Pay',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_Amount_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->utr = 0;
                        $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
            }
            
            }else{
                $initiator_id = env('EKO_AEPS_INITIATOR_ID');
                $post = [
                    "initiator_id" => $initiator_id,
                    "customer_id" => $initiator_id,
                    "amount" => $amount,
                    "client_ref_id" => $txnid,
                    "recipient_name" => $request->beneficiary_name,
                    "customer_vpa" => $request->upi_id
                ];
                $api_params = http_build_query($post, '', '&');
                $result = $this->EkoController->payoutTransactionUpi($api_params,'198481007');
                
                
                $json = json_decode($result);
                if($json->status == 0){
                    if($json->data->tx_status == 0){
                        //success
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 1;
                        $update->utr = $json->data->tid;
                        $update->save();
                        // $this->WalletCalculation->distributorUpi($txnid);
                        // $this->WalletCalculation->retailorUpi($txnid);
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                        
                    }elseif($json->data->tx_status == 2){
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    }elseif($json->data->tx_status == 5){
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 5;
                        $update->utr = $json->data->tid;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    }else{
                        
                        $txn_wl_fee = $wallet->depositFloat($fee,[
                            'meta' => [
                                'Title' => 'Scan And Pay Fee',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_CustomerFee_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        $txn_wl = $wallet->depositFloat($amount,[
                            'meta' => [
                                'Title' => 'Scan And Pay',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_Amount_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->utr = 0;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                    }
                    
                }else{
                        //Refund Proccess
                        
                        $txn_wl_fee = $wallet->depositFloat($fee,[
                            'meta' => [
                                'Title' => 'Scan And Pay Fee',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_CustomerFee_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        $txn_wl = $wallet->depositFloat($amount,[
                            'meta' => [
                                'Title' => 'Scan And Pay',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_Amount_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->utr = 0;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                }
            }
            
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    public function getFundBanks(Request $request){
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
        $user = User::where('mobile',$request->user_mobile)->first();
        
       
        
        $data = fund_banks::where('user_id',1)->get();
        
        return response()->json(['success' => true, 'message' => 'banks.', 'data'=>$data]);
    }
    
    public function listMoneyRequest(Request $request){
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
        
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $data = fund_requests::where('user_id',$user->id)->whereBetween('fund_requests.created_at', array($from, $to))->get();
        
        return response()->json(['success' => true, 'message' => 'banks.', 'data'=>$data]);
    }
    
    public function pgListMoneyRequest(Request $request){
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
        
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $data = fund_onlines::where('user_id',$user->id)->whereBetween('fund_onlines.created_at', array($from, $to))->whereIn('pg_id',[1,2])->get();
        
        return response()->json(['success' => true, 'message' => 'banks.', 'data'=>$data]);
    }
    
    public function qrPaymentReport(Request $request){
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
        
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $data = fund_onlines::where('user_id',$user->id)->whereBetween('fund_onlines.created_at', array($from, $to))->whereIn('pg_id',[3])->get();
        
        return response()->json(['success' => true, 'message' => 'banks.', 'data'=>$data]);
    }
    
    public function createMoneyRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "amount" => "required",
            "transfer_type" => "required",
            "bank_id" => "exists:fund_banks,id",
            "deposit_date" => "required",
            "img" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {   
            $amount = $request->get("amount");
            $transfer_type = $request->get("transfer_type");
            $bank_id = $request->get("bank_id");
            $bank_ref = $request->get("bank_ref");
            $deposit_date = $request->get("deposit_date");
            $remark = $request->get("remark");
            
            $user = User::where('mobile',$request->user_mobile)->first();
            $user_id = $user->id;
            
            if($amount > 0) {
                
                
                if($transfer_type != 'Cash'){
                    
                    $check_entry = fund_requests::where('bank_id',$bank_id)->where('bank_ref',$bank_ref)->whereIn('status',[1,0])->first();
                    if($check_entry) {
                        return response()->json(['success' => false, 'message' => 'You can not request with same bank and refrence id!']);
                    }
                }else{
                    $transfer_type = 'Bank';
                }
                
                $txn = $this->UserAuth->txnId("MR");
                $imagename = '';
                
                if ($request->input('img')) {
                    // $file = $request->file('img');
                    // $destinationPath = public_path('/uploads/money_request/');
                    // $imagename = 'IMG'. $user_id . time() . '.' . $file->getClientOriginalExtension();
                    // $file->move($destinationPath, $imagename);
                    
                    $image = $request->input('img');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $imagename = 'IMG' . $user_id . time() . '.jpg';
                    $filePath = $imagename;
                    Storage::disk('public_img')->put($filePath, base64_decode($image));
                }
                
                $money_request = new fund_requests();
                $money_request->user_id = $user_id;
                $money_request->transaction_id = $txn;
                $money_request->amount = $amount;
                $money_request->transfer_type = $transfer_type;
                $money_request->bank_id = $bank_id;
                $money_request->bank_ref = $bank_ref;
                $money_request->deposit_date = $deposit_date;
                $money_request->remark = $remark;
                $money_request->status = 0;
                $money_request->img = $imagename;
                $money_request->save();
                    
                if($money_request)
                    return response()->json(['success' => true, 'message' => 'Money request sent successfully','data'=>$money_request]);
                else
                    return response()->json(['success' => false, 'message' => 'Money request not sent!']);
            }
            else{
                return response()->json(['success' => false,'message' => 'Please enter valid amount!']);
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function phonepePGData(Request $request) {
        
        $user = User::where('mobile',$request->user_mobile)->first();
        $user_id = $user->id;
        $amount = $request->amount;    
        $transactionId = $this->UserAuth->txnId('PHN');
        $ins = new fund_onlines();
        $ins->user_id = $user_id;
        $ins->transaction_id = $transactionId;
        $ins->amount = $amount;
        $ins->save();
        
        return response()->json(['success' => true,'message' => 'AEPS Keys data', 
            'merchantId' => 'M22WE8KIMIHEL',
            'merchantTransactionId' => $transactionId,
            'merchantUserId' => 'MUID123',
            'developer_key' => '4ac75d00-2767-486d-8f38-69cec9af394d',
            'callbackUrl' => 'https://user.payritepayment.in/api/v1/callback/phonepe',
            'environment' => "production",
            'host_url' => "https://api.phonepe.com/apis/hermes",
            'transaction_id' => $transactionId,
            
            ]);
    }
    
    public function callbackPhonePe(Request $request) {
        // $phonepe = new LaravelPhonePe();
        //  $response = $phonepe->getTransactionStatus($request->all());
        //  if($response == true){
        //     //Payment Success
        //  }
        //  else
        //  {
        //     //Payment Failed           
        //  }
         
        $payload = $request->all();
        $callbackResponse = file_get_contents('php://input');
        
        // Log::info($callbackResponse);
        
        $payload_decode = json_decode($callbackResponse, true);
        // Log::info($payload_decode);
        // Step 1: Decode the base64-encoded string
        $jsonString = base64_decode($payload_decode['response']);
        
        // Step 2: Decode the JSON string into a PHP associative array
        $responseArray = json_decode($jsonString, true);
        Log::info('Received PhonePe callback Decode:');
        Log::info($responseArray);
        // Check for JSON decoding errors
        if($responseArray['success']){
            if($responseArray['data']['state'] == 'COMPLETED'){
                $transaction_id = $responseArray['data']['merchantTransactionId'];
                $find = fund_onlines::where('transaction_id',$transaction_id)->where('status',0)->first();
                if($find){
                    $userwallet = User::find($find->user_id);
                    $wallet = $userwallet->wallet;
                    $fee = 1.45;
                    $amount_main = $find->amount;
                    $fee_val = $amount_main * $fee / 100;
                    $amount = $amount_main - $fee_val;
                    
                    $txn_wl = $wallet->depositFloat($amount_main,[
                        'meta' => [
                            'Title' => 'Online Load Phonepe',
                            'detail' => 'Credit_Phonepe_'.$responseArray['data']['transactionId'].'_Amount_'.$amount_main,
                            'transaction_id' => $transaction_id,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    $find->amount_txn = $amount;
                    $find->fee = $fee_val;
                    $find->ref_id = $responseArray['data']['transactionId'];
                    $find->closing_balance = $wallet->balanceFloat;
                    $find->status = 1;
                    $find->pg_id = 1;
                    $find->wallets_uuid = $txn_wl->uuid;
                    $find->save();
                    
                    $transactionId_fee = $this->UserAuth->txnId('FEE');
                    
                    $txn_wl_fee = $this->WalletCalculation->walletWithdrawFloat($find->user_id,$fee_val,'Online Load Phonepe Fee','Debit_Phonepe_'.$responseArray['data']['transactionId'].'_Fee_'.$fee_val,$transactionId_fee);
                    // $txn_wl_fee = $wallet->withdrawFloat($fee_val,[
                    //     'meta' => [
                    //         'Title' => 'Online Load Phonepe Fee',
                    //         'detail' => 'Debit_Phonepe_'.$responseArray['data']['transactionId'].'_Fee_'.$fee_val,
                    //         'transaction_id' => $transactionId_fee,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    // $ins = new fund_onlines();
                    // $ins->user_id = $find->user_id;
                    // $ins->transaction_id = $transactionId_fee;
                    // $ins->amount = $amount;
                    // $ins->ref_id = $transaction_id;
                    // $ins->closing_balance = $wallet->balanceFloat;
                    // $ins->status = 1;
                    // $ins->pg_id = 1;
                    // $ins->wallets_uuid = $txn_wl_fee->uuid;
                    // $ins->save();
                    
                    
                }else{
                    Log::info('Transaction id not found phonepe:', $transaction_id);
                }
                
            }else{
                Log::info('Transaction failed phonepe:');
            }
            
        }else{
            Log::info('Transaction Wrong phonepe:');
        }
        return response()->json(['message' => 'Callback received successfully'], 200);
    }
    
    public function callbackBulkpeCredit(Request $request) {
        $payload = $request->all();
        Log::channel('bulkpeapi')->info("CALLBACK Credit");
        Log::channel('bulkpeapi')->info($payload);
    }
    
    public function callbackBulkpe(Request $request) {
        $payload = $request->all();
        Log::channel('bulkpeapi')->info("CALLBACK");
        Log::channel('bulkpeapi')->info($payload);
        
        $status = $payload['data']['trx_status'];
        
        if($status == "SUCCESS") {
            $txn_id = $payload['data']['reference_id'];
            $utr = $payload['data']['utr'];
            
            $transaction = transactions_dmt::where('transaction_id',$txn_id)->where('status',0)->first();
            
            if($transaction){
                $amount = $transaction->amount;
                $mobile = $transaction->mobile;
                $transaction->status = 1;
                $transaction->utr = $utr;
                $transaction->save();
                // $this->WalletCalculation->distributorDmt($txn_id);
                // $this->WalletCalculation->retailorDmt($txn_id);
                $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txn_id on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                    $api_response = $this->ApiCalls->smsgatewayhubGetCall($mobile,$text,1207172205377529606);
            }
            return response()->json(['success' => true, 'message' => 'success']);
        }
        
        if($status == "FAILED") {
            $txn_id = $payload['data']['reference_id'];
            $utr = $payload['data']['utr'];
            if(isset($payload['data']['trx_message'])) 
                $response_reason = $payload['data']['trx_message'];
            else
                $response_reason = '';
            
            $transaction = transactions_dmt::where('transaction_id',$txn_id)->where('status',0)->first();
            
            if($transaction){
                $amount = $transaction->amount;
                $fee = $transaction->fee;
                $userwallet = User::find($transaction->user_id);
                $wallet = $userwallet->wallet;
                $transfer_type = $transaction->transfer_type;
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$transaction->mobile,$txn_id);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$amount,"Money Transfer",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$transaction->mobile,$txn_id);
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$transaction->mobile,
                    //         'transaction_id' => $txn_id,
                    //     ]
                    // ]);
                    // $balance_update_fee = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$transaction->mobile,
                    //         'transaction_id' => $txn_id,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $transaction->status = 2;
                    $transaction->utr = $utr;
                    $transaction->response_reason = $response_reason;
                    $transaction->save();
            }
        }
    }
    
    public function callbackEkoPayout(Request $request) {
        
        $body = file_get_contents('php://input');
        
        Log::channel('ekoapi')->info("CALLBACK RESPONSE");
        Log::channel('ekoapi')->info($body);
        
        $payload = json_decode($body);
        
        $status = $payload->tx_status;
        
        if($status == "0") {
            $txn_id = $payload->client_ref_id;
            $utr = $payload->tid;
            
            $transaction = transactions_dmt::where('transaction_id',$txn_id)->where('status',0)->first();
            
            if($transaction){
                $amount = $transaction->amount;
                $mobile = $transaction->mobile;
                $transaction->status = 1;
                $transaction->utr = $utr;
                $transaction->save();
                // $this->WalletCalculation->distributorDmt($txn_id);
                // $this->WalletCalculation->retailorDmt($txn_id);
                $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txn_id on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                    $api_response = $this->ApiCalls->smsgatewayhubGetCall($mobile,$text,1207172205377529606);
            }
            return response()->json(['success' => true, 'message' => 'success']);
        }
        
        if($status == "1" || $status == "4") {
            $txn_id = $payload->client_ref_id;
            $utr = $payload->tid;
            if(isset($payload->txstatus_desc)) 
                $response_reason = $payload->txstatus_desc;
            else
                $response_reason = '';
            
            $transaction = transactions_dmt::where('transaction_id',$txn_id)->where('status',0)->first();
            
            if($transaction){
                $amount = $transaction->amount;
                $fee = $transaction->fee;
                $userwallet = User::find($transaction->user_id);
                $wallet = $userwallet->wallet;
                $transfer_type = $transaction->transfer_type;
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$transaction->mobile,$txn_id);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$amount,"Money Transfer",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$transaction->mobile,$txn_id);
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$transaction->mobile,
                    //         'transaction_id' => $txn_id,
                    //     ]
                    // ]);
                    // $balance_update_fee = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$transaction->mobile,
                    //         'transaction_id' => $txn_id,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $transaction->status = 2;
                    $transaction->utr = $utr;
                    $transaction->response_reason = $response_reason;
                    $transaction->save();
            }
        }
    }
    
    public function callbackEkoCms(Request $request) {
        
        $body = file_get_contents('php://input');
        
        Log::channel('ekoapi')->info("CALLBACK RESPONSE");
        Log::channel('ekoapi')->info($body);
    }
    
    public function fcmtesting(Request $request) {
        $api_response = $this->ApiCalls->sendfcmNotification(3,"Payrite","Testing");
        
    }
    
    public function getStateOfCyrusState(Request $request) {
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $api_params = array('MerchantID' => 'AP487545',
                                'MerchantKey' => 'J9BvBJnUM377177YnJG8fpD+GoWcFAtwfzkV0abuFFg=',
                                'MethodName' => 'AEPS3GETSTATE');
            
                $result = $this->CyrusController->getState($api_params);
                
                $data = json_decode($result);
                return response()->json(['success' => true, 'message' => '', 'data'=>$data]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function getBankOfCyrus(Request $request) {
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $api_params = array('MerchantID' => 'AP487545',
                                'MerchantKey' => 'J9BvBJnUM377177YnJG8fpD+GoWcFAtwfzkV0abuFFg=',
                                'MethodName' => 'AEPS3GETBANK');
            
                $result = $this->CyrusController->getBank($api_params);
                
                print_r($result);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function registrationCyrusAeps(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "state" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $addresses = Addresses::join('cities','cities.id','addresses.city_id')->where('user_id',$user->id)->first();
            $shop = shop_details::where('user_id',$user->id)->first();
            $kyc = kyc_docs::where('user_id',$user->id)->first();
            $user_id = $user->id;
            
            $api_params = '{
                        "MerchantID": "AP487545",
                        "MerchantKey": "J9BvBJnUM377177YnJG8fpD+GoWcFAtwfzkV0abuFFg=",
                        "uniqueid": "'.$user->user_id.'",
                        "uniquepin": "12345678",
                        "MethodName": "registration",
                        "Mobile": "'.$user->mobile.'",
                        "Email": "'.$user->mobile.'",
                        "Company": "'.$shop->shop_name.'",
                        "Name": "'.$user->name.' '.$user->surname.'",
                        "Pan": "'.$kyc->pan_number.'",
                        "Pincode": "'.$addresses->pincode.'",
                        "Address": "'.$shop->shop_address.'",
                        "Aadhar": "'.$kyc->aadhaar_number.'",
                        "state": "'.$request->state.'",
                        "city": "'.$addresses->name.'",
                        "latitude":"'.$shop->latitude.'", 
                        "longitude":"'.$shop->longitude.'"
                    }';
            
                $result = $this->CyrusController->registrationAeps($api_params);
                
                // print_r($result);
                $data = json_decode($result);
                if($data->statusCode == 10000){
                    $service = eko_services::where('user_id',$user->id)->first();
                    if($service){
                        $service->cyrus_code = $data->data->primaryKeyId;
                        $service->cyrus_FPTxnId = $data->data->encodeFPTxnId;
                        $service->cyrus_aeps = 2;
                        $service->save();
                    }else{
                        $ins = new eko_services();
                        $ins->user_id = $user_id;
                        $ins->cyrus_code = $data->data->primaryKeyId;
                        $ins->cyrus_FPTxnId = $data->data->encodeFPTxnId;
                        $ins->cyrus_aeps = 2;
                        $ins->save();
                        
                    }
                    
                    return response()->json(['success' => true, 'message' => 'OTP Send On Your Number.']);
                }else{
                    return response()->json(['success' => false, 'message' => $data->message]);
                }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function otpVerifyCyrusAeps(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "otp" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $service = eko_services::where('user_id',$user->id)->first();
            $user_id = $user->id;
            
            $api_params = '{
                            "MerchantID": "AP487545",
                            "MerchantKey": "J9BvBJnUM377177YnJG8fpD+GoWcFAtwfzkV0abuFFg=",
                            "merchantLoginId": "'.$user->user_id.'",
                            "OTP": "'.$request->otp.'",
                            "MethodName": "submitotp",
                            "primaryKeyId": "'.$service->cyrus_code.'",
                            "encodeFPTxnId": "'.$service->cyrus_FPTxnId.'"
                        }';
            
                $result = $this->CyrusController->registrationAeps($api_params);
                
                
                $data = json_decode($result);
                if($data->statusCode == 10000){
                    $service = eko_services::where('user_id',$user->id)->first();
                    if($service){
                        $service->cyrus_aeps = 1;
                        $service->save();
                    }
                    
                    return response()->json(['success' => true, 'message' => 'AEPS Registration Done.']);
                }else{
                    return response()->json(['success' => false, 'message' => $data->message]);
                }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function getCyrusAeps(Request $request) {
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $service = eko_services::where('user_id',$user->id)->first();
            $user_id = $user->id;
            
            $api_params = array('MerchantID' => 'AP487545',
                                'MerchantKey' => 'J9BvBJnUM377177YnJG8fpD+GoWcFAtwfzkV0abuFFg=',
                                'MethodName' => 'GETURL',
                                'OutLetID' => $user->user_id);
            
                $result = $this->CyrusController->getBank($api_params);
                
                
                $data = json_decode($result);
                if($data->statuscode == 10000){
                    
                    
                    return response()->json(['success' => true, 'message' => 'AEPS Registration Done.']);
                }else{
                    return response()->json(['success' => false, 'message' => $data->status]);
                }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function dailyKycCyrusAeps(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "device_data" => "required",
            "aadhar" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $service = eko_services::where('user_id',$user->id)->first();
            $user_id = $user->id;
            
            $api_params = '{
                            "MerchantID": "AP487545",
                            "MerchantKey": "J9BvBJnUM377177YnJG8fpD+GoWcFAtwfzkV0abuFFg=",
                            "merchantLoginId": "'.$user->user_id.'",
                            "MethodName": "biomatrickyc",
                            "primaryKeyId": "'.$service->cyrus_code.'",
                            "encodeFPTxnId": "'.$service->cyrus_FPTxnId.'",
                            "captureResponse":'.$request->device_data.',
                            "cardnumberORUID": {
                                "adhaarNumber": "'.$request->aadhar.'",
                                "indicatorforUID": 0,
                                "nationalBankIdentificationNumber": "508534"
                            },
                            "requestRemarks": "6464",
                            "superMerchantId": 0
                        }';
            
                $result = $this->CyrusController->dailyKYC($api_params);
                
                
                $data = json_decode($result);
                if($data->statusCode == 10000){
                    $service = eko_services::where('user_id',$user->id)->first();
                    if($service){
                        $service->cyrus_aeps_daily = 1;
                        $service->save();
                    }
                    
                    return response()->json(['success' => true, 'message' => 'AEPS Daily KYC Complete.']);
                }else{
                    return response()->json(['success' => false, 'message' => $data->message]);
                }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function dmtTransactionStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "transaction_id" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $transaction_id = $request->transaction_id;
            $data = transactions_dmt::select('status')->where('transaction_id',$transaction_id)->first();
            
            return response()->json(['success' => true, 'message' => '', 'status'=>$data->status]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function ekoDmtCustomerLogin(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $customer_mobile = $request->customer_mobile;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            
            $service = eko_services::where('user_id',$user->id)->first();
            $user_code = $service->eko_code;
            
            $post = [
                "mobile" => $customer_mobile,
                "user_code" => $user_code
            ];
            $result = $this->EkoController->dmtCustomerLogin($post);
            $decode = json_decode($result);
            if($decode->response_status_id == 1 && $decode->response_type_id == -1){
                return response()->json(['success' => false, 'message' => 'Customer does not exist in System', 'data'=>$decode]);
            }
            $available = $decode->data->available_limit;
            $dmt_use = $decode->data->used_limit;
            $dmt_limit = $decode->data->total_limit;
            $check_customer = $decode->data;
            
            
            return response()->json(['success' => true, 'message' => 'Customer Login','data'=>$check_customer,'is_reg'=>1,'available'=>$available,'used'=>$dmt_use,'limit'=>$dmt_limit]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function ekoDmtGetBenf(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $customer_mobile = $request->customer_mobile;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            $service = eko_services::where('user_id',$user->id)->first();
            $user_code = $service->eko_code;
            
            $post = [
                "mobile" => $customer_mobile,
                "user_code" => $user_code
            ];
            $result = $this->EkoController->dmtBenf($post);
            $decode = json_decode($result);
            
            return response()->json(['success' => true, 'message' => '', 'data'=>$decode]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function ekoDmtAddBenf(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required|exists:dmt_customers,mobile",
            "name" => "required",
            "account" => "required",
            "ifsc" => "required",
            "is_verify" => "required",
            "bank_id" => "required",
            "benf_mobile" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $customer_mobile = $request->customer_mobile;
            $name = $request->name;
            $bank_id = $request->bank_id;
            $ifsc = $request->ifsc;
            $account = $request->account;
            $benf_mobile = $request->benf_mobile;
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $user = User::where('mobile',$request->user_mobile)->first();
            $service = eko_services::where('user_id',$user->id)->first();
            $user_code = $service->eko_code;
            
            $post = [
                "mobile_number" => $customer_mobile,
                "user_code" => $user_code,
                "initiator_id" => $initiator_id,
                "recipient_mobile" => $benf_mobile,
                "bank_id" => $bank_id,
                "acc_ifsc" => $account."_".$ifsc,
                "recipient_name" => $name
            ];
            $result = $this->EkoController->dmtAddBenf($post);
            $decode = json_decode($result);
            
            return response()->json(['success' => true, 'message' => '', 'data'=>$decode]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function ekoDmtDoTransactions(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "beneficiary_id" => "required",
            "amount" => "required",
            "transfer_type" => "required",
            "latitude" => "required",
            "longitude" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
        
            $shop = shop_details::where('user_id',$user->id)->first();
            if($shop->latitude == null || $shop->longitude == null){
                return response()->json(['success' => false, 'message' => "Please Insert Shop Detail."]);
            }
            
            $distance = $this->UserAuth->geteDistance($shop->latitude, $shop->longitude, $request->latitude, $request->longitude);
            log::channel("location")->info("Retailer");
            log::channel("location")->info($request->latitude."/".$request->longitude);
            log::channel("location")->info("SHOP");
            log::channel("location")->info($shop->latitude."/".$shop->longitude);
            log::channel("location")->info("KM");
            log::channel("location")->info($distance);
            if($distance > 25){
                return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
            }
            
            $transfer_type = $request->transfer_type;
            $customer_mobile = $request->customer_mobile;
            $beneficiary_id = $request->beneficiary_id;
            $amount = $request->amount;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $user = User::where('mobile',$request->user_mobile)->first();
            $service = eko_services::where('user_id',$user->id)->first();
            $user_code = $service->eko_code;
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            
            if($amount >= 101 && $amount <= 1000){
                $fee = 10;
            }else{
                $fee = $amount * 1/100;
            }
            $totalamount = $amount + $fee;
            $gst = $fee - ($fee / 1.18);
            sleep(rand(1,6));
            
            $userwallet = User::find($user->id);
            $balance = $userwallet->wallet->balanceFloat;
            // print_r($balance);exit;
            if($balance < $totalamount){
                return response()->json(['success' => false, 'message' => 'Insufficient balance']);
            }
            $wallet = $userwallet->wallet;
            $recentTime = Carbon::now()->subMinutes(10);
            $duplicate = transactions_dmt::where('user_id', $user->id)
                            ->where('mobile', $customer_mobile)
                            ->where('eko_beneficiary_id', $beneficiary_id)
                            ->where('amount', $amount)
                            ->where('created_at', '>=', $recentTime)
                            ->exists();
            if ($duplicate) {
                log::info('Duplicate');
                log::info($request->all());
                return response()->json(['success' => false, 'message' => 'Duplicate! By Mistake Double Click or Duplicat Transaction. Please Check you report.']);
            }
            $txnid = $this->UserAuth->txnId('DMT');
            
            $txn_wl_fee = $this->WalletCalculation->walletWithdrawFloat($user->id,$fee,'Money Transfer Fee','Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnid);
            // $txn_wl_fee = $wallet->withdrawFloat($fee,[
            //     'meta' => [
            //         'Title' => 'Money Transfer Fee',
            //         'detail' => 'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
            //         'transaction_id' => $txnid,
            //     ]
            // ]);
            // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
            
            $txn_wl = $this->WalletCalculation->walletWithdrawFloat($user->id,$amount,'Money Transfer','Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnid);
            // $txn_wl = $wallet->withdrawFloat($amount,[
            //     'meta' => [
            //         'Title' => 'Money Transfer',
            //         'detail' => 'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
            //         'transaction_id' => $txnid,
            //     ]
            // ]);
            // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
            
            
            
            $ins = new transactions_dmt();
            $ins->user_id = $user->id;
            $ins->event = 'DMT';
            $ins->transaction_id = $txnid;
            $ins->amount = $amount;
            $ins->mobile = $request->customer_mobile;
            $ins->transfer_type = $transfer_type;
            $ins->fee = $fee;
            $ins->tds = 0;
            $ins->gst = $gst;
            $ins->closing_balance = $wallet->balanceFloat;
            $ins->ben_name = '';
            $ins->ben_ac_number = '';
            $ins->ben_ac_ifsc = '';
            $ins->utr = 0;
            $ins->status = 0;
            $ins->api_id = 1;
            $ins->wallets_uuid = $txn_wl;
            $ins->eko_beneficiary_id = $beneficiary_id;
            $ins->save();
            
            if ($duplicate) {
                return response()->json(['message' => 'Duplicate entry detected'], 409); // HTTP 409 Conflict
            }
            
            if($transfer_type == 'IMPS'){
                $payment_mode = 2;
            }else{
                $payment_mode = 1;
            }
            $txnid = $this->UserAuth->txnId('DMT');
            $post = [
                "initiator_id" => $initiator_id,
                "customer_id" => $customer_mobile,
                "recipient_id" => $beneficiary_id,
                "amount" => $amount,
                "client_ref_id" => $txnid,
                "latlong" => $latitude." ".$longitude,
                "channel" => $payment_mode,
                "state" => 1,
                "timestamp" => date('Y-m-d H:i:s'),
                "currency" => "INR",
                "user_code" => $user_code
            ];
            $api_params = http_build_query($post, '', '&');
            $result = $this->EkoController->dmtTransaction($api_params);
            $json = json_decode($result);
            if($json->response_status_id == 0){
                if($json->data->tx_status == "0"){
                    //success
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 1;
                    $update->eko_status = 0;
                    $update->utr = $json->data->tid;
                    $update->ben_name = $json->data->recipient_name;
                    $update->ben_ac_number = $json->data->account;
                    $update->bank_name = $json->data->bank;
                    $update->sender_name = $json->data->sender_name;
                    $update->save();
                    // $this->WalletCalculation->distributorDmt($txnid);
                    // $this->WalletCalculation->retailorDmt($txnid);
                    $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txnid on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                    $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->customer_mobile,$text,1207172205377529606);
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    return response()->json(['success' => true, 'message' => 'Transaction Success.', 'data'=>$data]);
                    
                }elseif($json->data->tx_status == "2" || $json->data->tx_status == "5"){
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->eko_status = $json->data->tx_status;
                    $update->utr = $json->data->tid;
                    $update->ben_name = $json->data->recipient_name;
                    $update->ben_ac_number = $json->data->account;
                    $update->bank_name = $json->data->bank;
                    $update->sender_name = $json->data->sender_name;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                }else{
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnid);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnid);
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->eko_status = $json->data->tx_status;
                    $update->utr = 0;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                }
            }else{
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnid);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnid);
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    //         'transaction_id' => $txnid,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->eko_status = 1;
                    $update->utr = 0;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function ekoDmtRefund(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "transaction_id" => "required|exists:transactions_dmts,transaction_id",
            "otp" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $transaction_id = $request->transaction_id;
            $otp = $request->otp;
            
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $user = User::where('mobile',$request->user_mobile)->first();
            $service = eko_services::where('user_id',$user->id)->first();
            $user_code = $service->eko_code;
            
            $data = transactions_dmt::where('transaction_id',$transaction_id)->first();
            $tid = $data->utr;
            $post = [
                "otp" => $otp,
                "user_code" => $user_code,
                "initiator_id" => $initiator_id,
                "transaction_id" => $tid,
                "state" => 1
            ];
            $result = $this->EkoController->dmtRefund($post);
            $decode = json_decode($result);
            
            return response()->json(['success' => true, 'message' => '', 'data'=>$decode]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function billDmtCustomerLogin(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "bank_channel" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        

        if($response)
        {
            $customer_mobile = $request->customer_mobile;
            $bank_id = $request->bank_channel;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            
            $post = [
                    "mobile" => $customer_mobile,
                    "bank_id" => $bank_id
            ];
            
            $result = $this->BillavenueController->DmtCustomerLogin($post);
            $decode = json_decode($result);
            
            if($decode->responseReason == 'Failure'){
                return response()->json(['success' => true, 'message' => 'Customer does not exist in System', 'data'=>$decode,'is_reg'=>0]);
            }
            $available = $decode->availableLimit;
            $dmt_use = $decode->usedLimit;
            $dmt_limit = $decode->totalLimit;
            $check_customer = $decode;
            
            $check_sender = dmt_customers::where('bank_id',$bank_id)->where('mobile',$customer_mobile)->first();
            if(!$check_sender){
                $ins_sender = new dmt_customers();
                $ins_sender->user_id = $user->id;
                $ins_sender->first_name = $decode->senderName;
                $ins_sender->mobile = $customer_mobile;
                $ins_sender->dmt_type = '2';
                $ins_sender->bank_id = $bank_id;
                $ins_sender->save();
                    
            }
            
            
            return response()->json(['success' => true, 'message' => 'Customer Login','data'=>$check_customer,'is_reg'=>1,'available'=>$available,'used'=>$dmt_use,'limit'=>$dmt_limit]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access! OR Update Your APP.']);
        }
    }
    
    public function billDmtCreateCustomer(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "mobile" => "required",
            "name" => "required|min:5",
            "pincode" => "required",
            "bank_channel" => "required",
            "aadhar" => "required_if:bank_channel,FINO",
            "piddata" => "required_if:bank_channel,FINO",
            "piddata_type" => "required_if:bank_channel,FINO",
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
        
        $mobile = $request->mobile;
        $name = $request->name;
        $pincode = $request->pincode;
        $piddata = $request->get('piddata');
        $aadhar = $request->get('aadhar');
        $piddata_type = $request->get('piddata_type');
        $bank_channel = $request->get('bank_channel');
        
        $name = preg_replace('/[\*\?\[\]\{\}\^\$\(\)\+\|,\.\/\\\\]/', '', $name);
        if($user){
            
            $post = [
                "mobile" => $mobile,
                "name" => $name,
                "pincode" => $pincode,
                "piddata" => base64_encode($piddata),
                "aadhar" => $aadhar,
                "biotype" => $piddata_type,
                "bank_id" => $bank_channel
            ];
            $result = $this->BillavenueController->DmtCreateCustomer($post);
            $decode = json_decode($result);
            if($decode->responseCode == '000'){
                return response()->json(['success' => true, 'message' => 'Otp Send','is_reg'=>0,'data'=>$decode]);
            }else{
                return response()->json(['success' => false, 'message' => $decode->errorInfo->error->errorMessage,'is_reg'=>0,'data'=>$decode]);
            }
            
            
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
        
    }
    
    public function billDmtCustomerVerify(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "mobile" => "required",
            "otp" => "required",
            "bank_channel" => "required",
            "aadhar" => "required_if:bank_channel,ARTL",
            "piddata" => "required_if:bank_channel,ARTL",
            "piddata_type" => "required_if:bank_channel,ARTL",
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
        
        $otp = $request->otp;
        $additional_reg_data = $request->additional_reg_data;
        $mobile = $request->mobile;
        $piddata = $request->get('piddata');
        $aadhar = $request->get('aadhar');
        $bank_channel = $request->get('bank_channel');
        
        if($user){
            if(empty($additional_reg_data)){
                $additional_reg_data = "NA";
            }
            
            $user_debit = $this->WalletCalculation->walletWithdrawFloat($user->id,10,"eKYC FEE","Debit_ekyc_fee_sender_$mobile",$aadhar);
            
            Log::info($user_debit);
            if($user_debit != 0){
                
            }else{
                return response()->json(['success' => false, 'message' => "Please Check With Admin"]);
            }
            
            $piddata_type = $request->get('piddata_type');
            
            $post = [
                "mobile" => $mobile,
                "otp" => $otp,
                "additional_reg_data" => $additional_reg_data,
                "piddata" => base64_encode($piddata),
                "aadhar" => $aadhar,
                "biotype" => $piddata_type,
                "bank_id" => $bank_channel,
            ];
            $result = $this->BillavenueController->DmtOtpCustomerVerify($post);
            $decode = json_decode($result);
            if($decode->responseCode == '000'){
                $post = [
                    "mobile" => $mobile,
                    "bank_id" => $bank_channel,
                ];
                $result = $this->BillavenueController->DmtCustomerLogin($post);
                $decode = json_decode($result);
                
                $available = $decode->availableLimit;
                $dmt_use = $decode->usedLimit;
                $dmt_limit = $decode->totalLimit;
                $check_customer = $decode;
                
                $check_sender = dmt_customers::where('bank_id',$bank_channel)->where('mobile',$mobile)->first();
                if(!$check_sender){
                    $ins_sender = new dmt_customers();
                    $ins_sender->user_id = $user->id;
                    $ins_sender->first_name = $decode->senderName;
                    $ins_sender->mobile = $mobile;
                    $ins_sender->dmt_type = '2';
                    $ins_sender->bank_id = $bank_channel;
                    $ins_sender->save();
                        
                }
                return response()->json(['success' => true, 'message' => 'Verify','is_reg'=>1,'data'=>$check_customer,'is_reg'=>1,'available'=>$available,'used'=>$dmt_use,'limit'=>$dmt_limit]);
            }else{
                $user_debit = $this->WalletCalculation->walletDepositFloat($user->id,10,"eKYC FEE","Refund_ekyc_fee_sender_$mobile",$aadhar);
            
                return response()->json(['success' => false, 'message' => $decode->errorInfo->error->errorMessage,'is_reg'=>0,'data'=>$decode]);
            }
            
            
        }else{
            
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
        
    }
    
    public function billDmtGetBenf(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "bank_channel" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $bank_channel = $request->get('bank_channel');
            $customer_mobile = $request->customer_mobile;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            
            $post = [
                "mobile" => $customer_mobile,
                "bank_id" => $bank_channel,
            ];
            $result = $this->BillavenueController->DmtGetBeneficiaries($post);
            $decode = json_decode($result);
            
            if(isset($decode->recipientList->dmtRecipient)){
                $data = $decode->recipientList->dmtRecipient;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            
            return response()->json(['success' => true, 'message' => '', 'data'=>$decode, 'recipientList'=>$data]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function billDmtDeleteBenf(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "beneficiary_id" => "required",
            "bank_channel" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $bank_channel = $request->get('bank_channel');
            $customer_mobile = $request->customer_mobile;
            $beneficiary_id = $request->beneficiary_id;
            $user = User::where('mobile',$request->user_mobile)->first();
            
            $post = [
                "mobile" => $customer_mobile,
                "beneficiary_id" => $beneficiary_id,
                "bank_id" => $bank_channel,
            ];
            $result = $this->BillavenueController->DmtDeleteBeneficiary($post);
            $decode = json_decode($result);
            
            if($decode->responseCode == '000'){
                return response()->json(['success' => true, 'message' => 'Delete Beneficiary', 'data'=>'', 'recipientList'=>'']);
            }else{
                return response()->json(['success' => false, 'message' => 'Something Wrong']);
            }
            
            
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function billDmtAddBenf(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required",
            "customer_mobile" => "required",
            "name" => "required|min:5",
            "account" => "required",
            "ifsc" => "required",
            "is_verify" => "required",
            "bank_id" => "required",
            "benf_mobile" => "required",
            "bank_channel" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $bank_channel = $request->get('bank_channel');
            $customer_mobile = $request->customer_mobile;
            $name = $request->name;
            $bank_id = $request->bank_id;
            $ifsc = $request->ifsc;
            $account = $request->account;
            $benf_mobile = $request->benf_mobile;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            $name = preg_replace('/[\*\?\[\]\{\}\^\$\(\)\+\|,\.\/\\\\]/', '', $name);
            $post = [
                "mobile_number" => $customer_mobile,
                "recipient_mobile" => $benf_mobile,
                "bank_id" => $bank_id,
                "account" => $account,
                "ifsc" => $ifsc,
                "recipient_name" => $name,
                "bank_channel" => $bank_channel,
            ];
            $result = $this->BillavenueController->DmtAddBeneficiary($post);
            $decode = json_decode($result);
            if($decode->responseCode == '000'){
                return response()->json(['success' => true, 'message' => '', 'data'=>$decode]);
            }else{
                if(isset($decode->errorInfo->error->errorMessage)){
                    $msg = $decode->errorInfo->error->errorMessage;
                }else{
                    $msg = "Something Wrong";
                }
                return response()->json(['success' => false, 'message' => $msg, 'data'=>$decode]);
            }
            
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function billDmtCheckStatus(Request $request) {
        $txns = transactions_dmt::where("status","0")->whereIn("eko_status",["0","2"])->where("api_id",1)->whereNotNull("tid")->get();
        foreach($txns as $r){
          
        
        $bank_channel = $r->bank_channel;
        $post_fee = [
                "uniqueRefId" => $r->tid,
            ];
            $result_fee = $this->BillavenueController->checkStatus($post_fee);
            $json_fee = json_decode($result_fee);
            
            if($json_fee->responseCode == '000'){
              Log::info($r->transaction_id);
              $status = $json_fee->fundTransferDetails->fundDetail->txnStatus;
                $post = [
                  "recipient_id" => $r->eko_beneficiary_id,
                  "customer_id" => $r->mobile,
                  "bank_id" => $bank_channel,
              ];
              $result_benf = $this->BillavenueController->DmtGetBeneficiary($post);
              $json_bnef = json_decode($result_benf);
              $post_sender = [
                  "mobile" => $r->mobile,
                  "bank_id" => $bank_channel,
              ];
              $result_sender = $this->BillavenueController->DmtCustomerLogin($post_sender);
              $json_sender = json_decode($result_sender);
            
              if($status == 'T'){
                $u = transactions_dmt::find($r->id);
                $u->eko_status = 3;
                $u->save();
              }
              
              if($status == 'C'){
                        if(isset($json_fee->fundTransferDetails->fundDetail)){
                            $utr =  $json_fee->fundTransferDetails->fundDetail->bankTxnId;
                            $DmtTxnId = $json_fee->fundTransferDetails->fundDetail->DmtTxnId;
                        }else{
                            $utr = 0;
                            $DmtTxnId = 0;
                        }
                $u = transactions_dmt::find($r->id);
                $u->status = 1;
                $u->utr = $utr;
                $u->dmt_txn_id = $DmtTxnId;
                if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                        $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                    }else{
                        $recipientName = ".";
                    }
                    $u->ben_name = $recipientName;
                $u->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                $u->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                $u->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                $u->sender_name = $json_sender->senderName;
                $u->save();
                
                    //Commission Start
                        $json_com = $this->WalletCalculation->retailorDmt($r->transaction_id);
                        $commission = $json_com['comm'];
                        $tds = $json_com['tds'];
                        $txnid_com = $this->WalletCalculation->txnId('COMM');
                        
                        $user = User::find($r->user_id);
                        $wallets_uuid = $this->WalletCalculation->walletDepositFloat($r->user_id,$commission,"Commission",'Credit_Retailer_'.$user->mobile.'_DMT_Remittance_Commission',$txnid_com);
                        
                        $ins = new user_commissions();
                        $ins->user_id = $r->user_id;
                        $ins->transaction_id = $txnid_com;
                        $ins->total_amount = $commission + $tds;
                        $ins->amount = $commission;
                        $ins->tds = $tds;
                        $ins->tds_par = 0;
                        $ins->wallets_uuid = $wallets_uuid;
                        $ins->ref_transaction_id = $r->transaction_id;
                        $ins->save();
                        
                        $update = transactions_dmt::find($r->id);
                        $update->comm_settl = 1;
                        $update->save();
                    //Commission end
              }
              
              if($status == 'F' || $status == 'R'){
                $u = transactions_dmt::find($r->id);
                $u->status = 2;
                $u->save();
                $transfer_type = $r->transfer_type;
                $user = User::find($r->user_id);
                $wallet = $user->wallet;
                $txnidr = $r->transaction_id;
                $amount = $r->amount;
                $fee = $r->fee;
                
                
                $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$r->mobile,$txnidr);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$r->mobile,$txnidr);
                    
                // $txn_wl_fee = $wallet->depositFloat($fee,[
                //             'meta' => [
                //                 'Title' => 'Money Transfer Fee',
                //                 'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$r->mobile,
                //                 'transaction_id' => $txnidr,
                //             ]
                //         ]);
                //         $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                //         $txn_wl = $wallet->depositFloat($amount,[
                //             'meta' => [
                //                 'Title' => 'Money Transfer',
                //                 'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$r->mobile,
                //                 'transaction_id' => $txnidr,
                //             ]
                //         ]);
                //         $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        if(isset($json_fee->fundTransferDetails->fundDetail)){
                            if(is_array($json_fee->fundTransferDetails->fundDetail)){
                                $utr = $json_fee->fundTransferDetails->fundDetail[0]->bankTxnId;
                                $DmtTxnId = $json_fee->fundTransferDetails->fundDetail[0]->DmtTxnId;
                            }else{
                                $utr =  $json_fee->fundTransferDetails->fundDetail->bankTxnId;
                                $DmtTxnId = $json_fee->fundTransferDetails->fundDetail->DmtTxnId;
                            }
                        }else{
                            $utr = 0;
                            $DmtTxnId = 0;
                        }
                        $update = transactions_dmt::find($r->id);
                        $update->status = 2;
                        $update->eko_status = 0;
                        $update->utr = $utr;
                        $update->dmt_txn_id = $DmtTxnId;
                        if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                            $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                        }else{
                            $recipientName = ".";
                        }
                        $update->ben_name = $recipientName;
                        $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                        $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                        $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                        $update->sender_name = $json_sender->senderName;
                        $update->save();
              }
            }
        }
    }
    
    public function billDmtDoTransactions(Request $request) {
        exit;
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "beneficiary_id" => "required",
            "amount" => [
                            "required",
                            "numeric",
                            function($attribute, $value, $fail) {
                                // Check if the amount is greater than 100
                                if ($value < 100) {
                                    $fail($attribute . ' must be greater than 100.');
                                }
                            },
                        ],
            "transfer_type" => "required",
            "latitude" => "required",
            "longitude" => "required",
            "bank_channel" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $bank_channel = $request->get('bank_channel');
            $user = User::where('mobile',$request->user_mobile)->first();
            // if($user->id != 21){
            //     return response()->json(['success' => false, 'message' => "Try after 11:00AM."]);
            // }
        
            $shop = shop_details::where('user_id',$user->id)->first();
            if($shop->latitude == null || $shop->longitude == null){
                return response()->json(['success' => false, 'message' => "Please Insert Shop Detail."]);
            }
            
            $distance = $this->UserAuth->geteDistance($shop->latitude, $shop->longitude, $request->latitude, $request->longitude);
            log::channel("location")->info("Retailer");
            log::channel("location")->info($request->latitude."/".$request->longitude);
            log::channel("location")->info("SHOP");
            log::channel("location")->info($shop->latitude."/".$shop->longitude);
            log::channel("location")->info("KM");
            log::channel("location")->info($distance);
            $distance_mnd = 25;
            if(isset($request->accuracy)){
                $distance_mnd = $distance_mnd + $request->accuracy;
                if($request->accuracy >= 100){
                    $distance_mnd = 220;
                }
                $distance_mnd = 350;
                
            }
            
            if($distance > $distance_mnd){
                if($user->id == 363){
                    
                }else{
                    return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
                }
            }
            
            $transfer_type = $request->transfer_type;
            $customer_mobile = $request->customer_mobile;
            $beneficiary_id = $request->beneficiary_id;
            $amount = $request->amount;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $user = User::where('mobile',$request->user_mobile)->first();
            $amount_par = $this->UserAuth->partitionAmount($amount);
            $main_amount = $amount;
            $loop_count = count($amount_par);
            $txnid = $this->UserAuth->txnId('DMT');
            //sleep(rand(2,6));
            $recentTime = Carbon::now()->subMinutes(10);
            $userwallet_check = User::find($user->id);
            $balance_compaer = $userwallet_check->wallet->balanceFloat;
            if($balance_compaer < $main_amount){
                return response()->json(['success' => false, 'message' => 'Insufficient balance.']);
            }
            // $duplicate = transactions_dmt::where('user_id', $user->id)
            //                 ->where('mobile', $customer_mobile)
            //                 ->where('eko_beneficiary_id', $beneficiary_id)
            //                 ->whereIn('status', [0,1])
            //                 ->where('amount', $amount_par[0])
            //                 ->where('created_at', '>=', $recentTime)
            //                 ->exists();
            // if ($duplicate) {
            //     log::info('Duplicate');
            //     log::info($request->all());
            //     return response()->json(['success' => false, 'message' => 'Duplicate! By Mistake Double Click or Duplicat Transaction. Please Check you report.']);
            // }
            
            //txn start
            //FEE ST
            // for($i = 0;$i < $loop_count;$i++){
            $i = 0;
                $txnidm = $txnid.'#'.$i;
                $txnidr = $this->UserAuth->txnId('DMT');
                $amount = $amount_par[$i];
                
            if($amount >= 100 && $amount <= 1000){
                if($amount > 1000){
                    $fee = $amount * 1/100;
                }else{
                    $fee = 10;
                }
                
            }else{
                $fee = $amount * 1/100;
            }
            $totalamount = $amount + $fee;
            $gst = $fee - ($fee / 1.18);
            sleep(rand(1,2));
            //FEE END
            $i_balance = 1;
            // billdodmtamount:
            $userwallet = User::find($user->id);
            $balance = $userwallet->wallet->balanceFloat;
            // print_r($balance);exit;
            if($balance < $totalamount){
                if($i == 0){
                    return response()->json(['success' => false, 'message' => 'Insufficient balance.']);
                }else{
                    goto skip;
                }
                
                
                
            }
            $wallet = $userwallet->wallet;
            // $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
            // if($balance_check->balance != $userwallet->wallet->balance){
            //         Log::channel("balancemissmatch")->info("========");
            //         Log::channel("balancemissmatch")->info($wallet->id);
            //         Log::channel("balancemissmatch")->info($balance_check->balance);
            //         Log::channel("balancemissmatch")->info($userwallet->wallet->balance);
            //         if($i_balance >= 25){
            //             if($i == 0){
            //                 return response()->json(['success' => false, 'message' => 'Try after 5 Min.']);
            //             }else{
            //                 goto skip;
            //             }
            //         }else{
            //             $i_balance++;
            //             goto billdodmtamount;
            //         }
                    
            // }
            
            $txn_wl_fee = $this->WalletCalculation->walletWithdrawFloat($user->id,$fee,'Money Transfer Fee','Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
            // $txn_wl_fee = $wallet->withdrawFloat($fee,[
            //     'meta' => [
            //         'Title' => 'Money Transfer Fee',
            //         'detail' => 'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
            //         'transaction_id' => $txnidr,
            //     ]
            // ]);
            // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
            
            $i_fee = 1;
            // billdodmtfee:
            $userwallet = User::find($user->id);
            $wallet = $userwallet->wallet;
            $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
            // if($balance_check->balance != $userwallet->wallet->balance){
            //         Log::channel("balancemissmatch")->info("========");
            //         Log::channel("balancemissmatch")->info($wallet->id);
            //         Log::channel("balancemissmatch")->info($balance_check->balance);
            //         Log::channel("balancemissmatch")->info($userwallet->wallet->balance);
            //         if($i_fee >= 25){
            //             if($i == 0){
            //                 return response()->json(['success' => false, 'message' => 'Try after 5 Min.']);
            //             }else{
            //                 goto skip;
            //             }
            //         }else{
            //             $i_fee++;
            //             goto billdodmtfee;
            //         }
                    
            // }
            
            $txn_wl = $this->WalletCalculation->walletWithdrawFloat($user->id,$amount,'Money Transfer','Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
            // $txn_wl = $wallet->withdrawFloat($amount,[
            //     'meta' => [
            //         'Title' => 'Money Transfer',
            //         'detail' => 'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
            //         'transaction_id' => $txnidr,
            //     ]
            // ]);
            // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
            
            
            
            $ins = new transactions_dmt();
            $ins->user_id = $user->id;
            $ins->event = 'DMT';
            $ins->transaction_id = $txnidr;
            $ins->multi_transaction_id = $txnidm;
            $ins->amount = $amount;
            $ins->mobile = $request->customer_mobile;
            $ins->transfer_type = $transfer_type;
            $ins->fee = $fee;
            $ins->tds = 0;
            $ins->gst = $gst;
            $ins->closing_balance = $wallet->balanceFloat;
            $ins->ben_name = '';
            $ins->ben_ac_number = '';
            $ins->ben_ac_ifsc = '';
            $ins->utr = 0;
            $ins->status = 0;
            $ins->eko_status = 6;
            $ins->api_id = 1;
            $ins->wallets_uuid = $txn_wl;
            $ins->eko_beneficiary_id = $beneficiary_id;
            $ins->bank_channel = $bank_channel;
            $ins->save();
            
            // if ($duplicate) {
            //     // return response()->json(['message' => 'Duplicate entry detected'], 409); // HTTP 409 Conflict
            // }
            
            if($transfer_type == 'IMPS'){
                $payment_mode = 2;
            }else{
                $payment_mode = 1;
            }
            $amount_bill = $amount * 100;
            
            $post_fee = [
                "amount" => $amount_bill,
            ];
            $result_fee = $this->BillavenueController->DmtGetCCFFee($post_fee);
            $json_fee = json_decode($result_fee);
            if($json_fee->responseCode == '000' || $json_fee->responseCode == '901'){
                $con_fee = $json_fee->custConvFee;
            }else{
                
                $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
                // $txn_wl_fee = $wallet->depositFloat($fee,[
                //             'meta' => [
                //                 'Title' => 'Money Transfer Fee',
                //                 'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                //                 'transaction_id' => $txnidr,
                //             ]
                //         ]);
                //         $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                //         $txn_wl = $wallet->depositFloat($amount,[
                //             'meta' => [
                //                 'Title' => 'Money Transfer',
                //                 'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                //                 'transaction_id' => $txnidr,
                //             ]
                //         ]);
                //         $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->eko_status = 0;
                        $update->save();
                        goto loopskip;
                        
            }
            
            $post = [
                "customer_id" => $customer_mobile,
                "recipient_id" => $beneficiary_id,
                "amount" => $amount_bill,
                "client_ref_id" => $txnidr,
                "latlong" => $latitude." ".$longitude,
                "transfer_type" => $transfer_type,
                "con_fee" => $con_fee,
                "bank_id" => $bank_channel,
            ];
            
            $result = $this->BillavenueController->DmtdoTransactions($post);
            $json = json_decode($result);
            $result_benf = $this->BillavenueController->DmtGetBeneficiary($post);
            $json_bnef = json_decode($result_benf);
            $post_sender = [
                "mobile" => $customer_mobile,
                "bank_id" => $bank_channel,
            ];
            $result_sender = $this->BillavenueController->DmtCustomerLogin($post_sender);
            $json_sender = json_decode($result_sender);
            Log::info("===========TXN SEND OTP==============");
            Log::info($result);
            Log::info($result_sender);
            if($json->responseCode == '000'){
                
                return response()->json(['success' => true, 'message' => 'OTP Send On Your Sender Mobile.', 'data'=>'','transaction_id'=>$txnidr]);
            }else{
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    //         'transaction_id' => $txnidr,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    //         'transaction_id' => $txnidr,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->eko_status = 1;
                    $update->utr = 0;
                    if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                        $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                    }else{
                        $recipientName = ".";
                    }
                    $update->ben_name = $recipientName;
                    $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                    $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                    $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                    $update->sender_name = $json_sender->senderName;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                     return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            loopskip:
            // }
            skip:
            // $data = transactions_dmt::select('transactions_dmts.*',
            //         'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
            //         ->where('transactions_dmts.multi_transaction_id','LIKE','%'.$txnid.'%')
            //         ->whereIn('status',[0,1])
            //         ->orderBy('transactions_dmts.id','DESC')->first();
            // $summary = transactions_dmt::select(DB::raw('(SELECT transaction_id FROM transactions_dmts WHERE multi_transaction_id LIKE "%'.$txnid.'%" ORDER BY transaction_id ASC LIMIT 1) as transaction_id'),
            //             'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
            //             ->addSelect(DB::raw('SUM(CASE WHEN status IN (0, 1) THEN amount ELSE 0 END) as amount'))
            //             ->addSelect(DB::raw('SUM(CASE WHEN status IN (0, 1) THEN fee ELSE 0 END) as fee'))
            //             ->addSelect(DB::raw('CASE 
            //                 WHEN SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) > 0 
            //                     THEN 0
            //                 WHEN SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) > 0 
            //                     THEN 1
            //                 ELSE 2 
            //                 END as status'))
            //             ->where('multi_transaction_id','LIKE','%'.$txnid.'%')
            //             ->groupBy('transactions_dmts.sender_name', 'transactions_dmts.mobile', 'transactions_dmts.bank_name')
            //             ->first();
            //     if($data){
            //         $summary_bkp = $summary;
            //         $summary = $data;
            //         if($summary_bkp->transaction_id){
            //             $summary->transaction_id = $summary_bkp->transaction_id;
            //         }
                    
            //         $summary->amount = $summary_bkp->amount;
            //         $summary->fee = $summary_bkp->fee;
            //         $summary->status = $summary_bkp->status;
            //     }else{
            //         $data = transactions_dmt::select('transactions_dmts.*',
            //         'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
            //         ->where('transactions_dmts.multi_transaction_id','LIKE','%'.$txnid.'%')
            //         ->orderBy('transactions_dmts.id','DESC')->first();
            //         $summary_bkp = $summary;
            //         $summary = $data;
            //         if($summary_bkp->transaction_id){
            //             $summary->transaction_id = $summary_bkp->transaction_id;
            //         }
                    
            //         $summary->amount = $summary_bkp->amount;
            //         $summary->fee = $summary_bkp->fee;
            //         $summary->status = $summary_bkp->status;
                    
            //     }
                
                        
            // if($summary){
            //     // Log::info('AFTER TXN RESULT');
            //     // Log::info($summary);
            //     if($summary->status == 1 || $summary->status == 0){
            //         return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$summary]);
            //     }else{
            //         return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$summary]);
            //     }
                
            // }else{
            //     return response()->json(['success' => false, 'message' => 'Insufficient balance.Please Check Report.']);
            // }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function billDmtDoTransactionsWeb(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "beneficiary_id" => "required",
            "amount" => [
                            "required",
                            "numeric",
                            function($attribute, $value, $fail) {
                                // Check if the amount is greater than 100
                                if ($value < 100) {
                                    $fail($attribute . ' must be greater than 100.');
                                }
                            },
                        ],
            "transfer_type" => "required",
            "latitude" => "required",
            "longitude" => "required",
            "bank_channel" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $bank_channel = $request->get('bank_channel');
            $user = User::where('mobile',$request->user_mobile)->first();
            // if($user->id != 27){
            //     return response()->json(['success' => false, 'message' => "Try after 05:00PM."]);
            // }
        
            $shop = shop_details::where('user_id',$user->id)->first();
            if($shop->latitude == null || $shop->longitude == null){
                return response()->json(['success' => false, 'message' => "Please Insert Shop Detail."]);
            }
            
            $distance = $this->UserAuth->geteDistance($shop->latitude, $shop->longitude, $request->latitude, $request->longitude);
            log::channel("location")->info("Retailer");
            log::channel("location")->info($request->latitude."/".$request->longitude);
            log::channel("location")->info("SHOP");
            log::channel("location")->info($shop->latitude."/".$shop->longitude);
            log::channel("location")->info("KM");
            log::channel("location")->info($distance);
            $distance_mnd = 25;
            if(isset($request->accuracy)){
                $distance_mnd = $distance_mnd + $request->accuracy;
                if($request->accuracy >= 100){
                    $distance_mnd = 220;
                }
                $distance_mnd = 350;
                
            }
            
            if($request->get('platform') == 'web'){
                $distance_mnd = 1000;
                if($distance > $distance_mnd){
                    if($user->id == 363){
                        
                    }else{
                        return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
                    }
                    
                }
            }else{
                if($distance > $distance_mnd){
                    if($user->id == 363){
                        
                    }else{
                        return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
                    }
                    
                }
                
            }
            
            $transfer_type = $request->transfer_type;
            $customer_mobile = $request->customer_mobile;
            $beneficiary_id = $request->beneficiary_id;
            $amount = $request->amount;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $user = User::where('mobile',$request->user_mobile)->first();
            $amount_par = $this->UserAuth->partitionAmount($amount);
            $main_amount = $amount;
            $loop_count = count($amount_par);
            $txnid = $this->UserAuth->txnId('DMT');
            //sleep(rand(2,6));
            $recentTime = Carbon::now()->subMinutes(10);
            $userwallet_check = User::find($user->id);
            $balance_compaer = $userwallet_check->wallet->balanceFloat;
            if($balance_compaer < $main_amount){
                return response()->json(['success' => false, 'message' => 'Insufficient balance.']);
            }
            
            
            //txn start
            //FEE ST
            for($i = 0;$i < $loop_count;$i++){
            
                $txnidm = $txnid.'#'.$i;
                $txnidr = $this->UserAuth->txnId('DMT');
                $amount = $amount_par[$i];
                
            if($amount >= 100 && $amount <= 1000){
                if($main_amount > 1000){
                    $fee = $amount * 1/100;
                }else{
                    $fee = 10;
                }
                
            }else{
                $fee = $amount * 1/100;
            }
            $totalamount = $amount + $fee;
            $gst = $fee - ($fee / 1.18);
            //sleep(rand(1,2));
            //FEE END
            $i_balance = 1;
            // billdodmtamount:
            $userwallet = User::find($user->id);
            $balance = $userwallet->wallet->balanceFloat;
            // print_r($balance);exit;
            if($balance < $totalamount){
                return response()->json(['success' => false, 'message' => 'Insufficient balance.']);
                
                
                
            }
            $wallet = $userwallet->wallet;
            
            
            $txn_wl_fee = $this->WalletCalculation->walletWithdrawFloat($user->id,$fee,'Money Transfer Fee','Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
            
            
            $i_fee = 1;
            // billdodmtfee:
            $userwallet = User::find($user->id);
            $wallet = $userwallet->wallet;
            $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
            
            
            $txn_wl = $this->WalletCalculation->walletWithdrawFloat($user->id,$amount,'Money Transfer','Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
            
            
            
            
            $ins = new transactions_dmt();
            $ins->user_id = $user->id;
            $ins->event = 'DMT';
            $ins->transaction_id = $txnidr;
            $ins->multi_transaction_id = $txnidm;
            $ins->amount = $amount;
            $ins->mobile = $request->customer_mobile;
            $ins->transfer_type = $transfer_type;
            $ins->fee = $fee;
            $ins->tds = 0;
            $ins->gst = $gst;
            $ins->closing_balance = $wallet->balanceFloat;
            $ins->ben_name = '';
            $ins->ben_ac_number = '';
            $ins->ben_ac_ifsc = '';
            $ins->utr = 0;
            $ins->status = 0;
            $ins->eko_status = 6;
            $ins->api_id = 1;
            $ins->wallets_uuid = $txn_wl;
            $ins->eko_beneficiary_id = $beneficiary_id;
            $ins->bank_channel = $bank_channel;
            $ins->save();
            
            $json_response = 1;
            if($json_response == '1'){
                
                $txns[] = $ins->id;
            }else{
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
                    
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->eko_status = 1;
                    $update->utr = 0;
                    if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                        $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                    }else{
                        $recipientName = ".";
                    }
                    $update->ben_name = $recipientName;
                    $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                    $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                    $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                    $update->sender_name = $json_sender->senderName;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                     return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            
            loopskip:
            }
            skip:
                
            $txn_data = transactions_dmt::select('transaction_id','mobile','otp_reference')->whereIn('id', $txns)->where('status',0)->where('eko_status',6)->get();
            return response()->json(['success' => true, 'message' => 'Transaction Created Please Complete it.','transaction_ids'=>$txns,'transaction_data'=>$txn_data,'transaction_id'=>$txnid]);
            
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function billDmtDoTransactionsOtpSend(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "transaction_id" => "required",
            "latitude" => "required",
            "longitude" => "required",
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
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        if($response)
        {
            $transaction_id = $request->transaction_id;
            $data = transactions_dmt::where('status','0')->where('transaction_id',$transaction_id)->first();
            if($data){
                $customer_mobile = $data->mobile;
                $bank_channel = $data->bank_channel;
                $account = $data->ben_ac_number;
                $amount = $data->amount;
                $latitude = $data->latitude;
                $longitude = $data->longitude;
                $trans_id = $data->id;
                $transfer_type = $data->transfer_type;
                $fee = $data->fee;
                $txnidr = $data->transaction_id;
                $beneficiary_id = $data->eko_beneficiary_id;
                
                if($transfer_type == 'IMPS'){
                    $payment_mode = 2;
                }else{
                    $payment_mode = 1;
                }
                $amount_bill = $amount * 100;
                
                $post_fee = [
                    "amount" => $amount_bill,
                ];
                $result_fee = $this->BillavenueController->DmtGetCCFFee($post_fee);
                $json_fee = json_decode($result_fee);
                if($json_fee->responseCode == '000' || $json_fee->responseCode == '901'){
                    $con_fee = $json_fee->custConvFee;
                }else{
                    $user = User::where('id',$data->iser_id)->first();
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$customer_mobile,$txnidr);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$customer_mobile,$txnidr);
                    
                            
                    $update = transactions_dmt::find($data->id);
                    $update->status = 2;
                    $update->eko_status = 0;
                    $update->save();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed Try Again!']);
                            
                            
                }
                
                $post = [
                    "customer_id" => $customer_mobile,
                    "recipient_id" => $beneficiary_id,
                    "amount" => $amount_bill,
                    "client_ref_id" => $txnidr,
                    "latlong" => $latitude." ".$longitude,
                    "transfer_type" => $transfer_type,
                    "con_fee" => $con_fee,
                    "bank_id" => $bank_channel,
                ];
                
                $result = $this->BillavenueController->DmtdoTransactions($post);
                $json = json_decode($result);
                $result_benf = $this->BillavenueController->DmtGetBeneficiary($post);
                $json_bnef = json_decode($result_benf);
                $post_sender = [
                    "mobile" => $customer_mobile,
                    "bank_id" => $bank_channel,
                ];
                $result_sender = $this->BillavenueController->DmtCustomerLogin($post_sender);
                $json_sender = json_decode($result_sender);
                Log::info("===========TXN SEND OTP==============");
                Log::info($result);
                Log::info($result_sender);
            
                            
                if($json->responseCode == '000'){
                    
                    $update = transactions_dmt::find($trans_id);
                    $update->otp_reference = $json->uniqueRefId;
                    $update->save();
                
                    return response()->json(['success' => true, 'message' => 'OTP Send On Your Sender Mobile.', 'data'=>'','transaction_id'=>$txnidr]);
                }else{
                        $user = User::where('id',$data->iser_id)->first();
                        $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
                        $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
                        
            
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->eko_status = 1;
                        $update->utr = 0;
                        if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                            $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                        }else{
                            $recipientName = ".";
                        }
                        $update->ben_name = $recipientName;
                        $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                        $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                        $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                        $update->sender_name = $json_sender->senderName;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                         return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                }
            }else{
                return response()->json(['success' => false, 'message' => 'Transaction Not Found!']);
            }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function billDmtDoTransactionsOtpVerify(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "transaction_id" => "required",
            "otp" => "required",
            "bank_channel" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $bank_channel = $request->get('bank_channel');
            $customer_mobile = $request->customer_mobile;
            $transaction_id = $request->transaction_id;
            $data = transactions_dmt::where('status','0')->where('transaction_id',$transaction_id)->first();
            
            if(!$data){
                return response()->json(['success' => false, 'message' => 'Transaction Not Initiated. Please Try again']);
            }
            $data->eko_status = 0;
            $data->save();
            
            $amount = $data->amount;
            $fee = $data->fee;
            $transfer_type = $data->transfer_type;
            $txnidr = $transaction_id;
            $user = User::find($data->user_id);
            $post_fee = [
                "amount" => $data->amount * 100,
            ];
            $result_fee = $this->BillavenueController->DmtGetCCFFee($post_fee);
            $json_fee = json_decode($result_fee);
            $con_fee = 0;
            Log::info($result_fee);
            if($json_fee->responseCode == '000' || $json_fee->responseCode == '901'){
                $con_fee = $json_fee->custConvFee;
            }
            
            $post_sender = [
                "mobile" => $customer_mobile,
                "bank_id" => $bank_channel,
            ];
            $result_sender = $this->BillavenueController->DmtCustomerLogin($post_sender);
            $json_sender = json_decode($result_sender);
            if(isset($json_sender->errorInfo->error->errorCode)){
                return response()->json(['success' => false, 'message' => 'Transaction Not Initiated. Please Try again']);
            }
            
            $post = [
                "customer_id" => $customer_mobile,
                "recipient_id" => $data->eko_beneficiary_id,
                "amount" => $data->amount*100,
                "transfer_type" => $data->transfer_type,
                "con_fee" => $con_fee,
                "otp" => $request->otp,
                "bank_id" => $bank_channel,
            ];
            
            $result = $this->BillavenueController->DmtdoTransactionsOtpVerify($post);
            $json = json_decode($result);
            $result_benf = $this->BillavenueController->DmtGetBeneficiary($post);
            $json_bnef = json_decode($result_benf);
            
            $userwallet = User::find($data->user_id);
            $wallet = $userwallet->wallet;
            Log::info($result);
            if($data->user_id == 27){
                // $result = '{"fundTransferDetails":{"fundDetail":{"uniqueRefId":"BA250727180328259BA2507271803282590","bankTxnId":{},"custConvFee":"3000","DmtTxnId":"125393833","impsName":"MIRA DEVI","refId":"CCAF5CE6DAE75D34CA1841F864D24697B9E","txnAmount":"2500","txnStatus":"Q"}},"respDesc":"Rs. 2500.00 is debited from your wallet.  ","responseCode":"000","responseReason":"Successful","senderMobileNo":"8401838825","uniqueRefId":"BA250727180328259BA2507271803282590"}';
                // $json = json_decode($result);
            }
            
            if($json->responseCode == "000" || $json->responseCode == "901"){
                
                if($json->responseCode == "000" && $json->fundTransferDetails->fundDetail->txnStatus == "C"){
                    //success
                    Log::info('AFTER TXN RESULT 000 C');
                    if(isset($json->fundTransferDetails->fundDetail)){
                        if(is_array($json->fundTransferDetails->fundDetail)){
                            $utr = $json->fundTransferDetails->fundDetail[0]->bankTxnId;
                            $DmtTxnId = $json->fundTransferDetails->fundDetail[0]->DmtTxnId;
                        }else{
                            $utr =  $json->fundTransferDetails->fundDetail->bankTxnId;
                            $DmtTxnId = $json->fundTransferDetails->fundDetail->DmtTxnId;
                        }
                    }else{
                        $utr = 0;
                        $DmtTxnId = 0;
                    }
                    
                    $update = transactions_dmt::find($data->id);
                    $update->status = 1;
                    $update->eko_status = 0;
                    $update->utr = $utr;
                    $update->tid = $json->uniqueRefId;
                    $update->dmt_txn_id = $DmtTxnId;
                    if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                        $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                    }else{
                        $recipientName = ".";
                    }
                    $update->ben_name = $recipientName;
                    $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                    $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                    $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                    $update->sender_name = $json_sender->senderName;
                    $update->save();
                    
                    // $this->WalletCalculation->distributorDmt($txnidr);
                    // $this->WalletCalculation->retailorDmt($txnidr);
                    
                    //Commission Start
                        $json_com = $this->WalletCalculation->retailorDmt($data->transaction_id);
                        $commission = $json_com['comm'];
                        $tds = $json_com['tds'];
                        $txnid_com = $this->WalletCalculation->txnId('COMM');
                        
                        $user = User::find($data->user_id);
                        $wallets_uuid = $this->WalletCalculation->walletDepositFloat($data->user_id,$commission,"Commission",'Credit_Retailer_'.$user->mobile.'_DMT_Remittance_Commission',$txnid_com);
                        
                        $ins = new user_commissions();
                        $ins->user_id = $data->user_id;
                        $ins->transaction_id = $txnid_com;
                        $ins->total_amount = $commission + $tds;
                        $ins->amount = $commission;
                        $ins->tds = $tds;
                        $ins->tds_par = 0;
                        $ins->wallets_uuid = $wallets_uuid;
                        $ins->ref_transaction_id = $data->transaction_id;
                        $ins->save();
                        
                        $update = transactions_dmt::find($data->id);
                        $update->comm_settl = 1;
                        $update->save();
                    //Commission end
                    
                    $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txnidr on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                    // $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->customer_mobile,$text,1207172205377529606);
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$data->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    // return response()->json(['success' => true, 'message' => 'Transaction Success.', 'data'=>$data]);
                    
                }elseif($json->responseCode == "000" && $json->fundTransferDetails->fundDetail->txnStatus == "P"){
                    Log::info('AFTER TXN RESULT 000 P');
                    if(isset($json->fundTransferDetails->fundDetail)){
                        if(is_array($json->fundTransferDetails->fundDetail)){
                            
                            $DmtTxnId = $json->fundTransferDetails->fundDetail[0]->DmtTxnId;
                        }else{
                            
                            $DmtTxnId = $json->fundTransferDetails->fundDetail->DmtTxnId;
                        }
                    }else{
                        
                        $DmtTxnId = 0;
                    }
                    
                    $utr = 0;
                    $update = transactions_dmt::find($data->id);
                    $update->eko_status = 0;
                    $update->utr = $utr;
                    $update->tid = $json->uniqueRefId;
                    $update->dmt_txn_id = $DmtTxnId;
                    if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                        $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                    }else{
                        $recipientName = ".";
                    }
                    $update->ben_name = $recipientName;
                    $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                    $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                    $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                    $update->sender_name = $json_sender->senderName;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$data->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    // return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    
                }elseif($json->responseCode == "000" && $json->fundTransferDetails->fundDetail->txnStatus == "Q"){
                    Log::info('AFTER TXN RESULT 000 Q');
                    if(isset($json->fundTransferDetails->fundDetail)){
                        if(is_array($json->fundTransferDetails->fundDetail)){
                            
                            $DmtTxnId = $json->fundTransferDetails->fundDetail[0]->DmtTxnId;
                        }else{
                            
                            $DmtTxnId = $json->fundTransferDetails->fundDetail->DmtTxnId;
                        }
                    }else{
                        
                        $DmtTxnId = 0;
                    }
                    $utr = 0;
                    $update = transactions_dmt::find($data->id);
                    $update->eko_status = 2;
                    $update->utr = $utr;
                    $update->dmt_txn_id = $DmtTxnId;
                    $update->tid = $json->uniqueRefId;
                    if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                        $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                    }else{
                        $recipientName = ".";
                    }
                    $update->ben_name = $recipientName;
                    $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                    $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                    $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                    $update->sender_name = $json_sender->senderName;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$data->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    
                }elseif($json->responseCode == "901"){
                    Log::info('AFTER TXN RESULT 901');
                    if(isset($json->fundTransferDetails->fundDetail)){
                        if(is_array($json->fundTransferDetails->fundDetail)){
                            
                            $DmtTxnId = $json->fundTransferDetails->fundDetail[0]->DmtTxnId;
                        }else{
                            
                            $DmtTxnId = $json->fundTransferDetails->fundDetail->DmtTxnId;
                        }
                    }else{
                        
                        $DmtTxnId = 0;
                    }
                    $utr = 0;
                    $update = transactions_dmt::find($data->id);
                    $update->eko_status = 0;
                    $update->utr = $utr;
                    $update->dmt_txn_id = $DmtTxnId;
                    $update->tid = $json->uniqueRefId;
                    if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                        $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                    }else{
                        $recipientName = ".";
                    }
                    $update->ben_name = $recipientName;
                    $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                    $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                    $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                    $update->sender_name = $json_sender->senderName;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$data->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    // return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                }else{
                    Log::info('AFTER TXN RESULT 000');
                    if($json->responseCode == "000" && $json->fundTransferDetails->fundDetail->txnStatus == "F"){
                        
                        $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$amount,"Money Transfer",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
                        // $txn_wl_fee = $wallet->depositFloat($fee,[
                        //     'meta' => [
                        //         'Title' => 'Money Transfer Fee',
                        //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                        //         'transaction_id' => $txnidr,
                        //     ]
                        // ]);
                        // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        // $txn_wl = $wallet->depositFloat($amount,[
                        //     'meta' => [
                        //         'Title' => 'Money Transfer',
                        //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                        //         'transaction_id' => $txnidr,
                        //     ]
                        // ]);
                        // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        if(isset($json->fundTransferDetails->fundDetail)){
                            if(is_array($json->fundTransferDetails->fundDetail)){
                                $utr = $json->fundTransferDetails->fundDetail[0]->bankTxnId;
                                $DmtTxnId = $json->fundTransferDetails->fundDetail[0]->DmtTxnId;
                            }else{
                                $utr =  $json->fundTransferDetails->fundDetail->bankTxnId;
                                $DmtTxnId = $json->fundTransferDetails->fundDetail->DmtTxnId;
                            }
                        }else{
                            $utr = 0;
                            $DmtTxnId = 0;
                        }
                        $update = transactions_dmt::find($data->id);
                        $update->status = 2;
                        $update->eko_status = 0;
                        $update->utr = $utr;
                        $update->tid = $json->uniqueRefId;
                        $update->dmt_txn_id = $DmtTxnId;
                        if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                            $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                        }else{
                            $recipientName = ".";
                        }
                        $update->ben_name = $recipientName;
                        $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                        $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                        $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                        $update->sender_name = $json_sender->senderName;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->where('transactions_dmts.id',$data->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        // return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                    }elseif($json->responseCode == "000" && $json->fundTransferDetails->fundDetail->txnStatus == "R"){
                        
                        $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$amount,"Money Transfer",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
                        
                        // $txn_wl_fee = $wallet->depositFloat($fee,[
                        //     'meta' => [
                        //         'Title' => 'Money Transfer Fee',
                        //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                        //         'transaction_id' => $txnidr,
                        //     ]
                        // ]);
                        // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        // $txn_wl = $wallet->depositFloat($amount,[
                        //     'meta' => [
                        //         'Title' => 'Money Transfer',
                        //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                        //         'transaction_id' => $txnidr,
                        //     ]
                        // ]);
                        // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        if(isset($json->fundTransferDetails->fundDetail)){
                            if(is_array($json->fundTransferDetails->fundDetail)){
                                $utr = $json->fundTransferDetails->fundDetail[0]->bankTxnId;
                                $DmtTxnId = $json->fundTransferDetails->fundDetail[0]->DmtTxnId;
                            }else{
                                $utr =  $json->fundTransferDetails->fundDetail->bankTxnId;
                                $DmtTxnId = $json->fundTransferDetails->fundDetail->DmtTxnId;
                            }
                        }else{
                            $utr = 0;
                            $DmtTxnId = 0;
                        }
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->eko_status = 0;
                        $update->utr = $utr;
                        $update->tid = $json->uniqueRefId;
                        $update->dmt_txn_id = $DmtTxnId;
                        if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                            $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                        }else{
                            $recipientName = ".";
                        }
                        $update->ben_name = $recipientName;
                        $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                        $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                        $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                        $update->sender_name = $json_sender->senderName;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->where('transactions_dmts.id',$data->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        // return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                        
                    }elseif($json->responseCode == "000" && $json->fundTransferDetails->fundDetail->txnStatus == "S"){
                        
                        $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$amount,"Money Transfer",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
                        // $txn_wl_fee = $wallet->depositFloat($fee,[
                        //     'meta' => [
                        //         'Title' => 'Money Transfer Fee',
                        //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                        //         'transaction_id' => $txnidr,
                        //     ]
                        // ]);
                        // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        // $txn_wl = $wallet->depositFloat($amount,[
                        //     'meta' => [
                        //         'Title' => 'Money Transfer',
                        //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                        //         'transaction_id' => $txnidr,
                        //     ]
                        // ]);
                        // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        if(isset($json->fundTransferDetails->fundDetail)){
                            if(is_array($json->fundTransferDetails->fundDetail)){
                                $utr = $json->fundTransferDetails->fundDetail[0]->bankTxnId;
                                $DmtTxnId = $json->fundTransferDetails->fundDetail[0]->DmtTxnId;
                            }else{
                                $utr =  $json->fundTransferDetails->fundDetail->bankTxnId;
                                $DmtTxnId = $json->fundTransferDetails->fundDetail->DmtTxnId;
                            }
                        }else{
                            $utr = 0;
                            $DmtTxnId = 0;
                        }
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->eko_status = 0;
                        $update->utr = $utr;
                        $update->dmt_txn_id = $DmtTxnId;
                        $update->tid = $json->uniqueRefId;
                        if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                            $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                        }else{
                            $recipientName = ".";
                        }
                        $update->ben_name = $recipientName;
                        $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                        $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                        $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                        $update->sender_name = $json_sender->senderName;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->where('transactions_dmts.id',$data->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        // return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                        
                    }else{
                        if(isset($json->fundTransferDetails->fundDetail)){
                            if(is_array($json->fundTransferDetails->fundDetail)){
                                $utr = $json->fundTransferDetails->fundDetail[0]->bankTxnId;
                                $DmtTxnId = $json->fundTransferDetails->fundDetail[0]->DmtTxnId;
                            }else{
                                $utr =  $json->fundTransferDetails->fundDetail->bankTxnId;
                                $DmtTxnId = $json->fundTransferDetails->fundDetail->DmtTxnId;
                            }
                        }else{
                            $utr = 0;
                            $DmtTxnId = 0;
                        }
                        $update = transactions_dmt::find($ins->id);
                        $update->eko_status = 0;
                        $update->utr = $utr;
                        $update->dmt_txn_id = $DmtTxnId;
                        $update->tid = $json->uniqueRefId;
                        if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                            $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                        }else{
                            $recipientName = ".";
                        }
                        $update->ben_name = $recipientName;
                        $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                        $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                        $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                        $update->sender_name = $json_sender->senderName;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->where('transactions_dmts.id',$data->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        // return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    }
                    
                }
            }else{
                Log::info("BA FAILD");
                Log::info($result);
                    if(isset($json->errorInfo->error->errorCode)){
                        if($json->errorInfo->error->errorCode == "DMT031"){
                            if(isset($json->fundTransferDetails->fundDetail) && isset($json->fundTransferDetails->fundDetail->txnStatus)){
                                if($json->fundTransferDetails->fundDetail->txnStatus == 'F'){
                                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
                                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$amount,"Money Transfer",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
                                    
                                    $update = transactions_dmt::find($data->id);
                                    $update->status = 2;
                                    $update->eko_status = 1;
                                    $update->utr = 0;
                                    if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                                        $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                                    }else{
                                        $recipientName = ".";
                                    }
                                    $update->ben_name = $recipientName;
                                    $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                                    $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                                    $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                                    $update->sender_name = $json_sender->senderName;
                                    $update->save();
                                }else{
                                    $update = transactions_dmt::find($data->id);
                                    $update->status = 0;
                                    $update->eko_status = 2;
                                    $update->utr = 0;
                                    if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                                        $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                                    }else{
                                        $recipientName = ".";
                                    }
                                    $update->ben_name = $recipientName;
                                    $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                                    $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                                    $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                                    $update->sender_name = $json_sender->senderName;
                                    $update->save();
                                }
                            }else{
                                $update = transactions_dmt::find($data->id);
                                $update->status = 0;
                                $update->eko_status = 2;
                                $update->utr = 0;
                                if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                                    $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                                }else{
                                    $recipientName = ".";
                                }
                                $update->ben_name = $recipientName;
                                $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                                $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                                $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                                $update->sender_name = $json_sender->senderName;
                                $update->save();
                            }
                            
                        }else{
                            $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
                            $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$amount,"Money Transfer",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
                            // $txn_wl_fee = $wallet->depositFloat($fee,[
                            //     'meta' => [
                            //         'Title' => 'Money Transfer Fee',
                            //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                            //         'transaction_id' => $txnidr,
                            //     ]
                            // ]);
                            // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                            
                            // $txn_wl = $wallet->depositFloat($amount,[
                            //     'meta' => [
                            //         'Title' => 'Money Transfer',
                            //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                            //         'transaction_id' => $txnidr,
                            //     ]
                            // ]);
                            // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                            
                            $update = transactions_dmt::find($data->id);
                            $update->status = 2;
                            $update->eko_status = 1;
                            $update->utr = 0;
                            if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                                $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                            }else{
                                $recipientName = ".";
                            }
                            $update->ben_name = $recipientName;
                            $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                            $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                            $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                            $update->sender_name = $json_sender->senderName;
                            $update->save();
                        }
                    }else{
                        // $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
                    // $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$amount,"Money Transfer",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
                        // $txn_wl_fee = $wallet->depositFloat($fee,[
                        //     'meta' => [
                        //         'Title' => 'Money Transfer Fee',
                        //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                        //         'transaction_id' => $txnidr,
                        //     ]
                        // ]);
                        // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        // $txn_wl = $wallet->depositFloat($amount,[
                        //     'meta' => [
                        //         'Title' => 'Money Transfer',
                        //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                        //         'transaction_id' => $txnidr,
                        //     ]
                        // ]);
                        // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        
                        $update = transactions_dmt::find($data->id);
                        // $update->status = 2;
                        $update->eko_status = 0;
                        $update->utr = 00;
                        if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                            $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                        }else{
                            $recipientName = ".";
                        }
                        $update->ben_name = $recipientName;
                        $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                        $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                        $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                        $update->sender_name = $json_sender->senderName;
                        $update->save();
                    }
                    
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$data->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    // return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            
            $txnid = $data->multi_transaction_id;
            $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.multi_transaction_id','LIKE','%'.$txnid.'%')
                    ->whereIn('status',[0,1])
                    ->orderBy('transactions_dmts.id','DESC')->first();
            $summary = transactions_dmt::select(DB::raw('(SELECT transaction_id FROM transactions_dmts WHERE multi_transaction_id LIKE "%'.$txnid.'%" ORDER BY transaction_id ASC LIMIT 1) as transaction_id'),
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->addSelect(DB::raw('SUM(CASE WHEN status IN (0, 1) THEN amount ELSE 0 END) as amount'))
                        ->addSelect(DB::raw('SUM(CASE WHEN status IN (0, 1) THEN fee ELSE 0 END) as fee'))
                        ->addSelect(DB::raw('CASE 
                            WHEN SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) > 0 
                                THEN 0
                            WHEN SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) > 0 
                                THEN 1
                            ELSE 2 
                            END as status'))
                        ->where('multi_transaction_id','LIKE','%'.$txnid.'%')
                        ->groupBy('transactions_dmts.sender_name', 'transactions_dmts.mobile', 'transactions_dmts.bank_name')
                        ->first();
                if($data){
                    $summary_bkp = $summary;
                    $summary = $data;
                    if($summary_bkp->transaction_id){
                        $summary->transaction_id = $summary_bkp->transaction_id;
                    }
                    
                    $summary->amount = $summary_bkp->amount;
                    $summary->fee = $summary_bkp->fee;
                    $summary->status = $summary_bkp->status;
                }else{
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.multi_transaction_id','LIKE','%'.$txnid.'%')
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    $summary_bkp = $summary;
                    $summary = $data;
                    if($summary_bkp->transaction_id){
                        $summary->transaction_id = $summary_bkp->transaction_id;
                    }
                    
                    $summary->amount = $summary_bkp->amount;
                    $summary->fee = $summary_bkp->fee;
                    $summary->status = $summary_bkp->status;
                    
                }
                
                        
            if($summary){
                // Log::info('AFTER TXN RESULT');
                // Log::info($summary);
                if($summary->status == 1 || $summary->status == 0){
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$summary]);
                }else{
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$summary]);
                }
                
            }else{
                return response()->json(['success' => false, 'message' => 'Insufficient balance.Please Check Report.']);
            }
            
        }
        
        
    }
    
    public function billDmtRefundRequestManul(Request $request) {
        // $post = [
        //     "txnId" => 109441435
        // ];
        // $result = $this->BillavenueController->refundRequest($post);
        // $decode = json_decode($result);
        
        $post = [
            "txnId" => 118615915,
            "otp" => '483747',
            "uniqueRefId" => 'BA250213093618529BA2502130936185290',
        ];
        $result = $this->BillavenueController->refundOtpVerify($post);
        $decode = json_decode($result);
        print_r($decode);
    }
    
    public function billDmtRefundRequest(Request $request) {
        
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required",
            "transaction_id" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $txn = transactions_dmt::where('transaction_id',$request->transaction_id)
                    ->whereNotNull('dmt_txn_id')
                    ->where('status',0)
                    ->where('eko_status',3)
                    ->first();
            if(!$txn){
                return response()->json(['success' => false, 'message' => 'Please Check With Admin!']);
            }
            $post = [
                "txnId" => $txn->dmt_txn_id
            ];
            $result = $this->BillavenueController->refundRequest($post);
            $decode = json_decode($result);
            if($decode->responseCode == '000'){
                return response()->json(['success' => true, 'message' => $decode->respDesc, 'data'=>$decode]);
            }else{
                return response()->json(['success' => false, 'message' => 'Something Wrong', 'data'=>$decode]);
            }
            
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function billDmtRefundOtpVerify(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required",
            "transaction_id" => "required",
            "otp" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $txn = transactions_dmt::where('transaction_id',$request->transaction_id)->where('status',0)
                    ->where('eko_status',3)
                    ->first();
            if(!$txn){
                return response()->json(['success' => false, 'message' => 'Please Check With Admin!']);
            }
            $post = [
                "txnId" => $txn->dmt_txn_id,
                "otp" => $request->otp,
                "uniqueRefId" => $txn->tid,
            ];
            $result = $this->BillavenueController->refundOtpVerify($post);
            $decode = json_decode($result);
            if($decode->responseCode == '000'){
                
                $user = User::find($txn->user_id);
                $wallet = $user->wallet;
                $txnidr = $txn->transaction_id;
                $fee = $txn->fee;
                $transfer_type = $txn->transfer_type;
                $amount = $txn->amount;
                $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$txn->mobile,$txnidr);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$txn->mobile,$txnidr);
                // $txn_wl_fee = $wallet->depositFloat($fee,[
                //     'meta' => [
                //         'Title' => 'Money Transfer Fee',
                //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$txn->mobile,
                //         'transaction_id' => $txnidr,
                //     ]
                // ]);
                // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                
                // $amount = $txn->amount;
                
                // $txn_wl = $wallet->depositFloat($amount,[
                //     'meta' => [
                //         'Title' => 'Money Transfer',
                //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$txn->mobile,
                //         'transaction_id' => $txnidr,
                //     ]
                // ]);
                // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                
                $update = transactions_dmt::find($txn->id);
                $update->status = 2;
                $update->eko_status = 0;
                $update->refund_dmt_id = $decode->refundTxnId;
                $update->save();
                
                return response()->json(['success' => true, 'message' => $decode->respDesc, 'data'=>$decode]);
            }else{
                return response()->json(['success' => false, 'message' => 'Something Wrong', 'data'=>$decode]);
            }
            
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function bulkpeUPIcreation(Request $request) {
        $mobile = '9978177178';
        $user = User::where('mobile',$mobile)->first();
        $shop_details = shop_details::where('user_id',$user->id)->first();
        $kyc_docs = kyc_docs::where('user_id',$user->id)->first();
        $Addresses = Addresses::where('user_id',$user->id)->first();
        
        $api_params = '{
                            "reference_id": "PARITE01",
                            "amount": 1
                        }';
            
        $result = $this->BulkpeController->createUPI($api_params);
        print_r($result);
    }
    
    public function calculateAEPS(Request $request) {
        // Fetch all users with their successful transactions from yesterday using eager loading
        $users = User::with(['transactionsAeps' => function ($query) {
            $query->where('status', '1')->where('comm_settl', '0')->whereIn('transfer_type', ['cash_withdrawal','mini_statement'])
                // ->whereDate('created_at', '<', Carbon::today());
                  ->whereDate('updated_at', Carbon::yesterday());
        }])->get();
        
        // Calculate commission for each transaction
        foreach ($users as $user) {
            $totalCommission = 0;
            $totalCommission_dist = 0;
            $totaltds = 0;
            $totaltds_dist = 0;
            $toplevel_id = 0;
            $total_txn = 0;
            foreach ($user->transactionsAeps as $k => $transaction) {
                $total_txn++;
                $txnid = $transaction->transaction_id;
                $json = $this->WalletCalculation->retailorAeps($txnid);
                $commission = $json['comm'];
                $tds = $json['tds'];
                $totalCommission += $commission;
                $totaltds += $tds;
                $topup_user = user_levels::where('user_id',$transaction->user_id)->first();
                if($topup_user){
                    $json_dist = $this->WalletCalculation->distributorAeps($txnid);
                    $toplevel_id = $topup_user->toplevel_id;
                    $commission_dist = $json_dist['comm'];
                    $tds_dist = $json_dist['tds'];
                    $totalCommission_dist += $commission_dist;
                    $totaltds_dist += $tds_dist;
                }
                
                
                
            }
            
            if($totalCommission != 0){
                
            
            $txnid = $this->WalletCalculation->txnId('COMM');
            $userwallet = User::find($user->id);
            $wallet = $userwallet->wallet;
            
            $user_commission = $this->WalletCalculation->walletDepositFloat($user->id,$totalCommission,"Commission",'Credit_Retailer_'.$user->mobile.'_AEPS_Commission',$txnid);
            
            $topup_user = user_levels::where('user_id',$transaction->user_id)->first();
            $ins = new user_commissions();
            $ins->user_id = $user->id;
            $ins->transaction_id = $txnid;
            $ins->total_amount = $totalCommission + $totaltds;
            $ins->amount = $totalCommission;
            $ins->tds = $totaltds;
            $ins->tds_par = 0;
            $ins->wallets_uuid = $user_commission;
            $ins->ref_transaction_id = 0;
            $ins->save();
        
            if($topup_user){
                $txnid_dist = $this->WalletCalculation->txnId('COMM');
                $userwallet_dist = User::find($topup_user->toplevel_id);
                $wallet_dist = $userwallet_dist->wallet;
                
                $user_commission_dist = $this->WalletCalculation->walletDepositFloat($topup_user->toplevel_id,$totalCommission_dist,"Commission",'Credit_Distributor_'.$userwallet_dist->mobile.'_AEPS_Commission_Retailer_'.$user->mobile,$txnid_dist);
                
                $ins = new user_commissions();
                $ins->user_id = $userwallet_dist->id;
                $ins->transaction_id = $txnid_dist;
                $ins->total_amount = $totalCommission_dist + $totaltds_dist;
                $ins->amount = $totalCommission_dist;
                $ins->tds = $totaltds_dist;
                $ins->tds_par = 0;
                $ins->wallets_uuid = $user_commission_dist;
                $ins->ref_transaction_id = 0;
                $ins->save();
                
            }
            
            
            echo $total_txn."#".$totalCommission."#".$user->id."#".$user->mobile."<br>";
            echo $totalCommission_dist."#".$toplevel_id."<br>";
            echo "==================================<br>";
            }
        }
        
        $balance_update_dist = transactions_aeps::where('status', 1)
                                ->where('comm_settl', '0')
                                ->whereIn('transfer_type', ['cash_withdrawal','mini_statement'])
                                ->whereDate('created_at', Carbon::yesterday())
                                // ->whereDate('created_at', '<', Carbon::today())
                                ->update(['comm_settl' => 1]);
    }
    
    public function calculateDMT(Request $request) {
        $date = Carbon::yesterday();
        
        
        
        // Fetch all users with their successful transactions from yesterday using eager loading
        $users = User::with(['transactionsDmt' => function ($query) use ($date) {
            $query->where('status', '1')->where('comm_settl', '0')->where('event', 'DMT')
                  ->whereDate('updated_at', $date);
        }])->get();
        $balance_update_dist = transactions_dmt::where('status', 1)
                                ->where('comm_settl', '0')
                                ->where('event', 'DMT')
                                ->whereDate('created_at', $date)
                                ->update(['comm_settl' => 1]);
        
        // Calculate commission for each transaction
        foreach ($users as $user) {
            $totalCommission = 0;
            $totalCommission_dist = 0;
            $totaltds = 0;
            $totaltds_dist = 0;
            $toplevel_id = 0;
            foreach ($user->transactionsDmt as $transaction) {
                $txnid = $transaction->transaction_id;
                $json = $this->WalletCalculation->retailorDmt($txnid);
                $commission = $json['comm'];
                $tds = $json['tds'];
                $totalCommission += $commission;
                $totaltds += $tds;
                $topup_user = user_levels::where('user_id',$transaction->user_id)->first();
                if($topup_user){
                    $json_dist = $this->WalletCalculation->distributorDmt($txnid);
                    $toplevel_id = $topup_user->toplevel_id;
                    $commission_dist = $json_dist['comm'];
                    $tds_dist = $json_dist['tds'];
                    $totalCommission_dist += $commission_dist;
                    $totaltds_dist += $tds_dist;
                }
                
                
                
            }
            
            if($totalCommission != 0){
                
            
            $txnid = $this->WalletCalculation->txnId('COMM');
            $userwallet = User::find($user->id);
            $wallet = $userwallet->wallet;
            $txn = $wallet->depositFloat($totalCommission,[
                'meta' => [
                    'Title' => 'Commission',
                    'detail' => 'Credit_Retailer_'.$user->mobile.'_DMT_Remittance_Commission',
                    'transaction_id' => $txnid,
                ]
            ]);
            $balance_update = Transaction::where('uuid', $txn->uuid)->update(['balance' => $wallet->balance]);
            $topup_user = user_levels::where('user_id',$transaction->user_id)->first();
            $ins = new user_commissions();
            $ins->user_id = $user->id;
            $ins->transaction_id = $txnid;
            $ins->total_amount = $totalCommission + $totaltds;
            $ins->amount = $totalCommission;
            $ins->tds = $totaltds;
            $ins->tds_par = 0;
            $ins->wallets_uuid = $txn->uuid;
            $ins->ref_transaction_id = 0;
            $ins->save();
        
            if($topup_user){
                $txnid_dist = $this->WalletCalculation->txnId('COMM');
                $userwallet_dist = User::find($topup_user->toplevel_id);
                $wallet_dist = $userwallet_dist->wallet;
                $txn_dist = $wallet_dist->depositFloat($totalCommission_dist,[
                    'meta' => [
                        'Title' => 'Commission',
                        'detail' => 'Credit_Distributor_'.$userwallet_dist->mobile.'_DMT_Remittance_Commission_Retailer_'.$user->mobile,
                        'transaction_id' => $txnid_dist,
                    ]
                ]);
                $balance_update_dist = Transaction::where('uuid', $txn_dist->uuid)->update(['balance' => $wallet_dist->balance]);
                
                $ins = new user_commissions();
                $ins->user_id = $userwallet_dist->id;
                $ins->transaction_id = $txnid_dist;
                $ins->total_amount = $totalCommission_dist + $totaltds_dist;
                $ins->amount = $totalCommission_dist;
                $ins->tds = $totaltds_dist;
                $ins->tds_par = 0;
                $ins->wallets_uuid = $txn_dist->uuid;
                $ins->ref_transaction_id = 0;
                $ins->save();
                
            }
            log::channel('myuse')->info("RET : ".$totalCommission."#".$user->id);
            log::channel('myuse')->info("DIST : ".$totalCommission_dist."#".$toplevel_id);
            echo $totalCommission."#".$user->id."<br>";
            echo $totalCommission_dist."#".$toplevel_id."<br>";
            echo "==================================<br>";
            }
        }
        
        // $balance_update_dist = transactions_dmt::where('status', 1)
        //                         ->where('comm_settl', '0')
        //                         ->where('event', 'DMT')
        //                         ->whereDate('created_at', Carbon::yesterday())
        //                         ->update(['comm_settl' => 1]);
    }
    
    public function calculateDMTDist(Request $request) {
        
        $users = User::with(['transactionsDmt' => function ($query) {
            $query->where('status', '1')->where('comm_settl_dist', '0')->where('event', 'DMT')
                  ->whereDate('created_at', Carbon::yesterday());
        }])->where('user_type',2)->get();
        $dist = array();
        $dist_tds = array();
        // Calculate commission for each transaction
        foreach ($users as $user) {
            $totalCommission = 0;
            $totalCommission_dist = 0;
            $totaltds = 0;
            $totaltds_dist = 0;
            $toplevel_id = 0;
            
            foreach ($user->transactionsDmt as $transaction) {
                $txnid = $transaction->transaction_id;
                
                $topup_user = user_levels::where('user_id',$transaction->user_id)->first();
                if($topup_user){
                    $json_dist = $this->WalletCalculation->distributorDmt($txnid);
                    $toplevel_id = $topup_user->toplevel_id;
                    if (array_key_exists($toplevel_id, $dist)) {
                        
                    }else{
                        $dist[$toplevel_id] = 0;
                        $dist_tds[$toplevel_id] = 0;
                    }
                    $commission_dist = $json_dist['comm'];
                    $tds_dist = $json_dist['tds'];
                    $totalCommission_dist += $commission_dist;
                    $totaltds_dist += $tds_dist;
                    
                    $dist[$toplevel_id] = $dist[$toplevel_id] + $commission_dist;
                    $dist_tds[$toplevel_id] = $dist_tds[$toplevel_id] + $tds_dist;
                }
                
                $update = transactions_dmt::where('transaction_id',$txnid)->first();
                $update->comm_settl_dist = 1;
                $update->save();
                
            }
            
        }
        
        foreach($dist as $key => $r){
            echo $key.'='.$r;echo "<br>";
            echo $key.'='.$dist_tds[$key];echo "<br>";
            $user = User::find($key);
            $txnid_com = $this->WalletCalculation->txnId('COMM');
            $commission = $r;
            $tds = $dist_tds[$key];
            $wallets_uuid = $this->WalletCalculation->walletDepositFloat($key,$commission,"Commission",'Credit_Distributor_'.$user->mobile.'_DMT_Remittance_Commission',$txnid_com);
            
            $ins = new user_commissions();
            $ins->user_id = $key;
            $ins->transaction_id = $txnid_com;
            $ins->total_amount = $commission + $tds;
            $ins->amount = $commission;
            $ins->tds = $tds;
            $ins->tds_par = 0;
            $ins->wallets_uuid = $wallets_uuid;
            $ins->ref_transaction_id = 0;
            $ins->save();
        }
    }
    
    public function calculateUPI(Request $request) {
        
        // Fetch all users with their successful transactions from yesterday using eager loading
        $users = User::with(['transactionsDmt' => function ($query) {
            $query->where('status', '1')->where('comm_settl', '0')->where('event', 'SCANNPAY')
                  ->whereDate('created_at', Carbon::yesterday());
        }])->get();
        
        // Calculate commission for each transaction
        foreach ($users as $user) {
            $totalCommission = 0;
            $totalCommission_dist = 0;
            $totaltds = 0;
            $totaltds_dist = 0;
            $toplevel_id = 0;
            foreach ($user->transactionsDmt as $transaction) {
                $txnid = $transaction->transaction_id;
                $json = $this->WalletCalculation->retailorUpi($txnid);
                $commission = $json['comm'];
                $tds = $json['tds'];
                $totalCommission += $commission;
                $totaltds += $tds;
                $topup_user = user_levels::where('user_id',$transaction->user_id)->first();
                if($topup_user){
                    $json_dist = $this->WalletCalculation->distributorUpi($txnid);
                    $toplevel_id = $topup_user->toplevel_id;
                    $commission_dist = $json_dist['comm'];
                    $tds_dist = $json_dist['tds'];
                    $totalCommission_dist += $commission_dist;
                    $totaltds_dist += $tds_dist;
                }
                
                $update = transactions_dmt::where('transaction_id',$txnid)->first();
                $update->comm_settl = 1;
                $update->save();
                
            }
            
            if($totalCommission != 0){
                
            
            $txnid = $this->WalletCalculation->txnId('COMM');
            $userwallet = User::find($user->id);
            $wallet = $userwallet->wallet;
            $txn = $wallet->depositFloat($totalCommission,[
                'meta' => [
                    'Title' => 'Commission',
                    'detail' => 'Credit_Retailer_'.$user->mobile.'_SCAN_AND_PAY_Commission',
                    'transaction_id' => $txnid,
                ]
            ]);
            $balance_update = Transaction::where('uuid', $txn->uuid)->update(['balance' => $wallet->balance]);
            $topup_user = user_levels::where('user_id',$transaction->user_id)->first();
            $ins = new user_commissions();
            $ins->user_id = $user->id;
            $ins->transaction_id = $txnid;
            $ins->total_amount = $totalCommission + $totaltds;
            $ins->amount = $totalCommission;
            $ins->tds = $totaltds;
            $ins->tds_par = 0;
            $ins->wallets_uuid = $txn->uuid;
            $ins->ref_transaction_id = 0;
            $ins->save();
        
            if($topup_user){
                $txnid_dist = $this->WalletCalculation->txnId('COMM');
                $userwallet_dist = User::find($topup_user->toplevel_id);
                $wallet_dist = $userwallet_dist->wallet;
                $txn_dist = $wallet_dist->depositFloat($totalCommission_dist,[
                    'meta' => [
                        'Title' => 'Commission',
                        'detail' => 'Credit_Distributor_'.$userwallet_dist->mobile.'_SCAN_AND_PAY_Commission_Retailer_'.$user->mobile,
                        'transaction_id' => $txnid_dist,
                    ]
                ]);
                $balance_update_dist = Transaction::where('uuid', $txn_dist->uuid)->update(['balance' => $wallet_dist->balance]);
                
                $ins = new user_commissions();
                $ins->user_id = $userwallet_dist->id;
                $ins->transaction_id = $txnid_dist;
                $ins->total_amount = $totalCommission_dist + $totaltds_dist;
                $ins->amount = $totalCommission_dist;
                $ins->tds = $totaltds_dist;
                $ins->tds_par = 0;
                $ins->wallets_uuid = $txn_dist->uuid;
                $ins->ref_transaction_id = 0;
                $ins->save();
                
            }
            echo $totalCommission."#".$user->id."<br>";
            echo $totalCommission_dist."#".$toplevel_id."<br>";
            echo "==================================<br>";
            }
        }
        
        $balance_update_dist = transactions_dmt::where('status', 1)
                                ->where('comm_settl', '0')
                                ->where('event', 'SCANNPAY')
                                ->whereDate('created_at', Carbon::yesterday())
                                ->update(['comm_settl' => 1]);
    }
    
    public function calculateUPIDist(Request $request) {
        
        // Fetch all users with their successful transactions from yesterday using eager loading
        $users = User::with(['transactionsDmt' => function ($query) {
            $query->where('status', '1')->where('comm_settl', '0')->where('event', 'SCANNPAY')
                  ->whereDate('created_at', Carbon::yesterday());
        }])->get();
        
        // Calculate commission for each transaction
        foreach ($users as $user) {
            $totalCommission = 0;
            $totalCommission_dist = 0;
            $totaltds = 0;
            $totaltds_dist = 0;
            $toplevel_id = 0;
            foreach ($user->transactionsDmt as $transaction) {
                $txnid = $transaction->transaction_id;
                $json = $this->WalletCalculation->retailorUpi($txnid);
                $commission = $json['comm'];
                $tds = $json['tds'];
                $totalCommission += $commission;
                $totaltds += $tds;
                $topup_user = user_levels::where('user_id',$transaction->user_id)->first();
                if($topup_user){
                    $json_dist = $this->WalletCalculation->distributorUpi($txnid);
                    $toplevel_id = $topup_user->toplevel_id;
                    $commission_dist = $json_dist['comm'];
                    $tds_dist = $json_dist['tds'];
                    $totalCommission_dist += $commission_dist;
                    $totaltds_dist += $tds_dist;
                }
                
                
                $update = transactions_dmt::where('transaction_id',$txnid)->first();
                $update->comm_settl = 1;
                $update->save();
                
                
            }
            
            if($totalCommission_dist != 0){
            $topup_user = user_levels::where('user_id',$transaction->user_id)->first();
        
            if($topup_user){
                $txnid_dist = $this->WalletCalculation->txnId('COMM');
                $userwallet_dist = User::find($topup_user->toplevel_id);
                $wallet_dist = $userwallet_dist->wallet;
                $txn_dist = $wallet_dist->depositFloat($totalCommission_dist,[
                    'meta' => [
                        'Title' => 'Commission',
                        'detail' => 'Credit_Distributor_'.$userwallet_dist->mobile.'_SCAN_AND_PAY_Commission_Retailer_'.$user->mobile,
                        'transaction_id' => $txnid_dist,
                    ]
                ]);
                $balance_update_dist = Transaction::where('uuid', $txn_dist->uuid)->update(['balance' => $wallet_dist->balance]);
                
                $ins = new user_commissions();
                $ins->user_id = $userwallet_dist->id;
                $ins->transaction_id = $txnid_dist;
                $ins->total_amount = $totalCommission_dist + $totaltds_dist;
                $ins->amount = $totalCommission_dist;
                $ins->tds = $totaltds_dist;
                $ins->tds_par = 0;
                $ins->wallets_uuid = $txn_dist->uuid;
                $ins->ref_transaction_id = 0;
                $ins->save();
                
            }
            echo $totalCommission."#".$user->id."<br>";
            echo $totalCommission_dist."#".$toplevel_id."<br>";
            echo "==================================<br>";
            }
        }
    }
    
    public function callbackAirpay(Request $request) {
        $payload = $request->all();
        $callbackResponse = file_get_contents('php://input');
        
        Log::channel("airpay")->info("CallBack");
        Log::channel("airpay")->info($callbackResponse);
        Log::channel("airpay")->info($payload);
        
    }
    
    public function postCredopayOnboardingCallback(Request $request) {
        $payload = $request->all();
        $callbackResponse = file_get_contents('php://input');
        Log::channel('credopay')->info("CALLBACK MERCHANT ONBOARDING");
        Log::channel('credopay')->info($callbackResponse);
        
        $decode = json_decode($callbackResponse);
        if($decode->status->status == "approved"){
            
        
            $user = User::where('user_id',$decode->ref_id)->first();
            if($user){
                $check = eko_services::where('user_id',$user->id)->first();
                if(!$check){
                    $check = new eko_services();
                    $check->user_id = $user->id;
                }
                $check->credopay_cpid_mer = $decode->cpId;
                $check->credopay_aeps = 2;
                $check->save();
            }else{
                Log::channel('credopay')->info("MERCHANT NOT FOUND");
                Log::channel('credopay')->info($decode->ref_id);
            }
        }else{
            Log::channel('credopay')->info("MERCHANT NOT APPROVED");
            Log::channel('credopay')->info($decode->ref_id);
        }
    }
    public function postCredopayTerminalOnboardingCallback(Request $request) {
        $payload = $request->all();
        $callbackResponse = file_get_contents('php://input');
        Log::channel('credopay')->info("CALLBACK TERMINAL ONBOARDING");
        Log::channel('credopay')->info($callbackResponse);
        
        $decode = json_decode($callbackResponse);
        if($decode->status == "activated"){
            
        
            $user = User::where('mobile',$decode->mobile)->first();
            if($user){
                $check = eko_services::where('user_id',$user->id)->where('credopay_cpid_mer',$decode->merchant_cpId)->first();
                $check->credopay_cpid = $decode->cpId;
                $check->credopay_aeps = 1;
                $check->save();
            }else{
                Log::channel('credopay')->info("MERCHANT NOT FOUND");
                Log::channel('credopay')->info($decode->ref_id);
            }
        }else{
            Log::channel('credopay')->info("TERMINAL NOT APPROVED");
            Log::channel('credopay')->info($decode->ref_id);
        }
    }
    
    public function credoAepsTerminalUpdate(Request $request){
        $validator = Validator::make($request->all(), [
            "user_mobile" => "required"
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
        
        $mobile = $request->user_mobile;
        
        $user = User::where('mobile',$mobile)->first();
        $services = eko_services::where('user_id',$user->id)->whereNotNull('credopay_cpid_mer')->first();
            if($services){
                $token = user_login_tokens::where('user_id',$user->id)->first();
                if($token){
                    $add = Addresses::where('user_id',$user->id)->first();
                    $mobile = $mobile;
                    $user = User::where('mobile',$mobile)->first();;
                    $shop_details = shop_details::where('user_id',$user->id)->first();
                    $kyc_docs = kyc_docs::where('user_id',$user->id)->first();
                    $Addresses = Addresses::where('user_id',$user->id)->first();
                    $data = [
                                "merchantReferenceNumber" => $user->user_id,
                                "legalName" => $shop_details->shop_name,          // Legal name of the company
                                "address" => $shop_details->shop_address,            // Company address
                                "pincode" => $Addresses->pincode,            // Company pincode
                                "latitude" => $shop_details->latitude,           // Latitude coordinate
                                "longitude" => $shop_details->longitude,          // Longitude coordinate
                                "establishedYear" => Carbon::parse($shop_details->established)->format('d-m-Y'),    // Year of establishment
                                "pan_NO" => $kyc_docs->pan_number,             // PAN document number
                                "pan_URL" => "https://user.payritepayment.in/uploads/kycdocs/".$kyc_docs->pan_image."?mime=image/jpeg",            // URL to PAN document
                                "aadhar_NO" => $kyc_docs->aadhaar_number,          // Aadhar document number
                                "aadhar_URL_1" => "https://user.payritepayment.in/uploads/kycdocs/".$kyc_docs->aadhaar_front_image."?mime=image/jpeg",       // URL to Aadhar document part 1
                                "aadhar_URL_2" => "https://user.payritepayment.in/uploads/kycdocs/".$kyc_docs->aadhaar_back_image."?mime=image/jpeg",       // URL to Aadhar document part 2
                                "title" => $kyc_docs->name_title,              // Personal title (e.g., Mr., Mrs.)
                                "dob" => Carbon::parse($user->dob)->format('d-m-Y'),                // Date of birth
                                "firstName" => $user->name,          // First name
                                "lastName" => $user->surname,           // Last name
                                "personal_address" => $Addresses->address,   // Personal address
                                "personal_pincode" => $Addresses->pincode,   // Personal pincode
                                "mobile" => $user->mobile,             // Mobile number
                                "email" => $user->email,              // Email address
                                "bankAccountNumber" => $kyc_docs->bank_account,  // Bank account number
                                "ifsc" => $kyc_docs->bank_ifsc,               // IFSC code
                                "accountType" => $kyc_docs->bank_account_type,        // Account type (e.g., Savings, Current)
                                "CANCELLED_CHEQUE_NO" => $kyc_docs->cheque_number, // Cancelled cheque number
                                "CANCELLED_CHEQUE_URL" => "https://user.payritepayment.in/uploads/cheque/".$kyc_docs->cheque_image."?mime=image/jpeg",// URL to cancelled cheque image
                                "deviceModel" => $shop_details->device_model,        // Device model used $request->deviceModel
                                "deviceSerialNumber" => $shop_details->device_number  // Device serial number $request->deviceSerialNumber
                            ];
                    // $response = $this->CredoPayController->merchantUpdatingService($data,$services->credopay_cpid_mer);
                    $response = $this->CredoPayController->terminalUpdating($data,$services->credopay_cpid);
                    print_r($response);
                    echo "<br>";
                    echo $services->credopay_cpid_mer;
                    echo "<br>=============<br>";
                }
            }    
    }
    
    public function credoAepsMerchantOnboarding(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required"
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
        
        $mobile = $request->user_mobile;
        $user = User::where('mobile',$mobile)->first();;
        $shop_details = shop_details::where('user_id',$user->id)->first();
        $kyc_docs = kyc_docs::where('user_id',$user->id)->first();
        $Addresses = Addresses::where('user_id',$user->id)->first();
        $data = [
                    "merchantReferenceNumber" => $user->user_id,
                    "legalName" => $shop_details->shop_name,          // Legal name of the company
                    "address" => $shop_details->shop_address,            // Company address
                    "pincode" => $Addresses->pincode,            // Company pincode
                    "latitude" => $shop_details->latitude,           // Latitude coordinate
                    "longitude" => $shop_details->longitude,          // Longitude coordinate
                    "establishedYear" => Carbon::parse($shop_details->established)->format('d-m-Y'),    // Year of establishment
                    "pan_NO" => $kyc_docs->pan_number,             // PAN document number
                    "pan_URL" => "https://user.payritepayment.in/uploads/kycdocs/".$kyc_docs->pan_image."?mime=image/jpeg",            // URL to PAN document
                    "aadhar_NO" => $kyc_docs->aadhaar_number,          // Aadhar document number
                    "aadhar_URL_1" => "https://user.payritepayment.in/uploads/kycdocs/".$kyc_docs->aadhaar_front_image."?mime=image/jpeg",       // URL to Aadhar document part 1
                    "aadhar_URL_2" => "https://user.payritepayment.in/uploads/kycdocs/".$kyc_docs->aadhaar_back_image."?mime=image/jpeg",       // URL to Aadhar document part 2
                    "title" => $kyc_docs->name_title,              // Personal title (e.g., Mr., Mrs.)
                    "dob" => Carbon::parse($user->dob)->format('d-m-Y'),                // Date of birth
                    "firstName" => $user->name,          // First name
                    "lastName" => $user->surname,           // Last name
                    "personal_address" => $Addresses->address,   // Personal address
                    "personal_pincode" => $Addresses->pincode,   // Personal pincode
                    "mobile" => $user->mobile,             // Mobile number
                    "email" => $user->email,              // Email address
                    "bankAccountNumber" => $kyc_docs->bank_account,  // Bank account number
                    "ifsc" => $kyc_docs->bank_ifsc,               // IFSC code
                    "accountType" => $kyc_docs->bank_account_type,        // Account type (e.g., Savings, Current)
                    "CANCELLED_CHEQUE_NO" => $kyc_docs->cheque_number, // Cancelled cheque number
                    "CANCELLED_CHEQUE_URL" => "https://user.payritepayment.in/uploads/cheque/".$kyc_docs->cheque_image."?mime=image/jpeg",// URL to cancelled cheque image
                    "deviceModel" => $shop_details->device_model,        // Device model used $request->deviceModel
                    "deviceSerialNumber" => $shop_details->device_number  // Device serial number $request->deviceSerialNumber
                ];
        $response = $this->CredoPayController->merchantOnbording($data);
        $decode = json_decode($response);
        if($decode->status == 200){
            return response()->json(['success' => true, 'message' => $decode->message]);
        }else{
            return response()->json(['success' => false, 'message' => 'Something Wrong!!!']);
        }
    }
    
    public function credoAepsTerminalActivation(Request $request) {
        $response = $this->CredoPayController->terminalActivation($request->cpid);
        print_r($response);
    }
    
    public function credoAepsTerminalDeActivation(Request $request) {
        $response = $this->CredoPayController->terminalDeActivation($request->cpid);
        print_r($response);
    }
    
    public function postCredopayTransactionsCallback(Request $request) {
        $payload = $request->all();
        $callbackResponse = file_get_contents('php://input');
        Log::channel('credopay')->info("CALLBACK UPI-QR");
        Log::channel('credopay')->info($callbackResponse);
        $data = json_decode($callbackResponse);
        $status = $data->status;
        $cpid = $data->terminal_cpid;
        $amount = $data->amount;
        $transaction_type = $data->transaction_type;
        $network = $data->network;
        
        if($network == 'upi' || $network == 'upi'){
            if($transaction_type != 'sales'){
                return response()->json(['success' => false, 'message' => 'Something Wrong!!!']);
            }
            if(isset($data->CRN_U)){
                $transaction_id = $data->CRN_U;
            
                if($status == 'success'){
                    $services = eko_services::where('credopay_cpid',$cpid)->first();
                    if($services){
                        $ins = fund_onlines::where('user_id',$services->user_id)->where('amount',$amount)->where('transaction_id',$transaction_id)->where('status',4)->first();
                        if($ins){
                            if($amount <= 25000){
                                $fee_pr = 0.2;
                            }else{
                                $fee_pr = 0.25;
                            }
                            
                            $fee = $amount * $fee_pr / 100;
                            $final_amount = $amount - $fee;
                            
                            $ins->amount_txn = $final_amount;
                            $ins->fee = $fee;
                            if(isset($data->payer_vpa_id)){
                                $ins->card_number = $data->payer_vpa_id;
                            }
                            if(isset($data->app_type)){
                                $ins->card_type = $data->app_type;
                            }else{
                                $ins->card_type = 'NA';
                            }
                            
                            $ins->payment_method = 'upi';
                            
                            if(isset($data->bank_rrn)){
                                $ins->ref_id  = $data->bank_rrn;
                            }
                            $ins->pg_ref_id  = $data->rrn;
                            $ins->status = 1;
                            if(isset($data->payer_name)){
                                $ins->payer_name  = $data->payer_name;
                                $payer_name = $data->payer_name;
                            }else{
                                $payer_name = '';
                            }
                            
                            $ins->save();
                            
                            
                            $user_id = $services->user_id;
                            $user = User::find($user_id);
                            $transaction_id = $ins->transaction_id;
                            $this->WalletCalculation->walletDepositFloat($user_id,$amount,"UPI QR","Credit_".$user->mobile."UPI_QR_BY_".$payer_name,$transaction_id);
                            $this->WalletCalculation->walletWithdrawFloat($user_id,$fee,"UPI QR FEE","Credit_".$user->mobile."UPI_QR_FEE_$transaction_id",$transaction_id);
                        }
                    }
                }
            }
        }
        
        if($network != 'aeps'){
            if($data->response_description == 'APPROVED' && $status == 'status'){
                $transaction_id = $data->CRN_U;
            }
        }
    }
    
    public function credoAepsMerchantUpdate(Request $request) {
        $validator = Validator::make($request->all(), [
            "user_mobile" => "required"
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
        
        $mobile = $request->user_mobile;
        $user = User::where('mobile',$mobile)->first();;
        $shop_details = shop_details::where('user_id',$user->id)->first();
        $kyc_docs = kyc_docs::where('user_id',$user->id)->first();
        $Addresses = Addresses::where('user_id',$user->id)->first();
        $service = eko_services::where('user_id',$user->id)->first();
        $data = [
                    "merchantReferenceNumber" => $user->user_id,
                    "legalName" => $shop_details->shop_name,          // Legal name of the company
                    "address" => $shop_details->shop_address,            // Company address
                    "pincode" => $Addresses->pincode,            // Company pincode
                    "latitude" => $shop_details->latitude,           // Latitude coordinate
                    "longitude" => $shop_details->longitude,          // Longitude coordinate
                    "establishedYear" => Carbon::parse($shop_details->established)->format('d-m-Y'),    // Year of establishment
                    "pan_NO" => $kyc_docs->pan_number,             // PAN document number
                    "pan_URL" => "https://user.payritepayment.in/uploads/kycdocs/".$kyc_docs->pan_image."?mime=image/jpeg",            // URL to PAN document
                    "aadhar_NO" => $kyc_docs->aadhaar_number,          // Aadhar document number
                    "aadhar_URL_1" => "https://user.payritepayment.in/uploads/kycdocs/".$kyc_docs->aadhaar_front_image."?mime=image/jpeg",       // URL to Aadhar document part 1
                    "aadhar_URL_2" => "https://user.payritepayment.in/uploads/kycdocs/".$kyc_docs->aadhaar_back_image."?mime=image/jpeg",       // URL to Aadhar document part 2
                    "title" => $kyc_docs->name_title,              // Personal title (e.g., Mr., Mrs.)
                    "dob" => Carbon::parse($user->dob)->format('d-m-Y'),                // Date of birth
                    "firstName" => $user->name,          // First name
                    "lastName" => $user->surname,           // Last name
                    "personal_address" => $Addresses->address,   // Personal address
                    "personal_pincode" => $Addresses->pincode,   // Personal pincode
                    "mobile" => $user->mobile,             // Mobile number
                    "email" => $user->email,              // Email address
                    "bankAccountNumber" => $kyc_docs->bank_account,  // Bank account number
                    "ifsc" => $kyc_docs->bank_ifsc,               // IFSC code
                    "accountType" => $kyc_docs->bank_account_type,        // Account type (e.g., Savings, Current)
                    "CANCELLED_CHEQUE_NO" => $kyc_docs->cheque_number, // Cancelled cheque number
                    "CANCELLED_CHEQUE_URL" => "https://user.payritepayment.in/uploads/cheque/".$kyc_docs->cheque_image."?mime=image/jpeg",// URL to cancelled cheque image
                    "deviceModel" => $shop_details->device_model,        // Device model used $request->deviceModel
                    "deviceSerialNumber" => $shop_details->device_number  // Device serial number $request->deviceSerialNumber
                ];
        $response = $this->CredoPayController->merchantUpdating($data,$service->credopay_cpid_mer);
        $decode = json_decode($response);
        if($decode->status == 200){
            return response()->json(['success' => true, 'message' => $decode->Message]);
        }else{
            return response()->json(['success' => false, 'message' => 'Something Wrong!!!']);
        }
    }
    
    public function credoAepsTerminalOnboarding(Request $request) {
        $mobile = '9099956721';
        $user = User::where('mobile',$mobile)->first();;
        $shop_details = shop_details::where('user_id',$user->id)->first();
        $kyc_docs = kyc_docs::where('user_id',$user->id)->first();
        $Addresses = Addresses::where('user_id',$user->id)->first();
        $eko_services = eko_services::where('user_id',$user->id)->first();
        
        $data = [
                    "cpid" => $eko_services->credopay_cpid_mer,
                    "deviceSerialNumber" =>"2024001",
                    "mobile" => $user->mobile,
                    "firstName" => $user->name,
                    "latitude" => $shop_details->latitude,
                    "longitude" => $shop_details->longitude,
                    "pincode" => $Addresses->pincode,
                    "address" => $Addresses->address,
                ];
        $response = $this->CredoPayController->terminalOnboarding($data);
        print_r($response);
    }
    
    public function credoAepsBanks(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required"
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $banks = banks::select("name","credopay_id")->whereNotNull('credopay_id')->get();
            return response()->json(['success' => true, 'message' => 'Banks', 'banks' => $banks]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function credoAepsCheckFa(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required"
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $user_id = $user->id;
            $check = eko_services::where('user_id',$user_id)->first();
            if($check){
                if(empty($check->credopay_cpid)){
                    return response()->json(['success' => false, 'message' => 'Onboard As A Merchant Or Service Under Review.']);
                }
                $data = ["cpid"=>$check->credopay_cpid];
                $response = $this->CredoPayController->checkFa($data);
                $decode = json_decode($response);
                if($decode->aeps_twofactorauth){
                    return response()->json(['success' => true, 'message' => 'Authntication Complete', 'auth' => '1']);
                }else{
                    return response()->json(['success' => true, 'message' => 'Authntication Required', 'auth' => '0']);
                }
            }else{
                return response()->json(['success' => false, 'message' => 'Onboard As A Merchant.']);
            }
            
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
        
    }
    
    public function credoAepsFa(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required",
            "PidData" => "required",
            "mi" => "required",
            "rdsVer" => "required",
            "rdsId" => "required",
            "srno" => "required",
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
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;
        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $user_id = $user->id;
            $check = eko_services::where('user_id',$user_id)->first();
            $kyc_docs = kyc_docs::where('user_id',$user->id)->first();
            $aadhaar_number = $kyc_docs->aadhaar_number;
            $cpid = $check->credopay_cpid;
            $PidData = $request->PidData;
            $mi = $request->mi;
            $rdsVer = $request->rdsVer;
            $rdsId = $request->rdsId;
            $srno = $request->srno;
            $PidData = str_replace(["\r", "\n", "\t","  "], '', $PidData);
            $PidData = str_replace('"', '\"', $PidData);
            $data = ["cpid"=>$cpid,
                    "PidData"=>$PidData,
                    "mi"=>$mi,
                    "rdsVer"=>$rdsVer,
                    "rdsId"=>$rdsId,
                    "srno"=>$srno,"aadhaar_number"=>$aadhaar_number];
            $response = $this->CredoPayController->faAuth($data);
            $decode = json_decode($response);
            if(isset($decode->response_code)){
                if($decode->response_code == 00){
                    return response()->json(['success' => true, 'message' => 'Authntication Complate.']);
                }else{
                    return response()->json(['success' => false, 'message' => 'Error With Aunthintication.']);
                }
            }else{
                return response()->json(['success' => false, 'message' => 'Error With Aunthintication.']);
            }
            
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
        
    }
    
    public function credoAepsDoTransactions(Request $request) {
        
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required",
            "PidData" => "required",
            "mi" => "required",
            "rdsVer" => "required",
            "rdsId" => "required",
            "srno" => "required",
            "amount" => "required",
            "transaction_type" => "required",
            "bank_name" => "required",
            "aadhar" => "required",
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
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;
        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $user_id = $user->id;
            $user_id_credo = $user->user_id;
            $check = eko_services::where('user_id',$user_id)->first();
            $kyc_docs = kyc_docs::where('user_id',$user->id)->first();
            $aadhaar_number = $request->aadhar;
            $cpid = $check->credopay_cpid;
            $PidData = $request->PidData;
            $mi = $request->mi;
            $rdsVer = $request->rdsVer;
            $rdsId = $request->rdsId;
            $srno = $request->srno;
            
            $amount = $request->amount;
            $transaction_type = $request->transaction_type;
            $bank_name = $request->bank_name;
            $txn_id = $this->WalletCalculation->txnId('AEPS');
            
            $transactions = new transactions_aeps();
            $transactions->user_id = $user_id;
            $transactions->transaction_id = $txn_id;
            $transactions->vendor_id = $user_id_credo;
            $transactions->outlet_id = $user_id_credo;
            $transactions->bank_iin = $bank_name;
            $transactions->amount = $amount;
            $transactions->event = "AEPSTXN";
            $transactions->aadhaar = $aadhaar_number;
            $transactions->remitterName = $user->name;
            $transactions->transfer_type = $transaction_type;
            $transactions->status = 0;
            $transactions->save();
            
            $PidData = str_replace(["\r", "\n", "\t","  "], '', $PidData);
            $PidData = str_replace('"', '\"', $PidData);
            $data = ["cpid"=>$cpid,
                    "PidData"=>$PidData,
                    "mi"=>$mi,
                    "rdsVer"=>$rdsVer,
                    "rdsId"=>$rdsId,
                    "srno"=>$srno,
                    "aadhaar_number"=>$aadhaar_number,
                    "amount"=>$amount,
                    "transaction_type"=>$transaction_type,
                    "bank_name"=>$bank_name,
                    "user_id"=>$user_id_credo,
                    "transaction_id"=>$txn_id];
            $response = $this->CredoPayController->transactions($data);
            $decode = json_decode($response);
            if(isset($decode->response_code)){
                if($decode->response_code == 00){
                    $aadhaar_number_mask = substr($aadhaar_number, 0, 6) . 'XXXX' . substr($aadhaar_number, -2);
                    if($decode->transaction_type == 'balance_enquiry'){
                        $txn_add = transactions_aeps::find($transactions->id);
                        $txn_add->status = 1;
                        $txn_add->utr = $decode->rrn;
                        $txn_add->ben_name = $decode->customer_name;
                        $txn_add->referenceId = $decode->transaction_id;
                        $txn_add->save();
                        return response()->json(['success' => true, 'message' => 'Success', 'aadhaar_number'=>$aadhaar_number_mask, 'created_at'=>$decode->created_at, 'transaction_id'=>$decode->transaction_id, 'balance'=>$decode->balance, 'transaction_type'=>$decode->transaction_type, 'name'=>$decode->customer_name, 'CRN_U'=>$decode->CRN_U, 'rrn'=>$decode->rrn]);
                    }
                    
                    if($decode->transaction_type == 'mini_statement'){
                        $txn_add = transactions_aeps::find($transactions->id);
                        $txn_add->status = 1;
                        $txn_add->utr = $decode->rrn;
                        $txn_add->ben_name = $decode->customer_name;
                        $txn_add->referenceId = $decode->transaction_id;
                        $txn_add->save();
                        
                        //Commission Start
                        $json_com = $this->WalletCalculation->retailorAeps($txn_id);
                        $commission = $json_com['comm'];
                        $tds = $json_com['tds'];
                        $txnid_com = $this->WalletCalculation->txnId('COMM');
                        
                        $user = User::find($transactions->user_id);
                        $wallets_uuid = $this->WalletCalculation->walletDepositFloat($transactions->user_id,$commission,"Commission",'Credit_Retailer_'.$user->mobile.'_AEPS_Commission',$txnid_com);
                        
                        $ins = new user_commissions();
                        $ins->user_id = $transactions->user_id;
                        $ins->transaction_id = $txnid_com;
                        $ins->total_amount = $commission + $tds;
                        $ins->amount = $commission;
                        $ins->tds = $tds;
                        $ins->tds_par = 0;
                        $ins->wallets_uuid = $wallets_uuid;
                        $ins->ref_transaction_id = $transactions->transaction_id;
                        $ins->save();
                        
                        $update = transactions_aeps::find($transactions->id);
                        $update->comm_settl = 1;
                        $update->save();
                        //Commission end
                        
                        return response()->json(['success' => true, 'message' => 'Success', 'aadhaar_number'=>$aadhaar_number_mask, 'transaction_type'=>$decode->transaction_type, 'transaction_id'=>$decode->transaction_id, 'created_at'=>$decode->created_at, 'name'=>$decode->customer_name, 'CRN_U'=>$decode->CRN_U, 'rrn'=>$decode->rrn, 'mini_statement'=>$decode->mini_statement]);
                    }
                    
                    if($decode->transaction_type == 'cash_withdrawal'){
                        
                        $response = $this->CredoPayController->transactionsComplete($decode->transaction_id);
                        
                        
                        aepscashwh:
                        $userwallet = User::find($transactions->user_id);
                        $wallet = $userwallet->wallet;
                        
                        // $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
                        // if($balance_check->balance != $userwallet->wallet->balance){
                        //     Log::channel("balancemissmatch")->info("========");
                        //     Log::channel("balancemissmatch")->info($wallet->id);
                        //     Log::channel("balancemissmatch")->info($balance_check->balance);
                        //     Log::channel("balancemissmatch")->info($userwallet->wallet->balance);
                        //     goto aepscashwh;
                        // }
                        
                        // $txn_wl = $wallet->depositFloat($amount,[
                        //     'meta' => [
                        //         'Title' => 'AEPS',
                        //         'detail' => 'Credit_Retailer_'.$user->mobile.'_AEPS_Amount_'.$aadhaar_number_mask,
                        //         'transaction_id' => $txn_id,
                        //     ]
                        // ]);
                        // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        
                        $wallets_uuid = $this->WalletCalculation->walletDepositFloat($transactions->user_id,$amount,"AEPS",'Credit_Retailer_'.$user->mobile.'_AEPS_Amount_'.$aadhaar_number_mask,$txn_id);
                        
                        $txn_add = transactions_aeps::find($transactions->id);
                        $txn_add->status = 1;
                        $txn_add->utr = $decode->rrn;
                        $txn_add->ben_name = $decode->customer_name;
                        $txn_add->wallets_uuid = $wallets_uuid;
                        $txn_add->referenceId = $decode->transaction_id;
                        if(isset($decode->balance)){$txn_add->available_balance = $decode->balance;}
                        $txn_add->save();
                        
                        //Commission Start
                        $json_com = $this->WalletCalculation->retailorAeps($txn_id);
                        $commission = $json_com['comm'];
                        $tds = $json_com['tds'];
                        $txnid_com = $this->WalletCalculation->txnId('COMM');
                        
                        $user = User::find($transactions->user_id);
                        $wallets_uuid = $this->WalletCalculation->walletDepositFloat($transactions->user_id,$commission,"Commission",'Credit_Retailer_'.$user->mobile.'_AEPS_Commission',$txnid_com);
                        
                        $ins = new user_commissions();
                        $ins->user_id = $transactions->user_id;
                        $ins->transaction_id = $txnid_com;
                        $ins->total_amount = $commission + $tds;
                        $ins->amount = $commission;
                        $ins->tds = $tds;
                        $ins->tds_par = 0;
                        $ins->wallets_uuid = $wallets_uuid;
                        $ins->ref_transaction_id = $transactions->transaction_id;
                        $ins->save();
                        
                        $update = transactions_aeps::find($transactions->id);
                        $update->comm_settl = 1;
                        $update->save();
                        //Commission end
                        
                        return response()->json(['success' => true, 'message' => 'Success', 'available_balance'=>$decode->balance, 'aadhaar_number'=>$aadhaar_number_mask, 'created_at'=>$decode->created_at, 'transaction_id'=>$decode->transaction_id, 'transaction_amount'=>$decode->transaction_amount, 'transaction_type'=>$decode->transaction_type, 'name'=>$decode->customer_name, 'CRN_U'=>$decode->CRN_U, 'rrn'=>$decode->rrn]);
                    }
                    
                }else{
                    
                    return response()->json(['success' => false, 'message' => 'Error']);
                }
            }else{
                if($transaction_type == 'balance_enquiry'){
                    $txn_add = transactions_aeps::find($transactions->id);
                    $txn_add->status = 2;
                    if(isset($decode->message)){
                        $msgs = $decode->message;
                        $txn_add->reason = $decode->message;
                    }else{
                        $msgs = $decode->response_description;
                        // $txn_add->utr = $decode->rrn;
                        $txn_add->reason = $decode->response_description;
                        $txn_add->referenceId = $decode->transaction_id;
                    }
                    
                    
                    $txn_add->save();
                    return response()->json(['success' => false, 'message' => $msgs]);
                }
                if($transaction_type == 'mini_statement'){
                    $txn_add = transactions_aeps::find($transactions->id);
                    $txn_add->status = 2;
                    if(isset($decode->message)){
                        $msgs = $decode->message;
                        $txn_add->reason = $decode->message;
                    }else{
                        $msgs = $decode->response_description;
                        // $txn_add->utr = $decode->rrn;
                        $txn_add->reason = $decode->response_description;
                        $txn_add->referenceId = $decode->transaction_id;
                    }
                    
                    
                    $txn_add->save();
                    return response()->json(['success' => false, 'message' => $msgs]);
                }
                if($transaction_type == 'cash_withdrawal'){
                    $txn_add = transactions_aeps::find($transactions->id);
                    $txn_add->status = 2;
                    if(isset($decode->message)){
                        $msgs = $decode->message;
                        $txn_add->reason = $decode->message;
                    }else{
                        $msgs = $decode->response_description;
                        // $txn_add->utr = $decode->rrn;
                        $txn_add->reason = $decode->response_description;
                        if(isset($decode->transaction_id)){
                            $txn_add->referenceId = $decode->transaction_id;
                            
                        }
                    }
                    $txn_add->save();
                    return response()->json(['success' => false, 'message' => $msgs]);
                }
                return response()->json(['success' => false, 'message' => 'Error']);
            }
            
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
        
    }
    
    public function testCron(Request $request) {
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://cyrusrecharge.in/services_cyapi/Payout_cyapi.aspx',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array('MerchantID' => 'AP487545','MerchantKey' => 'cIs8S3~Vg2Mtmn69691','MethodName' => 'paytransfer','orderId' => 'PAYRITE'.date("YmdHis"),'Name' => 'Amit','amount' => '25000','MobileNo' => '8140666688','TransferType' => 'UPI','beneficiaryid' => 'CY_lQbMyRpHBwvI9V','remarks' => 'Developer'),
          CURLOPT_HTTPHEADER => array(
            'Cookie: ASP.NET_SessionId=bnspst2msqw4w3tgiuoltmzf'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        echo $response;
        exit;
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://cyrusrecharge.in/api/Beneficiary_API.aspx',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array('MerchantID' => 'AP487545','MerchantKey' => 'cIs8S3~Vg2Mtmn69691','MethodName' => 'GET_BENEFICIARY','pay_type' => 'vpa','beneficiary_vpa' => 'amitrkanani-1@okhdfcbank','beneficiary_name' => 'Amit Kanani','beneficiary_email' => 'amitrdeveloper@gmail.com','beneficiary_phone' => '8140666688','beneficiary_pan' => 'ALVPJ3781F','beneficiary_aadhar' => '435148294845','is_agreement_with_beneficiary' => 'YES','beneficiary_address' => '{"line": "rajkot","area": "rajkot","city": "rajkot","district": "rajkot","state": "gujarat","pincode": "360005"}','beneficiary_verification_status' => 'YES','bene_type' => 'EMPLOYEE','latlong' => '132,321'),
          CURLOPT_HTTPHEADER => array(
            'Cookie: ASP.NET_SessionId=bnspst2msqw4w3tgiuoltmzf'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        echo $response;


        exit;
        $users= "222";
        $user = explode(",",$users);
        $balances = "5488.92";
        $balance = explode(",",$balances);
        
        for($i=0;$i<count($user);$i++){
            echo $user[$i].'#'.$balance[$i]."<br>";
            
            // $user_debit = $this->WalletCalculation->walletWithdrawFloat($user[$i],$balance[$i],"BALANCE_CORRECTION","7th_FEB_BALANCE_CORRECTION",$user[$i]);
            // $user_debit = $this->WalletCalculation->walletDepositFloat($user[$i],$balance[$i],"BALANCE_CORRECTION","7th_FEB_BALANCE_CORRECTION",$user[$i]);
        }
        exit;
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://stageapi.digikhata.in/P2A/v1/generateotp',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
           "walletaccreatorcode":"RT01",
           "walletaccreatorpincode":"360005",
           "walletaccreatorname":"Agent Name",
           "mobilenumber":"8140666688"
        }',
          CURLOPT_HTTPHEADER => array(
            'AppID: PPINL008',
            'AuthKey: M9f$Gqz2LpD1@wV7uBzXsJ8hT&N3rF0KaQ6oY',
            'SecretKey: Z8qRpX$B5jH2kGvLtT1y6mFw7pC*I9zVsW0Dd',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        echo $response;

        exit;
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'www.rkwallet.in/Admin/RechargeAPI.aspx?UserID=9978177178&Password=43891910&MobileNo=9978177178&Message=JIO$9725127297$20$9086$0$RC19012406',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Cookie: ASP.NET_SessionId=5s4f3hd2to0ets3ujitr0f1e'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        echo $response;
        exit;
        $qrCode = base64_encode(QrCode::format('png')
            ->size(200)
            ->generate($request->input('data', 'Default Text')));
        echo "<img src='data:image/png;base64,$qrCode' />";exit;
        //return response()->json(['success' => false, 'message' => 'data:image/png;base64,' . $qrCode]);
        // $data = array("transaction_id"=>"TUSUPIQR0201202403","user_id"=>"RT240524224894","amount"=>10,"cpid"=>"CPST29010136561");
        // return $this->CredoPayController->transactionsUpi($data);
        // exit;
        $users = User::where('user_type',2)->get();
        foreach($users as $r){
            $services = eko_services::where('user_id',$r->id)->whereNotNull('credopay_cpid_mer')->first();
            if($services){
                $token = user_login_tokens::where('user_id',$r->id)->first();
                if($token){
                    $add = Addresses::where('user_id',$r->id)->first();
                    $mobile = $r->mobile;
                    $user = User::where('mobile',$mobile)->first();;
                    $shop_details = shop_details::where('user_id',$user->id)->first();
                    $kyc_docs = kyc_docs::where('user_id',$user->id)->first();
                    $Addresses = Addresses::where('user_id',$user->id)->first();
                    $data = [
                                "merchantReferenceNumber" => $user->user_id,
                                "legalName" => $shop_details->shop_name,          // Legal name of the company
                                "address" => $shop_details->shop_address,            // Company address
                                "pincode" => $Addresses->pincode,            // Company pincode
                                "latitude" => $shop_details->latitude,           // Latitude coordinate
                                "longitude" => $shop_details->longitude,          // Longitude coordinate
                                "establishedYear" => Carbon::parse($shop_details->established)->format('d-m-Y'),    // Year of establishment
                                "pan_NO" => $kyc_docs->pan_number,             // PAN document number
                                "pan_URL" => "https://user.payritepayment.in/uploads/kycdocs/".$kyc_docs->pan_image."?mime=image/jpeg",            // URL to PAN document
                                "aadhar_NO" => $kyc_docs->aadhaar_number,          // Aadhar document number
                                "aadhar_URL_1" => "https://user.payritepayment.in/uploads/kycdocs/".$kyc_docs->aadhaar_front_image."?mime=image/jpeg",       // URL to Aadhar document part 1
                                "aadhar_URL_2" => "https://user.payritepayment.in/uploads/kycdocs/".$kyc_docs->aadhaar_back_image."?mime=image/jpeg",       // URL to Aadhar document part 2
                                "title" => $kyc_docs->name_title,              // Personal title (e.g., Mr., Mrs.)
                                "dob" => Carbon::parse($user->dob)->format('d-m-Y'),                // Date of birth
                                "firstName" => $user->name,          // First name
                                "lastName" => $user->surname,           // Last name
                                "personal_address" => $Addresses->address,   // Personal address
                                "personal_pincode" => $Addresses->pincode,   // Personal pincode
                                "mobile" => $user->mobile,             // Mobile number
                                "email" => $user->email,              // Email address
                                "bankAccountNumber" => $kyc_docs->bank_account,  // Bank account number
                                "ifsc" => $kyc_docs->bank_ifsc,               // IFSC code
                                "accountType" => $kyc_docs->bank_account_type,        // Account type (e.g., Savings, Current)
                                "CANCELLED_CHEQUE_NO" => $kyc_docs->cheque_number, // Cancelled cheque number
                                "CANCELLED_CHEQUE_URL" => "https://user.payritepayment.in/uploads/cheque/".$kyc_docs->cheque_image."?mime=image/jpeg",// URL to cancelled cheque image
                                "deviceModel" => $shop_details->device_model,        // Device model used $request->deviceModel
                                "deviceSerialNumber" => $shop_details->device_number  // Device serial number $request->deviceSerialNumber
                            ];
                    // $response = $this->CredoPayController->merchantUpdatingService($data,$services->credopay_cpid_mer);
                    $response = $this->CredoPayController->terminalUpdating($Addresses->pincode,$services->credopay_cpid);
                    print_r($response);
                    echo "<br>";
                    echo $services->credopay_cpid_mer;
                    echo "<br>=============<br>";
                }
            }
        }
        
        // $url = "https://user.payritepayment.in/api/v1/dmt/aepsActivateService?user_mobile=9924727484&token=$2y$10$9M8V8WpqWytqV2OZsDWv5OBSVUfE83NHki2aC5iFl2sQ9IBZuiWiq";
        // $response = $this->ApiCalls->payritePostCall($url);
        //merchantUpdatingService
        // return $this->CredoPayController->merchantUpdatingUAT(1,1);
        // return $this->CredoPayController->terminalUpdatingUAT(1,1);
        // return $this->CredoPayController->transactionsUpi(1);
    }
    
    public function callbackSafexpay(Request $request) {
        $payload = $request->all();
        $callbackResponse = file_get_contents('php://input');
        Log::channel('safexpay')->info("CALLBACK");
        Log::channel('safexpay')->info($payload['payload']);
        $dec = $this->SafexPayController->decryptData("xVHYj9hwm9J9f6AohY5Wc6TKNVhYnoLTSRoUM8/xqRQ=",$payload['payload']);
        Log::channel('safexpay')->info($dec);
        
        $data = json_decode($dec);
        $status = $data->transactionDetails->statusCode;
        
        if($status == "0000") {
            $txn_id = $data->transactionDetails->orderRefNo;
            $utr = $data->transactionDetails->bankRefNo;
            
            $transaction = transactions_dmt::where('transaction_id',$txn_id)->where('status',0)->first();
            
            if($transaction){
                $amount = $transaction->amount;
                $mobile = $transaction->mobile;
                $transaction->status = 1;
                $transaction->utr = $utr;
                $transaction->save();
                // $this->WalletCalculation->distributorDmt($txn_id);
                // $this->WalletCalculation->retailorDmt($txn_id);
                $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txn_id on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                    $api_response = $this->ApiCalls->smsgatewayhubGetCall($mobile,$text,1207172205377529606);
            }
            return response()->json(['success' => true, 'message' => 'success']);
        }
        $status_code_fail = array('0002','0003','0008');
        if(in_array($status, $status_code_fail)) {
            $txn_id = $data->transactionDetails->orderRefNo;
            $utr = $data->transactionDetails->bankRefNo;
            $response_reason = '';
            
            $transaction = transactions_dmt::where('transaction_id',$txn_id)->where('status',0)->first();
            
            if($transaction){
                $amount = $transaction->amount;
                $fee = $transaction->fee;
                $userwallet = User::find($transaction->user_id);
                $wallet = $userwallet->wallet;
                $transfer_type = $transaction->transfer_type;
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$transaction->mobile,$txn_id);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($userwallet->id,$amount,"Money Transfer",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$transaction->mobile,$txn_id);
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$transaction->mobile,
                    //         'transaction_id' => $txn_id,
                    //     ]
                    // ]);
                    // $balance_update_fee = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$transaction->mobile,
                    //         'transaction_id' => $txn_id,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $transaction->status = 2;
                    $transaction->utr = $utr;
                    $transaction->response_reason = $response_reason;
                    $transaction->save();
            }
        }
        
        return response()->json(['success' => true, 'message' => 'OK']);
    }
    
    public function dmtStatusTxnFail(Request $request) {
        $recentTime = Carbon::now()->subMinutes(3);
        $transactions = transactions_dmt::where('eko_status',6)->where('status',0)->whereIn('api_id',[1])->where('created_at', '<=', $recentTime)->get();
        if($transactions){
            // $user_debit = $this->WalletCalculation->walletDepositFloat($user->id,10,"eKYC FEE","Refund_ekyc_fee_sender_$mobile",$aadhar);
            foreach($transactions as $r){
                log::info("AUTO FAIL DMT2 ".$r->transaction_id);
                $userwallet = User::find($r->user_id);
                $amount = $r->amount;
                $fee = $r->fee;
                $transfer_type = $r->transfer_type;
                
                
                $post_sender = [
                    "mobile" => $r->mobile,
                    "bank_id" => $r->bank_channel,
                ];
                $result_sender = $this->BillavenueController->DmtCustomerLogin($post_sender);
                $json_sender = json_decode($result_sender);
                
                $post = [
                    "customer_id" => $r->mobile,
                    "recipient_id" => $r->eko_beneficiary_id,
                    "bank_id" => $r->bank_channel,
                ];
                $result_benf = $this->BillavenueController->DmtGetBeneficiary($post);
                $json_bnef = json_decode($result_benf);
                
                if(is_string($json_bnef->recipientList->dmtRecipient->recipientName)){
                    $recipientName = $json_bnef->recipientList->dmtRecipient->recipientName;
                }else{
                    $recipientName = ".";
                }
            
                $transaction_up = transactions_dmt::where('transaction_id',$r->transaction_id)->where('status',0)->first();
                $transaction_up->status = 2;
                $transaction_up->ben_name = $recipientName;
                $transaction_up->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                $transaction_up->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                $transaction_up->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                $transaction_up->sender_name = $json_sender->senderName;
                $transaction_up->save();
                
                $user_debit = $this->WalletCalculation->walletDepositFloat($userwallet->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$r->mobile,$r->transaction_id);
                $user_debit = $this->WalletCalculation->walletDepositFloat($userwallet->id,$amount,"Money Transfer Transfer",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$r->mobile,$r->transaction_id);
            }
        }
    }
    
    public function dmt3StatusTxnFail(Request $request) {
        $recentTime = Carbon::now()->subMinutes(10);
        $transactions = transactions_dmt::where('eko_status',6)->where('status',0)->whereIn('api_id',[3])->where('created_at', '<=', $recentTime)->get();
        if($transactions){
            // $user_debit = $this->WalletCalculation->walletDepositFloat($user->id,10,"eKYC FEE","Refund_ekyc_fee_sender_$mobile",$aadhar);
            foreach($transactions as $r){
                $userwallet = User::find($r->user_id);
                $amount = $r->amount;
                $fee = $r->fee;
                $transfer_type = $r->transfer_type;
                

            
                $transaction_up = transactions_dmt::where('transaction_id',$r->transaction_id)->where('status',0)->first();
                $transaction_up->status = 2;
                $transaction_up->save();
                
                $user_debit = $this->WalletCalculation->walletDepositFloat($r->user_id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$r->mobile,$r->transaction_id);
                $user_debit = $this->WalletCalculation->walletDepositFloat($r->user_id,$amount,"Money Transfer Transfer",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$r->mobile,$r->transaction_id);
            }
        }
    }
    
    public function qrOtpSend(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "name" => "required",
            "surname" => "required",
            "mobile" => "required|regex:/^[0-9]{10}$/",
            "amount" => "required",
            
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        if($response){
            $buyerPhone = trim($request->mobile);
    	    $buyerFirstName = trim($request->name);
    	    $buyerLastName = trim($request->surname);
    	    $amount = trim($request->amount);
    	    $user = User::where('mobile',$request->user_mobile)->first();
    	    $orderid = trim($this->UserAuth->txnId('QR')); //Your System Generated Order ID
    	    $otp = rand(100000, 999999);
    	    $ins = new fund_onlines();
            $ins->user_id = $user->id;
            $ins->transaction_id = $orderid;
            $ins->amount = $amount;
            $ins->cust_name = $buyerFirstName;
            $ins->cust_surname = $buyerLastName;
            $ins->cust_mobile = $buyerPhone;
            $ins->pg_id = 3;
            $ins->status = 3;
            $ins->otp = $otp;
            $ins->save();
            
            $text = urlencode("Dear User, Use this OTP $otp to log in to your PAYRITE account. This OTP will be valid for the next 5 mins. https://payritepayment.in/");
            $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->mobile,$text,1207170972278643017);
            
            return response()->json(['success' => true, 'message' => 'Otp Send', 'transaction_id'=>$orderid]);
        }else{
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function qrOtpVerify(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "otp" => "required",
            "transaction_id" => "required",
            
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
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        if($response){
            $ins = fund_onlines::where('transaction_id',$request->transaction_id)->where('otp',$request->otp)->first();
            if($ins){
                $ins->status = 4;
                $ins->save();
                $user = User::where('mobile',$request->user_mobile)->first();
                
                $services = eko_services::where('user_id',$user->id)->whereNotNull('credopay_cpid')->first();
                if($services){
                    $data = array("transaction_id"=>$request->transaction_id,"user_id"=>$user->user_id,"amount"=>$ins->amount,"cpid"=>$services->credopay_cpid);
                    $result =  $this->CredoPayController->transactionsUpi($data);
                    $json = json_decode($result);
                    if(isset($json->qr_code_text)){
                        $qrCode = base64_encode(QrCode::format('png')
                                    ->size(200)
                                    ->generate($request->input('data', $json->qr_code_text)));
                        
                        return response()->json(['success' => true, 'message' => 'Pay Using QR', 'qr'=>'data:image/png;base64,' . $qrCode]);
                    }
                }else{
                    return response()->json(['success' => false, 'message' => 'Verification Failed! UPI Not Active!']);
                }
                
                
            }else{
                return response()->json(['success' => false, 'message' => 'Verification Failed!']);
            }
        }else{
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function qrTransactionStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "transaction_id" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $transaction_id = $request->transaction_id;
            $data = fund_onlines::select('status')->where('transaction_id',$transaction_id)->first();
            
            return response()->json(['success' => true, 'message' => '', 'status'=>$data->status]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function callbackRkwallet(Request $request) {
        Log::channel('rkwallet')->info("RK CALLBACK");
        Log::channel('rkwallet')->info($request->all());
        Log::channel('rkwallet')->info($request->ST);
        
        $txn = transactions_recharges::where('transaction_id',$request->TNO)->whereIn('status',[0,1])->first();
        if($txn){
            if($request->ST == 1){
                $txn->status = 1;
                $txn->referenceId = $request->OPRTID;
                $txn->save();
                
                $comm = $this->WalletCalculation->rechargeSlab($txn->transaction_id);
                
                $txnid = $this->WalletCalculation->txnId('COMM');
                $detais = 'Credit_Retailer_'.$txn->transaction_id.'_RECHARGE_Commission';
                $user_credit = $this->WalletCalculation->walletDepositFloat($comm['user_id'],$comm['comm'],"Recharge Commission",$detais,$txnid);
                $ins = new user_commissions();
                $ins->user_id = $comm['user_id'];
                $ins->transaction_id = $txnid;
                $ins->total_amount = $comm['comm'] + $comm['tds'];
                $ins->amount = $comm['comm'];
                $ins->tds = $comm['tds'];
                $ins->tds_par = 0;
                $ins->wallets_uuid = $user_credit;
                $ins->ref_transaction_id = 0;
                $ins->save();
                
                $txnid_dist = $this->WalletCalculation->txnId('COMM');
                $detais = 'Credit_Distributor_'.$txn->transaction_id.'_RECHARGE_Commission';
                $user_credit = $this->WalletCalculation->walletDepositFloat($comm['user_id_dist'],$comm['comm_dist'],"Recharge Commission",$detais,$txnid_dist);
                $ins = new user_commissions();
                $ins->user_id = $comm['user_id_dist'];
                $ins->transaction_id = $txnid_dist;
                $ins->total_amount = $comm['comm_dist'] + $comm['tds_dist'];
                $ins->amount = $comm['comm_dist'];
                $ins->tds = $comm['tds_dist'];
                $ins->tds_par = 0;
                $ins->wallets_uuid = $user_credit;
                $ins->ref_transaction_id = 0;
                $ins->save();
            }
            
            if($request->ST == 2 || $request->ST == 5 || $request->ST == 3){
                $detais = 'Refund_recharge_'.$txn->mobile;
                $user_debit = $this->WalletCalculation->walletDepositFloat($txn->user_id,$txn->amount,"Recharge",$detais,$txn->transaction_id);
                
                $txn->status = 2;
                $txn->referenceId = "-";
                $txn->wallets_uuid = $user_debit;
                $txn->save();
                
                
            }
        }
    }
    
    public function callbackAcemoney(Request $request) {
        $payload = $request->header();
        $callbackResponse = file_get_contents('php://input');
        Log::channel('acemoney')->info("ACE CALLBACK");
        Log::channel('acemoney')->info($payload);
        Log::channel('acemoney')->info($callbackResponse);
        $json_call = json_decode($callbackResponse);
        $enc = $json_call->enc_data;
        $iv = $request->header('iv');
        $key = 'Mzc0MjY2M2I3NmZiODliNGUyMzhlNDVkZTMxMzMxOWI=';
        
        // Step 1: Clean up the encrypted string (handle escaped slashes)
        $encryptedBase64 = str_replace('\\/', '/', $enc);
        
        // Step 2: Base64 decode the encrypted string
        $encryptedData = base64_decode($encryptedBase64);
        if ($encryptedData === false) {
            die("Error: Invalid base64 encoded string");
        }
        
        // Step 3: Decode the key (assuming it's base64 encoded)
        $key = base64_decode($key);
        if ($key === false) {
            die("Error: Invalid base64 encoded key");
        }
        
        // Step 4: Ensure IV is properly formatted
        // If IV is a numeric string, it needs to be properly padded to 16 bytes
        $iv = str_pad($iv, 16, '0'); // Ensure IV is 16 bytes long
        
        // Step 5: Decrypt the data
        $decryptedData = openssl_decrypt(
            $encryptedData,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        Log::channel('acemoney')->info('ACE CALLBACK DECODED');
        Log::channel('acemoney')->info($decryptedData);
        $json = json_decode($decryptedData,true);
        Log::channel('acemoney')->info($json);
        if($json['success']){
            if($json['data']['status'] == 'SUCCESS'){
                
                    if(isset($json['data']['refnumber'])){
                        $utr =  $json['data']['refnumber'];
                        $DmtTxnId = $json['data']['refnumber'];
                    }else{
                        $utr = 0;
                        $DmtTxnId = 0;
                    }
                    
                    $update = transactions_dmt::where('transaction_id',$json['data']['referenceId'])->where('status',0)->where('eko_status',0)->first();
                    if($update){
                        $update->status = 1;
                        $update->eko_status = 0;
                        $update->utr = $utr;
                        $update->tid = $utr;
                        $update->dmt_txn_id = $DmtTxnId;
                        $update->save();
                        
                        //Commission Start
                        $json_com = $this->WalletCalculation->retailorDmt($update->transaction_id);
                        $commission = $json_com['comm'];
                        $tds = $json_com['tds'];
                        $txnid_com = $this->WalletCalculation->txnId('COMM');
                        
                        $user = User::find($update->user_id);
                        $wallets_uuid = $this->WalletCalculation->walletDepositFloat($update->user_id,$commission,"Commission",'Credit_Retailer_'.$user->mobile.'_DMT_Remittance_Commission',$txnid_com);
                        
                        $ins = new user_commissions();
                        $ins->user_id = $update->user_id;
                        $ins->transaction_id = $txnid_com;
                        $ins->total_amount = $commission + $tds;
                        $ins->amount = $commission;
                        $ins->tds = $tds;
                        $ins->tds_par = 0;
                        $ins->wallets_uuid = $wallets_uuid;
                        $ins->ref_transaction_id = $update->transaction_id;
                        $ins->save();
                        
                        $update = transactions_dmt::find($update->id);
                        $update->comm_settl = 1;
                        $update->save();
                        //Commission end
                    }
                    
            }
        }else{
            
            $update = transactions_dmt::where('transaction_id',$json['data']['referenceId'])->where('status',0)->where('eko_status',0)->first();
            if($update){
                $user = User::find($update->user_id);
                $fee = $update->fee;
                $amount = $update->amount;
                $txnidr = $update->transaction_id;
                $transfer_type = $update->transfer_type;
                $sender_number = $update->mobile;
                $user_debit = $this->WalletCalculation->walletDepositFloat($update->user_id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$sender_number,$txnidr);
                $user_debit = $this->WalletCalculation->walletDepositFloat($update->user_id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$sender_number,$txnidr);
                  
                        
                $update = transactions_dmt::find($update->id);
                $update->status = 2;
                $update->eko_status = 1;
                $update->utr = 0;
                $update->save();
            }
            
        }
    }
    
    //DIGIKHATA
    public function digikhataCustomerLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "customer_name" => "required",
            "pincode" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        

        if($response)
        {
            $customer_mobile = $request->customer_mobile;
            $customer_name = $request->customer_name;
            $pincode = $request->pincode;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            
            
            $data = array("walletaccreatorcode"=>'RT002',"walletaccreatorpincode"=>$pincode,"walletaccreatorname"=>$customer_name,"mobilenumber"=>$customer_mobile);
            $json = json_encode($data);
            $result =  $this->PayPointController->generateOTP($json);
            
            $decode = json_decode($result);
            
            if($decode->resultCode != '2000'){
                return response()->json(['success' => true, 'message' => $decode->resultMessage]);
            }
            
            return response()->json(['success' => true, 'message' => 'OTP Send On Mobile Number','data'=>$decode]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access! OR Update Your APP.']);
        }
        
    }
    
    public function digikhataCustomerOtpVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "otp_token" => "required",
            "otp" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        

        if($response)
        {
            $otp_token = $request->otp_token;
            $otp = $request->otp;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            
            log::info($otp_token);
            $data = array("otpToken"=>$otp_token,"otp"=>$otp);
            $json = json_encode($data);
            $result =  $this->PayPointController->verifyOTP($json);
            
            $decode = json_decode($result);
            
            if($decode->resultCode == '2000'){
                
                if($decode->result->walletAcOpened){
                    $available = $decode->result->walletToBankLimitAvailable;
                    $dmt_use = $decode->result->walletToBankLimitConsumed;
                    $dmt_limit = $decode->result->walletToBankLimitAvailable;
                    $check_customer = $decode;
                
                    return response()->json(['success' => true, 'message' => $decode->resultMessage,'data'=>$check_customer,'is_reg'=>1,'available'=>$available,'used'=>$dmt_use,'limit'=>$dmt_limit]);
                }else{
                    return response()->json(['success' => true, 'message' => 'Customer does not exist in System', 'data'=>$decode,'is_reg'=>0]);
                }
                
            }else{
                return response()->json(['success' => false, 'message' => $decode->resultMessage]);
            }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access! OR Update Your APP.']);
        }
        
    }
    
    public function digikhataAadharOtp(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "customer_name" => "required",
            "pincode" => "required",
            "aadharno" => "required",
            "dmt_token" => "required",
            "walletAcApplicationNumber" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            
            
            $customer_mobile = $request->customer_mobile;
            $customer_name = $request->customer_name;
            $pincode = $request->pincode;
            $aadharno = $request->aadharno;
            $dmt_token = $request->dmt_token;
            
            $day = now()->format('D');
            $random = rand(100000, 999999);
            $walletaccreatorcode = strtoupper($day . date('ymdHis') . $random);
            
            
            $walletacapplicationnumber = $request->walletAcApplicationNumber;
            
            
            $data = array("aadharno"=>$aadharno,
                        "consentid"=>'1',
                        "walletaccreatorcode"=>$customer_mobile,
                        "walletaccreatorname"=>$customer_name,
                        "walletacapplicationnumber"=>(int)$walletacapplicationnumber,
                        "walletaccreatorpincode"=>$pincode);
            
            $json = json_encode($data);
            $result =  $this->PayPointController->generateAadhaarOTP($json,$dmt_token);
            
            $decode = json_decode($result);
            
            if($decode->resultCode == '2000'){
                
                return response()->json(['success' => true, 'message' => $decode->resultMessage,'data'=>$decode,'is_reg'=>0]);
                
            }else{
                return response()->json(['success' => false, 'message' => $decode->resultMessage]);
            }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access! OR Update Your APP.']);
        }
    }
    
    public function digikhataAadharOtpResend(Request $request)
    {
        $data = array("otpToken"=>'W597Z3CZFCoWX3OiG9S3Tnn9DFKKpoZHJaEENpiI1+U/N2Vlpku9lS9VeQj2NMd910EBF9YRFoidLolqFTHLp9soWU/0wMLMBlcoz07HXha51v+FtsWKkzZnDSgSlblcfODCLpDnx21nrZASl7bkCC2pRJkVDBZr');
        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJJZGVudGlmaWNhdGlvbkNvZGUiOiJ2SXFqNDlOL3hJK3cxUytKaG1aVDNRPT0iLCJCQ0FnZW50SWQiOiJUMXVnVjdtbmZKdz0iLCJleHAiOjE3Mzc4MTA1MjksImlzcyI6IlBheVBvaW50IiwiYXVkIjoiUGFydG5lcnMifQ.x-flHTvTb2_m0qJxBE2TD1B6W2KmZNELJrd3f6ptMFg";
        $json = json_encode($data);
        $result =  $this->PayPointController->resendAadhaarOTP($json,$token);
        
        return $result;
    }
    
    public function digikhataAadharOtpVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "otp_token" => "required",
            "otp" => "required",
            "customer_mobile" => "required",
            "dmt_token" => "required",
            "walletAcApplicationNumber" => "required"
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            
            $otp_token = $request->otp_token;
            $otp = $request->otp;
            $dmt_token = $request->dmt_token;
            $customer_mobile = $request->customer_mobile;
            
            $walletacapplicationnumber = $request->walletAcApplicationNumber;
            
            $data = array("walletAcApplicationNumber"=>(int)$walletacapplicationnumber,
                            "otpToken"=>$otp_token,
                            "otp"=>$otp);
            
            $json = json_encode($data);
            $result =  $this->PayPointController->validateAadhaarOTP($json,$dmt_token);
            
            $decode = json_decode($result);
            
            if($decode->resultCode == '2000'){
                
                return response()->json(['success' => true, 'message' => $decode->resultMessage,'data'=>$decode,'is_reg'=>0]);
                
            }else{
                return response()->json(['success' => false, 'message' => $decode->resultMessage]);
            }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access! OR Update Your APP.']);
        }
    }
    
    public function digikhataPancardKyc(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "customer_name" => "required",
            "pincode" => "required",
            "pan_number" => "required",
            "dmt_token" => "required",
            "walletAcApplicationNumber" => "required"
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            
            $customer_mobile = $request->customer_mobile;
            $customer_name = $request->customer_name;
            $pincode = $request->pincode;
            $pan_number = $request->pan_number;
            $dmt_token = $request->dmt_token;
            
            $day = now()->format('D');
            $random = rand(100000, 999999);
            $walletaccreatorcode = strtoupper($day . date('ymdHis') . $random);
            
            $walletacapplicationnumber = $request->walletAcApplicationNumber;
            
            
            $data = array("walletAcCreatorCode"=>$customer_mobile,
                        "walletAcCreatorPinCode"=>$pincode,
                        "walletAcCreatorName"=>$customer_name,
                        "walletAcApplicationNumber"=>(int)$walletacapplicationnumber,
                        "pancardNumber"=>$pan_number,
                        "partnertxnrefid"=>$walletaccreatorcode);
            
            $json = json_encode($data);
            $result =  $this->PayPointController->pancardKYC($json,$dmt_token);
            
            $decode = json_decode($result);
            
            if($decode->resultCode == '2000'){
                // $user_debit = $this->WalletCalculation->walletDepositFloat($user->id,10,"eKYC FEE","Refund_ekyc_fee_sender_$customer_mobile",$pan_number);
                $user_debit = $this->WalletCalculation->walletWithdrawFloat($user->id,10,"eKYC FEE","Debit_ekyc_fee_sender_$customer_mobile",$pan_number);
                return response()->json(['success' => true, 'message' => $decode->resultMessage,'data'=>$decode,'is_reg'=>1]);
                
            }else{
                return response()->json(['success' => false, 'message' => $decode->resultMessage]);
            }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access! OR Update Your APP.']);
        }
    }
    
    public function digikhatabeneficiaryList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "dmt_token" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $bank_channel = $request->get('bank_channel');
            $customer_mobile = $request->customer_mobile;
            $dmt_token = $request->dmt_token;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            
            $data = array("mobilenumber"=>$customer_mobile);
            $token = $dmt_token;
            $json = json_encode($data);
            $result =  $this->PayPointController->beneficiaryList($json,$token);
            
            $decode = json_decode($result);
            
            if(isset($decode->result->beneficiaries)){
                $data = $decode->result->beneficiaries;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            
            return response()->json(['success' => true, 'message' => '', 'data'=>$decode, 'recipientList'=>$data]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
        
        
        
        return $result;
    }
    
    public function digikhataAddbeneficiary(Request $request)
    {
        log::info($request->all());
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "ben_mobile" => "required",
            "ben_name" => "required",
            "ben_account" => "required",
            "customer_mobile" => "required",
            "dmt_token" => "required",
            "bank_name" => "required",
            "ifsc" => "required"
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $ben_mobile = $request->ben_mobile;
            $ben_name = $request->ben_name;
            $ben_account = $request->ben_account;
            // $bank_id = $request->bank_id;
            $bank_name = $request->bank_name;
            $ifsc = $request->ifsc;
            $dmt_token = $request->dmt_token;
            $customer_mobile = $request->customer_mobile;
            
            $day = now()->format('D');
            $random = rand(100000, 999999);
            $partnertxnrefid = strtoupper($day . date('ymdHis') . $random);
            
            $data = array("mobilenumber"=>$customer_mobile,
                        "partnertxnrefid"=>$partnertxnrefid,
                        "beneficiarymobilenumber"=>$ben_mobile,
                        "beneficiaryname"=>$ben_name,
                        "bankid"=>'0',
                        "bankaccountnumber"=>$ben_account,
                        "ifsccode"=>$ifsc,
                        "bankName"=>$bank_name,
                        "verifybeneficiary"=>false,);
            
            $json = json_encode($data);
            $result =  $this->PayPointController->addBeneficiary($json,$dmt_token);
            
            $decode = json_decode($result);
            
            if($decode->resultCode == '2000'){
                
                return response()->json(['success' => true, 'message' => $decode->resultMessage,'data'=>$decode]);
                
            }else{
                return response()->json(['success' => false, 'message' => $decode->resultMessage]);
            }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access! OR Update Your APP.']);
        }
    }
    
    public function digikhataDeletebeneficiary(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "ben_id" => "required",
            "customer_mobile" => "required",
            "dmt_token" => "required"
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $ben_id = $request->ben_id;
            $dmt_token = $request->dmt_token;
            $customer_mobile = $request->customer_mobile;
            
            $day = now()->format('D');
            $random = rand(100000, 999999);
            $partnertxnrefid = strtoupper($day . date('ymdHis') . $random);
            
            $data = array("mobilenumber"=>$customer_mobile,
                        "beneficiaryid"=>$ben_id);
            
            $json = json_encode($data);
            $result =  $this->PayPointController->deleteBeneficiary($json,$dmt_token);
            
            $decode = json_decode($result);
            
            if($decode->resultCode == '2000'){
                
                return response()->json(['success' => true, 'message' => $decode->resultMessage,'data'=>$decode]);
                
            }else{
                return response()->json(['success' => false, 'message' => $decode->resultMessage]);
            }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access! OR Update Your APP.']);
        }
    }
    
    public function digikhataDeletebeneficiaryOtp(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "otp_token" => "required",
            "otp" => "required",
            "customer_mobile" => "required",
            "dmt_token" => "required"
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $otp = $request->otp;
            $otp_token = $request->otp_token;
            $dmt_token = $request->dmt_token;
            $customer_mobile = $request->customer_mobile;
            
            $day = now()->format('D');
            $random = rand(100000, 999999);
            $partnertxnrefid = strtoupper($day . date('ymdHis') . $random);
            
            $data = array("mobilenumber"=>$customer_mobile,
                        "otpToken"=>$otp_token,
                        "otp"=>$otp);
            
            $json = json_encode($data);
            $result =  $this->PayPointController->deleteBeneficiaryOtp($json,$dmt_token);
            
            $decode = json_decode($result);
            
            if($decode->resultCode == '2000'){
                
                return response()->json(['success' => true, 'message' => $decode->resultMessage,'data'=>$decode]);
                
            }else{
                return response()->json(['success' => false, 'message' => $decode->resultMessage]);
            }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access! OR Update Your APP.']);
        }
    }
    
    public function digikhataDmtDoTransactions(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "beneficiary_id" => "required",
            "dmt_token" => "required",
            "amount" => [
                            "required",
                            "numeric",
                            function($attribute, $value, $fail) {
                                // Check if the amount is greater than 100
                                if ($value < 100) {
                                    $fail($attribute . ' must be greater than 100.');
                                }
                            },
                        ],
            "transfer_type" => "required",
            "latitude" => "required",
            "longitude" => "required",
            "sender_name" => "required",
            "ben_name" => "required",
            "ben_account" => "required",
            "ben_ifsc" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            
            $user = User::where('mobile',$request->user_mobile)->first();
            // if($user->id != 21){
            //     return response()->json(['success' => false, 'message' => "Try after 11:00AM."]);
            // }
        
            $shop = shop_details::where('user_id',$user->id)->first();
            if($shop->latitude == null || $shop->longitude == null){
                return response()->json(['success' => false, 'message' => "Please Insert Shop Detail."]);
            }
            
            $distance = $this->UserAuth->geteDistance($shop->latitude, $shop->longitude, $request->latitude, $request->longitude);
            log::channel("location")->info("Retailer");
            log::channel("location")->info($request->latitude."/".$request->longitude);
            log::channel("location")->info("SHOP");
            log::channel("location")->info($shop->latitude."/".$shop->longitude);
            log::channel("location")->info("KM");
            log::channel("location")->info($distance);
            $distance_mnd = 25;
            if(isset($request->accuracy)){
                $distance_mnd = $distance_mnd + $request->accuracy;
                // if($request->accuracy >= 100){
                //     $distance_mnd = 250;
                // }
                // $distance_mnd = 350;
            }
            if($request->get('platform') == 'web'){
                $distance_mnd = 1000;
                if($distance > $distance_mnd){
                    if($user->id == 363){
                        
                    }else{
                        return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
                    }
                    
                }
            }else{
                if($distance > $distance_mnd){
                    if($user->id == 363){
                        
                    }else{
                        return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
                    }
                    
                }
                
            }
            $sender_name = $request->sender_name;
            $ben_name = $request->ben_name;
            $ben_account = $request->ben_account;
            $ben_ifsc = $request->ben_ifsc;
            $dmt_token = $request->dmt_token;
            
            $transfer_type = $request->transfer_type;
            $customer_mobile = $request->customer_mobile;
            $beneficiary_id = $request->beneficiary_id;
            $amount = $request->amount;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $user = User::where('mobile',$request->user_mobile)->first();
            $amount_par = $this->UserAuth->partitionAmount($amount);
            $main_amount = $amount;
            $loop_count = count($amount_par);
            $txnid = $this->UserAuth->txnId('DMT');
            sleep(rand(2,6));
            $recentTime = Carbon::now()->subMinutes(10);
            $userwallet_check = User::find($user->id);
            $balance_compaer = $userwallet_check->wallet->balanceFloat;
            if($balance_compaer < $main_amount){
                return response()->json(['success' => false, 'message' => 'Insufficient balance.']);
            }
            
            
            //txn start
            //FEE ST
            // for($i = 0;$i < $loop_count;$i++){
            $i = 0;
                $txnidm = $txnid.'#'.$i;
                $txnidr = $this->UserAuth->txnId('DMT');
                // $amount = $amount_par[$i];
                
            if($amount >= 100 && $amount <= 1000){
                if($main_amount > 1000){
                    $fee = $amount * 1.2/100;
                }else{
                    $fee = 10;
                }
                
            }else{
                $fee = $amount * 1/100;
            }
            $totalamount = $amount + $fee;
            $gst = $fee - ($fee / 1.18);
            sleep(rand(1,2));
            //FEE END
            $i_balance = 1;
            // digidodmtamount:
            $userwallet = User::find($user->id);
            $balance = $userwallet->wallet->balanceFloat;
            // print_r($balance);exit;
            if($balance < $totalamount){
                if($i == 0){
                    return response()->json(['success' => false, 'message' => 'Insufficient balance.']);
                }else{
                    goto digiloopskip;
                }
                
                
                
            }
            $wallet = $userwallet->wallet;
            // $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
            // if($balance_check->balance != $userwallet->wallet->balance){
            //         Log::channel("balancemissmatch")->info("========");
            //         Log::channel("balancemissmatch")->info($wallet->id);
            //         Log::channel("balancemissmatch")->info($balance_check->balance);
            //         Log::channel("balancemissmatch")->info($userwallet->wallet->balance);
            //         if($i_balance >= 25){
            //             if($i == 0){
            //                 return response()->json(['success' => false, 'message' => 'Try after 5 Min.']);
            //             }else{
            //                 goto digiloopskip;
            //             }
            //         }else{
            //             $i_balance++;
            //             goto digidodmtamount;
            //         }
                    
            // }
            
            $txn_wl_fee = $this->WalletCalculation->walletWithdrawFloat($user->id,$fee,'Money Transfer Fee','Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
            // $txn_wl_fee = $wallet->withdrawFloat($fee,[
            //     'meta' => [
            //         'Title' => 'Money Transfer Fee',
            //         'detail' => 'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
            //         'transaction_id' => $txnidr,
            //     ]
            // ]);
            // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
            
            $i_fee = 1;
            // digidodmtfee:
            $userwallet = User::find($user->id);
            $wallet = $userwallet->wallet;
            // $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
            // if($balance_check->balance != $userwallet->wallet->balance){
            //         Log::channel("balancemissmatch")->info("========");
            //         Log::channel("balancemissmatch")->info($wallet->id);
            //         Log::channel("balancemissmatch")->info($balance_check->balance);
            //         Log::channel("balancemissmatch")->info($userwallet->wallet->balance);
            //         if($i_fee >= 25){
            //             if($i == 0){
            //                 return response()->json(['success' => false, 'message' => 'Try after 5 Min.']);
            //             }else{
            //                 goto digiloopskip;
            //             }
            //         }else{
            //             $i_fee++;
            //             goto digidodmtfee;
            //         }
                    
            // }
            
            $txn_wl = $this->WalletCalculation->walletWithdrawFloat($user->id,$amount,'Money Transfer','Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
            // $txn_wl = $wallet->withdrawFloat($amount,[
            //     'meta' => [
            //         'Title' => 'Money Transfer',
            //         'detail' => 'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
            //         'transaction_id' => $txnidr,
            //     ]
            // ]);
            // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
            
            
            
            $ins = new transactions_dmt();
            $ins->user_id = $user->id;
            $ins->event = 'DMT';
            $ins->transaction_id = $txnidr;
            $ins->multi_transaction_id = $txnidm;
            $ins->amount = $amount;
            $ins->mobile = $request->customer_mobile;
            $ins->transfer_type = $transfer_type;
            $ins->fee = $fee;
            $ins->tds = 0;
            $ins->gst = $gst;
            $ins->closing_balance = $wallet->balanceFloat;
            $ins->sender_name = $sender_name;
            $ins->ben_name = $ben_name;
            $ins->ben_ac_number = $ben_account;
            $ins->ben_ac_ifsc = $ben_ifsc;
            $ins->utr = 0;
            $ins->status = 0;
            $ins->eko_status = 6;
            $ins->api_id = 4;
            $ins->wallets_uuid = $txn_wl;
            $ins->eko_beneficiary_id = $beneficiary_id;
            $ins->save();
            
            // if ($duplicate) {
            //     // return response()->json(['message' => 'Duplicate entry detected'], 409); // HTTP 409 Conflict
            // }
            
            if($transfer_type == 'IMPS'){
                $payment_mode = 2;
            }else{
                $payment_mode = 1;
            }
            $amount_bill = $amount;
            
            $data = array("mobilenumber"=>$customer_mobile,
                            "bankaccountnumber"=>$ben_account,
                            "ifsccode"=>$ben_ifsc,
                            "beneficiaryid"=>$beneficiary_id,
                            "amount"=>"$amount");
            $json = json_encode($data);
            $result =  $this->PayPointController->doTransaction($json,$dmt_token);
            
            $json = json_decode($result);
            
            Log::info("===========TXN SEND OTP==============");
            Log::info($result);
            if($json->resultCode == '2000'){
                $update = transactions_dmt::find($ins->id);
                $update->otp_reference = $json->result->otpToken;
                $update->save();
                return response()->json(['success' => true, 'message' => 'OTP Send On Your Sender Mobile.', 'data'=>$json,'transaction_id'=>$txnidr]);
            }else{
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    //         'transaction_id' => $txnidr,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    //         'transaction_id' => $txnidr,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->eko_status = 1;
                    $update->utr = 0;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                     return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            digiloopskip:
                
                return response()->json(['success' => false, 'message' => 'Transaction Failed']);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function digiDmtDoTransactionsOtpVerify(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "transaction_id" => "required",
            "otp" => "required",
            "otp_token" => "required",
            "dmt_token" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            
            $customer_mobile = $request->customer_mobile;
            $transaction_id = $request->transaction_id;
            $otp = $request->otp;
            $otp_token = $request->otp_token;
            $dmt_token = $request->dmt_token;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            
            $data = transactions_dmt::where('status','0')->where('transaction_id',$transaction_id)->first();
            $amount = $data->amount;
            $fee = $data->fee;
            $transfer_type = $data->transfer_type;
            $txnidr = $transaction_id;
            $user = User::find($data->user_id);
            
            $data_par = array("mobilenumber"=>$customer_mobile,
                            "partnertxnrefid"=>$transaction_id,
                            "txntype"=>$data->transfer_type,
                            "beneficiaryid"=>$data->eko_beneficiary_id,
                            "amount"=>"$amount",
                            "otptoken"=>$otp_token,
                            "otp"=>$otp);
            $json = json_encode($data_par);
            $result =  $this->PayPointController->doTransactionVerify($json,$dmt_token);
            
            
            $json = json_decode($result);
            
            
            $userwallet = User::find($data->user_id);
            $wallet = $userwallet->wallet;
            Log::info($result);
            
            if($json->resultCode == 2000){
                
                if($json->resultStatus == "Success"){
                    //success
                    
                    if(isset($json->result->bankRRN)){
                            $utr =  $json->result->bankRRN;
                            $DmtTxnId = $json->result->txnreferenceid;
                        }else{
                        $utr = 0;
                        $DmtTxnId = 0;
                    }
                    
                    $update = transactions_dmt::find($data->id);
                    $update->status = 1;
                    $update->eko_status = 0;
                    $update->utr = $utr;
                    $update->tid = $utr;
                    $update->dmt_txn_id = $DmtTxnId;
                    $update->bank_name = $json->result->beneficiarybank;
                    $update->save();
                    
                    // $this->WalletCalculation->distributorDmt($txnidr);
                    // $this->WalletCalculation->retailorDmt($txnidr);
                    
                    //Commission Start
                        $json_com = $this->WalletCalculation->retailorDmt($data->transaction_id);
                        $commission = $json_com['comm'];
                        $tds = $json_com['tds'];
                        $txnid_com = $this->WalletCalculation->txnId('COMM');
                        
                        $user = User::find($data->user_id);
                        $wallets_uuid = $this->WalletCalculation->walletDepositFloat($data->user_id,$commission,"Commission",'Credit_Retailer_'.$user->mobile.'_DMT_Remittance_Commission',$txnid_com);
                        
                        $ins = new user_commissions();
                        $ins->user_id = $data->user_id;
                        $ins->transaction_id = $txnid_com;
                        $ins->total_amount = $commission + $tds;
                        $ins->amount = $commission;
                        $ins->tds = $tds;
                        $ins->tds_par = 0;
                        $ins->wallets_uuid = $wallets_uuid;
                        $ins->ref_transaction_id = $data->transaction_id;
                        $ins->save();
                        
                        $update = transactions_dmt::find($data->id);
                        $update->comm_settl = 1;
                        $update->save();
                    //Commission end
                    
                    $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txnidr on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                    // $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->customer_mobile,$text,1207172205377529606);
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$data->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    // return response()->json(['success' => true, 'message' => 'Transaction Success.', 'data'=>$data]);
                    
                }elseif($json->resultStatus == "Pending"){
                    
                    if(isset($json->result->bankRRN)){
                            $utr =  $json->result->bankRRN;
                            $DmtTxnId = $json->result->txnreferenceid;
                        }else{
                        
                        $DmtTxnId = 0;
                    }
                    
                    $utr = 0;
                    $update = transactions_dmt::find($data->id);
                    $update->eko_status = 0;
                    $update->utr = $utr;
                    $update->tid = $utr;
                    $update->dmt_txn_id = $DmtTxnId;
                    $update->bank_name = $json->result->beneficiarybank;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$data->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    // return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    
                }else{
                    Log::info('AFTER TXN RESULT 000');
                    
                        if(isset($json->result->refnumber)){
                            $utr =  $json->result->bankRRN;
                            $DmtTxnId = $json->result->txnreferenceid;
                        }else{
                            $utr = 0;
                            $DmtTxnId = 0;
                        }
                        $update = transactions_dmt::find($data->id);
                        $update->eko_status = 0;
                        $update->utr = $utr;
                        $update->dmt_txn_id = $DmtTxnId;
                        $update->tid = $DmtTxnId;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->where('transactions_dmts.id',$data->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        // return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    
                    
                }
            }else{
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
                    
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    //         'transaction_id' => $txnidr,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    //         'transaction_id' => $txnidr,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($data->id);
                    $update->status = 2;
                    $update->eko_status = 1;
                    $update->utr = 0;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$data->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    // return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            
            $txnid = $data->multi_transaction_id;
            $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.multi_transaction_id','LIKE','%'.$txnid.'%')
                    ->whereIn('status',[0,1])
                    ->orderBy('transactions_dmts.id','DESC')->first();
            $summary = transactions_dmt::select(DB::raw('(SELECT transaction_id FROM transactions_dmts WHERE multi_transaction_id LIKE "%'.$txnid.'%" ORDER BY transaction_id ASC LIMIT 1) as transaction_id'),
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->addSelect(DB::raw('SUM(CASE WHEN status IN (0, 1) THEN amount ELSE 0 END) as amount'))
                        ->addSelect(DB::raw('SUM(CASE WHEN status IN (0, 1) THEN fee ELSE 0 END) as fee'))
                        ->addSelect(DB::raw('CASE 
                            WHEN SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) > 0 
                                THEN 0
                            WHEN SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) > 0 
                                THEN 1
                            ELSE 2 
                            END as status'))
                        ->where('multi_transaction_id','LIKE','%'.$txnid.'%')
                        ->groupBy('transactions_dmts.sender_name', 'transactions_dmts.mobile', 'transactions_dmts.bank_name')
                        ->first();
                if($data){
                    $summary_bkp = $summary;
                    $summary = $data;
                    if($summary_bkp->transaction_id){
                        $summary->transaction_id = $summary_bkp->transaction_id;
                    }
                    
                    $summary->amount = $summary_bkp->amount;
                    $summary->fee = $summary_bkp->fee;
                    $summary->status = $summary_bkp->status;
                }else{
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.multi_transaction_id','LIKE','%'.$txnid.'%')
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    $summary_bkp = $summary;
                    $summary = $data;
                    if($summary_bkp->transaction_id){
                        $summary->transaction_id = $summary_bkp->transaction_id;
                    }
                    
                    $summary->amount = $summary_bkp->amount;
                    $summary->fee = $summary_bkp->fee;
                    $summary->status = $summary_bkp->status;
                    
                }
                
                        
            if($summary){
                // Log::info('AFTER TXN RESULT');
                // Log::info($summary);
                if($summary->status == 1 || $summary->status == 0){
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$summary]);
                }else{
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$summary]);
                }
                
            }else{
                return response()->json(['success' => false, 'message' => 'Insufficient balance.Please Check Report.']);
            }
            
        }
        
        
    }
    
    public function digiDmtRefundRequest(Request $request) {
        
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required",
            "transaction_id" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $txn = transactions_dmt::where('transaction_id',$request->transaction_id)
                    ->whereNotNull('dmt_txn_id')
                    ->where('status',0)
                    ->where('eko_status',3)
                    ->first();
            if(!$txn){
                return response()->json(['success' => false, 'message' => 'Please Check With Admin!']);
            }
            
            $customer_mobile = $txn->mobile;
            $transaction_id = $txn->dmt_txn_id;
            $data_par = array("mobilenumber"=>$customer_mobile,
                            "txnreferenceid"=>$transaction_id);
            $json = json_encode($data_par);
            $result =  $this->PayPointController->refundRequest($json);
            $decode = json_decode($result);
            
            if($decode->resultCode == '2000'){
                $txn->otp_reference = $decode->result->otpToken;
                $txn->save();
                return response()->json(['success' => true, 'message' => $decode->resultMessage, 'data'=>$decode]);
            }else{
                return response()->json(['success' => false, 'message' => 'Something Wrong', 'data'=>$decode]);
            }
            
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function digiDmtRefundOtpVerify(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required",
            "transaction_id" => "required",
            "otp" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $txn = transactions_dmt::where('transaction_id',$request->transaction_id)->where('status',0)
                    ->where('eko_status',3)
                    ->first();
            if(!$txn){
                return response()->json(['success' => false, 'message' => 'Please Check With Admin!']);
            }
            $post = [
                "txnId" => $txn->dmt_txn_id,
                "otp" => $request->otp,
                "uniqueRefId" => $txn->tid,
            ];
            
            $data_par = array("mobilenumber"=>$txn->mobile,
                            "otpToken"=>$txn->otp_reference,
                            "otp"=>$request->otp);
            $json = json_encode($data_par);
            
            $result = $this->PayPointController->refundRequestOTPverify($json);
            $decode = json_decode($result);
            if($decode->resultCode == '2000'){
                
                $user = User::find($txn->user_id);
                $wallet = $user->wallet;
                $txnidr = $txn->transaction_id;
                $fee = $txn->fee;
                $amount = $txn->amount;
                $transfer_type = $txn->transfer_type;
                
                $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$txn->mobile,$txnidr);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$txn->mobile,$txnidr);
                // $txn_wl_fee = $wallet->depositFloat($fee,[
                //     'meta' => [
                //         'Title' => 'Money Transfer Fee',
                //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$txn->mobile,
                //         'transaction_id' => $txnidr,
                //     ]
                // ]);
                // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                
                // $amount = $txn->amount;
                
                // $txn_wl = $wallet->depositFloat($amount,[
                //     'meta' => [
                //         'Title' => 'Money Transfer',
                //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$txn->mobile,
                //         'transaction_id' => $txnidr,
                //     ]
                // ]);
                // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                
                $update = transactions_dmt::find($txn->id);
                $update->status = 2;
                $update->eko_status = 0;
                $update->refund_dmt_id = $decode->result->refundtxnreferenceid;
                $update->save();
                
                return response()->json(['success' => true, 'message' => $decode->resultMessage, 'data'=>$decode]);
            }else{
                return response()->json(['success' => false, 'message' => 'Something Wrong', 'data'=>$decode]);
            }
            
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    
    
    //ACEMONEY
    public function acemoneyCustomerLogin(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "bank_channel" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        

        if($response)
        {
            $customer_mobile = $request->customer_mobile;
            $bank_id = $request->bank_channel;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            
            
            $data = array("agentid"=>'vinuparmar1986@gmail.com',
                        "CustomerMobileNo"=>$customer_mobile,
                        "dmt_type"=>$bank_id);
            $json = json_encode($data);
            $result =  $this->AceMoneyController->getCustomer($json);
            $decode = json_decode($result);
            
            if($decode->ekyc_status == '0'){
                return response()->json(['success' => true, 'message' => 'Customer does not exist in System', 'data'=>$decode,'is_reg'=>0]);
            }
            
            if($decode->success == false){
                return response()->json(['success' => false, 'message' => $decode->message]);
            }
            
            $data_benf = array("agentid"=>'vinuparmar1986@gmail.com',
                        "CustomerMobileNo"=>$customer_mobile,
                        "type"=>$bank_id);
            $json_benf = json_encode($data_benf);
            
            // $result_balance =  $this->AceMoneyController->getBeneficiary($json_benf);
            $result_balance =  $this->AceMoneyController->getCustomerBalance($json_benf);
            $decode_balance = json_decode($result_balance);
            
            if($decode_balance->success == false){
                return response()->json(['success' => false, 'message' => $decode_balance->message]);
            }
            
            $available = $decode_balance->limit;
            $dmt_use = $decode_balance->limit;
            $dmt_limit = $decode_balance->sendupto;
            $check_customer = $decode;
            
            $check_sender = dmt_customers::where('customer_id',$decode->id)->where('mobile',$customer_mobile)->first();
            if(!$check_sender){
                $ins_sender = new dmt_customers();
                $ins_sender->user_id = $user->id;
                $ins_sender->customer_id = $decode->id;
                $ins_sender->first_name = $decode->customer_name;
                $ins_sender->mobile = $customer_mobile;
                $ins_sender->dmt_type = '3';
                $ins_sender->bank_id = $bank_id;
                $ins_sender->save();
                
            }
            
            
            return response()->json(['success' => true, 'message' => 'Customer Login','data'=>$check_customer,'is_reg'=>1,'available'=>$available,'used'=>$dmt_use,'limit'=>$dmt_limit]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access! OR Update Your APP.']);
        }
    }
    
    public function acemoneyKycCustomer(Request $request){
        log::info($request->all());
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "mobile" => "required",
            "name" => "required|min:5",
            "pincode" => "required",
            "bank_channel" => "required",
            "aadhar" => "required",
            "piddata" => "required",
            "piddata_type" => "required",
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
        
        $mobile = $request->mobile;
        $name = $request->name;
        $pincode = $request->pincode;
        $piddata = $request->get('piddata');
        $aadhar = $request->get('aadhar');
        $piddata_type = $request->get('piddata_type');
        $bank_channel = $request->get('bank_channel');
        
        if($piddata_type == 'FIR'){
            $kyc_flag = 2;
        }else{
            $kyc_flag = 4;
        }
        
        // $name = preg_replace('/[\*\?\[\]\{\}\^\$\(\)\+\|,\.\/\\\\]/', '', $name);
        if($user){
            $piddata = str_replace(["\r", "\n", "\t","  "], '', $piddata);
            // $piddata = str_replace('\"','"', $piddata);
            // $piddata = str_replace('"', "'", $piddata);
            // $piddata = str_replace('\/', "/", $piddata);
            $data = array('agentid'=>'vinuparmar1986@gmail.com',
                        'CustomerMobileNo'=>$mobile,
                        'CustomerName'=>$name,
                        'AadharNo'=>$aadhar,
                        'Pid'=>$piddata,
                        'Latitude'=>'22.3039',
                        'longitude'=>'70.8022',
                        'PublicIP'=>'216.10.244.132',
                        'dmt_type'=>$bank_channel,
                        'KYCTypeFlag'=>$kyc_flag);
            
            $json = json_encode($data);
            $result =  $this->AceMoneyController->ekyc($json);
            
            $decode = json_decode($result);
            
            if (is_object($decode) || is_array($decode)){
                
            }else{
                
                return response()->json(['success' => false, 'message' => 'Bank Server Not Responding.']);
            }
            
            if($decode->success){
                return response()->json(['success' => true, 'message' => 'Otp Send','is_kyc'=>1,'is_otp'=>1,'is_reg'=>0,'data'=>$decode]);
            }else{
                if($decode->success){
                    return response()->json(['success' => true, 'message' => $decode->message,'is_kyc'=>1,'is_otp'=>0,'is_reg'=>0,'data'=>$decode]);
                }else{
                    return response()->json(['success' => false, 'message' => $decode->message,'is_kyc'=>0,'is_otp'=>0,'is_reg'=>0,'data'=>$decode]);
                }
                
            }
            
            
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
        
    }
    
    public function acemoneyCreateCustomerOtp(Request $request){
        log::info($request->all());
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "mobile" => "required",
            "name" => "required|min:5",
            "pincode" => "required",
            "bank_channel" => "required",
            "aadhar" => "required",
            "piddata" => "required",
            "piddata_type" => "required",
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
        
        $mobile = $request->mobile;
        $name = $request->name;
        $pincode = $request->pincode;
        $piddata = $request->get('piddata');
        $aadhar = $request->get('aadhar');
        $piddata_type = $request->get('piddata_type');
        $bank_channel = $request->get('bank_channel');
        
        // $name = preg_replace('/[\*\?\[\]\{\}\^\$\(\)\+\|,\.\/\\\\]/', '', $name);
        if($user){
            
            $data = array("agentid"=>'vinuparmar1986@gmail.com',
                        "CustomerMobileNo"=>$mobile,
                        "CustomerName"=>$name,
                        "AadharNo"=>$aadhar,
                        "Latitude"=>'22.3039',
                        "longitude"=>'70.8022',
                        "PublicIP"=>'216.10.244.132',
                        "dmt_type"=>$bank_channel);
            $json = json_encode($data);
            
            $json = json_encode($data);
            $result =  $this->AceMoneyController->createCustomer($json);
            
            $decode = json_decode($result);
            if($decode->success){
                return response()->json(['success' => true, 'message' => $decode->message,'is_kyc'=>1,'is_otp'=>1,'is_reg'=>0,'data'=>$decode]);
            }else{
                if($decode->success){
                    return response()->json(['success' => true, 'message' => $decode->message,'is_kyc'=>1,'is_otp'=>0,'is_reg'=>0,'data'=>$decode]);
                }else{
                    return response()->json(['success' => false, 'message' => $decode->message,'is_kyc'=>0,'is_otp'=>0,'is_reg'=>0,'data'=>$decode]);
                }
                
            }
            
            
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
        
    }
    
    public function acemoneyCreateCustomer(Request $request){
        log::info($request->all());
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "mobile" => "required",
            "name" => "required|min:5",
            "pincode" => "required",
            "bank_channel" => "required",
            "aadhar" => "required",
            "address" => "required",
            "otp" => "required",
            "KYCRequestId" => "required",
            "OTPRequestId" => "required",
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
        
        $mobile = $request->mobile;
        $name = $request->name;
        $pincode = $request->pincode;
        $aadhar = $request->get('aadhar');
        $bank_channel = $request->get('bank_channel');
        $KYCRequestId = $request->get('KYCRequestId');
        $OTPRequestId = $request->get('OTPRequestId');
        $otp = $request->get('otp');
        $address = $request->get('address');
        if($bank_channel == 4){
            $piddata = $request->get('piddata');
            $piddata_type = $request->get('piddata_type');
        }
        
        
        // $name = preg_replace('/[\*\?\[\]\{\}\^\$\(\)\+\|,\.\/\\\\]/', '', $name);
        if($user){
            // if($bank_channel == 1){
                $user_debit = $this->WalletCalculation->walletWithdrawFloat($user->id,10,"eKYC FEE","Debit_ekyc_fee_sender_$mobile",$aadhar);
                if($user_debit != 0){
                
                }else{
                    
                    return response()->json(['success' => false, 'message' => "Please Check With Admin"]);
                }
            // }
            
            
            if($bank_channel == 4){
                $piddata = str_replace(["\r", "\n", "\t","  "], '', $piddata);
            log::info($piddata);
                if($piddata_type == 'FIR'){
                    $KYCTypeFlag = 2;
                }else{
                    $KYCTypeFlag = 4;
                }
                $data = array("agentid"=>'vinuparmar1986@gmail.com',
                        "CustomerMobileNo"=>$mobile,
                        "CustomerName"=>$name,
                        "AadharNo"=>$aadhar,
                        "Latitude"=>'22.3039',
                        "longitude"=>'70.8022',
                        "PublicIP"=>'216.10.244.132',
                        "dmt_type"=>$bank_channel,
                        "KYCRequestId"=>$KYCRequestId,
                        "OTPRequestId"=>$OTPRequestId,
                        "address"=>$address,
                        "OTPPin"=>$otp,
                        "Pid"=>$piddata,
                        "KYCTypeFlag"=>$KYCTypeFlag);
            }else{
                $data = array("agentid"=>'vinuparmar1986@gmail.com',
                        "CustomerMobileNo"=>$mobile,
                        "CustomerName"=>$name,
                        "AadharNo"=>$aadhar,
                        "Latitude"=>'22.3039',
                        "longitude"=>'70.8022',
                        "PublicIP"=>'216.10.244.132',
                        "dmt_type"=>$bank_channel,
                        "KYCRequestId"=>$KYCRequestId,
                        "OTPRequestId"=>$OTPRequestId,
                        "address"=>$address,
                        "OTPPin"=>$otp);
            }
            
            
            $json = json_encode($data,JSON_UNESCAPED_SLASHES);
            $result =  $this->AceMoneyController->createCustomer($json);
            
            $decode = json_decode($result);
            
            
            if($decode->success){
                
                $data_benf = array("agentid"=>'vinuparmar1986@gmail.com',
                            "CustomerMobileNo"=>$mobile,
                            "type"=>$bank_channel);
                $json_benf = json_encode($data_benf);
                
                $result_balance =  $this->AceMoneyController->getCustomerBalance($json_benf);
                $decode_balance = json_decode($result_balance);
                
                $available = $decode_balance->limit;
                $dmt_use = $decode_balance->limit;
                $dmt_limit = $decode_balance->sendupto;
                
                $check_sender = dmt_customers::where('customer_id',$decode->id)->where('mobile',$mobile)->first();
                if(!$check_sender){
                    $ins_sender = new dmt_customers();
                    $ins_sender->user_id = $user->id;
                    $ins_sender->customer_id = $decode->id;
                    $ins_sender->first_name = $name;
                    $ins_sender->mobile = $mobile;
                    $ins_sender->dmt_type = '3';
                    $ins_sender->bank_id = $bank_channel;
                    $ins_sender->save();
                    
                }
                
                
                return response()->json(['success' => true, 'message' => $decode->message,'is_kyc'=>1,'is_otp'=>1,'is_reg'=>1,'data'=>$decode,'available'=>$available,'used'=>$dmt_use,'limit'=>$dmt_limit]);
            }else{
                if($decode->success){
                    return response()->json(['success' => true, 'message' => $decode->message,'is_kyc'=>1,'is_otp'=>0,'is_reg'=>0,'data'=>$decode]);
                }else{
                    // if($bank_channel == 1){
                    //     $user_debit = $this->WalletCalculation->walletDepositFloat($user->id,10,"eKYC FEE","Refund_ekyc_fee_sender_$mobile",$aadhar);
                    // }
                    $user_debit = $this->WalletCalculation->walletDepositFloat($user->id,10,"eKYC FEE","Refund_ekyc_fee_sender_$mobile",$aadhar);
                    return response()->json(['success' => false, 'message' => $decode->message,'is_kyc'=>0,'is_otp'=>0,'is_reg'=>0,'data'=>$decode]);
                }
                
            }
            
            
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
        
    }
    
    public function aceDmtGetBenf(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "bank_channel" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $bank_channel = $request->get('bank_channel');
            $customer_mobile = $request->customer_mobile;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            
            $data = array("agentid"=>'vinuparmar1986@gmail.com',
                        "CustomerMobileNo"=>$customer_mobile,
                        "dmt_type"=>$bank_channel);
            $json = json_encode($data);
            $result =  $this->AceMoneyController->getBeneficiary($json);
            $decode = json_decode($result);
            
            if(isset($decode->benes)){
                $data = $decode->benes;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            
            return response()->json(['success' => true, 'message' => '', 'data'=>$decode, 'recipientList'=>$data]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function aceDmtAddBenf(Request $request) {
        log::info("aceDmtAddBenf");
        log::info($request->all());
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required",
            "customer_mobile" => "required",
            "name" => "required|min:5",
            "account" => "required",
            "ifsc" => "required",
            "is_verify" => "required",
            "bank_id" => "required",
            "benf_mobile" => "required",
            "bank_channel" => "required",
        ]);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            log::info($param_message);
            return response()->json(['success' => false, 'message' => $param_message]);
        }


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $bank_channel = $request->get('bank_channel');
            $customer_mobile = $request->customer_mobile;
            $name = $request->name;
            $bank_id = $request->bank_id;
            $ifsc = $request->ifsc;
            $account = $request->account;
            $benf_mobile = $request->benf_mobile;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            $name = preg_replace('/[\*\?\[\]\{\}\^\$\(\)\+\|,\.\/\\\\]/', '', $name);
            
            $data = array("agent_id"=>'vinuparmar1986@gmail.com',
                        "senderno"=>$customer_mobile,
                        "mobileno"=>$benf_mobile,
                        "name"=>$name,
                        "accountno"=>$account,
                        "bankname"=>$bank_id,
                        "ifsccode"=>$ifsc);
            $json = json_encode($data);
            $result =  $this->AceMoneyController->addBeneficiary($json);
            $decode = json_decode($result);
            
            if($decode->success){
                return response()->json(['success' => true, 'message' => '', 'data'=>$decode]);
            }else{
                if(isset($decode->message)){
                    $msg = $decode->message;
                }else{
                    $msg = "Something Wrong";
                }
                return response()->json(['success' => false, 'message' => $msg, 'data'=>$decode]);
            }
            
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function aceDmtDoTransactions(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "beneficiary_id" => "required",
            "amount" => [
                            "required",
                            "numeric",
                            function($attribute, $value, $fail) {
                                // Check if the amount is greater than 100
                                if ($value < 100) {
                                    $fail($attribute . ' must be greater than 100.');
                                }
                            },
                        ],
            "transfer_type" => "required",
            "account" => "required",
            "beneficiary_name" => "required",
            "ifsc" => "required",
            "bank_name" => "required",
            "latitude" => "required",
            "longitude" => "required",
            "bank_channel" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $bank_channel = $request->get('bank_channel');
            $user = User::where('mobile',$request->user_mobile)->first();
            // if($user->id != 27){
            //     return response()->json(['success' => false, 'message' => "Try after 11:00AM."]);
            // }
        
            $shop = shop_details::where('user_id',$user->id)->first();
            if($shop->latitude == null || $shop->longitude == null){
                return response()->json(['success' => false, 'message' => "Please Insert Shop Detail."]);
            }
            
            $distance = $this->UserAuth->geteDistance($shop->latitude, $shop->longitude, $request->latitude, $request->longitude);
            log::channel("location")->info("Retailer");
            log::channel("location")->info($request->latitude."/".$request->longitude);
            log::channel("location")->info("SHOP");
            log::channel("location")->info($shop->latitude."/".$shop->longitude);
            log::channel("location")->info("KM");
            log::channel("location")->info($distance);
            $distance_mnd = 25;
            if(isset($request->accuracy)){
                $distance_mnd = $distance_mnd + $request->accuracy;
                // if($request->accuracy >= 100){
                //     $distance_mnd = 250;
                // }
                // $distance_mnd = 350;
            }
            if($request->get('platform') == 'web'){
                $distance_mnd = 1000;
                if($distance > $distance_mnd){
                    if($user->id == 363){
                        
                    }else{
                        return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
                    }
                    
                }
            }else{
                if($distance > $distance_mnd){
                    if($user->id == 363){
                        
                    }else{
                        return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
                    }
                    
                }
                
            }
            
            
            $transfer_type = $request->transfer_type;
            $customer_mobile = $request->customer_mobile;
            $beneficiary_id = $request->beneficiary_id;
            $amount = $request->amount;
            $account = $request->account;
            $beneficiary_name = $request->beneficiary_name;
            $ifsc = $request->ifsc;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $bank_name = $request->bank_name;
            $user = User::where('mobile',$request->user_mobile)->first();
            $amount_par = $this->UserAuth->partitionAmount($amount);
            $main_amount = $amount;
            $loop_count = count($amount_par);
            $txnid = $this->UserAuth->txnId('DMT');
            //sleep(rand(2,6));
            $recentTime = Carbon::now()->subMinutes(10);
            $userwallet_check = User::find($user->id);
            $balance_compaer = $userwallet_check->wallet->balanceFloat;
            if($balance_compaer < $main_amount){
                return response()->json(['success' => false, 'message' => 'Insufficient balance.']);
            }
            // $duplicate = transactions_dmt::where('user_id', $user->id)
            //                 ->where('mobile', $customer_mobile)
            //                 ->where('eko_beneficiary_id', $beneficiary_id)
            //                 ->whereIn('status', [0,1])
            //                 ->where('amount', $amount_par[0])
            //                 ->where('created_at', '>=', $recentTime)
            //                 ->exists();
            // if ($duplicate) {
            //     log::info('Duplicate');
            //     log::info($request->all());
            //     return response()->json(['success' => false, 'message' => 'Duplicate! By Mistake Double Click or Duplicat Transaction. Please Check you report.']);
            // }
            
            //txn start
            //FEE ST
            $txns = array();
            $final_response = true;
            $final_message = 'Transaction Procced';
            
            $check_sender = dmt_customers::where('bank_id',$bank_channel)->where('dmt_type','3')->where('mobile',$customer_mobile)->first();
            if($check_sender){
                $customer_name = $check_sender->first_name;
            }else{
                
                $data_sender = array("agentid"=>'vinuparmar1986@gmail.com',
                            "CustomerMobileNo"=>$customer_mobile,
                            "dmt_type"=>$bank_channel);
                $json_sender = json_encode($data_sender);
                $result_sender =  $this->AceMoneyController->getCustomer($json_sender);
                $decode_sender = json_decode($result_sender);
                if(!isset($decode_sender->customer_name)){
                    return response()->json(['success' => false, 'message' => 'Bank Not Responding please try after few min.']);
                }
                $customer_name = $decode_sender->customer_name;
            }
            
            for($i = 0;$i < $loop_count;$i++){
            
                $txnidm = $txnid.'#'.$i;
                $txnidr = $this->UserAuth->txnId('DMT');
                $amount = $amount_par[$i];
                
            if($amount >= 100 && $amount <= 1000){
                if($amount > 1000){
                    $fee = $amount * 1/100;
                }else{
                    $fee = 10;
                }
                
            }else{
                $fee = $amount * 1/100;
            }
            $totalamount = $amount + $fee;
            $gst = $fee - ($fee / 1.18);
            
            //FEE END
            
            
            
            $i_balance = 1;
            acedodmtamount:
            $userwallet = User::find($user->id);
            $balance = $userwallet->wallet->balanceFloat;
            // print_r($balance);exit;
            if($balance < $totalamount){
                if($i == 0){
                    return response()->json(['success' => false, 'message' => 'Insufficient balance.']);
                }else{
                    goto aceskip;
                }
                
                
                
            }
            $wallet = $userwallet->wallet;
            
            
            $txn_wl_fee = $this->WalletCalculation->walletWithdrawFloat($user->id,$fee,'Money Transfer Fee','Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
            
            
            $i_fee = 1;
            acedodmtfee:
            $userwallet = User::find($user->id);
            $wallet = $userwallet->wallet;
            
            
            $txn_wl = $this->WalletCalculation->walletWithdrawFloat($user->id,$amount,'Money Transfer','Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
            
            
            $ins = new transactions_dmt();
            $ins->user_id = $user->id;
            $ins->event = 'DMT';
            $ins->transaction_id = $txnidr;
            $ins->multi_transaction_id = $txnidm;
            $ins->amount = $amount;
            $ins->mobile = $request->customer_mobile;
            $ins->transfer_type = $transfer_type;
            $ins->fee = $fee;
            $ins->tds = 0;
            $ins->gst = $gst;
            $ins->closing_balance = $wallet->balanceFloat;
            $ins->sender_name = $customer_name;
            $ins->ben_name = $beneficiary_name;
            $ins->ben_ac_number = $account;
            $ins->ben_ac_ifsc = $ifsc;
            $ins->utr = 0;
            $ins->status = 0;
            $ins->eko_status = 6;
            $ins->api_id = 3;
            $ins->wallets_uuid = $txn_wl;
            $ins->eko_beneficiary_id = $beneficiary_id;
            $ins->bank_channel = $bank_channel;
            $ins->bank_name = $bank_name;
            $ins->save();
            
            // if ($duplicate) {
            //     // return response()->json(['message' => 'Duplicate entry detected'], 409); // HTTP 409 Conflict
            // }
            
            if($transfer_type == 'IMPS'){
                $payment_mode = 2;
            }else{
                $payment_mode = 1;
            }
            $amount_bill = $amount * 100;
            
            
            
            
            $json_data = 1;
            if($json_data == 1){
                // $update = transactions_dmt::find($ins->id);
                // $update->otp_reference = $json->OTPReferenceID;
                // $update->save();
                $txns[] = $ins->id;
                // return response()->json(['success' => true, 'message' => 'OTP Send On Your Sender Mobile.', 'data'=>$json,'transaction_id'=>$txnidr]);
            }else{
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
                    
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->eko_status = 1;
                    $update->utr = 0;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    $final_response = false;
                    $final_message = "Transaction Failed.";
                    //  return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            aceloopskip:
            }
            aceskip:  
            
            $txn_data = transactions_dmt::select('transaction_id','mobile','otp_reference')->whereIn('id', $txns)->where('status',0)->where('eko_status',6)->get();
            return response()->json(['success' => true, 'message' => 'Transaction Created Please Complete it.','transaction_ids'=>$txns,'transaction_data'=>$txn_data,'transaction_id'=>$txnid]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function aceDmtDoTransactionsOtpSend(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "transaction_id" => "required",
            "latitude" => "required",
            "longitude" => "required",
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
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        if($response)
        {
            $transaction_id = $request->transaction_id;
            $data = transactions_dmt::where('status','0')->where('transaction_id',$transaction_id)->first();
            if($data){
                $customer_mobile = $data->mobile;
                $bank_channel = $data->bank_channel;
                $account = $data->ben_ac_number;
                $amount = $data->amount;
                $latitude = $data->bank_channel;
                $longitude = $data->bank_channel;
                $trans_id = $data->id;
                $bank_channel = $data->bank_channel;
                
                $data_sender = array("agentid"=>'vinuparmar1986@gmail.com',
                            "CustomerMobileNo"=>$customer_mobile,
                            "dmt_type"=>$bank_channel);
                $json_sender = json_encode($data_sender);
                $result_sender =  $this->AceMoneyController->getCustomer($json_sender);
                $decode_sender = json_decode($result_sender);
                if(!isset($decode_sender->customer_name)){
                    return response()->json(['success' => false, 'message' => 'Bank Not Responding please try after few min.']);
                }
                
                
                $beneficiary_name = preg_replace('/[\*\?\[\]\{\}\^\$\(\)\+\|,\.\/\\\\]/', '', $data->ben_name);
            
                $data = array("customerId"=>$decode_sender->id,
                            "BeneName"=>$beneficiary_name,
                            "agentid"=>'vinuparmar1986@gmail.com',
                            "BeneAccountNo"=>$account,
                            "amount"=>$amount,
                            "Latitude"=>$latitude,
                            "longitude"=>$longitude,
                            "PublicIP"=>"216.10.244.132",
                            "dmt_type"=>$bank_channel);
                $json = json_encode($data);
                $result =  $this->AceMoneyController->OTPGenerationTxn($json);
                $json = json_decode($result);
                            
                if (is_object($json) || is_array($json)){
                    
                }else{
                    return response()->json(['success' => false, 'message' => 'Bank Server Not Responding.']);
                }
                
                Log::info("===========TXN SEND OTP ACE==============");
                Log::info($result);
                
                if($json->success){
                    $update = transactions_dmt::find($trans_id);
                    $update->otp_reference = $json->OTPReferenceID;
                    if($bank_channel == 4){
                    $update->otp_request_id = $json->OTPRequestId;
                    }
                    $update->save();
                    
                    return response()->json(['success' => true, 'message' => 'OTP Send.']);
                }
            }else{
                return response()->json(['success' => false, 'message' => 'Transaction Not Found!']);
            }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function aceDmtDoTransactionsOtpVerify(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "transaction_id" => "required",
            "otp" => "required",
            "bank_channel" => "required",
            "OTPReferenceID" => "required",
            "latitude" => "required",
            "longitude" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $bank_channel = $request->get('bank_channel');
            $customer_mobile = $request->customer_mobile;
            $transaction_id = $request->transaction_id;
            $otp = $request->otp;
            
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            
            $data = transactions_dmt::where('status','0')->where('transaction_id',$transaction_id)->first();
            $amount = $data->amount;
            $fee = $data->fee;
            $transfer_type = $data->transfer_type;
            $txnidr = $transaction_id;
            $user = User::find($data->user_id);
            $OtpRefrenceId = $data->otp_reference;
            $OTPRequestId = $data->otp_request_id;
            
            
            $data_sender = array("agentid"=>'vinuparmar1986@gmail.com',
                        "CustomerMobileNo"=>$customer_mobile,
                        "dmt_type"=>$bank_channel);
            $json_sender = json_encode($data_sender);
            $result_sender =  $this->AceMoneyController->getCustomer($json_sender);
            $decode_sender = json_decode($result_sender);
            
            $data_send = array("customerId"=>$decode_sender->id,
                        "beneId"=>$data->eko_beneficiary_id,
                        "OtpRefrenceId"=>$OtpRefrenceId,
                        "OTPRequestId"=>$OTPRequestId,
                        "OtpPin"=>$otp,
                        "paymentMode"=>$transfer_type,
                        "BeneName"=>$data->ben_name,
                        "agentid"=>'vinuparmar1986@gmail.com',
                        "referenceId"=>$transaction_id,
                        "amount"=>$amount,
                        "Latitude"=>$latitude,
                        "longitude"=>$longitude,
                        "PublicIP"=>"216.10.244.132",
                        "dmt_type"=>$bank_channel,
                        "Pid"=>"0");
            $json = json_encode($data_send);
            $result =  $this->AceMoneyController->FundTransfer($json);
            $json = json_decode($result);
            
            
            $userwallet = User::find($data->user_id);
            $wallet = $userwallet->wallet;
            Log::info($result);
            
            if($json->success){
                
                if($json->data->status == "SUCCESS"){
                    //success
                    
                    if(isset($json->data->refnumber)){
                        $utr =  $json->data->refnumber;
                        $DmtTxnId = $json->data->refnumber;
                    }else{
                        $utr = 0;
                        $DmtTxnId = 0;
                    }
                    
                    $update = transactions_dmt::find($data->id);
                    $update->status = 1;
                    $update->eko_status = 0;
                    $update->utr = $utr;
                    $update->tid = $utr;
                    $update->dmt_txn_id = $DmtTxnId;
                    $update->save();
                    
                    //Commission Start
                    $json_com = $this->WalletCalculation->retailorDmt($data->transaction_id);
                    $commission = $json_com['comm'];
                    $tds = $json_com['tds'];
                    $txnid_com = $this->WalletCalculation->txnId('COMM');
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($data->user_id,$commission,"Commission",'Credit_Retailer_'.$userwallet->mobile.'_DMT_Remittance_Commission',$txnid_com);
                    
                    $ins = new user_commissions();
                    $ins->user_id = $data->user_id;
                    $ins->transaction_id = $txnid_com;
                    $ins->total_amount = $commission + $tds;
                    $ins->amount = $commission;
                    $ins->tds = $tds;
                    $ins->tds_par = 0;
                    $ins->wallets_uuid = $wallets_uuid;
                    $ins->ref_transaction_id = $data->transaction_id;
                    $ins->save();
                    
                    $update = transactions_dmt::find($data->id);
                    $update->comm_settl = 1;
                    $update->save();
                    //Commission end
                    
                    // $this->WalletCalculation->distributorDmt($txnidr);
                    // $this->WalletCalculation->retailorDmt($txnidr);
                    
                    $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txnidr on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                    // $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->customer_mobile,$text,1207172205377529606);
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$data->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    // return response()->json(['success' => true, 'message' => 'Transaction Success.', 'data'=>$data]);
                    
                }elseif($json->data->status == "PENDING"){
                    
                    if(isset($json->data->refnumber)){
                        $utr =  $json->data->refnumber;
                        $DmtTxnId = $json->data->refnumber;
                    }else{
                        
                        $DmtTxnId = 0;
                    }
                    
                    $utr = 0;
                    $update = transactions_dmt::find($data->id);
                    $update->eko_status = 0;
                    $update->utr = $utr;
                    $update->tid = $utr;
                    $update->dmt_txn_id = $DmtTxnId;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$data->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    // return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    
                }else{
                    Log::info('AFTER TXN RESULT 000');
                    
                        if(isset($json->data->refnumber)){
                            $utr =  $json->data->refnumber;
                            $DmtTxnId = $json->data->refnumber;
                        }else{
                            $utr = 0;
                            $DmtTxnId = 0;
                        }
                        $update = transactions_dmt::find($data->id);
                        $update->eko_status = 0;
                        $update->utr = $utr;
                        $update->dmt_txn_id = $DmtTxnId;
                        $update->tid = $DmtTxnId;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->where('transactions_dmts.id',$data->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        // return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    
                    
                }
            }else{
                    
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,$txnidr);
                    $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Money Transfer",'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,$txnidr);
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    //         'transaction_id' => $txnidr,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    //         'transaction_id' => $txnidr,
                    //     ]
                    // ]);
                    // $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($data->id);
                    $update->status = 2;
                    $update->eko_status = 1;
                    $update->utr = 0;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$data->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    // return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            
            $txnid = $data->multi_transaction_id;
            $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.multi_transaction_id','LIKE','%'.$txnid.'%')
                    ->whereIn('status',[0,1])
                    ->orderBy('transactions_dmts.id','DESC')->first();
            $summary = transactions_dmt::select(DB::raw('(SELECT transaction_id FROM transactions_dmts WHERE multi_transaction_id LIKE "%'.$txnid.'%" ORDER BY transaction_id ASC LIMIT 1) as transaction_id'),
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->addSelect(DB::raw('SUM(CASE WHEN status IN (0, 1) THEN amount ELSE 0 END) as amount'))
                        ->addSelect(DB::raw('SUM(CASE WHEN status IN (0, 1) THEN fee ELSE 0 END) as fee'))
                        ->addSelect(DB::raw('CASE 
                            WHEN SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) > 0 
                                THEN 0
                            WHEN SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) > 0 
                                THEN 1
                            ELSE 2 
                            END as status'))
                        ->where('multi_transaction_id','LIKE','%'.$txnid.'%')
                        ->groupBy('transactions_dmts.sender_name', 'transactions_dmts.mobile', 'transactions_dmts.bank_name')
                        ->first();
                if($data){
                    $summary_bkp = $summary;
                    $summary = $data;
                    if($summary_bkp->transaction_id){
                        $summary->transaction_id = $summary_bkp->transaction_id;
                    }
                    
                    $summary->amount = $summary_bkp->amount;
                    $summary->fee = $summary_bkp->fee;
                    $summary->status = $summary_bkp->status;
                }else{
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.multi_transaction_id','LIKE','%'.$txnid.'%')
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    $summary_bkp = $summary;
                    $summary = $data;
                    if($summary_bkp->transaction_id){
                        $summary->transaction_id = $summary_bkp->transaction_id;
                    }
                    
                    $summary->amount = $summary_bkp->amount;
                    $summary->fee = $summary_bkp->fee;
                    $summary->status = $summary_bkp->status;
                    
                }
                
                        
            if($summary){
                // Log::info('AFTER TXN RESULT');
                // Log::info($summary);
                if($summary->status == 1 || $summary->status == 0){
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$summary]);
                }else{
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$summary]);
                }
                
            }else{
                return response()->json(['success' => false, 'message' => 'Insufficient balance.Please Check Report.']);
            }
            
        }
        
        
    }
    
    public function aceDmtReceipt(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "transaction_id" => "required|exists:transactions_dmts,transaction_id",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $retailer = User::with('shopDetail')->where('mobile',$request->user_mobile)->first(); 
            $user_id = $retailer->id;
            $id = $request->transaction_id;
            $data_check = transactions_dmt::where('transactions_dmts.transaction_id',$id)->first();
            $txnid = explode("#", $data_check->multi_transaction_id);            
            // $query = transactions_dmt::select('transactions_dmts.transaction_id',
            //             DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"),
            //             'users.mobile as retailer_mobile',
            //             'shop_details.shop_name'
            //         )
            //         ->join('users', 'users.id', 'transactions_dmts.user_id')
            //         ->join('shop_details', 'shop_details.user_id', 'transactions_dmts.user_id')
            //         ->where('transactions_dmts.user_id', $user_id)
            //         ->where('transactions_dmts.event', 'DMT')
            //         ->where('transactions_dmts.transaction_id',$id);
            
            // // Add joins only if transactions_dmts.api_id is not 2
            // if ($data_check->api_id == 0) {
            //     $query->addSelect(
            //         DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),
            //         'dmt_customers.mobile as customer_mobile',
            //         'dmt_beneficiaries.bank_name as bank_name','amount','fee','transactions_dmts.status','transactions_dmts.created_at'
            //     )
            //     ->join('dmt_customers', 'dmt_customers.mobile', 'transactions_dmts.mobile')
            //     ->leftJoin('dmt_beneficiaries', 'dmt_beneficiaries.id', 'transactions_dmts.dmt_beneficiary_id')
            //     ->where('dmt_customers.status', 1);
            //     $data = $query->first();
            // }else{
            //     $query->addSelect(
            //         'transactions_dmts.sender_name as customer_name',
            //         'transactions_dmts.mobile as customer_mobile',
            //         'transactions_dmts.bank_name','amount','fee','transactions_dmts.status','transactions_dmts.created_at'
            //     )->where('multi_transaction_id','LIKE','%'.$txnid[0].'%');
            //     $data = $query->first();
                
            //     $data_mult = transactions_dmt::select(DB::raw('SUM(amount) as amount'),DB::raw('SUM(fee) as fee'))
            //                 ->where('multi_transaction_id','LIKE','%'.$txnid[0].'%')
            //                 ->first();
            //                 // print_r($data_mult);exit;
            //     $data->amount = $data_mult->amount;
            //     $data->fee = $data_mult->fee; 
                
            //     $data->created_at = $data_check->created_at;
            // }
            
            $txnid = $txnid[0];
            $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.multi_transaction_id','LIKE','%'.$txnid.'%')
                    ->whereIn('status',[0,1])
                    ->orderBy('transactions_dmts.id','DESC')->first();
            $summary = transactions_dmt::select(DB::raw('(SELECT transaction_id FROM transactions_dmts WHERE multi_transaction_id LIKE "%'.$txnid.'%" ORDER BY transaction_id ASC LIMIT 1) as transaction_id'),
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->addSelect(DB::raw('SUM(CASE WHEN status IN (0, 1) THEN amount ELSE 0 END) as amount'))
                        ->addSelect(DB::raw('SUM(CASE WHEN status IN (0, 1) THEN fee ELSE 0 END) as fee'))
                        ->addSelect(DB::raw('CASE 
                            WHEN SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) > 0 
                                THEN 0
                            WHEN SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) > 0 
                                THEN 1
                            ELSE 2 
                            END as status'))
                        ->where('multi_transaction_id','LIKE','%'.$txnid.'%')
                        ->groupBy('transactions_dmts.sender_name', 'transactions_dmts.mobile', 'transactions_dmts.bank_name')
                        ->first();
                if($data){
                    $summary_bkp = $summary;
                    $summary = $data;
                    if($summary_bkp->transaction_id){
                        $summary->transaction_id = $summary_bkp->transaction_id;
                    }
                    
                    $summary->amount = $summary_bkp->amount;
                    $summary->fee = $summary_bkp->fee;
                    $summary->status = $summary_bkp->status;
                }else{
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.multi_transaction_id','LIKE','%'.$txnid.'%')
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    $summary_bkp = $summary;
                    $summary = $data;
                    if($summary_bkp->transaction_id){
                        $summary->transaction_id = $summary_bkp->transaction_id;
                    }
                    
                    $summary->amount = $summary_bkp->amount;
                    $summary->fee = $summary_bkp->fee;
                    $summary->status = $summary_bkp->status;
                    
                }
                
                        
            if($summary){
                return response()->json(['success' => true, 'message' => 'Transaction.', 'data'=>$summary]);
            }else{
                return response()->json(['success' => false, 'message' => 'Transaction.']);
            }
        
            // return response()->json(['success' => true, 'message' => 'Transaction.', 'data'=>$data]);
        }
    }
    
    //RECHARGE
    public function rkWalletRecharge(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "recharge_mobile" => "required",
            "op_code" => "required",
            "amount" => "required",
            "recharge_type" => "required",
            "latitude" => "required",
            "longitude" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $mobile = $request->get('recharge_mobile');
            $op_code = $request->get('op_code');
            $amount = $request->get('amount');
            $recharge_type = $request->get('recharge_type');
            
            if($recharge_type == 'PREPAID'){
                $recharge_type = 'PREPAID';
                $prifix = 'RC';
            }elseif($recharge_type == 'DTH'){
                $recharge_type = 'DTH';
                $prifix = 'DTH';
            }else{
                return response()->json(['success' => false, 'message' => 'Please select Recharge TYPE']);
            }
            
            $user = User::where('mobile',$request->user_mobile)->first();
            
            sleep(rand(2,5));
            sleep(rand(1,3));
            
            $op = ace_operators::where('op_code',$op_code)->where('status',1)->first();
            if(!$op){
                return response()->json(['success' => false, 'message' => 'Network Not Responding.']);
            }
            if(empty($op->rk_op_code) || $op->rk_op_code == NULL){
                return response()->json(['success' => false, 'message' => 'Network Not Responding.']);
            }
            $op_name = $op->name;
            $txnidr = $this->UserAuth->txnId($prifix);
            
            $recentTime = Carbon::now()->subMinutes(5);
            $duplicate = transactions_recharges::where('user_id', $user->id)
                            ->where('mobile', $mobile)
                            ->where('op_id', $op->id)
                            ->where('op_code', $op_code)
                            ->where('amount', $amount)
                            ->where('created_at', '>=', $recentTime)
                            ->exists();
            if ($duplicate) {
                log::info('Duplicate');
                log::info($request->all());
                return response()->json(['success' => false, 'message' => 'Duplicate! By Mistake Double Click or Duplicat Transaction. Please Check you report.']);
            }
            
            $user_debit = $this->WalletCalculation->walletWithdrawFloat($user->id,$amount,"Recharge","Debit_recharge_$mobile",$txnidr);
            
            $ins = new transactions_recharges();
            $ins->user_id = $user->id;
            $ins->transaction_id = $txnidr;
            $ins->event = $recharge_type;
            $ins->amount = $amount;
            $ins->mobile = $mobile;
            $ins->op_id = $op->id;
            $ins->op_code = $op_code;
            $ins->status = 0;
            $ins->wallets_uuid = $user_debit;
            $ins->save();
            
            $url = $op->rk_op_code.'$'.$mobile.'$'.$amount.'$9086$0$'.$txnidr;
            
            $api_response = $this->ApiCalls->rkwalletRecharge($url);
            $decode = explode("=",$api_response);
            if($decode[0] == 0){
                if($decode[1] == 'Success'){
                    $i = count($decode);
                    $j = $i-1;
                    
                    $up = transactions_recharges::find($ins->id);
                    $up->status = 1;
                    $up->referenceId = $decode[$j];
                    $up->save();
                }
                
                $data = transactions_recharges::select('transactions_recharges.*','ace_operators.name')
            ->join('ace_operators','ace_operators.id','transactions_recharges.op_id')
            ->where('transactions_recharges.id',$ins->id)->first();
                return response()->json(['success' => true, 'message' => 'Recharge Transaction initiated.', 'data'=>$data]);
            }else{
                $up = transactions_recharges::find($ins->id);
                $up->status = 2;
                $up->save();
                
                $user_debit = $this->WalletCalculation->walletDepositFloat($user->id,$amount,"Recharge","Refund_recharge_$mobile",$txnidr);
                
                $data = transactions_recharges::select('transactions_recharges.*','ace_operators.name')
            ->join('ace_operators','ace_operators.id','transactions_recharges.op_id')
            ->where('transactions_recharges.id',$ins->id)->first();
                return response()->json(['success' => false, 'message' => 'Recharge Transaction Failed.', 'data'=>$data]);
            }
            
            
            
            
        }else{
            return response()->json(['success' => false, 'message' => 'Failed.']);
        }
    }
    
    public function mplanRecharge(Request $request) {
        $op = ace_operators::where('op_code',$request->op)->where('status',1)->first();
        
        $url = "https://www.mplan.in/api/plans.php?apikey=c96e9b77a6f55792b4c4709dad6c0eba&offer=roffer&tel=".$request->mobile."&operator=".urlencode($op->mplan_op_cde);
        $api_response = $this->ApiCalls->mplan($url);
        $data = json_decode($api_response);
        return response()->json($data->records);
    }
    
    public function mplanDth(Request $request) {
        $op = ace_operators::where('op_code',$request->op)->where('status',1)->first();
        $url = "https://www.mplan.in/api/dthplans.php?apikey=c96e9b77a6f55792b4c4709dad6c0eba&operator=".urlencode($op->mplan_op_cde);
        $api_response = $this->ApiCalls->mplan($url);
        $data = json_decode($api_response);
        return response()->json($data->records->Plan);
    }
    
    public function mplanRechargeMobile(Request $request) {
        $op = ace_operators::where('op_code',$request->op)->where('status',1)->first();
        $url = "https://www.mplan.in/api/plans.php?apikey=c96e9b77a6f55792b4c4709dad6c0eba&offer=roffer&tel=".$request->mobile."&operator=".urlencode($op->mplan_op_cde);
        $api_response = $this->ApiCalls->mplan($url);
        $data = json_decode($api_response);
        return response()->json(['success' => true, 'message' => '', 'data'=>$data]);
    }
    
    public function rechargeOP(Request $request) {
        $op = ace_operators::select('name','op_code','op_image')->where('op_type',$request->type)->where('status',1)->get();
        return response()->json(['success' => true, 'message' => '', 'data'=>$op]);
    }
    
    public function mplanRechargeDth(Request $request) {
        $op = ace_operators::where('op_code',$request->op)->where('status',1)->first();
        $url = "https://www.mplan.in/api/dthplans.php?apikey=c96e9b77a6f55792b4c4709dad6c0eba&operator=".urlencode($op->mplan_op_cde);
        $api_response = $this->ApiCalls->mplan($url);
        $data = json_decode($api_response);
        return response()->json(['success' => true, 'message' => '', 'data'=>$data]);
    }
    
    public function mplanRechargeDthInfo(Request $request) {
        $op = ace_operators::where('op_code',$request->op)->where('status',1)->first();
        $url = "https://www.mplan.in/api/DthinfoMobile.php?apikey=c96e9b77a6f55792b4c4709dad6c0eba&offer=roffer&tel=".$op->mobile."&operator=".urlencode($op->mplan_op_cde);
        $api_response = $this->ApiCalls->mplan($url);
        $data = json_decode($api_response);
        return response()->json(['success' => true, 'message' => '', 'data'=>$data]);
    }
    
    public function rechargeReport(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "start_date" => "required",
            "end_date" => "required",
            "event" => "required",
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
            
            $data = transactions_recharges::select('transactions_recharges.*','ace_operators.name')
            ->join('ace_operators','ace_operators.id','transactions_recharges.op_id')
            ->where('transactions_recharges.user_id',$user->id)
            ->where('transactions_recharges.event',$request->event)
            ->whereBetween('transactions_recharges.created_at', array($from, $to))
            ->orderBy('transactions_recharges.id','DESC')->get();
            
            return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    public function bbpsCategoriesInsert(Request $request)
    {
        //bbps_categories
        $res = $this->PlutosController->getCategories('a');
        $data = json_decode($res);
        $count = count($data->payload->categories);
        $arr = $data->payload->categories;
        
        bbps_categories::query()->update(['status' => 0]);
        for($i=0;$i<$count;$i++){
            
            
            $check = bbps_categories::where('name',$arr[$i])->first();
            if($check){
                $check->status = 1;
                $check->save();
            }else{
                $ins = new bbps_categories();
                $ins->name = $arr[$i];
                $ins->status = 1;
                $ins->save();
            }
        }
        
    }
    
    public function bbpsBillerInsert(Request $request)
    {
        $categories = bbps_categories::where('status',1)->get();
        foreach($categories as $rr){
            $service_name = $rr->name;
            $res = $this->PlutosController->getBiller($service_name);
            $data_res = json_decode($res,true);
            // echo $rr->name; print_r($data_res['payload']['data']);echo "======================<br><br><br>======================";
            if(!is_null($data_res['payload']['data'])){
                $count = count($data_res['payload']['data']);
                $arr = $data_res['payload']['data'];
                echo $rr->name;
                for($i=0;$i<$count;$i++){
                    try {
                        $jsonData = $arr[$i];
                        $data = $jsonData;
                        $check_bill = bbps_billers::where('biller_id',$data['billerId'])->get()->count();
                        
                        if($check_bill == 0){
                            DB::beginTransaction();
                            
                            // Parse JSON data
                            
                            
                            // Insert into bbps_billers table
                            $billerId = bbps_billers::insertGetId([
                                'biller_id' => $data['billerId'],
                                'biller_name' => $data['billerName'],
                                'biller_alias_name' => $data['billerAliasName'],
                                'biller_category_id' => $rr->id,
                                'biller_mode' => $data['billerMode'],
                                'biller_accepts_adhoc' => $data['billerAcceptsAdhoc'] === 'true',
                                'parent_biller' => $data['parentBiller'] === 'true',
                                'biller_ownership' => $data['billerOwnerShp'],
                                'biller_coverage' => $data['billerCoverage'],
                                'fetch_requirement' => $data['fetchRequirement'],
                                'payment_amount_exactness' => $data['paymentAmountExactness'],
                                'support_bill_validation' => $data['supportBillValidation'],
                                'biller_effective_from' => $data['billerEffctvFrom'] ? Carbon::parse($data['billerEffctvFrom']) : null,
                                'biller_effective_to' => $data['billerEffctvTo'] ? Carbon::parse($data['billerEffctvTo']) : null,
                                'biller_temp_deactivation_start' => $data['billerTempDeactivationStart'] ? Carbon::parse($data['billerTempDeactivationStart']) : null,
                                'biller_temp_deactivation_end' => $data['billerTempDeactivationEnd'] ? Carbon::parse($data['billerTempDeactivationEnd']) : null,
                                'status' => $data['Status'] == 'ACTIVE' ? 1 : 0,
                                // 'biller_timeout' => $data['billerTimeOut'],
                                'plan_mdm_requirement' => $data['planMdmRequirement'],
                                'support_deemed' => $data['supportDeemed'],
                                'support_pending_status' => $data['supportPendingStatus']
                            ]);
                            
                            // Insert into bbps_biller_payment_modes table
                            if (!empty($data['billerPaymentModes'])) {
                                $paymentModes = [];
                                foreach ($data['billerPaymentModes'] as $mode) {
                                    $paymentModes[] = [
                                        'biller_id' => $data['billerId'],
                                        'payment_mode' => $mode['paymentMode'],
                                        'max_limit' => $mode['maxLimit']  ?? 0,
                                        'min_limit' => $mode['minLimit']  ?? 0,
                                        // 'support_pending_status' => $mode['supportPendingStatus']
                                    ];
                                }
                                bbps_biller_payment_modes::insert($paymentModes);
                            }
                            
                            // Insert into bbps_biller_payment_channels table
                            if (!empty($data['billerPaymentChannels'])) {
                                $paymentChannels = [];
                                foreach ($data['billerPaymentChannels'] as $channel) {
                                    $paymentChannels[] = [
                                        'biller_id' => $data['billerId'],
                                        'payment_channel' => $channel['paymentChannel'],
                                        'max_limit' => $channel['maxLimit'] ?? 0,
                                        'min_limit' => $channel['minLimit'] ?? 0,
                                        // 'support_pending_status' => $channel['supportPendingStatus']
                                    ];
                                }
                                bbps_biller_payment_channels::insert($paymentChannels);
                            }
                            
                            // Insert into bbps_biller_customer_params table
                            if (!empty($data['billerCustomerParams'])) {
                                $customerParams = [];
                                foreach ($data['billerCustomerParams'] as $param) {
                                    $customerParams[] = [
                                        'biller_id' => $data['billerId'],
                                        'param_name' => $param['paramName'],
                                        'data_type' => $param['dataType'],
                                        'is_optional' => $param['optional'] == 'true' ? 0 : 1,
                                        'min_length' => $param['minLength'] ?? 0,
                                        'max_length' => $param['maxLength'] ?? 0,
                                        'regex_pattern' => $param['regex'] ?? '',
                                        'visibility' => $param['visibility'] == 'true' ? 1 : 0,
                                        'encryption_type' => $param['encryptionType']
                                    ];
                                }
                                bbps_biller_customer_params::insert($customerParams);
                            }
                            
                            // Insert into bbps_biller_additional_info table
                            if (!empty($data['billerAdditionalInfo'])) {
                                $additionalInfo = [];
                                foreach ($data['billerAdditionalInfo'] as $info) {
                                    $additionalInfo[] = [
                                        'biller_id' => $data['billerId'],
                                        'param_name' => $info['paramName'],
                                        'data_type' => $info['dataType'],
                                        'is_optional' => $info['optional'] == 'true' ? 0 : 1
                                    ];
                                }
                                bbps_biller_additional_info::insert($additionalInfo);
                            }
                            
                            // Insert into bbps_biller_response_params table
                            // if (!empty($data['billerResponseParams'])) {
                            //     $responseParams = [];
                                
                            //     // Handle amountOptions
                            //     if (!empty($data['billerResponseParams']['amountOptions'])) {
                            //         $amountOptions = $data['billerResponseParams']['amountOptions'];
                            //         $responseParams[] = [
                            //             'biller_id' => $data['billerId'],
                            //             'param_type' => 'amountOptions',
                            //             'param_name' => 'amountBreakupSet',
                            //             'param_value' => $amountOptions['amountBreakupSet']
                            //         ];
                            //     }
                                
                            //     if (!empty($responseParams)) {
                            //         bbps_biller_response_params::insert($responseParams);
                            //     }
                            // }
                            
                            DB::commit();
                        
                        }//if
                        
                    } catch (\Exception $e) {
                        DB::rollback();
                        log::info($e->getMessage());
                    }
                } //for
            } //IF
        } // foreach
    }
    
    public function bbpsBillerIDInsert(Request $request)
    {
        $categories = bbps_categories::where('status',1)->where('id',21)->first();
        
            $service_name = $categories->name;
            $res = $this->PlutosController->getBillerByID('ATPOST000OU122');
            $data_res = json_decode($res,true);
            echo $categories->name; print_r($data_res['payload']['data']);echo "======================<br><br><br>======================";
            if(!is_null($data_res['payload']['data'])){
                $count = count($data_res['payload']['data']);
                $arr = $data_res['payload']['data'];
                echo $count;
                
                    try {
                        $jsonData = $arr;
                        $data = $jsonData;
                        $check_bill = bbps_billers::where('biller_id',$data['billerId'])->get()->count();
                        
                        if($check_bill == 0){
                            DB::beginTransaction();
                            
                            // Parse JSON data
                            
                            
                            // Insert into bbps_billers table
                            $billerId = bbps_billers::insertGetId([
                                'biller_id' => $data['billerId'],
                                'biller_name' => $data['billerName'],
                                'biller_alias_name' => $data['billerAliasName'],
                                'biller_category_id' => $categories->id,
                                'biller_mode' => $data['billerMode'],
                                'biller_accepts_adhoc' => $data['billerAcceptsAdhoc'] === 'true',
                                'parent_biller' => $data['parentBiller'] === 'true',
                                'biller_ownership' => $data['billerOwnerShp'],
                                'biller_coverage' => $data['billerCoverage'],
                                'fetch_requirement' => $data['fetchRequirement'],
                                'payment_amount_exactness' => $data['paymentAmountExactness'],
                                'support_bill_validation' => $data['supportBillValidation'],
                                'biller_effective_from' => $data['billerEffctvFrom'] ? Carbon::parse($data['billerEffctvFrom']) : null,
                                'biller_effective_to' => $data['billerEffctvTo'] ? Carbon::parse($data['billerEffctvTo']) : null,
                                'biller_temp_deactivation_start' => $data['billerTempDeactivationStart'] ? Carbon::parse($data['billerTempDeactivationStart']) : null,
                                'biller_temp_deactivation_end' => $data['billerTempDeactivationEnd'] ? Carbon::parse($data['billerTempDeactivationEnd']) : null,
                                'status' => $data['Status'] == 'ACTIVE' ? 1 : 0,
                                // 'biller_timeout' => $data['billerTimeOut'],
                                'plan_mdm_requirement' => $data['planMdmRequirement'],
                                'support_deemed' => $data['supportDeemed'],
                                'support_pending_status' => $data['supportPendingStatus']
                            ]);
                            
                            // Insert into bbps_biller_payment_modes table
                            if (!empty($data['billerPaymentModes'])) {
                                $paymentModes = [];
                                foreach ($data['billerPaymentModes'] as $mode) {
                                    $paymentModes[] = [
                                        'biller_id' => $data['billerId'],
                                        'payment_mode' => $mode['paymentMode'],
                                        'max_limit' => $mode['maxLimit']  ?? 0,
                                        'min_limit' => $mode['minLimit']  ?? 0,
                                        // 'support_pending_status' => $mode['supportPendingStatus']
                                    ];
                                }
                                bbps_biller_payment_modes::insert($paymentModes);
                            }
                            
                            // Insert into bbps_biller_payment_channels table
                            if (!empty($data['billerPaymentChannels'])) {
                                $paymentChannels = [];
                                foreach ($data['billerPaymentChannels'] as $channel) {
                                    $paymentChannels[] = [
                                        'biller_id' => $data['billerId'],
                                        'payment_channel' => $channel['paymentChannel'],
                                        'max_limit' => $channel['maxLimit'] ?? 0,
                                        'min_limit' => $channel['minLimit'] ?? 0,
                                        // 'support_pending_status' => $channel['supportPendingStatus']
                                    ];
                                }
                                bbps_biller_payment_channels::insert($paymentChannels);
                            }
                            
                            // Insert into bbps_biller_customer_params table
                            if (!empty($data['billerCustomerParams'])) {
                                $customerParams = [];
                                foreach ($data['billerCustomerParams'] as $param) {
                                    $customerParams[] = [
                                        'biller_id' => $data['billerId'],
                                        'param_name' => $param['paramName'],
                                        'data_type' => $param['dataType'],
                                        'is_optional' => $param['optional'] == 'true' ? 0 : 1,
                                        'min_length' => $param['minLength'] ?? 0,
                                        'max_length' => $param['maxLength'] ?? 0,
                                        'regex_pattern' => $param['regex'] ?? '',
                                        'visibility' => $param['visibility'] == 'true' ? 1 : 0,
                                        'encryption_type' => $param['encryptionType']
                                    ];
                                }
                                bbps_biller_customer_params::insert($customerParams);
                            }
                            
                            // Insert into bbps_biller_additional_info table
                            if (!empty($data['billerAdditionalInfo'])) {
                                $additionalInfo = [];
                                foreach ($data['billerAdditionalInfo'] as $info) {
                                    $additionalInfo[] = [
                                        'biller_id' => $data['billerId'],
                                        'param_name' => $info['paramName'],
                                        'data_type' => $info['dataType'],
                                        'is_optional' => $info['optional'] == 'true' ? 0 : 1
                                    ];
                                }
                                bbps_biller_additional_info::insert($additionalInfo);
                            }
                            
                            // Insert into bbps_biller_response_params table
                            // if (!empty($data['billerResponseParams'])) {
                            //     $responseParams = [];
                                
                            //     // Handle amountOptions
                            //     if (!empty($data['billerResponseParams']['amountOptions'])) {
                            //         $amountOptions = $data['billerResponseParams']['amountOptions'];
                            //         $responseParams[] = [
                            //             'biller_id' => $data['billerId'],
                            //             'param_type' => 'amountOptions',
                            //             'param_name' => 'amountBreakupSet',
                            //             'param_value' => $amountOptions['amountBreakupSet']
                            //         ];
                            //     }
                                
                            //     if (!empty($responseParams)) {
                            //         bbps_biller_response_params::insert($responseParams);
                            //     }
                            // }
                            
                            DB::commit();
                        
                        }//if
                        
                    } catch (\Exception $e) {
                        DB::rollback();
                        log::info($e->getMessage());
                    }
                
            } //IF
        
    }
    
    public function bbpsFetchBill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "pay_biller_id" => "required",
            "pay_payer_mob" => "required",
            "pay_payer_name" => "required",
            "param" => "required",
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
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $biller_id = $request->get('pay_biller_id');
            $pay_payer_mob = $request->get('pay_payer_mob');
            $pay_payer_name = $request->get('pay_payer_name');
            $param = $request->get('param');
            
            $param_tbl = bbps_biller_customer_params::where('biller_id',$biller_id)->orderBy('id','ASC')->get();
            foreach($param_tbl as $k => $r){
                $customerDetails[$r->param_name] = $param[$k];
            }
            
            $string = $this->UserAuth->txnIdPlutos('BA');
            $refId = $string;
            
            $now = Carbon::now('Asia/Kolkata');
            $data = [
                'refId' => $refId,
                'customerParams' => $customerDetails,
                'customerMobileNumber' => '9651137248',
                'agentId' => 'IF31IF03INT524833871',
                'billerId' => $biller_id,
                'deviceDetails' => [
                    'MAC' => '54-E1-AD-27-FE-E3',
                    'IP' => '216.10.244.132',
                ],
                'customerDetails' => [
                    'Email' => 'john.doe@me.com',
                ],
                'timeStamp' => $now->format('c'),
            ];
            
            // Convert to JSON
            $json = json_encode($data, JSON_PRETTY_PRINT);
            $res = $this->PlutosController->getFetch($json);
            $decode = json_decode($res);
            if($decode->code == 200){
                $data_res = [
                    "accountHolderName"=>$decode->payload->billerResponse->accountHolderName,
                    "amount"=>$decode->payload->billerResponse->amount,
                    "dueDate"=>$decode->payload->billerResponse->dueDate,
                    "billDate"=>$decode->payload->billerResponse->billDate,
                    "billPeriod"=>$decode->payload->billerResponse->billPeriod,
                    ];
                
                return response()->json(['success' => true, 'message' => '','billerResponse'=>$data_res]);
            }else{
                return response()->json(['success' => false, 'message' => $decode->message]);
            }
            
        }else{
            return response()->json(['success' => false, 'message' => 'Failed.']);
        }
        
        
    }
    
    public function bbpsPayBill(Request $request)
    {
            $string = $this->UserAuth->txnIdPlutos('BA');
            $refId = $string;
            $biller_id = 'ATPOST000OU122';
            $param_tbl = bbps_biller_customer_params::where('biller_id',$biller_id)->orderBy('id','ASC')->get();
            foreach($param_tbl as $k => $r){
                if($k == 0){
                     $customerDetails[$r->param_name] = '9910606584';
                }elseif($k == 1){
                    $customerDetails[$r->param_name] = '22900';
                }else{
                     $customerDetails[$r->param_name] = '8898040005';
                }
                //$customerDetails[$r->param_name] = '80003948751';
                
                
            }
            print_r($customerDetails);
            echo "<br><br><br>";
            $now = Carbon::now('Asia/Kolkata');
            $data = [
                'refId' => $refId,
                'customerParams' => $customerDetails,
                'customerMobileNumber' => '9651137248',
                'agentId' => 'IF31IF03INT524833871',
                'billerId' => $biller_id,
                'deviceDetails' => [
                    'MAC' => '54-E1-AD-27-FE-E3',
                    'IP' => '216.10.244.132',
                ],
                'customerDetails' => [
                    'Email' => 'john.doe@me.com',
                ],
                'timeStamp' => $now->format('c'),
            ];
            
            // Convert to JSON
            $json = json_encode($data, JSON_PRETTY_PRINT);
            print_r($json);
            $res = $this->PlutosController->getFetch($json);
            
            $decode = json_decode($res);
        // return $decode; 
        echo "<br>";
        echo "BILL FETCH";
        echo "<br>";
        print_r($res);
        echo "<br>";
        print_r($decode);
        echo "<br>";
        echo "BILL PAY";
        echo "<br>";
         $string = $this->UserAuth->txnIdPlutos('BA');
         $refId = $string;    
        
        sleep(5);
        $billPaymentArray = [
            "refId" => $decode->payload->refId,
            "agentId" => "IF31IF03INT524833871",
            "billerId" => $biller_id,
            "customerParams" => $customerDetails,
            "deviceDetails" => [
                "MAC" => "54-E1-AD-27-FE-E3",
                "IP" => "216.10.244.132"
            ],
            "customerDetails" => [
                "EMAIL" => "customer@example.com",
            ],
            "customerMobile" => "8953176688",
            "isQuickPay" => "No",
            "amount" => [
                "amount" => $decode->payload->billerResponse->amount,
            ],
            "paymentInformation" => [
                "CardNum|AuthCode" => "7890|78999",
            ],
            "txn" => [
                "ts" => $now->format('c'),
                "paymentRefId" => "ABCD1234abcd"
            ],
            "paymentMethod" => [
                "splitPay" => "No",
                "OFFUSPay" => "Yes",
                "paymentMode" => "Credit Card"
            ]
        ];
        
        
        $json_pay = json_encode($billPaymentArray,JSON_PRETTY_PRINT);
        
        $res = $this->PlutosController->PayBill($json_pay);
        print_r($res);
    }
    
    public function bulkpeCreateSender(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "name" => "required",
            "pan" => "required",
            "aadhar" => "required",
            "mobile" => "required",
            "card_number" => "required|string|min:13|max:19",
            "cvv" => "required|string|min:3|max:4",
            "expiry" => "required|string",
            "sender_create_id" => "required",
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
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            if($request->sender_create_id != 0){
                $sender = CcSenders::find($request->sender_create_id);
                $name = $sender->name;
                $pan = $sender->pan;
                $aadhar = $sender->aadhar_number;
                $mobile = $sender->mobile;
                $card_number = $sender->card_number;
                $cvv = $sender->cvv;
                $expiry = $sender->expiry;
                
                // bulkpePostCall
            }else{
                
                $name = $request->name;
                $pan = $request->pan;
                $aadhar = $request->aadhar;
                $mobile = $request->mobile;
                $card_number = $request->card_number;
                $cvv = $request->cvv;
                $expiry = $request->expiry;
                
                $sender = new CcSenders();
                $sender->user_id = $user->id;
                $sender->name = $request->name;
                $sender->pan = $request->pan;
                $sender->aadhar_number = $request->aadhar;
                
                $sender->card_number = $request->card_number;
                $sender->expiry = $request->expiry;
                $sender->cvv = $request->cvv;
                $sender->mobile = $request->mobile;
                $sender->card_type = $request->card_type;
                
                
                if ($request->input('cc_front')) {
                    $image = $request->input('cc_front');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $panimagename_fron = 'CCFRONT' . $user->mobile . time() . '.jpg';
                    $filePath = $panimagename_fron;
                    Storage::disk('public_img_cc')->put($filePath, base64_decode($image));
                }
                
                if ($request->input('cc_back')) {
                    $image = $request->input('cc_back');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $panimagename_back = 'CCBACK' . $user->mobile . time() . '.jpg';
                    $filePath = $panimagename_back;
                    Storage::disk('public_img_cc')->put($filePath, base64_decode($image));
                }
                
                $sender->cc_front = $panimagename_fron;
                $sender->cc_back = $panimagename_back;
                
                
                $sender->save();
                
            }
            $txnidr = $this->UserAuth->txnId('CCS');
            $userData = [
                        'referenceId' => $txnidr,
                        'name' => $name,
                        'pan' => $pan,
                        'aadhar' => $aadhar,
                        'phone' => $mobile,
                        'cardNo' => $card_number,
                        'cvv' => $cvv,
                        'expiry' => $expiry
                    ];
            $json_data = json_encode($userData,JSON_UNESCAPED_SLASHES);
            $url = "https://api.bulkpe.in/client/cc/createSender";
            $response = $this->ApiCalls->bulkpePostCall($url,$json_data);
            $decode = json_decode($response);
            
            if($decode->status == true){
                $senderId = $decode->data->senderId;
                $referenceId = $decode->data->referenceId;
                $nameInPan = $decode->data->nameInPan;
                $charge = $decode->data->charge;
                $gst = $decode->data->gst;
                
                $sender_u = CcSenders::find($sender->id);
                $sender_u->sender_id = $senderId;
                $sender_u->reference_id = $referenceId;
                $sender_u->name_in_pan = $nameInPan;
                $sender_u->charge = $charge;
                $sender_u->gst = $gst;
                $sender_u->save();
                
                
                $fullPath_front = public_path('uploads/creditcard/').$sender->cc_front;
                $fullPath_back = public_path('uploads/creditcard/').$sender->cc_back;
                
                // Validate file exists
                if (!file_exists($fullPath_front)) {
                    log::channel('bulkpeapi')->info("File not found: " . $fullPath_front);
                    
                }
                
                // Validate file is readable
                if (!is_readable($fullPath_front)) {
                    log::channel('bulkpeapi')->info("File not readable: " . $fullPath_front);
                    
                }
                $realPath_front = realpath($fullPath_front);
                $realPath_back = realpath($fullPath_back);
                if ($fullPath_front === false) {
                    log::channel('bulkpeapi')->info("Could not resolve real path for: " . $fullPath_front);
                    
                }

                $mimeType_front = mime_content_type($realPath_front);
                $fullPath_front = new CURLFile($realPath_front, $mimeType_front, basename($realPath_front));
                
                $mimeType_back = mime_content_type($realPath_back);
                $fullPath_back = new CURLFile($realPath_back, $mimeType_back, basename($realPath_back));

                $url = "https://api.bulkpe.in/client/cc/uploadCreditcard";
                $array_data= array(
                                'senderId' => $senderId,
                                'cardImageType' => 'front',
                                'file' => $fullPath_front
                            );
                
                $response = $this->ApiCalls->bulkpePostCallFile($url,$array_data);
                $array_data= array(
                                'senderId' => $senderId,
                                'cardImageType' => 'back',
                                'file' => $fullPath_back
                            );
                $response = $this->ApiCalls->bulkpePostCallFile($url,$array_data);
                
                $txn_wl = $this->WalletCalculation->walletWithdrawFloat($user->id,10,'PAN Verification','Debit_Retailer_'.$user->mobile.'_PAN_Verification_Fee_'.$pan,$txnidr);
                
                return response()->json(['success' => true, 'message' => 'Sender Created.', 'data'=>$sender_u]);
            }else{
                return response()->json(['success' => false, 'message' => 'Failed.']);
            }
        }
    }
    
    public function bulkpeUploadSender(Request $request)
    {
        $sender = CcSenders::find(21);
        $senderId = $sender->sender_id;
        
        $fullPath_front = public_path('uploads/creditcard/').$sender->cc_front;
                $fullPath_back = public_path('uploads/creditcard/').$sender->cc_back;
                
                // Validate file exists
                if (!file_exists($fullPath_front)) {
                    log::channel('bulkpeapi')->info("File not found: " . $fullPath_front);
                    
                }
                
                // Validate file is readable
                if (!is_readable($fullPath_front)) {
                    log::channel('bulkpeapi')->info("File not readable: " . $fullPath_front);
                    
                }
                $realPath_front = realpath($fullPath_front);
                $realPath_back = realpath($fullPath_back);
                if ($fullPath_front === false) {
                    log::channel('bulkpeapi')->info("Could not resolve real path for: " . $fullPath_front);
                    
                }

                $mimeType_front = mime_content_type($realPath_front);
                $fullPath_front = new CURLFile($realPath_front, $mimeType_front, basename($realPath_front));
                
                $mimeType_back = mime_content_type($realPath_back);
                $fullPath_back = new CURLFile($realPath_back, $mimeType_back, basename($realPath_back));

                $url = "https://api.bulkpe.in/client/cc/uploadCreditcard";
                $array_data= array(
                                'senderId' => $senderId,
                                'cardImageType' => 'front',
                                'file' => $fullPath_front
                            );
                
                $response = $this->ApiCalls->bulkpePostCallFile($url,$array_data);
                $array_data= array(
                                'senderId' => $senderId,
                                'cardImageType' => 'back',
                                'file' => $fullPath_back
                            );
                $response = $this->ApiCalls->bulkpePostCallFile($url,$array_data);
    }
    
    public function bulkpegetSender(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "mobile" => "required",
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
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $sender_u = CcSenders::where('mobile',$request->mobile)->get();
            
            if($sender_u){
                return response()->json(['success' => true, 'message' => 'Senders.','data'=>$sender_u]);
            }else{
                return response()->json(['success' => true, 'message' => 'Senders.','data'=>$sender_u]);
            }
            
            
        }
    }
    
    public function bulkpegetBenf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "sender_id" => "required",
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
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $sender_u = CcBeneficiaries::where('sender_id',$request->sender_id)->where('status',1)->get();
            
            return response()->json(['success' => true, 'message' => 'Beneficiaries.','data'=>$sender_u]);
        }
    }
    
    public function bulkpeAddBenf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "sender_id" => "required",
            "name" => "required",
            "account" => "required",
            "ifsc" => "required",
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
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        //$response = 1;

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $sender_id = $request->sender_id;
            $name = $request->name;
            $account = $request->account;
            $ifsc = $request->ifsc;
            $txnidr = $this->UserAuth->txnId('BENF');
            $userData = [
                        'reference' => $txnidr,
                        'name' => $name,
                        'accountNumber' => $account,
                        'ifsc' => $ifsc
                    ];
            $json_data = json_encode($userData,JSON_UNESCAPED_SLASHES);
            $url = "https://api.bulkpe.in/client/cc/createBeneficiary";
            $response = $this->ApiCalls->bulkpePostCall($url,$json_data);
            $decode = json_decode($response);
            
            if($decode->status == true){
                $data = $decode->data;
                $ins = new CcBeneficiaries();
                $ins->user_id = $user->id;
                $ins->sender_id = $sender_id;
                $ins->reference = $data->reference;
                $ins->beneficiary_id = $data->beneficiaryId;
                $ins->account_holder_name = $data->beneficiaryName;
                $ins->account_number = $data->accountNumber;
                $ins->ifsc = $data->ifsc;
                $ins->save();
                
                return response()->json(['success' => true, 'message' => 'Add Beneficiary.']);
                
            }else{
                
                $message = $decode->message;

                if(strcmp($message, "Active beneficary account already exists!") === 0) {
                    $benf = CcBeneficiaries::where('account_number',$account)->first();
                    
                    $ins = new CcBeneficiaries();
                    $ins->user_id = $user->id;
                    $ins->sender_id = $sender_id;
                    $ins->reference = $benf->reference;
                    $ins->beneficiary_id = $benf->beneficiary_id;
                    $ins->account_holder_name = $benf->account_holder_name;
                    $ins->account_number = $benf->account_number;
                    $ins->ifsc = $benf->ifsc;
                    $ins->save();
                    
                    return response()->json(['success' => true, 'message' => 'Add Beneficiary.']);
                    
                }
                return response()->json(['success' => false, 'message' => $decode->message]);
            }
            
            
        }
    }
    
    public function bulkpeDoTransactions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "sender_id" => "required|exists:cc_senders,sender_id",
            "beneficiary_id" => "required|exists:cc_beneficiaries,beneficiary_id",
            "amount" => [
                            "required",
                            "numeric",
                            function($attribute, $value, $fail) {
                                // Check if the amount is greater than 100
                                if ($value < 500) {
                                    $fail($attribute . ' must be greater than 500.');
                                }
                            },
                        ],
            "latitude" => "required",
            "longitude" => "required",
            "transfer_type" => "required",
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


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);

        if($response)
        {
            
            $user = User::where('mobile',$request->user_mobile)->first();
        
            $shop = shop_details::where('user_id',$user->id)->first();
            if($shop->latitude == null || $shop->longitude == null){
                return response()->json(['success' => false, 'message' => "Please Insert Shop Detail."]);
            }
            
            $distance = $this->UserAuth->geteDistance($shop->latitude, $shop->longitude, $request->latitude, $request->longitude);
            log::channel("location")->info("Retailer");
            log::channel("location")->info($request->latitude."/".$request->longitude);
            log::channel("location")->info("SHOP");
            log::channel("location")->info($shop->latitude."/".$shop->longitude);
            log::channel("location")->info("KM");
            log::channel("location")->info($distance);
            $distance_mnd = 25;
            if(isset($request->accuracy)){
                $distance_mnd = $distance_mnd + $request->accuracy;
                // if($request->accuracy >= 100){
                //     $distance_mnd = 250;
                // }
                // $distance_mnd = 350;
            }
            if($request->get('platform') == 'web'){
                $distance_mnd = 1000;
                if($distance > $distance_mnd){
                    if($user->id == 363){
                        
                    }else{
                        return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
                    }
                    
                }
            }else{
                if($distance > $distance_mnd){
                    if($user->id == 363){
                        
                    }else{
                        return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
                    }
                    
                }
                
            }
            
            $transfer_type = $request->transfer_type;
            $sender_id = $request->sender_id;
            $beneficiary_id = $request->beneficiary_id;
            $amount = $request->amount;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $user = User::where('mobile',$request->user_mobile)->first();
            
            $txnid = $this->UserAuth->txnId('CCP');
            
            $recentTime = Carbon::now()->subMinutes(10);
            $userwallet_check = User::find($user->id);
            $balance_compaer = $userwallet_check->wallet->balanceFloat;
            
            $sender = CcSenders::where('sender_id',$sender_id)->first();
            $beneficiary = CcBeneficiaries::where('beneficiary_id',$beneficiary_id)->where('status',1)->first();
            $card_type = $sender->card_type;
            
            if($transfer_type == 1){
                $fee = $amount * 2.10/100;
                
            }else{
                $fee = $amount * 1.90/100;
            }
            $fee_api = $amount * 0.6/100;
            $gst = $fee - ($fee / 1.18);
            // if($balance_compaer < $fee){
            //     return response()->json(['success' => false, 'message' => 'Insufficient balance.']);
            // }
            
            // $txn_wl = $this->WalletCalculation->walletWithdrawFloat($user->id,$fee,'CC PAYOUT','Debit_Retailer_'.$user->mobile.'_CC_Payout_'.$transfer_type.'_Amount_'.$beneficiary->account_holder_name,$txnid);
            $txn_wl = '0';
            
            $ins = new transactions_cc();
            $ins->user_id = $user->id;
            $ins->event = 'CCPAYOUT';
            $ins->transaction_id = $txnid;
            $ins->amount = $amount;
            $ins->sender_id = $sender_id;
            $ins->beneficiary_id = $beneficiary_id;
            $ins->transfer_type = $transfer_type;
            $ins->fee = $fee;
            $ins->tds = 0;
            $ins->gst = $gst;
            $ins->ben_name = '';
            $ins->ben_ac_number = '';
            $ins->ifsc = '';
            $ins->utr = 0;
            $ins->status = 0;
            $ins->wallets_uuid = $txn_wl;
            $ins->latitude = $latitude;
            $ins->longitude = $longitude;
            $ins->save();
            
            $userData = [
                        'reference' => $txnid,
                        'beneficiaryId' => $beneficiary_id,
                        'senderId' => $sender_id,
                        'amount' => $amount,
                        'type' => $transfer_type,//1 for instant payout and 2 for T+1 (next day)
                        'redirectUrl' => 'https://user.payritepayment.in/retailer/dashboard',
                        'cardType' => $card_type,//visa or rupay or master,
                        'additionalCharge' => $fee_api
                    ];
            $json_data = json_encode($userData,JSON_UNESCAPED_SLASHES);
            $url = "https://api.bulkpe.in/client/cc/createCardCollectionUrl";
            $response = $this->ApiCalls->bulkpePostCall($url,$json_data);
            $decode = json_decode($response);
            
            if($decode->status){
                $ins_u = transactions_cc::find($ins->id);
                $ins_u->status = 5;
                $ins_u->save();
                
                $fee_blp = $decode->data->charge;
                $fee_gst = $decode->data->gst;
                $fee_add = $decode->data->additionalCharge;
                
                $gst_add = $fee_add - ($fee_add / 1.18);
                
                $total_fee = $fee_blp + $fee_gst + $fee_add;
                $total_gst = $fee_gst + $gst_add;
                
                $ins_u = transactions_cc::find($ins->id);
                $ins_u->fee = $total_fee;
                $ins_u->gst = $total_gst;
                $ins_u->save();
                
                
                return response()->json(['success' => true, 'message' => $decode->message,'data'=>$decode]);
            }else{
                $ins_u = transactions_cc::find($ins->id);
                $ins_u->status = 2;
                $ins_u->save();
                
                return response()->json(['success' => false, 'message' => $decode->message]);
            }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function callbackBulkpeCC(Request $request) {
        $payload = $request->all();
        Log::channel('bulkpeapi')->info("CALLBACK CC");
        Log::channel('bulkpeapi')->info($payload);
        $status = $payload['data']['status'];
        if($status == 'SUCCESS'){
            $update = transactions_cc::where('transaction_id',$payload['data']['reference_id'])->where('status',5)->first();
            $update->utr = $payload['data']['utr'];
            $update->status = 1;
            $update->save();
            
            $transaction_id = $payload['data']['reference_id'];
            $amount = $update->fee + $update->amount;
            $fee = $update->fee;
            $amount_act = $update->amount;
            $user_id = $update->user_id;
            $user = User::find($user_id);
            $beneficiary = CcBeneficiaries::where('beneficiary_id',$update->beneficiary_id)->first();
            
            $amount_dep = $this->WalletCalculation->walletDepositFloat($user_id,$amount,"CC PAYOUT",'Credit_Retailer_'.$user->mobile.'_CC_Payout_'.$beneficiary->account_holder_name,$transaction_id);
            
            $txn_fee = $this->WalletCalculation->walletWithdrawFloat($user_id,$fee,'CC PAYOUT Fee','Debit_Retailer_'.$user->mobile.'_CC_Payout_Fee_'.$beneficiary->account_holder_name,$transaction_id);
            
            $txn_act = $this->WalletCalculation->walletWithdrawFloat($user_id,$amount_act,'CC PAYOUT','Debit_Retailer_'.$user->mobile.'_CC_Payout_'.$beneficiary->account_holder_name,$transaction_id);
            
            $update->wallets_uuid = $txn_act;
            $update->save();
        }
        if($status == 'FAILED'){
            $update = transactions_cc::where('transaction_id',$payload['data']['reference_id'])->where('status',5)->first();
            $update->status = 2;
            $update->save();
        }
    }
    
    public function ccpayoutReport(Request $request){
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
            $data = transactions_cc::with('senders','beneficiaries')
            ->where('user_id',$user->id)
            ->whereBetween('created_at', array($from, $to))
            ->orderBy('id','DESC')->get();
            
            return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
}
