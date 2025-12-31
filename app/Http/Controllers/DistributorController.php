<?php

namespace App\Http\Controllers;
use App\Classes\UserAuth;
use App\Classes\ApiCalls;
use App\Classes\WalletCalculation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use DataTables;
// use Excel;
use Mail;
use Auth;
use DB;
use PDF;
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
use App\Models\transactions;
use App\Models\transactions_dmt;
use App\Models\transactions_aeps;
use App\Models\cities;
use App\Models\states;
use App\Models\fund_banks;
use App\Models\fund_requests;
use App\Models\fund_onlines;
use App\Models\fund_ods;
use App\Models\fund_od_reverses;

class DistributorController extends Controller
{
    public function __construct(UserAuth $Auth, ApiCalls $ApiCalls, WalletCalculation $WalletCalculation) {
        $this->middleware('auth');
        
        $this->UserAuth = $Auth;
        $this->ApiCalls = $ApiCalls;
        $this->WalletCalculation = $WalletCalculation;
    }
    
    public function index()
    {
        $total_retailer = User::select('users.name')->join('user_levels','user_levels.user_id','users.id')->where('user_levels.toplevel_id',Auth::user()->id)->where('users.user_type','2')->get()->count();
        $toal_pending_req = fund_requests::join('user_levels','user_levels.user_id','fund_requests.user_id')->where('user_levels.toplevel_id',Auth::user()->id)->where('fund_requests.status','0')->get()->count();
        $toal_accept_req = fund_requests::join('user_levels','user_levels.user_id','fund_requests.user_id')->where('user_levels.toplevel_id',Auth::user()->id)->where('fund_requests.status','1')->get()->count();
        $toal_reject_req = fund_requests::join('user_levels','user_levels.user_id','fund_requests.user_id')->where('user_levels.toplevel_id',Auth::user()->id)->where('fund_requests.status','2')->get()->count();
        $toal_req = fund_requests::join('user_levels','user_levels.user_id','fund_requests.user_id')->where('user_levels.toplevel_id',Auth::user()->id)->get()->count();
        $toal_od = fund_ods::join('user_levels','user_levels.user_id','fund_ods.user_id')->where('user_levels.toplevel_id',Auth::user()->id)->where('fund_ods.status','1')->where('fund_ods.is_od',1)->get()->sum('amount');
        
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
            $transactions = transactions_dmt::join('user_levels','user_levels.user_id','transactions_dmts.user_id')->where('user_levels.toplevel_id',Auth::user()->id)->where('transactions_dmts.status','1')->whereDate('transactions_dmts.created_at', $currentDate->toDateString())->get();
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
            $transactions = transactions_dmt::join('user_levels','user_levels.user_id','transactions_dmts.user_id')->where('user_levels.toplevel_id',Auth::user()->id)->where('transactions_dmts.status','1')->whereDate('transactions_dmts.created_at', $currentDate->toDateString())->get();
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
        
        
        
        return view('new_pages.distributor.home',compact('toal_accept_req','toal_reject_req','toal_req','total_retailer','toal_pending_req','toal_od','lastWeekTransactions','lastWeekday','currentWeekTransactions','thisweek_dmt_total'));
    }
    
    public function getTreansactionId(Request $request)
    {
        return $this->UserAuth->getUserId($request->get("prifix"));
    }
    
    public function getCity(Request $request){
        $city = cities::where('state_id',$request->state)->get();
        $html = "";
        foreach($city as $r){
            $html .= "<option value='$r->id'>$r->name</option>"; 
        }
        
        return $html;
    }
    
    public function createRetailer()
    {
        
        $states = states::get();
        return view('new_pages.distributor.retailer.create',compact('states'));
    }
    
    public function viewRetailer()
    {
        $states = states::get();
        return view('new_pages.distributor.retailer.view');
    }
    
    public function viewRetailerData(Request $request)
    {
        $data = User::with('wallet')->select('users.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"))
                ->join('user_levels','user_levels.user_id','users.id')
                ->where('user_levels.toplevel_id',Auth::user()->id)
                ->where('users.user_type','2')->get();
        
        return response()->json($data);
    }
    
    public function postCreateRetailer(Request $request)
    {
        $check_user = User::where('mobile',$request->mobile)->where('status','0')->first();
        if($check_user){
            $mobile = $check_user->mobile;
            $name = $check_user->name." ".$check_user->surname;
            $otp = rand(100000, 999999);
            $check_user->otp = $otp;
            $check_user->save();
            $text = urlencode("Action Required: Use OTP $otp to verify and accept Payrite Payment Partner agreement of $name account within 10 minutes. https://payritepayment.in/terms_conditions.php ");
            $api_response = $this->ApiCalls->smsgatewayhubGetCall($mobile,$text,1207171774902713997);
            
            Session::flash('success', 'OTP Send On Your Mobile');
            return view('new_pages.distributor.retailer.otp',compact('mobile')); 
        }
        $minDateOfBirth = Carbon::now()->subYears(18)->toDateString();
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "mobile" => "required|unique:users,mobile",
            "surname" => "required",
            "email" => "required|unique:users,email",
            "dob" => "required|date|before:$minDateOfBirth",
            "address" => "required",
            "pincode" => "required",
            "states" => "required",
            "city" => "required",
            "pan_number" => "required|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/|unique:kyc_docs,pan_number",
            "aadhar_number" => "required|unique:kyc_docs,aadhaar_number",
            "pan_image" => "required",
            "aadhaar_front_image" => "required",
            "aadhaar_back_image" => "required",
            "shop_name" => "required",
            "shop_address" => "required",
            "shop_image" => "required",
            "selfie" => "required",
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
            return redirect()->back()->withInput();
        }
        $passwprd = $this->UserAuth->getPassword(8);
        $retailer_mobile = $request->mobile;
        
        $mobile = $request->mobile;
        $name = $request->name." ".$request->surname;
        $otp = rand(100000, 999999);
        
        $ins = new User();
        $ins->user_id = $this->UserAuth->getUserId("RT");
        $ins->name = $request->name;
        $ins->surname = $request->surname;
        $ins->email = $request->email;
        $ins->mobile = $request->mobile;
        $ins->dob = $request->dob;
        $ins->otp = $otp;
        $ins->user_type = 2;
        $ins->status = 0;
        $ins->password = bcrypt($passwprd);
        $ins->user_token = bcrypt(date('ymd').rand(100000, 999999));
        $ins->save();
        
        $insadd = new Addresses();
        $insadd->user_id = $ins->id;
        $insadd->address = $request->address;
        $insadd->pincode = $request->pincode;
        $insadd->city_id = $request->city;
        $insadd->state_id = $request->states;
        $insadd->save();
        
        $insshop = new shop_details();
        $insshop->user_id = $ins->id;
        $insshop->shop_name = $request->shop_name;
        $insshop->shop_address = $request->shop_address;
        $insshop->latitude = $request->latitude;
        $insshop->longitude = $request->longitude;
        if ($request->hasFile('shop_image')) {
            $file = $request->file('shop_image');
            $destinationPath = public_path('/uploads/shop/');
            $imagename = 'SHOP'. $ins->id . time() . '.' . $file->getClientOriginalExtension();
            
            // Resize and compress the image using Intervention Image
            $resizedImage = Image::load($file->getPathname())
                 ->quality(50); // 75 is the quality percentage
            // Define the storage path
            $path = $destinationPath . $imagename;
            // Save the resized and compressed image
            $resizedImage->save($path);
            // $file->move($destinationPath, $imagename);
            
            $insshop->shop_img = $imagename;
        }
        if ($request->hasFile('selfie')) {
            $file = $request->file('selfie');
            $destinationPath = public_path('/uploads/selfie/');
            $imagename = 'SELFIE'. $ins->id . time() . '.' . $file->getClientOriginalExtension();
            // Resize and compress the image using Intervention Image
            $resizedImage = Image::load($file->getPathname())
                 ->quality(50); // 75 is the quality percentage
            // Define the storage path
            $path = $destinationPath . $imagename;
            // Save the resized and compressed image
            $resizedImage->save($path);
            // $file->move($destinationPath, $imagename);
            $insshop->selfie = $imagename;
        }
        $insshop->save();
        
        $kyc = new kyc_docs();
        $kyc->user_id = $ins->id;
        $kyc->pan_number = strtoupper($request->get('pan_number'));
        $kyc->aadhaar_number = $request->get('aadhar_number');
        $kyc->status = 1;
        
        if ($request->hasFile('pan_image')) {
            $file = $request->file('pan_image');
            $destinationPath = public_path('/uploads/kycdocs/');
            $imagename = 'PAN'. $ins->id . time() . '.' . $file->getClientOriginalExtension();
            // Resize and compress the image using Intervention Image
            $resizedImage = Image::load($file->getPathname())
                 ->quality(50); // 75 is the quality percentage
            // Define the storage path
            $path = $destinationPath . $imagename;
            // Save the resized and compressed image
            $resizedImage->save($path);
            // $file->move($destinationPath, $imagename);
            $kyc->pan_image = $imagename;
        }
        if ($request->hasFile('aadhaar_front_image')) {
            $file = $request->file('aadhaar_front_image');
            $destinationPath = public_path('/uploads/kycdocs/');
            $imagename = 'ADHARF'. $ins->id . time() . '.' . $file->getClientOriginalExtension();
            // Resize and compress the image using Intervention Image
            $resizedImage = Image::load($file->getPathname())
                 ->quality(50); // 75 is the quality percentage
            // Define the storage path
            $path = $destinationPath . $imagename;
            // Save the resized and compressed image
            $resizedImage->save($path);
            // $file->move($destinationPath, $imagename);
            $kyc->aadhaar_front_image = $imagename;
        }
        if($request->hasFile('aadhaar_back_image')) {
            $file = $request->file('aadhaar_back_image');
            $destinationPath = public_path('/uploads/kycdocs/');
            $imagename = 'ADHARB'. $ins->id . time() . '.' . $file->getClientOriginalExtension();
            // Resize and compress the image using Intervention Image
            $resizedImage = Image::load($file->getPathname())
                 ->quality(50); // 75 is the quality percentage
            // Define the storage path
            $path = $destinationPath . $imagename;
            // Save the resized and compressed image
            $resizedImage->save($path);
            // $file->move($destinationPath, $imagename);
            $kyc->aadhaar_back_image = $imagename;
        }
        $kyc->save();
        
        $ins_level = new user_levels();
        $ins_level->user_id = $ins->id;
        $ins_level->toplevel_id = Auth::user()->id;
        $ins_level->save();
        
        $wallet = Wallet::create(['user_id' => $ins->id,'holder_id' => $ins->id]);
        
        $text = urlencode("Action Required: Use OTP $otp to verify and accept Payrite Payment Partner agreement of $name account within 10 minutes. https://payritepayment.in/terms_conditions.php ");
        $api_response = $this->ApiCalls->smsgatewayhubGetCall($mobile,$text,1207171774902713997);
        
        Session::flash('success', 'OTP Send On Your Mobile');
        return view('new_pages.distributor.retailer.otp',compact('mobile'));
        
    }
    
    public function postCreateRetailerOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "mobile" => "required|exists:users,mobile",
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
            return redirect()->back()->withInput();
        }
        
        $user = User::where('mobile',$request->mobile)->first();
        if($user){
            $mobile = $request->mobile;
            if($user->otp == $request->otp){
                $passwprd = $this->UserAuth->getPassword(8);
                $user->password = bcrypt($passwprd);
                $user->status = 1 ;
                $user->save();
                
                $shop = shop_details::where('user_id',$user->id)->first();
                
                // Define the data to pass to the view
                $data = [
                    'shop_name' => $shop->shop_name,
                    'name' => $user->name." ".$user->surname,
                    'email' => $user->email,
                    'address' => $shop->shop_address,
                    'mobile' => $user->mobile,
                    'place' => 'Rajkot',
                    'territory' => 'Rajkot, Gujarat'
                ];
        
                // Load the Blade view and generate the PDF
                $pdf = PDF::loadView('pdf.myPDF', $data);
        
                // Define the file path and name
                $filePath = public_path('Agreement/agreement_'.$user->user_id.'.pdf');
        
                // Ensure the directory exists
                if (!file_exists(dirname($filePath))) {
                    mkdir(dirname($filePath), 0777, true);
                }
        
                // Save the PDF to the specified path
                $pdf->save($filePath);
                
                $text = urlencode("Your account at Payrite has been successfully created! Your login id $mobile and password $passwprd. https://payritepayment.in/");
                $api_response = $this->ApiCalls->smsgatewayhubGetCall($mobile,$text,1207170972078183783);
        
                Session::flash('success', 'Agreement Created.');
                return redirect()->route('dashboard_distributor');
            }else{
                Session::flash('error', 'OTP Not Match.');
                return view('new_pages.distributor.retailer.otp',compact('mobile'));
            }
        }else{
            Session::flash('error', 'User Not FOund');
            return redirect()->route('dashboard_distributor');
        }
    }
    
    public function rekycRetailer(Request $request)
    {
        $data = User::select('users.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"))
                ->join('user_levels','user_levels.user_id','users.id')
                ->join('eko_services','eko_services.user_id','users.id')
                ->where('user_levels.toplevel_id',Auth::user()->id)
                ->where('users.user_type','2')
                ->where('eko_services.eko_status','4')
                ->where('eko_services.eko_aeps','3')
                ->get();
        
        return view('new_pages.distributor.retailer.rekyc', compact('data'));
    }
    
    public function getUserDetails(Request $request)
    {
        $ins = User::select('users.name','users.surname','kyc_docs.pan_number','kyc_docs.aadhaar_number')
                ->join('kyc_docs','kyc_docs.user_id','users.id')->where("mobile",$request->get("mobile"))->first();
        return response()->json(["name"=>$ins->name,"surname"=>$ins->surname,"pan_number"=>$ins->pan_number,"aadhaar_number"=>$ins->aadhaar_number]);
    }
    
    public function postKycDocument(Request $request)
    {
        $data = User::select('kyc_docs.pan_image','kyc_docs.aadhaar_front_image','kyc_docs.aadhaar_back_image')
                ->join('kyc_docs','kyc_docs.user_id','users.id')->where("mobile",$request->get("mobile"))->first();
                // print_r($data);exit;
        return view('new_pages.distributor.retailer.model.kyc_document_body', compact('data'));
    }
    
    public function postRekycRetailer(Request $request)
    {
        $minDateOfBirth = Carbon::now()->subYears(18)->toDateString();
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "mobile" => "required|exists:users,mobile",
            "surname" => "required",
            "pan_number" => "required|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/",
            "aadhar_number" => "required",
            "pan_image" => "required",
            "aadhaar_front_image" => "required",
            "aadhaar_back_image" => "required",
            "onboarding_proccess" => "required",
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
        
        $ins = User::where("mobile",$request->get("mobile"))->first();
        $ins->name = $request->get("name");
        $ins->surname = $request->get("surname");
        $ins->save();
        
        $kyc = kyc_docs::where('user_id',$ins->id)->first();
        $kyc->pan_number = strtoupper($request->get('pan_number'));
        $kyc->aadhaar_number = $request->get('aadhar_number');
        $kyc->status = 1;
        
        if ($request->hasFile('pan_image')) {
            $file = $request->file('pan_image');
            $destinationPath = public_path('/uploads/kycdocs/');
            $imagename = 'PAN'. $ins->id . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $imagename);
            $kyc->pan_image = $imagename;
        }
        if ($request->hasFile('aadhaar_front_image')) {
            $file = $request->file('aadhaar_front_image');
            $destinationPath = public_path('/uploads/kycdocs/');
            $imagename = 'ADHARF'. $ins->id . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $imagename);
            $kyc->aadhaar_front_image = $imagename;
        }
        if($request->hasFile('aadhaar_back_image')) {
            $file = $request->file('aadhaar_back_image');
            $destinationPath = public_path('/uploads/kycdocs/');
            $imagename = 'ADHARB'. $ins->id . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $imagename);
            $kyc->aadhaar_back_image = $imagename;
        }
        $kyc->save();
        
        if($request->get('onboarding_proccess') == 0){
            $eko_service = eko_services::where('user_id',$ins->id)->first();
            $eko_service->eko_status = 0;
            $eko_service->save();
        }
        
        Session::flash('success', 'Merchant Kyc Updated Successfully.');
        return redirect()->back();
    }
    
    public function fundRequest()
    {
        
        return view('new_pages.distributor.fundrequest.fund_request');
    }
    
    public function fundRequestData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $user_id = Auth::user()->id;
        $data = fund_requests::select('fund_requests.*','fund_banks.bank_name')->join('users','users.id','fund_requests.user_id')
                ->leftjoin('fund_banks','fund_banks.id','fund_requests.bank_id')
                ->where('fund_requests.user_id',$user_id)
                ->whereBetween('fund_requests.created_at', array($from, $to))
                ->get();
        
        return response()->json($data);
    }
    
    public function fundRequestApprove(Request $request)
    {
        $fund_req_id = $request->fund_req_id;
        $user_id = Auth::user()->id;
        $user_balance = Auth::user()->wallet->balanceFloat;
        
        
        $fund_req = fund_requests::find($fund_req_id);
        if($user_balance < $fund_req->amount){
            return response()->json(['success' => false, 'message' => 'Please Load You Balance']);
        }
        $user = User::find($fund_req->user_id);
        
        $wallet = Wallet::findOrNew($user);
        
        // Check if the user has a wallet
        if (!$wallet) {
            // If the user doesn't have a wallet, create one
            $wallet = Wallet::create(['user_id' => $fund_req->user_id]);
        } else {
            // If the user already has a wallet, fetch it
            $wallet = $user->wallet;
        }
        
        // Deposit funds into the user's wallet
        $txn_wl = $wallet->depositFloat($fund_req->amount,[
                'meta' => [
                    'Title' => 'Fund Request',
                    'detail' => 'Credit_Retailer_'.$user->mobile.'_Fund_Request_Transfer_By_'.Auth::user()->name,
                    'transaction_id' => $fund_req->transaction_id,
                ]
            ]);
        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
            
           
        // withdraw funds into the user's wallet
        $user_dist = User::find($user_id); 
        $wallet_dist = $user_dist->wallet;
        $txn_wl_dist = $wallet_dist->withdrawFloat($fund_req->amount,[
                'meta' => [
                    'Title' => 'Fund Request',
                    'detail' => 'Debit_Retailer_'.$user->mobile.'_Fund_Request_Transfer_By_'.Auth::user()->name,
                    'transaction_id' => $fund_req->transaction_id,
                ]
            ]);
        $balance_update = Transaction::where('uuid', $txn_wl_dist->uuid)->update(['balance' => $wallet_dist->balance]);
        
        $fund_req->closing_balance = $wallet->balanceFloat;
        $fund_req->wallets_uuid = $txn_wl->uuid;
        $fund_req->status = 1;
        $fund_req->approved_by = $user_id;
        $fund_req->approved_at = date("Y-m-d H:i:s");
        $fund_req->save();
        
        $msg = $fund_req->transaction_id." Fund Request Accepted.";
        $api_response = $this->ApiCalls->sendfcmNotification($fund_req->user_id,"Fund Request",$msg);
        return response()->json(['success' => true, 'message' => 'Request Accepted.']);
        
    }
    
    public function fundRequestReject(Request $request)
    {
        $fund_req_id = $request->fund_req_id;
        $user_id = Auth::user()->id;
        
        
        $fund_req = fund_requests::find($fund_req_id);
        $fund_req->status = 2;
        $fund_req->save();
        
        $msg = $fund_req->transaction_id." Fund Request Rejected.";
        $api_response = $this->ApiCalls->sendfcmNotification($fund_req->user_id,"Fund Request",$msg);
        
        return response()->json(['success' => true, 'message' => 'Request Rejected.']);
        
        
    }
    
    public function addFundBank()
    {
        
        return view('new_pages.distributor.fundrequest.add_bank');
    }
    
    public function postAddFundBank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "holder" => "required",
            "account" => "required",
            "ifsc" => "required",
            "transfer_type" => "required"
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
        $user_id = Auth::user()->id;
        $transfer = "";
        
        foreach ($request->transfer_type as $r) {
            $transfer .= " ".$r;
        }
        
        $ins = new fund_banks();
        $ins->user_id = $user_id;
        $ins->holder_name = $request->holder;
        $ins->account_number = $request->account;
        $ins->ifsc = $request->ifsc;
        $ins->transfer_types = $transfer;
        $ins->save();
        
        Session::flash('success', 'Bank Added Successfully.');
        return redirect()->back();
        
    }
    
    public function fundOD(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "mobile" => "required",
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
            
            Session::flash('error', $param_message);
            return redirect()->route('view_retailer_distributor');
        }
        if($request->type == 'Credit'){
            $retailer_mobile = $request->mobile;
            $user_id = Auth::user()->id;
            $amount = $request->amount;
            $user_balance = Auth::user()->wallet->balanceFloat;
            
            if($user_balance < $amount){
                // return response()->json(['success' => false, 'message' => 'Please Load Your Balance']);
                Session::flash('error', 'Please Load Your Balance.');
                return redirect()->route('view_retailer_distributor');
            }
            
            $user = User::where('mobile',$retailer_mobile)->first();
            $retailer_id = $user->id;
            
            sleep(rand(2,6));
            
            $recentTime = Carbon::now()->subMinutes(1);
            $duplicate = fund_ods::where('user_id', $retailer_id)
                        ->where('amount', $amount)
                        ->where('created_at', '>=', $recentTime)
                        ->exists();
            if ($duplicate) {
                log::info('Duplicate');
                log::info($request->all());
                Session::flash('error', 'Amount ₹'.$amount.' Transfered.');
                return redirect()->back();
            }
            
            $i_credit = 1;
            distodcredit:
            $userwallet = User::find($user->id);
            $wallet = $userwallet->wallet;
            
            $txnid = $this->UserAuth->txnId('OD');
            
            $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->whereDate('created_at', Carbon::today())->first();
           
            if($balance_check){
                $balance_check = $balance_check->balance;
            }else{
                $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
                
                if($balance_check){
                    $balance_check = $balance_check->balance;
                }else{
                    $balance_check = 0;
                }
                
            }
            
                
            $wallets_uuid_d = $this->WalletCalculation->walletDepositFloat($userwallet->id,$amount,'Fund OD','Credit_Retailer_'.$user->mobile.'_Fund_OD_Transfer_By_'.Auth::user()->name,$txnid);   
            
                
            $i_credit_dist = 1;
            distodcreditdist:
            // withdraw funds into the user's wallet
            $user_dist = User::find($user_id); 
            $wallet_dist = $user_dist->wallet;
            
            $balance_check_dist = Transaction::where('wallet_id', $wallet_dist->id)->orderBy('id','desc')->whereDate('created_at', Carbon::today())->first();
            if($balance_check_dist){
                $balance_check_dist = $balance_check_dist->balance;
            }else{
                $balance_check_dist = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
                if($balance_check_dist){
                    $balance_check_dist = $balance_check_dist->balance;
                }else{
                    $balance_check_dist = 0;
                }
                
            }
            
            $wallets_uuid = $this->WalletCalculation->walletWithdrawFloat($user_dist->id,$amount,'Fund OD','Debit_Distributor_'.$user->mobile.'_Fund_OD_Transfer_By_'.Auth::user()->name,$txnid);
            
            
            $fund_req = new fund_ods();
            $fund_req->user_id = $retailer_id;
            $fund_req->transaction_id = $txnid;
            $fund_req->amount = $amount;
            $fund_req->wallets_uuid = $wallets_uuid_d;
            $fund_req->status = 1;
            $fund_req->save();
            
            // try {
            //     $msg = 'Amount ₹'.$amount.' Credited.';
            //     $api_response = $this->ApiCalls->sendfcmNotification($fund_req->user_id,"Fund Deposit",$msg);
            // }
            // catch(Exception $e) {
              
            // }
            
            
            
            Session::flash('success', 'OD amount ₹'.$amount.' Transfered.');
            return redirect()->route('view_retailer_distributor');
        }
        
        if($request->type == 'Debit'){
            $retailer_mobile = $request->mobile;
            $user_id = Auth::user()->id;
            $amount = $request->amount;
            
            $i_debit = 1;
            distoddebit:
            $user = User::with('wallet')->where('mobile',$retailer_mobile)->first();
            $user_balance = $user->wallet->balanceFloat;
            $retailer_id = $user->id;
            if($user_balance < $amount){
                // return response()->json(['success' => false, 'message' => 'Please Load Your Balance']);
                Session::flash('error', 'Retailer balance low.');
                return redirect()->route('view_retailer_distributor');
            }
            $wallet = $user->wallet;
            $txnid = $this->UserAuth->txnId('OD');
            
            sleep(rand(2,6));
            
            $recentTime = Carbon::now()->subMinutes(1);
            $duplicate = fund_ods::where('user_id', $retailer_id)
                        ->where('amount', $amount)
                        ->where('created_at', '>=', $recentTime)
                        ->exists();
            if ($duplicate) {
                log::info('Duplicate');
                log::info($request->all());
                Session::flash('error', 'Amount ₹'.$amount.' Transfered.');
                return redirect()->back();
            }
            
            // Deposit funds into the user's wallet
            $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->whereDate('created_at', Carbon::today())->first();
            if($balance_check){
                $balance_check = $balance_check->balance;
            }else{
                $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
                if($balance_check){
                    $balance_check = $balance_check->balance;
                }else{
                    $balance_check = 0;
                }
                
            }
            
            $wallets_uuid_w = $this->WalletCalculation->walletWithdrawFloat($user->id,$amount,'Fund OD Reverse','Debit_Retailer_'.$user->mobile.'_Fund_OD_Reverse_Transfer_By_'.Auth::user()->name,$txnid);
            
                
            $i_debit_dist = 1;
            distoddebitdist:
            // withdraw funds into the user's wallet
            $user_dist = User::find($user_id); 
            $wallet_dist = $user_dist->wallet;
            
            $balance_check_dist = Transaction::where('wallet_id', $wallet_dist->id)->orderBy('id','desc')->whereDate('created_at', Carbon::today())->first();
            if($balance_check_dist){
                $balance_check_dist = $balance_check_dist->balance;
            }else{
                $balance_check_dist = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
                if($balance_check_dist){
                    $balance_check_dist = $balance_check_dist->balance;
                }else{
                    $balance_check_dist = 0;
                }
                
            }
            
            $wallets_uuid = $this->WalletCalculation->walletDepositFloat($user_dist->id,$amount,'Fund OD','Credit_Distributor_'.$user->mobile.'_Fund_OD_Reverse_Transfer_By_'.Auth::user()->name,$txnid);
            
            
            $fund_req = new fund_ods();
            $fund_req->user_id = $retailer_id;
            $fund_req->transaction_id = $txnid;
            $fund_req->amount = $amount;
            $fund_req->wallets_uuid = $wallets_uuid_w;
            $fund_req->status = 1;
            $fund_req->save();
            
            // try {
            //     $msg = 'Amount ₹'.$amount.' Credited.';
            //     $api_response = $this->ApiCalls->sendfcmNotification($fund_req->user_id,"Fund Deposit",$msg);
            // }
            // catch(Exception $e) {
              
            // }
            
            Session::flash('success', 'OD amount ₹'.$amount.' Transfered.');
            return redirect()->route('view_retailer_distributor');
        }
    }
    
    public function fundODReport()
    {
        
        return view('new_pages.distributor.fundrequest.fund_od');
    }
    
    public function fundODReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        $data = fund_ods::select('fund_ods.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"),'users.mobile as retailer_mobile','shop_details.shop_name','transactions.type')
                ->join('users','users.id','fund_ods.user_id')
                ->join('shop_details','shop_details.user_id','fund_ods.user_id')
                ->join('user_levels','user_levels.user_id','fund_ods.user_id')
                ->leftJoin('transactions', 'transactions.uuid', 'fund_ods.wallets_uuid')
                ->where('user_levels.toplevel_id',$user_id)
                ->where('fund_ods.is_od',1)
                ->whereBetween('fund_ods.created_at', array($from, $to))
                ->get();
        
        return response()->json($data);
    }
    
    public function myStatment()
    {
        
        return view('new_pages.distributor.report.my_statment');
    }
    
    public function myStatmenttData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $user_id = Auth::user()->id;
        $user = User::findOrFail($user_id);
        $data = Transaction::select('transactions.*',DB::raw('ROUND(transactions.balance / 100, 2) as balance'))->where('payable_id',$user_id)->where('payable_type', User::class)->whereBetween('created_at', array($from, $to))->orderBy('created_at', 'desc')->get();
        
        return response()->json($data);
    }
    
    public function dmtReport(Request $request)
    {
        return view('new_pages.distributor.report.dmt');
    }
    
    public function dmtReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        $api_id = $request->dmt_id;
        
        $data = transactions_dmt::select('transactions_dmts.*',
            DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"),'users.mobile as retailer_mobile','shop_details.shop_name')
            ->join('users','users.id','transactions_dmts.user_id')
            ->join('shop_details','shop_details.user_id','transactions_dmts.user_id')
            ->join('user_levels','user_levels.user_id','users.id')
            ->where('user_levels.toplevel_id',$user_id)
            ->where('transactions_dmts.event','DMT')
            ->whereBetween('transactions_dmts.created_at', array($from, $to));
        
        if ($api_id == 0) {
            $data->addSelect(
                DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),
                'dmt_customers.mobile as customer_mobile',
                'dmt_beneficiaries.bank_name as bank_name'
            )
            ->join('dmt_customers', 'dmt_customers.mobile', 'transactions_dmts.mobile')
            ->leftJoin('dmt_beneficiaries', 'dmt_beneficiaries.id', 'transactions_dmts.dmt_beneficiary_id')
            ->where('dmt_customers.status', 1)->where('transactions_dmts.api_id', 0)->whereNull('dmt_customers.dmt_type');
        }else{
            $data->addSelect(
                'transactions_dmts.sender_name as customer_name',
                'transactions_dmts.mobile as customer_mobile',
                'transactions_dmts.bank_name as bank_name'
            );
            $data->where('transactions_dmts.api_id', $api_id);
        }
        
        $data = $data->orderBy('transactions_dmts.id', 'DESC')->get();
            
        return response()->json($data);
    }
    
    public function scanandpayReport(Request $request)
    {
        return view('new_pages.distributor.report.scanandpay');
    }
    
    public function scanandpayReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        
        $data = transactions_dmt::select('transactions_dmts.*',
            DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"),'users.mobile as retailer_mobile','shop_details.shop_name')
            ->join('users','users.id','transactions_dmts.user_id')
            ->join('shop_details','shop_details.user_id','transactions_dmts.user_id')
            ->join('user_levels','user_levels.user_id','users.id')
            ->where('user_levels.toplevel_id',$user_id)
            ->where('transactions_dmts.event','SCANNPAY')
            ->whereBetween('transactions_dmts.created_at', array($from, $to))
            ->orderBy('transactions_dmts.id','DESC')->get();
            
        return response()->json($data);
    }
    
    public function retailerFundRequest()
    {
        
        return view('new_pages.distributor.report.retailer_fund_request');
    }
    
    public function retailerFundRequestData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        $data = fund_requests::select('fund_requests.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"),'fund_banks.bank_name')
                ->join('user_levels','user_levels.user_id','fund_requests.user_id')
                ->join('users','users.id','fund_requests.user_id')
                ->leftjoin('fund_banks','fund_banks.id','fund_requests.bank_id')
                ->where('user_levels.toplevel_id',$user_id)
                ->whereBetween('fund_requests.created_at', array($from, $to))->get();
        
        return response()->json($data);
    }
    public function onlineFund(Request $request)
    {
        return view('new_pages.distributor.fundrequest.online_load');
    }
    
    public function onlineFundData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        $data = fund_onlines::select('fund_onlines.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"),'users.mobile as retailer_mobile','shop_details.shop_name')
            ->join('users','users.id','fund_onlines.user_id')
            ->join('shop_details','shop_details.user_id','fund_onlines.user_id')
            ->join('user_levels', 'user_levels.user_id', 'fund_onlines.user_id') // assuming there's a join to user_levels
            ->where(function ($query) use ($user_id) {
                $query->where('user_levels.user_id', $user_id)
                      ->orWhere('user_levels.toplevel_id', $user_id);
            })
            ->where('fund_onlines.status',1)
            // ->whereBetween('transactions_dmts.created_at', array($from, $to))
            ->whereBetween('fund_onlines.created_at', array($from, $to))
            ->orderBy('id','DESC')->get();
            
        return response()->json($data);
    }
    
    public function createFundRequest()
    {
        
        
        $fund_banks = fund_banks::where('user_id',1)->get();
        return view('new_pages.distributor.fundrequest.create',compact('fund_banks'));
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
}
