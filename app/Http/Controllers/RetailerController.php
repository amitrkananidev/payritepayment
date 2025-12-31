<?php

namespace App\Http\Controllers;
use App\Classes\UserAuth;
use App\Classes\ApiCalls;
use App\Http\Controllers\AirPayController;
use App\Classes\WalletCalculation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use DataTables;
// use Excel;
use Mail;
use Auth;
use DB;
use Log;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Simple\Manager;

use App\Models\User;
use App\Models\user_levels;
use App\Models\Addresses;
use App\Models\eko_services;
use App\Models\kyc_docs;
use App\Models\banks;
use App\Models\dmt_customers;
use App\Models\dmt_beneficiaries;
use App\Models\shop_details;
use App\Models\transactions_dmt;
use App\Models\transactions_aeps;
use App\Models\cities;
use App\Models\states;
use App\Models\fund_banks;
use App\Models\fund_requests;
use App\Models\fund_onlines;
use App\Models\dmt_upi_beneficiaries;
use App\Models\fund_ods;
use App\Models\fund_od_reverses;

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

use App\Models\CcSenders;
use App\Models\CcBeneficiaries;
use App\Models\transactions_cc;

class RetailerController extends Controller
{
    public function __construct(UserAuth $Auth, ApiCalls $ApiCalls, AirPayController $AirPayController, WalletCalculation $WalletCalculation) {
        $this->middleware('auth');
        
        $this->UserAuth = $Auth;
        $this->ApiCalls = $ApiCalls;
        $this->AirPayController = $AirPayController;
        $this->WalletCalculation = $WalletCalculation;
    }
    
    public function index()
    {
        $today = Carbon::today();
        $user_id = Auth::user()->id;
        // Get the start date of the current month
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
        // Define the start and end dates for the last month
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d H:i:s');
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d H:i:s');
        

        $data = array();
        //Money Transfer Vol
        $data['today_total_transactions_dmt'] = transactions_dmt::where('status',1)->where('transactions_dmts.event','DMT')->where('user_id', $user_id)->whereDate('created_at', $today)->get()->sum('amount');
        $data['mst_total_transactions_dmt'] = transactions_dmt::where('status',1)->where('transactions_dmts.event','DMT')->where('user_id', $user_id)->whereBetween('created_at', [$startOfMonth, $today])->get()->sum('amount');
        $data['last_month_total_transactions_dmt'] = transactions_dmt::where('status',1)->where('transactions_dmts.event','DMT')->where('user_id', $user_id)->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->get()->sum('amount');
        $data['last_month_vs_this_month'] = $this->UserAuth->calculatePercentageChange($data['last_month_total_transactions_dmt'],$data['mst_total_transactions_dmt'])."%";
        //Money Transfer Status
        $data['today_success_transactions_dmt'] = transactions_dmt::where('status',1)->where('transactions_dmts.event','DMT')->where('user_id', $user_id)->whereDate('created_at', $today)->get()->sum('amount');
        $data['today_pending_transactions_dmt'] = transactions_dmt::where('status',0)->where('transactions_dmts.event','DMT')->where('user_id', $user_id)->whereDate('created_at', $today)->get()->sum('amount');
        $data['today_failed_transactions_dmt'] = transactions_dmt::where('status',2)->where('transactions_dmts.event','DMT')->where('user_id', $user_id)->whereDate('created_at', $today)->get()->sum('amount');
        //UPI Vol
        $data['today_total_transactions_upi'] = transactions_dmt::where('status',1)->where('transactions_dmts.event','SCANNPAY')->where('user_id', $user_id)->whereDate('created_at', $today)->get()->sum('amount');
        $data['mst_total_transactions_upi'] = transactions_dmt::where('status',1)->where('transactions_dmts.event','SCANNPAY')->where('user_id', $user_id)->whereBetween('created_at', [$startOfMonth, $today])->get()->sum('amount');
        $data['last_month_total_transactions_upi'] = transactions_dmt::where('status',1)->where('transactions_dmts.event','SCANNPAY')->where('user_id', $user_id)->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->get()->sum('amount');
        $data['last_month_vs_this_month_upi'] = $this->UserAuth->calculatePercentageChange($data['last_month_total_transactions_upi'],$data['mst_total_transactions_upi'])."%";
        //UPI Status
        $data['today_success_transactions_upi'] = transactions_dmt::where('status',1)->where('transactions_dmts.event','SCANNPAY')->where('user_id', $user_id)->whereDate('created_at', $today)->get()->sum('amount');
        $data['today_pending_transactions_upi'] = transactions_dmt::where('status',0)->where('transactions_dmts.event','SCANNPAY')->where('user_id', $user_id)->whereDate('created_at', $today)->get()->sum('amount');
        $data['today_failed_transactions_upi'] = transactions_dmt::where('status',2)->where('transactions_dmts.event','SCANNPAY')->where('user_id', $user_id)->whereDate('created_at', $today)->get()->sum('amount');
        //AEPS Vol
        $data['today_total_transactions_aeps'] = transactions_aeps::where('status',1)->where('user_id', $user_id)->whereDate('created_at', $today)->get()->sum('amount');
        $data['mst_total_transactions_aeps'] = transactions_aeps::where('status',1)->where('user_id', $user_id)->whereBetween('created_at', [$startOfMonth, $today])->get()->sum('amount');
        $data['last_month_total_transactions_aeps'] = transactions_aeps::where('status',1)->where('user_id', $user_id)->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->get()->sum('amount');
        $data['last_month_vs_this_month_aeps'] = $this->UserAuth->calculatePercentageChange($data['last_month_total_transactions_aeps'],$data['mst_total_transactions_aeps'])."%";
        //AEPS Status
        $data['today_success_transactions_aeps'] = transactions_aeps::where('status',1)->where('user_id', $user_id)->whereDate('created_at', $today)->get()->sum('amount');
        $data['today_BE_transactions_aeps'] = transactions_aeps::where('status',1)->where('transactions_aeps.transfer_type','balance_enquiry')->where('user_id', $user_id)->whereDate('created_at', $today)->get()->count();
        $data['today_MS_transactions_aeps'] = transactions_aeps::where('status',1)->where('transactions_aeps.transfer_type','mini_statement')->where('user_id', $user_id)->whereDate('created_at', $today)->get()->count();
        $data['today_failed_transactions_aeps'] = transactions_aeps::where('status',2)->where('user_id', $user_id)->whereDate('created_at', $today)->get()->sum('amount');
        
        $data['total_transactions_aeps'] = transactions_aeps::where('status',1)->whereBetween('created_at', [$startOfMonth, $today])->get()->sum('amount');
        
        // DMT Transactions
        // Get the start and end dates of last week
        $startOfWeek = Carbon::now()->startOfWeek()->subWeek();
        $endOfWeek = Carbon::now()->startOfWeek()->subWeek()->endOfWeek();
        
        // Create an array to store the results
        $lastWeekTransactions = [];
        $lastWeekday = [];
        
        // Loop through each day of the last week
        $currentDate = $startOfWeek->copy();
        while ($currentDate <= $endOfWeek) {
            // Get the transactions for the current day and calculate the sum of amounts
            $transactions = transactions_dmt::where('user_id',Auth::user()->id)->where('transactions_dmts.event','DMT')->where('transactions_dmts.status','1')->whereDate('transactions_dmts.created_at', $currentDate->toDateString())->get();
            $totalAmount = $transactions->sum('amount');
        
            // Store the total amount for the current day along with the day name in the result array
            $lastWeekTransactions[] = $totalAmount;
            $lastWeekday[] = $currentDate->format('l');
        
            // Move to the next day
            $currentDate->addDay();
        }
        
        // Fill in missing days with 0
        while ($currentDate->isBefore(Carbon::now()->startOfWeek())) {
            $lastWeekTransactions[] = 0;
            $currentDate->addDay();
        }
        
        // Get the start and end dates of the current week (Monday to Sunday)
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        // Create an array to store the results
        $currentWeekTransactions = [];
        $thisweek_dmt_total = 0;
        // Loop through each day of the current week
        $currentDate = $startOfWeek->copy();
        while ($currentDate <= $endOfWeek) {
            // Get the transactions for the current day and calculate the sum of amounts
            $transactions = transactions_dmt::where('user_id',Auth::user()->id)->where('transactions_dmts.status','1')->where('transactions_dmts.event','DMT')->whereDate('transactions_dmts.created_at', $currentDate->toDateString())->get();
            $totalAmount = $transactions->sum('amount');
        
            // Store the total amount for the current day along with the day name in the result array
            $currentWeekTransactions[] = $totalAmount;
            $thisweek_dmt_total = $thisweek_dmt_total + $totalAmount;
            // Move to the next day
            $currentDate->addDay();
        }
        
        // Fill in missing days with 0
        while ($currentDate->isBefore(Carbon::now()->startOfWeek()->addWeek())) {
            $currentWeekTransactions[] = 0;
            $currentDate->addDay();
        }
        
        // AEPS Transactions
        // Get the start and end dates of last week
        $startOfWeek = Carbon::now()->startOfWeek()->subWeek();
        $endOfWeek = Carbon::now()->startOfWeek()->subWeek()->endOfWeek();
        // Create an array to store the results
        $lastWeekTransactions_aeps = [];
        $lastWeekday_aeps = [];
        
        // Loop through each day of the last week
        $currentDate = $startOfWeek->copy();
        while ($currentDate <= $endOfWeek) {
            // Get the transactions for the current day and calculate the sum of amounts
            $transactions_aeps = transactions_aeps::where('user_id',Auth::user()->id)->where('transactions_aeps.transfer_type','cash_withdrawal')->where('transactions_aeps.status','1')->whereDate('transactions_aeps.created_at', $currentDate->toDateString())->get();
            $totalAmount_aeps = $transactions_aeps->sum('amount');
        
            // Store the total amount for the current day along with the day name in the result array
            $lastWeekTransactions_aeps[] = $totalAmount_aeps;
            $lastWeekday_aeps[] = $currentDate->format('l');
        
            // Move to the next day
            $currentDate->addDay();
        }
        
        // Fill in missing days with 0
        while ($currentDate->isBefore(Carbon::now()->startOfWeek())) {
            $lastWeekTransactions_aeps[] = 0;
            $currentDate->addDay();
        }
        
        // Get the start and end dates of the current week (Monday to Sunday)
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        // Create an array to store the results
        $currentWeekTransactions_aeps = [];
        $thisweek_aeps_total = 0;
        // Loop through each day of the current week
        $currentDate = $startOfWeek->copy();
        while ($currentDate <= $endOfWeek) {
            // Get the transactions for the current day and calculate the sum of amounts
            $transactions_aeps = transactions_aeps::where('user_id',Auth::user()->id)->where('transactions_aeps.status','1')->where('transactions_aeps.transfer_type','cash_withdrawal')->whereDate('transactions_aeps.created_at', $currentDate->toDateString())->get();
            $totalAmount_aeps = $transactions_aeps->sum('amount');
        
            // Store the total amount for the current day along with the day name in the result array
            $currentWeekTransactions_aeps[] = $totalAmount_aeps;
            $thisweek_aeps_total = $thisweek_aeps_total + $totalAmount_aeps;
            // Move to the next day
            $currentDate->addDay();
        }
        
        // Fill in missing days with 0
        while ($currentDate->isBefore(Carbon::now()->startOfWeek()->addWeek())) {
            $currentWeekTransactions_aeps[] = 0;
            $currentDate->addDay();
        }
        
        return view('new_pages.retailer.home',compact('data','lastWeekTransactions','lastWeekday','currentWeekTransactions','thisweek_dmt_total',
                                                        'lastWeekTransactions_aeps','lastWeekday_aeps','currentWeekTransactions_aeps','thisweek_aeps_total'));
    }
    
    public function createFundRequest()
    {
        
        
        $fund_banks = fund_banks::where('user_id',1)->get();
        return view('new_pages.retailer.fundrequest.create',compact('fund_banks'));
    }
    
    public function postCreateFundRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "amount" => "required",
            "payment_mode" => "required",
            "deposit_date" => "required",
            "deposit_time" => "required",
        ]);
        
        $validator->sometimes('bank_ref', 'required', function($input) {
            return $input->payment_mode === 'Bank';
        });
        
        $validator->sometimes('bank_id', 'exists:fund_banks,id', function($input) {
            return $input->payment_mode === 'Bank';
        });
        
        // Optionally, you can add specific rules for the 'image' field if it needs to be a file type
        $validator->sometimes('img', 'image|mimes:jpeg,png,jpg|max:2048', function($input) {
            return $input->payment_mode === 'Bank';
        });
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back()->withInput();
        }
        
        $user_id = Auth::user()->id;
        $amount = $request->get("amount");
        $transfer_type = $request->get("payment_mode");
        $bank_id = $request->get("bank_id");
        $bank_ref = $request->get("bank_ref");
        $deposit_date = $request->get("deposit_date")." ".$request->get("deposit_time");
        $remark = $request->get("remark");
        
        if($transfer_type != 'Cash'){
                    
            $check_entry = fund_requests::where('bank_id',$bank_id)->where('bank_ref',$bank_ref)->whereIn('status',[1,0])->first();
            if($check_entry) {
                
                Session::flash('error', 'You can not request with same bank and refrence id!');
                return redirect()->back()->withInput();
            }
        }
        
        $txn = $this->UserAuth->txnId("MR");
        $imagename = '';
        
        if ($request->hasFile('img')) {
            $file = $request->file('img');
            $destinationPath = public_path('/uploads/money_request/');
            $imagename = 'FNR'. $user_id . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $imagename);
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
        
        Session::flash('success', 'Money request sent successfully');
        return redirect()->back();
    }
    
    public function getAeps()
    {
        $user_id = Auth::user()->id;
        $check = eko_services::where('user_id',$user_id)->first();
        if(!$check){
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $url = 'https://user.payritepayment.in/api/v1/dmt/ekoOnboard?token='.$token.'&user_mobile='.Auth::user()->mobile;
            
            $response = $this->ApiCalls->payritePostCall($url);
            log::info($response);
            $decode = json_decode($response);
            if(!isset($decode->success)){
                Session::flash('error', 'Something Wrong!');
                return redirect()->back();
            }
            if($decode->success){
                return view('new_pages.retailer.services.aeps.next',compact('response'));
            }else{
                Session::flash('error', $decode->message);
                return redirect()->back();
            }
        }
        
        if($check->eko_status == 0){
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $url = 'https://user.payritepayment.in/api/v1/dmt/ekoOnboard?token='.$token.'&user_mobile='.Auth::user()->mobile;
            
            $response = $this->ApiCalls->payritePostCall($url);
            log::info($response);
            $decode = json_decode($response);
            if(!isset($decode->success)){
                Session::flash('error', 'Something Wrong!');
                return redirect()->back();
            }
            if($decode->success){
                return view('new_pages.retailer.services.aeps.next');
            }else{
                Session::flash('error', $decode->message);
                return redirect()->back();
            }
        }elseif($check->eko_status == 4){
            return view('new_pages.retailer.services.aeps.next');
        }elseif($check->eko_status == 1 && $check->eko_aeps == 2){
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $url = 'https://user.payritepayment.in/api/v1/dmt/aepsUserServiceEnquiry?token='.$token.'&user_mobile='.Auth::user()->mobile;
            $response = $this->ApiCalls->payritePostCall($url);
            
            $decode = json_decode($response);
            if(!isset($decode->success)){
                Session::flash('error', 'Something Wrong!');
                return redirect()->back();
            }
            if($decode->success){
                Session::flash('success', $decode->message);
                return redirect()->route('dashboard_retailer');
            }else{
                Session::flash('error', $decode->message);
                return redirect()->route('dashboard_retailer');
            }
        }elseif($check->eko_status == 1 && $check->eko_aeps == 1){
            Session::flash('success', 'User Active');
            return redirect()->route('dashboard_retailer');
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $url = 'https://user.payritepayment.in/api/v1/dmt/aepsUserServiceEnquiry?token='.$token.'&user_mobile='.Auth::user()->mobile;
            $response = $this->ApiCalls->payritePostCall($url);
            
            $decode = json_decode($response);
            if(!isset($decode->success)){
                Session::flash('error', 'Something Wrong!');
                return redirect()->back();
            }
            if($decode->success){
                
            }else{
                Session::flash('error', $decode->message);
                return redirect()->route('dashboard_retailer');
            }
            
            
            $post_data = array(
                'token'=>Auth::user()->user_token,
                'user_mobile'=>Auth::user()->mobile,
                );
            $curl = curl_init();
            curl_setopt_array($curl, array(
    
              CURLOPT_URL => "https://user.payritepayment.in/api/v1/aeps/aeps_keys_data",
    
              CURLOPT_RETURNTRANSFER => true,
    
              CURLOPT_ENCODING => '',
    
              CURLOPT_MAXREDIRS => 10,
    
              CURLOPT_TIMEOUT => 0,
    
              CURLOPT_FOLLOWLOCATION => true,
    
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    
              CURLOPT_CUSTOMREQUEST => 'POST',
    
              CURLOPT_POSTFIELDS => $post_data,
    
            ));
    
            $response = curl_exec($curl);
    
            curl_close($curl);
    
            $data = json_decode($response);
            return view('new_pages.retailer.services.aeps',compact('data'));
        }else{
            Session::flash('error', 'Something Wrong, Please Connect With Admin');
            return redirect()->back();
        }
    }
    
    public function AepsOtp(Request $request)
    {
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $devicenumber = $request->number;
        $devicename = $request->name;
        $url = "https://user.payritepayment.in/api/v1/dmt/aepsActivateService?token=$token&devicenumber=$devicenumber&devicename=$devicename&user_mobile=".Auth::user()->mobile;
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            // print_r($decode);exit;
            Session::flash('success', $decode->message);
            return redirect()->route('dashboard_retailer');
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
        
    }
    
    public function getpayout(Request $request)
    {
        $banks = banks::get();
        $customer_mobile = 9876543210;
        $customer_name = "Demo";
        $data = "";
        return view('new_pages.retailer.services.payout.payout',compact('data','customer_name','customer_mobile','banks'));
    }
    
    public function dmtLogin(Request $request)
    {
        // Session::flash('error', 'Service down');
            // return redirect()->route('dashboard_retailer');
        
        return view('new_pages.retailer.services.dmt.login');
    }
    
    public function pincodeLocation(Request $request)
    {
        $pincode = $request->pincode;
        $apiKey = 'AIzaSyBNdPZHybJOOp0q3FUOg3Hp7U6t6nbiGIA'; // Replace with your actual API key
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$pincode&key=$apiKey";
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
    
        $data = json_decode($response, true);
        log::info($data);
        if ($data['status'] === 'OK') {
            $addressComponents = $data['results'][0]['address_components'];
            $city = '';
            $state = '';
    
            foreach ($addressComponents as $component) {
                if (in_array('administrative_area_level_3', $component['types'])) {
                    $city = $component['long_name'];
                }
                if (in_array('administrative_area_level_1', $component['types'])) {
                    $state = $component['long_name'];
                }
                
            }
    
            if ($city === '' && $state === '') {
                return response()->json(["success"=>false,"message"=>"No detailed area information found.","city"=>"","state"=>""]);
                
            } else {
                
                return response()->json(["success"=>true,"message"=>"ok","city"=>$city,"state"=>$state]);
            }
        } else {
            
            return response()->json(["success"=>false,"message"=>$data['status'],"city"=>"","state"=>""]);
        }
    }
    
    public function postDmtLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required|exists:dmt_customers,mobile",
            "latitude" => "required",
            "longitude" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    if($field == 'latitude' || $field == 'longitude'){
                        $redirect = 0;
                    }else{
                        $redirect = 1;
                    }
                    $param_message .= "$field : $message";
                }
            }
            if($redirect == 0){
                Session::flash('error', $param_message);
                return redirect()->back();
            }else{
                Session::flash('error', $param_message);
                $customer_mobile = $request->get('mobile');
                return view('new_pages.retailer.services.dmt.registration',compact('customer_mobile'));
            }
        }
        $customer_mobile = $request->get('mobile');
        $check_customer = dmt_customers::where('mobile',$customer_mobile)->where('status','1')->first();
        if(!$check_customer){
            Session::flash('error', "Mobile Not Registred.");
            return view('new_pages.retailer.services.dmt.registration',compact('customer_mobile'));
        }
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        
        $url = "https://user.payritepayment.in/api/v1/dmt/get-beneficiaries?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile;
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            // print_r($decode);exit;
            $data = $decode->data;
            $banks = banks::get();
            $dmt_limit = env('DMT_TXN_LIMIT');
            $dmt_use = $this->UserAuth->getCustomerUse($customer_mobile);
            $available = $dmt_limit - $dmt_use;
            $customer_name = $check_customer->first_name." ".$check_customer->last_name;
            Session::flash('success', 'Successfuly Login.');
            return view('new_pages.retailer.services.dmt.beneficiaries',compact('data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return view('new_pages.retailer.services.dmt.registration',compact('customer_mobile'));
        }
        
        
    }
    
    public function postDmtTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required|exists:dmt_customers,mobile",
            "beneficiaries" => "required|exists:dmt_beneficiaries,id",
            "transafer_type" => "required",
            "amount" => "required",
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
            
            Session::flash('error', $param_message);
            return redirect()->route('dashboard_retailer');
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $customer_mobile = $request->get('mobile');
        $beneficiaries = $request->get('beneficiaries');
        $transafer_type = $request->get('transafer_type');
        $amount = $request->get('amount');
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $url = "https://user.payritepayment.in/api/v1/dmt/do-transactions?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&beneficiary_id=$beneficiaries&amount=$amount&transfer_type=$transafer_type&latitude=$latitude&longitude=$longitude";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            // print_r($decode);exit;
            $data = $decode->data;
            Session::flash('dmt_success', $decode->message);
            Session::flash('dmt_transaction_id', $decode->data->transaction_id);
            return redirect()->route('dashboard_retailer');
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
        
        
    }
    
    public function postDmtBenfDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required|exists:dmt_customers,mobile",
            "beneficiaries" => "required|exists:dmt_beneficiaries,id",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            
            return response()->json(["success"=>false,"message"=>$param_message,"data"=>""]);
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $customer_mobile = $request->get('mobile');
        $beneficiaries = $request->get('beneficiaries');
        $url = "https://user.payritepayment.in/api/v1/dmt/delete-beneficiary?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&beneficiary_id=$beneficiaries";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            // print_r($decode);exit;
            
            
            $msg = $decode->message;
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            $url = "https://user.payritepayment.in/api/v1/dmt/get-beneficiaries?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile;
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            $data = $decode->data;
            $html = "<option value=''>Select Beneficiaries</option>";
            foreach($data as $r){
                $html .= "<option value='$r->id'>$r->account_holder_name | $r->account_number | $r->ifsc | $r->mobile</option>"; 
            }
            
            return response()->json(["success"=>true,"message"=>$msg,"data"=>$html]);
            
            
        }else{
            // print_r($decode);exit;
            $msg = $decode->message;
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            $url = "https://user.payritepayment.in/api/v1/dmt/get-beneficiaries?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile;
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            $data = $decode->data;
            $html = "<option value=''>Select Beneficiaries</option>";
            foreach($data as $r){
                $html .= "<option value='$r->id'>$r->account_holder_name | $r->account_number | $r->ifsc | $r->mobile</option>"; 
            }
            return response()->json(["success"=>false,"message"=>$msg,"data"=>$html]);
        }
        
        
    }
    
    public function postDmtBenfVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
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
            
            
            return response()->json(["success"=>false,"message"=>$param_message,"data"=>""]);
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $account = $request->get('account');
        $ifsc = $request->get('ifsc');
        $url = "https://user.payritepayment.in/api/v1/dmt/verify-beneficiary?token=$token&user_mobile=".Auth::user()->mobile."&account=$account&ifsc=$ifsc";
        $response = $this->ApiCalls->payritePostCall($url);
        
        return $response;
        
        
    }
    
    public function postDmtBenfAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required|exists:dmt_customers,mobile",
            "benf_name" => "required",
            "number" => "required",
            "ifsc" => "required",
            "banks" => "required|exists:banks,id",
            "is_verify" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            $url = "https://user.payritepayment.in/api/v1/dmt/get-beneficiaries?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile;
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            $data = $decode->data;
            $banks = banks::get();
            
            $dmt_limit = env('DMT_TXN_LIMIT');
            $check_customer = dmt_customers::where('mobile',$customer_mobile)->where('status','1')->first();
            $dmt_use = $this->UserAuth->getCustomerUse($customer_mobile);
            $available = $dmt_limit - $dmt_use;
            $customer_name = $check_customer->first_name." ".$check_customer->last_name;
            
            Session::flash('error', $param_message);
            return view('new_pages.retailer.services.dmt.beneficiaries',compact('data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $customer_mobile = $request->get('mobile');
        $benf_name = urlencode($request->get('benf_name'));
        $number = $request->get('number');
        $ifsc = $request->get('ifsc');
        $is_verify = $request->get('is_verify');
        
        $bank = banks::find($request->get('banks'));
        $bank_name = urlencode($bank->name);
        
        $url = "https://user.payritepayment.in/api/v1/dmt/add-beneficiary?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&name=$benf_name&account=$number&bank_name=$bank_name&ifsc=$ifsc&is_verify=$is_verify";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        
        if($decode->success){
            // print_r($decode);exit;
            
            
            $msg = $decode->message;
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            $url = "https://user.payritepayment.in/api/v1/dmt/get-beneficiaries?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile;
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            
            
            $data = $decode->data;
            $banks = banks::get();
            
            $dmt_limit = env('DMT_TXN_LIMIT');
            $check_customer = dmt_customers::where('mobile',$customer_mobile)->where('status','1')->first();
            $dmt_use = $this->UserAuth->getCustomerUse($customer_mobile);
            $available = $dmt_limit - $dmt_use;
            $customer_name = $check_customer->first_name." ".$check_customer->last_name;
            
            Session::flash('success', $msg);
            // return view('new_pages.retailer.services.dmt.beneficiaries',compact('data','customer_mobile','banks'));
            return view('new_pages.retailer.services.dmt.beneficiaries',compact('data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
            
            
        }else{
            // print_r($decode);exit;
            $msg = $decode->message;
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            $url = "https://user.payritepayment.in/api/v1/dmt/get-beneficiaries?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile;
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            
            $data = $decode->data;
            $banks = banks::get();
            
            $dmt_limit = env('DMT_TXN_LIMIT');
            $check_customer = dmt_customers::where('mobile',$customer_mobile)->where('status','1')->first();
            $dmt_use = $this->UserAuth->getCustomerUse($customer_mobile);
            $available = $dmt_limit - $dmt_use;
            $customer_name = $check_customer->first_name." ".$check_customer->last_name;
            
            Session::flash('error', $msg);
            // return view('new_pages.retailer.services.dmt.beneficiaries',compact('data','customer_mobile','banks'));
            
            return view('new_pages.retailer.services.dmt.beneficiaries',compact('data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
        }
        
        
    }
    
    public function dmtReceipt($id,Request $request)
    {
        $user_id = Auth::user()->id;
        $data_check = transactions_dmt::where('transactions_dmts.transaction_id',$id)->first();
        $txnid = explode("#", $data_check->multi_transaction_id);            
        $query = transactions_dmt::select('transactions_dmts.transaction_id',
                    DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"),
                    'users.mobile as retailer_mobile',
                    'shop_details.shop_name'
                )
                ->join('users', 'users.id', 'transactions_dmts.user_id')
                ->join('shop_details', 'shop_details.user_id', 'transactions_dmts.user_id')
                ->where('transactions_dmts.user_id', $user_id)
                ->where('transactions_dmts.event', 'DMT')
                ->where('transactions_dmts.transaction_id',$id);
        
        // Add joins only if transactions_dmts.api_id is not 2
        if ($data_check->api_id == 0) {
            $query->addSelect(
                DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),
                'dmt_customers.mobile as customer_mobile',
                'dmt_beneficiaries.bank_name as bank_name','amount','fee','transactions_dmts.status','transactions_dmts.created_at'
            )
            ->join('dmt_customers', 'dmt_customers.mobile', 'transactions_dmts.mobile')
            ->leftJoin('dmt_beneficiaries', 'dmt_beneficiaries.id', 'transactions_dmts.dmt_beneficiary_id')
            ->where('dmt_customers.status', 1);
            $data = $query->first();
        }else{
            $query->addSelect(
                'transactions_dmts.sender_name as customer_name',
                'transactions_dmts.mobile as customer_mobile',
                'transactions_dmts.bank_name','amount','fee','transactions_dmts.status','transactions_dmts.created_at'
            )->where('multi_transaction_id','LIKE','%'.$txnid[0].'%');
            $data = $query->first();
            
            // $data = transactions_dmt::select(DB::raw('(SELECT transaction_id FROM transactions_dmts WHERE multi_transaction_id LIKE "%'.$txnid[0].'%" ORDER BY transaction_id ASC LIMIT 1) as transaction_id'),
            //             'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name','transactions_dmts.created_at')
            //             ->addSelect(DB::raw('SUM(CASE WHEN status IN (0, 1) THEN amount ELSE 0 END) as amount'))
            //             ->addSelect(DB::raw('SUM(CASE WHEN status IN (0, 1) THEN fee ELSE 0 END) as fee'))
            //             ->addSelect(DB::raw('CASE 
            //                 WHEN SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) > 0 
            //                     THEN 0
            //                 WHEN SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) > 0 
            //                     THEN 1
            //                 ELSE 2 
            //                 END as status'))
            //             ->where('transactions_dmts.user_id', $user_id)
            //             ->where('multi_transaction_id','LIKE','%'.$txnid[0].'%')
            //             ->groupBy('transactions_dmts.sender_name', 'transactions_dmts.mobile', 'transactions_dmts.bank_name')
            //             ->first();
            
            $data_mult = transactions_dmt::select(DB::raw('SUM(amount) as amount'),DB::raw('SUM(fee) as fee'))
                        ->where('multi_transaction_id','LIKE','%'.$txnid[0].'%')
                        ->whereIn('status',[0,1])
                        ->first();
            $utr_values = transactions_dmt::where('multi_transaction_id','LIKE','%'.$txnid[0].'%')
                          ->whereIn('status',[0,1])
                          ->pluck('utr')
                          ->implode(',');
            $data->amount = $data_mult->amount;
            $data->fee = $data_mult->fee;
            $data->created_at = $data_check->created_at;
            $data->utr = $utr_values;
        }
        
        
        if(!$data){
            Session::flash('error', "Unauthorized access");
            return redirect()->route('dashboard_retailer');
        }
    
        $retailer = User::with('shopDetail')->where('id',$user_id)->first();
        return view('new_pages.retailer.receipt.dmt_new',compact('data','retailer','data_check'));
    }
    
    public function dmtReceiptUat($id,Request $request)
    {
        $user_id = 32;
        $data_check = transactions_dmt::where('transactions_dmts.transaction_id',$id)->first();
        $txnid = explode("#", $data_check->multi_transaction_id);            
        $query = transactions_dmt::select('transactions_dmts.transaction_id',
                    DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"),
                    'users.mobile as retailer_mobile',
                    'shop_details.shop_name'
                )
                ->join('users', 'users.id', 'transactions_dmts.user_id')
                ->join('shop_details', 'shop_details.user_id', 'transactions_dmts.user_id')
                ->where('transactions_dmts.user_id', $user_id)
                ->where('transactions_dmts.event', 'DMT')
                ->where('transactions_dmts.transaction_id',$id);
        
        // Add joins only if transactions_dmts.api_id is not 2
        if ($data_check->api_id != 1) {
            $query->addSelect(
                DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),
                'dmt_customers.mobile as customer_mobile',
                'dmt_beneficiaries.bank_name as bank_name','amount','fee','transactions_dmts.status'
            )
            ->join('dmt_customers', 'dmt_customers.mobile', 'transactions_dmts.mobile')
            ->leftJoin('dmt_beneficiaries', 'dmt_beneficiaries.id', 'transactions_dmts.dmt_beneficiary_id')
            ->where('dmt_customers.status', 1)->where('transactions_dmts.api_id', 0);
            $data = $query->first();
        }else{
            $data = transactions_dmt::select(DB::raw('(SELECT transaction_id FROM transactions_dmts WHERE multi_transaction_id LIKE "%'.$txnid[0].'%" ORDER BY transaction_id ASC LIMIT 1) as transaction_id'),
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
                        ->where('transactions_dmts.user_id', $user_id)
                        ->where('multi_transaction_id','LIKE','%'.$txnid[0].'%')
                        ->groupBy('transactions_dmts.sender_name', 'transactions_dmts.mobile', 'transactions_dmts.bank_name')
                        ->first();
                        
                        $data->created_at = $data_check->created_at;
        }
        
        // print_r($data);
        // exit;
        if(!$data){
            Session::flash('error', "Unauthorized access");
            return redirect()->route('dashboard_retailer');
        }
    
        $retailer = User::with('shopDetail')->where('id',$user_id)->first();
        return view('new_pages.retailer.receipt.dmt_new',compact('data','retailer','data_check'));
        
    }
    
    public function dmtReport(Request $request)
    {
        return view('new_pages.retailer.report.dmt');
    }
    
    public function dmtReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $api_id = $request->api_id;
        $user_id = Auth::user()->id;
        // $data = transactions_dmt::select('transactions_dmts.*',
        //     DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"),'users.mobile as retailer_mobile','shop_details.shop_name',
        //     DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
        //     ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
        //     ->join('users','users.id','transactions_dmts.user_id')
        //     ->join('shop_details','shop_details.user_id','transactions_dmts.user_id')
        //     ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
        //     ->where('transactions_dmts.user_id',$user_id)
        //     ->where('transactions_dmts.event','DMT')
        //     ->where('dmt_customers.status',1)
        //     ->whereBetween('transactions_dmts.created_at', array($from, $to))
        //     ->orderBy('transactions_dmts.id','DESC')->get();
        
        $query = transactions_dmt::select(
                    'transactions_dmts.*',
                    DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"),
                    'users.mobile as retailer_mobile',
                    'shop_details.shop_name'
                )
                ->join('users', 'users.id', 'transactions_dmts.user_id')
                ->join('shop_details', 'shop_details.user_id', 'transactions_dmts.user_id')
                ->where('transactions_dmts.user_id', $user_id)
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
            ->join('dmt_beneficiaries', 'dmt_beneficiaries.id', 'transactions_dmts.dmt_beneficiary_id')
            ->where('dmt_customers.status', 1)->where('transactions_dmts.api_id', 0)->whereNull('dmt_customers.dmt_type');
        }else{
            $query->addSelect(
                'transactions_dmts.sender_name as customer_name',
                'transactions_dmts.mobile as customer_mobile',
                'transactions_dmts.bank_name as bank_name'
            );
            $query->where('transactions_dmts.api_id', $api_id);
        }
        
        $data = $query->orderBy('transactions_dmts.id', 'DESC')->get();

            
        return response()->json($data);
    }
    
    public function scanandpayReport(Request $request)
    {
        return view('new_pages.retailer.report.scanandpay');
    }
    
    public function scanandpayReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $user_id = Auth::user()->id;
        $data = transactions_dmt::select('transactions_dmts.*',
            DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
            ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
            ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
            ->where('transactions_dmts.event','SCANNPAY')
            ->where('transactions_dmts.user_id',$user_id)
            ->where('dmt_customers.status','1')
            ->where('dmt_customers.user_id',$user_id)
            ->whereBetween('transactions_dmts.created_at', array($from, $to))
            ->orderBy('transactions_dmts.id','DESC')->get();
            
        return response()->json($data);
    }
    
    public function onlineFund(Request $request)
    {
        return view('new_pages.retailer.fundrequest.online_load');
    }
    
    public function onlineFundData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        $data = fund_onlines::where('user_id',$user_id)->where('status',1)
            ->whereBetween('created_at', array($from, $to))
            ->whereIn('pg_id',[1,2])
            ->orderBy('id','DESC')->get();
            
        return response()->json($data);
    }
    
    public function qrFund(Request $request)
    {
        return view('new_pages.retailer.fundrequest.qr_load');
    }
    
    public function qrFundData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        $data = fund_onlines::where('user_id',$user_id)->where('status',1)
            ->whereBetween('created_at', array($from, $to))
            ->whereIn('pg_id',[3])
            ->orderBy('id','DESC')->get();
            
        return response()->json($data);
    }
    
    public function fundRequest()
    {
        
        return view('new_pages.retailer.fundrequest.fund_request');
    }
    
    public function fundRequestData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $user_id = Auth::user()->id;
        $data = fund_requests::select('fund_requests.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"),'fund_banks.bank_name')
                ->join('users','users.id','fund_requests.user_id')
                ->leftjoin('fund_banks','fund_banks.id','fund_requests.bank_id')
                ->where('fund_requests.user_id',$user_id)
                ->whereBetween('fund_requests.created_at', array($from, $to))
                ->get();
        
        return response()->json($data);
    }
    
    public function dmtRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "name" => "required",
            "surname" => "required",
            "address" => "required",
            "pincode" => "required",
            "city" => "required",
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
            
            Session::flash('error', $param_message);
            $customer_mobile = $request->get('mobile');
            return view('new_pages.retailer.services.dmt.registration',compact('customer_mobile'));
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $customer_mobile = $request->get('mobile');
        $first_name = urlencode($request->get('name'));
        $surname = urlencode($request->get('surname'));
        $address = urlencode($request->get('address'));
        $pincode = $request->get('pincode');
        $city = $request->get('city');
        $state = $request->get('state');
        $string ="&mobile=$customer_mobile&first_name=$first_name&last_name=$surname&address=$pincode&city=$city&state=$state&pincode=$pincode";
        $url = "https://user.payritepayment.in/api/v1/dmt/create-customer?token=$token&user_mobile=".Auth::user()->mobile."$string";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            // print_r($decode);exit;
            
            Session::flash('success', 'OTP Sent on your mobile.');
            return view('new_pages.retailer.services.dmt.otp',compact('customer_mobile'));
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
        
        
    }
    
    public function dmtOtp(Request $request)
    {
       $validator = Validator::make($request->all(), [
            
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
            
            Session::flash('error', $param_message);
            return redirect()->route('dashboard_retailer');
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $customer_mobile = $request->get('mobile');
        $otp = $request->get('otp');
        $string ="&mobile=$customer_mobile&otp=$otp";
        $url = "https://user.payritepayment.in/api/v1/dmt/otp-customer-verify?token=$token&user_mobile=".Auth::user()->mobile."$string";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            // print_r($decode);exit;
            
            Session::flash('success', $decode->message);
            return redirect()->route('dmt_login_retailer');
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return redirect()->route('dmt_login_retailer');
        }
    }
    
    public function cyrusAeps(Request $request)
    {
        $user_id = Auth::user()->id;
        $check = eko_services::where('user_id',$user_id)->first();
        if(!$check){
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $url = 'https://user.payritepayment.in/api/v1/aeps/get-state-cyrus?token='.$token.'&user_mobile='.Auth::user()->mobile;
            
            $response = $this->ApiCalls->payritePostCall($url);
            
            $decode = json_decode($response);
            if(!isset($decode->success)){
                Session::flash('error', 'Something Wrong!');
                return redirect()->back();
            }
            if($decode->success){
                $states = $decode->data;
                return view('new_pages.retailer.services.aeps.cyrus_registration',compact('states'));
            }else{
                Session::flash('error', $decode->message);
                return redirect()->back();
            }
        }
        
        if($check->cyrus_aeps == 0){
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $url = 'https://user.payritepayment.in/api/v1/aeps/get-state-cyrus?token='.$token.'&user_mobile='.Auth::user()->mobile;
            
            $response = $this->ApiCalls->payritePostCall($url);
            
            $decode = json_decode($response);
            if(!isset($decode->success)){
                Session::flash('error', 'Something Wrong!');
                return redirect()->back();
            }
            if($decode->success){
                $states = $decode->data;
                return view('new_pages.retailer.services.aeps.cyrus_registration',compact('states'));
            }else{
                Session::flash('error', $decode->message);
                return redirect()->back();
            }
        }
        
        if($check->cyrus_aeps == 1 && $check->cyrus_aeps_daily == 0){
            echo 'Biomatric';
        }
        
    }
    
    public function cyrusAepsRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
            
            Session::flash('error', $param_message);
            return redirect()->route('dashboard_retailer');
        }
        $user_id = Auth::user()->id;
        $state = $request->state;
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $url = 'https://user.payritepayment.in/api/v1/aeps/registration-cyrus-aeps?token='.$token.'&user_mobile='.Auth::user()->mobile.'&state='.$state;
            
        $response = $this->ApiCalls->payritePostCall($url);
            
        $decode = json_decode($response);
        if(!isset($decode->success)){
            Session::flash('error', 'Something Wrong!');
            return redirect()->route('dashboard_retailer');
        }
        if($decode->success){
            Session::flash('success', $decode->message);
            return view('new_pages.retailer.services.aeps.cyrus_otp');
        }else{
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
    }
    
    public function cyrusAepsOtpVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
            
            Session::flash('error', $param_message);
            return redirect()->route('dashboard_retailer');
        }
        $user_id = Auth::user()->id;
        $state = $request->state;
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $url = 'https://user.payritepayment.in/api/v1/aeps/registration-cyrus-aeps?token='.$token.'&user_mobile='.Auth::user()->mobile.'&state='.$state;
            
        $response = $this->ApiCalls->payritePostCall($url);
            
        $decode = json_decode($response);
        if(!isset($decode->success)){
            Session::flash('error', 'Something Wrong!');
            return redirect()->route('dashboard_retailer');
        }
        if($decode->success){
            Session::flash('success', 'Registration Completed');
            return redirect()->route('aeps_2_retailer');
        }else{
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
    }
    
    public function upiTransferTest(Request $request)
    {
        $user_id = Auth::user()->id;
        $ben = User::with('beneficiariesUpi')->find($user_id);
        
        $results = $ben->beneficiariesUpi->map(function($user) {
            
            return [
                'name' => $user->account_holder_name,
                'upi' => $user->account_number
            ];
        });

        return response()->json($results);
    }
    
    public function upiTransfer(Request $request)
    {
        return view('new_pages.retailer.services.upi.upi');
    }
    
    public function postUpiTransfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "upi" => "required",
            "amount" => "required",
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
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        
        $user_id = Auth::user()->id;
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $name = urlencode($request->name);
        $upi = $request->upi;
        $amount = $request->amount;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $url = 'https://user.payritepayment.in/api/v1/upi/do-transactions?token='.$token.'&user_mobile='.Auth::user()->mobile.'&beneficiary_name='.$name.'&upi_id='.$upi.'&amount='.$amount.'&latitude='.$latitude.'&longitude='.$longitude;
            
        $response = $this->ApiCalls->payritePostCall($url);
            
        $decode = json_decode($response);
        
        if(!isset($decode->success)){
            Session::flash('error', 'Something Wrong!');
            return redirect()->route('dashboard_retailer');
        }
        if($decode->success){
            Session::flash('success', $decode->message);
            return redirect()->route('dashboard_retailer');
        }else{
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
    }
    
    public function myStatment()
    {
        
        return view('new_pages.retailer.report.my_statment');
    }
    
    public function myStatmenttData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $user_id = Auth::user()->id;
        $user = User::findOrFail($user_id);
        $data = Transaction::select('transactions.*',DB::raw('ROUND(transactions.balance / 100, 2) as balance'))
            ->where('transactions.payable_id',$user_id)
            ->where('transactions.payable_type', User::class)
            ->whereBetween('transactions.created_at', array($from, $to))
            ->orderBy('transactions.created_at', 'desc')
            ->get();
        
        return response()->json($data);
    }
    
    public function fundODReport()
    {
        
        return view('new_pages.retailer.report.fund_od');
    }
    
    public function fundODReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        $data = fund_ods::select('fund_ods.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"),'users.mobile as retailer_mobile','shop_details.shop_name','transactions.type')
                ->join('users','users.id','fund_ods.user_id')
                ->join('shop_details','shop_details.user_id','fund_ods.user_id')
                ->leftJoin('transactions', 'transactions.uuid', 'fund_ods.wallets_uuid')
                ->where('fund_ods.user_id',$user_id)
                ->where('fund_ods.is_od',1)
                ->whereBetween('fund_ods.created_at', array($from, $to))
                ->get();
        
        return response()->json($data);
    }
    
    //EKO DMT
    public function ekoDmtLogin(Request $request)
    {
        
        return view('new_pages.retailer.services.eko_dmt.login');
    }
    
    public function ekoPostDmtLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "latitude" => "required",
            "longitude" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    if($field == 'latitude' || $field == 'longitude'){
                        $redirect = 0;
                    }else{
                        $redirect = 1;
                    }
                    $param_message .= "$field : $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        $customer_mobile = $request->get('mobile');

        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        
        $url = "https://user.payritepayment.in/api/v1/eko-dmt/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile;
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        
        if($decode->success == true && $decode->is_reg == 1){
            $url = "https://user.payritepayment.in/api/v1/eko-dmt/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile";
            $response_benf = $this->ApiCalls->payritePostCall($url);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode);exit;
            if(isset($decode_benf->data->data->recipient_list)){
                $data = $decode_benf->data->data->recipient_list;
            }else{
                $data = [];
            }
            
            $banks = banks::get();
            $dmt_limit = $decode->limit;
            $dmt_use = $decode->used;
            $available = $decode->available;
            $customer_name = $decode->data->name;
            Session::flash('success', 'Successfuly Login.');
            return view('new_pages.retailer.services.eko_dmt.beneficiaries',compact('data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return view('new_pages.retailer.services.eko_dmt.registration',compact('customer_mobile'));
        }
        
        
    }
    
    public function ekoPostDmtTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "beneficiaries" => "required",
            "transafer_type" => "required",
            "amount" => "required",
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
            
            Session::flash('error', $param_message);
            return redirect()->route('dashboard_retailer');
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $customer_mobile = $request->get('mobile');
        $beneficiaries = $request->get('beneficiaries');
        $transafer_type = $request->get('transafer_type');
        $amount = $request->get('amount');
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $url = "https://user.payritepayment.in/api/v1/eko-dmt/do-transactions?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&beneficiary_id=$beneficiaries&amount=$amount&transfer_type=$transafer_type&latitude=$latitude&longitude=$longitude";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            // print_r($decode);exit;
            $data = $decode->data;
            Session::flash('dmt_success', $decode->message);
            Session::flash('dmt_transaction_id', $decode->data->transaction_id);
            return redirect()->route('dashboard_retailer');
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
        
        
    }
    
    public function ekoPostDmtBenfAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "benf_name" => "required",
            "number" => "required",
            "ifsc" => "required",
            "banks" => "required|exists:banks,id",
            "is_verify" => "required",
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
            Session::flash('error', $param_message);
            
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            $url = "https://user.payritepayment.in/api/v1/eko-dmt/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile;
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            $url = "https://user.payritepayment.in/api/v1/eko-dmt/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile";
            $response_benf = $this->ApiCalls->payritePostCall($url);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode);exit;
            if(isset($decode_benf->data->data->recipient_list)){
                $data = $decode_benf->data->data->recipient_list;
            }else{
                $data = [];
            }
            
            $banks = banks::get();
            $dmt_limit = $decode->limit;
            $dmt_use = $decode->used;
            $available = $decode->available;
            $customer_name = $decode->data->name;
            return view('new_pages.retailer.services.eko_dmt.beneficiaries',compact('data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $customer_mobile = $request->get('mobile');
        $benf_name = urlencode($request->get('benf_name'));
        $number = $request->get('number');
        $ifsc = $request->get('ifsc');
        $is_verify = $request->get('is_verify');
        $benf_mobile = urlencode($request->get('benf_mobile'));
        
        $bank = banks::find($request->get('banks'));
        $bank_id = $bank->eko_id;
        
        $url = "https://user.payritepayment.in/api/v1/eko-dmt/add-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&name=$benf_name&account=$number&bank_id=$bank_id&ifsc=$ifsc&benf_mobile=$benf_mobile&is_verify=$is_verify";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        
        if($decode->success){
            // print_r($decode);exit;
            
            
            $msg = $decode->message;
            Session::flash('success', $msg);
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            
            $url = "https://user.payritepayment.in/api/v1/eko-dmt/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile;
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            $url = "https://user.payritepayment.in/api/v1/eko-dmt/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile";
            $response_benf = $this->ApiCalls->payritePostCall($url);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode);exit;
            if(isset($decode_benf->data->data->recipient_list)){
                $data = $decode_benf->data->data->recipient_list;
            }else{
                $data = [];
            }
            
            $banks = banks::get();
            $dmt_limit = $decode->limit;
            $dmt_use = $decode->used;
            $available = $decode->available;
            $customer_name = $decode->data->name;
            return view('new_pages.retailer.services.eko_dmt.beneficiaries',compact('data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
            
        }else{
            // print_r($decode);exit;
            $msg = $decode->message;
            Session::flash('error', $msg);
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            $url = "https://user.payritepayment.in/api/v1/eko-dmt/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile;
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            $url = "https://user.payritepayment.in/api/v1/eko-dmt/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile";
            $response_benf = $this->ApiCalls->payritePostCall($url);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode);exit;
            if(isset($decode_benf->data->data->recipient_list)){
                $data = $decode_benf->data->data->recipient_list;
            }else{
                $data = [];
            }
            
            $banks = banks::get();
            $dmt_limit = $decode->limit;
            $dmt_use = $decode->used;
            $available = $decode->available;
            $customer_name = $decode->data->name;
            return view('new_pages.retailer.services.eko_dmt.beneficiaries',compact('data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
            
        }
        
        
    }
    
    //BILL DMT
    public function billDmtLogin(Request $request)
    {
        
        return view('new_pages.retailer.services.bill_dmt.login');
    }
    
    public function billPostDmtLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "bank_channel" => "required",
            "latitude" => "required",
            "longitude" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    if($field == 'latitude' || $field == 'longitude'){
                        $redirect = 0;
                    }else{
                        $redirect = 1;
                    }
                    $param_message .= "$field : $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        $customer_mobile = $request->get('mobile');
        $bank_channel = $request->get('bank_channel');

        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        
        $url = "https://user.payritepayment.in/api/v1/bill-dmt/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        
        if($decode->success == true && $decode->is_reg == 1){
            $url = "https://user.payritepayment.in/api/v1/bill-dmt/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&bank_channel=$bank_channel";
            $response_benf = $this->ApiCalls->payritePostCall($url);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode_benf);exit;
            if(isset($decode_benf->data->recipientList->dmtRecipient)){
                $data = $decode_benf->data->recipientList->dmtRecipient;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            // print_r(count($data));exit;
            $banks = banks::get();
            $dmt_limit = $decode->limit;
            $dmt_use = $decode->used;
            $available = $decode->available;
            $customer_name = $decode->data->senderName;
            Session::flash('success', 'Successfuly Login.');
            return view('new_pages.retailer.services.bill_dmt.beneficiaries',compact('bank_channel','data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            if($bank_channel == 'FINO'){
                return view('new_pages.retailer.services.bill_dmt.registration_fino',compact('customer_mobile','bank_channel'));
            }else{
                return view('new_pages.retailer.services.bill_dmt.registration',compact('customer_mobile','bank_channel'));
            }
            
        }
        
        
    }
    
    public function billPostDmtCreateCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "name" => "required",
            "pincode" => "required",
            "PidData" => "required",
            "aadhar" => "required",
            "bank_channel" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    if($field == 'latitude' || $field == 'longitude'){
                        $redirect = 0;
                    }else{
                        $redirect = 1;
                    }
                    $param_message .= "$field : $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        $customer_mobile = $request->get('mobile');
        $name = urlencode($request->get('name'));
        $pincode = $request->get('pincode');
        $PidData = $request->get('PidData');
        $aadhar = $request->get('aadhar');
        $bank_channel = $request->get('bank_channel');

        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        
        $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "customer_mobile"=>$customer_mobile,
                        "mobile"=>$customer_mobile,
                        "name"=>$name,
                        "pincode"=>$pincode,
                        "piddata"=>$PidData,
                        "piddata_type"=>'FIR',
                        "aadhar"=>$aadhar,
                        "bank_channel"=>$bank_channel);
        
        $url = "https://user.payritepayment.in/api/v1/bill-dmt/create-customer";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        
        if($decode->success == true && $decode->is_reg == 0){
            $additional_reg_data = $decode->data->additionalRegData;
            Session::flash('success', 'OTP Sent On your Number');
            return view('new_pages.retailer.services.bill_dmt.otp_fino',compact('additional_reg_data','customer_mobile','bank_channel'));
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return view('new_pages.retailer.services.bill_dmt.registration_fino',compact('customer_mobile','bank_channel'));
        }
        
        
    }
    
    public function billPostDmtCreateCustomerArtl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "name" => "required",
            "pincode" => "required",
            "bank_channel" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    if($field == 'latitude' || $field == 'longitude'){
                        $redirect = 0;
                    }else{
                        $redirect = 1;
                    }
                    $param_message .= "$field : $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        $customer_mobile = $request->get('mobile');
        $name = urlencode($request->get('name'));
        $pincode = $request->get('pincode');
        $bank_channel = $request->get('bank_channel');

        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        
        $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "customer_mobile"=>$customer_mobile,
                        "mobile"=>$customer_mobile,
                        "name"=>$name,
                        "pincode"=>$pincode,
                        "bank_channel"=>$bank_channel);
        
        $url = "https://user.payritepayment.in/api/v1/bill-dmt/create-customer";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        
        if($decode->success == true && $decode->is_reg == 0){
            $additional_reg_data = $decode->data->additionalRegData;
            Session::flash('success', 'OTP Sent On your Number');
            return view('new_pages.retailer.services.bill_dmt.otp',compact('additional_reg_data','customer_mobile','bank_channel'));
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return view('new_pages.retailer.services.bill_dmt.registration',compact('customer_mobile','bank_channel'));
        }
        
        
    }
    
    public function billPostDmtCreateCustomerVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "otp" => "required",
            "additional_reg_data" => "required",
            "bank_channel" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    if($field == 'latitude' || $field == 'longitude'){
                        $redirect = 0;
                    }else{
                        $redirect = 1;
                    }
                    $param_message .= "$field : $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        $customer_mobile = $request->get('mobile');
        $otp = $request->get('otp');
        $additional_reg_data = $request->get('additional_reg_data');
        $bank_channel = $request->get('bank_channel');
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "customer_mobile"=>$customer_mobile,
                        "mobile"=>$customer_mobile,
                        "additional_reg_data"=>$additional_reg_data,
                        "otp"=>$otp,
                        "bank_channel"=>$bank_channel);
        $url = "https://user.payritepayment.in/api/v1/bill-dmt/otp-customer-verify";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        
        if($decode->success == true && $decode->is_reg == 1){
            
            // Session::flash('success', 'OTP Verified');
            // return redirect()->route('dashboard_retailer');
            
            $url = "https://user.payritepayment.in/api/v1/bill-dmt/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            
            if($decode->success == true && $decode->is_reg == 1){
                $url = "https://user.payritepayment.in/api/v1/bill-dmt/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&bank_channel=$bank_channel";
                $response_benf = $this->ApiCalls->payritePostCall($url);
                $decode_benf = json_decode($response_benf);
                
                // print_r($decode_benf);exit;
                if(isset($decode_benf->data->recipientList->dmtRecipient)){
                    $data = $decode_benf->data->recipientList->dmtRecipient;
                    if(is_array($data)){
                        
                    }else{
                        $data = [$data];
                    }
                }else{
                    $data = [];
                }
                // print_r(count($data));exit;
                $banks = banks::get();
                $dmt_limit = $decode->limit;
                $dmt_use = $decode->used;
                $available = $decode->available;
                $customer_name = $decode->data->senderName;
                Session::flash('success', 'Successfuly Login.');
                return view('new_pages.retailer.services.bill_dmt.beneficiaries',compact('bank_channel','data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
            }else{
                // print_r($decode);exit;
                Session::flash('success', 'OTP Verified');
                return redirect()->route('dashboard_retailer');
            }
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
            // return view('new_pages.retailer.services.bill_dmt.otp',compact('additional_reg_data','customer_mobile'));
        }
        
        
    }
    
    public function billPostDmtCreateCustomerVerifyArtl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "otp" => "required",
            "additional_reg_data" => "required",
            "PidData" => "required",
            "aadhar" => "required",
            "bank_channel" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    if($field == 'latitude' || $field == 'longitude'){
                        $redirect = 0;
                    }else{
                        $redirect = 1;
                    }
                    $param_message .= "$field : $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        $customer_mobile = $request->get('mobile');
        $otp = $request->get('otp');
        $additional_reg_data = $request->get('additional_reg_data');
        $PidData = $request->get('PidData');
        $aadhar = $request->get('aadhar');
        $bank_channel = $request->get('bank_channel');
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "customer_mobile"=>$customer_mobile,
                        "mobile"=>$customer_mobile,
                        "additional_reg_data"=>$customer_mobile,
                        "piddata"=>$PidData,
                        "piddata_type"=>'FIR',
                        "aadhar"=>$aadhar,
                        "otp"=>$otp,
                        "bank_channel"=>$bank_channel);
        $url = "https://user.payritepayment.in/api/v1/bill-dmt/otp-customer-verify";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        
        if($decode->success == true && $decode->is_reg == 1){
            
            // Session::flash('success', 'OTP Verified');
            // return redirect()->route('dashboard_retailer');
            
            $url = "https://user.payritepayment.in/api/v1/bill-dmt/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            
            if($decode->success == true && $decode->is_reg == 1){
                $url = "https://user.payritepayment.in/api/v1/bill-dmt/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&bank_channel=$bank_channel";
                $response_benf = $this->ApiCalls->payritePostCall($url);
                $decode_benf = json_decode($response_benf);
                
                // print_r($decode_benf);exit;
                if(isset($decode_benf->data->recipientList->dmtRecipient)){
                    $data = $decode_benf->data->recipientList->dmtRecipient;
                    if(is_array($data)){
                        
                    }else{
                        $data = [$data];
                    }
                }else{
                    $data = [];
                }
                // print_r(count($data));exit;
                $banks = banks::get();
                $dmt_limit = $decode->limit;
                $dmt_use = $decode->used;
                $available = $decode->available;
                $customer_name = $decode->data->senderName;
                Session::flash('success', 'Successfuly Login.');
                return view('new_pages.retailer.services.bill_dmt.beneficiaries',compact('bank_channel','data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
            }else{
                // print_r($decode);exit;
                Session::flash('success', 'OTP Verified');
                return redirect()->route('dashboard_retailer');
            }
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
            // return view('new_pages.retailer.services.bill_dmt.otp',compact('additional_reg_data','customer_mobile'));
        }
        
        
    }
    
    public function billPostDmtTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "beneficiaries" => "required",
            "transafer_type" => "required",
            "amount" => "required",
            "latitude" => "required",
            "longitude" => "required",
            "accuracy" => "required",
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
            
            Session::flash('error', $param_message);
            return redirect()->route('dashboard_retailer');
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $bank_channel = $request->get('bank_channel');
        $customer_mobile = $request->get('mobile');
        $beneficiaries = $request->get('beneficiaries');
        $transafer_type = $request->get('transafer_type');
        $amount = $request->get('amount');
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $accuracy = $request->get('accuracy')/1000;
        $url = "https://user.payritepayment.in/api/v1/bill-dmt/do-transactions-web?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&beneficiary_id=$beneficiaries&amount=$amount&transfer_type=$transafer_type&latitude=$latitude&longitude=$longitude&accuracy=$accuracy&bank_channel=$bank_channel&platform=web";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            
            $url = "https://user.payritepayment.in/api/v1/bill-dmt/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
            $response = $this->ApiCalls->payritePostCall($url);
            $decode_sender = json_decode($response);
            
            $banks = banks::get();
            $dmt_limit = $decode_sender->limit;
            $dmt_use = $decode_sender->used;
            $available = $decode_sender->available;
            $customer_name = $decode_sender->data->senderName;
            $transaction_id = $decode->transaction_id;
            $transafer_type = $request->get('transafer_type');
            $transaction_ids = $decode->transaction_ids;
            
            
            $txns = transactions_dmt::whereIn('id', $transaction_ids)->where('status',0)->where('eko_status',6)->get();
            
            Session::flash('success', $decode->message);
            return view('new_pages.retailer.services.bill_dmt.txns_otp',compact('bank_channel','transaction_id','customer_name','customer_mobile','dmt_limit','dmt_use','available','amount','transafer_type','txns'));
            
        }else{
            
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
        
        
    }
    
    public function billPostDmtTransactionOTPSend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "transaction_id" => "required",
            "latitude" => "required",
            "longitude" => "required"
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
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $transaction_id = $request->get('transaction_id');
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $url = "https://user.payritepayment.in/api/v1/bill-dmt/do-transactions-otp-send?token=$token&user_mobile=".Auth::user()->mobile."&transaction_id=$transaction_id&latitude=$latitude&longitude=$longitude";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            $data = transactions_dmt::where('transaction_id',$transaction_id)->first();
            return response()->json(['success' => true, 'message' => $decode->message,'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => $decode->message]);
        }
        
    }
    
    public function billPostDmtTransactionOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
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
        
        
        $customer_mobile = $request->customer_mobile;
        $transaction_id = $request->transaction_id;
        $otp = $request->otp;
        $bank_channel = $request->get('bank_channel');
        
        $check_txn = transactions_dmt::where('transaction_id', $transaction_id)->where('status',0)->where('eko_status',6)->first();
        if(!$check_txn){
            
            return response()->json(['success' => false, 'message' => "Transacton Already Completed Check status In Your Report : $transaction_id"]);
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $url = "https://user.payritepayment.in/api/v1/bill-dmt/do-transactions-otp-verify?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&otp=$otp&transaction_id=$transaction_id&bank_channel=$bank_channel";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            $data = $decode->data;
            // Session::flash('dmt_success', $decode->message);
            // Session::flash('dmt_transaction_id', $decode->data->transaction_id);
            // return redirect()->route('dashboard_retailer');
            return response()->json(['success' => true, 'message' => 'Transaction Procced.','data'=>'']);
        }else{
            // print_r($decode);exit;
            return response()->json(['success' => false, 'message' => $decode->message]);
            // Session::flash('error', $decode->message);
            // return redirect()->route('dashboard_retailer');
        }
    }
    
    
    public function billPostDmtBenfAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "benf_name" => "required",
            "number" => "required",
            "ifsc" => "required",
            "banks" => "required|exists:banks,id",
            "is_verify" => "required",
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
            Session::flash('error', $param_message);
            
            $bank_channel = $request->get('bank_channel');
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            $url = "https://user.payritepayment.in/api/v1/bill-dmt/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            $url = "https://user.payritepayment.in/api/v1/bill-dmt/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&bank_channel=$bank_channel";
            $response_benf = $this->ApiCalls->payritePostCall($url);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode);exit;
            if(isset($decode_benf->data->recipientList->dmtRecipient)){
                $data = $decode_benf->data->recipientList->dmtRecipient;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            
            $banks = banks::get();
            $dmt_limit = $decode->limit;
            $dmt_use = $decode->used;
            $available = $decode->available;
            $customer_name = $decode->data->senderName;
            return view('new_pages.retailer.services.bill_dmt.beneficiaries',compact('bank_channel','data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
        }
        
        $bank_channel = $request->get('bank_channel');
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $customer_mobile = $request->get('mobile');
        $benf_name = urlencode($request->get('benf_name'));
        $number = $request->get('number');
        $ifsc = $request->get('ifsc');
        $is_verify = $request->get('is_verify');
        $benf_mobile = urlencode($request->get('benf_mobile'));
        
        $bank = banks::find($request->get('banks'));
        $bank_id = $bank->bill_id;
        
        $url = "https://user.payritepayment.in/api/v1/bill-dmt/add-beneficiary?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&name=$benf_name&account=$number&bank_id=$bank_id&ifsc=$ifsc&benf_mobile=$benf_mobile&is_verify=$is_verify&bank_channel=$bank_channel";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        
        if($decode->success){
            // print_r($decode);exit;
            
            
            $msg = $decode->message;
            Session::flash('success', $msg);
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            
            $url = "https://user.payritepayment.in/api/v1/bill-dmt/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            $url = "https://user.payritepayment.in/api/v1/bill-dmt/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&bank_channel=$bank_channel";
            $response_benf = $this->ApiCalls->payritePostCall($url);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode);exit;
            if(isset($decode_benf->data->recipientList->dmtRecipient)){
                $data = $decode_benf->data->recipientList->dmtRecipient;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            
            $banks = banks::get();
            $dmt_limit = $decode->limit;
            $dmt_use = $decode->used;
            $available = $decode->available;
            $customer_name = $decode->data->senderName;
            return view('new_pages.retailer.services.bill_dmt.beneficiaries',compact('bank_channel','data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
            
        }else{
            // print_r($decode);exit;
            $msg = $decode->message;
            Session::flash('error', $msg);
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            $url = "https://user.payritepayment.in/api/v1/bill-dmt/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            $url = "https://user.payritepayment.in/api/v1/bill-dmt/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&bank_channel=$bank_channel";
            $response_benf = $this->ApiCalls->payritePostCall($url);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode);exit;
            if(isset($decode_benf->data->data->recipient_list)){
                $data = $decode_benf->data->recipientList->dmtRecipient;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            
            $banks = banks::get();
            $dmt_limit = $decode->limit;
            $dmt_use = $decode->used;
            $available = $decode->available;
            $customer_name = $decode->data->senderName;
            return view('new_pages.retailer.services.bill_dmt.beneficiaries',compact('bank_channel','data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
            
        }
        
    }
    
    public function billDmtBenfDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "beneficiaries" => "required",
            "bank_channel" => "required"
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            
            return response()->json(["success"=>false,"message"=>$param_message,"data"=>""]);
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $bank_channel = $request->get('bank_channel');
        $customer_mobile = $request->get('mobile');
        $beneficiaries = $request->get('beneficiaries');
        $url = "https://user.payritepayment.in/api/v1/bill-dmt/delete-beneficiary?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&beneficiary_id=$beneficiaries&bank_channel=$bank_channel";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            // print_r($decode);exit;
            
            
            $msg = $decode->message;
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            $url = "https://user.payritepayment.in/api/v1/bill-dmt/get-beneficiaries?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            if(isset($decode->data->recipientList->dmtRecipient)){
                $data = $decode->data->recipientList->dmtRecipient;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            // $data = $decode->data;
            $html = "<option value=''>Select Beneficiaries</option>";
            foreach($data as $r){
                
                if(is_string($r->mobileNumber)){
                    $mobileno = $r->mobileNumber;
                }else{
                    $mobileno = '';
                }
                
                if(is_string($r->recipientName)){
                    $html .= "<option value='$r->recipientId'>$r->recipientName | $r->bankAccountNumber | $r->ifsc | $mobileno</option>"; 
                }else{
                    $html .= "<option value='$r->recipientId'> | $r->bankAccountNumber | $r->ifsc | $mobileno</option>"; 
                }
                
            }
            
            return response()->json(["success"=>true,"message"=>$msg,"data"=>$html]);
            
            
        }else{
            // print_r($decode);exit;
            $msg = $decode->message;
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            $url = "https://user.payritepayment.in/api/v1/dmt/get-beneficiaries?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            if(isset($decode->data->recipientList->dmtRecipient)){
                $data = $decode->data->recipientList->dmtRecipient;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            // $data = $decode->data;
            $html = "<option value=''>Select Beneficiaries</option>";
            foreach($data as $r){
                $html .= "<option value='$r->recipientId'>$r->recipientName | $r->bankAccountNumber | $r->ifsc | $r->mobileNumber</option>"; 
            }
            return response()->json(["success"=>false,"message"=>$msg,"data"=>$html]);
        }
        
        
    }
    
    public function billDmtRefundRequest($id)
    {
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $url = "https://user.payritepayment.in/api/v1/bill-dmt/refund-request?token=$token&transaction_id=$id&user_mobile=".Auth::user()->mobile;
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            $txn = transactions_dmt::where('transaction_id',$id)->first();
            return view('new_pages.retailer.report.refund_otp',compact('txn'));
        }else{
            Session::flash('error', 'Something Wrong, Please Connect With Admin');
            return redirect()->back();
        }
            
        
    }
    
    public function billDmtRefundOtpVeirfy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "otp" => "required",
            "txnid" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            
            return redirect()->route('dashboard_retailer');
        }
        
        $otp = $request->otp;
        $txnid = $request->txnid;
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $url = "https://user.payritepayment.in/api/v1/bill-dmt/refund-otp?token=$token&user_mobile=".Auth::user()->mobile."&transaction_id=$txnid&otp=$otp";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            Session::flash('success', 'Refund Request Submited.');
            return redirect()->route('dashboard_retailer');
        }else{
            Session::flash('error', 'Something Wrong, Please Connect With Admin');
            return redirect()->route('dashboard_retailer');
        }
    }
    
    public function qrOtpSend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "amount" => "required|numeric|min:10",
            "name" => "required|string|max:100",
            "surname" => "required|string|max:100",
            "mobile" => "required|regex:/^[0-9]{10}$/",
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
        
        $buyerPhone = trim($request->mobile);
    	$buyerFirstName = trim($request->name);
    	$buyerLastName = trim($request->surname);
    	$amount = trim($request->amount);
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $url = "https://user.payritepayment.in/api/v1/qr-payment/send-otp?token=$token&user_mobile=".Auth::user()->mobile."&amount=$amount&name=$buyerFirstName&surname=$buyerLastName&mobile=$buyerPhone";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        
        if(!isset($decode->success)){
            
            return response()->json(['success' => false, 'message' => 'Something Wrong!']);
        }
        if($decode->success == true){
            return response()->json(['success' => true, 'message' => "OTP SEND ON YOUR MOBILE","transaction_id" => $decode->transaction_id]);
        }else{
            return response()->json(['success' => false, 'message' => $decode->message]);
        }
    }
    
    public function qrOtpVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
        
        $transaction_id = trim($request->transaction_id);
    	$otp = trim($request->otp);
    	
    	$token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $url = "https://user.payritepayment.in/api/v1/qr-payment/verify-otp?token=$token&user_mobile=".Auth::user()->mobile."&otp=$otp&transaction_id=$transaction_id";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        
        if(!isset($decode->success)){
            
            return response()->json(['success' => false, 'message' => 'Something Wrong!']);
        }
        if($decode->success == true){
            return response()->json(['success' => true, 'message' => "QR","qr" => $decode->qr]);
        }else{
            return response()->json(['success' => false, 'message' => $decode->message]);
        }
    }
    
    public function airpayPg(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Disable']);
        $validator = Validator::make($request->all(), [
            
            "amount" => "required|numeric|min:50",
            "method" => "required",
            "email" => "required|email",
            "name" => "required|string|max:100",
            "surname" => "required|string|max:100",
            "mobile" => "required|regex:/^[0-9]{10}$/",
            "selfie" => "required_if:amount,>5000|image|mimes:jpeg,png,jpg,gif|max:5120",
        ]);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back()->withInput();
        }
        
        if($request->amount >= 5000){
            
        
            $validator_image = Validator::make($request->all(), [
                
                "selfie" => "required|image|mimes:jpeg,png,jpg|max:5120",
            ]);
            
            if ($validator_image->fails()) {
                $errors = $validator_image->errors();
                $param_message = '';
                foreach ($errors->messages() as $field => $messages) {
                    foreach ($messages as $message) {
                        $param_message .= " $message";
                    }
                }
                
                Session::flash('error', $param_message);
                return redirect()->back()->withInput();
            }
        }
        
        $service = eko_services::where('user_id',Auth::user()->id)->first();
        $allowed = array(182,26,34,31);
        if($service){
            if($service->airpay_pg_status != 1){
                Session::flash('error', 'Check With Admin');
                return redirect()->back()->withInput();
            }
        }else{
            Session::flash('error', 'Check With Admin');
            return redirect()->back()->withInput();
        }
        
        $buyerEmail = trim($request->email);
    	$buyerPhone = trim($request->mobile);
    	$buyerFirstName = trim($request->name);
    	$buyerLastName = trim($request->surname);
    	$buyerAddress = trim(Auth::user()->addresses->cities->name);
    	$amount = trim($request->amount);
    	$method = trim($request->method);
    	$buyerCity = trim(Auth::user()->addresses->cities->name);
    	$buyerState = trim(Auth::user()->addresses->cities->state_name);
    	$buyerPinCode = trim(Auth::user()->addresses->pincode);
    	$buyerCountry = trim('India');
    	$orderid = trim($this->UserAuth->txnId('ARP')); //Your System Generated Order ID
    	
    	$ins = new fund_onlines();
        $ins->user_id = Auth::user()->id;
        $ins->transaction_id = $orderid;
        $ins->amount = $amount;
        $ins->cust_name = $buyerFirstName;
        $ins->cust_surname = $buyerLastName;
        $ins->cust_email = $buyerEmail;
        $ins->cust_mobile = $buyerPhone;
        $ins->pg_id = 2;
        $ins->payment_method = $method;
        if ($request->hasFile('selfie')) {
            $file = $request->file('selfie');
            $destinationPath = public_path('/uploads/selfie_pg/');
            $imagename = 'PG'. Auth::user()->id . time() . '.' . $file->getClientOriginalExtension();
            // Resize and compress the image using Intervention Image
            $resizedImage = Image::load($file->getPathname())
                 ->quality(50); // 75 is the quality percentage
            // Define the storage path
            $path = $destinationPath . $imagename;
            // Save the resized and compressed image
            $resizedImage->save($path);
            // $file->move($destinationPath, $imagename);
            $ins->selfie = $imagename;
        }
        $ins->save();
    	
    	//UAT
    // 	$username =  '1669076'; // Username
    //     $password =  'u9RVqNzn'; // Password
    //     $secret =    'jBtjXt2NM7Xur8h6'; // API key
    //     $mercid = '270462'; //Merchant ID
        //LIVE
        $username =  'nJpKsy3dw2'; // Username
        $password =  'XqkEY6aW'; // Password
        $secret =    'eCkrg5XFk935RvK4'; // API key
        $mercid = '314685'; //Merchant ID
        
        $alldata   = $buyerEmail.$buyerFirstName.$buyerLastName.$buyerAddress.$buyerCity.$buyerState.$buyerCountry.$amount.$orderid;
        Log::channel("airpay")->info($alldata);
        $privatekey = $this->AirPayController->encrypt($username.":|:".$password, $secret);
        $keySha256 = $this->AirPayController->encryptSha256($username."~:~".$password);
        $checksum = $this->AirPayController->calculateChecksumSha256($alldata.date('Y-m-d'),$keySha256);
        $outputForm = $this->AirPayController->outputForm($checksum);
        $verify =  $this->AirPayController->verifyChecksum($checksum,$alldata.date('Y-m-d'),$secret);
        
        date_default_timezone_set('Asia/Kolkata');
        header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
        header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
        header( 'Cache-Control: no-store, no-cache, must-revalidate' );
        header( 'Cache-Control: post-check=0, pre-check=0', false );
        header( 'Pragma: no-cache' );
        
        return view('new_pages.airpay_pg',compact('outputForm','privatekey','mercid','orderid',
                                                'checksum','amount','method','buyerEmail','buyerFirstName','buyerLastName','buyerPhone'));
    }
    
    public function responseAirpay(Request $request)
    {
        Log::info($request->TRANSACTIONPAYMENTSTATUS);
        
        Session::flash('error', 'Done');
        return redirect()->route('dashboard_retailer');
    }
    
    public function credopayAeps(Request $request)
    {
        $user_id = Auth::user()->id;
        $check = eko_services::where('user_id',$user_id)->first();
        if(!$check){
            return view('new_pages.retailer.services.credo_aeps.registration');
        }
        
        if($check->credopay_aeps == 0){
            return view('new_pages.retailer.services.credo_aeps.registration');
        }
        
        if($check->credopay_aeps == 1){
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $url = "https://user.payritepayment.in/api/v1/cp-aeps/check-fa?token=$token&user_mobile=".Auth::user()->mobile;
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            if($decode->success){
                if($decode->auth == 1){
                    // $retailer = User::with('shopDetail')->where('id',$user_id)->first();
                    // $decode = json_decode('{"transaction_type":"mini_statement","response_code":"00","response_status":"TXN DONE SUCCESSFULLY","transaction_amount":"0","customer_name":"","reference_no":"428220526702","date":"20241008","time":"","response_description":"TXN DONE SUCCESSFULLY","transaction_id":"670548418d56cadd847c9f58","rrn":"428220526702","CRN_U":"TUEAEPS241008901286","created_at":"2024-10-08 20:27:05","mini_statement":["08/10 UPI/F/56973210   D    1000.00","08/10 UPI/F/56406910   C     500.00","08/10 UPI/F/55585314   C    1000.00","08/10 FIG/F/53777204   D       5.90","08/10 NEF/F/35560193   C     885.00","07/10 UPI/F/26169672   D    3900.00","                   Balance:+2167.03"]}');
                    // return view('new_pages.retailer.services.credo_aeps.mini',compact('decode','retailer'));
                    $banks = banks::whereNotNull('credopay_id')->get();
                    return view('new_pages.retailer.services.credo_aeps.txn',compact('banks'));
                }else{
                    // return view('new_pages.retailer.services.credo_aeps.txn');
                    return view('new_pages.retailer.services.credo_aeps.2fa');
                }
            }else{
                Session::flash('error', $decode->message);
                return redirect()->route('dashboard_retailer');
            }
        }
        
        Session::flash('error', "Waiting For Activation!!");
        return redirect()->route('dashboard_retailer');
        
    }
    
    public function credopayAepsRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "establishedYear" => "required",
            "title" => "required",
            "bankAccountNumber" => "required",
            "accountType" => "required",
            "CANCELLED_CHEQUE_NO" => "required",
            "CANCELLED_CHEQUE" => "required|image|mimes:jpeg,png,jpg,gif|max:5120",
            "deviceSerialNumber" => "required",
            "device_model" => "required",
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
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        
        $inskyc = kyc_docs::where('user_id',Auth::user()->id)->where('status',1)->first();
        if($inskyc){
            if ($request->hasFile('CANCELLED_CHEQUE')) {
                $file = $request->file('CANCELLED_CHEQUE');
                $destinationPath = public_path('/uploads/cheque/');
                $imagename = 'CHEQUE'. Auth::user()->id . time() . '.' . $file->getClientOriginalExtension();
                // Resize and compress the image using Intervention Image
                $resizedImage = Image::load($file->getPathname())
                     ->quality(50); // 75 is the quality percentage
                // Define the storage path
                $path = $destinationPath . $imagename;
                // Save the resized and compressed image
                $resizedImage->save($path);
                // $file->move($destinationPath, $imagename);
                $inskyc->cheque_image = $imagename;
                $inskyc->cheque_number = $request->CANCELLED_CHEQUE_NO;
                $inskyc->bank_account = $request->bankAccountNumber;
                $inskyc->bank_ifsc = $request->ifsc;
                $inskyc->bank_account_type = $request->accountType;
                $inskyc->name_title = $request->title;
            }
            $inskyc->save();
        }else{
            Session::flash('error', 'Please Add KYC Details');
            return redirect()->back();
        }
        
        $insshop = shop_details::where('user_id',Auth::user()->id)->where('status',1)->first();
        if($insshop){
            $insshop->established = $request->establishedYear;
            $insshop->device_number = $request->deviceSerialNumber;
            $insshop->device_model = $request->device_model;
            $insshop->save();
        }else{
            Session::flash('error', 'Please Add Shop Details');
            return redirect()->back();
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $url = "https://user.payritepayment.in/api/v1/cp-aeps/merchant-onboarding?token=$token&user_mobile=".Auth::user()->mobile;
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        
        if($decode->success){
            $check = eko_services::where('user_id',Auth::user()->id)->first();
            if(!$check){
                $check = new eko_services();
                $check->user_id = Auth::user()->id;
                $check->credopay_aeps = 2;
                $check->save();
            }else{
                $check->credopay_aeps = 2;
                $check->save();
            }
            Session::flash('success', 'Merchant Detail Submited. Please Wait for 24h for activation.');
            return redirect()->route('dashboard_retailer');
        }else{
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
    }
    
    public function credopayAepsFa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
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
            
            
            return redirect()->back();
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $PidData = $request->PidData;
        $mi = $request->mi;
        $rdsVer = $request->rdsVer;
        $rdsId = $request->rdsId;
        $srno = $request->srno;
        
        // $append = "&PidData=$PidData&mi=$mi&rdsVer=$rdsVer&rdsId=$rdsId&srno=$srno";
        $data = array("PidData"=>$PidData,
                        "mi"=>$mi,
                        "rdsVer"=>$rdsVer,
                        "rdsId"=>$rdsId,
                        "srno"=>$srno,
                        "user_mobile"=>Auth::user()->mobile,
                        "token"=>$token);
        $url = "https://user.payritepayment.in/api/v1/cp-aeps/fa";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        if($decode->success){
            Session::flash('success', $decode->message);
            return redirect()->route('credo_aeps_retailer');
        }else{
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
        
        
    }
    
    public function credopayAepsTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "PidData" => "required",
            "mi" => "required",
            "rdsVer" => "required",
            "rdsId" => "required",
            "srno" => "required",
            "amount" => "required",
            "bank_name" => "required",
            "aadhar" => "required",
            "transaction_type" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            
            return redirect()->back();
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $PidData = $request->PidData;
        $mi = $request->mi;
        $rdsVer = $request->rdsVer;
        $rdsId = $request->rdsId;
        $srno = $request->srno;
        
        $transaction_type = $request->transaction_type;
        $aadhar = $request->aadhar;
        $bank_name = $request->bank_name;
        $amount = $request->amount;
        
        $user_id = Auth::user()->id;
        
        // $append = "&PidData=$PidData&mi=$mi&rdsVer=$rdsVer&rdsId=$rdsId&srno=$srno";
        $data = array("PidData"=>$PidData,
                        "mi"=>$mi,
                        "rdsVer"=>$rdsVer,
                        "rdsId"=>$rdsId,
                        "srno"=>$srno,
                        "user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "amount"=>$amount,
                        "transaction_type"=>$transaction_type,
                        "bank_name"=>$bank_name,
                        "aadhar"=>$aadhar);
        $url = "https://user.payritepayment.in/api/v1/cp-aeps/do-transaction";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        if($decode->success){
            $retailer = User::with('shopDetail')->where('id',$user_id)->first();
            $bank = banks::where('credopay_id',$bank_name)->first();
            if($decode->transaction_type == 'mini_statement'){
                return view('new_pages.retailer.services.credo_aeps.mini',compact('decode','retailer','bank'));
            }
            
            if($decode->transaction_type == 'cash_withdrawal'){
                
                
                return view('new_pages.retailer.services.credo_aeps.cash',compact('decode','retailer','bank'));
            }
            
            if($decode->transaction_type == 'balance_enquiry'){
                return view('new_pages.retailer.services.credo_aeps.balance',compact('decode','retailer','bank'));
            }
            
        }else{
            Session::flash('error', $decode->message);
            return redirect()->route('credo_aeps_retailer');
        }
        
    }
    
    public function aepsReport(Request $request)
    {
        return view('new_pages.retailer.report.aeps');
    }
    
    public function aepsReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $user_id = Auth::user()->id;
        $data = transactions_aeps::select('transactions_aeps.*','banks.name')
            ->join('banks','banks.credopay_id','transactions_aeps.bank_iin')
            ->where('transactions_aeps.user_id',$user_id)
            ->whereBetween('transactions_aeps.created_at', array($from, $to))
            ->orderBy('transactions_aeps.id','DESC')->get();
            
        return response()->json($data);
    }
    
    public function businessReport(Request $request)
    {
        $users = User::where('user_type',2)->get();
        return view('new_pages.retailer.report.business_report',compact('users'));
    }
    
    public function businessReportData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
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
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        
        $user_id = Auth::user()->id;
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $data = Transaction::select("transactions.created_at",
            DB::raw('COALESCE(transactions_aeps.transfer_type, transactions_dmts.transfer_type, transactions_dmts.event) as txn_type'),
            "transactions.type",
            DB::raw('COALESCE(transactions_aeps.remitterName, transactions_dmts.ben_name) as ben_name'),
            DB::raw('COALESCE(transactions_aeps.aadhaar, transactions_dmts.ben_ac_number) as account_no'),
            DB::raw('COALESCE(transactions_aeps.transaction_id, transactions_dmts.transaction_id) as transaction_id'),
            DB::raw('COALESCE(transactions_aeps.status, transactions_dmts.status) as status'),
            DB::raw("COALESCE(users.name, ' ', users.surname) as user_name"),'transactions.type',
            DB::raw('ROUND(transactions.amount / 100, 2) as amount'),
            DB::raw('ROUND(transactions.balance / 100, 2) as balance'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(transactions.meta, "$.meta.detail")) as narration'),
            DB::raw('COALESCE(transactions_aeps.utr, transactions_dmts.utr) as utr'),
            "transactions_dmts.fee",
            "transactions_dmts.tds",
            "transactions_dmts.gst",
            "shop_details.shop_name",
            "users.mobile")
            ->join('users','users.id','transactions.payable_id')
            ->join('shop_details','shop_details.user_id','transactions.payable_id')
            ->leftjoin('transactions_aeps','transactions_aeps.wallets_uuid','transactions.uuid')
            ->leftjoin('transactions_dmts','transactions_dmts.wallets_uuid','transactions.uuid')
            ->where('transactions.payable_id',$user_id)
            ->where('transactions.payable_type', User::class)
            ->whereBetween('transactions.created_at', array($from, $to))
            ->orderBy('transactions.id', 'desc')
            ->get();
            
            $data = $data->map(function($data) {
                $commission = 0;
                $commission_tds = 0;
                // Calculate commission for each transaction
                if($data->txn_type == 'UPI'){
                    $commission_details = $this->WalletCalculation->retailorUpi($data->transaction_id);
                    // Add commission details to transaction
                    $commission = $commission_details['comm'];
                    $commission_tds = $commission_details['tds'];
                }
                
                if($data->txn_type == 'IMPS' || $data->txn_type == 'NEFT'){
                    $commission_details = $this->WalletCalculation->retailorDmt($data->transaction_id);
                    // Add commission details to transaction
                    $commission = $commission_details['comm'];
                    $commission_tds = $commission_details['tds'];
                }
                
                if($data->txn_type == 'cash_withdrawal' || $data->txn_type == 'mini_statement'){
                    $commission_details = $this->WalletCalculation->retailorAeps($data->transaction_id);
                    // Add commission details to transaction
                    $commission = $commission_details['comm'];
                    $commission_tds = $commission_details['tds'];
                }
                
                $final_commission = $commission + $commission_tds;
                if($data->txn_type == 'IMPS' || $data->txn_type == 'NEFT' || $data->txn_type == 'UPI' || $data->txn_type == 'cash_withdrawal' || $data->txn_type == 'balance_enquiry' || $data->txn_type == 'mini_statement'){
                    $status = $data->status == 1 ? "Success" 
                             : ($data->status == 0 ? "Pending" 
                             : ($data->status == 2 ? "Failed" 
                             : ($data->status == 3 ? "Failed" 
                             : "Failed")));
                }else{
                    $status = "";
                }
                return [
                        'user_name' => $data->user_name,
                        'mobile' => $data->mobile,
                        'shop_name' => $data->shop_name,
                        'created_at' => $data->created_at,
                        'txn_type' => $data->txn_type,
                        'type' => $data->type,
                        'status' => $status,
                        
                        'ben_name' => $data->ben_name,
                        'account_no' => $data->account_no,
                        'transaction_id' => $data->transaction_id,
                        'narration' => $data->narration,
                        'utr' => $data->utr,
                        'balance' => $data->balance,
                        'amount' => $data->amount,
                        'fee' => $data->fee,
                        'tds' => $data->tds,
                        'commission' => $commission,
                        'commission_tds' => $commission_tds,
                        'final_commission' => $final_commission
                    ];
            })
            ->toArray();
            
        // print_r($data);
        return $this->toCsvExport($data,'user_business.csv');
    }
    
    //DIGI DMT
    public function digiDmtLogin(Request $request)
    {
        
        return view('new_pages.retailer.services.digi_dmt.login');
    }
    
    public function digiPostDmtLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "latitude" => "required",
            "longitude" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    if($field == 'latitude' || $field == 'longitude'){
                        $redirect = 0;
                    }else{
                        $redirect = 1;
                    }
                    $param_message .= "$field : $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        $customer_mobile = $request->get('mobile');

        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        
        $url = "https://user.payritepayment.in/api/v1/digikhata/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&customer_name=Payrite&pincode=360005";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        
        if($decode->success == true){
            $otp_token = $decode->data->result->otpToken;
            Session::flash('success', $decode->message);
            return view('new_pages.retailer.services.digi_dmt.login_otp',compact('otp_token','customer_mobile'));
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return redirect()->back();
            
        }
        
        
    }
    
    public function digiPostDmtLoginOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "otp" => "required",
            "otp_token" => "required",
            "mobile" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    if($field == 'latitude' || $field == 'longitude'){
                        $redirect = 0;
                    }else{
                        $redirect = 1;
                    }
                    $param_message .= "$field : $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        $otp = $request->get('otp');
        $otp_token = $request->get('otp_token');
        $customer_mobile = $request->get('mobile');

        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        
        $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "customer_mobile"=>$customer_mobile,
                        "otp"=>$otp,
                        "otp_token"=>$otp_token);
        $url = "https://user.payritepayment.in/api/v1/digikhata/customer-otp-verify";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        
        if($decode->success == true && $decode->is_reg == 1){
            $dmt_token = $decode->data->result->token;
            
            $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "customer_mobile"=>$customer_mobile,
                        "dmt_token"=>$dmt_token);
                        
            $url = "https://user.payritepayment.in/api/v1/digikhata/get-beneficiaries";
            $response_benf = $this->ApiCalls->payritePostCallWithParam($url,$data);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode_benf);exit;
            if(isset($decode_benf->recipientList)){
                $data = $decode_benf->recipientList;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            // print_r($data);
            // print_r(count($data));exit;
            $banks = banks::get();
            $dmt_limit = $decode->limit;
            $dmt_use = $decode->used;
            $available = $decode->available;
            $customer_name = $decode->data->result->walletHolderName;
            
            Session::flash('success', 'Successfuly Login.');
            return view('new_pages.retailer.services.digi_dmt.beneficiaries',compact('dmt_token','data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
        }else{
            Session::flash('error', $decode->message);
            if(isset($decode->is_reg)){
                $dmt_token = $decode->data->result->token;
            // print_r($decode);exit;
            $walletAcApplicationNumber = $decode->data->result->walletAcApplicationNumber;
            Session::put('walletAcApplicationNumber', $walletAcApplicationNumber);
            
            return redirect()->route('digi_get_dmt_create_customer_retailer', ['id' => $customer_mobile,'token'=>$dmt_token]);
            // return view('new_pages.retailer.services.digi_dmt.registration',compact('customer_mobile'));
            }
            return redirect()->route('dashboard_retailer');
            
            
        }
        
        
    }
    
    public function digiGetDmtCreateCustomer($customer_mobile,Request $request)
    {
        $dmt_token = $request->token;
        return view('new_pages.retailer.services.digi_dmt.registration',compact('customer_mobile','dmt_token'));
    }
    
    public function digiPostDmtCreateCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "mobile" => "required",
            "name" => "required",
            "pincode" => "required",
            "aadharno" => "required",
            "dmt_token" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    if($field == 'latitude' || $field == 'longitude'){
                        $redirect = 0;
                    }else{
                        $redirect = 1;
                    }
                    $param_message .= "$field : $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        $customer_mobile = $request->get('mobile');
        $name = $request->get('name');
        $pincode = $request->get('pincode');
        $aadharno = $request->get('aadharno');
        $dmt_token = $request->get('dmt_token');
        $walletAcApplicationNumber = session('walletAcApplicationNumber');

        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        
        $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "customer_mobile"=>$customer_mobile,
                        "aadharno"=>$aadharno,
                        "customer_name"=>$name,
                        "pincode"=>$pincode,
                        "dmt_token"=>$dmt_token,
                        "walletAcApplicationNumber"=>$walletAcApplicationNumber);
        
        $url = "https://user.payritepayment.in/api/v1/digikhata/aadhar-otp";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        
        if($decode->success == true && $decode->is_reg == 0){
            $otp_token = $decode->data->result->otpToken;
            Session::flash('success', 'OTP Sent On your Number');
            return view('new_pages.retailer.services.digi_dmt.otp_fino',compact('decode','customer_mobile','dmt_token','name','pincode','otp_token'));
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return view('new_pages.retailer.services.digi_dmt.registration',compact('customer_mobile','dmt_token'));
        }
        
        
    }
    
    public function digiPostDmtCreateCustomerVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "name" => "required",
            "pincode" => "required",
            "otp" => "required",
            "otp_token" => "required",
            "pan_number" => "required",
            "dmt_token" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    if($field == 'latitude' || $field == 'longitude'){
                        $redirect = 0;
                    }else{
                        $redirect = 1;
                    }
                    $param_message .= "$field : $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        $customer_mobile = $request->get('mobile');
        $otp = urlencode($request->get('otp'));
        $otp_token = $request->get('otp_token');
        $pan_number = $request->get('pan_number');
        $dmt_token = $request->get('dmt_token');
        $name = $request->get('name');
        $pincode = $request->get('pincode');
        $walletAcApplicationNumber = session('walletAcApplicationNumber');

        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        
        $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "customer_mobile"=>$customer_mobile,
                        "otp_token"=>$otp_token,
                        "otp"=>$otp,
                        "dmt_token"=>$dmt_token,
                        "walletAcApplicationNumber"=>$walletAcApplicationNumber);
        
        $url = "https://user.payritepayment.in/api/v1/digikhata/aadhar-otp-verify";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        
        if($decode->success == true && $decode->is_reg == 0){
            
            $data = array("user_mobile"=>Auth::user()->mobile,
                            "token"=>$token,
                            "customer_mobile"=>$customer_mobile,
                            "customer_name"=>$name,
                            "pincode"=>$pincode,
                            "pan_number"=>$pan_number,
                            "dmt_token"=>$dmt_token,
                            "walletAcApplicationNumber"=>$walletAcApplicationNumber);
            
            $url = "https://user.payritepayment.in/api/v1/digikhata/pancard-kyc";
            $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
            $decode_pan = json_decode($response);
            
            if($decode_pan->success == true){
                Session::flash('success', 'Registration Completed');
                // return redirect()->route('dashboard_retailer');
                
                $data = [];
                $banks = banks::get();
            
                $dmt_limit = $decode_pan->data->resultData->cashTopUpLimitAvailable;
                $dmt_use = 0;
                $available = $decode_pan->data->resultData->cashTopUpLimitAvailable;
                $customer_name = $decode_pan->data->resultData->walletHolderName;
            
                return view('new_pages.retailer.services.digi_dmt.beneficiaries',compact('dmt_token','data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
            }
            
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
        
        
    }
    
    public function digiPostDmtBenfAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "benf_name" => "required",
            "number" => "required",
            "ifsc" => "required",
            "banks" => "required",
            "is_verify" => "required",
            "benf_mobile" => "required",
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
        
        $dmt_token = $request->get('dmt_token');
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $customer_mobile = $request->get('mobile');
        $benf_name = $request->get('benf_name');
        $number = $request->get('number');
        $ifsc = $request->get('ifsc');
        $is_verify = $request->get('is_verify');
        $benf_mobile = urlencode($request->get('benf_mobile'));
        $banks = $request->get('banks');
        // $bank = banks::find($request->get('banks'));
        // $bank_id = urlencode($bank->name);
        
        $data = array("user_mobile"=>Auth::user()->mobile,
                            "token"=>$token,
                            "customer_mobile"=>$customer_mobile,
                            "ben_mobile"=>$benf_mobile,
                            "ben_name"=>$benf_name,
                            "ben_account"=>$number,
                            "bank_name"=>$banks,
                            "dmt_token"=>$dmt_token,
                            "ifsc"=>$ifsc);
            
        $url = "https://user.payritepayment.in/api/v1/digikhata/add-beneficiary";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        
        if($decode->success){
            $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "customer_mobile"=>$customer_mobile,
                        "dmt_token"=>$dmt_token);
                        
            $url = "https://user.payritepayment.in/api/v1/digikhata/get-beneficiaries";
            $response_benf = $this->ApiCalls->payritePostCallWithParam($url,$data);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode_benf);exit;
            if(isset($decode_benf->recipientList)){
                $data = $decode_benf->recipientList;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            
            $html = "<option value=''>Select Beneficiaries</option>";
            foreach($data as $r){
                
                if(is_string($r->beneficiaryMobile)){
                    $mobileno = $r->beneficiaryMobile;
                }else{
                    $mobileno = '';
                }
                
                if(is_string($r->beneficiaryName)){
                    $html .= "<option value='$r->beneId#$r->beneficiaryName#$r->accountNo#$r->ifsCcode'>$r->beneficiaryName | $r->accountNo | $r->ifsCcode | $mobileno</option>";
                    
                }else{
                    $html .= "<option value='$r->beneId#-#$r->accountNo#$r->ifsCcode'> | $r->accountNo | $r->ifsCcode | $mobileno</option>"; 
                }
                
            }
            
            return response()->json(['success' => true, 'message' => '', 'data'=>$html]);
            
        }else{
            return response()->json(['success' => false, 'message' => $decode->message]);
        }
        
    }
    
    public function digiPostDmtBenfDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "ben_id" => "required",
            "sender_mobile" => "required",
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
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $dmt_token = $request->get('dmt_token');
        $ben_id = $request->get('ben_id');
        $customer_mobile = $request->get('sender_mobile');
        
        $data = array("user_mobile"=>Auth::user()->mobile,
                            "token"=>$token,
                            "customer_mobile"=>$customer_mobile,
                            "ben_id"=>$ben_id,
                            "dmt_token"=>$dmt_token);
            
        $url = "https://user.payritepayment.in/api/v1/digikhata/delete-beneficiary";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        
        if($decode->success){
            return response()->json(['success' => true, 'message' => $decode->message, 'data'=>$decode]);
        }else{
            return response()->json(['success' => false, 'message' => $decode->message]);
        }
    }
    
    public function digiPostDmtBenfDeleteOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "otp" => "required",
            "otp_token" => "required",
            "sender_mobile" => "required",
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
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $dmt_token = $request->get('dmt_token');
        $otp = $request->get('otp');
        $otp_token = $request->get('otp_token');
        $customer_mobile = $request->get('sender_mobile');
        
        $data = array("user_mobile"=>Auth::user()->mobile,
                            "token"=>$token,
                            "customer_mobile"=>$customer_mobile,
                            "otp_token"=>$otp_token,
                            "otp"=>$otp,
                            "dmt_token"=>$dmt_token);
            
        $url = "https://user.payritepayment.in/api/v1/digikhata/delete-beneficiary-otp";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        
        if($decode->success){
            $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "customer_mobile"=>$customer_mobile,
                        "dmt_token"=>$dmt_token);
                        
            $url = "https://user.payritepayment.in/api/v1/digikhata/get-beneficiaries";
            $response_benf = $this->ApiCalls->payritePostCallWithParam($url,$data);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode_benf);exit;
            if(isset($decode_benf->recipientList)){
                $data = $decode_benf->recipientList;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            
            $html = "<option value=''>Select Beneficiaries</option>";
            foreach($data as $r){
                
                if(is_string($r->beneficiaryMobile)){
                    $mobileno = $r->beneficiaryMobile;
                }else{
                    $mobileno = '';
                }
                
                if(is_string($r->beneId)){
                    $html .= "<option value='$r->beneId#$r->beneficiaryName#$r->accountNo#$r->ifsCcode'>$r->beneficiaryName | $r->accountNo | $r->ifsCcode | $mobileno</option>";
                    
                }else{
                    $html .= "<option value='$r->beneId#-#$r->accountNo#$r->ifsCcode'> | $r->accountNo | $r->ifsCcode | $mobileno</option>"; 
                }
                
            }
            
            return response()->json(['success' => true, 'message' => $decode->message, 'data'=>$html]);
        }else{
            return response()->json(['success' => false, 'message' => $decode->message]);
        }
    }
    
    public function digiPostDmtTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "beneficiaries" => "required",
            "transafer_type" => "required",
            "amount" => "required",
            "latitude" => "required",
            "longitude" => "required",
            "accuracy" => "required",
            "sender_name" => "required",
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
            
            Session::flash('error', $param_message);
            return redirect()->route('dashboard_retailer');
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        
        $sender_name = $request->get('sender_name');
        $dmt_token = $request->get('dmt_token');
        $customer_mobile = $request->get('mobile');
        $beneficiaries = $request->get('beneficiaries');
        
        $beneficiary = explode("#", $beneficiaries);
        
        $transafer_type = $request->get('transafer_type');
        $amount = $request->get('amount');
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $accuracy = $request->get('accuracy')/1000;
        $extra_string = "&ben_account=$beneficiary[2]&ben_name=".urlencode($beneficiary[1])."&ben_ifsc=$beneficiary[3]";
        
        $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "sender_name"=>$sender_name,
                        "customer_mobile"=>$customer_mobile,
                        "dmt_token"=>$dmt_token,
                        "ben_account"=>$beneficiary[2],
                        "ben_name"=>$beneficiary[1],
                        "ben_ifsc"=>$beneficiary[3],
                        "beneficiary_id"=>$beneficiary[0],
                        "amount"=>$amount,
                        "transfer_type"=>$transafer_type,
                        "latitude"=>$latitude,
                        "longitude"=>$longitude,
                        "accuracy"=>$accuracy,
                        "platform"=>'web');
                        
        $url = "https://user.payritepayment.in/api/v1/digikhata/do-transactions";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        
        
        if($decode->success){
            
            $banks = banks::get();
            $customer_name = $sender_name;
            
            $transafer_type = $request->get('transafer_type');
            $transaction_ids = $decode->transaction_id;
            $otp_token = $decode->data->result->otpToken;
            
            $txns = transactions_dmt::where('transaction_id', $transaction_ids)->where('status',0)->where('eko_status',6)->first();
            
            Session::flash('success', $decode->message);
            return view('new_pages.retailer.services.digi_dmt.txn_otp',compact('txns','customer_name','customer_mobile','otp_token','dmt_token'));
            
        }else{
            
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
        
        
    }
    
    public function digiPostDmtTransactionOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "customer_mobile" => "required",
            "transaction_id" => "required",
            "otp" => "required",
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
            
            Session::flash('error', $param_message);
            return redirect()->route('dashboard_retailer');
        }
        
        
        $customer_mobile = $request->customer_mobile;
        $transaction_id = $request->transaction_id;
        $otp = $request->otp;
        $dmt_token = $request->get('dmt_token');
        
        $check_txn = transactions_dmt::where('transaction_id', $transaction_id)->where('status',0)->where('eko_status',6)->first();
        if(!$check_txn){
            Session::flash('error', "Transacton Already Completed Check status In Your Report : $transaction_id");
            return redirect()->route('dashboard_retailer');
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "customer_mobile"=>$customer_mobile,
                        "otp_token"=>$check_txn->otp_reference,
                        "dmt_token"=>$dmt_token,
                        "amount"=>$check_txn->amount,
                        "otp"=>$otp,
                        "transaction_id"=>$transaction_id,);
                        
        $url = "https://user.payritepayment.in/api/v1/digikhata/do-transactions-otp-verify";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        if($decode->success){
            $data = $decode->data;
            Session::flash('dmt_success', $decode->message);
            Session::flash('dmt_transaction_id', $decode->data->transaction_id);
            return redirect()->route('dashboard_retailer');
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
    }
    
    public function digiDmtRefundRequest($id)
    {
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $url = "https://user.payritepayment.in/api/v1/digikhata/refund-request?token=$token&transaction_id=$id&user_mobile=".Auth::user()->mobile;
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            $txn = transactions_dmt::where('transaction_id',$id)->first();
            return view('new_pages.retailer.report.refund_otp_digi',compact('txn'));
        }else{
            Session::flash('error', 'Something Wrong, Please Connect With Admin');
            return redirect()->back();
        }
            
        
    }
    
    public function digiDmtRefundOtpVeirfy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "otp" => "required",
            "txnid" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            
            return redirect()->route('dashboard_retailer');
        }
        
        $otp = $request->otp;
        $txnid = $request->txnid;
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $url = "https://user.payritepayment.in/api/v1/digikhata/refund-otp?token=$token&user_mobile=".Auth::user()->mobile."&transaction_id=$txnid&otp=$otp";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            Session::flash('success', 'Refund Request Submited.');
        }else{
            Session::flash('error', 'Something Wrong, Please Connect With Admin');
        }
        
        if(Auth::user()->user_type == 1){
            return redirect()->route('dashboard_admin');
        }else{
            return redirect()->route('dashboard_retailer');
        }
    }
    
    //ACEMONEY DMT
    public function aceDmtLogin(Request $request)
    {
        
        return view('new_pages.retailer.services.ace_dmt.login');
    }
    
    public function acePostDmtLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "bank_channel" => "required",
            "latitude" => "required",
            "longitude" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    if($field == 'latitude' || $field == 'longitude'){
                        $redirect = 0;
                    }else{
                        $redirect = 1;
                    }
                    $param_message .= "$field : $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        $customer_mobile = $request->get('mobile');
        $bank_channel = $request->get('bank_channel');

        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        
        $url = "https://user.payritepayment.in/api/v1/acemoney/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        
        if($decode->success == true && $decode->is_reg == 1){
            $url = "https://user.payritepayment.in/api/v1/acemoney/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&bank_channel=$bank_channel";
            $response_benf = $this->ApiCalls->payritePostCall($url);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode_benf);exit;
            if(isset($decode_benf->data->benes)){
                $data = $decode_benf->data->benes;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            // print_r($data);
            // print_r(count($data));exit;
            $banks = banks::get();
            $dmt_limit = $decode->limit;
            $dmt_use = $decode->used;
            $available = $decode->available;
            $customer_name = $decode->data->customer_name;
            Session::flash('success', 'Successfuly Login.');
            return view('new_pages.retailer.services.ace_dmt.beneficiaries',compact('bank_channel','data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return view('new_pages.retailer.services.ace_dmt.registration_fino',compact('customer_mobile','bank_channel'));
            
        }
        
        
    }
    
    public function acePostDmtCreateCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "name" => "required",
            "pincode" => "required",
            "address" => "required",
            "PidData" => "required",
            "aadhar" => "required",
            "bank_channel" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    if($field == 'latitude' || $field == 'longitude'){
                        $redirect = 0;
                    }else{
                        $redirect = 1;
                    }
                    $param_message .= "$field : $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        $customer_mobile = $request->get('mobile');
        $name = $request->get('name');
        $address = $request->get('address');
        $pincode = $request->get('pincode');
        $PidData = $request->get('PidData');
        $aadhar = $request->get('aadhar');
        $bank_channel = $request->get('bank_channel');

        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        session(['piddata_ace' => $PidData]);
        $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "customer_mobile"=>$customer_mobile,
                        "mobile"=>$customer_mobile,
                        "name"=>$name,
                        "pincode"=>$pincode,
                        "piddata"=>$PidData,
                        "piddata_type"=>'FIR',
                        "aadhar"=>$aadhar,
                        "bank_channel"=>$bank_channel);
        
        $url = "https://user.payritepayment.in/api/v1/acemoney/ekyc-customer";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        
        if($decode->success == true && $decode->is_reg == 0){
            if($decode->is_otp == 1){
                Session::flash('success', 'OTP Sent On your Number');
                return view('new_pages.retailer.services.ace_dmt.otp_fino',compact('decode','customer_mobile','bank_channel','name','pincode','aadhar','address'));
            }else{
                
            }
            
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return view('new_pages.retailer.services.ace_dmt.registration_fino',compact('customer_mobile','bank_channel'));
        }
        
        
    }
    
    public function acePostDmtCreateCustomerVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "mobile" => "required",
            "otp" => "required",
            "bank_channel" => "required",
            "name" => "required",
            "pincode" => "required",
            "OTPRequestID" => "required",
            "KYCRequestID" => "required",
            "address" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    if($field == 'latitude' || $field == 'longitude'){
                        $redirect = 0;
                    }else{
                        $redirect = 1;
                    }
                    $param_message .= "$field : $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        $customer_mobile = $request->get('mobile');
        $otp = $request->get('otp');
        $bank_channel = $request->get('bank_channel');
        $name = $request->get('name');
        $pincode = $request->get('pincode');
        $aadhar = $request->get('aadhar');
        $address = $request->get('address');
        $piddata = session('piddata_ace');
        $OTPRequestID = $request->get('OTPRequestID');
        $KYCRequestID = $request->get('KYCRequestID');
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "mobile"=>$customer_mobile,
                        "name"=>$name,
                        "pincode"=>$pincode,
                        "aadhar"=>$aadhar,
                        "address"=>$address,
                        "otp"=>$otp,
                        "KYCRequestId"=>$KYCRequestID,
                        "OTPRequestId"=>$OTPRequestID,
                        "bank_channel"=>$bank_channel,
                        "piddata"=>$piddata,
                        "piddata_type"=>'FIR');
        $url = "https://user.payritepayment.in/api/v1/acemoney/create-customer";
        $response = $this->ApiCalls->payritePostCallWithParam($url,$data);
        $decode = json_decode($response);
        
        if($decode->success == true && $decode->is_reg == 1){
            session(['piddata_ace' => '']);
            // Session::flash('success', 'OTP Verified');
            // return redirect()->route('dashboard_retailer');
            
            $url = "https://user.payritepayment.in/api/v1/acemoney/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            
            if($decode->success == true && $decode->is_reg == 1){
                $url = "https://user.payritepayment.in/api/v1/acemoney/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&bank_channel=$bank_channel";
                $response_benf = $this->ApiCalls->payritePostCall($url);
                $decode_benf = json_decode($response_benf);
                
                // print_r($decode_benf);exit;
                if(isset($decode_benf->data->benes)){
                    $data = $decode_benf->data->benes;
                    if(is_array($data)){
                        
                    }else{
                        $data = [$data];
                    }
                }else{
                    $data = [];
                }
                // print_r(count($data));exit;
                $banks = banks::get();
                $dmt_limit = $decode->limit;
                $dmt_use = $decode->used;
                $available = $decode->available;
                $customer_name = $decode->data->customer_name;
                Session::flash('success', 'Successfuly Login.');
                return view('new_pages.retailer.services.ace_dmt.beneficiaries',compact('bank_channel','data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
            }else{
                // print_r($decode);exit;
                Session::flash('success', 'OTP Verified');
                return redirect()->route('dashboard_retailer');
            }
        }else{
            // print_r($decode);exit;
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
            // return view('new_pages.retailer.services.bill_dmt.otp',compact('additional_reg_data','customer_mobile'));
        }
        
        
    }
    
    public function acePostDmtBenfAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "benf_name" => "required",
            "number" => "required",
            "ifsc" => "required",
            "banks" => "required|exists:banks,id",
            "is_verify" => "required",
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
            Session::flash('error', $param_message);
            
            $bank_channel = $request->get('bank_channel');
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            $url = "https://user.payritepayment.in/api/v1/acemoney/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            $url = "https://user.payritepayment.in/api/v1/acemoney/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&bank_channel=$bank_channel";
            $response_benf = $this->ApiCalls->payritePostCall($url);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode);exit;
            if(isset($decode_benf->data->benes)){
                $data = $decode_benf->data->benes;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            
            $banks = banks::get();
            $dmt_limit = $decode->limit;
                $dmt_use = $decode->used;
                $available = $decode->available;
                $customer_name = $decode->data->customer_name;
            return view('new_pages.retailer.services.ace_dmt.beneficiaries',compact('bank_channel','data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
        }
        
        $bank_channel = $request->get('bank_channel');
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $customer_mobile = $request->get('mobile');
        $benf_name = urlencode($request->get('benf_name'));
        $number = $request->get('number');
        $ifsc = $request->get('ifsc');
        $is_verify = $request->get('is_verify');
        $benf_mobile = urlencode($request->get('benf_mobile'));
        
        $bank = banks::find($request->get('banks'));
        $bank_id = urlencode($bank->name);
        
        $url = "https://user.payritepayment.in/api/v1/acemoney/add-beneficiary?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&name=$benf_name&account=$number&bank_id=$bank_id&ifsc=$ifsc&benf_mobile=$benf_mobile&is_verify=$is_verify&bank_channel=$bank_channel";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        
        if($decode->success){
            // print_r($decode);exit;
            
            
            $msg = $decode->message;
            Session::flash('success', $msg);
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            
            $url = "https://user.payritepayment.in/api/v1/acemoney/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            $url = "https://user.payritepayment.in/api/v1/acemoney/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&bank_channel=$bank_channel";
            $response_benf = $this->ApiCalls->payritePostCall($url);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode);exit;
            if(isset($decode_benf->data->benes)){
                $data = $decode_benf->data->benes;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            
            $banks = banks::get();
            $dmt_limit = $decode->limit;
                $dmt_use = $decode->used;
                $available = $decode->available;
                $customer_name = $decode->data->customer_name;
            return view('new_pages.retailer.services.ace_dmt.beneficiaries',compact('bank_channel','data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
            
        }else{
            // print_r($decode);exit;
            $msg = $decode->message;
            Session::flash('error', $msg);
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $customer_mobile = $request->get('mobile');
            $url = "https://user.payritepayment.in/api/v1/acemoney/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
            $response = $this->ApiCalls->payritePostCall($url);
            $decode = json_decode($response);
            $url = "https://user.payritepayment.in/api/v1/acemoney/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&bank_channel=$bank_channel";
            $response_benf = $this->ApiCalls->payritePostCall($url);
            $decode_benf = json_decode($response_benf);
            
            // print_r($decode);exit;
            if(isset($decode_benf->data->benes)){
                $data = $decode_benf->data->benes;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            
            $banks = banks::get();
            $dmt_limit = $decode->limit;
                $dmt_use = $decode->used;
                $available = $decode->available;
                $customer_name = $decode->data->customer_name;
            return view('new_pages.retailer.services.ace_dmt.beneficiaries',compact('bank_channel','data','customer_name','customer_mobile','banks','dmt_limit','dmt_use','available'));
            
        }
        
    }
    
    public function acePostDmtTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "beneficiaries" => "required",
            "transafer_type" => "required",
            "amount" => "required",
            "latitude" => "required",
            "longitude" => "required",
            "accuracy" => "required",
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
            
            Session::flash('error', $param_message);
            return redirect()->route('dashboard_retailer');
        }
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $bank_channel = $request->get('bank_channel');
        $customer_mobile = $request->get('mobile');
        $beneficiaries = $request->get('beneficiaries');
        
        $beneficiary = explode("#", $beneficiaries);
        
        $transafer_type = $request->get('transafer_type');
        $amount = $request->get('amount');
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $accuracy = $request->get('accuracy')/1000;
        $extra_string = "&account=$beneficiary[2]&beneficiary_name=".urlencode($beneficiary[1])."&ifsc=$beneficiary[3]&platform=web&bank_name=".urlencode($beneficiary[4]);
        $url = "https://user.payritepayment.in/api/v1/acemoney/do-transactions?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&beneficiary_id=$beneficiary[0]&amount=$amount&transfer_type=$transafer_type&latitude=$latitude&longitude=$longitude&accuracy=$accuracy&bank_channel=$bank_channel".$extra_string;
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        
        
        if($decode->success){
            
            $url = "https://user.payritepayment.in/api/v1/acemoney/customer-login?token=$token&customer_mobile=$customer_mobile&user_mobile=".Auth::user()->mobile."&bank_channel=$bank_channel";
            $response = $this->ApiCalls->payritePostCall($url);
            $decode_sender = json_decode($response);
            
            $banks = banks::get();
            $dmt_limit = $decode_sender->limit;
                $dmt_use = $decode_sender->used;
                $available = $decode_sender->available;
                $customer_name = $decode_sender->data->customer_name;
            
            $transafer_type = $request->get('transafer_type');
            $transaction_ids = $decode->transaction_ids;
            
            
            $txns = transactions_dmt::whereIn('id', $transaction_ids)->where('status',0)->where('eko_status',6)->get();
            Session::flash('success', $decode->message);
            return view('new_pages.retailer.services.ace_dmt.txns_otp',compact('txns','bank_channel','customer_name','customer_mobile','dmt_limit','dmt_use','available'));
            
        }else{
            
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
        
        
    }
    
    public function acePostDmtTransactionOTPSend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "transaction_id" => "required",
            "latitude" => "required",
            "longitude" => "required"
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
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $transaction_id = $request->get('transaction_id');
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $url = "https://user.payritepayment.in/api/v1/acemoney/do-transactions-otp-send?token=$token&user_mobile=".Auth::user()->mobile."&transaction_id=$transaction_id&latitude=$latitude&longitude=$longitude";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            $data = transactions_dmt::where('transaction_id',$transaction_id)->first();
            return response()->json(['success' => true, 'message' => $decode->message,'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => $decode->message]);
        }
        
    }
    
    public function acePostDmtTransactionOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "transaction_id" => "required",
            "otp" => "required",
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
        $transaction_id = $request->transaction_id;
        $check_txn = transactions_dmt::where('transaction_id', $transaction_id)->where('status',0)->where('eko_status',6)->first();
        $customer_mobile = $check_txn->mobile;
        $bank_channel = $check_txn->bank_channel;
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        
        
        $otp = $request->otp;
        $OTPReferenceID = $check_txn->otp_reference;
        $count = 1;
        $success = false;
        
        if(!$check_txn){
            
            return response()->json(['success' => false, 'message' => "Transacton Already Completed Check status In Your Report : $transaction_id"]);
        }
            
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $url = "https://user.payritepayment.in/api/v1/acemoney/do-transactions-otp-verify?token=$token&user_mobile=".Auth::user()->mobile."&customer_mobile=$customer_mobile&otp=$otp&transaction_id=$transaction_id&bank_channel=$bank_channel&OTPReferenceID=$OTPReferenceID&latitude=$latitude&longitude=$longitude";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            $transaction_id = $decode->data->transaction_id;
            $success = true;
        }
        
        if($success){
            $data = $decode->data;
            // Session::flash('dmt_success', 'Transaction Procced.');
            // Session::flash('dmt_transaction_id', $transaction_id);
            // return redirect()->route('dashboard_retailer');
            return response()->json(['success' => true, 'message' => 'Transaction Procced.','data'=>'']);
        }else{
            // print_r($decode);exit;
            return response()->json(['success' => false, 'message' => $decode->message]);
            // Session::flash('error', $decode->message);
            // return redirect()->route('dashboard_retailer');
        }
    }
    
    
    
    public function getRecharge(Request $request)
    {
        $operators = ace_operators::where('op_type',1)->where('status',1)->orderBy('name','ASC')->get();
        return view('new_pages.retailer.services.other.recharge',compact('operators'));
    }
    
    public function getDthRecharge(Request $request)
    {
        $operators = ace_operators::where('op_type',2)->where('status',1)->orderBy('name','ASC')->get();
        return view('new_pages.retailer.services.other.dth',compact('operators'));
    }
    
    public function postRecharge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "mobile" => "required",
            "op_code" => "required",
            "amount" => "required",
            "latitude" => "required",
            "longitude" => "required",
            "recharge_type" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            Session::flash('error', $param_message);
            return redirect()->route('get_recharge_retailer');
        }
        
        $mobile = $request->mobile;
        $op_code = $request->op_code;
        $amount = $request->amount;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $recharge_type = $request->recharge_type;
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $url = 'https://user.payritepayment.in/api/v1/rkwallet/recharge?token='.$token.'&user_mobile='.Auth::user()->mobile.'&recharge_mobile='.$mobile.'&op_code='.$op_code.'&amount='.$amount.'&latitude='.$latitude.'&longitude='.$longitude.'&recharge_type='.$recharge_type;
            
        $response = $this->ApiCalls->payritePostCall($url);
            
        $decode = json_decode($response);
        
        if(!isset($decode->success)){
            Session::flash('error', 'Something Wrong!');
            return redirect()->route('dashboard_retailer');
        }
        if($decode->success){
            Session::flash('success', $decode->message);
            return redirect()->route('dashboard_retailer');
        }else{
            Session::flash('error', $decode->message);
            return redirect()->route('dashboard_retailer');
        }
    }
    
    public function rechargeReport(Request $request)
    {
        return view('new_pages.retailer.report.recharge');
    }
    
    public function rechargeReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $user_id = Auth::user()->id;
        $data = transactions_recharges::select('transactions_recharges.*','ace_operators.name')
            ->join('ace_operators','ace_operators.id','transactions_recharges.op_id')
            ->where('transactions_recharges.user_id',$user_id)
            ->whereBetween('transactions_recharges.created_at', array($from, $to))
            ->orderBy('transactions_recharges.id','DESC')->get();
            
        return response()->json($data);
    }
    
    public function rechargeReportDataLast(Request $request)
    {
        
        $user_id = Auth::user()->id;
        $data = transactions_recharges::select('transactions_recharges.*','ace_operators.name')
            ->join('ace_operators','ace_operators.id','transactions_recharges.op_id')
            ->where('transactions_recharges.user_id',$user_id)
            ->where('transactions_recharges.event','PREPAID')
            ->orderBy('transactions_recharges.id','DESC')->limit(10)->get();
            
        return response()->json($data);
    }
    
    public function rechargeDthReportDataLast(Request $request)
    {
        
        $user_id = Auth::user()->id;
        $data = transactions_recharges::select('transactions_recharges.*','ace_operators.name')
            ->join('ace_operators','ace_operators.id','transactions_recharges.op_id')
            ->where('transactions_recharges.user_id',$user_id)
            ->where('transactions_recharges.event','DTH')
            ->orderBy('transactions_recharges.id','DESC')->limit(10)->get();
            
        return response()->json($data);
    }
    
    public function getBbps(Request $request)
    {
        $categories = [
            1 => 'Communication',
            2 => 'Entertainment', 
            3 => 'Finance',
            4 => 'Health',
            5 => 'Housing & Utilities',
            6 => 'Leisure',
            7 => 'Travel',
            10 => 'Others'
        ];
        $operators = bbps_categories::where('status',1)->orderBy('cat_type','ASC')->get();
        return view('new_pages.retailer.services.bbps.index',compact('operators','categories'));
    }
    
    public function getBbpsCategory($id,Request $request)
    {
        if($request->get('success')){
            Session::flash('bbps_success', 'success');
        }
        
        $operators = bbps_categories::where('status',1)->where('slug',$id)->first();
        return view('new_pages.retailer.services.bbps.bbps',compact('operators'));
    }
    
    public function postBbpsBiller(Request $request)
    {
        $operators = bbps_billers::where('biller_category_id',$request->cat_id)->where('status',1)->orderBy('biller_name','ASC')->get();
        return view('new_pages.retailer.services.bbps.biller',compact('operators'));
    }
    
    public function postBbpsBillerParam(Request $request)
    {
        $param = bbps_biller_customer_params::where('biller_id',$request->biller_id)->orderBy('id','ASC')->get();
        $biller = bbps_billers::where('biller_id',$request->biller_id)->first();
        
        return view('new_pages.retailer.services.bbps.param',compact('param','biller'));
    }
    
    public function postBbpsBillerDetail1(Request $request)
    {
        
        
        return view('new_pages.retailer.services.bbps.detail');
    }
    
    public function postBbpsReport(Request $request)
    {
        
        
        return view('new_pages.retailer.report.bbps_complaint');
    }
    
    public function postBbpsBillerDetail(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            
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
        
        $biller_id = $request->get('pay_biller_id');
        $pay_payer_mob = $request->get('pay_payer_mob');
        $pay_payer_name = $request->get('pay_payer_name');
        $param = $request->get('param');
        
        
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $data = array("user_mobile"=>Auth::user()->mobile,
                        "token"=>$token,
                        "pay_biller_id"=>$biller_id,
                        "pay_payer_mob"=>$pay_payer_mob,
                        "pay_payer_name"=>$pay_payer_name,
                        "param"=>$param);
        
        $url = "https://user.payritepayment.in/api/v1/bbps/fetch";
        $response = $this->ApiCalls->payritePostCallWithParam($url,http_build_query($data));
        //print_r($response);
        $decode = json_decode($response);
        // print_r($decode);exit;
        return view('new_pages.retailer.services.bbps.detail',compact('decode'));
    }
    
    public function bbpsReceipt(Request $request)
    {
        
        
        return view('new_pages.retailer.receipt.bbps');
    }
    
    public function getCcPayoutSender(Request $request)
    {
        return view('new_pages.retailer.services.ccpayout.index');
    }
    
    public function postCcPayoutSender(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "mobile" => "required"
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
        
        $check = CcSenders::where('mobile',$request->mobile)->WhereNotNull('sender_id')->get();
        if($check->count() != 0){
            return view('new_pages.retailer.services.ccpayout.list',compact('check'));
        }else{
            return redirect()->route('get_create_sender_retailer');
        }
        
    }
    
    public function getCreateSender(Request $request)
    {
        return view('new_pages.retailer.services.ccpayout.sender');
    }
    
    public function postCreateSender(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            "name" => "required",
            "pan" => "required",
            "aadhar" => "required",
            "mobile" => "required",
            "card_number" => "required|string|min:13|max:19",
            "cvv" => "required|string|min:3|max:4",
            "expiry" => "required|string|date_format:m/Y|after:today",
            "cc_front" => "image|mimes:jpeg,png,jpg,gif|max:5120",
            "cc_back" => "image|mimes:jpeg,png,jpg,gif|max:5120",
            "card_type" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            
            Session::flash('error', $param_message);
            return redirect()->back();
        }
        
        $check_record = CcSenders::where('mobile',$request->mobile)->where('pan',$request->pan)->where('card_number',$request->card_number)->where('expiry',$request->expiry)->where('cvv',$request->cvv)->first();
        if($check_record){
            
            Session::flash('error', 'Card Already Registred with This number.');
            return redirect()->back();
        }
            
            
            $ins = new CcSenders();
            $ins->user_id = Auth::user()->id;
            $ins->name = $request->name;
            $ins->pan = $request->pan;
            $ins->aadhar_number = $request->aadhar;
            
            $ins->card_number = $request->card_number;
            $ins->expiry = $request->expiry;
            $ins->cvv = $request->cvv;
            $ins->mobile = $request->mobile;
            $ins->card_type = $request->card_type;
            
            if ($request->hasFile('cc_front')) {
                $file = $request->file('cc_front');
                $destinationPath = public_path('/uploads/creditcard/');
                $imagename = 'CCFRONT'. Auth::user()->id . time() . '.' . $file->getClientOriginalExtension();
                // Resize and compress the image using Intervention Image
                $resizedImage = Image::load($file->getPathname())
                     ->quality(50); // 75 is the quality percentage
                // Define the storage path
                $path = $destinationPath . $imagename;
                // Save the resized and compressed image
                $resizedImage->save($path);
                // $file->move($destinationPath, $imagename);
                $ins->cc_front = $imagename;
            }
            
            if ($request->hasFile('cc_back')) {
                $file = $request->file('cc_back');
                $destinationPath = public_path('/uploads/creditcard/');
                $imagename = 'CCBACK'. Auth::user()->id . time() . '.' . $file->getClientOriginalExtension();
                // Resize and compress the image using Intervention Image
                $resizedImage = Image::load($file->getPathname())
                     ->quality(50); // 75 is the quality percentage
                // Define the storage path
                $path = $destinationPath . $imagename;
                // Save the resized and compressed image
                $resizedImage->save($path);
                // $file->move($destinationPath, $imagename);
                $ins->cc_back = $imagename;
            }
            
            $ins->save();
            $id = $ins->id;
            
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $data = array("user_mobile"=>Auth::user()->mobile,
                            "token"=>$token,
                            "name"=>$request->name,
                            "pan"=>$request->pan,
                            "aadhar"=>$request->aadhar,
                            "mobile"=>$request->mobile,
                            "card_number"=>$request->card_number,
                            "cvv"=>$request->cvv,
                            "expiry"=>$request->expiry,
                            "sender_create_id"=>$id);
            
            $url = "https://user.payritepayment.in/api/v1/bulkpe/create-sender";
            $response = $this->ApiCalls->payritePostCallWithParam($url,http_build_query($data));
            $decode = json_decode($response);
            if($decode->success){
                $sender_id = $decode->data->sender_id;
                return redirect()->route('get_benf_retailer', $sender_id);
            }else{
                Session::flash('error', $decode->message);
                return redirect()->back();
            }
            
        
    }
    
    public function getCcPayoutBenf($sender_id)
    {
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $url = "https://user.payritepayment.in/api/v1/bulkpe/get-beneficiaries?token=$token&user_mobile=".Auth::user()->mobile."&sender_id=$sender_id";
        $response_benf = $this->ApiCalls->payritePostCall($url);
        $decode_benf = json_decode($response_benf);
        $data = $decode_benf->data;
        $banks = banks::get();
        return view('new_pages.retailer.services.ccpayout.beneficiaries',compact('data','sender_id','banks'));
    }
    
    public function postAddBenf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            
            "benf_name" => "required",
            "number" => "required",
            "ifsc" => "required",
            "sender_id" => "required|exists:cc_senders,sender_id",
            "is_verify" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            Session::flash('error', $param_message);
            

            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $sender_id = $request->get('sender_id');
            return redirect()->route('get_benf_retailer', $sender_id);
        }
        
        $sender_id = $request->get('sender_id');
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $benf_name = urlencode($request->get('benf_name'));
        $number = $request->get('number');
        $ifsc = $request->get('ifsc');
        $is_verify = $request->get('is_verify');
        $benf_mobile = urlencode($request->get('benf_mobile'));
        
        $bank = banks::find($request->get('banks'));
        $bank_id = urlencode($bank->name);
        
        $url = "https://user.payritepayment.in/api/v1/bulkpe/add-beneficiary?token=$token&user_mobile=".Auth::user()->mobile."&sender_id=$sender_id&name=$benf_name&account=$number&ifsc=$ifsc";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        
        if($decode->success){
            // print_r($decode);exit;
            
            
            $msg = $decode->message;
            Session::flash('success', $msg);
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $sender_id = $request->get('sender_id');
            return redirect()->route('get_benf_retailer', $sender_id);
            
        }else{
            // print_r($decode);exit;
            $msg = $decode->message;
            Session::flash('error', $msg);
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $sender_id = $request->get('sender_id');
            return redirect()->route('get_benf_retailer', $sender_id);
            
        }
        
    }
    
    public function postDoTransactions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "longitude" => "required",
            "latitude" => "required",
            "amount" => "required",
            "sender_id" => "required|exists:cc_senders,sender_id",
            "beneficiary_id" => "required|exists:cc_beneficiaries,beneficiary_id",
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
            Session::flash('error', $param_message);
            

            
            $sender_id = $request->get('sender_id');
            return redirect()->route('get_benf_retailer', $sender_id);
        }
        
        $sender_id = $request->get('sender_id');
        $beneficiary_id = $request->get('beneficiary_id');
        $amount = $request->get('amount');
        $longitude = $request->get('longitude');
        $latitude = $request->get('latitude');
        $transfer_type = $request->get('transfer_type');
        $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
        $url = "https://user.payritepayment.in/api/v1/bulkpe/do-transactions?token=$token&user_mobile=".Auth::user()->mobile."&sender_id=$sender_id&beneficiary_id=$beneficiary_id&amount=$amount&latitude=$latitude&longitude=$longitude&transfer_type=$transfer_type&platform=web";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        
        if($decode->success){
            // print_r($decode);exit;
            
            
            $collectionUrl = $decode->data->data->collectionUrl;
            log::info('Redurect URL');
            log::info($collectionUrl);
            return redirect($collectionUrl);
            
        }else{
            // print_r($decode);exit;
            $msg = $decode->message;
            Session::flash('error', $msg);
            $token = $this->UserAuth->createAuthUserToken(Auth::user()->mobile);
            $sender_id = $request->get('sender_id');
            return redirect()->route('get_benf_retailer', $sender_id);
            
        }
    }
    
    public function ccpayoutReport(Request $request)
    {
        return view('new_pages.retailer.report.ccpayout');
    }
    
    public function postCcpayoutReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $user_id = Auth::user()->id;
        $data = transactions_cc::with('senders','beneficiaries')
            ->where('user_id',$user_id)
            ->whereBetween('created_at', array($from, $to))
            ->orderBy('id','DESC')->get();
            
        return response()->json($data);
    }
    
    //private
    private function toCsvExport($sims,$filename)
    {
		
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename='.$filename,
            'Expires' => '0',
            'Pragma' => 'public'
        ];
		
        array_unshift($sims, array_keys($this->flattenArray($sims[0])));
		
        $callback = function () use ($sims) {
            $resource = fopen('php://output', 'w');
            foreach ($sims as $sim) {
				//Log::info($sim);
                fputcsv($resource, $sim);
            }
            fclose($resource);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    private function flattenArray(array $array)
    {
        $return = array();
        array_walk_recursive($array, function ($a, $b) use (&$return) {
            $return[$b] = $a;
        });
        return $return;
    }
    
    
}
