<?php

namespace App\Http\Controllers;
use App\Classes\UserAuth;
use App\Classes\ApiCalls;
use App\Classes\WalletCalculation;
use App\Http\Controllers\EkoController;
use App\Http\Controllers\BulkpeController;
use App\Http\Controllers\CyrusController;

use Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use DataTables;
// use Excel;
use Mail;
use Auth;
use DB;
use PDF;

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
use App\Models\fund_ods;
use App\Models\user_commissions;
use App\Models\recharge_commissions;
use App\Models\recharge_slabs;
use App\Models\ace_operators;
use App\Models\transactions_recharges;

use App\Models\CcSenders;
use App\Models\CcBeneficiaries;
use App\Models\transactions_cc;

class AdminController extends Controller
{
    public function __construct(UserAuth $Auth, WalletCalculation $WalletCalculation, EkoController $EkoController, BulkpeController $BulkpeController, CyrusController $CyrusController, ApiCalls $ApiCalls) {
        $this->middleware('auth');
        
        $this->UserAuth = $Auth;
        $this->WalletCalculation = $WalletCalculation;
        $this->EkoController = $EkoController;
        $this->BulkpeController = $BulkpeController;
        $this->CyrusController = $CyrusController;
        $this->ApiCalls = $ApiCalls;
    }
    
    public function getRechargeCreateOp(){
        return view('new_pages.admin.recharge.create_op');
    }
    
    public function postRechargeCreateOp(Request $request){
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "op_code" => "required|unique:ace_operators,op_code",
            "op_image" => "required",
            "op_type" => "required",
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
        
        $op = new ace_operators();
        $op->name = strtoupper($request->get('name'));
        $op->op_code = $request->get('op_code');
        $op->status = 1;
        $op->op_type = $request->get('op_type');
        
        if ($request->hasFile('op_image')) {
            $file = $request->file('op_image');
            $destinationPath = public_path('/uploads/recharge/');
            $imagename = 'OP'. $request->get('op_code') . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $imagename);
            $op->op_image = $imagename;
        }
        $op->save();
        
        Session::flash('success', 'Network Created!');
        return redirect()->route('get_recharge_create_op');
        
    }
    
    public function getRechargeSlab(){
        return view('new_pages.admin.recharge.create_slab');
    }
    
    public function postRechargeSlab(Request $request){
        $validator = Validator::make($request->all(), [
            "name" => "required",
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
        
        $op = new recharge_slabs();
        $op->name = strtoupper($request->get('name'));
        $op->save();
        
        Session::flash('success', 'Network Slab Created!');
        return redirect()->route('get_recharge_create_slab');
        
    }
    
    public function getRechargeSlabCommission(){
        $slabs = recharge_slabs::orderBy('name','ASC')->get();
        $operators = ace_operators::orderBy('name','ASC')->get();
        return view('new_pages.admin.recharge.commission_slab',compact('slabs','operators'));
    }
    
    public function postRechargeSlabCommission(Request $request){
        $validator = Validator::make($request->all(), [
            "slab_id" => "required|exists:recharge_slabs,id",
            "op_id" => "required|exists:ace_operators,id",
            "commission_type" => "required",
            "commission" => "required|numeric",
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
        
        $op = new recharge_commissions();
        $op->slab_id = $request->get('slab_id');
        $op->op_id = $request->get('op_id');
        $op->commission_type = $request->get('commission_type');
        $op->commission = $request->get('commission');
        $op->save();
        
        Session::flash('success', 'Commission Added!');
        return redirect()->route('get_recharge_slab_commission');
        
    }
    
    
    
    public function index()
    {
        $today = Carbon::today();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $user_id = Auth::user()->id;
        $total_retailer = User::select('users.name')->where('users.user_type','2')->get()->count();
        $total_dist = User::select('users.name')->where('users.user_type','3')->get()->count();
        $total_fund_requests = fund_requests::select('fund_requests.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"))->join('users','users.id','fund_requests.user_id')->join('user_levels','user_levels.user_id','fund_requests.user_id')->where('user_levels.toplevel_id',$user_id)->where('fund_requests.status','0')->get()->count();
        $total_transactions_dmt = transactions_dmt::where('status',1)->get()->sum('amount');
        $total_transactions_aeps = transactions_aeps::where('status',1)->get()->sum('amount');
        $total_transactions_online = fund_onlines::where('status',1)->get()->sum('amount');
        //today
        $today_transactions_dmt = transactions_dmt::where('status',1)->whereDate('created_at', $today)->get()->sum('amount');
        $today_transactions_aeps = transactions_aeps::where('status',1)->whereDate('created_at', $today)->get()->sum('amount');
        $today_transactions_qr = fund_onlines::where('status',1)->where('pg_id',3)->whereDate('created_at', $today)->get()->sum('amount');
        
        //this month
        $month_transactions_dmt = transactions_dmt::where('status',1)->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->get()->sum('amount');
        $month_transactions_aeps = transactions_aeps::where('status',1)->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->get()->sum('amount');
        $month_transactions_qr = fund_onlines::where('status',1)->where('pg_id',3)->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->get()->sum('amount');
        
        
        return view('new_pages.admin.home',compact('month_transactions_qr','today_transactions_qr','total_transactions_online','total_transactions_aeps','month_transactions_aeps','today_transactions_aeps','total_retailer','total_dist','total_fund_requests','total_transactions_dmt','today_transactions_dmt','month_transactions_dmt','today_transactions_dmt'));
    }
    
    public function getCity(Request $request){
        $city = cities::where('state_id',$request->state)->get();
        $html = "";
        foreach($city as $r){
            $html .= "<option value='$r->id'>$r->name</option>"; 
        }
        
        return $html;
    }
    
    public function createPdf()
    {
        // Define the data to pass to the view
        $data = [
            'shop_name' => 'XYZ Mobile SHop',
            'name' => 'XYZ ABC',
            'email' => 'xyzabc@gmail.com',
            'address' => 'Rajkot, Gujarat',
            'mobile' => '9876543210',
            'place' => 'rajkot',
            'territory' => 'rajkot'
        ];

        // Load the Blade view and generate the PDF
        $pdf = PDF::loadView('pdf.myPDF', $data);

        // Define the file path and name
        $filePath = public_path('pdf/myStoredPDF.pdf');

        // Ensure the directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        // Save the PDF to the specified path
        $pdf->save($filePath);

        // Optionally return a response
        return response()->json(['message' => 'PDF saved successfully', 'path' => $filePath]);
    }
    
    public function createRetailer()
    {
        $states = states::get();
        return view('new_pages.admin.retailer.create',compact('states'));
    }
    
    public function viewRetailer()
    {
        $states = states::get();
        $dist = User::select('users.id','users.name','users.mobile')->where('users.user_type','3')->get();
        return view('new_pages.admin.retailer.view',compact('dist'));
    }
    
    public function viewRetailerData(Request $request)
    {
        if($request->get('dist_id') != 0){
            $dist_id = $request->get('dist_id');
            $data = User::with(['wallet','shopDetail','addresses','kycDocs'])->select('users.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"))
                    ->join('user_levels','user_levels.user_id','users.id')->where('user_levels.toplevel_id',$dist_id)->where('user_type','2')->get();
        }else{
            $data = User::with(['wallet','shopDetail','addresses','kycDocs'])->select('users.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"))->where('user_type','2')->get();
        }
        
        return response()->json($data);
    }
    
    public function viewRetailerDataExport(Request $request)
    {
        if($request->get('dist_id') != 0){
            $dist_id = $request->get('dist_id');
            $data = User::with('wallet')->select('users.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"))
                    ->join('user_levels','user_levels.user_id','users.id')->where('user_levels.toplevel_id',$dist_id)->where('user_type','2')->get();
        }else{
            $data = User::with('wallet')->select('users.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"))->where('user_type','2')->get();
        }
        
        $data_e = $data->map(function ($d) {
				return [
				    "Name"=>$d->user_name,
				    "Mobile"=>$d->mobile,
				    "Email"=>$d->email,
				    "Balance"=> $d->wallet->balance / 100
				    ];
            
        })->toArray();
        
        // print_r($data_e);exit;
        return $this->toCsvExport($data_e,'retailers.csv');
    }
    
    public function postCreateRetailer(Request $request)
    {
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
        $ins->status = 2;
        $ins->password = bcrypt("123456789");
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
        
        $ins_level = new user_levels();
        $ins_level->user_id = $ins->id;
        $ins_level->toplevel_id = 1;
        $ins_level->save();
        
        $wallet = Wallet::create(['user_id' => $ins->id,'holder_id' => $ins->id]);
        
        $text = urlencode("Action Required: Use OTP $otp to verify and accept Payrite Payment Partner agreement of $name account within 10 minutes. https://payritepayment.in/terms_conditions.php ");
        $api_response = $this->ApiCalls->smsgatewayhubGetCall($mobile,$text,1207171774902713997);
        
        Session::flash('success', 'OTP Send On Your Mobile');
        return view('new_pages.admin.retailer.otp',compact('mobile'));
        
    }
    
    public function viewDistributor()
    {
        
        return view('new_pages.admin.distributor.view');
    }
    
    public function viewDistributorData(Request $request)
    {
        $data = User::with(['wallet','shopDetail','addresses','kycDocs'])->select('users.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"))->where('user_type','3')->get();
        return response()->json($data);
    }
    
    public function createDistributor()
    {
        $states = states::get();
        return view('new_pages.admin.distributor.create',compact('states'));
    }
    
    public function postCreateDistributor(Request $request)
    {
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
            
            Session::flash('error', $param_message);
            return redirect()->back()->withInput();
        }
        $mobile = $request->mobile;
        $name = $request->name." ".$request->surname;
        $otp = rand(100000, 999999);
        $ins = new User();
        $ins->user_id = $this->UserAuth->getUserId("DT");
        $ins->name = $request->name;
        $ins->surname = $request->surname;
        $ins->email = $request->email;
        $ins->mobile = $request->mobile;
        $ins->dob = $request->dob;
        $ins->otp = $otp;
        $ins->user_type = 3;
        $ins->status = 0;
        $ins->password = bcrypt("123456789");
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
            $file->move($destinationPath, $imagename);
            $insshop->shop_img = $imagename;
        }
        if ($request->hasFile('selfie')) {
            $file = $request->file('selfie');
            $destinationPath = public_path('/uploads/selfie/');
            $imagename = 'SELFIE'. $ins->id . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $imagename);
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
        
        $ins_level = new user_levels();
        $ins_level->user_id = $ins->id;
        $ins_level->toplevel_id = 1;
        $ins_level->save();
        
        $wallet = Wallet::create(['user_id' => $ins->id,'holder_id' => $ins->id]);
        
        $text = urlencode("Action Required: Use OTP $otp to verify and accept Payrite Payment Partner agreement of $name account within 10 minutes. https://payritepayment.in/terms_conditions.php ");
        $api_response = $this->ApiCalls->smsgatewayhubGetCall($mobile,$text,1207171774902713997);
        
        Session::flash('success', 'OTP Send On Your Mobile');
        return view('new_pages.admin.distributor.otp',compact('mobile'));
        
    }
    
    public function postCreateDistributorOtp(Request $request)
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
                return redirect()->route('dashboard_admin');
            }else{
                Session::flash('error', 'OTP Not Match.');
                return view('new_pages.admin.distributor.otp',compact('mobile'));
            }
        }else{
            Session::flash('error', 'User Not FOund');
            return redirect()->route('dashboard_admin');
        }
    }
    
    public function getAeps() {
        
        $post_data = array(
            'token'=>"$2a$12$/QqLhcvjkP3.WateC7hqfe1UH.ZXonr8hUcZQ/HlsSu3E7nqF.cFe",
            'user_mobile'=>9876543211,
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



    }
    
    public function fundRequest()
    {
        return view('new_pages.admin.fundrequest.fund_request');
    }
    
    public function fundRequestData(Request $request)
    {
        $user_id = Auth::user()->id;
        $data = fund_requests::select('fund_requests.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"),'fund_banks.bank_name')
                ->join('users','users.id','fund_requests.user_id')
                ->leftjoin('fund_banks','fund_banks.id','fund_requests.bank_id')
                ->where('fund_requests.status','0')->get();
        
        return response()->json($data);
    }
    
    public function fundRequestApprove(Request $request)
    {
        $fund_req_id = $request->fund_req_id;
        $user_id = Auth::user()->id;
        $remark = $request->remark;
        
        $fund_req = fund_requests::find($fund_req_id);
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
        $txnid_ref = $fund_req->transaction_id;
        
        // $txn_wl = $wallet->depositFloat($fund_req->amount,[
        //                 'meta' => [
        //                     'Title' => 'Fund Request',
        //                     'detail' => 'Credit_Retailer_'.$user->mobile.'_Fund_Request_Transfer_By_'.Auth::user()->name.'_'.$remark,
        //                     'transaction_id' => $txnid_ref,
        //                 ]
        //             ]);
        
        $wallets_uuid = $this->WalletCalculation->walletDepositFloat($fund_req->user_id,$fund_req->amount,"Fund Request",'Credit_Retailer_'.$user->mobile.'_Fund_Request_Transfer_By_'.Auth::user()->name.'_'.$remark,$txnid_ref);            
        $balance_update = Transaction::where('uuid', $wallets_uuid)->update(['balance' => $wallet->balance]);
        
        $fund_req->wallets_uuid = $wallets_uuid;
        $fund_req->status = 1;
        $fund_req->closing_balance = $wallet->balanceFloat;
        $fund_req->approved_by = $user_id;
        $fund_req->approved_at = date("Y-m-d H:i:s");
        $fund_req->admin_remark = $remark;
        $fund_req->save();
        
        $msg = $fund_req->transaction_id." Fund Request Accepted.";
        $api_response = $this->ApiCalls->sendfcmNotification($fund_req->user_id,"Fund Request",$msg);
        
        return response()->json(['success' => true, 'message' => 'Request Accepted.']);
        
    }
    
    public function fundRequestReject(Request $request)
    {
        $fund_req_id = $request->fund_req_id;
        $remark = $request->remark;
        $user_id = Auth::user()->id;
        
        
        $fund_req = fund_requests::find($fund_req_id);
        $fund_req->status = 2;
        $fund_req->admin_remark = $remark;
        $fund_req->save();
        
        $msg = $fund_req->transaction_id." Fund Request Rejected.";
        $api_response = $this->ApiCalls->sendfcmNotification($fund_req->user_id,"Fund Request",$msg);
        
        return response()->json(['success' => true, 'message' => 'Request Rejected.']);
        
        
    }
    
    public function fundRequestReport()
    {
        
        return view('new_pages.admin.report.fund_request');
    }
    
    public function fundRequestReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        $data = fund_requests::select('fund_requests.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"),'fund_banks.bank_name')
                ->join('users','users.id','fund_requests.user_id')
                ->leftjoin('fund_banks','fund_banks.id','fund_requests.bank_id')
                ->whereBetween('fund_requests.created_at', array($from, $to))->get();
        
        return response()->json($data);
    }
    
    public function servicesRetailer()
    {
        return view('new_pages.admin.retailer.services');
    }
    
    public function servicesRetailerData(Request $request)
    {
        $data = User::select('users.mobile','users.id as user_idd',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"),'eko_services.*')
                ->leftjoin('eko_services','eko_services.user_id','users.id')
                ->get();
        return response()->json($data);
    }
    
    public function serviceActive(Request $request)
    {
        if($request->service == 'cyrus_aeps'){
            $user = User::select('users.mobile','users.id as user_idd',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"),"addresses.*","shop_details.*","kyc_docs.*")
                    ->join('addresses','addresses.user_id','users.id')
                    ->join('shop_details','shop_details.user_id','users.id')
                    ->join('kyc_docs','kyc_docs.user_id','users.id')
                    ->get();
            $api_params = array('MerchantID' => 'AP487545',
            'MerchantKey' => '{{MerchantKeyAEPS3}}',
            'MethodName' => 'REGISTRATION',
            'Mobile' => $user->user_name,
            'Email' => $user->email,
            'Company' => $user->shop_name,
            'Name' => $user->pan_number,
            'Pan' => $user->pan_number,
            'Pincode' => $user->pincode,
            'Address' => $user->address,
            'Aadhar' => $user->aadhaar_number);
            
            $result = $this->CyrusController->payoutTransaction($api_params);
        }else{
            $service = eko_services::where('user_id',$request->user_id)->first();
            if($service){
                $clnm = $request->service;
                $service->$clnm = $request->status;
                $service->save();
                
                return response()->json(['success' => true, 'message' => 'Service Status Changed!!']);
            }else{
                $clnm = $request->service;
                $check = new eko_services();
                $check->user_id = $request->user_id;
                $check->$clnm = $request->status;
                $check->save();
                return response()->json(['success' => true, 'message' => 'Service Status Changed!!']);
            }
        }
    }
    
    public function postSendPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "mobile" => "required|exists:users,mobile"
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
        
        $retailer_mobile = $request->mobile;
        $user = User::where('mobile',$retailer_mobile)->first();
        $passwprd = $this->UserAuth->getPassword(8);
        $user->password = bcrypt($passwprd);
        $user->save();
        
        $text = urlencode("Your account at Payrite has been successfully created! Your login id $retailer_mobile and password $passwprd. https://payritepayment.in/");
        $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->mobile,$text,1207170972078183783);
        
        return response()->json(['success' => true, 'message' => 'Password Sent On User Mobile.']);
    }
    
    public function fundOD(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "mobile" => "required",
            "amount" => "required",
            "is_od" => "required",
            "type" => "required",
            "remark" => "required"
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
        if($request->type == 'Credit'){
        $retailer_mobile = $request->mobile;
        $user_id = Auth::user()->id;
        $amount = $request->amount;
        $user_balance = Auth::user()->wallet->balanceFloat;
        
        
        $user = User::where('mobile',$retailer_mobile)->first();
        $retailer_id = $user->id;
        
        $userwallet = User::find($user->id);
        $wallet = $userwallet->wallet;
        sleep(rand(2,6));
        $txnid = $this->UserAuth->txnId('OD');
        
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
        $remarks = str_replace(' ', '_', $request->remark);
        // $txn_wl = $wallet->depositFloat($amount,[
        //         'meta' => [
        //             'Title' => 'Admin Fund Transfer',
        //             'detail' => 'Credit_User_'.$user->mobile.'_'.$remarks,
        //             'transaction_id' => $txnid,
        //         ]
        //     ]);
            
        $wallets_uuid = $this->WalletCalculation->walletDepositFloat($retailer_id,$amount,"Admin Fund Transfer",'Credit_User_'.$user->mobile.'_'.$remarks,$txnid);
        $balance_update = Transaction::where('uuid', $wallets_uuid)->update(['balance' => $wallet->balance]);
        
        $fund_req = new fund_ods();
        $fund_req->user_id = $retailer_id;
        $fund_req->transaction_id = $txnid;
        $fund_req->amount = $amount;
        $fund_req->closing_balance = $wallet->balanceFloat;
        $fund_req->wallets_uuid = $wallets_uuid;
        $fund_req->remark = $request->remark;
        $fund_req->status = 1;
        $fund_req->is_od = $request->is_od;
        $fund_req->save();
        
        
        Session::flash('success', 'Amount ₹'.$amount.' Transfered.');
        return redirect()->back();
        }
        
        if($request->type == 'Debit'){
            $retailer_mobile = $request->mobile;
            $user_id = Auth::user()->id;
            $amount = $request->amount;
            
            $user = User::with('wallet')->where('mobile',$retailer_mobile)->first();
            $user_balance = $user->wallet->balanceFloat;
            $retailer_id = $user->id;
            if($user_balance < $amount){
                // return response()->json(['success' => false, 'message' => 'Please Load Your Balance']);
                Session::flash('error', 'Dist balance low.');
                return redirect()->route('view_distributor');
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
            $remarks = str_replace(' ', '_', $request->remark);
            // $txn_wl = $wallet->withdrawFloat($amount,[
            //         'meta' => [
            //             'Title' => 'Admin Fund Transfer',
            //             'detail' => 'Debit_User_'.$user->mobile.'_'.$remarks,
            //             'transaction_id' => $txnid,
            //         ]
            //     ]);
            
            $wallets_uuid = $this->WalletCalculation->walletWithdrawFloat($retailer_id,$amount,"Admin Fund Transfer",'Debit_User_'.$user->mobile.'_'.$remarks,$txnid);
            $balance_update = Transaction::where('uuid', $wallets_uuid)->update(['balance' => $wallet->balance]);
        
            
            $fund_req = new fund_ods();
            $fund_req->user_id = $retailer_id;
            $fund_req->transaction_id = $txnid;
            $fund_req->amount = $amount;
            $fund_req->wallets_uuid = $wallets_uuid;
            $fund_req->remark = $request->remark;
            $fund_req->status = 1;
            $fund_req->is_od = $request->is_od;
            $fund_req->save();
            
            Session::flash('success', 'OD Reverse amount ₹'.$amount.' Transfered.');
            return redirect()->route('view_distributor');
        }
        
    }
    
    public function userStatment($mobile)
    {
        
        return view('new_pages.admin.report.my_statment',compact('mobile'));
    }
    
    public function usserStatmentData($user_mobile,Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $user_find = User::where('mobile',$user_mobile)->first();
        $user_id = $user_find->id;
        $user = User::findOrFail($user_id);
        $data = Transaction::select('transactions.*',DB::raw('ROUND(transactions.balance / 100, 2) as balance'))
                ->where('payable_id',$user_id)
                ->where('payable_type', User::class)
                ->whereBetween('created_at', array($from, $to))
                ->orderBy('id', 'desc')->get();
        
        return response()->json($data);
    }
    
    public function usserStatmentDataExport($user_mobile,Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $user_find = User::where('mobile',$user_mobile)->first();
        $user_id = $user_find->id;
        $user = User::findOrFail($user_id);
        $data = Transaction::select('transactions.type',
                DB::raw('ROUND(transactions.amount / 100, 2) as amount'),
                DB::raw('ROUND(transactions.balance / 100, 2) as balance'),
                DB::raw('JSON_UNQUOTE(JSON_EXTRACT(transactions.meta, "$.meta.transaction_id")) as transaction_id'),
                DB::raw('JSON_UNQUOTE(JSON_EXTRACT(transactions.meta, "$.meta.detail")) as detail'),'transactions.created_at')
                ->where('payable_id',$user_id)
                ->where('payable_type', User::class)
                ->whereBetween('created_at', array($from, $to))
                ->orderBy('id', 'desc')->get()->toArray();
        
        
            
        return $this->toCsvExport($data,$user_mobile.'-statment.csv');
    }
    
    public function dmtReport(Request $request)
    {
        return view('new_pages.admin.report.dmt');
    }
    
    public function dmtReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $api_id = $request->api_id;
        $event = $request->event;
        $data = transactions_dmt::select('transactions_dmts.*',
            DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"),'users.mobile as retailer_mobile','shop_details.shop_name',
            DB::raw("CONCAT(dist_user.name, ' ', dist_user.surname) as dist_name"),'dist_user.mobile as dist_mobile')
            ->join('users','users.id','transactions_dmts.user_id')
            ->join('shop_details','shop_details.user_id','transactions_dmts.user_id')
            ->join('user_levels','user_levels.user_id','users.id')
            ->join('users as dist_user','dist_user.id','user_levels.toplevel_id');
            if($event == 'ALL'){
                $data = $data->whereIn('transactions_dmts.event',['DMT','SCANNPAY']);
            }else{
                $data = $data->where('transactions_dmts.event',$event);
            }
        
        if($event == 'DMT'){
            if($api_id == 0) {
                $data = $data->addSelect(
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),
                    'dmt_customers.mobile as customer_mobile',
                    'dmt_beneficiaries.bank_name as bank_name','dmt_customers.id as dmtcustid'
                )
                ->join('dmt_customers', 'dmt_customers.mobile', 'transactions_dmts.mobile')
                ->join('dmt_beneficiaries', 'dmt_beneficiaries.id', 'transactions_dmts.dmt_beneficiary_id')
                ->where('dmt_customers.status', 1)->where('transactions_dmts.api_id', 0)->whereNull('dmt_customers.dmt_type');
            }else{
                $data = $data->addSelect(
                    'transactions_dmts.sender_name as customer_name',
                    'transactions_dmts.mobile as customer_mobile',
                    'transactions_dmts.bank_name as bank_name'
                );
                $data = $data->where('transactions_dmts.api_id', $api_id);
            }
        }else{
            $data = $data->addSelect(
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),
                    'dmt_customers.mobile as customer_mobile',
                    'dmt_beneficiaries.bank_name as bank_name','dmt_customers.id as dmtcustid'
                )
                ->leftJoin('dmt_customers', 'dmt_customers.mobile', 'transactions_dmts.mobile')
                ->leftJoin('dmt_beneficiaries', 'dmt_beneficiaries.id', 'transactions_dmts.dmt_beneficiary_id')
                ->where('transactions_dmts.api_id', 0);
        }
            
            $data = $data->whereBetween('transactions_dmts.created_at', array($from, $to))
            ->orderBy('transactions_dmts.id','DESC')->get();
            
        return response()->json($data);
    }
    
    public function dmtReportDataExport(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $api_id = $request->api_id;
        $event = $request->event;
        
        $data = transactions_dmt::select('transactions_dmts.*',
            DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"),'users.mobile as retailer_mobile','shop_details.shop_name',
            DB::raw("CONCAT(dist_user.name, ' ', dist_user.surname) as dist_name"),
            DB::raw("CASE WHEN transactions_dmts.status = 2 THEN 'Failed' WHEN transactions_dmts.status = 1 THEN 'Success' WHEN transactions_dmts.status = 0 THEN 'Pending' END as status_txn"),'dist_user.mobile as dist_mobile')
            ->join('users','users.id','transactions_dmts.user_id')
            ->join('shop_details','shop_details.user_id','transactions_dmts.user_id')
            ->join('user_levels','user_levels.user_id','users.id')
            ->join('users as dist_user','dist_user.id','user_levels.toplevel_id');
            if($event == 'ALL'){
                $data = $data->whereIn('transactions_dmts.event',['DMT','SCANNPAY']);
            }else{
                $data = $data->where('transactions_dmts.event',$event);
            }
        
        if($event == 'DMT'){
            if($api_id == 0) {
                $data = $data->addSelect(
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),
                    'dmt_customers.mobile as customer_mobile',
                    'dmt_beneficiaries.bank_name as bank_name','dmt_customers.id as dmtcustid'
                )
                ->join('dmt_customers', 'dmt_customers.mobile', 'transactions_dmts.mobile')
                ->leftJoin('dmt_beneficiaries', 'dmt_beneficiaries.id', 'transactions_dmts.dmt_beneficiary_id')
                ->where('dmt_customers.status', 1)->where('transactions_dmts.api_id', 0);
            }else{
                $data = $data->addSelect(
                    'transactions_dmts.sender_name as customer_name',
                    'transactions_dmts.mobile as customer_mobile',
                    'transactions_dmts.bank_name as bank_name'
                );
                $data = $data->where('transactions_dmts.api_id', $api_id);
            }
        }else{
            $data = $data->addSelect(
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),
                    'dmt_customers.mobile as customer_mobile',
                    'dmt_beneficiaries.bank_name as bank_name','dmt_customers.id as dmtcustid'
                )
                ->leftJoin('dmt_customers', 'dmt_customers.mobile', 'transactions_dmts.mobile')
                ->leftJoin('dmt_beneficiaries', 'dmt_beneficiaries.id', 'transactions_dmts.dmt_beneficiary_id')
                ->where('transactions_dmts.api_id', 0);
        }
           
            $data = $data->whereBetween('transactions_dmts.created_at', array($from, $to))
            ->orderBy('transactions_dmts.id','DESC')->get()->toArray();
            
            
        return $this->toCsvExport($data,'dmtreport.csv');
    }
    
    public function dmtTxnFailed(Request $request)
    {
        $txn_id = $request->transaction_id;
        $user_id = Auth::user()->id;
        if(Auth::user()->user_type == 1){
            $transaction = transactions_dmt::where('transaction_id',$txn_id)->where('status',0)->first();
            if($transaction){
                $amount = $transaction->amount;
                $fee = $transaction->fee;
                $userwallet = User::find($transaction->user_id);
                $wallet = $userwallet->wallet;
                $transfer_type = $transaction->transfer_type;
                
                
                    // $txn_wl_fee = $wallet->depositFloat($fee,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer Fee',
                    //         'detail' => 'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$transaction->mobile,
                    //         'transaction_id' => $txn_id,
                    //     ]
                    // ]);
                    
                    $txn_wl_fee = $this->WalletCalculation->walletDepositFloat($transaction->user_id,$fee,"Money Transfer Fee",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$transaction->mobile,$txn_id);
                    $balance_update_fee = Transaction::where('uuid', $txn_wl_fee)->update(['balance' => $wallet->balance]);
                    
                    // $txn_wl = $wallet->depositFloat($amount,[
                    //     'meta' => [
                    //         'Title' => 'Money Transfer',
                    //         'detail' => 'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$transaction->mobile,
                    //         'transaction_id' => $txn_id,
                    //     ]
                    // ]);
                    
                    $txn_wl = $this->WalletCalculation->walletDepositFloat($transaction->user_id,$amount,"Money Transfer",'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$transaction->mobile,$txn_id);
                    $balance_update = Transaction::where('uuid', $txn_wl)->update(['balance' => $wallet->balance]);
                    
                    $transaction->status = 2;
                    $transaction->utr = 0;
                    $transaction->response_reason = 'Admin Failed';
                    $transaction->save();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction failed and refund.']);
            }else{
                return response()->json(['success' => false, 'message' => 'Transaction not found.']);
            }
        }
    }
    
    public function dmtTxnSuccess(Request $request)
    {
        $txn_id = $request->transaction_id;
        $user_id = Auth::user()->id;
        if(Auth::user()->user_type == 1){
            $transaction = transactions_dmt::where('transaction_id',$txn_id)->where('status',0)->first();
            if($transaction){
                
                    
                    $transaction->status = 1;
                    $transaction->utr = 0;
                    $transaction->save();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction success.']);
            }else{
                return response()->json(['success' => false, 'message' => 'Transaction not found.']);
            }
        }
    }
    
    public function dmtReceipt($id)
    {
        $user_id = Auth::user()->id;
        $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),
                    'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.transaction_id',$id)
                    ->first();
        if(!$data){
            Session::flash('error', "Unauthorized access!");
            return redirect()->route('dashboard_admin');
        }
        return view('new_pages.retailer.receipt.dmt',compact('data'));
    }
    
    public function getGst(Request $request)
    {
        return view('new_pages.admin.report.gst');
    }
    
    public function gstData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $data = transactions_dmt::select('transactions_dmts.user_id', DB::raw('ROUND(SUM(gst),2) as gst'),DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"))
            ->join('users','users.id','transactions_dmts.user_id')
            ->where('transactions_dmts.status', 1)
            ->whereBetween('transactions_dmts.created_at', array($from, $to))
            ->groupBy('transactions_dmts.user_id')
            ->get();
        
        
            
        return response()->json($data);
    }
    
    public function getTds(Request $request)
    {
        return view('new_pages.admin.report.tds');
    }
    
    public function tdsData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        // $data = user_commissions::select('user_commissions.user_id', DB::raw('ROUND(SUM(tds),2) as tds'),DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"))
        //     ->join('users','users.id','user_commissions.user_id')
        //     ->whereBetween('user_commissions.created_at', array($from, $to))
        //     ->groupBy('user_commissions.user_id')
        //     ->get();
            
        $data = user_commissions::select('user_commissions.user_id','user_commissions.created_at'
            ,'user_commissions.amount' ,'users.dob' ,'user_commissions.total_amount' ,'user_commissions.tds_par' ,'users.email' 
            ,'users.mobile', 'kyc_docs.pan_number', 'kyc_docs.aadhaar_number', 'addresses.address', 'addresses.pincode'
            ,DB::raw('ROUND(tds,2) as tds')
            ,DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name")
            ,DB::raw("CASE WHEN users.user_type = 2 THEN 'Retailer' WHEN users.user_type = 3 THEN 'Distributor' END as user_type"))
            ->join('users','users.id','user_commissions.user_id')
            ->leftjoin('kyc_docs','kyc_docs.user_id','user_commissions.user_id')
            ->leftjoin('addresses','addresses.user_id','user_commissions.user_id')
            ->whereBetween('user_commissions.created_at', array($from, $to))
            ->get();
        
        
            
        return response()->json($data);
    }
    
    public function tdsDataExport(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        // $data = user_commissions::select('user_commissions.user_id', DB::raw('ROUND(SUM(tds),2) as tds'),DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"))
        //     ->join('users','users.id','user_commissions.user_id')
        //     ->whereBetween('user_commissions.created_at', array($from, $to))
        //     ->groupBy('user_commissions.user_id')
        //     ->get();
            
        $data = user_commissions::select('user_commissions.user_id','user_commissions.created_at'
            ,'user_commissions.amount' ,'users.dob' ,'user_commissions.total_amount' ,'user_commissions.tds_par' ,'users.email' 
            ,'users.mobile', 'kyc_docs.pan_number', 'kyc_docs.aadhaar_number', 'addresses.address', 'addresses.pincode'
            ,DB::raw('ROUND(tds,2) as tds')
            ,DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name")
            ,DB::raw("CASE WHEN users.user_type = 2 THEN 'Retailer' WHEN users.user_type = 3 THEN 'Distributor' END as user_type"))
            ->join('users','users.id','user_commissions.user_id')
            ->leftjoin('kyc_docs','kyc_docs.user_id','user_commissions.user_id')
            ->leftjoin('addresses','addresses.user_id','user_commissions.user_id')
            ->whereBetween('user_commissions.created_at', array($from, $to))
            ->get()->toArray();
        
        
            
        return $this->toCsvExport($data,'tdsreport.csv');
    }
    
    public function fundODReport()
    {
        
        return view('new_pages.admin.fundrequest.fund_od');
    }
    
    public function fundODReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        $data = fund_ods::select('fund_ods.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"),'transactions.type')
                ->join('users','users.id','fund_ods.user_id')
                ->join('user_levels','user_levels.user_id','fund_ods.user_id')
                ->join('transactions', 'transactions.uuid', 'fund_ods.wallets_uuid')
                ->where('user_levels.toplevel_id',$user_id)
                ->where('fund_ods.is_od',1)
                ->whereBetween('fund_ods.created_at', array($from, $to))
                ->get();
        
        return response()->json($data);
    }
    
    public function postChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "current_password" => "required",
            "new_password" => "required|string|min:8",
            "confirm_password" => "required|same:new_password"
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
        $user = User::find($user_id);
        $hashedPassword = $user->password;
        $plainPassword = $request->get('current_password');
        $newPassword = $request->get('new_password');
        
        if(Hash::check($plainPassword, $hashedPassword)) {
                $user->password = bcrypt($newPassword);
                $user->save();
                Session::flash('success', 'Password Updated!');
                return redirect()->back();
        }else{
            Session::flash('error', 'password not match');
            return redirect()->back()->withInput();
        }
    }
    
    public function postBankRec(Request $request)
    {
        $data = fund_requests::select('img')
                ->where("id",$request->get("fund_id"))->first();
                // print_r($data);exit;
        return view('new_pages.admin.fundrequest.model.bank_rec_body', compact('data'));
    }
    
    public function onlineFund(Request $request)
    {
        return view('new_pages.admin.fundrequest.online_load');
    }
    
    public function onlineFundData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        $data = fund_onlines::select('fund_onlines.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"))
                ->join('users','users.id','fund_onlines.user_id')
                ->where('fund_onlines.status',1)
                ->whereIn('pg_id',[1,2])
                ->whereBetween('fund_onlines.created_at', array($from, $to))
                ->orderBy('fund_onlines.id','DESC')->get();
            
        return response()->json($data);
    }
    
    public function qrFund(Request $request)
    {
        return view('new_pages.admin.fundrequest.qr_load');
    }
    
    public function qrFundData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        $data = fund_onlines::select('fund_onlines.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"))
                ->join('users','users.id','fund_onlines.user_id')
                ->where('fund_onlines.status',1)
                ->whereIn('pg_id',[3])
                ->whereBetween('fund_onlines.created_at', array($from, $to))
                ->orderBy('fund_onlines.id','DESC')->get();
            
        return response()->json($data);
    }
    
    public function commissionFee(Request $request)
    {
        return view('new_pages.admin.report.commission_fee');
    }
    
    public function commissionFeeData(Request $request)
    {
        $startDate = $this->UserAuth->fetchFromDate($request->start_date);
        $endDate = $this->UserAuth->fetchToDate($request->end_date);
        $users = $request->users;
        // $users = User::with(['transactionsDmt', 'commissions'])->get();
        
        // Fetch users with filtered transactions and commissions
        $usersQuery = User::query();
        if($users == 'ALL') {
            
        }else{
            $usersQuery->where('user_type', $users);
        }
        $users = $usersQuery->with(['transactionsDmt' => function($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->where('status',1);
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }, 'commissions' => function($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }])->get();

        $results = $users->map(function($user) use ($startDate, $endDate) {
            $dmt1_fee = transactions_dmt::where('user_id',$user->id)->where('status',1)->where('event','DMT')->where('api_id','0')->whereBetween('created_at', [$startDate, $endDate])->sum('fee');
            $dmt2_fee = transactions_dmt::where('user_id',$user->id)->where('status',1)->where('event','DMT')->where('api_id','1')->whereBetween('created_at', [$startDate, $endDate])->sum('fee');
            $upi_fee = transactions_dmt::where('user_id',$user->id)->where('status',1)->where('event','SCANNPAY')->whereBetween('created_at', [$startDate, $endDate])->sum('fee');
            return [
                'name' => $user->name.' '.$user->surname,
                'mobile' => $user->mobile,
                'fee_total' => number_format($user->transactionsDmt->sum('fee'),2),
                'dmt1_fee_total' => number_format($dmt1_fee,2),
                'dmt2_fee_total' => number_format($dmt2_fee,2),
                'upi_fee_total' => number_format($upi_fee,2),
                'gst_total' => number_format($user->transactionsDmt->sum('gst'),2),
                'total_commission_total' => number_format($user->commissions->sum('total_amount'),2),
                'commission_total' => number_format($user->commissions->sum('amount'),2),
                'tds_total' => number_format($user->commissions->sum('tds'),2)
            ];
        });

        return response()->json($results);
    }
    
    public function allUsersWallet(Request $request)
    {
        return view('new_pages.admin.report.user_wallet');
    }
    
    public function allUsersWalletData(Request $request)
    {
        
        // Define the date range using Carbon
        $startDate = Carbon::create($request->start_date)->startOfDay();
        $endDate = Carbon::create($request->end_date)->endOfDay();
        
        // Fetch users with their wallets and transactions for the date range
        $users = User::with([
            'wallet',
            'transactions' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        ])->get();
        
        $results = $users->map(function ($user) use ($startDate, $endDate) {
            // Fetch the last transaction of the previous day
            $previousDayTransaction = $user->transactions()
                ->whereDate('created_at','<', $startDate->format('Y-m-d'))
                ->orderBy('id', 'desc')
                ->first();
        
            // Fetch the last transaction in the date range
            $lastTransactionInRange = $user->transactions()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('id', 'desc')
                ->first();
        
            // Calculate totals for deposits and withdrawals in the date range
            $totalDeposits = $user->transactions()
                ->where('type', 'deposit')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');
        
            $totalWithdrawals = $user->transactions()
                ->where('type', 'withdraw')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');
        
            // Get the opening balance (last transaction before the date range)
            $openingBalance = optional($previousDayTransaction)->balance ?? 0;
        
            // Get the closing balance (last transaction in the date range)
            $closingBalance = optional($lastTransactionInRange)->balance ?? 0;
        
            // Get current wallet balance
            $currentBalance = optional($user->wallet)->balance ?? 0;
        
            return [
                'user_name' => $user->name.' '.$user->surname,
                'mobile' => $user->mobile,
                'total_deposits' => $totalDeposits/100,
                'total_withdrawals' => $totalWithdrawals/100,
                'opening_balance' => $openingBalance/100,
                'closing_balance' => $closingBalance/100,
                'current_balance' => $currentBalance/100,
            ];
            
            
        });
        
        return $results;
    }
    
    public function distToRetailer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "current_mobile" => "required",
            "new_mobile" => "required|unique:users,mobile",
            "new_email" => "required|unique:users,email",
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
        
        $new_mobile = $request->new_mobile;
        $new_email = $request->new_email;
        $current_mobile = $request->current_mobile;
        $user = User::where('mobile',$current_mobile)->first();
        $userExists = User::join('addresses', 'users.id', '=', 'addresses.user_id')
                        ->join('shop_details', 'users.id', '=', 'shop_details.user_id')
                        ->join('kyc_docs', 'users.id', '=', 'kyc_docs.user_id')
                        ->where('users.mobile', $current_mobile)->exists();
        if($userExists){
            $passwprd = $this->UserAuth->getPassword(8);
            
            $retailer_mobile = $user->mobile;
            $mobile = $user->mobile;
            $name = $user->name." ".$user->surname;
            $otp = rand(100000, 999999);
            $user_id_s = $this->UserAuth->getUserId("RT");
            $ins = new User();
            $ins->user_id = $user_id_s;
            $ins->name = $user->name;
            $ins->surname = $user->surname;
            $ins->email = $new_email;
            $ins->mobile = $new_mobile;
            $ins->dob = $user->dob;
            $ins->otp = 111444;
            $ins->user_type = 2;
            $ins->status = 1;
            $ins->password = bcrypt($passwprd);
            $ins->user_token = bcrypt(date('ymd').rand(100000, 999999));
            $ins->save();
            
            $Addresses = Addresses::where('user_id',$user->id)->first();
            $insadd = new Addresses();
            $insadd->user_id = $ins->id;
            $insadd->address = $Addresses->address;
            $insadd->pincode = $Addresses->pincode;
            $insadd->city_id = $Addresses->city_id;
            $insadd->state_id = $Addresses->state_id;
            $insadd->save();
            
            $shop_details = shop_details::where('user_id',$user->id)->first();
            $insshop = new shop_details();
            $insshop->user_id = $ins->id;
            $insshop->shop_name = $shop_details->shop_name;
            $insshop->shop_address = $shop_details->shop_address;
            $insshop->latitude = $shop_details->latitude;
            $insshop->longitude = $shop_details->longitude;
            $insshop->shop_img = $shop_details->shop_img;
            $insshop->selfie = $shop_details->selfie;
            $insshop->save();
            
            $kyc_docs = kyc_docs::where('user_id',$user->id)->first();
            $kyc = new kyc_docs();
            $kyc->user_id = $ins->id;
            $kyc->pan_number = $kyc_docs->pan_number;
            $kyc->aadhaar_number = $kyc_docs->aadhaar_number;
            $kyc->status = 1;
            $kyc->pan_image = $kyc_docs->pan_image;
            $kyc->aadhaar_front_image = $kyc_docs->aadhaar_front_image;
            $kyc->aadhaar_back_image = $kyc_docs->aadhaar_back_image;
            $kyc->save();
            
            $ins_level = new user_levels();
            $ins_level->user_id = $ins->id;
            $ins_level->toplevel_id = $user->id;
            $ins_level->save();
            
            $wallet = Wallet::create(['user_id' => $ins->id,'holder_id' => $ins->id]);
            
            $shop = shop_details::where('user_id',$ins->id)->first();
                
            // Define the data to pass to the view
            $data = [
                    'shop_name' => $shop->shop_name,
                    'name' => $user->name." ".$user->surname,
                    'email' => $new_email,
                    'address' => $shop->shop_address,
                    'mobile' => $new_mobile,
                    'place' => 'Rajkot',
                    'territory' => 'Rajkot, Gujarat'
            ];
        
            // Load the Blade view and generate the PDF
            $pdf = PDF::loadView('pdf.myPDF', $data);
        
            // Define the file path and name
            $filePath = public_path('Agreement/agreement_'.$user_id_s.'.pdf');
        
            // Ensure the directory exists
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0777, true);
            }
        
            // Save the PDF to the specified path
            $pdf->save($filePath);
            
            $text = urlencode("Your account at Payrite has been successfully created! Your login id $new_mobile and password $passwprd. https://payritepayment.in/");
            $api_response = $this->ApiCalls->smsgatewayhubGetCall($new_mobile,$text,1207170972078183783);
        
            Session::flash('success', 'Created successfully');
            return redirect()->route('dashboard_distributor');
        }else{
            Session::flash('error', 'User Not Exists');
            return redirect()->route('dashboard_distributor');
        }
    }
    
    public function accountVeirfy(Request $request)
    {
        return view('new_pages.admin.report.account_verify');
    }
    
    public function accountVeirfyData(Request $request)
    {
        $startDate = $this->UserAuth->fetchFromDate($request->start_date);
        $endDate = $this->UserAuth->fetchToDate($request->end_date);
        $users = $request->users;
        // $users = User::with(['transactionsDmt', 'commissions'])->get();
        
        // Fetch users with filtered transactions and commissions
        $usersQuery = User::query();

        $users = $usersQuery->with(['transactionsDmt' => function($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->where('status',1);
                $query->where('event','ACCOUNTVERIFY');
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }])->get();

        $results = $users->map(function($user) use ($startDate, $endDate) {
            return [
                'name' => $user->name.' '.$user->surname,
                'mobile' => $user->mobile,
                'fee_total' => number_format($user->transactionsDmt->sum('amount'),2),
                
            ];
        });

        return response()->json($results);
    }
    
    public function aepsReport(Request $request)
    {
        return view('new_pages.admin.report.aeps');
    }
    
    public function aepsReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        $data = transactions_aeps::select('transactions_aeps.*',DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"))
                ->join('users','users.id','transactions_aeps.user_id')
                ->whereBetween('transactions_aeps.created_at', array($from, $to))
                ->orderBy('transactions_aeps.id','DESC')->get();
            
        return response()->json($data);
    }
    
    public function businessReport(Request $request)
    {
        $users = User::where('user_type',2)->get();
        return view('new_pages.admin.report.business_report',compact('users'));
    }
    
    public function businessReportData(Request $request)
    {
        $user_id = $request->user_ids;
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
                        'created_at' => $data->created_at,
                        'txn_type' => $data->txn_type,
                        'type' => $data->type,
                        'status' => $status,
                        'mobile' => $data->mobile,
                        'ben_name' => $data->ben_name,
                        'account_no' => $data->account_no,
                        'transaction_id' => $data->transaction_id,
                        'user_name' => $data->user_name,
                        'shop_name' => $data->shop_name,
                        'balance' => $data->balance,
                        'narration' => $data->narration,
                        'utr' => $data->utr,
                        'amount' => $data->amount,
                        'fee' => $data->fee,
                        'tds' => $data->tds,
                        'gst' => $data->gst,
                        'commission' => $commission,
                        'commission_tds' => $commission_tds,
                        'final_commission' => $final_commission
                    ];
            })
            ->toArray();
            
        // print_r($data);
        return $this->toCsvExport($data,'user_business.csv');
    }
    
    public function billDmtRefundRequest($id)
    {
        $txn = transactions_dmt::where('transaction_id',$id)->first();
        return view('new_pages.admin.report.refund_otp',compact('txn'));
            
        
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
        $txn = transactions_dmt::where('transaction_id',$txnid)->first();
        $user = User::find($txn->user_id);
        $token = $this->UserAuth->createAuthUserToken($user->mobile);
        $url = "https://user.payritepayment.in/api/v1/bill-dmt/refund-otp?token=$token&user_mobile=".$user->mobile."&transaction_id=$txnid&otp=$otp";
        $response = $this->ApiCalls->payritePostCall($url);
        $decode = json_decode($response);
        if($decode->success){
            Session::flash('success', 'Refund Request Submited.');
            return redirect()->route('dashboard_admin');
        }else{
            Session::flash('error', 'Something Wrong, Please Connect With Admin');
            return redirect()->route('dashboard_admin');
        }
    }
    
    //enf of Fin. year
    public function gettotalTdsDataExport(Request $request)
    {
        
        return view('new_pages.admin.report.totaltds');
    }
    
    public function totalTdsDataExport(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        // $data = user_commissions::select('user_commissions.user_id', DB::raw('ROUND(SUM(tds),2) as tds'),DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"))
        //     ->join('users','users.id','user_commissions.user_id')
        //     ->whereBetween('user_commissions.created_at', array($from, $to))
        //     ->groupBy('user_commissions.user_id')
        //     ->get();
            
        $data = user_commissions::select(
            'user_commissions.user_id',
            'users.dob',
            'users.user_id as user_no',
            'users.email',
            'users.user_id as user_identifier',
            'users.mobile',
            'kyc_docs.pan_number',
            'kyc_docs.aadhaar_number',
            'addresses.address',
            'addresses.pincode',
            DB::raw('ROUND(SUM(tds),2) as tds'),
            DB::raw('ROUND(SUM(user_commissions.amount),2) as amount'),
            DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"),
            DB::raw("CASE WHEN users.user_type = 2 THEN 'Retailer' WHEN users.user_type = 3 THEN 'Distributor' END as user_type")
        )
        ->join('users', 'users.id', '=', 'user_commissions.user_id')
        ->leftjoin('kyc_docs', 'kyc_docs.user_id', '=', 'user_commissions.user_id')
        ->leftjoin('addresses', 'addresses.user_id', '=', 'user_commissions.user_id')
        ->whereBetween('user_commissions.created_at', array($from, $to))
        ->groupBy(
            'user_commissions.user_id',
            'users.dob',
            'users.user_id',
            'users.email',
            'users.user_id',
            'users.mobile',
            'kyc_docs.pan_number',
            'kyc_docs.aadhaar_number',
            'addresses.address',
            'addresses.pincode', 
            'users.name',
            'users.surname',
            'users.user_type'
        )
        ->get();
        
        $data = $data->map(function($data) {
            return [
                    'Challan Serial No.' => 'NA',
                    'Section Code' => '194H',
                    'Deductee Code' => '02-Other than Companies',
                    'Permanent Account Number (PAN) of deductee' => $data->pan_number,
                    'Deductee Ref. No. / PAN Ref. No.' => 'NA',
                    'Name of Deductee / )User id Retailer Or Dist)' => $data->user_no,
                    'Deductee  Address1' => $data->address,
                    'Deductee  Address2' => '',
                    'Deductee  Address3' => '',
                    'Deductee  Address4' => '',
                    'Deductee  Address5' => '',
                    'Deductee State' => '',
                    'Deductee  PIN' => 'NA',
                    'Amount of Payment' => $data->amount,
                    'Date on which Amount paid / credited' => '31/03/2025',
                    'Rate at which Tax deducted' => '2%',
                    'Surcharge Rate' => 'NA',
                    'Health and Education Cess Rate' => 'NA',
                    'Amount of Tax deducted' => $data->tds,
                    'Surcharge Amount' => 'NA',
                    'Health and Education Cess Amount' => 'NA',
                    'Total Tax Deposited' => '',
                    'Date on which tax deducted' => '31/03/2025',
                    'Reason for Non-deduction/Lower Deduction/Deduction at Higher Rate, if any' => 'A',
                    'Certificate number' => 'NA',
                ];
        })->toArray();
        
        // print_r($data);    
        return $this->toCsvExport($data,'tdsreport.csv');
    }
    
    //MARCH ENDING
    public function getExportConsolidatedAccountStatement(Request $request)
    {
        
        return view('new_pages.admin.report.consolidated');
    }
    
    public function exportConsolidatedAccountStatement(Request $request)
    {
        $page = 1;
        $perPage = 50;
        // $from = $this->UserAuth->fetchFromDate('01-04-2025');
        // $to = $this->UserAuth->fetchToDate('30-04-2025');
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        // Prepare data array
        $data = [];
        
        // Get all distributors (user_type = 3)
        $distributors = User::with('getRetailers')
                ->where('user_type', 3)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();
        // print_r($distributors[2]->getRetailers);
        // exit;
        $dist_count = 0;
        foreach ($distributors as $distributor) {
            $dist_count++;
            
            // Get load balance (you may need to adjust this based on your actual database structure)
            $totalLoadBalance = $this->getDistributorLoadBalance($distributor->id,$from, $to);
            
            // Get commission credited for distributor
            $totalCommissionCredited = user_commissions::where('user_id', $distributor->id)->whereBetween('created_at', array($from, $to))->sum('amount');
            
            // Other credit (placeholder - adjust as needed)
            $otherCreditdist = $this->otherCreditDist($distributor->id,$from, $to);
            $otherCreditdist = $otherCreditdist / 100;
            // Calculate total credit
            
            
            // Get credits given to retailers
            // $creditsToRetailers = $this->getCreditsGivenToRetailers($distributor->id);
            $creditsToRetailers = Transaction::where('payable_id', $distributor->id)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta_title', 'Fund OD') // Laravel 5.6+ syntax
                    ->where('type', 'withdraw')
                    ->sum('amount');
            $creditsToRetailers = abs($creditsToRetailers/100);
            
            //OD reversal Admin
            $od_reversel_admin = Transaction::where('payable_id', $distributor->id)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta_title', 'Admin Fund Transfer') // Laravel 5.6+ syntax
                    ->where('type', 'withdraw')
                    ->sum('amount');
            $od_reversel_admin = abs($od_reversel_admin/100);
            
            //OD reversal
            $od_reversel = Transaction::where('payable_id', $distributor->id)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta_title', 'Fund OD') // Laravel 5.6+ syntax
                    ->where('type', 'deposit')
                    ->sum('amount');
            $od_reversel = abs($od_reversel/100);
            
            //FEE
            $pgfee = fund_onlines::where('user_id', $distributor->id)
            ->where('status',1)
            ->whereBetween('created_at', array($from, $to))
            ->sum('fee');
            
            // Calculate closing balance
            $closing_data = Transaction::select('transactions.*',DB::raw('ROUND(transactions.balance / 100, 2) as balance'))
                ->where('payable_id',$distributor->id)
                ->whereBetween('created_at', array($from, $to))
                ->orderBy('id', 'desc')->first();
            if($closing_data){
                $distClosingBalance = $closing_data->balance;
            }else{
                $distClosingBalance = 0;
            }
            
            $opning_data = Transaction::select('transactions.*',DB::raw('ROUND(transactions.balance / 100, 2) as balance'))
                ->where('payable_id',$distributor->id)
                ->whereBetween('created_at', array($from, $to))
                ->orderBy('id', 'asc')->first();
            if($opning_data){
                if($opning_data->type == 'deposit'){
                    $opningBalance = $opning_data->balance - ($opning_data->amount / 100);
                }else{
                    $opningBalance = $opning_data->balance + ($opning_data->amount / 100);
                }
                    
            }else{
                    $opningBalance = 0;
            }
            
            $total_debit = $creditsToRetailers + $pgfee;
            $totalCreditdist = $totalLoadBalance['fund_requests'] + $totalLoadBalance['fund_onlines'] + $totalCommissionCredited + $totalLoadBalance['fund_ods'] + $otherCreditdist + $od_reversel;
            
            
            // Get retailers under this distributor 
            // Assuming retailers have a column like 'distributor_id' or similar
            $retailers = $distributor->getRetailers;
            $total_retailes = count($retailers);
            $retailerCount = 0;
            $distributorRow = [];
            
            // First row for distributor with first retailer
            if ($retailers->count() > 0) {
                foreach ($retailers as $retailer) {
                    $retailerCount++;
                    
                    // For first retailer, include distributor data
                    if ($retailerCount === 1) {
                        $distributorRow = [
                            'Distributor Name' => $distributor->name,
                            'Distributor ID' => $distributor->user_id,
                            'Dist OPning' => $opningBalance,
                            'Total Load Balance Thru Fund Request' => $totalLoadBalance['fund_requests'],
                            'Load Balance Thru Online' => $totalLoadBalance['fund_onlines'],
                            'Total Commission Credited Dist' => $totalCommissionCredited,
                            'Advance OD' => $totalLoadBalance['fund_ods'],
                            'OD Reversal Credit' => $od_reversel,
                            'Other Credit Dist' => $otherCreditdist,
                            'Total Credit Dist' => $totalCreditdist,
                            'Total Credits given to Retailer' => $creditsToRetailers,
                            'Total Debit Dist' => $total_debit,
                            'Fee' => $pgfee,
                            'Advance OD Reversal' => $od_reversel_admin,
                            'Dist Closing Balance' => $distClosingBalance,
                            'Total Retailer' => $total_retailes,
                        ];
                    } else {
                        // For subsequent retailers, leave distributor fields empty
                        $distributorRow = [
                            'Distributor Name' => '',
                            'Distributor ID' => '',
                            'Dist OPning' => '',
                            'Total Load Balance Thru Fund Request' => '',
                            'Load Balance Thru Online' => '',
                            'Total Commission Credited Dist' => '',
                            'Advance OD' => '',
                            'OD Reversal Credit' => '',
                            'Other Credit Dist' => '',
                            'Total Credit Dist' => '',
                            'Total Credits given to Retailer' => '',
                            'Total Debit Dist' => '',
                            'Fee' => '',
                            'Advance OD Reversal' => '',
                            'Dist Closing Balance' => '',
                            'Total Retailer' => '',
                        ];
                    }
                    
                    
                    // Get retailer data
                    $retailerData = $this->getRetailerData($retailer->user_id, $distributor->toplevel_id, $from, $to);
                    
                    // Combine distributor and retailer data
                    $combinedRow = array_merge($distributorRow, $retailerData);
                    $data[] = $combinedRow;
                    $current_dist[] = $combinedRow;
                    
                }
                
                
                // 
                // Add retailer totals row
                $retailerTotals = $this->getRetailerTotals($current_dist);
                $emptyDistributorRow = [
                    'Distributor Name' => '',
                            'Distributor ID' => '',
                            'Dist OPning' => '',
                            'Total Load Balance Thru Fund Request' => '',
                            'Load Balance Thru Online' => '',
                            'Total Commission Credited Dist' => '',
                            'Advance OD' => '',
                            'OD Reversal Credit' => '',
                            'Other Credit Dist' => '',
                            'Total Credit Dist' => '',
                            'Total Credits given to Retailer' => '',
                            'Total Debit Dist' => '',
                            'Fee' => '',
                            'Advance OD Reversal' => '',
                            'Dist Closing Balance' => '',
                            'Total Retailer' => '',
                ];
                $data[] = array_merge($emptyDistributorRow, $retailerTotals);
                unset($current_dist);
                // Add empty row as separator if this isn't the last distributor
                if ($distributor->id !== $distributors->last()->id) {
                    $emptyRow = array_fill_keys(array_keys(array_merge($distributorRow, $retailerData)), '');
                    $data[] = $emptyRow;
                }
            }
        }
        
        return $this->toCsvExport($data, 'consolidated_account_statement_summary_report.csv');
    }
    
    /**
     * Get retailer data
     */
    private function getRetailerData($retailerId, $distributorId, $from, $to)
    {
        $retailer = User::find($retailerId);
        if($retailer){
            
        
        // Get received credit from distributor (adjust based on your table structure)
        // $receivedCredit = 0;
        // $receivedCredit = fund_ods::whereHas('walletTransactionDeposit', function($query) use ($retailerId) {
        //             $query->where('type', 'deposit')->where('payable_id', $retailerId);
        //         })
        //         ->where('user_id', $retailerId)
        //         ->whereBetween('created_at', [$from, $to])
        //         ->sum('amount');
        // $fund_ods_withdraw = 0;
        // $fund_ods_withdraw = fund_ods::whereHas('walletTransactionDeposit', function($query) use ($retailerId) {
        //             $query->where('type', 'withdraw')->where('payable_id', $retailerId);
        //         })
        //         ->where('user_id', $retailerId)
        //         ->whereBetween('created_at', [$from, $to])
        //         ->sum('amount');
        $receivedCredit =Transaction::where('payable_id', $retailerId)->where('meta_title','LIKE', '%OD%')->where('type', 'deposit')->whereBetween('created_at', [$from, $to])->sum('amount');
        $receivedCredit = abs($receivedCredit/100);
        $fund_ods_withdraw =Transaction::where('payable_id', $retailerId)->where('meta_title','LIKE', '%OD%')->where('type', 'withdraw')->whereBetween('created_at', [$from, $to])->sum('amount');
        $fund_ods_withdraw = abs($fund_ods_withdraw/100);
        
        // $refund_amount = Transaction::where('payable_id', $retailerId)->where('meta_detail','LIKE', '%Refund%')->where('type', 'deposit')->whereBetween('created_at', [$from, $to])->sum('amount');
        // $refund_amount = $refund_amount / 100;
                
        // $receivedCredit = $this->getReceivedCreditFromDistributor(20, $from, $to);
        
        // Get QR Credit and Credit Card Load from fund_onlines table
        // $qrCredit = 0;
        $qrCredit = fund_onlines::where('user_id', $retailerId)
            ->where('status',1)
            ->whereBetween('created_at', array($from, $to))
            ->where('pg_id', '3') // Adjust based on your actual data structure
            ->sum('amount');
        $qrCreditfee = fund_onlines::where('user_id', $retailerId)
            ->where('status',1)
            ->whereBetween('created_at', array($from, $to))
            ->where('pg_id', '3') // Adjust based on your actual data structure
            ->sum('fee');
        
        // $creditCardLoad = 0;
        $creditCardLoad = fund_onlines::where('user_id', $retailerId)
            ->whereBetween('created_at', array($from, $to))
            ->where('pg_id', '!=', '3') // Adjust based on your actual data structure
            ->where('status',1)
            ->sum('amount_txn');
        
        // $pgfee = 0;
        $pgfee = fund_onlines::where('user_id', $retailerId)
            ->where('status',1)
            ->where('pg_id', '!=', '3')
            ->whereBetween('created_at', array($from, $to))
            ->sum('fee');
        
        // Get Commission Credit from user_commissions table
        // $commissionCredit = 0;
        $commissionCredit = user_commissions::where('user_id', $retailerId)
            ->whereBetween('created_at', array($from, $to))
            ->sum('amount');
        
        // Get AEPS Credit from transactions_aeps table
        // $aepsCredit = 0;
        $aepsCredit = transactions_aeps::where('user_id', $retailerId)
            ->where('status',1)
            ->where('event', 'AEPSTXN') // Adjust based on your actual data structure
            ->whereBetween('created_at', array($from, $to))
            ->sum('amount');
        
        // Load fund request (placeholder - adjust as needed)
        // $loadFundRequest = 0;
        $loadFundRequest = fund_requests::where('user_id', $retailerId)
            ->whereBetween('created_at', array($from, $to))
            ->where('status', 1)
            ->sum('amount');
        // $loadFundRequest = 0;
        
        // Other credit (placeholder - adjust as needed)
        $otherCredit = 0;
        
        // Calculate total credit
        // $totalCredit = $receivedCredit + $qrCredit + $commissionCredit + $aepsCredit + $creditCardLoad + $loadFundRequest + $otherCredit;
        $totalCredit = Transaction::where('payable_id', $retailerId)->where('type', 'deposit')->whereBetween('created_at', [$from, $to])->sum('amount');
        $totalCredit = abs($totalCredit/100);
        // Get DMT Debit and Account Verification Debit from transactions_dmts table
        // $dmtDebit = 0;
        
        //IMPS
        $find_string = "Debit_Retailer_".$retailer->mobile."_DMT_Remittance_IMPS_Amount";
        $dmtDebit = Transaction::where('payable_id', $retailerId)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta_detail', 'LIKE', '%'.$find_string.'%') // Laravel 5.6+ syntax
                    ->where('type', 'withdraw')
                    ->sum('amount');
        $dmtDebit = abs($dmtDebit/100);
        
        //NEFT
        $find_string_neft = "Debit_Retailer_".$retailer->mobile."_DMT_Remittance_NEFT_Amount";
        $dmtDebit_neft = Transaction::where('payable_id', $retailerId)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta_detail', 'LIKE', '%'.$find_string_neft.'%') // Laravel 5.6+ syntax
                    ->where('type', 'withdraw')
                    ->sum('amount');
        $dmtDebit_neft = abs($dmtDebit_neft/100);
        
        $dmtDebit = $dmtDebit + $dmtDebit_neft;
        
        // $dmtfee = 0;    
        //IMPS FEE
        $find_string = "Debit_Retailer_".$retailer->mobile."_DMT_Remittance_IMPS_CustomerFee";
        $dmtfee = Transaction::where('payable_id', $retailerId)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta_detail', 'LIKE', '%'.$find_string.'%') // Laravel 5.6+ syntax
                    ->where('type', 'withdraw')
                    ->sum('amount');
        $dmtfee = abs($dmtfee/100);
        
        //NEFT FEE
        $find_string_neft = "Debit_Retailer_".$retailer->mobile."_DMT_Remittance_NEFT_CustomerFee";
        $dmtfee_neft = Transaction::where('payable_id', $retailerId)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta_detail', 'LIKE', '%'.$find_string_neft.'%') // Laravel 5.6+ syntax
                    ->where('type', 'withdraw')
                    ->sum('amount');
        $dmtfee_neft = abs($dmtfee_neft/100);
        
        $dmtfee = $dmtfee + $dmtfee_neft;
            
        // IMPS REFUND
        $find_string = "Refund_Retailer_".$retailer->mobile."_DMT_Remittance_IMPS_Amount";
        $dmtDebitrefund = Transaction::where('payable_id', $retailerId)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta_detail', 'LIKE', '%'.$find_string.'%') // Laravel 5.6+ syntax
                    ->where('type', 'deposit')
                    ->sum('amount');
        $dmtDebitrefund = abs($dmtDebitrefund/100);
        
        //NEFT
        $find_string_neft = "Refund_Retailer_".$retailer->mobile."_DMT_Remittance_NEFT_Amount";
        $dmtDebitrefund_neft = Transaction::where('payable_id', $retailerId)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta_detail', 'LIKE', '%'.$find_string_neft.'%') // Laravel 5.6+ syntax
                    ->where('type', 'deposit')
                    ->sum('amount');
        $dmtDebitrefund_neft = abs($dmtDebitrefund_neft/100);
        
        $dmtDebitrefund = $dmtDebitrefund + $dmtDebitrefund_neft;
        
        //IMPS FEE REFUND
        $find_string = "Refund_Retailer_".$retailer->mobile."_DMT_Remittance_IMPS_CustomerFee";
        $dmtfeerefund = Transaction::where('payable_id', $retailerId)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta_detail', 'LIKE', '%'.$find_string.'%') // Laravel 5.6+ syntax
                    ->where('type', 'deposit')
                    ->sum('amount');
        $dmtfeerefund = abs($dmtfeerefund/100);
        
        //NEFT FEE
        $find_string_neft = "Refund_Retailer_".$retailer->mobile."_DMT_Remittance_NEFT_CustomerFee";
        $dmtfeerefund_neft = Transaction::where('payable_id', $retailerId)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta_detail', 'LIKE', '%'.$find_string_neft.'%') // Laravel 5.6+ syntax
                    ->where('type', 'deposit')
                    ->sum('amount');
        $dmtfeerefund_neft = abs($dmtfeerefund_neft/100);
        
        $dmtfeerefund = $dmtfeerefund + $dmtfeerefund_neft;
        
        //RECHARGE
        $find_string = "Debit_recharge_";
        $rechargeDebit = Transaction::where('payable_id', $retailerId)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta_detail', 'LIKE', '%'.$find_string.'%') // Laravel 5.6+ syntax
                    ->where('type', 'withdraw')
                    ->sum('amount');
        $rechargeDebit = abs($rechargeDebit/100);
        
        //RECHARGE REFUND
        $find_string = "Refund_recharge_";
        $rechargeCredit = Transaction::where('payable_id', $retailerId)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta_detail', 'LIKE', '%'.$find_string.'%') // Laravel 5.6+ syntax
                    ->where('type', 'deposit')
                    ->sum('amount');
        $rechargeCredit = abs($rechargeCredit/100);
        
        // $accVerificationDebit = 0;
        $accVerificationDebit = transactions_dmt::where('user_id', $retailerId)
            ->where('event', 'ACCOUNTVERIFY') // Adjust based on your actual data structure
            ->where('status', 1) // Adjust based on your actual data structure
            ->whereBetween('created_at', array($from, $to))
            ->where('status',1)
            ->sum('amount');
        
        // Ekyc debit (placeholder - adjust as needed)
        // $ekycDebit = Transaction::where('meta','LIKE','%Debit_ekyc_fee%')
        //         ->where('payable_id',$retailerId)
        //         ->whereBetween('created_at', array($from, $to))
        //         ->sum('amount');
        $ekycDebit = Transaction::where('payable_id', $retailerId)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta->meta->Title', 'eKYC FEE') // Laravel 5.6+ syntax
                    ->where('type', 'withdraw')
                    ->sum('amount');
        $ekycDebit = abs($ekycDebit/100);
        
        $ekycrefund = Transaction::where('payable_id', $retailerId)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta->meta->Title', 'eKYC FEE') // Laravel 5.6+ syntax
                    ->where('type', 'deposit')
                    ->sum('amount');
        $ekycrefund = abs($ekycrefund/100);
        
        // Other debit (placeholder - adjust as needed)
        $otherDebit = $pgfee;
        
        // Calculate total debit
        // $totalDebit = $dmtDebit + $accVerificationDebit + $ekycDebit + $otherDebit;
        $totalDebit = Transaction::where('payable_id', $retailerId)->where('type', 'withdraw')->whereBetween('created_at', [$from, $to])->sum('amount');
        $totalDebit = abs($totalDebit/100);
        // Calculate closing balance
        // $closing_data = 0;
        $closing_data = Transaction::select('transactions.*',DB::raw('ROUND(transactions.balance / 100, 2) as balance'))
                ->where('payable_id',$retailerId)
                ->whereBetween('created_at', array($from, $to))
                ->orderBy('id', 'desc')->first();
        if($closing_data){
                $closingBalance = $closing_data->balance;
            }else{
                $closingBalance = 0;
            }
        
        $opning_data = Transaction::select('transactions.*',DB::raw('ROUND(transactions.balance / 100, 2) as balance'))
                ->where('payable_id',$retailerId)
                ->whereBetween('created_at', array($from, $to))
                ->orderBy('id', 'asc')->first();
        if($opning_data){
            if($opning_data->type == 'deposit'){
                $opningBalance = $opning_data->balance - ($opning_data->amount / 100);
            }else{
                $opningBalance = $opning_data->balance + ($opning_data->amount / 100);
            }
                
        }else{
                $opningBalance = 0;
        }
        
        return [
            'Retailer Name' => $retailer->name,
            'Retailer ID' => $retailer->user_id,
            'OPning' => $opningBalance,
            'Load Fund Request' => $loadFundRequest,
            'Credit Fund_OD' => $receivedCredit,
            'Debit Fund_OD' => $fund_ods_withdraw,
            'QR Credit' => $qrCredit,
            'AEPS Credit' => $aepsCredit,
            'PG Load' => $creditCardLoad,
            'eKYC Refund' => $ekycrefund,
            'DMT Refund' => $dmtDebitrefund,
            'DMT Fee Refund' => $dmtfeerefund,
            'Recharge Refund' => $rechargeCredit,
            'Account Verification Refund' => 0,
            'Commission Credit' => $commissionCredit,
            'Total Credit' => $totalCredit,
            'QR Credit Fee' => $qrCreditfee,
            'Ekyc Debit' => $ekycDebit,
            'Account Verification Debit' => $accVerificationDebit,
            'DMT Debit' => $dmtDebit,
            'DMT FEE' => $dmtfee,
            'Recharge Debit' => $rechargeDebit,
            'Total Debit' => $totalDebit,
            'Other Credit' => $otherCredit,
            'PG FEE' => $pgfee,
            'Closing Balance' => $closingBalance,
        ];
        }else{
            return [
            'Retailer Name' => '',
            'Retailer ID' => '',
            'OPning' => 0,
            'Load Fund Request' => 0,
            'Credit Fund_OD' => 0,
            'QR Credit' => 0,
            'AEPS Credit' => 0,
            'PG Load' => 0,
            'eKYC Refund' => 0,
            'DMT Refund' => 0,
            'DMT Fee Refund' => 0,
            'Recharge Refund' => 0,
            'Account Verification Refund' => 0,
            'Commission Credit' => 0,
            'Total Credit' => 0,
            'QR Credit Fee' => 0,
            'Ekyc Debit' => 0,
            'Account Verification Debit' => 0,
            'DMT Debit' => 0,
            'Recharge Debit' => 0,
            'DMT FEE' => 0,
            'Total Debit' => 0,
            'Other Credit' => 0,
            'PG FEE' => 0,
            'Closing Balance' => 0,
        ];
        }
    }
    
    /**
     * Get retailer totals
     */
    private function getRetailerTotals($retailerDataArray) {
        
        $sums = [];
        
        // Initialize sum array with the same keys but zero values
        if (!empty($retailerDataArray)) {
            $keys = array_keys($retailerDataArray[0]);
            foreach ($keys as $key) {
                // Skip non-numeric fields (like names and IDs)
                if (!in_array($key, ['Retailer Name', 'Retailer ID', 'Distributor Name', 'Distributor ID'])) {
                    $sums[$key] = 0;
                }else{
                    $sums[$key] = '';
                }
            }
        }
        
        // Calculate sums for each key
        foreach ($retailerDataArray as $retailerData) {
            foreach ($retailerData as $key => $value) {
                // Only sum numeric values and skip certain fields
                if (!in_array($key, ['Retailer Name', 'Retailer ID', 'Distributor Name', 'Distributor ID']) && is_numeric($value)) {
                    $sums[$key] += $value;
                }else{
                    $sums[$key] = '';
                }
            }
        }
        
        return $sums;
    }
    
    /**
     * Get distributor load balance
     * Placeholder function - adjust as needed based on your database structure
     */
    private function getDistributorLoadBalance($distributorId, $from, $to)
    {
        // This is a placeholder. You may need to adjust this based on your actual database structure
        // For example, you might have a 'wallets' table or a 'balances' table
        $fund_requests = fund_requests::where('user_id', $distributorId)
            ->whereBetween('created_at', array($from, $to))
            ->where('status', 1)
            ->sum('amount');
        
        $fund_ods = Transaction::where('payable_id', $distributorId)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('meta_title', 'Admin Fund Transfer') // Laravel 5.6+ syntax
                    ->where('type', 'deposit')
                    ->sum('amount');
        $fund_ods = abs($fund_ods/100);    
        // $fund_ods = fund_ods::with('walletTransactionDeposit')->where('user_id', $distributorId)
        //     ->whereBetween('created_at', array($from, $to))
        //     ->sum('amount');
            
        $fund_ods_withdraw = fund_ods::with('walletTransactionWithdraw')->where('user_id', $distributorId)
            ->whereBetween('created_at', array($from, $to))
            ->sum('amount');
        
        $fund_onlines = fund_onlines::where('user_id', $distributorId)
            ->whereBetween('created_at', array($from, $to))
            ->where('status', 1)
            ->sum('amount');    
            
        $total = $fund_requests + $fund_ods - $fund_ods_withdraw + $fund_onlines;
        $data = ["fund_requests"=>$fund_requests,"fund_ods"=>$fund_ods,"fund_ods_withdraw"=>$fund_ods_withdraw,"fund_onlines"=>$fund_onlines,"total"=>$total];
        return $data;
    }
    
    private function otherCreditDist($distributorId, $from, $to)
    {
        $independent_transactions = Transaction::where('payable_id', $distributorId)
        ->whereBetween('created_at', [$from, $to])
        ->whereNotIn('uuid', function($query) use ($distributorId, $from, $to) {
            // Subquery to get UUIDs from fund_ods
            $query->select('wallets_uuid')
                ->from('fund_ods')
                ->where('user_id', $distributorId)
                ->whereBetween('created_at', [$from, $to]);
        })
        ->whereNotIn('uuid', function($query) use ($distributorId, $from, $to) {
            // Subquery to get UUIDs from fund_onlines
            $query->select('wallets_uuid')
                ->from('fund_onlines')
                ->where('user_id', $distributorId)
                ->whereBetween('created_at', [$from, $to])
                ->where('status', 1);
        })
        ->whereNotIn('uuid', function($query) use ($distributorId, $from, $to) {
            // Subquery to get UUIDs from fund_requests
            $query->select('wallets_uuid')
                ->from('fund_requests')
                ->where('user_id', $distributorId)
                ->whereBetween('created_at', [$from, $to])
                ->where('status', 1);
        })
        ->whereNotIn('uuid', function($query) use ($distributorId, $from, $to) {
            // Subquery to get UUIDs from user_commissions
            $query->select('wallets_uuid')
                ->from('user_commissions')
                ->where('user_id', $distributorId)
                ->whereBetween('created_at', [$from, $to]);
        })
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.meta.Title')) != 'Fund OD'")
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.meta.Title')) != 'Fund Request'")
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.meta.Title')) != 'Commission'")
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.meta.Title')) != 'Online Load Phonepe'")
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.meta.Title')) != 'Online Load Phonepe Fee'")
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.meta.Title')) != 'Fund OD Reverse'")
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.meta.Title')) != 'Online Load Fee Airpay'")
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.meta.Title')) != 'Commission Reverse'")
        // ->get();
        ->sum('amount');
        // echo "<br>";
        // echo $distributorId;
        // echo "<br>";
        // print_r($independent_transactions);exit;
        
        // $independent_transactions1 = Transaction::where('payable_id', $distributorId)
        // ->whereBetween('created_at', [$from, $to])->where('type', 'withdraw')->sum('amount');
        // $independent_transactions2 = Transaction::where('payable_id', $distributorId)
        // ->whereBetween('created_at', [$from, $to])->where('type', 'deposit')->sum('amount');
        // print_r($independent_transactions2 + $independent_transactions1);exit;
        return $independent_transactions;
    }
    
    /**
     * Get credits given to retailers
     * Placeholder function - adjust as needed based on your database structure
     */
    private function getCreditsGivenToRetailers($distributorId)
    {
        // This is a placeholder. You may need to adjust this based on your actual database structure
        // For example, you might have a 'transactions' table with a 'type' column
        return 0;
    }
    
    /**
     * Get received credit from distributor
     * Placeholder function - adjust as needed based on your database structure
     */
    private function getReceivedCreditFromDistributor($retailerId, $from, $to)
    {
        // This is a placeholder. You may need to adjust this based on your actual database structure
        // For example, you might have a 'transactions' table with a 'from_user_id' and 'to_user_id' column
        $fund_ods = fund_ods::whereHas('walletTransactionDeposit', function($query) use ($retailerId) {
                    $query->where('type', 'deposit')->where('payable_id', $retailerId);
                })
                ->where('user_id', $retailerId)
                ->whereBetween('created_at', [$from, $to])
                ->sum('amount');
        $fund_ods_withdraw = fund_ods::whereHas('walletTransactionDeposit', function($query) use ($retailerId) {
                    $query->where('type', 'withdraw')->where('payable_id', $retailerId);
                })
                ->where('user_id', $retailerId)
                ->whereBetween('created_at', [$from, $to])
                ->sum('amount');
        print_r($fund_ods_withdraw);
        print_r("====");
        print_r($fund_ods);
        print_r("====");
        print_r($fund_ods - $fund_ods_withdraw);exit;
        return $fund_ods;
    }
    
    public function rechargeReport(Request $request)
    {
        return view('new_pages.admin.report.recharge');
    }
    
    public function rechargeReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        $data = transactions_recharges::select('transactions_recharges.*',
                DB::raw("CONCAT(users.name, ' ', users.surname) as user_name"),'ace_operators.name as op_name', 
                DB::raw("CONCAT(dist_user.name, ' ', dist_user.surname) as dist_name"),'users.mobile as retailer_mobile','shop_details.shop_name','dist_user.mobile as dist_mobile')
                ->join('ace_operators','ace_operators.id','transactions_recharges.op_id')
                ->join('users','users.id','transactions_recharges.user_id')
                ->join('user_levels','user_levels.user_id','users.id')
                ->join('users as dist_user','dist_user.id','user_levels.toplevel_id')
                ->join('shop_details','shop_details.user_id','transactions_recharges.user_id')
                ->whereBetween('transactions_recharges.created_at', array($from, $to))
                ->orderBy('transactions_recharges.id','DESC')->get();
            
        return response()->json($data);
    }
    
    public function ccpayoutReport(Request $request)
    {
        return view('new_pages.admin.report.ccpayout');
    }
    
    public function ccpayoutReportData(Request $request)
    {
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        $user_id = Auth::user()->id;
        $data = transactions_cc::with('senders','beneficiaries')->select('transactions_ccs.*',
            DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"),'users.mobile as retailer_mobile','shop_details.shop_name',
            DB::raw("CONCAT(dist_user.name, ' ', dist_user.surname) as dist_name"),'dist_user.mobile as dist_mobile')
            ->join('users','users.id','transactions_ccs.user_id')
            ->join('shop_details','shop_details.user_id','transactions_ccs.user_id')
            ->join('user_levels','user_levels.user_id','users.id')
            ->join('users as dist_user','dist_user.id','user_levels.toplevel_id')
            ->whereBetween('transactions_ccs.created_at', array($from, $to))
            ->orderBy('transactions_ccs.id','DESC')->get();
            
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
