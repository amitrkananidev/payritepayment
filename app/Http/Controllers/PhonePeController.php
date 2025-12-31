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

use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Simple\Manager;

use App\Models\fund_onlines;

class PhonePeController extends Controller
{
    public function __construct(UserAuth $Auth) {
        $this->middleware('auth');
        
        $this->UserAuth = $Auth;
    }
    
    public function initiatePayment(Request $request)
    {
        $amount = $request->input('amount');
        // $transactionId = uniqid(); // Generate unique transaction ID
        $transactionId = $this->UserAuth->txnId('PHN');
        $ins = new fund_onlines();
        $ins->user_id = Auth::user()->id;
        $ins->transaction_id = $transactionId;
        $ins->amount = $amount;
        $ins->save();
        $phonepe = new LaravelPhonePe();
        //amount, phone number, callback url, unique merchant transaction id
        $url = $phonepe->makePayment($amount, '8140666688', route('main-page'),$transactionId);
        return redirect()->away($url);
    }
    
    

    public function paymentCallback(Request $request)
    {
        $data = $request->all();
        

        return redirect()->route('main-page');
    }
}
