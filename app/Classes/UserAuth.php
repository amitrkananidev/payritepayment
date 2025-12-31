<?php

namespace App\Classes;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Auth;
use DB;

use App\Models\User;
use App\Models\kyc_docs;
use App\Models\transactions_dmt;
use App\Models\transactions_aeps;
use App\Models\fund_requests;
use App\Models\fund_onlines;
use App\Models\dmt_ba_refids;
use App\Models\user_login_tokens;

class UserAuth
{
    public function partitionAmount($amount){
        $partition_size = 5000;
        $partitions = [];
    
        while ($amount > $partition_size) {
            $partitions[] = $partition_size;
            $amount -= $partition_size;
        }
    
        // Add remaining amount if any
        if ($amount > 0) {
            $partitions[] = $amount;
        }
    
        return $partitions;
    }
    
    public function partitionAmountTest($amount){
        $partition_size = 100;
        $partitions = [];
    
        while ($amount > $partition_size) {
            $partitions[] = $partition_size;
            $amount -= $partition_size;
        }
    
        // Add remaining amount if any
        if ($amount > 0) {
            $partitions[] = $amount;
        }
    
        return $partitions;
    }
    
    public function geteDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);
    
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
    
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }
    
    public function authUserToken($mobile,$token) {
        $user = User::where('mobile',$mobile)->first();
        $check_token = user_login_tokens::where('user_id',$user->id)->where('token',$token)->first();
        if($check_token){
            return 1;
        }else{
            return 0;
        }
    }
    
    public function authUserTokenOld($mobile,$token) {
        $currentDate = app('currentDate');
        $password = 'P@yr!t'.$currentDate->format('Y-m-d');
        // $password = "Pyr!$@";
        $current = Carbon::now();
		$previous = (clone $current)->subMinute();
		$date = $current->format('dmyHi');//date("dmyHi");
		$date_previous = $previous->format('dmyHi');
		
        // $date = date("dmyHi");
        
        $plainPassword = $password.'#'.$mobile.'#'.$date;
        $plainPassword_previous = $password.'#'.$mobile.'#'.$date_previous;
        
        if (Hash::check($plainPassword, $token) || Hash::check($plainPassword_previous, $token)) {
            // Passwords match
            return 1;
        } else {
            // Passwords do not match;
            Log::info("==================================================");
            Log::info($plainPassword);
            Log::info($plainPassword_previous);
            Log::info($token);
            return 0;
        
        }
    }
    public function createAuthUserToken($mobile) {
        // $currentDate = app('currentDate');
        // $password = 'P@yr!t'.$currentDate->format('Y-m-d');
        // // $password = "Pyr!$@";
        // $date = date("dmyHi");
        
        // $plainPassword = $password.'#'.$mobile.'#'.$date;
        
        // return bcrypt($plainPassword);
        
        $user = User::where('mobile',$mobile)->first();
        
        $check_token = user_login_tokens::where('user_id',$user->id)->count();
        if($check_token == 0){
            $token = $this->createToken();
                
            $ins_token = new user_login_tokens();
            $ins_token->user_id = $user->id;
            $ins_token->token = $token;
            $ins_token->save();
            
        }
                
        $login_token = user_login_tokens::where('user_id',$user->id)->first();        
        return $login_token->token;
        
    }
    
    public function getUserToken() {
        // Produces something like "2019-03-11 12:25:00"
        $current_date_time = Carbon::now()->toDateTimeString();
        return bcrypt($current_date_time);
    }
    
    public function getUserId($prifix) {
        
        $txn = rand(100000, 999999);
        $txn_id = strtoupper($prifix . date('ymd') . $txn);
        
        $check_trans = User::where('user_id',$txn_id)->first();
        
        if($check_trans) {
            return $this->getUserId($prifix=null);
        }
        else {
            return $txn_id;
        }
    }

    public function getPassword($length_of_string) {
    	$str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    	// $str_result = '0123456789';
    	return substr(str_shuffle($str_result),0, $length_of_string);
    }

    public function getOTP() {
        return rand(100000, 999999);
    }
    
    // public function txnId($prifix=null) {

    //     $day = now()->format('D');
    //     $txn = rand(100000, 999999);
    //     $txn_id = strtoupper($day . $prifix . date('ymd') . $txn);

    //     $check_trans = Transactions::where('transaction_id',$txn_id)->first();
    //     $check_money_request = MoneyRequest::where('transaction_id',$txn_id)->first();

    //     if($check_trans || $check_money_request) {
    //         return $this->txnId($prifix=null);
    //     }
    //     else {
    //         return $txn_id;
    //     }
    // }
    
    public function createToken() {

        $day = now()->format('D');
        $txn = rand(1000000, 9999999);
        $txn_id = strtoupper($day . date('ymdHi') . $txn);
        
        return bcrypt($txn_id);
    }

    public function verifyUserToken($user_id,$usertoken) {
        $check = User::where('id',$user_id)->where('user_token',$usertoken)->where('status',1)->first();
        if($check)
            return true;
        else
            return false;
    }
    
    public function getUserProfile($user_id) {
        
        // $baseurl = env('IMAGE_URL').'uploads/qrcodes/';
        // $qr_img = DB::raw("CONCAT('$baseurl',users.qr_img) AS qr_img");
        
        $baseurl2 = env('IMAGE_URL').'uploads/profile_pics/';
        $profile_pic = DB::raw("CONCAT('$baseurl2',users.profile_pic) AS profile_pic");
                
        $data = User::select('users.*',
        'cities.name as city_name','states.name as state_name',
        'addresses.address','addresses.pincode')
        ->leftjoin('addresses','addresses.user_id','users.id')
        ->leftjoin('cities','cities.id','addresses.city_id')
        ->leftjoin('states','states.id','addresses.state_id')
        ->where('users.id',$user_id)
        ->first();
        
        $kycdocs = kyc_docs::where('user_id',$user_id)->first();
        if($kycdocs)
            $kyc_status=$kycdocs->status;
        else
            $kyc_status=3;
            
        // $upi_collections = Transactions::where('user_id',$user_id)->where('event','UPICREDIT')->where('status',1)
        // ->whereDate('created_at', Carbon::today())->sum('amount');
        
        // $va_collections = Transactions::where('user_id',$user_id)->where('event','CREDITVA')->where('status',1)
        // ->whereDate('created_at', Carbon::today())->sum('amount');
        
        // $money_transfers = Transactions::where('user_id',$user_id)->whereIn('event',['QUICKUPI','QUICKDMT'])->where('status',1)
        // ->whereDate('created_at', Carbon::today())->sum('amount');
        
        $aeps_txns = 0;
            
        $data['kyc_status'] = $kyc_status; 
        $data['dmt_active'] = 1; 
        
        // $data['today_upi_collections_total'] = number_format($upi_collections,2); 
        // $data['today_va_collections_total'] = number_format($va_collections,2); 
        // $data['today_money_transfers_total'] = number_format($money_transfers,2); 
        // $data['today_aeps_txns_total'] = number_format($aeps_txns,2); 
        // $data['settlement_charge'] = env('SETTLEMENT_CHARGE'); 
        // $data['payu_charge'] = env('PAYU_CHARGE'); 
        // $data['retailer_activation_fee'] = env('RETAILER_ACTIVATION_FEE');
        // $data['topup_net_banking_charge'] = env('TOPUP_NET_BANKING_CHARGE');
        // $data['topup_other_charge'] = env('TOPUP_OTHER_CHARGE');
        
        return $data;
    }
    
    public function txnId($prifix=null) {

        $day = now()->format('D');
        $txn = rand(100000, 999999);
        $txn_id = strtoupper($day . $prifix . date('ymdHis') . $txn);
        
        $check_trans = transactions_dmt::where('transaction_id',$txn_id)->first();
        $check_trans_aeps = transactions_aeps::where('transaction_id',$txn_id)->first();
        $check_trans_fund_requests = fund_requests::where('transaction_id',$txn_id)->first();
        $check_trans_fund_onlines = fund_onlines::where('transaction_id',$txn_id)->first();
        
        if($check_trans || $check_trans_aeps || $check_trans_fund_requests || $check_trans_fund_onlines) {
            return $this->txnId($prifix=null);
        }
        else {
            return $txn_id;
        }
    }
    
    public function txnIdBa($prifix=null) {

        $day = now()->format('D');
        $txn = rand(100, 999);
        $txn_id = strtoupper($prifix . date('ymdHis') . $txn);
        $check = dmt_ba_refids::where('ref_id',$txn_id)->first();
        if($check){
            return $this->txnIdBa($prifix=null);
        }
        
        $ins = new dmt_ba_refids();
        $ins->ref_id = $txn_id;
        $ins->save();
        
        return $txn_id;
    }
    
    public function txnIdPlutos($prifix=null) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $result = '';
        for ($i = 0; $i < 26; $i++) {
            $result .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        $julianDate = now()->format('yz');
        $time = now()->format('Hi');
        
        $txn_id = strtoupper($result . $julianDate . $time);
        $check = dmt_ba_refids::where('ref_id',$txn_id)->first();
        if($check){
            return $this->txnIdBa($prifix=null);
        }
        
        $ins = new dmt_ba_refids();
        $ins->ref_id = $txn_id;
        $ins->save();
        
        return $txn_id;
    }
    
    public function fetchFromDate($sdate) {
        return date('Y-m-d'. ' 00:00:00', strtotime($sdate));
    }
    
    public function fetchToDate($edate) {
        return date('Y-m-d'. ' 23:59:59', strtotime($edate));
    }
    
    public function calculatePercentageChange($lastMonthTotal, $thisMonthTotal)
    {
        if ($lastMonthTotal == 0) {
            return $thisMonthTotal == 0 ? 0 : 100; // Handle division by zero if last month total is zero
        }

        // Calculate percentage change
        $change = $thisMonthTotal - $lastMonthTotal;
        $percentageChange = ($change / abs($lastMonthTotal)) * 100;

        return round($percentageChange,2);
    }
    
    public function getCustomerUse($mobile){
        // Fetch current month and year
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $transactions = transactions_dmt::whereIn('status',[1,0])->where('event','DMT')->where('api_id','0')->where('mobile',$mobile)->whereMonth('created_at', $currentMonth)
                      ->whereYear('created_at', $currentYear)->sum('amount');
        
        return $transactions;
    }
}
?>