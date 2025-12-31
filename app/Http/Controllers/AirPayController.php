<?php

namespace App\Http\Controllers;
use App\Classes\UserAuth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Dipesh79\LaravelPhonePe\LaravelPhonePe;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use DataTables;
use Log;
use Mail;
use Auth;
use DB;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Simple\Manager;

use App\Models\User;
use App\Models\fund_onlines;

class AirPayController extends Controller
{
    public function __construct(UserAuth $Auth) {
        // $this->middleware('auth');
        
        $this->UserAuth = $Auth;
    }
    
    public function calculateChecksum($data, $secret_key) {
		$checksum = md5($data.$secret_key);
		
		return $checksum;
	}

    public function encrypt($data, $salt) {
        // Build a 256-bit $key which is a SHA256 hash of $salt and $password.
        $key = hash('SHA256', $salt.'@'.$data);
        return $key;
    }	
	

	public function encryptSha256($data) {   
		$key = hash('SHA256', $data);
		return $key;
	}    
	public function calculateChecksumSha256($data, $salt) { 
		// print($data);
		// exit;
		$checksum = hash('SHA256', $salt.'@'.$data);
		Log::info($checksum);
		return $checksum;
	}

	
    public function outputForm($checksum) {
		//ksort($_POST);
		foreach($_POST as $key => $value) {
				echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />'."\n";
		}
		echo '<input type="hidden" name="checksum" value="'.$checksum.'" />'."\n";
		
		
	}

    public function verifyChecksum($checksum, $all, $secret) {
		$cal_checksum = $this->calculateChecksum($secret, $all);
		
		$bool = 0;
		if($checksum == $cal_checksum)	{
			$bool = 1;
		}

		return $bool;
	}
	
	public function responseAirpay(Request $request)
    {
        
        Log::channel("airpay")->info($request);
        if($request->TRANSACTIONPAYMENTSTATUS == 'SUCCESS'){
            $transaction_id = $request->TRANSACTIONID;
            $amount = $request->AMOUNT;
            $refid = $request->ap_SecureHash;
            $chmod = $request->CHMOD;
            $pg_ref_id = $request->APTRANSACTIONID;
            
            $cardtypes = array("pgcc","pgdc","pg");
            $issuer = "";
            $card_number = "";
            $card_type = "";
            $slab = $chmod;
            if(in_array($chmod, $cardtypes)){
                $issuer = $request->CARDISSUER;
                $card_number = $request->CARD_NUMBER;
                $card_type = $request->CARDTYPE;
                $slab = $slab.$issuer;
            }
            
            if($chmod == 'ppc'){
                $issuer = $request->BANKNAME;
            }
            $find = fund_onlines::where('transaction_id',$transaction_id)->where('status',0)->first();
            $amount_main = $find->amount;
            $fee_type = 1;
            
            switch ($slab) {
                //PG + VISA
                case "pgvisa":
                    if($card_type == 'Credit'){
                        $fee = 2.85;
                    }else{
                        if($amount_main <= 1999){
                            $fee = 2.85;
                        }else{
                            $fee = 2.85;
                        }
                        
                    }
                    break;
                //PG + MASTER    
                case "pgmastercard":
                    if($card_type == 'Credit'){
                        $fee = 2.85;
                    }else{
                        if($amount_main <= 1999){
                            $fee = 2.85;
                        }else{
                            $fee = 2.85;
                        }
                    }
                    break;
                //PG + RUPAY    
                case "pgrupay":
                    if($card_type == 'Credit'){
                        $fee = 3;
                    }else{
                        $fee = 1.25;
                    }
                    break;
                //NETBANKING
                case "nb":
                    $fee = 35;
                    $fee_type = 0;
                    break;
                //PREPAID
                case "ppc":
                    $fee = 1.45;
                    break;
                //UPI
                case "upi":
                    $fee = 1.20;
                    break;
                //PAY LATER
                case "payltr":
                    $fee = 3.15;
                    break;
                //EMI
                case "emi":
                    $fee = 3.15;
                    break;
                default:
                    if($card_type == 'Credit'){
                        $fee = 4;
                    }else{
                        $fee = 1.45;
                    }
                    break;
            }
            
            
            balancematch1:
            $userwallet = User::find($find->user_id);
            $wallet = $userwallet->wallet;
            
            if($fee_type == 1){
                $fee_val = $amount_main * $fee / 100;
            }else{
                $fee_val = $fee;
            }
            
            $amount = $amount_main - $fee_val;
            
            $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
            if($balance_check){
                $balance_check_amt = $balance_check->balance;
            }else{
                $balance_check_amt = 0;
            }
            if($balance_check_amt != $userwallet->wallet->balance){
                    Log::channel("balancemissmatch")->info("========");
                    Log::channel("balancemissmatch")->info($wallet->id);
                    Log::channel("balancemissmatch")->info($balance_check->balance);
                    Log::channel("balancemissmatch")->info($userwallet->wallet->balance);
                    goto balancematch1;
            }
                    
            $txn_wl = $wallet->depositFloat($amount_main,[
                'meta' => [
                    'Title' => 'Online Load Airpay',
                    'detail' => 'Credit_AirPay_'.$refid.'_Amount_'.$amount_main,
                    'transaction_id' => $transaction_id,
                ]
            ]);
            $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
            
            $txn_fee = $wallet->withdrawFloat($fee_val,[
                        'meta' => [
                            'Title' => 'Online Load Fee Airpay',
                            'detail' => 'Debit_AirPay_'.$refid.'_Fee_Amount_'.$fee_val,
                            'transaction_id' => $transaction_id,
                        ]
                    ]);
            $balance_update = Transaction::where('uuid', $txn_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    
            $find->amount_txn = $amount;
            $find->fee = $fee_val;
            $find->ref_id = $refid;
            $find->closing_balance = $wallet->balanceFloat;
            $find->status = 1;
            $find->wallets_uuid = $txn_wl->uuid;
            $find->card_issuer = $issuer;
            $find->card_number = $card_number;
            $find->card_type = $card_type;
            
            $find->save();
            
            Auth::loginUsingId($find->user_id);
            Session::flash('success', 'Payment Is Completed.');
            if(Auth::user()->user_type == 2){
                return redirect()->route('dashboard_retailer');
            }else{
                return redirect()->route('dashboard_distributor');
            }
        }else{
            
            $transaction_id = $request->TRANSACTIONID;
            $find = fund_onlines::where('transaction_id',$transaction_id)->where('status',0)->first();
            Auth::loginUsingId($find->user_id);
            if(isset($request->REASON)){
                $msg = $request->REASON;
            }else{
                $msg = $request->MESSAGE;
            }
            Session::flash('error', $msg);
            if(Auth::user()->user_type == 2){
                return redirect()->route('dashboard_retailer');
            }else{
                return redirect()->route('dashboard_distributor');
            }
            
        }
        
    }
}
